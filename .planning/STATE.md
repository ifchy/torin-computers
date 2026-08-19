---
gsd_state_version: 1.0
milestone: v1.0
milestone_name: milestone
current_phase: 03
current_phase_name: content-trust-signal-build-out
status: executing
stopped_at: Phase 03 Wave 2 merged (03-02); live verification pending deploy
last_updated: "2026-08-18T00:00:00.000Z"
last_activity: 2026-08-18
last_activity_desc: 03-02 merged (Wave 2 complete); CSS budget exhausted
progress:
  total_phases: 3
  completed_phases: 2
  total_plans: 23
  completed_plans: 16
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-08-04)

**Core value:** A visitor with a specific repair problem must immediately see that Torin fixes exactly that, and find a clear path to contact the shop.
**Current focus:** Phase 03 — content-trust-signal-build-out

## Current Position

Phase: 03 (content-trust-signal-build-out) — EXECUTING
Plan: 2 of 9 complete (03-01 verified, 03-02 merged); next is Wave 3 (03-03/04/05/06)
Status: Executing Phase 03
Last activity: 2026-08-18 — 03-01 deployed to staging and verified PASS at both viewports

Progress: [██████░░░░] 65%

## Performance Metrics

**Velocity:**

- Total plans completed: 14
- Average duration: - min
- Total execution time: 0 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 01 | 5 | - | - |
| 02 | 9 | - | - |

**Recent Trend:**

- Last 5 plans: -
- Trend: -

*Updated after each plan completion*
**Per-Plan Metrics:**

| Plan | Duration | Tasks | Files |
|------|----------|-------|-------|
| Phase 01 P02 | 18min | 2 tasks | 2 files |
| Phase 01 P01 | 18min | 2 tasks | 7 files |
| Phase 01 P4 | 55min | 2 tasks | 1 files |
| Phase 01 P03 | 20min | 2 tasks | 3 files |
| Phase 01 P05 | 15min | 2 tasks | 15 files |
| Phase 02 P01 | 55min | 2 tasks | 15 files |
| Phase 02 P02 | 24min | 2 tasks | 4 files |
| Phase 02 P03 | 62min | 2 tasks | 8 files |
| Phase 02 P04 | 40min | 3 tasks | 17 files |
| Phase 02 P05 | 20 min | 2 tasks | 2 files |
| Phase 02 P06 | 12 min | 2 tasks | 2 files |
| Phase 02 P08 | 50m | 3 tasks | 5 files |
| Phase 02 P09 | 40min | 3 tasks | 2 files |

## Accumulated Context

### Decisions

Decisions are logged in PROJECT.md Key Decisions table.
Recent decisions affecting current work:

