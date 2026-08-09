---
phase: 02-design-system-information-architecture
plan: 08
subsystem: build-deploy
tags: [caching, cache-invalidation, php, htaccess, verification-gate, gap-closure]
status: complete
gap_closure: true
gap_ids: [G-02-1]
requirements: [DESIGN-01]

requires:
  - "src/includes/header.php (plan 02-06) — the sole shared head for all sixteen pages"
  - "src/includes/dev-switcher.php (plan 02-01, D-03) — the dev-only theme override"
  - "src/.htaccess mod_expires block (plan 02-01) — the existing module guard this extends"
  - "scripts/deploy-new.sh — FTPS deploy of individually named paths"
provides:
  - "torin_asset_url() — the single place a static-asset URL is version-stamped"
  - "scripts/asset-version-check.sh — the durable non-cache-busted gate over all sixteen deployed pages"
  - "Bounded staging cache lifetime for text/css and both JavaScript media types"
affects:
  - "Phase 4 cutover: must raise the css/js lifetime once the stamp is proven in production (DESIGN-02), and deletes dev-switcher.php"
  - "Plan 02-09 (same wave): depends on the Sofia Sans woff2 preload staying UNSTAMPED"

tech-stack:
  added: []
  patterns:
    - "Version stamping via ?v=<filemtime> computed in PHP at request time — no build step, no rename discipline, no new dependency"
    - "Failed stat returns a ?v=0 sentinel rather than a bare href, so a broken stamp is detectable instead of indistinguishable from the defect"
    - "Verification fetches BARE URLs — no cache-busting query, no Cache-Control/Pragma request header"

key-files:
  created:
    - "src/includes/asset-version.php"
    - "scripts/asset-version-check.sh"
  modified:
    - "src/includes/header.php"
    - "src/includes/dev-switcher.php"
    - "src/.htaccess"

decisions:
  - "Query-string invalidation (?v=<filemtime>) chosen over the owner's per-version rename, because rename is manual and every manual step is a step someone eventually skips"
  - "Token is a modification time, not a content hash — a content hash would mean digesting every stylesheet on every request on a PHP 5.2 shared host with no reliable opcode cache"
  - "Sofia Sans woff2 preload deliberately NOT stamped, for two independent reasons (font-face byte-match, and font-swap probe glob blinding)"
  - "dev-switcher.php line 34's unconditional theme-a.css link observed and deliberately NOT fixed — out of scope, file is deleted at Phase 4"
  - "Staging css/js max-age set to 300s (5 minutes), to be raised by Phase 4 (DESIGN-02)"

metrics:
  duration: "~50 min"
  completed: 2026-08-09
  tasks: 3
  commits: 3

actuals:
  tokens: 21000
  tasks: 3
  commits: 3
---

# Phase 02 Plan 08: Asset Version Stamping Summary

Closed G-02-1 by routing every stylesheet and the one script through a new PHP `torin_asset_url()` helper that appends `?v=<filemtime>`, bounding the staging cache to 5 minutes as an independent second line of defence, and committing the first verification gate in this phase that fetches with **bare URLs**.

## What was built

| Artifact | Role |
|---|---|
| `src/includes/asset-version.php` | New PHP 5.2-safe library, one function `torin_asset_url($rel)`. Emits nothing on include. Resolves `dirname(dirname(__FILE__)) . '/' . $rel`, guards `file_exists()` then `filemtime()`, returns `?v=0` on a failed stat. |
| `src/includes/header.php` | Five asset URLs stamped: `css/base.css`, `css/layout.css`, `css/components.css`, `css/no-js.css` (inside `noscript`), `js/site.js`. Font preload left bare with both reasons recorded beside it. |
| `src/includes/dev-switcher.php` | The dev-only `css/theme-a.css` link stamped by the same helper. |
| `src/.htaccess` | `text/css` cut from 7 days to 5 minutes; `application/javascript` and `text/javascript` given the same rule (previously **no** rule matched `js/site.js`). Strictly inside the existing `<IfModule mod_expires.c>` guard. |
| `scripts/asset-version-check.sh` | The durable gate: sixteen pages, bare URLs, three checks, exits non-zero on any failure. |

