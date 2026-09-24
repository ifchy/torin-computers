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
#
# .htaccess is REFUSED by this script -- skipped in a no-argument run, a hard
# error when named explicitly. It is in root form and this script only writes to
# the staging subtree; see the guard below for the incident that motivates it.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
# The credentials file is gitignored, so it exists ONLY in the primary checkout
# — a git worktree (used for parallel plan execution) has its own root and no
# copy of it. Rather than duplicating secret material into every worktree, or
# symlinking it in, the path is overridable:
#
#   TORIN_CRED_FILE=/path/to/primary/filezilla-server-data.xml scripts/deploy-new.sh ...
#
# The default is unchanged, so an ordinary run from the primary checkout needs
# no environment at all. The variable carries a PATH, never a password — the
# password still never leaves the short-lived Python process below.
CRED_FILE="${TORIN_CRED_FILE:-${REPO_ROOT}/filezilla-server-data.xml}"
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
# Scratch space for deploy-time transforms (see strip_css below). Removed by the
# same trap as the credentials file, including on error.
STRIP_DIR=$(mktemp -d "${TMPDIR:-/tmp}/deploy-new-strip.XXXXXX")
cleanup() {
  rm -f "$NETRC_FILE"
  rm -rf "$STRIP_DIR"
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

# Deploy-time transform: stylesheets AND scripts ship without their comments.
#
# This project has no build step, so source bytes are wire bytes. components.css
# is ~60% comments by raw size, costing ~9.2 KB gzipped -- 45% of the 20 KB CSS
# transfer budget in the Phase 3 UI-SPEC, which the tree had already exceeded by
# 2,234 B before this was added. The comments earn their keep in source (they are
# what caught the CR-01/CR-02 specificity bugs in Phase 2), so they are stripped
# here rather than deleted: source keeps the rationale, the wire does not pay.
#
# Stripping is string-aware, not a regex -- see scripts/lib/strip-css-comments.py
# for why that distinction is load-bearing.
#
# JS goes through the same treatment for the same reason (ledger #43, option b).
# analytics.js gzipped to 1959 B against a 1 KB budget while its CODE was only
# 972 B -- the comments were the entire overage -- and photo-resize.js sat at
# 2042 B against 2048 B, six bytes from breaking a gate. Stripped, they are
# 782 B and 1492 B. The JS stripper carries one extra rule the CSS one does not
# need: it REFUSES (exit 2) rather than guess at a regex literal, because
# guessing wrong truncates a file silently. A refusal lands in the fail-open
# branch below and ships the source unchanged, which is the safe direction.
# scripts/strip-js-selftest.sh proves all of that, including that every file in
# src/js/ still parses after stripping.
#
# Prints the path to upload: either a stripped temp copy, or the original when
# the file is neither CSS nor JS, or when stripping failed.
resolve_upload_path() {
  local rel="$1" src="$2"
  case "$rel" in
    *.css|*.js)
      local stripper="lib/strip-css-comments.py"
      case "$rel" in *.js) stripper="lib/strip-js-comments.py" ;; esac
      local out="${STRIP_DIR}/$(echo "$rel" | tr '/' '_')"
      if python3 "${SCRIPT_DIR}/${stripper}" < "$src" > "$out" 2>/dev/null \
         && [ -s "$out" ]; then
        printf '%s' "$out"
        return 0
      fi
      # Fail open: a stripper problem must not block a deploy. Ship the source.
      # For JS this is also the deliberate landing spot for a REFUSAL (exit 2)
      # on a regex literal -- loud in the log, unchanged on the wire.
      echo "WARNING: could not strip comments from ${rel}; uploading it unchanged" >&2
      printf '%s' "$src"
      ;;
    *) printf '%s' "$src" ;;
  esac
}

# ---------------------------------------------------------------------------
# .htaccess IS NOT DEPLOYABLE FROM HERE, AND THAT IS NOW ENFORCED IN CODE.
#
# src/.htaccess was promoted to ROOT form in 04-09 (D4-30): its RewriteBase and
# its canonicalisation target both address "/". REMOTE_ROOT above is hardcoded
# to the staging subtree. Uploading that file through this script therefore
# points the rewrite base at / from a file living in /new/, which BREAKS EVERY
# REDIRECT IN IT.
#
# This is not a hypothetical. It shipped once already: the retirement rules
# answered with a perfectly well-formed 301 whose Location was
# https://torin.bg/home/torin/public_html/new/<target> -- a server path leaked
# into a public URL, 404 on follow, while the 301 itself looked correct to every
# check that read the status line instead of following it.
#
# Until this block existed the rule was written in two places that only a
# careful human reads: a boxed comment at the top of src/.htaccess, and step 2.4
# of the cutover checklist. `find -type f` matches dotfiles, so a no-argument
# run cheerfully uploaded it anyway. A rule a script can violate is not a rule.
#
# The file goes up exactly once, by hand, with the 04-10 root swap.
#
# MATCH THE ROOT FILE ONLY, NOT EVERY .htaccess. The first cut of this guard
# tested basename and therefore also blocked src/vendor/phpmailer/.htaccess --
# which is a deny block that MUST ship, and whose absence is the whole of ledger
# 42. A nested .htaccess is scoped to its own directory and carries no
# RewriteBase; only the root file was promoted. Caught by running the guard
# rather than by reading it, which is the same lesson this tree keeps relearning.
is_root_htaccess() { [ "${1#./}" = ".htaccess" ]; }
# ---------------------------------------------------------------------------

# Build the file list: explicit arguments, or every file under src/.
FILES=()
if [ "$#" -gt 0 ]; then
  # NAMED EXPLICITLY -> hard error. This is the invocation that produces the
  # failure above, so it earns a stop rather than a skip.
  for rel in "$@"; do
    if is_root_htaccess "$rel" && [ "${TORIN_DEPLOY_HTACCESS:-}" != "1" ]; then
      echo "ERROR: refusing to upload '${rel}' to ${REMOTE_ROOT}/" >&2
      echo "       src/.htaccess is in ROOT form (RewriteBase /). Uploading it to the" >&2
      echo "       staging subtree breaks every redirect in it -- this project has" >&2
      echo "       already shipped that defect once. It goes up by hand with the" >&2
      echo "       04-10 root swap." >&2
      echo "       Override only if you know why: TORIN_DEPLOY_HTACCESS=1" >&2
      exit 1
    fi
  done
  FILES=("$@")
else
  # NO ARGUMENT -> skip it, loudly. The operator meant "deploy the site"; they
  # did not name this file, so aborting the whole routine deploy over it would
  # just teach people to route around the guard.
  while IFS= read -r f; do
    if is_root_htaccess "$f" && [ "${TORIN_DEPLOY_HTACCESS:-}" != "1" ]; then
      echo "NOTE: skipping ${f} -- root-form file, not deployable to ${REMOTE_ROOT}/ (D4-30)." >&2
      echo "      It goes up by hand with the 04-10 root swap." >&2
      continue
    fi
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
  upload_path=$(resolve_upload_path "$rel" "$local_path")
  if [ "$upload_path" != "$local_path" ]; then
    printf '  uploading %s (comments stripped: %s -> %s B)\n' \
      "$rel" "$(wc -c < "$local_path" | tr -d ' ')" "$(wc -c < "$upload_path" | tr -d ' ')"
  else
    printf '  uploading %s\n' "$rel"
  fi
  if ! "${CURL_BASE[@]}" -T "$upload_path" \
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
