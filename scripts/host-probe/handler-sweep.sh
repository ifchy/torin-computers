#!/usr/bin/env bash
#
# handler-sweep.sh — the acceptance gate for plan 04-01 Task 3.
#
# Fetches every page in the staging subtree once and asserts FOUR things about
# each response. Three of them were in the plan; the fourth exists because of
# the fail-safe in src/.htaccess, and without it this whole sweep can pass on a
# site that was never upgraded.
#
#   1. status is 200
#
#   2. the body contains ZERO literal PHP open tags
#        A 200 carrying readable source is the P-3 / T-04-03 failure, and it is
#        completely invisible to a status-code check. This is the whole reason
#        the gate reads bodies at all.
#
#   3. the body contains ZERO 'Warning:' / 'Fatal error:' / 'Parse error:'
#        display_errors = On in php85-fcgi.ini (measured 2026-09-17), so a PHP
#        error renders INTO the page rather than vanishing into a log. That is a
#        production defect flagged for 04-10, but for the duration of this sweep
#        it is precisely what makes breakage visible.
#
#   4. x-powered-by does NOT report PHP/5.2
#        THE ONE THAT MATTERS. src/.htaccess deliberately keeps the old php52
#        handler inside <IfModule !mod_fcgid.c>, so that an absent mod_fcgid
#        cannot strand .html with no handler and serve 19 pages of source. The
#        cost of that safety is that a failed upgrade renders perfectly: 19x
#        200, correct markup, zero warnings — and still PHP 5.2.17. Checks 1-3
#        all pass in that state. Only the runtime assertion separates "upgraded"
#        from "fell back", and this project has already shipped one check that
#        passed on a broken state. expose_php = On (php85-fcgi.ini:286), so the
#        header is present and load-bearing.
#
# kontakti.html is deliberately absent: src/kontakti.html does not exist yet,
# plan 04-02 creates it, and its 404 is correct rather than a regression. The
# page list is generated from src/*.html rather than typed, so it cannot drift
# from the tree.
#
# Usage:
#   scripts/host-probe/handler-sweep.sh                  # sweeps https://torin.bg/new
#   scripts/host-probe/handler-sweep.sh --base <origin>  # e.g. the root at cutover
#
# Exit status is the gate: 0 only if every page passed every assertion.

set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
BASE_URL="https://torin.bg/new"

if [ "${1:-}" = "--base" ]; then
	if [ -z "${2:-}" ]; then
		echo "ERROR: --base needs an origin" >&2
		exit 64
	fi
	BASE_URL="${2%/}"
fi

PAGE_LIST="$(ls "${REPO_ROOT}/src"/*.html 2>/dev/null | sed 's|.*/||; s|\.html$||' | sort)"

if [ -z "$PAGE_LIST" ]; then
	echo "ERROR: no pages found under ${REPO_ROOT}/src" >&2
	exit 1
fi

TOTAL="$(printf '%s\n' "$PAGE_LIST" | wc -l | tr -d ' ')"
echo "Sweeping ${TOTAL} page(s) against ${BASE_URL}"
echo

HDR="$(mktemp "${TMPDIR:-/tmp}/handler-sweep-hdr.XXXXXX")"
trap 'rm -f "$HDR"' EXIT

FAILED=0

for page in $PAGE_LIST; do
	url="${BASE_URL}/${page}.html"

	# ONE request per page. Two requests are two different observations being
	# reported as one, and during a deploy the runtime can genuinely differ
	# between them.
	body="$(curl -sS -D "$HDR" --max-time 30 "$url" 2>/dev/null)" || body=""
	code="$(awk 'tolower($1) ~ /^http/ { c = $2 } END { print c + 0 }' "$HDR")"
	powered="$(awk 'tolower($1) == "x-powered-by:" { $1 = ""; sub(/^ /, ""); sub(/\r$/, ""); print }' "$HDR" | tail -1)"
	if [ -z "$powered" ]; then
		powered="(absent)"
	fi

	# grep -c on a pattern that legitimately matches zero times exits 1, which
	# would abort the loop under a stricter shell and silently skip pages here.
	php_tags="$(printf '%s' "$body" | grep -c '<?php' || true)"
	errs="$(printf '%s' "$body" | grep -cE 'Warning:|Fatal error:|Parse error:' || true)"

	problems=""
	if [ "$code" != "200" ]; then
		problems="${problems} status=${code}"
	fi
	if [ "$php_tags" != "0" ]; then
		problems="${problems} RAW-PHP-SOURCE(${php_tags})"
	fi
	if [ "$errs" != "0" ]; then
		problems="${problems} php-errors(${errs})"
	fi
	case "$powered" in
		PHP/5.2*) problems="${problems} STILL-ON-5.2" ;;
	esac

	if [ -n "$problems" ]; then
		printf 'FAIL  %-28s %-14s %s\n' "$page" "$powered" "$problems"
		FAILED=$((FAILED + 1))
	else
		printf 'ok    %-28s %s\n' "$page" "$powered"
	fi
done

echo
if [ "$FAILED" -eq 0 ]; then
	echo "PASS — ${TOTAL}/${TOTAL} pages: 200, no source leak, no PHP errors, not on 5.2."
	exit 0
fi

echo "FAIL — ${FAILED} of ${TOTAL} page(s) failed at least one assertion." >&2
exit 1
