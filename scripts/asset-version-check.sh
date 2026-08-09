#!/usr/bin/env bash
#
# asset-version-check.sh
#
# The non-cache-busted gate for gap G-02-1: asserts that every stylesheet and
# the one script emitted by the shared head carry a live, resolvable version
# token, on all sixteen deployed pages, fetched the way a real browser fetches
# them.
#
# WHY THIS SCRIPT EXISTS -- and why it fetches BARE URLs.
# Every rendered probe in Phase 2 reached the origin with a cache-busting query
# string appended to the page URL. That bypassed exactly the cache that produced
# G-02-1, so the automated verification measured a state no returning visitor
# ever sees, and could not have caught this class of defect however many checks
# it ran (02-UAT.md G-02-1 `method_defect`). This script therefore performs NO
# cache-defeating fetch of any kind: no version query on a page URL, no
# Cache-Control request header, no Pragma request header. It asserts on what a
# browser would actually request.
#
# WHAT IT PROVES, AND WHAT IT DOES NOT.
# It proves a version token is present, LIVE and resolvable on bare-URL fetches.
# It does NOT itself demonstrate that a browser evicted an old file -- curl does
# not cache, so no curl-based check can observe an eviction. The token is the
# MECHANISM by which a browser does so (a different URL is a different cache
# entry), and the bounded max-age asserted in Check C is the independent
# backstop behind it. Two mechanisms must both fail before the condition recurs.
#
# THE TWO LIVENESS CLAIMS ARE NOT THE SAME CLAIM. Check B compares each token
# against the epoch of the origin's Last-Modified for that same asset. That
# proves PATH RESOLUTION, not freshness: filemtime() and Apache's Last-Modified
# read the same inode stat, so the two are equal BY CONSTRUCTION whenever the
# helper stat'd the same file the origin serves. It is a real test of exactly
# that -- it catches a stamp computed against a path that does not exist on the
# server, or against the wrong copy of a file, and (via the ?v=0 sentinel this
# script rejects) a stat that failed outright. It CANNOT catch a frozen stamp,
# because freezing moves both values together and they stay equal. Freeze
# detection is a separate observation this script cannot make: re-upload a
# byte-identical js/site.js and confirm the emitted token MOVES. Do not read a
# green run here as proof the stamp is unfrozen.
#
# No npm dependency, consistent with this project's no-build-step constraint.
# node is used only for date arithmetic, exactly as render-check.sh uses it for
# port selection.
#
# Usage:
#   scripts/asset-version-check.sh                       # staging default
#   scripts/asset-version-check.sh https://torin.bg/new  # explicit base URL

set -euo pipefail

BASE="${1:-https://torin.bg/new}"
BASE="${BASE%/}"

# The sixteen deployed slugs. Same set the 02-07 sweep uses.
SLUGS="index about laptopi profilaktika-laptop optimizatsiq mehanichni-problemi \
za-bateriite tokov-udar zalivane-technosti rezervni-chasti warrently \
uslovia covid test-laptop problem-stari msg"

FAILURES=0

fail() {
	echo "  FAIL: $*" >&2
	FAILURES=$((FAILURES + 1))
}

# grep -c exits 1 on zero matches, which set -e would treat as fatal. Every
# count in this script goes through here so a legitimate zero is a value, not
# an abort.
count() {
	local pattern="$1"
	local body="$2"
	printf '%s\n' "$body" | grep -cE "$pattern" || true
}

echo "asset-version-check: ${BASE}"
echo "fetching with bare URLs -- no version query, no cache-defeating header"
echo

# ---------------------------------------------------------------------------
# Check A -- sixteen pages, bare URLs.
# ---------------------------------------------------------------------------
echo "== Check A: sixteen pages, bare-URL fetch =="
printf '%-24s %-8s %-6s %-8s %-10s %-8s %-8s %-8s %s\n' \
	PAGE HTTP PHP STAMPED UNSTAMPED SITEJS WOFF2 WOFF2Q SENTINEL