## Commits

| Task | Commit | Description |
|---|---|---|
| 1 (tracer) | `4960b83` | Library + three unconditional stylesheets |
| 2 | `2e70886` | Conditional assets, font exclusion, bounded cache |
| 3 | `150ae4c` | The non-cache-busted gate |

## Measured results

### Check A — sixteen pages, bare-URL fetch (verbatim)

```
PAGE                     HTTP     PHP    STAMPED  UNSTAMPED  SITEJS   WOFF2    WOFF2Q   SENTINEL
index.html               200      0      5        0          1        1        0        0
about.html               200      0      5        0          1        1        0        0
laptopi.html             200      0      5        0          1        1        0        0
profilaktika-laptop.html 200      0      5        0          1        1        0        0
optimizatsiq.html        200      0      5        0          1        1        0        0
mehanichni-problemi.html 200      0      5        0          1        1        0        0
za-bateriite.html        200      0      5        0          1        1        0        0
tokov-udar.html          200      0      5        0          1        1        0        0
zalivane-technosti.html  200      0      5        0          1        1        0        0
rezervni-chasti.html     200      0      5        0          1        1        0        0
warrently.html           200      0      5        0          1        1        0        0
uslovia.html             200      0      5        0          1        1        0        0
covid.html               200      0      5        0          1        1        0        0
test-laptop.html         200      0      5        0          1        1        0        0
problem-stari.html       200      0      5        0          1        1        0        0
msg.html                 200      0      5        0          1        1        0        0
```

### Check B — token versus `Last-Modified` (verbatim)

```
ASSET                        HTTP     TOKEN          LASTMODIFIED   VERDICT
css/base.css                 200      1786024632     1786024632     MATCH
css/components.css           200      1786040571     1786040571     MATCH
css/layout.css               200      1785985631     1785985631     MATCH
css/no-js.css                200      1786025202     1786025202     MATCH
css/theme-a.css              200      1785967706     1785967706     MATCH
js/site.js                   200      1785984699     1785984699     MATCH
```

### Check C — `Cache-Control` returned by the origin (verbatim)

```
ASSET                        MAXAGE       VERDICT
css/base.css                 300          OK
css/components.css           300          OK
js/site.js                   300          OK
```

`js/site.js` is served as `content-type: application/javascript`, so the new rule matches it; before this plan it matched no rule at all and fell to heuristic caching derived from `Last-Modified`.

### Freeze detection — byte-identical redeploy of `js/site.js`

```
before = js/site.js?v=1785984699
after  = js/site.js?v=1786283329
sha256 before = 25c5daa2c8cdf781619e31b366953453a9c0020ba63456a01136929c1de58f8f
sha256 after  = 25c5daa2c8cdf781619e31b366953453a9c0020ba63456a01136929c1de58f8f
```

Source byte-identical (`git diff --quiet HEAD -- src/js/site.js` clean, sha256 unchanged), token moved. Gate re-run green afterwards.

### Rendered non-regression versus `02-RENDERED-VERIFICATION.md`

| Probe | Baseline | Re-measured | Verdict |
|---|---|---|---|
| `hero-stack.js` @ 360x640 | `sizedByMinHeightNotContent: true`, headroom 35.4px | `true`, headroom **35.4px** (min-height 268.8, stack 233.4) | PASS, unchanged |
| `no-script-nav.js` @ 1440x900 | 5 top-level items + 6 category links visible; disclosure controls not visible, not Tab-reachable | 5 + 6 visible; `toggleVisible: false`, `disclosureVisible: false`, `toggleTabReachable: false`, `disclosureTabReachable: false`; `overflowPx: -15`; `problems: []` | PASS, unchanged |

`no-script-nav.js` reported `noJsStylesheetApplied: true`, which independently confirms the newly-stamped `css/no-js.css` still resolves in the no-script rendering — the one stamped URL no bare-`curl` page check exercises as a stylesheet.

## The three qualifications, carried rather than paraphrased

**1. What was proven, and by which check.** The gate proves a resolvable version token is emitted on bare-URL fetches. It does **not** itself demonstrate that a browser evicted an old file — `curl` does not cache, so no curl-based check can observe an eviction. The token is the *mechanism* by which a browser does so (a different URL is a different cache entry); the bounded `max-age` is the independent backstop.

