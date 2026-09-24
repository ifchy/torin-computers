#!/usr/bin/env bash
# strip-js-selftest.sh -- prove scripts/lib/strip-js-comments.py before a deploy
# is ever allowed to run it over a real file.
#
# WHY THIS EXISTS AT ALL. A comment stripper is a file rewriter that runs on the
# way to production, unattended, on every deploy. Its worst failure is not an
# error -- it is a SILENT TRUNCATION that still uploads, still returns 200, and
# breaks a page for a visitor. The CSS stripper's own header records the near
# miss that motivated string-awareness (a `content: "/"` one character away from
# a regex stripper eating the file).
#
# So this suite is written in the shape the rest of the project's selftests use:
# each assertion names the defect it exists to catch, and the LAST section runs
# the stripper over every real file in src/js/ and requires the output to still
# parse. A stripper that has never been run against the actual tree is a
# specification, not a gate.
set -uo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
STRIP="${SCRIPT_DIR}/lib/strip-js-comments.py"
PASS=0; FAIL=0

ok()   { PASS=$((PASS+1)); printf 'ok   %s\n' "$1"; }
bad()  { FAIL=$((FAIL+1)); printf 'FAIL %s\n' "$1"; [ $# -gt 1 ] && printf '     %s\n' "$2"; }

# expect_out <name> <input> <expected-substring>
expect_has() {
  local name="$1" in="$2" want="$3" got
  got="$(printf '%s' "$in" | python3 "$STRIP" 2>/dev/null)"
  case "$got" in *"$want"*) ok "$name" ;; *) bad "$name" "wanted to find <<$want>> in <<$got>>" ;; esac
}

expect_lacks() {
  local name="$1" in="$2" bad_str="$3" got
  got="$(printf '%s' "$in" | python3 "$STRIP" 2>/dev/null)"
  case "$got" in *"$bad_str"*) bad "$name" "found <<$bad_str>> in <<$got>>" ;; *) ok "$name" ;; esac
}

expect_refuse() {
  local name="$1" in="$2"
  printf '%s' "$in" | python3 "$STRIP" >/dev/null 2>&1
  if [ "$?" = "2" ]; then ok "$name"; else bad "$name" "expected exit 2 (refuse), got $?"; fi
}

echo "--- comments actually come off ---"
expect_lacks "a line comment is removed"            'var a = 1; // gone
' 'gone'
expect_lacks "a block comment is removed"           'var a = /* gone */ 1;' 'gone'
expect_has   "the code around it survives"          'var a = /* gone */ 1;' 'var a ='

echo "--- ASI: newlines are never eaten ---"
# `return` on its own line means `return undefined`. If the stripper removes the
# newline after a trailing comment, the meaning of the program changes silently.
expect_has "a line comment keeps its newline"       'return // c
x' '
x'
expect_has "a multi-line block keeps its newlines"  'a /* one
two */ b' 'a
'

echo "--- strings are not scanned for comments ---"
# The CSS stripper's motivating defect, ported: a comment opener inside a string.
expect_has "// inside a single-quoted string stays" "var u = 'http://x/y';" "http://x/y"
expect_has "/* inside a double-quoted string stays" 'var s = "a /* b";' 'a /* b'
expect_has "an escaped quote does not end a string" 'var s = "a\"// b"; var t = 1;' '// b'
expect_has "a template literal is left alone"       'var s = `a // b`;' '// b'
expect_has "a template interpolation survives"      'var s = `a ${x + 1} b`;' '${x + 1}'

echo "--- division is not mistaken for a regex ---"
# photo-resize.js has `it.o.size / 1024` and `MAX / Math.max(...)`. Refusing on
# those would make this stripper useless on the one file with the least headroom.
expect_has "identifier / number is division"        'var k = size / 1024;' 'size / 1024'
expect_has ") / identifier is division"             'var s = Math.min(1, MAX / Math.max(w, h));' 'MAX / Math.max'
expect_has "a quoted mime type is not division"     "var t = 'image/jpeg';" 'image/jpeg'

echo "--- a regex literal is REFUSED, not guessed at ---"
expect_refuse "after = a slash is a regex"          'var re = /ab*/;'
expect_refuse "after ( a slash is a regex"          'if (/x/.test(s)) { y(); }'
expect_refuse "after return a slash is a regex"     'function f() { return /x/; }'

echo "--- bang comments are kept (license convention) ---"
expect_has "/*! ... */ survives"                    '/*! keep me */ var a = 1;' 'keep me'

echo "--- the real tree: every file still parses after stripping ---"
if ! command -v node >/dev/null 2>&1; then
  printf 'SKIP every-file parse check -- no node binary to parse with\n'
else
  for f in "${SCRIPT_DIR}/../src/js/"*.js; do
    [ -e "$f" ] || continue
    b="$(basename "$f")"
    tmp="$(mktemp "${TMPDIR:-/tmp}/stripjs-XXXXXX").js"
    if python3 "$STRIP" < "$f" > "$tmp" 2>/dev/null; then
      if node --check "$tmp" 2>/dev/null; then
        raw=$(wc -c < "$f" | tr -d ' '); new=$(wc -c < "$tmp" | tr -d ' ')
        rawz=$(gzip -9 -c "$f" | wc -c | tr -d ' '); newz=$(gzip -9 -c "$tmp" | wc -c | tr -d ' ')
        ok "$b parses after stripping  ${raw}->${new} B raw, ${rawz}->${newz} B gzipped"
      else
        bad "$b parses after stripping" "node --check rejected the stripped output"
      fi
    else
      bad "$b was stripped" "the stripper refused; deploy would ship it unchanged"
    fi
    rm -f "$tmp"
  done
fi

echo
if [ "$FAIL" = "0" ]; then
  echo "PASS -- ${PASS}/${PASS} behaviours hold."
  exit 0
fi
echo "FAIL -- ${FAIL} failing, ${PASS} passing."
exit 1
