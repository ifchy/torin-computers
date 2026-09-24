#!/usr/bin/env bash
#
# sitemap-check.sh
#
# Asserts the equality that matters: the set of URLs in src/sitemap.xml equals
# the set of pages that are actually publishable and indexable. A static sitemap
# is a second copy of information that lives in the source tree, and in this
# project every duplicated value has eventually disagreed with its twin.
#
# WHAT IT CHECKS
#   A  Every indexable page in the tree appears in the sitemap.  (missing URL)
#   B  Every sitemap URL corresponds to a real indexable page.   (extra URL)
#   C  No no-indexed page is listed.                             (contradiction)
#   D  Every <loc> is built from site-config.php base_url.       (cutover drift)
#   E  robots.txt Sitemap: matches that same base_url.           (cutover drift)
#   F  --live only: every listed URL returns 200. Reasons from the ORIGIN.
#
# Usage:
#   scripts/sitemap-check.sh                      # offline, against the tree
#   scripts/sitemap-check.sh --live               # also fetch every URL
#   scripts/sitemap-check.sh --live --base URL    # override the origin
#   scripts/sitemap-check.sh --sitemap FILE       # check a specific file
#   scripts/sitemap-check.sh --robots FILE        # check a specific robots.txt
#
# --sitemap/--robots exist so every branch below can be PROVED to fail against a
# corrupted input without mutating a tracked file. A check only ever shown to
# pass proves nothing: this project found seven defective check commands in one
# phase, every one by running it rather than reading it.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
SRC_DIR="${REPO_ROOT}/src"
CONFIG="${SRC_DIR}/includes/site-config.php"
SITEMAP="${SRC_DIR}/sitemap.xml"
ROBOTS="${SRC_DIR}/robots.txt"

LIVE=0
BASE_OVERRIDE=""

while [ "$#" -gt 0 ]; do
	case "$1" in
		--live)    LIVE=1; shift ;;
		--base)    BASE_OVERRIDE="${2:-}"; shift 2 ;;
		--sitemap) SITEMAP="${2:-}"; shift 2 ;;
		--robots)  ROBOTS="${2:-}"; shift 2 ;;
		*) echo "ERROR: unknown argument '$1'" >&2; exit 2 ;;
	esac
done

FAILURES=0
fail() { echo "  FAIL: $*" >&2; FAILURES=$((FAILURES + 1)); }
ok()   { echo "  ok:   $*"; }

for f in "$CONFIG" "$SITEMAP" "$ROBOTS"; do
	if [ ! -f "$f" ]; then
		echo "ERROR: required file not found: $f" >&2
		exit 2
	fi
done

BASE_URL="$(sed -n "s/^[[:space:]]*'base_url'[[:space:]]*=>[[:space:]]*'\([^']*\)'.*/\1/p" "$CONFIG" | head -1)"
if [ -z "$BASE_URL" ]; then
	echo "ERROR: could not read 'base_url' from ${CONFIG}" >&2
	exit 2
fi
BASE_URL="${BASE_URL%/}/"

echo "sitemap-check"
echo "  sitemap : ${SITEMAP}"
echo "  base_url: ${BASE_URL} (from src/includes/site-config.php)"
echo

