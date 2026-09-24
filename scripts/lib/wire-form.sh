#!/usr/bin/env bash
# wire-form.sh -- the ONE definition of "what bytes does this source file turn
# into on the wire". Sourced by scripts/deploy-new.sh (which uploads that form)
# and by scripts/cutover-sweep.sh (which asserts the origin is serving it).
#
# WHY THIS FILE EXISTS -- ledger #56.
#
# On 2026-09-23 staging was found to be roughly TWO DAYS behind the tree. The
# footer band the owner had removed was still live on all 20 pages and the dev
# theme scaffolding deleted at 04-07 was still being served. The cutover sweep
# reported 20/20 PASS throughout, because nothing it asserted asked whether the
# bytes on the origin were the bytes in the commit: section 3b asserts a file is
# PRESENT, and the rendered probe asserts a page renders prose. A stale file is
# present, and it renders prose.
#
# The obvious fix -- digest the local file, digest the served file, compare --
# is WRONG here, and that is the whole reason this helper exists rather than ten
# lines inside the sweep. deploy-new.sh does not upload source bytes. It strips
# comments from CSS (and, since ledger #43, from JS), so those files are
# DELIBERATELY not byte-identical to src/ and a naive comparison would report a
# permanent false failure on every stylesheet and every script. A gate that
# always fails is a gate that gets switched off.
#
# So the comparison has to be against the TRANSFORMED form -- and if the sweep
# reimplemented that transform, the two copies would drift the first time the
# deploy pipeline changed, and the sweep would start lying in whichever
# direction the drift went. Instead both callers ask THIS function. There is one
# definition; drift is structurally impossible rather than merely discouraged.
#
# Usage:  source scripts/lib/wire-form.sh
#         torin_wire_form <rel-path> <src-path> <out-path>
#
# Writes the expected wire bytes for <src-path> to <out-path>.
#   returns 0 -- <out-path> holds the TRANSFORMED form
#   returns 1 -- the file ships UNCHANGED; <out-path> holds a copy of the source
#   returns 2 -- THE TRANSFORM IS BROKEN. Nothing can be concluded.
#
# Return 1 is not an error: it is the answer for every file that is not CSS or
# JS, and it is the deliberate landing spot for a stripper that REFUSED (the JS
# stripper exits 2 rather than guess at a regex literal). deploy-new.sh fails
# open on it and ships the source, so "the stripper refused" and "the sweep
# expects source bytes" stay one decision made in one place.
#
# RETURN 2 EXISTS BECAUSE OF A BUG THIS FILE ALREADY HAD. The first version
# computed its own directory from ${BASH_SOURCE[0]} at CALL time and, when that
# came back empty, resolved the stripper against the caller's cwd, found
# nothing, and fell into the return-1 branch. A MISSING STRIPPER THEREFORE
# LOOKED EXACTLY LIKE "this file ships unchanged" -- so the sweep would have
# compared every stylesheet against its unstripped source and reported all four
# as stale, or, had the drift gone the other way, quietly passed. A gate whose
# breakage is indistinguishable from a legitimate answer is the precise failure
# ledger #56 exists to stop. Missing tooling is now loud.
#
# The directory is resolved ONCE, when this file is sourced, not per call.

if [ -n "${BASH_SOURCE:-}" ]; then
  _TORIN_WIRE_SELF="${BASH_SOURCE[0]}"
elif [ -n "${ZSH_VERSION:-}" ]; then
  _TORIN_WIRE_SELF="${(%):-%x}"          # zsh has no BASH_SOURCE
else
  _TORIN_WIRE_SELF="$0"
fi
_TORIN_WIRE_DIR="$(cd "$(dirname "${_TORIN_WIRE_SELF}")" 2>/dev/null && pwd)"

torin_wire_form() {
  local rel="$1" src="$2" out="$3"
  local stripper

  case "$rel" in
    *.css) stripper="${_TORIN_WIRE_DIR}/strip-css-comments.py" ;;
    *.js)  stripper="${_TORIN_WIRE_DIR}/strip-js-comments.py"  ;;
    *)     cp "$src" "$out" 2>/dev/null; return 1              ;;
  esac

  # Loud, not silent: a stripper that is not where we think it is means the
  # wire form is unknown, and an unknown wire form is not a comparison.
  if [ ! -f "$stripper" ]; then
    echo "wire-form: TRANSFORM MISSING -- ${stripper} not found; the expected wire form for ${rel} is unknown" >&2
    return 2
  fi

  if python3 "$stripper" < "$src" > "$out" 2>/dev/null && [ -s "$out" ]; then
    return 0
  fi

  # Stripper ran and declined (JS refusal on a regex literal, or an empty
  # result). That IS a legitimate answer: the source ships as-is.
  cp "$src" "$out" 2>/dev/null
  return 1
}
