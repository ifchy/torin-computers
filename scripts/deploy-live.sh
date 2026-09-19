#!/usr/bin/env bash
#
# deploy-live.sh
#
# Uploads ONE named, allowlisted file from site-current/ to public_html/ on
# bell.host.bg over FTPS. public_html/ is the LIVE PRODUCTION ROOT of torin.bg.
#
# This is deliberately a separate script rather than a parameterised
# deploy-new.sh. deploy-new.sh has a no-argument branch that enumerates its
# whole source tree and uploads everything; making its remote root configurable
# would put a full overwrite of the live site one environment variable away from
# an accidental invocation. Guarding that branch with a conditional would be a
# protection that survives only as long as nobody deletes the guard.
#
# The safety here is structural instead of advisory:
#
#   1. There is no source-tree enumeration code in this file at all. No
#      no-argument branch, no recursion, no glob expansion over a directory.
#      Zero arguments is a hard error. A whole-directory upload is therefore not
#      discouraged, it is unexpressible.
#   2. Every argument must equal an entry in the hardcoded allowlist below by
#      exact string comparison -- not prefix, not glob, not a case pattern.
#      Every entry is a bare filename containing no separator, so an argument
#      carrying a slash, a "..", or a leading slash cannot match any of them and
#      path escape is impossible by construction.
#   3. TORIN_LIVE_DEPLOY_CONFIRM=1 must be set. A live-root write earns one
#      deliberate speed bump, so an invocation recalled from shell history or
#      produced by tab completion is a no-op.
#   4. No flag is passed that would let curl auto-create remote directories.
#      Nothing needs creating at the root, and omitting it means a mistyped
#      remote path fails loudly instead of quietly leaving a stray directory on
#      the production host. (The literal flag name is deliberately not written
#      anywhere in this file, so a grep for its absence cannot be satisfied by
#      a comment -- this project has been bitten three times by checks that
#      counted prose.)
#
# No deploy-time transform of any kind is applied: the bytes on disk are the
# bytes on the wire. (deploy-new.sh percent-encodes remote paths because its
# tree contains a filename with a literal space; every name this script will
# accept is a bare ASCII filename, so there is nothing to encode.)
#
# Credentials are read at runtime from filezilla-server-data.xml (gitignored,
# project root). The password is base64-decoded entirely inside a short-lived
# Python process and written straight into a chmod-600 .netrc-style temp file
# that curl consumes via --netrc-file -- it is never printed, never placed in
# a shell variable, and never appears on any command line (so it cannot show
# up in `ps aux`). The temp file is removed via a trap on exit, including on
# error. This is the handling scripts/backup-live-site.sh established.
#
# Run scripts/backup-live-site.sh first. The snapshot it produces is the
# rollback anchor for anything this script overwrites.
#
# Usage:
#   TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php
#
# From a git worktree (which has no copy of the gitignored credentials file):
#   TORIN_CRED_FILE=/path/to/primary/filezilla-server-data.xml \
#     TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
CRED_FILE="${TORIN_CRED_FILE:-${REPO_ROOT}/filezilla-server-data.xml}"
SERVER_NAME="TORIN"
SRC_ROOT="${REPO_ROOT}/site-current"
REMOTE_ROOT="public_html"

# The complete set of filenames this script may ever write to the live root.
# Bare names only -- see safety property 2 above. Adding an entry is a
# deliberate, reviewable act.
ALLOWED_FILES=(
  mailer.php
)

allowlist_as_text() {
  printf '%s ' "${ALLOWED_FILES[@]}"
}

# --- Safety gate 1: at least one argument, and no directory semantics. -------
if [ "$#" -eq 0 ]; then
  echo "ERROR: no file named." >&2
  echo "This script uploads one allowlisted file at a time to the LIVE root; it has no" >&2
  echo "whole-directory mode by design." >&2
  echo "Allowed: $(allowlist_as_text)" >&2
  echo "Usage: TORIN_LIVE_DEPLOY_CONFIRM=1 ${0} mailer.php" >&2
  exit 2
fi

# --- Safety gate 2: exact-match allowlist. -----------------------------------
for arg in "$@"; do
  matched=0
  for allowed in "${ALLOWED_FILES[@]}"; do
    if [ "$arg" = "$allowed" ]; then
      matched=1
      break
    fi
  done
  if [ "$matched" -ne 1 ]; then
    echo "ERROR: '${arg}' is not on the live-deploy allowlist." >&2
    echo "Allowed: $(allowlist_as_text)" >&2
    exit 3
  fi
done

# --- Safety gate 3: explicit confirmation for a live-root write. -------------
if [ "${TORIN_LIVE_DEPLOY_CONFIRM:-}" != "1" ]; then
  echo "REFUSING: this writes to the LIVE production root (${REMOTE_ROOT}/) on torin.bg." >&2
  echo "Would have uploaded: $*" >&2
  echo "Re-run with TORIN_LIVE_DEPLOY_CONFIRM=1 to proceed." >&2
  exit 4
fi

if [ ! -f "$CRED_FILE" ]; then
  echo "ERROR: credentials file not found at ${CRED_FILE}" >&2
  echo "It is gitignored and exists only in the primary checkout; set TORIN_CRED_FILE to its path." >&2
  exit 1
fi

