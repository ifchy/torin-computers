#!/usr/bin/env bash
#
# render-check.sh
#
# Runs a rendered-verification probe against a deployed page in a real headless
# Chromium, and prints the probe's JSON result on stdout.
#
# This exists because Phase 2's success criteria ("displays correctly on mobile
# and desktop", "all Cyrillic text renders correctly") are stated in RENDERED
# terms, and for most of that phase they were recorded as unverifiable on the
# grounds that no automatable browser was installed. That was wrong: Brave is
# installed, Brave is Chromium, and it speaks CDP. Everything needed to measure
# contrast ratios, focus rings, layout overflow and no-script behaviour is
# already on this machine.
#
# No npm install, no package.json, no dev dependency -- consistent with this
# project's "no build step" constraint. The CDP client is scripts/lib/cdp-client.js.
#
# Browser lifecycle is owned here: a throwaway user-data-dir is created per run
# and removed via a trap on exit, so runs never share cookies (which matters --
# the theme switcher persists its choice in a cookie, and a leaked cookie would
# silently test the wrong theme).
#
# Usage:
#   scripts/render-check.sh <probe.js> [url] [width] [height] [--no-script]
#
# Examples:
#   scripts/render-check.sh scripts/probes/criteria.js
#   scripts/render-check.sh scripts/probes/criteria.js https://torin.bg/new/index.html 1440 900
#   scripts/render-check.sh scripts/probes/no-script-nav.js '' 1440 900 --no-script
#
# A probe is a Node module exporting `async function run(session, cdp, opts)`
# and returning a JSON-serialisable result. See scripts/probes/ for examples.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"

PROBE="${1:-}"
URL="${2:-https://torin.bg/new/index.html}"
WIDTH="${3:-390}"
HEIGHT="${4:-844}"
NO_SCRIPT="${5:-}"

if [ -z "$PROBE" ]; then
  echo "ERROR: probe path required. Usage: scripts/render-check.sh <probe.js> [url] [width] [height] [--no-script]" >&2
  exit 1
fi
if [ ! -f "$PROBE" ]; then
  echo "ERROR: probe not found: ${PROBE}" >&2
  exit 1
fi
[ -n "$URL" ] || URL="https://torin.bg/new/index.html"

# Validate the viewport explicitly. A non-numeric width used to fall through to
# the 390 default inside the client (Number("360 640") is NaN, and NaN || 390
# is 390), so a caller that mis-split its arguments got three confident runs
# that silently all measured the same viewport. Fail loudly instead.
case "$WIDTH" in
  ''|*[!0-9]*) echo "ERROR: width must be a positive integer, got '${WIDTH}'" >&2; exit 1 ;;
esac
case "$HEIGHT" in
  ''|*[!0-9]*) echo "ERROR: height must be a positive integer, got '${HEIGHT}'" >&2; exit 1 ;;
esac

# Locate a Chromium-family binary. Brave is what this machine has; the others
# are listed so this keeps working on a machine that has them instead.
BROWSER=""
for candidate in \
  "/Applications/Brave Browser.app/Contents/MacOS/Brave Browser" \
  "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  "/Applications/Chromium.app/Contents/MacOS/Chromium" \
  "/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge"; do
  if [ -x "$candidate" ]; then BROWSER="$candidate"; break; fi
done
if [ -z "$BROWSER" ]; then
  echo "ERROR: no Chromium-family browser found. Install Brave, Chrome, Chromium or Edge." >&2
  exit 1
fi

if ! command -v node >/dev/null 2>&1; then
  echo "ERROR: node is required to run the CDP client but was not found on PATH" >&2
  exit 1
fi

# Pick a free port so concurrent runs do not collide.
PORT=$(node -e 'const n=require("net");const s=n.createServer();s.listen(0,()=>{process.stdout.write(String(s.address().port));s.close()})')
PROFILE_DIR=$(mktemp -d "${TMPDIR:-/tmp}/render-check-profile-XXXXXX")
BROWSER_PID=""

cleanup() {
  if [ -n "$BROWSER_PID" ]; then
    kill "$BROWSER_PID" 2>/dev/null || true
    # Give the browser a moment to flush and exit; removing the profile while
    # it is still writing leaves "Directory not empty" noise on stderr.
    wait "$BROWSER_PID" 2>/dev/null || true
  fi
  rm -rf "$PROFILE_DIR" 2>/dev/null || true
}
trap cleanup EXIT

"$BROWSER" \
  --headless \
  --disable-gpu \
  --no-sandbox \
  --no-first-run \
  --disable-extensions \
  --remote-debugging-port="$PORT" \
  --user-data-dir="$PROFILE_DIR" \
  "about:blank" >/dev/null 2>&1 &
BROWSER_PID=$!

# Wait for the debugging endpoint rather than sleeping a fixed interval.
for _ in $(seq 1 40); do
  if curl -s --max-time 1 "http://127.0.0.1:${PORT}/json/version" >/dev/null 2>&1; then break; fi
  sleep 0.25
done
if ! curl -s --max-time 2 "http://127.0.0.1:${PORT}/json/version" >/dev/null 2>&1; then
  echo "ERROR: headless browser did not expose a debugging port within 10s" >&2
  exit 1
fi

NO_SCRIPT_FLAG="false"
[ "$NO_SCRIPT" = "--no-script" ] && NO_SCRIPT_FLAG="true"

REPO_ROOT="$REPO_ROOT" \
RENDER_CHECK_PORT="$PORT" \
RENDER_CHECK_URL="$URL" \
RENDER_CHECK_WIDTH="$WIDTH" \
RENDER_CHECK_HEIGHT="$HEIGHT" \
RENDER_CHECK_NOSCRIPT="$NO_SCRIPT_FLAG" \
node -e '
const path = require("path");
const cdp = require(path.join(process.env.REPO_ROOT, "scripts/lib/cdp-client.js"));
const probe = require(path.resolve(process.argv[1]));
(async () => {
  const session = await cdp.connect(Number(process.env.RENDER_CHECK_PORT));
  const opts = {
    url: process.env.RENDER_CHECK_URL,
    width: Number(process.env.RENDER_CHECK_WIDTH),
    height: Number(process.env.RENDER_CHECK_HEIGHT),
    noScript: process.env.RENDER_CHECK_NOSCRIPT === "true"
  };
  const result = await probe.run(session, cdp, opts);
  console.log(JSON.stringify(result, null, 2));
  session.ws.close();
  process.exit(0);
})().catch(e => { console.error("RENDER-CHECK ERROR:", e.message); process.exit(1); });
' "$PROBE"
