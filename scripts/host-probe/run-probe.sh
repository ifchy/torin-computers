#!/usr/bin/env bash
#
# run-probe.sh
#
# Drives the whole lifecycle of the Phase 4 host-capability probe (D4-06):
# generate, materialise, hand the deploy to the developer, read the answer into
# 04-HOST-CAPABILITIES.md, and prove the file is gone again.
#
# WHY THIS SCRIPT EXISTS.
# This project has no local PHP interpreter — that is a structural property, not
# a gap — so the live deploy is the only PHP check that exists (STATE.md, Phase
# 3.5). Six dependencies the rest of Phase 4 is built on (GD, EXIF, cURL,
# outbound 443, a local MTA, and the raised upload limits) cannot be answered
# from the dev machine at all. One request to a throwaway file answers all six.
#
# WHY IT DOES NOT RUN THE DEPLOY ITSELF.
# scripts/deploy-new.sh is denied to subagents by the permission classifier
# (STATE.md, Phase 3 note, plan 03-01); the developer runs it. This script
# therefore PRINTS the exact command rather than invoking it. It is not being
# polite — an agent that shells out to the deploy gets denied mid-run and leaves
# a materialised probe sitting in src/, which is the disclosure this whole
# design exists to prevent.
#
# WHY THE PROBE SOURCE LIVES UNDER scripts/ AND NOT UNDER src/.
# `deploy-new.sh` with no arguments uploads EVERY file under src/
# (scripts/deploy-new.sh:161, pitfall P-10). A file that prints ini values,
# extension lists and filesystem paths must not be one no-argument run away from
# publication. The template lives here; --prepare copies it into src/ under a
# random name for exactly as long as the upload takes, and --read deletes that
# copy as its last act. The name is also gitignored (/src/hc-*.php), so an
# accidental `git add` cannot commit one either.
#
# THE 404 CHECK TAKES THE TOKEN, AND THAT IS NOT AN OVERSIGHT.
# The probe answers a bare 404 to any request without the right token. So a
# tokenless fetch returning 404 proves NOTHING — a live, fully-readable probe
# returns exactly the same thing. --verify-gone therefore fetches WITH the real
# token: only a genuinely deleted file 404s then. A cleanup check that cannot
# distinguish "deleted" from "still there" is worse than no check, because it
# reports success either way.
#
# Usage:
#   scripts/host-probe/run-probe.sh --prepare
#       Generate a 32-hex token and an unguessable filename, write the probe
#       into src/, and print the two commands to run next.
#
#   scripts/host-probe/run-probe.sh --read <filename> <token>
#       Fetch the deployed probe, append its body plus the command and date to
#       .planning/phases/04-hardening-cutover/04-HOST-CAPABILITIES.md, and
#       remove the local copy from src/.
#
#   scripts/host-probe/run-probe.sh --verify-gone <filename> <token>
#       Fetch the probe URL WITH its token and assert 404. Appends the result
#       and the command to 04-HOST-CAPABILITIES.md. Exits non-zero on anything
#       other than 404 — the probe is still reachable and must be deleted.
#
# Deleting the remote file is a manual step: deploy-new.sh uploads and never
# deletes, and no script in this project can delete a remote file (STATE.md,
# Phase 3.5 cutover note). Use cPanel File Manager or FileZilla.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/../.." && pwd)"
SRC_ROOT="${REPO_ROOT}/src"
TEMPLATE="${SCRIPT_DIR}/probe.php.tpl"
CAPABILITIES="${REPO_ROOT}/.planning/phases/04-hardening-cutover/04-HOST-CAPABILITIES.md"

# The staging subtree, matching deploy-new.sh's hardcoded REMOTE_ROOT. The probe
# is never deployed to the live root: /new/ is where D4-02 confines the blast
# radius of everything in this plan.
BASE_URL="https://torin.bg/new"

die() { echo "ERROR: $*" >&2; exit 1; }

usage() {
	cat >&2 <<'EOF'
run-probe.sh — Phase 4 host-capability probe (D4-06)

  --prepare                          generate token + filename, write src/hc-<hex>.php
  --read <filename> <token>          fetch it, record the body, delete the local copy
  --verify-gone <filename> <token>   assert the deployed probe now 404s

Between --prepare and --read the developer runs, by hand:

  scripts/deploy-new.sh <filename>

That step is not automated: deploy-new.sh is denied to subagents by the
permission classifier (STATE.md, Phase 3).
EOF
	exit 64
}

[ -f "$TEMPLATE" ] || die "probe template not found at ${TEMPLATE}"

MODE="${1:---help}"

case "$MODE" in

--prepare)
	command -v openssl >/dev/null 2>&1 || die "openssl is required to generate the probe token"

	# 32 hex characters, as C-1's gate expects. Not a timestamp, not a hash of
	# anything guessable — the token is the only access control the probe has.
	TOKEN="$(openssl rand -hex 16)"
	# The filename is a second, independent barrier: even an attacker who somehow
	# learns the token still has to find the file. "hc" = host capabilities; the
	# literal string "probe" is deliberately absent from the deployed name so a
	# directory guess against the obvious word finds nothing.
	NAME="hc-$(openssl rand -hex 16).php"
	TARGET="${SRC_ROOT}/${NAME}"

	[ -d "$SRC_ROOT" ] || die "src/ not found at ${SRC_ROOT}"

	# sed, not a shell expansion, so the token cannot be re-quoted or truncated by
	# anything in the template body.
	sed "s/REPLACE_WITH_RANDOM_32_CHARS/${TOKEN}/" "$TEMPLATE" > "$TARGET"

	# Written as an `if`, not as `grep -q ... && die`: under `set -e` an AND-list
	# whose left side fails is itself a failing command, so the success path (no
	# placeholder left) would abort the script.
	if grep -q "REPLACE_WITH_RANDOM_32_CHARS" "$TARGET"; then
		rm -f "$TARGET"
		die "token substitution failed — removed the ungated probe rather than leave it in src/"
	fi

	cat <<EOF