if ! command -v python3 >/dev/null 2>&1; then
  echo "ERROR: python3 is required to parse ${CRED_FILE} but was not found on PATH" >&2
  exit 1
fi

NETRC_FILE=$(mktemp "${TMPDIR:-/tmp}/deploy-live-netrc.XXXXXX")
chmod 600 "$NETRC_FILE"
cleanup() {
  rm -f "$NETRC_FILE"
}
trap cleanup EXIT

# Decode the named FileZilla server entry's Host/User/Pass and write a
# .netrc-style file directly from Python -- the plaintext password never
# transits back into this shell's variable space or stdout.
FTP_HOST=$(python3 - "$CRED_FILE" "$SERVER_NAME" "$NETRC_FILE" <<'PYEOF'
import sys
import base64
import xml.etree.ElementTree as ET

cred_file, server_name, netrc_path = sys.argv[1], sys.argv[2], sys.argv[3]
tree = ET.parse(cred_file)

for server in tree.iter("Server"):
    name_el = server.find("Name")
    if name_el is None or name_el.text != server_name:
        continue
    host = server.find("Host").text
    user = server.find("User").text
    pass_el = server.find("Pass")
    raw = pass_el.text or ""
    if pass_el.get("encoding") == "base64":
        password = base64.b64decode(raw).decode("utf-8")
    else:
        password = raw
    with open(netrc_path, "w") as f:
        f.write("machine %s\n" % host)
        f.write("login %s\n" % user)
        f.write("password %s\n" % password)
    print(host)  # only the hostname is non-secret; safe to return to the shell
    sys.exit(0)

sys.stderr.write("ERROR: server entry '%s' not found in %s\n" % (server_name, cred_file))
sys.exit(1)
PYEOF
)

if [ -z "${FTP_HOST:-}" ]; then
  echo "ERROR: failed to resolve FTP credentials for server '${SERVER_NAME}' from ${CRED_FILE}" >&2
  exit 1
fi

# Same transport rationale as backup-live-site.sh and deploy-new.sh: the
# shared-hosting wildcard cert (*.superhosting.bg) does not cover the vanity
# hostname bell.host.bg, so -k is required -- but -k disables the ENTIRE
# verification chain (hostname, CA trust, expiry), so --pinnedpubkey
# cryptographically pins this specific host's public key to compensate. Only a
# peer holding the exact matching private key is accepted. Update the pin if the
# certificate is ever reissued with a new key; recompute via:
#   echo | openssl s_client -connect bell.host.bg:21 -starttls ftp 2>/dev/null | \
#     openssl x509 -pubkey -noout | openssl pkey -pubin -outform der | \
#     openssl dgst -sha256 -binary | base64
FTP_HOST_PUBKEY_PIN="sha256//Z7N5Hk+6AzND7F/ToDmzG91E2tHDk6WVlyWLfDqXcRU="

# --tls-max 1.2 is load-bearing, not a precaution. Discovered live 2026-08-06:
# with a TLS 1.3 data connection this host aborts every upload larger than a
# single ~16 KB TLS record with "451 Error during read from data connection",
# *after* curl reports the bytes fully sent -- so small text files appear to
# succeed while every binary silently fails. Capping the data channel at TLS 1.2
# fixes it while keeping the transport fully encrypted and the public key
# pinned; do NOT "fix" this instead by dropping to --ftp-ssl-control, which
# would put file bytes on the wire in the clear (T-02-06).
#
# Note what is NOT in this flag list: anything that would let curl create a
# remote directory. See safety property 4 above.
CURL_BASE=(curl --fail --silent --show-error --netrc-file "$NETRC_FILE"
           --ftp-ssl -k --pinnedpubkey "$FTP_HOST_PUBKEY_PIN" --tls-max 1.2)

UPLOAD_FAILED=0
for name in "$@"; do
  local_path="${SRC_ROOT}/${name}"
  if [ ! -f "$local_path" ]; then
    echo "ERROR: local file not found: ${local_path}" >&2
    UPLOAD_FAILED=1
    continue
  fi
  dest_url="ftp://${FTP_HOST}/${REMOTE_ROOT}/${name}"
  printf 'uploading %s (%s bytes) -> %s\n' \
    "$local_path" "$(wc -c < "$local_path" | tr -d ' ')" "$dest_url"
  if ! "${CURL_BASE[@]}" -T "$local_path" "$dest_url"; then
    echo "ERROR: failed to upload ${name}" >&2
    UPLOAD_FAILED=1
  fi
done

if [ "$UPLOAD_FAILED" -ne 0 ]; then
  echo "ERROR: one or more uploads failed -- not reporting success" >&2
  exit 1
fi

SNAPSHOT_HINT="backups/<newest-timestamp>"
if [ -d "${REPO_ROOT}/backups" ]; then
  newest=$(ls -1t "${REPO_ROOT}/backups" 2>/dev/null | head -1 || true)
  if [ -n "${newest:-}" ]; then
    SNAPSHOT_HINT="backups/${newest}"
  fi
fi

echo "Deploy complete: $* -> ${REMOTE_ROOT}/ (LIVE)"
echo "Rollback: cp ${SNAPSHOT_HINT}/public_html/<name> site-current/<name> && TORIN_LIVE_DEPLOY_CONFIRM=1 ${0} <name>"