The two liveness claims are **separate and must stay separate**:

- The **token-equals-`Last-Modified`** comparison proves **path resolution** — that the helper stat'd the same file the origin serves. `filemtime()` and Apache's `Last-Modified` read the same inode stat, so the two are equal *by construction* whenever the path is right. It catches a stamp computed against a non-existent or wrong path, and (via the `?v=0` sentinel the checker rejects) a stat that failed outright. **It cannot detect a frozen stamp**, because freezing moves both values together and they stay equal.
- The **byte-identical redeploy** of `js/site.js` is the **only** freeze detector in this plan. The equality is not written up as one.

**2. The retracted cutover reasoning stays retracted.** The original blocker severity rested on returning torin.bg customers receiving new HTML with old CSS at cutover. That reasoning is wrong and is not restated: the legacy site loads `assets1/css/theme.min.css`, `business.css` and `animation.css`; the new site loads `css/base.css`, `layout.css` and `components.css` — zero overlap in filename or directory, so at cutover no returning visitor holds any of the new files cached. What is real, hence major, is **staging-review reliability** while `/new/` is iterated several times a day, and **every future CSS edit after launch**.

**3. Known cost of the chosen mechanism.** The token is a modification time, not a content hash, so a redeploy of byte-identical files bumps it and warms caches unnecessarily — directly observed above, where an unchanged `site.js` got a new token. That costs a few KB on a low-traffic brochure site; a content hash would mean reading and digesting every stylesheet on every request on a PHP 5.2 shared host with no reliable opcode or user cache. The owner's alternative — renaming the file per version — remains the fallback if query-string invalidation ever proves unreliable on this host; it is not chosen because it is manual.

## Deviations from Plan

### 1. [Rule 1 — verification-method defect] Two of task 2's source greps are false negatives on BSD grep

**Found during:** Task 2.

**Issue:** Two plan-supplied assertions on `dev-switcher.php` did not read their expected values:

- `grep -c "in_array(\$_COOKIE['torin_theme'], \$torin_allowed_themes, true)"` returned **0**, expected 1 (same for the `$_GET` variant).
- `grep -cE "data-theme=\"' *\. *\$"` returned **1**, expected 0.

**Diagnosis — neither is a code defect.** Both were re-run against the **unchanged `HEAD` version** of the file:

- The `in_array` pattern returns 0 on the untouched file too. It is a BSD-vs-GNU BRE escaping artifact (`grep -Ec` with escaped parens returns 1; `grep -cF` returns 1). macOS ships BSD grep.
- The `data-theme` match is line 14, inside the **pre-existing SECURITY comment** that quotes the anti-pattern as the thing not to do: `echo ' data-theme="' . $_GET['theme'] . '"' is reflected XSS`. It reads 1 on `HEAD` as well. This is the same comment-sensitivity trap the plan itself guards against elsewhere (it strips comments before the `.htaccess` and checker-script greps, but not this one).

**Fix:** Substituted intent-equivalent assertions and proved the region untouched:

```
grep -cF "in_array($_COOKIE['torin_theme'], $torin_allowed_themes, true)"  -> 1
grep -cF "in_array($_GET['theme'], $torin_allowed_themes, true)"           -> 1
grep -v '^[[:space:]]*//' dev-switcher.php | grep -cE "data-theme=\"' *\. *\$" -> 0
diff HEAD:lines1-33 vs now:lines1-33 -> IDENTICAL
```

The three security points (both whitelist checks, the literal-not-reflected attribute) are byte-identical to `HEAD`. No code changed; only the measurement did.

### 2. [Process] Tracer feedback gate resolved autonomously

Auto mode was off (`workflow._auto_chain_active` and `workflow.auto_advance` both `false`), which would normally make the tracer gate a human checkpoint. This plan declares `autonomous: true` with zero `checkpoint:*` tasks, and every tracer assertion is machine-checkable. The tracer's `<verify>` was re-run end-to-end against the live origin and passed completely (3 stamped hrefs, 0 unstamped, 0 sentinels, token `MATCH`, four pages HTTP 200 with zero PHP leakage) before any expansion task began. Recorded so the choice is visible rather than silent.