Probe materialised: src/${NAME}
Token:              ${TOKEN}

It is gitignored (/src/hc-*.php) and will be deleted from src/ by --read.
Until then, do NOT run scripts/deploy-new.sh with no arguments: that uploads
everything under src/, this file included, and the point of the explicit path
below is that nothing else goes up with it.

Run these two, in order:

  scripts/deploy-new.sh ${NAME}

  scripts/host-probe/run-probe.sh --read ${NAME} ${TOKEN}

EOF
	;;

--read)
	NAME="${2:-}"
	TOKEN="${3:-}"
	[ -n "$NAME" ] && [ -n "$TOKEN" ] || die "usage: run-probe.sh --read <filename> <token>"

	URL="${BASE_URL}/${NAME}?k=${TOKEN}"
	echo "Fetching ${BASE_URL}/${NAME}?k=<token> ..."

	HTTP_CODE="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 "$URL" || echo "000")"
	[ "$HTTP_CODE" = "200" ] || die "probe returned HTTP ${HTTP_CODE}, not 200 — deployed? token correct?"

	BODY="$(curl -s --max-time 30 "$URL")"

	# The body must look like the probe's own output and not like a PHP source
	# listing. On a host that serves .php as text (the exact failure mode plan
	# 04-01 exists to prevent for .html) a 200 tells you nothing.
	case "$BODY" in
		*"<?php"*) die "response body contains raw PHP source — the host is NOT executing this file" ;;
	esac
	printf '%s' "$BODY" | grep -q '^version *:' \
		|| die "response body does not look like probe output — refusing to record it"

	TODAY="$(date +%Y-%m-%d)"
	mkdir -p "$(dirname "$CAPABILITIES")"

	if [ ! -f "$CAPABILITIES" ]; then
		cat > "$CAPABILITIES" <<EOF
# Phase 4 — Measured host capabilities (bell.host.bg, public_html/new/)

Every figure below was read out of a live response body. The command that
produced it and the date it was produced are recorded alongside it, following
the evidence discipline 03.5-TRUTH-AUDIT.md established.

**Anything not read out of a response body is an assumption and is labelled
one.** There are no unmarked inferences in this file.

The probe token is redacted from the recorded commands: it gated a file that no
longer exists, but writing a live credential into a committed artefact is a
habit worth not having.

EOF
	fi

	{
		echo ""
		echo "## Probe run — ${TODAY}"
		echo ""
		echo "Command:"
		echo ""
		echo '```'
		echo "curl -s '${BASE_URL}/${NAME}?k=<32-hex-token>'"
		echo '```'
		echo ""
		echo "Response body, verbatim:"
		echo ""
		echo '```'
		printf '%s\n' "$BODY"
		echo '```'
	} >> "$CAPABILITIES"

	echo "Recorded in ${CAPABILITIES#${REPO_ROOT}/}"

	# Last act: remove the local copy, closing the no-argument-deploy window.
	rm -f "${SRC_ROOT}/${NAME}"
	echo "Removed local src/${NAME}"

	cat <<EOF

NOW DELETE IT FROM THE SERVER. This is manual — deploy-new.sh uploads and never
deletes, and nothing in this repo can delete a remote file.

  cPanel -> File Manager -> public_html/new/ -> ${NAME} -> Delete
  (or FileZilla: connect, open public_html/new/, delete ${NAME})

Then prove it:

  scripts/host-probe/run-probe.sh --verify-gone ${NAME} ${TOKEN}

EOF
	;;

--verify-gone)
	NAME="${2:-}"
	TOKEN="${3:-}"
	[ -n "$NAME" ] && [ -n "$TOKEN" ] || die "usage: run-probe.sh --verify-gone <filename> <token>"

	URL="${BASE_URL}/${NAME}?k=${TOKEN}"
	HTTP_CODE="$(curl -s -o /dev/null -w '%{http_code}' --max-time 30 "$URL" || echo "000")"
	TODAY="$(date +%Y-%m-%d)"

	{
		echo ""
		echo "## Probe cleanup — ${TODAY}"
		echo ""
		echo "Fetched WITH the valid token, because a tokenless 404 is what a LIVE"
		echo "probe returns too and would prove nothing:"
		echo ""
		echo '```'
		echo "curl -s -o /dev/null -w '%{http_code}' '${BASE_URL}/${NAME}?k=<32-hex-token>'"
		echo "${HTTP_CODE}"
		echo '```'
	} >> "$CAPABILITIES"

	if [ "$HTTP_CODE" = "404" ]; then
		echo "OK: ${NAME} returns 404 with a valid token — the probe is gone."
	else
		echo "STILL REACHABLE: ${NAME} returned HTTP ${HTTP_CODE} with a valid token." >&2
		echo "Delete it from public_html/new/ and run this again." >&2
		exit 1
	fi
	;;

*)
	usage
	;;
esac
