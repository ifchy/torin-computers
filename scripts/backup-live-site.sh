#!/usr/bin/env bash
#
# backup-live-site.sh
#
# Pulls a full local snapshot of the currently-live torin.bg site
# (bell.host.bg, public_html/) into backups/<UTC-timestamp>/public_html/
# before any FTP upload/deploy touches the live host. Implements MIGR-03's
# pre-deploy backup discipline.
#
# Credentials are read at runtime from filezilla-server-data.xml (gitignored,
# project root). The password is base64-decoded entirely inside a short-lived
# Python process and written straight into a chmod-600 .netrc-style temp file
# that curl consumes via --netrc-file -- it is never printed, never placed in
# a shell variable, and never appears on any command line (so it cannot show
# up in `ps aux`). The temp file is removed via a trap on exit, including on
# error.
#
# Usage: scripts/backup-live-site.sh

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
CRED_FILE="${REPO_ROOT}/filezilla-server-data.xml"
SERVER_NAME="TORIN"
REMOTE_ROOT="public_html"

if [ ! -f "$CRED_FILE" ]; then
  echo "ERROR: credentials file not found at ${CRED_FILE}" >&2
  exit 1
fi

if ! command -v python3 >/dev/null 2>&1; then
  echo "ERROR: python3 is required to parse ${CRED_FILE} but was not found on PATH" >&2
  exit 1
fi

NETRC_FILE=$(mktemp "${TMPDIR:-/tmp}/backup-live-site-netrc.XXXXXX")
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

TIMESTAMP=$(date -u +"%Y%m%dT%H%M%SZ")
BACKUP_ROOT="${REPO_ROOT}/backups/${TIMESTAMP}/public_html"
mkdir -p "$BACKUP_ROOT"

CURL_BASE=(curl --fail --silent --show-error --netrc-file "$NETRC_FILE")

# Prefer FTPS (explicit TLS), matching the pattern confirmed live against
# bell.host.bg in plan 01-01. The certificate chain itself is validly signed
# by a public CA (Sectigo) -- it is NOT self-signed or untrusted -- but the
# shared-hosting wildcard cert (*.superhosting.bg) doesn't cover the vanity
# hostname bell.host.bg, so hostname verification fails. `-k`/--insecure
# disables the ENTIRE verification chain (hostname, CA trust, expiry), not
# just hostname checking, which alone would leave an active MITM presenting
# any other cert accepted silently. To compensate, --pinnedpubkey cryptographically
# pins to this specific host's known public key, independent of and in addition to
# whatever -k skips: only a peer holding the exact matching private key is accepted.
# NOTE: this pin will need updating if bell.host.bg's certificate is ever reissued
# with a new key (e.g. cert renewal/rotation) -- recompute via:
#   echo | openssl s_client -connect bell.host.bg:21 -starttls ftp 2>/dev/null | \
#     openssl x509 -pubkey -noout | openssl pkey -pubin -outform der | \
#     openssl dgst -sha256 -binary | base64
FTP_HOST_PUBKEY_PIN="sha256//Z7N5Hk+6AzND7F/ToDmzG91E2tHDk6WVlyWLfDqXcRU="
PROTO_FLAGS=(--ftp-ssl -k --pinnedpubkey "$FTP_HOST_PUBKEY_PIN")
if ! "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" --list-only "ftp://${FTP_HOST}/${REMOTE_ROOT}/" >/dev/null 2>&1; then
  echo "ERROR: FTPS probe failed against ${FTP_HOST} (TLS handshake, or pubkey pin mismatch -- possible MITM or cert rotation)." >&2
  echo "Refusing to fall back to plaintext FTP with live credentials." >&2
  if [ "${BACKUP_ALLOW_PLAINTEXT_FTP:-0}" = "1" ]; then
    echo "BACKUP_ALLOW_PLAINTEXT_FTP=1 set -- proceeding over unencrypted plain FTP as explicitly requested." >&2
    PROTO_FLAGS=()
    if ! "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" --list-only "ftp://${FTP_HOST}/${REMOTE_ROOT}/" >/dev/null 2>&1; then
      echo "ERROR: could not connect to ftp://${FTP_HOST}/${REMOTE_ROOT}/ over plain FTP either." >&2
      exit 1
    fi
  else
    echo "If the pin failure is due to an expected cert rotation, recompute FTP_HOST_PUBKEY_PIN (see comment above)." >&2
    echo "To explicitly accept the plaintext-FTP risk instead, re-run with BACKUP_ALLOW_PLAINTEXT_FTP=1." >&2
    exit 1
  fi
fi

urlencode_path() {
  # Percent-encode a remote path for safe inclusion in a curl ftp:// URL,
  # keeping "/" as a literal path separator. Needed because the live tree
  # contains at least one filename with a literal space
  # (assets1/img/Preloader-icon ORI.gif), which curl otherwise rejects as a
  # malformed URL.
  python3 -c 'import sys, urllib.parse; print(urllib.parse.quote(sys.argv[1], safe="/"))' "$1"
}

download() {
  # download <remote-relative-path> <local-path>
  local remote="$1" local_path="$2" remote_url_path
  remote_url_path=$(urlencode_path "$remote")
  mkdir -p "$(dirname "$local_path")"
  if ! "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" "ftp://${FTP_HOST}/${REMOTE_ROOT}/${remote_url_path}" -o "$local_path"; then
    echo "ERROR: failed to download ${remote}" >&2
    return 1
  fi
}

