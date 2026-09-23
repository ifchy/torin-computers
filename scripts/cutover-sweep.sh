#!/usr/bin/env bash
#
# cutover-sweep.sh
#
# The go/no-go instrument for the Phase 4 root cutover (D4-32, plan 04-09).
# Drives the non-rendered assertions with curl, delegates the rendered ones to
# scripts/probes/cutover-sweep.js via scripts/render-check.sh, and aggregates a
# single verdict. Any FAIL is the rollback trigger.
#
# Usage:
#   scripts/cutover-sweep.sh --target https://torin.bg/new     # rehearse on staging
#   scripts/cutover-sweep.sh --target https://torin.bg         # the real go/no-go
#   scripts/cutover-sweep.sh --target https://torin.bg --submit
#   scripts/cutover-sweep.sh --target https://torin.bg --no-render
#
# Flags:
#   --target <origin>  Origin, optionally with a path prefix. REQUIRED. The whole
#                      sweep is expressed relative to it, so the same script runs
#                      unchanged against the staging subtree and the live root.
#   --submit           Also POST a real enquiry through the contact form.
#                      OFF BY DEFAULT ON PURPOSE: a submission sends a real
#                      Telegram message and a real email to the shop owner. A
#                      rehearsal must not page a human. Without it, the delivery
#                      assertion is reported SKIPPED, never PASSED.
#   --no-render        Skip the browser probe (curl-only run).
#
# ───────────────────────────────────────────────────────────────────────────
# THE ONE ASSERTION THIS SCRIPT EXISTS FOR
#
# Every redirect is FOLLOWED TO ITS TERMINAL RESPONSE, and the FINAL status, the
# FINAL URL and the HOP COUNT are asserted. Not the first status line. Not the
# Location header.
#
# A check that greps only `^HTTP/` and `^location:` PASSES the exact defect this
# project shipped: on the first deploy of the retirement rules, a relative
# substitution with no RewriteBase made Apache build the Location from the
# filesystem path, and all four retired URLs answered a correct-looking 301
# pointing at https://torin.bg/home/torin/public_html/new/<target> — which 404'd
# on the follow. Nothing in any build failed. Only following the redirect found it.
#
# A homepage spot-check was explicitly rejected as the go/no-go for the same
# reason: it would not have caught that.
# ───────────────────────────────────────────────────────────────────────────
#
# STAGING PROFILE vs ROOT PROFILE. Some assertions are only meaningful at the
# true domain root, and saying so is not a loophole — it is the difference
# between a check and a ritual:
#   - robots.txt and sitemap.xml are IGNORED by crawlers outside the domain root
#     (which is precisely why the staging subtree needs an X-Robots-Tag header
#     instead), so they are not applicable under a path prefix.
#   - the X-Robots-Tag noindex header is REQUIRED on staging and FORBIDDEN at the
#     root. The same value is a pass in one profile and a critical failure in the
#     other, so it is asserted in both directions rather than one.
#   - the Search Console verification file belongs at the true root.
# Non-applicable assertions are reported SKIPPED with a reason and are counted.
# A run with skips prints that it does NOT authorise a cutover.
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
SRC_ROOT="${REPO_ROOT}/src"

TARGET=""
DO_SUBMIT=0
DO_RENDER=1

while [ "$#" -gt 0 ]; do
  case "$1" in
    --target) TARGET="${2:-}"; shift 2 ;;
    --submit) DO_SUBMIT=1; shift ;;
    --no-render) DO_RENDER=0; shift ;;
    -h|--help) sed -n '2,40p' "${BASH_SOURCE[0]}"; exit 0 ;;
    *) echo "ERROR: unknown argument: $1" >&2; exit 2 ;;
  esac
done

if [ -z "$TARGET" ]; then
  echo "ERROR: --target <origin> is required. Example: --target https://torin.bg/new" >&2
  exit 2
fi

# Normalise: strip any trailing slash so "${TARGET}/x" never doubles it.
TARGET="${TARGET%/}"

# Split the target into scheme, host and path prefix. The path prefix is what
# decides the profile, and the host is what the variant checks are built from.
TARGET_NOSCHEME="${TARGET#*://}"
TARGET_HOST="${TARGET_NOSCHEME%%/*}"
if [ "$TARGET_NOSCHEME" = "$TARGET_HOST" ]; then
  TARGET_PATH=""
