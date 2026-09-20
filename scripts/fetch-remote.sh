#!/usr/bin/env bash
#
# fetch-remote.sh
#
# Prints ONE remote file from bell.host.bg to stdout, over FTPS. Read-only:
# it has no upload path, no delete path and no argument that writes anything.
#
# WHY THIS EXISTS RATHER THAN A PHP PROBE. Plan 04-03 has to read the host's
# error_log to prove that no visitor photograph survives a request. The
# obvious route — a token-gated PHP reader in the document root — is the exact
# liability Pitfall P-10 describes, on the one endpoint this phase spends its
# whole threat model hardening, and it has to be deployed, fetched and deleted
# without anything going wrong in between. This reads the file over the
# credentialled channel that already exists, and puts NOTHING on the server.
#
# Credentials are handled exactly as scripts/backup-live-site.sh and
# scripts/deploy-new.sh handle them: decoded inside a short-lived Python
# process straight into a chmod-600 .netrc-style temp file that curl consumes
# via --netrc-file. The password never enters a shell variable and never
# appears on a command line, so it cannot surface in `ps aux`. The temp file
# is removed by an EXIT trap, including on error.
#
# The credentials file is gitignored and therefore absent from a git worktree;
# TORIN_CRED_FILE overrides the path, carrying a PATH and never a password.
#
# Usage:
#   scripts/fetch-remote.sh public_html/error_log
#   scripts/fetch-remote.sh public_html/new/.user.ini

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
CRED_FILE="${TORIN_CRED_FILE:-${REPO_ROOT}/filezilla-server-data.xml}"
SERVER_NAME="TORIN"

REMOTE_PATH="${1:-}"
if [ -z "$REMOTE_PATH" ]; then
  echo "usage: scripts/fetch-remote.sh <remote-path-relative-to-ftp-root>" >&2
  exit 64
fi

if [ ! -f "$CRED_FILE" ]; then
  echo "ERROR: credentials file not found at ${CRED_FILE}" >&2
  echo "       (in a worktree, pass TORIN_CRED_FILE=/path/to/primary/filezilla-server-data.xml)" >&2
  exit 1
fi

command -v python3 >/dev/null 2>&1 || { echo "ERROR: python3 required" >&2; exit 1; }

NETRC_FILE=$(mktemp "${TMPDIR:-/tmp}/torin-fetch-netrc.XXXXXX")
chmod 600 "$NETRC_FILE"
trap 'rm -f "$NETRC_FILE"' EXIT

FTP_HOST=$(python3 - "$CRED_FILE" "$SERVER_NAME" "$NETRC_FILE" <<'PYEOF'
import sys, base64, xml.etree.ElementTree as ET

cred_file, server_name, netrc_path = sys.argv[1], sys.argv[2], sys.argv[3]
for server in ET.parse(cred_file).iter("Server"):
    name_el = server.find("Name")
    if name_el is None or name_el.text != server_name:
        continue
    host = server.find("Host").text
    user = server.find("User").text
    pass_el = server.find("Pass")
    raw = pass_el.text or ""
    password = base64.b64decode(raw).decode("utf-8") if pass_el.get("encoding") == "base64" else raw
    with open(netrc_path, "w") as f:
        f.write("machine %s\nlogin %s\npassword %s\n" % (host, user, password))
    print(host)   # the hostname is the only non-secret part
    sys.exit(0)
sys.stderr.write("ERROR: server entry '%s' not found in %s\n" % (server_name, cred_file))
sys.exit(1)
PYEOF
)

[ -n "${FTP_HOST:-}" ] || { echo "ERROR: could not resolve FTP credentials" >&2; exit 1; }

# Same TLS posture, and the same public-key pin, as backup-live-site.sh. The
# shared-hosting wildcard certificate does not cover the vanity hostname, so
# -k is unavoidable — and because -k disables the WHOLE chain rather than just
# the hostname check, the pin is what actually authenticates the peer. If this
# ever fails after a certificate rotation, recompute it the way that script's
# comment documents; do NOT drop the pin and do NOT fall back to plaintext FTP
# with live credentials.
PIN="sha256//Z7N5Hk+6AzND7F/ToDmzG91E2tHDk6WVlyWLfDqXcRU="
exec curl --fail --silent --show-error --netrc-file "$NETRC_FILE" \
  --ftp-ssl -k --pinnedpubkey "$PIN" --max-time 60 \
  "ftp://${FTP_HOST}/${REMOTE_PATH}"
