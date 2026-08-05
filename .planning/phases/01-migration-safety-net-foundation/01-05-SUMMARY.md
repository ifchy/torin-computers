---
phase: 01-migration-safety-net-foundation
plan: 5
subsystem: infra
tags: [php, ftp, ftps, seo, html]

# Dependency graph
requires:
  - phase: 01-01
    provides: "PHP 5.2-safe includes/{site-config,header,footer}.php pattern and the .htaccess PHP-as-.html handler, both live-verified end-to-end"
provides:
  - "All 16 of site-current/'s live page filenames now exist under public_html/new/ on bell.host.bg, byte-identical (case, extension) to their site-current counterparts"
  - "Live end-to-end proof (HTTP 200, non-zero body) for the site's complete 16-page URL surface, not just the tracer's one page"
affects: [phase-2, phase-3, phase-4-cutover]

actuals:
  tokens: 2000
  tasks: 2
  commits: 1

tech-stack:
  added: []
  patterns:
    - "Repeat plan 01-01's two-require_once skeleton (header.php, footer.php via dirname(__FILE__)) verbatim across every new page file - no per-page variation in the include mechanism"
    - "Reused scripts/backup-live-site.sh's credential-handling pattern (Python-subprocess base64 decode -> chmod-600 .netrc temp file -> curl --netrc-file -> trap cleanup) for the upload+verify step, kept as a scratchpad-only script rather than a committed repo script since this is a one-time migration action, not a repeatable tool"

key-files:
  created:
    - src/about.html
    - src/covid.html
    - src/laptopi.html
    - src/mehanichni-problemi.html
    - src/msg.html
    - src/optimizatsiq.html
    - src/problem-stari.html
    - src/profilaktika-laptop.html
    - src/rezervni-chasti.html
    - src/test-laptop.html
    - src/tokov-udar.html
    - src/uslovia.html
    - src/warrently.html
    - src/za-bateriite.html
    - src/zalivane-technosti.html
  modified: []

key-decisions:
  - "Placeholder heading text is the literal <title> tag text from each site-current page, not an invented per-page title. All 15 pages share the exact same generic <title> on the live site today (a pre-existing per-page-title gap in the current site itself) - this plan preserves that fact rather than fixing it, since fixing it is real-content work reserved for Phase 3"
  - "Did not implement redirect logic for covid.html or problem-stari.html despite their decision-pending disposition in 01-URL-INVENTORY.md, per the plan's explicit instruction - filename/URL preservation only in this phase"

patterns-established: []

requirements-completed: [SEO-04]

coverage:
  - id: D1
    description: "All 15 remaining live page filenames scaffolded locally under src/ using plan 01-01's proven two-include pattern, with placeholder headings sourced from each page's real site-current <title> text"
    requirement: "SEO-04"
    verification:
      - kind: e2e
        ref: "ls src/*.html | wc -l (17: index.html + 15 new + phptest.html, a pre-existing plan-01-01 local-only artifact not deployed); diff of src/*.html filename set (excluding phptest.html) against site-current/*.html (excluding the Google-verification file) - no output, full match"
        status: pass
    human_judgment: false
  - id: D2
    description: "All 16 live page URLs (index.html + the 15 new files) uploaded to public_html/new/ on bell.host.bg and live-verified to return HTTP 200 with non-zero body size, proving SEO-04's URL-preservation guarantee across the complete site surface"
    requirement: "SEO-04"
    verification:
      - kind: e2e
        ref: "curl -s -o <body> -w '%{http_code} %{size_download}' https://torin.bg/new/<file> looped over all 16 known filenames - all 16 returned 200 with size > 0 (index.html: 2279 bytes; the 15 new pages: 2369 bytes each)"
        status: pass
    human_judgment: false

duration: 15min
completed: 2026-08-05
status: complete
---

# Phase 1 Plan 5: Full Site URL-Preservation Rollout Summary

**All 16 of site-current's live page filenames now exist, live-verified with HTTP 200, under public_html/new/ on bell.host.bg using plan 01-01's proven PHP-include pattern**

## Performance

- **Duration:** 15 min
- **Started:** 2026-08-05T09:37:00+03:00 (approx.)
- **Completed:** 2026-08-05T09:52:00+03:00
- **Tasks:** 2
- **Files modified:** 15 (all created)

## Accomplishments