else
  TARGET_PATH="/${TARGET_NOSCHEME#*/}"
fi
# The apex form, with any leading www stripped — this is the canonical host.
APEX_HOST="${TARGET_HOST#www.}"
CANONICAL="https://${APEX_HOST}${TARGET_PATH}"

if [ -n "$TARGET_PATH" ]; then
  PROFILE="staging"
else
  PROFILE="root"
fi

CURL_OPTS=(--silent --show-error --max-time 30)

PASS_N=0
FAIL_N=0
SKIP_N=0
FAILURES=()

pass() { PASS_N=$((PASS_N + 1)); printf '  PASS  %s\n' "$1"; }
fail() { FAIL_N=$((FAIL_N + 1)); FAILURES+=("$1"); printf '  FAIL  %s\n' "$1"; }
skip() { SKIP_N=$((SKIP_N + 1)); printf '  SKIP  %s\n' "$1"; }

# ── follow_redirect <url> <expected-final-url> <expected-hops> <label> ──────
# THE CORE ASSERTION. -L follows every hop to the terminal response; the final
# status, the final URL and the hop count all come from the completed chain.
follow_redirect() {
  local url="$1" want_url="$2" want_hops="$3" label="$4"
  local out code final hops
  out="$(curl "${CURL_OPTS[@]}" -L -o /dev/null \
        -w '%{http_code} %{url_effective} %{num_redirects}' "$url" 2>/dev/null || echo "000 - -")"
  code="$(printf '%s' "$out" | awk '{print $1}')"
  final="$(printf '%s' "$out" | awk '{print $2}')"
  hops="$(printf '%s' "$out" | awk '{print $3}')"

  # Compare ignoring a trailing slash on either side.
  local f="${final%/}" w="${want_url%/}"

  if [ "$code" != "200" ]; then
    fail "${label}: terminal status ${code} (expected 200) — final URL ${final}, ${hops} hop(s)"
  elif [ "$f" != "$w" ]; then
    fail "${label}: landed on ${final} (expected ${want_url}) after ${hops} hop(s), terminal status ${code}"
  elif [ "$hops" != "$want_hops" ]; then
    fail "${label}: ${hops} hop(s), expected ${want_hops} — final ${final} ${code}"
  else
    pass "${label}: ${code} -> ${final} in ${hops} hop(s)"
  fi
}

# ── expect_status <url> <expected> <label> ─────────────────────────────────
expect_status() {
  local url="$1" want="$2" label="$3" code
  code="$(curl "${CURL_OPTS[@]}" -o /dev/null -w '%{http_code}' "$url" 2>/dev/null || echo "000")"
  if [ "$code" = "$want" ]; then
    pass "${label}: ${code}"
  else
    fail "${label}: ${code} (expected ${want})"
  fi
}

header_value() {
  curl "${CURL_OPTS[@]}" -I "$1" 2>/dev/null \
    | tr -d '\r' | awk -F': ' -v h="$2" 'tolower($1)==tolower(h){print $2; exit}'
}

echo "════════════════════════════════════════════════════════════════"
echo " cutover sweep — target: ${TARGET}"
echo " profile: ${PROFILE}   canonical: ${CANONICAL}"
echo " started: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
echo "════════════════════════════════════════════════════════════════"

# ── 1. The four retirement redirects, followed to terminal ─────────────────
echo
echo "[1] Retirement redirects (SEO-05) — followed to terminal response"
follow_redirect "${TARGET}/covid.html"            "${CANONICAL}/about.html"                    1 "covid.html"
follow_redirect "${TARGET}/laptopi.html"          "${CANONICAL}/index.html"                    1 "laptopi.html"
follow_redirect "${TARGET}/rezervni-chasti.html"  "${CANONICAL}/ekran-klaviatura-portove.html"  1 "rezervni-chasti.html"
follow_redirect "${TARGET}/za-bateriite.html"     "${CANONICAL}/zalivane-technosti.html"        1 "za-bateriite.html"