## Known Stubs

None. No placeholder value, no hardcoded empty, no TODO was introduced.

## Still open, deliberately untouched

- **G-02-1b (category icon redraw)** — deferred to Phase 3 by explicit owner decision on 2026-08-09, tracked as `.planning/todos/pending/redraw-category-icons.md`. `src/includes/icons.php` was not touched by this plan.
- **Phase 4 cutover obligations** — must raise the `text/css` and JavaScript lifetimes once the stamp is proven in production (DESIGN-02), and must delete `dev-switcher.php`, after which the checker's `theme-a.css` assertion becomes a no-op by design.
- **`dev-switcher.php` line 34 emits `$torin_extra_head` outside the `if ($torin_theme === 'a')` branch**, so `theme-a.css` is linked on every page load including the default Theme B. Observed during this plan and deliberately **not** fixed: it is a one-line move inside a file Phase 4 deletes outright, and its whole effect is one wasted request for a stylesheet whose single `[data-theme="a"]` block is inert on a Theme-B page. **Task 2's assertion of exactly one stamped `theme-a.css` on the default page depends on this defect** and would read 0 once it is fixed. Task 3's committed gate asserts the same link **conditionally** precisely so it survives that fix and the Phase 4 deletion — the two assertions differ **on purpose**, not by oversight, and the reason is stated at the code in both places.
- **The Sofia Sans preload must stay unstamped for a second reason invisible from `header.php`:** `scripts/probes/font-swap.js` line 39 blocks fonts with the glob `['*.woff2','*.woff','*.ttf']`, which stops matching once a query string is appended. Stamping the font would let the fallback pass load the real font, making both passes identical, and plan 02-09's G-02-4 gate would report `maxAbsDeltaPx: 0` — a clean-looking pass that measures nothing. **Stamping this href does not fail that gate, it blinds it.** Both plans in this wave carry the coupling in their own text so a future change to either side cannot silently break the other. The checker enforces it: `WOFF2Q` is asserted 0 on all sixteen pages, with the reason in the failure message.

### 3. [Rule 2 — project precedent] DESIGN-01 deliberately NOT flipped to Complete

The plan's frontmatter carries `requirements: [DESIGN-01]`, which would normally be marked complete on plan completion. It was deliberately left open:

- DESIGN-01 is claimed by **eight** plans in this phase, including **02-09**, which is still outstanding in this same wave.
- Its traceability row in `REQUIREMENTS.md` reads **`Gaps Found`**, and G-02-4 (font-swap reflow) is the gap 02-09 exists to close.
- This project has already reverted exactly this class of premature flip once (commit `abd5ba8`, recorded as a phase-02 decision).

Flipping it here would claim a phase-wide requirement on the strength of one gap closure. `roadmap update-plan-progress 02` was run and correctly reports 8/9 plans, `In Progress`.

### 4. [Rule 1] `state.advance-plan` wrote an incorrect Current Position

The handler read a stale `Plan: 7 of 7` line and emitted `Plan: 2 of 9`. Corrected in `STATE.md` to `Plan: 9 of 9` with status `02-01..02-08 complete; 02-09 remains`. The frontmatter counts (`completed_plans: 13 / total_plans: 14`) come from a disk scan and were already correct.

## Threat Flags

None. No new network endpoint, auth path, file-access pattern or schema change at a trust boundary. `T-02-27` (the `?v=<filemtime>` query publishing each asset's deploy timestamp) remains **accepted**: the origin already publishes the identical value in the `Last-Modified` response header on every one of these assets, as the Check B table above shows directly — the query string discloses nothing the response headers do not.

## Self-Check: PASSED

- `src/includes/asset-version.php` — FOUND
- `src/includes/header.php` — FOUND
- `src/includes/dev-switcher.php` — FOUND
- `src/.htaccess` — FOUND
- `scripts/asset-version-check.sh` — FOUND (executable)
- Commits `4960b83`, `2e70886`, `150ae4c` — all FOUND in `git log`
