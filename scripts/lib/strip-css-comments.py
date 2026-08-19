#!/usr/bin/env python3
"""
strip-css-comments.py -- remove /* ... */ comments from a stylesheet.

Why this exists
---------------
This project has no build step, so every byte of source ships to the browser.
components.css is ~60% comments by raw bytes, and those comments cost ~9.2 KB
gzipped -- 45% of the 20 KB CSS transfer budget the Phase 3 UI-SPEC sets.

The comments are NOT waste: recording *why* a selector is shaped the way it is
prevented real defects in Phase 2 (the CR-01/CR-02 specificity bugs). So they
stay in source and are stripped on the way out, at deploy time. Source keeps the
rationale; the wire does not pay for it.

Why not a regex
---------------
A naive s|/\\*.*?\\*/||s eats the rest of the file the moment a stylesheet
contains "/*" inside a quoted string. components.css already has content: "/"
(the breadcrumb separator), which proves quoted punctuation is in play here --
one more character and a regex stripper would silently truncate the file. This
scanner tracks string state, so a comment opener inside '...' or "..." is left
alone.

Preserved deliberately:
  - /*! ... */ bang comments (the license convention), if any are ever added
  - everything inside quoted strings
  - url(...) tokens, which cannot contain a comment

Usage: strip-css-comments.py < in.css > out.css
"""
import sys


def strip(css):
    out = []
    i = 0
    n = len(css)
    quote = None  # active string delimiter, or None
    while i < n:
        c = css[i]
        if quote:
            out.append(c)
            if c == "\\" and i + 1 < n:      # escape: copy the pair verbatim
                out.append(css[i + 1])
                i += 2
                continue
            if c == quote:
                quote = None
            i += 1
            continue
        if c in ("'", '"'):
            quote = c
            out.append(c)
            i += 1
            continue
        if c == "/" and i + 1 < n and css[i + 1] == "*":
            if i + 2 < n and css[i + 2] == "!":   # /*! ... */ -- keep
                end = css.find("*/", i + 3)
                end = n if end == -1 else end + 2
                out.append(css[i:end])
                i = end
                continue
            end = css.find("*/", i + 2)
            i = n if end == -1 else end + 2       # unterminated: drop the tail
            # A comment can sit between two tokens that must not fuse. Emit a
            # single space so `a/*x*/b` becomes `a b`, never `ab`.
            if out and not out[-1].isspace():
                out.append(" ")
            continue
        out.append(c)
        i += 1
    return "".join(out)


def collapse_blank_lines(css):
    """A stripped comment usually leaves its whole line blank. Drop runs of
    blank lines down to one so the output stays readable if anyone views it."""
    lines = css.split("\n")
    kept = []
    blank = False
    for line in lines:
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
    sys.stdout.write(collapse_blank_lines(strip(src)))