- Expanded plan 01-01's single-page tracer to the site's full URL surface: all 15 remaining live filenames (`about.html` through `zalivane-technosti.html`) now exist under `src/`, each using the identical `require_once(dirname(__FILE__) . '/includes/header.php')` / `footer.php` skeleton already proven to work end-to-end on `bell.host.bg`.
- Uploaded all 15 new files to `public_html/new/` and live-verified every one of the 16 known page URLs (the tracer's `index.html` plus these 15) returns HTTP 200 with a non-zero response body.
- Re-ran the filename-set diff between `src/` and `site-current/` post-upload as the final SEO-04 proof: identical 16/16 match (excluding the pre-existing, non-deployed `phptest.html` spike artifact from plan 01-01 and the unrelated Google site-verification file, neither of which is part of the live page surface).
- Left `covid.html` and `problem-stari.html` as plain filename-preserving skeletons with no redirect logic, per the plan's explicit scope boundary — their keep/retire disposition remains an open owner decision tracked in `01-URL-INVENTORY.md` and `STATE.md`.

## Task Commits

Each task was committed atomically:

1. **Task 1: Scaffold the remaining 15 page files using the proven include pattern** - `9e873fc` (feat)
2. **Task 2: Upload and live-verify all 16 pages preserve their exact URLs** - no local file changes (FTP upload + live HTTP verification only); reported here and in this SUMMARY commit

**Plan metadata:** (this commit, following SUMMARY.md write)

## Files Created/Modified

- `src/about.html` - Filename-preserved page skeleton using the proven include pattern
- `src/covid.html` - Filename-preserved page skeleton; disposition decision-pending per `01-URL-INVENTORY.md`, not resolved here
- `src/laptopi.html` - Filename-preserved page skeleton using the proven include pattern
- `src/mehanichni-problemi.html` - Filename-preserved page skeleton using the proven include pattern
- `src/msg.html` - Filename-preserved page skeleton using the proven include pattern
- `src/optimizatsiq.html` - Filename-preserved page skeleton using the proven include pattern
- `src/problem-stari.html` - Filename-preserved page skeleton; disposition decision-pending per `01-URL-INVENTORY.md`, not resolved here
- `src/profilaktika-laptop.html` - Filename-preserved page skeleton using the proven include pattern
- `src/rezervni-chasti.html` - Filename-preserved page skeleton using the proven include pattern
- `src/test-laptop.html` - Filename-preserved page skeleton; disposition keep-as-is per DIFF-01 (self-diagnostic tool)
- `src/tokov-udar.html` - Filename-preserved page skeleton using the proven include pattern
- `src/uslovia.html` - Filename-preserved page skeleton using the proven include pattern
- `src/warrently.html` - Filename-preserved page skeleton using the proven include pattern
- `src/za-bateriite.html` - Filename-preserved page skeleton using the proven include pattern
- `src/zalivane-technosti.html` - Filename-preserved page skeleton using the proven include pattern

## Decisions Made

- **Placeholder heading source:** Used each page's literal site-current `<title>` text rather than inventing new copy. All 15 pages carry the exact same generic site-wide `<title>` on the live site today — this is a pre-existing per-page-title gap in the current site (not something introduced or fixed by this plan); real per-page titles are Phase 3 content work.
- **No redirect/merge logic for `covid.html`/`problem-stari.html`:** Per the plan's explicit instruction, these two pages remain plain filename-preserving skeletons. Their disposition (keep/retire/merge) stays an explicit open owner decision, already tracked as a blocker in `STATE.md` and `01-URL-INVENTORY.md`.
- **Upload/verify script kept scratchpad-only, not committed:** `scripts/backup-live-site.sh` is a repeatable tool (re-run before every future deploy); this plan's upload step is a one-time migration action for 15 specific files, so its script was written to the session scratchpad (reusing the same secure credential-handling pattern) rather than added to the repo.

## Deviations from Plan

### Auto-fixed Issues

None — no bugs, missing critical functionality, or blocking issues encountered.

### Note on Task 1's file-count acceptance criteria

The plan's Task 1 acceptance criteria states "src/ contains exactly 16 .html files." In practice `src/` contains 17: the 16 live-page filenames (index.html + these 15) plus `src/phptest.html`, a pre-existing local-only spike-test artifact from plan 01-01 (committed in `87b61bd`, explicitly documented there as "kept locally only, not deployed"). This plan's tasks did not create, modify, or remove `phptest.html` — it predates this plan and is out of scope for its file list. The filename-SET match against `site-current/` (the actual SEO-04 guarantee) is exact at 16/16 once `phptest.html` (not part of `site-current/`) and `site-current/google1718743335455f1c.html` (a Google-verification file with no analog under `src/`) are excluded from the comparison, as verified by the post-upload diff in Task 2.

This is not tracked as a Rule-triggered deviation because nothing is broken: `phptest.html` was never uploaded to `public_html/new/`, does not appear in the live URL-preservation verification, and its presence has zero effect on SEO-04. Flagged here for transparency against the plan's literal wording only.

---

**Total deviations:** 0 auto-fixed
**Impact on plan:** None. Plan executed as written; the file-count note above documents a pre-existing artifact from a prior plan, not new scope.

## Issues Encountered

None.

## User Setup Required

None - no external service configuration required. All work targeted the isolated `public_html/new/` staging subfolder on the already-configured `bell.host.bg` host; no new accounts, API keys, or dashboard configuration were introduced.

## Next Phase Readiness

- SEO-04's URL-preservation guarantee is now proven for the site's complete 16-page URL surface (not just the tracer's single page) — Phase 2/3 visual and content work can proceed against this filename-stable foundation without re-verifying the mechanism.
- `covid.html` and `problem-stari.html` still need an explicit owner decision (keep/retire/merge) before any redirect logic is added — carried forward as an existing `STATE.md` blocker, unchanged by this plan.
- This is the last plan in Phase 1 (migration-safety-net-foundation) — all 5 plans are now complete, closing out the phase's PHP-include foundation, URL inventory, backup/rollback drill, off-site GitHub backup, and full-site URL-preservation rollout.

---
*Phase: 01-migration-safety-net-foundation*
*Completed: 2026-08-05*

## Self-Check: PASSED

All 15 created files confirmed present on disk under `src/`. Commit `9e873fc` confirmed present in `git log --oneline --all`. All 16 live page URLs confirmed returning HTTP 200 with non-zero body size via direct `curl` against `https://torin.bg/new/`. Filename-set diff between `src/*.html` (excluding the pre-existing `phptest.html`) and `site-current/*.html` (excluding the Google-verification file) confirmed empty (exact 16/16 match). Grepped `git diff` and the relevant portion of `git log -p` for password/credential material — none found; the only matches were narrative references to the word "password" in prior plans' own SUMMARY prose, not the actual secret.
