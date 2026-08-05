#!/usr/bin/env bash
#
# deploy-new.sh
#
# Uploads files from src/ to public_html/new/ on bell.host.bg over FTPS.
# public_html/new/ is the Phase 1 staging subtree; this script deliberately
# refuses to write anywhere else, so a stray argument cannot touch the live
# site. Run scripts/backup-live-site.sh before the first upload of a phase
# (MIGR-03).
#
# Credentials are read at runtime from filezilla-server-data.xml (gitignored,
# project root). The password is base64-decoded entirely inside a short-lived
# Python process and written straight into a chmod-600 .netrc-style temp file
# that curl consumes via --netrc-file -- it is never printed, never placed in
# a shell variable, and never appears on any command line (so it cannot show
# up in `ps aux`). The temp file is removed via a trap on exit, including on
# error. This is the same handling scripts/backup-live-site.sh established.
#
# Usage:
#   scripts/deploy-new.sh                    # upload every file under src/
#   scripts/deploy-new.sh css/base.css ...   # upload only the named paths
#                                            # (each relative to src/)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
CRED_FILE="${REPO_ROOT}/filezilla-server-data.xml"
SERVER_NAME="TORIN"
SRC_ROOT="${REPO_ROOT}/src"
REMOTE_ROOT="public_html/new"

if [ ! -f "$CRED_FILE" ]; then
  echo "ERROR: credentials file not found at ${CRED_FILE}" >&2
  exit 1
fi

if ! command -v python3 >/dev/null 2>&1; then
  echo "ERROR: python3 is required to parse ${CRED_FILE} but was not found on PATH" >&2
  exit 1
fi

NETRC_FILE=$(mktemp "${TMPDIR:-/tmp}/deploy-new-netrc.XXXXXX")
chmod 600 "$NETRC_FILE"
cleanup() {
  rm -f "$NETRC_FILE"
}
trap cleanup EXIT

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

# Same transport rationale as backup-live-site.sh: the shared-hosting wildcard
# cert (*.superhosting.bg) does not cover the vanity hostname bell.host.bg, so
# -k is required -- but -k disables the ENTIRE verification chain, so
# --pinnedpubkey cryptographically pins this specific host's public key to
# compensate. Update the pin if the certificate is ever reissued with a new key.
FTP_HOST_PUBKEY_PIN="sha256//Z7N5Hk+6AzND7F/ToDmzG91E2tHDk6WVlyWLfDqXcRU="

# --tls-max 1.2 is load-bearing, not a precaution. Discovered live 2026-08-06:
# with a TLS 1.3 data connection this host aborts every upload larger than a
# single ~16 KB TLS record with "451 Error during read from data connection",
# *after* curl reports the bytes fully sent -- so small text files appear to
# succeed while every font/image binary silently fails. Capping the data channel
# at TLS 1.2 fixes it while keeping the transport fully encrypted and the public
# key pinned; do NOT "fix" this instead by dropping to --ftp-ssl-control, which
# would put file bytes on the wire in the clear (T-02-06).
CURL_BASE=(curl --fail --silent --show-error --netrc-file "$NETRC_FILE"
           --ftp-ssl -k --pinnedpubkey "$FTP_HOST_PUBKEY_PIN" --tls-max 1.2
           --ftp-create-dirs)

urlencode_path() {
  python3 -c 'import sys, urllib.parse; print(urllib.parse.quote(sys.argv[1], safe="/"))' "$1"
}

# Build the file list: explicit arguments, or every file under src/.
FILES=()
if [ "$#" -gt 0 ]; then
  FILES=("$@")
else
  while IFS= read -r f; do
    FILES+=("$f")
  done < <(cd "$SRC_ROOT" && find . -type f ! -name '.DS_Store' | sed 's|^\./||' | sort)
fi

if [ "${#FILES[@]}" -eq 0 ]; then
  echo "ERROR: nothing to upload" >&2
  exit 1
fi

echo "Deploying ${#FILES[@]} file(s) to ftp://${FTP_HOST}/${REMOTE_ROOT}/ ..."

UPLOAD_FAILED=0
for rel in "${FILES[@]}"; do
  # Refuse anything that could escape the staging subtree.
  case "$rel" in
    /*|*..*)
      echo "ERROR: refusing suspicious path '${rel}' (absolute or contains '..')" >&2
      UPLOAD_FAILED=1
      continue
      ;;
  esac

  local_path="${SRC_ROOT}/${rel}"
  if [ ! -f "$local_path" ]; then
    echo "ERROR: local file not found: ${local_path}" >&2
    UPLOAD_FAILED=1
    continue
  fi

  remote_url_path=$(urlencode_path "$rel")
  printf '  uploading %s\n' "$rel"
  if ! "${CURL_BASE[@]}" -T "$local_path" \
       "ftp://${FTP_HOST}/${REMOTE_ROOT}/${remote_url_path}"; then
    echo "ERROR: failed to upload ${rel}" >&2
    UPLOAD_FAILED=1
  fi
done

if [ "$UPLOAD_FAILED" -ne 0 ]; then
  echo "ERROR: one or more uploads failed -- not reporting success" >&2
  exit 1
fi

echo "Deploy complete: ${#FILES[@]} file(s) -> ${REMOTE_ROOT}/"
