#!/usr/bin/env bash
#
# assert-capabilities.sh — the Step B precondition for plan 04-01 Task 3.
#
# WHY THIS EXISTS.
# The 8.5 probe run of 2026-09-19 came back with SEVEN of nine extensions
# missing. Nothing in the repo would have objected: the handler sweep reads
# rendered pages, and the 19 pages call no function from any missing extension,
# so they render perfectly on a runtime that cannot resize an image, read EXIF,
# sniff a MIME type or open an HTTPS connection. The sweep would have reported
# PASS 19/19 and the phase would have carried a silently crippled runtime into
# 04-02's tracer. "Extensions are back" therefore has to be CHECKED, not read
# off a pasted body by eye — hence a script.
#
# It asserts over a probe body, not over a live host, because the probe is
# one-shot and self-deleting: the body is the artefact that outlives it.
#
# Usage:
#   scripts/host-probe/assert-capabilities.sh <file-containing-probe-body>
#   scripts/host-probe/run-probe.sh --read <f> <t> | tee /dev/tty | scripts/host-probe/assert-capabilities.sh -
#
# Exit 0 only if every required capability is present. Anything else is a
# blocker for Step B.

set -uo pipefail

SRC="${1:--}"

if [ "$SRC" = "-" ]; then
	BODY="$(cat)"
elif [ -f "$SRC" ]; then
	BODY="$(cat "$SRC")"
else
	echo "ERROR: no such file: ${SRC}" >&2
	echo "usage: assert-capabilities.sh <probe-body-file> | -" >&2
	exit 64
fi

if ! printf '%s' "$BODY" | grep -q '^version *:'; then
	echo "ERROR: that does not look like a probe body (no 'version :' line)." >&2
	exit 64
fi

# Extract the value for a probe key.
#
# The obvious implementation — strip up to the first colon — is WRONG on this
# body and was caught by running this script against a real one before trusting
# it. Probe keys contain colons themselves ("ext:openssl", "outbound:curl443"),
# so "up to the first colon" leaves "openssl          : yes" as the value, and
# every present extension then compares unequal to "yes" and reports as a
# BLOCKER. The first run of this script announced that openssl, hash and filter
# were missing while the body plainly said they were there. A checker that
# invents failures is no better than one that misses them.
#
# So: strip the key literally, then the separator, then the padding.
field() {
	local line
	line="$(printf '%s' "$BODY" | grep "^$1 *:" | head -1)"
	[ -n "$line" ] || return 0
	line="${line#"$1"}"
	line="${line#*:}"
	printf '%s' "$line" | sed 's/^[[:space:]]*//; s/[[:space:]]*$//'
}

FAILED=0
note() {
	printf '  %-6s %-22s %s\n' "$1" "$2" "$3"
}

echo "Step B precondition check"
echo

# ---------------------------------------------------------------------------
# 1. Runtime identity.
# ---------------------------------------------------------------------------
VERSION="$(field version)"
SAPI="$(field sapi)"

case "$VERSION" in
	8.*) note "ok" "version" "$VERSION" ;;
	*)   note "FAIL" "version" "$VERSION (expected 8.x — is this a stale 5.2 body?)"; FAILED=$((FAILED + 1)) ;;
esac

case "$SAPI" in
	cgi-fcgi) note "ok" "sapi" "$SAPI" ;;
	*)        note "WARN" "sapi" "$SAPI (expected cgi-fcgi)" ;;
esac

echo

# ---------------------------------------------------------------------------
# 2. Extensions. Each is named with what it blocks, so a failure reports the
#    consequence rather than just the absence.
# ---------------------------------------------------------------------------
check_ext() {
	local ext="$1" why="$2" val
	val="$(field "ext:${ext}")"
	if [ "$val" = "yes" ]; then
		note "ok" "ext:${ext}" "$why"
	else
		note "FAIL" "ext:${ext}" "${val:-absent} — blocks ${why}"
		FAILED=$((FAILED + 1))
	fi
}

check_ext gd       "04-03 photo resizing (D4-13)"
check_ext exif     "04-03 EXIF orientation (EA-07)"
check_ext fileinfo "04-03/04-05 upload MIME validation — a security control"
check_ext curl     "04-02 tracer + 04-05 Telegram (D4-05)"
check_ext openssl  "HTTPS from PHP, and PHPMailer TLS (D4-11)"
check_ext mbstring "multibyte-safe string handling for Bulgarian text"
check_ext hash     "spam-guard timestamp signing (04-05)"
check_ext ctype    "input validation helpers (04-05)"
check_ext filter   "filter_var email validation (04-05)"

echo

# ---------------------------------------------------------------------------
# 3. Outbound 443 — the D4-06 gate. EITHER path answering OK is sufficient:
#    the question is whether the host can reach the internet, not which
#    extension carries the request. Tying it to cURL alone is what let a missing
#    extension turn a measured answer back into an unknown on 2026-09-19.
# ---------------------------------------------------------------------------
CURL443="$(field 'outbound:curl443')"
FOPEN443="$(field 'outbound:fopen443')"

OUT_OK=0
case "$CURL443" in OK*) OUT_OK=1 ;; esac
case "$FOPEN443" in OK*) OUT_OK=1 ;; esac

if [ "$OUT_OK" = "1" ]; then
	note "ok" "outbound:443" "curl=[${CURL443:-absent}] fopen=[${FOPEN443:-absent}]"
else
	note "FAIL" "outbound:443" "curl=[${CURL443:-absent}] fopen=[${FOPEN443:-absent}] — D4-06 unanswered, D4-05 cannot be committed to"
	FAILED=$((FAILED + 1))
fi

# ---------------------------------------------------------------------------
# 4. Hygiene: the probe should have removed itself.
#
# This is CORROBORATION, not the gate. The gate is run-probe.sh --read's own
# authenticated 404 assertion, which exits non-zero and lives outside this body.
# So an ABSENT selfdelete line is a warning, not a failure: it means the probe
# that ran predates the self-deleting build, which is a real and expected
# situation whenever the probe is driven from a checkout that does not carry
# these commits. Treating absence as failure would cry wolf; treating it as
# success would defeat the check. A line that is PRESENT and not OK is a genuine
# blocker — the probe tried to delete itself and could not.
# ---------------------------------------------------------------------------
SELFDEL="$(field selfdelete)"
if [ "$SELFDEL" = "OK" ]; then
	note "ok" "selfdelete" "probe removed itself"
elif [ -z "$SELFDEL" ]; then
	note "WARN" "selfdelete" "absent — probe build predates self-deletion; confirm --verify-gone returned 404"
else
	note "FAIL" "selfdelete" "${SELFDEL} — a live probe is an environment disclosure (T-04-01)"
	FAILED=$((FAILED + 1))
fi

echo
if [ "$FAILED" -eq 0 ]; then
	echo "PASS — Step B may proceed."
	exit 0
fi

echo "FAIL — ${FAILED} blocker(s). Step B must NOT proceed." >&2
exit 1