for p in $SLUGS; do
	url="${BASE}/${p}.html"
	http=$(curl -s -o /dev/null -w '%{http_code}' "$url")
	body=$(curl -s "$url")

	php_tags=$(count '<\?php' "$body")
	stamped=$(count 'href="css/[a-z0-9-]+\.css\?v=[0-9]{10}"' "$body")
	unstamped=$(count 'href="css/[a-z0-9-]+\.css"' "$body")
	sitejs=$(count 'src="js/site\.js\?v=[0-9]{10}"' "$body")
	woff2=$(count 'href="fonts/sofia-sans-cyrillic\.woff2"' "$body")
	woff2q=$(count 'fonts/[a-z0-9-]+\.woff2\?' "$body")
	sentinel=$(count '\?v=0([^0-9]|$)' "$body")

	printf '%-24s %-8s %-6s %-8s %-10s %-8s %-8s %-8s %s\n' \
		"${p}.html" "$http" "$php_tags" "$stamped" "$unstamped" \
		"$sitejs" "$woff2" "$woff2q" "$sentinel"

	[ "$http" = "200" ]   || fail "${p}.html HTTP ${http}, expected 200"
	[ "$php_tags" -eq 0 ] || fail "${p}.html leaked ${php_tags} literal PHP open tag(s) -- the shared head fataled or the handler is gone"
	[ "$stamped" -ge 4 ]  || fail "${p}.html has only ${stamped} stamped stylesheet href(s), expected at least 4"
	[ "$unstamped" -eq 0 ] || fail "${p}.html has ${unstamped} UNSTAMPED stylesheet href(s) -- this is G-02-1 reopening"
	[ "$sitejs" -eq 1 ]   || fail "${p}.html has ${sitejs} stamped js/site.js, expected exactly 1"
	[ "$woff2" -eq 1 ]    || fail "${p}.html has ${woff2} bare woff2 preload(s), expected exactly 1"
	[ "$woff2q" -eq 0 ]   || fail "${p}.html carries a QUERY STRING on a woff2 URL. Never stamp the font: the preload must byte-match the @font-face src in base.css (which has no query string) or the subset downloads twice, AND scripts/probes/font-swap.js blocks fonts by the glob *.woff2, which a query string defeats -- a stamped font makes the G-02-4 reflow gate report maxAbsDeltaPx: 0, blinding it rather than failing it"
	[ "$sentinel" -eq 0 ] || fail "${p}.html emitted the ?v=0 stat-failed sentinel ${sentinel} time(s) -- torin_asset_url() could not stat the file, so the path it resolves is wrong on the server"

	# css/theme-a.css is asserted CONDITIONALLY: stamped if present, no failure
	# if absent. This deliberately differs from the exactly-one assertion plan
	# 02-08 task 2 used on the same link, and the difference is not an
	# inconsistency to reconcile away.
	#
	# That link is emitted today only because dev-switcher.php line 34 assigns
	# $torin_extra_head OUTSIDE the `if ($torin_theme === 'a')` branch above it,
	# so the Theme A override is linked on every page load including the default
	# Theme B. Task 2's job was to pin the markup that plan actually emitted.
	# THIS SCRIPT'S JOB IS DIFFERENT: it is committed and re-run for the rest of
	# the project's life, and TWO coming changes will remove that link --
	#   (1) the one-line fix moving line 34 inside the branch, after which the
	#       link is absent on Theme B, and
	#   (2) the Phase 4 cutover, which deletes dev-switcher.php outright.
	# A hard assertion here would turn either of those into a spurious failure.
	# If you are making one of those changes: nothing below needs editing.
	theme_any=$(count 'href="css/theme-a\.css' "$body")
	if [ "$theme_any" -gt 0 ]; then
		theme_stamped=$(count 'href="css/theme-a\.css\?v=[0-9]{10}"' "$body")
		[ "$theme_stamped" -eq "$theme_any" ] \
			|| fail "${p}.html links theme-a.css ${theme_any} time(s) but only ${theme_stamped} stamped"
	fi
done
echo