list_dir() {
  # list_dir <remote-relative-dir-with-trailing-slash>
  # Uses FTP's full LIST format (Unix `ls -l`-style, first column encodes
  # entry type) rather than NLST -- names alone are not a reliable signal of
  # file-vs-directory on this host (e.g. assets1/vendors/jqury.mb.YTPlayer is
  # a directory despite containing dots).
  local remote="$1" remote_url_path
  remote_url_path=$(urlencode_path "$remote")
  "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" "ftp://${FTP_HOST}/${REMOTE_ROOT}/${remote_url_path}" | tr -d '\r'
}

# The 16 live pages (verified inventory, 01-URL-INVENTORY.md / 01-RESEARCH.md).
PAGES=(
  index.html about.html covid.html laptopi.html mehanichni-problemi.html
  msg.html optimizatsiq.html problem-stari.html profilaktika-laptop.html
  rezervni-chasti.html test-laptop.html tokov-udar.html uslovia.html
  warrently.html za-bateriite.html zalivane-technosti.html
)

# The 7 must-carry non-page root files (01-RESEARCH.md "Code Examples").
MUST_CARRY_FILES=(
  .htaccess favicon.ico google1718743335455f1c.html header.js otpuska.js mailer.php error_log
)

# The 4 must-carry root directories, recursed without a fixed-depth assumption.
MUST_CARRY_DIRS=(
  .well-known/ cgi-bin/ covid-19/ assets1/
)

echo "Backing up ftp://${FTP_HOST}/${REMOTE_ROOT}/ to ${BACKUP_ROOT} ..."

DOWNLOAD_FAILED=0

for f in "${PAGES[@]}" "${MUST_CARRY_FILES[@]}"; do
  echo "  downloading ${f}"
  if ! download "$f" "${BACKUP_ROOT}/${f}"; then
    DOWNLOAD_FAILED=1
  fi
done

if [ "$DOWNLOAD_FAILED" -ne 0 ]; then
  echo "ERROR: one or more required root files failed to download -- aborting backup, not reporting success" >&2
  exit 1
fi

# Recurse into a directory's LIST output, bounded to whatever entries the
# server actually reports (no fixed-depth assumption). Directory vs. file is
# read directly from the LIST type column ('d' vs '-'), not guessed from the
# entry name.
recurse_dir() {
  local remote_dir="$1"
  local entries
  if ! entries=$(list_dir "$remote_dir"); then
    echo "ERROR: failed to list ${remote_dir}" >&2
    return 1
  fi
  local line type_char name remote_entry
  while IFS= read -r line; do
    [ -z "$line" ] && continue
    type_char="${line:0:1}"
    name=$(awk '{ $1=$2=$3=$4=$5=$6=$7=$8=""; sub(/^[ \t]+/, ""); print }' <<< "$line")
    [ -z "$name" ] && continue
    [ "$name" = "." ] && continue
    [ "$name" = ".." ] && continue
    case "$name" in
      */*|*..*)
        echo "ERROR: suspicious entry name '${name}' in ${remote_dir} (contains '/' or '..') -- skipping, not writing outside the backup root" >&2
        continue
        ;;
    esac
    remote_entry="${remote_dir}${name}"
    if [ "$type_char" = "d" ]; then
      if ! recurse_dir "${remote_entry}/"; then
        return 1
      fi
    else
      if ! download "$remote_entry" "${BACKUP_ROOT}/${remote_entry}"; then
        return 1
      fi
    fi
  done <<< "$entries"
}

for d in "${MUST_CARRY_DIRS[@]}"; do
  echo "  recursing into ${d}"
  if ! recurse_dir "$d"; then
    echo "ERROR: failed to fully mirror ${d} -- aborting backup, not reporting success" >&2
    exit 1
  fi
done

# --- Completeness verification (must not silently report success on a partial pull) ---

HTML_COUNT=0
for p in "${PAGES[@]}"; do
  if [ -s "${BACKUP_ROOT}/${p}" ]; then
    HTML_COUNT=$((HTML_COUNT + 1))
  fi
done

if [ "$HTML_COUNT" -ne 16 ]; then
  echo "ERROR: expected 16 known .html pages present and non-empty, found ${HTML_COUNT}" >&2
  exit 1
fi

ASSETS_SIZE_KB=$(du -sk "${BACKUP_ROOT}/assets1" 2>/dev/null | cut -f1)
ASSETS_SIZE_KB=${ASSETS_SIZE_KB:-0}
ASSETS_BASELINE_KB=14000
ASSETS_MIN_KB=10000  # "well under" threshold -- strong signal of a truncated pull

if [ "$ASSETS_SIZE_KB" -lt "$ASSETS_MIN_KB" ]; then
  echo "ERROR: assets1/ total size (${ASSETS_SIZE_KB}KB) is well under the ~${ASSETS_BASELINE_KB}KB baseline -- likely a truncated pull" >&2
  exit 1
fi

echo "Backup complete: ${BACKUP_ROOT}"
echo "  .html pages verified: ${HTML_COUNT}/16"
echo "  must-carry root files: ${#MUST_CARRY_FILES[@]}/${#MUST_CARRY_FILES[@]}"
echo "  must-carry directories mirrored: ${MUST_CARRY_DIRS[*]}"
echo "  assets1/ size: ${ASSETS_SIZE_KB}KB (baseline ~${ASSETS_BASELINE_KB}KB)"