# --- the tree's view -------------------------------------------------------
# Same derivation gen-sitemap.sh uses, deliberately: if the rule for "indexable"
# ever changes it must change in both, and a check that re-derives the set is
# what makes that failure visible instead of silent.
EXPECTED_LOCS=""
NOINDEX_LOCS=""
for f in "${SRC_DIR}"/*.html; do
	[ -e "$f" ] || continue
	base="$(basename "$f")"
	if [ "$base" = "index.html" ]; then
		loc="${BASE_URL}"
	else
		loc="${BASE_URL}${base}"
	fi
	# Not every .html in src/ is a page -- the Search Console verification token
	# is plain text with no PHP and no $torin_robots line, so the noindex rule
	# cannot see it. Discriminate on including includes/header.php, exactly as
	# gen-sitemap.sh does. KEEP THE TWO RULES IDENTICAL: this block exists to
	# re-derive the set independently, and it is only worth anything while both
	# sides answer the same question.
	if ! grep -q 'includes/header.php' "$f"; then
		continue
	fi
	if grep -qE '^\$torin_robots[[:space:]]*=.*noindex' "$f"; then
		NOINDEX_LOCS="${NOINDEX_LOCS}${loc}"$'\n'
	else
		EXPECTED_LOCS="${EXPECTED_LOCS}${loc}"$'\n'
	fi
done

# --- the sitemap's view ----------------------------------------------------
# Every `|| true` below guards the SAME trap: grep exits 1 on zero matches, and
# with `set -euo pipefail` that aborts the script mid-check -- exiting non-zero
# having printed no reason at all, which is indistinguishable from a crash. An
# EMPTY sitemap is precisely the corrupted input this script must DIAGNOSE, so
# it must not be the input that kills it. Found by feeding it an empty sitemap,
# not by reading the code.
ACTUAL_LOCS="$(grep -o '<loc>[^<]*</loc>' "$SITEMAP" | sed 's|<loc>||;s|</loc>||' || true)"$'\n'

expected_sorted="$(printf '%s' "$EXPECTED_LOCS" | grep -v '^$' | sort || true)"
actual_sorted="$(printf '%s' "$ACTUAL_LOCS"   | grep -v '^$' | sort || true)"

if [ -z "$actual_sorted" ]; then
	fail "sitemap contains no <loc> entries at all (${SITEMAP})"
fi

echo "Check A/B -- sitemap URL set equals the indexable page set"
missing="$(comm -23 <(printf '%s\n' "$expected_sorted") <(printf '%s\n' "$actual_sorted") || true)"
extra="$(comm -13 <(printf '%s\n' "$expected_sorted") <(printf '%s\n' "$actual_sorted") || true)"

if [ -n "$missing" ]; then
	while IFS= read -r u; do [ -n "$u" ] && fail "MISSING from sitemap (page exists and is indexable): $u"; done <<< "$missing"
fi
if [ -n "$extra" ]; then
	while IFS= read -r u; do [ -n "$u" ] && fail "EXTRA in sitemap (no such indexable page): $u"; done <<< "$extra"
fi
if [ -z "$missing" ] && [ -z "$extra" ]; then
	ok "$(printf '%s\n' "$actual_sorted" | grep -c . || true) URLs, exact match"
fi
echo

echo "Check C -- no no-indexed page is listed"
c_fail=0
while IFS= read -r u; do
	[ -z "$u" ] && continue
	if printf '%s\n' "$actual_sorted" | grep -qxF "$u"; then
		fail "CONTRADICTION: $u carries a noindex directive but is listed in the sitemap"
		c_fail=1
	fi
done <<< "$NOINDEX_LOCS"
[ "$c_fail" -eq 0 ] && ok "$(printf '%s' "$NOINDEX_LOCS" | grep -c . || true) no-indexed page(s) correctly absent"
echo

echo "Check D -- every <loc> is built from base_url"
d_fail=0
while IFS= read -r u; do
	[ -z "$u" ] && continue
	case "$u" in
		"${BASE_URL}"*) ;;
		*) fail "STALE BASE: $u does not start with ${BASE_URL} -- regenerate after the cutover"; d_fail=1 ;;
	esac
done <<< "$actual_sorted"
[ "$d_fail" -eq 0 ] && ok "all <loc> values share the configured base"
echo

echo "Check E -- robots.txt Sitemap: matches base_url"
# `|| true` is load-bearing. grep exits 1 when it matches nothing, and under
# `set -euo pipefail` that aborts the script HERE -- exiting non-zero without
# ever printing the reason. The absent-Sitemap-line case then looks like a
# failure that diagnoses nothing, which is the worst kind: it is indistinguishable
# from a crash. Caught by running the case, not by reading the line.
robots_sitemap="$(grep -i '^[[:space:]]*Sitemap:' "$ROBOTS" | head -1 | sed 's/^[[:space:]]*[Ss]itemap:[[:space:]]*//' | tr -d '\r' || true)"
expected_sitemap_url="${BASE_URL}sitemap.xml"
if [ -z "$robots_sitemap" ]; then
	fail "robots.txt has no Sitemap: line"