# ── 2. The four protocol/host variants (D4-29) ─────────────────────────────
echo
echo "[2] Host canonicalisation (D4-29) — all variants to the canonical apex"
VARIANT_PAGE="index.html"
follow_redirect "http://${APEX_HOST}${TARGET_PATH}/${VARIANT_PAGE}"       "${CANONICAL}/${VARIANT_PAGE}" 1 "http apex"
follow_redirect "http://www.${APEX_HOST}${TARGET_PATH}/${VARIANT_PAGE}"   "${CANONICAL}/${VARIANT_PAGE}" 1 "http www"
follow_redirect "https://www.${APEX_HOST}${TARGET_PATH}/${VARIANT_PAGE}"  "${CANONICAL}/${VARIANT_PAGE}" 1 "https www"
follow_redirect "https://${APEX_HOST}${TARGET_PATH}/${VARIANT_PAGE}"      "${CANONICAL}/${VARIANT_PAGE}" 0 "https apex (canonical, no hop)"

# ── 3. Every page in the tree returns 200 ──────────────────────────────────
echo
echo "[3] Page availability — derived from src/*.html, not a hardcoded list"
PAGE_COUNT=0
PAGE_URLS=()
for f in "${SRC_ROOT}"/*.html; do
  [ -e "$f" ] || continue
  b="$(basename "$f")"
  # The verification file is not a page; it is asserted byte-wise in [6].
  case "$b" in google*.html) continue ;; esac
  PAGE_COUNT=$((PAGE_COUNT + 1))
  # The SAME list feeds the rendered probe in [9]. Derived once, here, so the
  # set that is status-checked and the set that is rendered cannot drift apart
  # — a second hardcoded list is how a page ends up in one and not the other.
  PAGE_URLS+=("${TARGET}/${b}")
  expect_status "${TARGET}/${b}" 200 "page ${b}"
done
echo "  (${PAGE_COUNT} pages checked)"

# ── 3b. Every static asset in the tree is actually SERVED ──────────────────
# THE CHECK THAT WOULD HAVE CAUGHT 2026-09-23. index.html was deployed
# referencing six icon files that were still local; all six returned 404, the
# live homepage showed six broken images, and this script reported 20/20 PASS,
# because nothing it asserted was about whether the ORIGIN HAS THE FILES.
#
# deploy-new.sh uploads and never deletes, keeps no manifest, and the origin
# holds no record of what it is missing — so an incomplete deploy is invisible
# from both ends unless something walks the tree and asks.
#
# THIS IS THE OPPOSITE DIRECTION FROM THE RENDERED PROBE, and both are needed.
# Section 9 asks what the PAGES request, catching a reference to a path that was
# never committed. This asks what the TREE contains, catching a file committed
# but never uploaded EVEN WHEN NO PAGE REFERENCES IT YET — which is the stronger
# check before a cutover, because it fails before the markup that needs the file
# ships.
#
# WHAT IT DOES NOT COVER, stated so it is never assumed: files on the ORIGIN
# that are no longer in src/. deploy-new.sh never deletes and nothing in this
# project can list remote files, so orphans are undetectable from here. That is
# the Phase 3.5 withdrawn-photographs problem — three unreferenced but still
# fetchable JPEGs plus four retired pages — and it stays a manual FileZilla pass
# at cutover.
#
# It also does not compare CONTENT, so a stale-but-present file still passes.
# That is the other half of the 2026-09-23 pair (staging silently two days
# behind) and needs a deployed-vs-committed digest, which this is not.
echo
echo "[3b] Static assets present on the origin — walked from src/, not hardcoded"
ASSET_COUNT=0
ASSET_MISSING=()
while IFS= read -r f; do
  rel="${f#${SRC_ROOT}/}"
  case "$rel" in
    # Deliberate exceptions, each for a stated reason:
    vendor/phpmailer/*) continue ;;   # deny block returns 403 BY DESIGN (ledger 42)
    *.htaccess)         continue ;;   # deploy-new.sh refuses it — root form (D4-30)
  esac
  ASSET_COUNT=$((ASSET_COUNT + 1))
  code="$(curl "${CURL_OPTS[@]}" -o /dev/null -w '%{http_code}' "${TARGET}/${rel}" 2>/dev/null || echo "000")"
  [ "$code" = "200" ] || ASSET_MISSING+=("${rel} -> ${code}")
done < <(find "${SRC_ROOT}" -type f \
           \( -name '*.css' -o -name '*.js' -o -name '*.svg' -o -name '*.png' \
              -o -name '*.jpg' -o -name '*.webp' -o -name '*.ico' -o -name '*.woff2' \) 2>/dev/null)

if [ "${#ASSET_MISSING[@]}" -eq 0 ]; then
  pass "all ${ASSET_COUNT} static assets served"
else
  fail "${#ASSET_MISSING[@]} of ${ASSET_COUNT} static assets NOT served — the tree is deployed INCOMPLETELY:"
  for m in "${ASSET_MISSING[@]}"; do printf '          %s\n' "$m"; done
fi

# ── 4. The noindex header, asserted in BOTH directions ─────────────────────
echo
echo "[4] X-Robots-Tag — required on staging, forbidden at the root"
XR="$(header_value "${TARGET}/index.html" 'x-robots-tag' || true)"
if [ "$PROFILE" = "root" ]; then
  if [ -z "$XR" ]; then
    pass "no X-Robots-Tag at the root (the staging noindex block did not travel)"
  else
    fail "X-Robots-Tag PRESENT AT THE ROOT: '${XR}' — this deindexes the entire live site. ROLL BACK."
  fi
else
  if [ -n "$XR" ]; then
    pass "X-Robots-Tag present on staging as required: '${XR}'"
  else
    fail "X-Robots-Tag MISSING on staging — the subtree is indexable"
  fi
fi

# ── 5. Root-only files ─────────────────────────────────────────────────────
echo
echo "[5] Root-only artefacts"
if [ "$PROFILE" = "root" ]; then
  expect_status "${TARGET}/robots.txt"  200 "robots.txt present"
  expect_status "${TARGET}/sitemap.xml" 200 "sitemap.xml present"
else
  skip "robots.txt — ignored by crawlers outside the domain root, not applicable under ${TARGET_PATH}/ (and not deployed: ledger #47)"
  skip "sitemap.xml — declared at the domain root, not applicable under ${TARGET_PATH}/ (and not deployed: ledger #47)"
fi
expect_status "${TARGET}/favicon.ico" 200 "favicon.ico present"

# ── 6. The Search Console verification file, byte-identical ────────────────
echo
echo "[6] Search Console verification file"
GSC_SRC="$(ls "${SRC_ROOT}"/google*.html 2>/dev/null | head -n1 || true)"
if [ "$PROFILE" != "root" ]; then
  skip "verification file — belongs at the true domain root, not applicable under ${TARGET_PATH}/"
elif [ -z "$GSC_SRC" ]; then
  fail "no google*.html in src/ — the cutover would orphan the verification token"
else
  GSC_NAME="$(basename "$GSC_SRC")"
  GSC_TMP="$(mktemp)"
  trap 'rm -f "${GSC_TMP}"' EXIT
  if curl "${CURL_OPTS[@]}" -o "${GSC_TMP}" "${TARGET}/${GSC_NAME}" 2>/dev/null \
     && cmp -s "${GSC_SRC}" "${GSC_TMP}"; then
    pass "${GSC_NAME} served byte-identical to the source copy"
  else
    # This host EXECUTES .html as PHP, so the file is parsed before being
    # served. Byte-identical is the assertion; "exists" is not enough.
    fail "${GSC_NAME} differs from src/ or is missing — this host parses .html as PHP, so it must still come back byte-identical"
  fi
fi

# ── 7. The host error log must not be readable ─────────────────────────────
echo
echo "[7] Host error log refused"
LOG_CODE="$(curl "${CURL_OPTS[@]}" -o /dev/null -w '%{http_code}' "${TARGET}/error_log" 2>/dev/null || echo "000")"
if [ "$LOG_CODE" = "200" ]; then
  fail "error_log is PUBLICLY READABLE (200) — the root now runs PHP and accepts submissions, so it will accumulate entries"
else
  pass "error_log refused: ${LOG_CODE}"
fi

# ── 8. Contact page: reachable, and never cached ───────────────────────────
echo
echo "[8] Contact page"
expect_status "${TARGET}/kontakti.html" 200 "kontakti.html reachable"
CC="$(header_value "${TARGET}/kontakti.html" 'cache-control' || true)"
case "$CC" in
  *no-store*) pass "kontakti.html Cache-Control: ${CC}" ;;
  "")         fail "kontakti.html has NO Cache-Control — a cached contact page serves every visitor the same signed render timestamp, and the form then refuses real people" ;;
  *)          fail "kontakti.html Cache-Control is '${CC}', missing no-store — see above" ;;
esac

if [ "$DO_SUBMIT" = "1" ]; then
  SUB="$(curl "${CURL_OPTS[@]}" -o /dev/null -w '%{http_code}' \
        -X POST "${TARGET}/contact-send.php" \
        -F "name=cutover-sweep" -F "phone=0000000000" \
        -F "message=Automated cutover sweep submission — please ignore." \
        -F "consent=1" 2>/dev/null || echo "000")"
  case "$SUB" in
    200|303) pass "live submission accepted: ${SUB}" ;;
    *)       fail "live submission returned ${SUB}" ;;
  esac
else
  skip "live submission — NOT attempted (a real submission pages the shop owner). Re-run with --submit, and confirm the enquiry arrives EXACTLY ONCE."
fi

# ── 9. Rendered probe ──────────────────────────────────────────────────────
echo
echo "[9] Rendered probe (Cyrillic prose, runtime diagnostics, chat widget)"
if [ "$DO_RENDER" = "0" ]; then
  skip "rendered probe — --no-render was passed"
elif [ ! -x "${SCRIPT_DIR}/render-check.sh" ]; then
  skip "rendered probe — scripts/render-check.sh not executable"
elif [ "${#PAGE_URLS[@]}" -eq 0 ]; then
  fail "rendered probe — no pages were derived from ${SRC_ROOT}/*.html, so there was nothing to render"
else
  # EVERY page from [3], in one browser session. The probe owns the loop; the
  # browser is launched once. One newline-separated list, one env var.
  RENDER_OUT="$(SWEEP_URLS="$(printf '%s\n' "${PAGE_URLS[@]}")" \
                "${SCRIPT_DIR}/render-check.sh" "${SCRIPT_DIR}/probes/cutover-sweep.js" \
                "${TARGET}/index.html" 390 844 2>&1 || true)"

  # Twenty raw JSON blobs is not a report. Print one line per page, and the
  # full record only for pages that did not pass. If the output cannot be
  # parsed (harness error, browser failure), fall back to printing it raw —
  # that text is the diagnosis.
  RENDER_TMP="$(mktemp "${TMPDIR:-/tmp}/cutover-render-XXXXXX")"
  printf '%s' "$RENDER_OUT" > "$RENDER_TMP"
  if ! node "${SCRIPT_DIR}/lib/render-digest.js" "$RENDER_TMP" 2>/dev/null; then
    echo "$RENDER_OUT" | sed 's/^/      /'
  fi
  rm -f "$RENDER_TMP"

  # Match ONLY the aggregate key. Per-page records carry `pageVerdict`, so a
  # single passing page can never satisfy the run-level match.
  case "$RENDER_OUT" in
    *'"sweepVerdict": "PASS"'*|*'"sweepVerdict":"PASS"'*)
      pass "rendered probe: all ${PAGE_COUNT} pages" ;;
    *'"sweepVerdict": "INCONCLUSIVE"'*|*'"sweepVerdict":"INCONCLUSIVE"'*)
      skip "rendered probe returned INCONCLUSIVE — at least one page asserted nothing, which is not a pass" ;;
    *) fail "rendered probe did not report PASS across all ${PAGE_COUNT} pages" ;;
  esac
fi

# ── Verdict ────────────────────────────────────────────────────────────────
echo
echo "════════════════════════════════════════════════════════════════"
echo " pass: ${PASS_N}   fail: ${FAIL_N}   skipped: ${SKIP_N}"
if [ "$FAIL_N" -gt 0 ]; then
  echo
  echo " FAILURES:"
  for f in "${FAILURES[@]}"; do echo "   - $f"; done
  echo
  echo " VERDICT: NO-GO — roll back (cutover checklist Section 7)."
  echo "════════════════════════════════════════════════════════════════"
  exit 1
fi
if [ "$PROFILE" != "root" ]; then
  echo " VERDICT: rehearsal PASS against ${TARGET}."
  echo " THIS RUN DOES NOT AUTHORISE A CUTOVER — it was not run against the root."
elif [ "$SKIP_N" -gt 0 ]; then
  echo " VERDICT: PASS with ${SKIP_N} skipped assertion(s) — review each before go."
else
  echo " VERDICT: GO."
fi
echo "════════════════════════════════════════════════════════════════"
exit 0