- [Research]: PHP-include restructuring chosen over Astro/Node rebuild — solves the real maintainability defect (duplicated header/nav/footer) with zero new tooling and the lowest risk to existing URL/SEO continuity.
- [Roadmap]: Phase 1 isolates all migration-safety work (URL inventory, backup/rollback discipline, PHP-include foundation) before any visual rebuild starts, per research's top pitfall (broken URLs/lost rankings).
- [Roadmap]: Cutover folded into Phase 4 alongside hardening rather than its own phase — coarse granularity setting, and cutover has only one directly-owned requirement (MIGR-02); it is sequenced last regardless.
- [Phase ?]: MIGR-01 URL inventory: test-laptop.html resolved keep-as-is per DIFF-01; covid.html and problem-stari.html left decision-pending (owner sign-off required, not silently retired)
- [Phase ?]: PHP-as-.html handler on bell.host.bg requires AddHandler application/x-httpd-php52 (CloudLinux Alt-PHP version-specific name), not the generic application/x-httpd-php variants
- [Phase ?]: FTPS to bell.host.bg uses curl --ftp-ssl -k: TLS handshake succeeds but the shared-hosting wildcard cert (*.superhosting.bg) doesn't match the vanity hostname, so hostname verification is skipped while transport stays encrypted
- [Phase ?]: 01-04: GitHub off-site backup wired to private repo torin-computers, verified in sync with local main
- [Phase ?]: MIGR-03: scripts/backup-live-site.sh detects FTP directory-vs-file type from the LIST type column (not name heuristics) and percent-encodes remote paths -- both discovered necessary via live run against bell.host.bg (dotted directory name, spaced filename)
- [Phase ?]: 01-05: All 16 live page filenames scaffolded and live-verified under public_html/new/ - SEO-04 URL-preservation guarantee proven site-wide, completing Phase 1
- [Phase ?]: 02-01: mod_deflate + mod_expires ARE available on bell.host.bg — production CSS costs 6,268 B gzipped vs 14,322 B raw, so the 20 KB budget is a target, not a hard wire cost
- [Phase ?]: 02-01: FTPS to bell.host.bg must cap the data channel at TLS 1.2 — TLS 1.3 aborts any upload over ~16 KB with 451 AFTER reporting bytes sent, silently failing every binary asset
- [Phase ?]: 02-01: Sofia Sans V1 shaping gate passed 15/15 (bg default outlines vs .loclRUS) — Bulgarian letterforms confirmed independent of browser locl support
- [Phase ?]: closeAll() skips any disclosure whose aria-controls panel contains the button just used — the research JS would otherwise collapse the mobile panel when Услуги is tapped inside it
- [Phase ?]: The mobile nav panel is in flow but right-aligned under the hamburger, not full-width: display:contents would risk the nav landmark and an absolutely-positioned brand breaks if the logo is ever redrawn taller
- [Phase ?]: geo coordinates are emitted as JSON strings, not floats — schema.org accepts Text, and PHP 5.2 float serialisation risks precision artefacts
- [Phase ?]: The 02-RESEARCH V3 PHP-5.2 lint regex false-positives on any config subscript right of a =>, which the plan's own key_link requires — narrow it to short-array literals
- [Phase ?]: The three unpublished category page files are deferred to Phase 3 (D-23/D-25): torin_category_href() already routes cards and dropdown to homepage anchors, so publishing is create-a-file plus flip-a-boolean
- [Phase ?]: The category template guards the intro and the TRUST-03 warranty summary like optional blocks — D-24 puts them in the spine but D-25 assigns their content to Phase 3, and a spine slot shipping an empty heading is the thin-content shape the publish gate exists to prevent
- [Phase ?]: The 56px call-bar reserve is scoped with body:not(:has(.callbar)) rather than a positive :has() — :not() is non-forgiving, so a browser without :has() keeps the safe unconditional reserve instead of letting the bar occlude the homepage footer
- [Phase ?]: 02-05: CR-01/CR-02 closed by scoping the WINNING selector, never escalating the loser — .hero p (0,1,1) became .hero__inner > p:not(.trust-badge) (0,2,1), and the on-dark focus ring got a (0,3,0) per-surface group; no !important, no id selector, no @layer
- [Phase ?]: 02-05: a focus ring drawn with outline-offset is painted on the surface BEHIND the control, so its contrast is measured against that surface — never against the button fill the old comment reasoned about
- [Phase ?]: 02-05: .callbar is a position:fixed sibling of <main>, inside neither .hero nor .site-footer — it must be named explicitly in every dark-surface rule group; omitting it is what produced CR-02
- [Phase ?]: 02-05: 02-REVIEW.md's 5.16:1 and 8.7:1 focus-ring figures are THEME A values reported as if they were the shipping theme — recomputed Theme B measures 9.49:1 and 11.09:1
- [Phase ?]: 02-06: the no-script nav ships via a <noscript> override stylesheet, not by shipping the nav open and collapsing it with JS — the latter has an inherent flash on every one of 16 page loads and suppressing it needs a render-blocking capability marker, i.e. a second writer of nav state
- [Phase ?]: 02-06: with scripting blocked the ENTIRE five-item nav was hidden below 56.25rem, not just the six category links — header.php's old comment understated it, and an understated record makes a later phase close the wrong defect
- [Phase ?]: 02-06: the hamburger and Услуги button are display:none in the no-script rendering because each carries a collapsed-state ARIA attribute — left visible over an open list they would announce the opposite of what is rendered, which is worse than absent
- [Phase ?]: 02-06: IA-02 deliberately NOT flipped to Complete — every rendered no-script check is unrun (no automatable browser), and commit abd5ba8 already reverted this exact class of premature flip once
- [Phase ?]: G-02-1 closed with ?v=<filemtime> query-string stamping in PHP, not per-version file renames — rename is manual and every manual step is eventually skipped
- [Phase ?]: Sofia Sans woff2 preload deliberately NOT version-stamped: it must byte-match the @font-face src in base.css, and a query string defeats font-swap.js's *.woff2 block glob, which would blind plan 02-09's G-02-4 gate rather than fail it
- [Phase ?]: Staging text/css and both JavaScript media types bounded to max-age=300 as a second line of defence behind the stamp; Phase 4 raises this once proven in production (DESIGN-02)
- [Phase ?]: 02-09: no bold local() name form resolves in Chromium (Arial Bold, Arial-BoldMT, Helvetica Neue Bold, HelveticaNeue-Bold all reject despite the files being on disk) — only the FAMILY name resolves, so a metric-adjusted fallback can only be sourced from a regular face
- [Phase ?]: 02-09: the fallback ships as ONE font-weight:400 face, not two — declaring a 700 face from a regular local file suppresses synthetic bolding and renders the heading light (canvas ink 32694 vs 40347 synthesised, against Sofia 700's 40762)
- [Phase ?]: 02-09: size-adjust 97% shipped, 9 points below the measured 106% two-line cliff — margin deliberately on the narrow side, since one percent too wide costs a whole 36.8px line while too narrow costs nothing
- [Phase ?]: 02-09: an unresolved @font-face falls to the last-resort font, which is NARROWER than Sofia Sans and sets the hero h1 at exactly 73.6px — the target height, so a fully broken measurement reads as a perfect match; every scan iteration now carries a resolution guard

- [Phase 3]: 03-01: the plan's PHP-5.2 short-array gate regex `(=>[^;]*\]|\[\s*[^]]*=>)` is broken — it matches any array read to the right of `=>`, returning 5 hits on untouched jsonld.php, so it could never pass. Plans 03-02…03-09 inherit it; use `(=>|=)[[:space:]]*\[|return[[:space:]]+\[` instead.
- [Phase 3]: 03-01: `deploy-new.sh` is denied to subagents by the permission classifier — the user must run it via `!`. It is staging-only (`public_html/new/`, hardcoded), and since no local PHP interpreter exists the deploy IS the only PHP 5.2 syntax check available.
- [Phase 3]: 03-01 live-verified: served page returns 200 with keyword h1, breadcrumbs, urgent block, warranty term line and BreadcrumbList; svc-page probe PASS at 360x640 and 1440x900 (sectionCount 3 -> 8 is the positive control).

- [Phase 3]: CSS budget resolved by stripping comments at DEPLOY time (scripts/lib/strip-css-comments.py, wired into deploy-new.sh), not by deleting them. Source keeps every comment; the wire does not pay. Production CSS 22,714 -> 5,309 B gzipped; headroom went from -2,234 B (already over) to +15,171 B. Stripper is string-aware, not a regex: components.css:853 has content:"/" and one more character would let a regex truncate the file. Brace balance verified identical in all four stylesheets.
- [Phase 3]: 03-02: CSS transfer budget is EXHAUSTED — 20,453 of 20,480 B gzipped, 27 B left for seven remaining plans. Root cause measured: components.css is 60% comments by raw bytes; stripping them drops its gzip from 12,015 to 2,849 B. The comments ship because there is no build step.
- [Phase 3]: 03-02: Google rating badge is built and gated OFF (`gbp_badge_enabled => false` in site-config.php) plus an emptiness safety net. Enabling needs a boolean flip plus THREE values, not two — the Google Business Profile URL has never been captured in this repo.
- [Phase 3]: 03-02: plan-named DIFF photos were wrong — profilaktika3 is a dust-clogged heatsink not the infrared station, and baterry.jpg/baterry2.jpg are schematic diagrams unreadable at a 100x100 crop. profilaktika6.jpg is a GIF wearing a .jpg extension.
- [Phase 3]: 03-02: photo port used `jpegtran -copy none -optimize`, not the plan's `sips -q80` which would have INFLATED the set by 147 KB and added a second lossy generation.

### Pending Todos

None yet.

### Blockers/Concerns

- [Phase 1]: RESOLVED (01-01) — `.htaccess` `.html`-as-PHP behavior spike-verified live against `bell.host.bg`. Neither `AddType application/x-httpd-php` nor `AddHandler application/x-httpd-php5`/`php-script`/`application/x-httpd-php5` (AddType variant) worked (raw source served, or 500 Internal Server Error). The host runs CloudLinux "Alt-PHP" (`/opt/alt/php52/...` confirmed via `error_log`), which requires the fully-versioned handler name: `AddHandler application/x-httpd-php52 .html .htm`. This is now what `src/.htaccess` uses; live-verified via `curl https://torin.bg/new/phptest.html` → `PHP-IN-HTML-OK 5.2.17`.
- [Phase 1]: RESOLVED (01-01) — PHP version live-reconfirmed as **5.2.17** via direct `phpversion()` probe (not just the `X-Powered-By` header this time), executing through the confirmed `.html`-as-PHP handler. Composer availability still unconfirmed (no shell/SSH access to test `composer --version`). Open question carried to Phase 4: does a cPanel/control-panel login exist for `bell.host.bg`, separate from the FTP-only credentials in `filezilla-server-data.xml`? The host's CloudLinux Alt-PHP setup (confirmed this session) normally exposes PHP-version switching via cPanel's "MultiPHP Manager"/"Select PHP Version" — if a cPanel login exists, bumping PHP version for PHPMailer 6.x (Phase 4) is likely straightforward; if not, PHPMailer 6.x (which requires PHP ≥5.5) cannot be used and an older PHPMailer major version or alternative mail library must be selected instead (RESEARCH.md Pitfall B / Open Question 1).
- [Phase 3]: Category 6 (non-standard electrical equipment) has no existing content — needs direct owner input on scope before this content can be written.
- [Phase 3]: Google Business Profile status (active with reviews?) unverified — confirm before committing to the Google rating badge.
- [Phase 3/4]: Real price ranges, before/after photos, and turnaround-time commitments are v2 (deferred) but were owner-input-dependent gaps noted in research; not blocking v1.
- [Phase 4]: Holiday-banner script (`otpuska.js`) intent needs an explicit owner decision (keep with maintained equivalent, or drop).
- [Phase 1/3]: `covid.html`'s fate is undecided — pending-owner-decision, not silently retired. Content is slated to move to the About page per CONTENT-02 (Phase 3), but retiring the currently-indexed URL itself needs explicit owner sign-off before doing so, since an unreviewed retirement risks losing existing ranking/traffic. See `01-URL-INVENTORY.md`.
- [Phase 1]: `problem-stari.html`'s fate is undecided — pending-owner-decision. No current v1 requirement addresses this page; suspected content overlap with `mehanichni-problemi.html` is unconfirmed. Flag for owner review before any retire/merge/redirect decision is made. See `01-URL-INVENTORY.md`.
- [Phase 2] Web-font swap backstop is observed but UNMITIGATED: cold-cache first paint shows a brief flash of invisible text (font-display: swap's block period). No layout shift (CLS 0.00053). Closing it properly means size-adjusting the fallback stack — deliberately deferred, not fixed in 02-01.
- [Phase 2] UI-SPEC's above-the-fold arithmetic assumed the hero min-height would bind, but the two CTA labels (178.2px + 189.4px) cannot share a 328px content box at 360px, so the CTA block is intrinsically two rows. Plans adding hero content must re-check the 42% budget.
- OWNER-QUESTIONS #20 (working hours) and #21 (chat-capable number) block the Phase 4 cutover: both ship on all 16 pages and #20 also feeds Google's structured data
- [Phase 2] 02-05 rendered-geometry and keyboard human-checks are UNRUN: no automatable browser on the build machine (no Chrome/Chromium/Playwright; Safari remote automation disabled behind a GUI-only setting). The hero-stack figure in components.css is DERIVED (241.6px), not measured, and the FOUT backstop is re-opened by the 8px stack change.
- [Phase 2] 02-06 rendered no-script checks are UNRUN (same no-browser blocker as 02-05): nav visibility/activation at 360/900/1440px, header focus order, and scrollWidth<=innerWidth with scripting disabled. The UI-SPEC overflow backstop is RE-OPENED by 02-06's desktop layout and abstains to human_needed. Also open: 'flex: 1 0 100%' on the mid-list has-sub item splits the visible top-level links across two wrapped rows rather than one.

## Deferred Items

Items acknowledged and carried forward from previous milestone close:

| Category | Item | Status | Deferred At |
|----------|------|--------|-------------|
| v2 requirements | PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01 | Deferred | Requirements definition (2026-08-04) |

## Session Continuity

Last session: 2026-08-18
Stopped at: Phase 03 Wave 1 complete and verified (03-01 merged at 53b421e; deploy authorized and run, live verification PASSED at both viewports). Resuming at Wave 2 (03-02).
Resume file: .planning/phases/03-content-trust-signal-build-out/03-02-PLAN.md