elif [ "$robots_sitemap" != "$expected_sitemap_url" ]; then
	fail "robots.txt Sitemap: is '${robots_sitemap}' but base_url implies '${expected_sitemap_url}'"
else
	ok "robots.txt Sitemap: ${robots_sitemap}"
fi
echo

# --- live mode -------------------------------------------------------------
if [ "$LIVE" -eq 1 ]; then
	LIVE_BASE="${BASE_OVERRIDE:-$BASE_URL}"
	LIVE_BASE="${LIVE_BASE%/}/"
	echo "Check F -- live fetch against ${LIVE_BASE}"
	echo "  NOTE: this measures the DEPLOYED build. If src/ has not been uploaded"
	echo "        since the last edit, a failure here is about the server, not the tree."

	while IFS= read -r u; do
		[ -z "$u" ] && continue
		fetch_url="$u"
		if [ -n "$BASE_OVERRIDE" ]; then
			fetch_url="${LIVE_BASE}${u#$BASE_URL}"
		fi
		code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$fetch_url" || echo 000)"
		if [ "$code" = "200" ]; then
			ok "200 $fetch_url"
		else
			fail "listed URL returned ${code}: ${fetch_url}"
		fi
	done <<< "$actual_sorted"

	# The other direction: a page that is NOT in the sitemap but answers 200 and
	# is indexable is a page the sitemap forgot. Only the no-indexed set is
	# allowed to 200 while absent.
	while IFS= read -r u; do
		[ -z "$u" ] && continue
		fetch_url="$u"
		if [ -n "$BASE_OVERRIDE" ]; then
			fetch_url="${LIVE_BASE}${u#$BASE_URL}"
		fi
		code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "$fetch_url" || echo 000)"
		ok "no-indexed ${fetch_url} -> ${code} (absent from sitemap by design)"
	done <<< "$NOINDEX_LOCS"

	robots_code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "${LIVE_BASE}robots.txt" || echo 000)"
	sitemap_code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 20 "${LIVE_BASE}sitemap.xml" || echo 000)"
	[ "$robots_code"  = "200" ] && ok "200 ${LIVE_BASE}robots.txt"  || fail "robots.txt returned ${robots_code}"
	[ "$sitemap_code" = "200" ] && ok "200 ${LIVE_BASE}sitemap.xml" || fail "sitemap.xml returned ${sitemap_code}"
	echo
fi

# ---------------------------------------------------------------------------
# Check F -- the file is actually WELL-FORMED XML.
#
# WHY THIS EXISTS, and it is not hypothetical. Every assertion above this line
# is a grep. Greps cannot see malformed markup, so on 2026-09-24 this script
# reported PASS on all five checks against a sitemap that no XML parser would
# accept -- caught one step before the file was due to be submitted to Search
# Console, and present in every sitemap this project had ever generated.
#
# The cause: the generated header comment contained "--", which is ILLEGAL
# inside an XML comment. gen-sitemap.sh no longer emits it. This check is the
# backstop, because the next malformation will be something else.
#
# A sitemap that does not parse is not a partially-working sitemap. The search
# engine rejects the whole file, and the failure is reported in a console the
# person who deployed it may never open.
echo
echo "Check F -- the sitemap parses as XML"
if command -v python3 >/dev/null 2>&1; then
	if xml_err="$(python3 -c '
import sys, xml.etree.ElementTree as ET
try:
    r = ET.parse(sys.argv[1]).getroot()
except Exception as e:
    print(e); sys.exit(1)
ns = "{http://www.sitemaps.org/schemas/sitemap/0.9}"
n = len(list(r.iter(ns + "loc")))
if n == 0:
    print("parsed, but no <loc> elements in the sitemap namespace"); sys.exit(1)
print(n)
' "$SITEMAP" 2>&1)"; then
		ok "well-formed XML, ${xml_err} <loc> elements in the sitemap namespace"
	else
		fail "sitemap is NOT well-formed XML: ${xml_err}"
	fi
else
	echo "  SKIP: no python3 to parse with -- Check F did not run"
fi

if [ "$FAILURES" -gt 0 ]; then
	echo "sitemap-check: ${FAILURES} failure(s)" >&2
	exit 1
fi
echo "sitemap-check: PASS"