# ---------------------------------------------------------------------------
# Check B -- every distinct stamped asset URL resolves, and its token is real.
# ---------------------------------------------------------------------------
echo "== Check B: stamped URL resolves, token == Last-Modified epoch =="
echo "   (proves PATH RESOLUTION -- the helper stat'd the file the origin serves."
echo "    A frozen stamp passes this check; only a redeploy can detect freezing.)"
printf '%-28s %-8s %-14s %-14s %s\n' ASSET HTTP TOKEN LASTMODIFIED VERDICT

HOME_BODY=$(curl -s "${BASE}/index.html")
STAMPED_ASSETS=$(printf '%s\n' "$HOME_BODY" \
	| grep -oE '(css/[a-z0-9-]+\.css|js/site\.js)\?v=[0-9]+' \
	| sort -u || true)

if [ -z "$STAMPED_ASSETS" ]; then
	fail "no stamped asset URLs found on the homepage at all"
fi

for entry in $STAMPED_ASSETS; do
	path="${entry%%\?*}"
	token="${entry##*v=}"

	http=$(curl -s -o /dev/null -w '%{http_code}' "${BASE}/${entry}")
	lm=$(curl -sI "${BASE}/${path}" | tr -d '\r' \
		| awk 'tolower($1)=="last-modified:"{sub(/^[^:]*: */,"");print}')

	if [ -z "$lm" ]; then
		epoch="none"
		verdict="NO-LAST-MODIFIED"
	else
		epoch=$(node -e 'const t=Date.parse(process.argv[1]); console.log(Number.isNaN(t)?"none":Math.floor(t/1000));' "$lm")
		if [ "$epoch" = "$token" ]; then verdict="MATCH"; else verdict="MISMATCH"; fi
	fi

	printf '%-28s %-8s %-14s %-14s %s\n' "$path" "$http" "$token" "$epoch" "$verdict"

	[ "$http" = "200" ] || fail "stamped URL ${entry} returned ${http} -- the stamped URL does not resolve"
	[ "$token" != "0" ] || fail "${path} carries the ?v=0 stat-failed sentinel"
	[ "$verdict" = "MATCH" ] || fail "${path} token ${token} != Last-Modified epoch ${epoch} -- the helper stat'd a different file than the origin serves"
done
echo

# ---------------------------------------------------------------------------
# Check C -- the cache lifetime is bounded.
# ---------------------------------------------------------------------------
echo "== Check C: Cache-Control max-age bounded (<= 600) =="
echo "   (the second line of defence behind the stamp; catches a future edit"
echo "    raising the staging expiry back to days while the stamp stands alone)"
printf '%-28s %-12s %s\n' ASSET MAXAGE VERDICT

MAX_ALLOWED=600
for path in css/base.css css/components.css js/site.js; do
	cc=$(curl -sI "${BASE}/${path}" | tr -d '\r' \
		| awk 'tolower($1)=="cache-control:"{sub(/^[^:]*: */,"");print}')
	maxage=$(printf '%s\n' "$cc" | grep -oE 'max-age=[0-9]+' | head -1 | sed 's/.*=//' || true)

	if [ -z "$maxage" ]; then
		printf '%-28s %-12s %s\n' "$path" "none" "MISSING"
		fail "${path} returned no Cache-Control max-age -- it falls to heuristic caching, which is undeclared behaviour"
	elif [ "$maxage" -gt "$MAX_ALLOWED" ]; then
		printf '%-28s %-12s %s\n' "$path" "$maxage" "TOO LONG"
		fail "${path} max-age=${maxage} exceeds ${MAX_ALLOWED} -- /new/ is a reviewed staging preview whose CSS changes several times a day (Phase 4 raises this, DESIGN-02)"
	else
		printf '%-28s %-12s %s\n' "$path" "$maxage" "OK"
	fi
done
echo

if [ "$FAILURES" -gt 0 ]; then
	echo "asset-version-check: FAILED (${FAILURES} failure(s))" >&2
	exit 1
fi

echo "asset-version-check: PASS"
echo "  16/16 pages stamped on bare-URL fetches, every token resolves and equals"
echo "  the origin's Last-Modified, every lifetime bounded."
exit 0
