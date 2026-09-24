#!/usr/bin/env python3
"""
strip-js-comments.py -- remove // and /* ... */ comments from a script.

Why this exists
---------------
Same reason as strip-css-comments.py, which this deliberately mirrors: this
project has no build step, so every source byte is a wire byte. analytics.js
gzips to 1959 B against the 1 KB budget the UI-SPEC sets -- and the CODE ALONE
is 972 B. The comments are the entire overage. photo-resize.js is the second
file to hit the same wall, at 2042 B against 2048 B: six bytes of headroom, so
the next sentence anyone writes in it breaks a gate.

Those comments are not waste -- each one exists to stop a specific future edit
from reintroducing a defect. So they stay in source and come off on the way out.
Source keeps the rationale; the wire does not pay for it. (Ledger #43, option b.)

Why not a regex
---------------
The CSS stripper already explains why a naive s|/\\*.*?\\*/||s is unsafe around
quoted strings. JavaScript adds two hazards CSS does not have:

  1. ASI. Removing a line comment must NEVER remove its newline. `return // x`
     followed by a newline means something different from `return` joined to the
     next line. Every newline in the input survives to the output.

  2. Regex literals. `/ab*/.test(s)` contains `/*`, and a stripper that does not
     know it is inside a regex will eat the rest of the file. Telling a regex
     literal from a division sign requires knowing the preceding token, which is
     most of a JS lexer.

This scanner tracks string state (', ", and template literals including their
${...} interpolations) and, on hitting a bare `/`, decides regex-vs-division
from the last significant token. If that decision lands on "regex", it does NOT
try to lex the literal -- it REFUSES, exits non-zero, and lets the caller ship
the file unchanged. deploy-new.sh already fails open that way for CSS.

Refusing is the point. A stripper that guesses wrong here truncates a file and
the failure is silent until a visitor loads a broken page. There are no regex
literals in this tree today; this exists so that adding one is a loud no-op
rather than a quiet corruption.

Preserved deliberately:
  - /*! ... */ bang comments (the license convention)
  - everything inside strings and template literals
  - every newline, for ASI

Usage: strip-js-comments.py < in.js > out.js
Exit:  0 stripped, 2 refused (caller should ship the source unchanged)
"""
import sys

# After one of these, a `/` starts a REGEX. After anything else (an identifier,
# a number, `)`, `]`) it is DIVISION. This is the standard heuristic and it is
# only consulted to decide whether to refuse, never to parse.
_REGEX_OK_PUNCT = set("(,=:[!&|?{};+-*%^~<>")
_REGEX_OK_WORDS = {
    "return", "typeof", "instanceof", "in", "of", "new", "delete", "void",
    "throw", "case", "do", "else", "yield", "await",
}


class Refuse(Exception):
    pass


def _last_significant(out):
    """Last non-whitespace character emitted so far, and the word it ends."""
    i = len(out) - 1
    while i >= 0 and out[i].isspace():
        i -= 1
    if i < 0:
        return None, ""
    ch = out[i]
    if not (ch.isalnum() or ch == "_" or ch == "$"):
        return ch, ""
    j = i
    while j >= 0 and (out[j].isalnum() or out[j] == "_" or out[j] == "$"):
        j -= 1
    return ch, "".join(out[j + 1:i + 1])


def strip(src):
    out = []
    i = 0
    n = len(src)
    # Stack of open template-literal contexts, so ${ ... } nesting is tracked.
    tmpl_depth = []
    brace_depth = 0

    while i < n:
        c = src[i]

        # --- string literals -------------------------------------------------
        if c in ("'", '"'):
            quote = c
            out.append(c)
            i += 1
            while i < n:
                d = src[i]
                out.append(d)
                if d == "\\" and i + 1 < n:
                    out.append(src[i + 1])
                    i += 2
                    continue
                i += 1
                if d == quote:
                    break
            continue

        # --- template literals, including ${ } -------------------------------
        if c == "`":
            out.append(c)
            i += 1
            while i < n:
                d = src[i]
                if d == "\\" and i + 1 < n:
                    out.append(d)
                    out.append(src[i + 1])
                    i += 2
                    continue
                if d == "$" and i + 1 < n and src[i + 1] == "{":
                    out.append("${")
                    i += 2
                    tmpl_depth.append(brace_depth)
                    break
                out.append(d)
                i += 1
                if d == "`":
                    break
            continue

        if c == "{":
            brace_depth += 1
            out.append(c)
            i += 1
            continue

        if c == "}":
            # Closing a ${ } returns us to template-literal text.
            if tmpl_depth and brace_depth == tmpl_depth[-1]:
                tmpl_depth.pop()
                out.append(c)
                i += 1
                while i < n:
                    d = src[i]
                    if d == "\\" and i + 1 < n:
                        out.append(d)
                        out.append(src[i + 1])
                        i += 2
                        continue
                    if d == "$" and i + 1 < n and src[i + 1] == "{":
                        out.append("${")
                        i += 2
                        tmpl_depth.append(brace_depth)
                        break
                    out.append(d)
                    i += 1
                    if d == "`":
                        break
                continue
            brace_depth -= 1
            out.append(c)
            i += 1
            continue

        # --- comments and the regex/division fork ----------------------------
        if c == "/" and i + 1 < n:
            nxt = src[i + 1]

            if nxt == "/":
                # Line comment: drop it, KEEP the newline (ASI).
                j = src.find("\n", i)
                i = n if j == -1 else j
                continue

            if nxt == "*":
                if i + 2 < n and src[i + 2] == "!":       # /*! ... */ -- keep
                    end = src.find("*/", i + 3)
                    end = n if end == -1 else end + 2
                    out.append(src[i:end])
                    i = end
                    continue
                end = src.find("*/", i + 2)
                chunk = src[i:(n if end == -1 else end + 2)]
                i = n if end == -1 else end + 2
                # Preserve every newline the comment spanned, for ASI, and keep
                # tokens from fusing across a one-line comment.
                nl = chunk.count("\n")
                if nl:
                    out.append("\n" * nl)
                elif out and not out[-1].isspace():
                    out.append(" ")
                continue

            # A bare `/`. Regex or division?
            ch, word = _last_significant(out)
            is_regex = (
                ch is None
                or (word and word in _REGEX_OK_WORDS)
                or (not word and ch in _REGEX_OK_PUNCT)
            )
            if is_regex:
                raise Refuse(
                    "a regex literal appears to start at offset %d; refusing to "
                    "strip rather than risk truncating the file" % i
                )
            out.append(c)
            i += 1
            continue

        out.append(c)
        i += 1

    return "".join(out)


def collapse_blank_lines(js):
    """A stripped comment usually leaves its line blank. Collapse runs to one so
    the shipped file stays readable to anyone who views source."""
    kept = []
    blank = False
    for line in js.split("\n"):
        if line.strip() == "":
            if not blank:
                kept.append("")
            blank = True
        else:
            kept.append(line.rstrip())
            blank = False
    return "\n".join(kept).strip() + "\n"


if __name__ == "__main__":
    src = sys.stdin.read()
    try:
        sys.stdout.write(collapse_blank_lines(strip(src)))
    except Refuse as e:
        sys.stderr.write("strip-js-comments: REFUSED -- %s\n" % e)
        sys.exit(2)
