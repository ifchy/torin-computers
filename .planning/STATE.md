---
gsd_state_version: 1.0
milestone: v1.0
milestone_name: milestone
current_phase: 02
current_phase_name: design-system-information-architecture
status: executing
stopped_at: Completed 02-01-PLAN.md
last_updated: "2026-08-05T22:11:54.319Z"
last_activity: 2026-08-06
last_activity_desc: Phase 02 execution started
progress:
  total_phases: 2
  completed_phases: 1
  total_plans: 9
  completed_plans: 6
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-08-04)

**Core value:** A visitor with a specific repair problem must immediately see that Torin fixes exactly that, and find a clear path to contact the shop.
**Current focus:** Phase 02 — design-system-information-architecture

## Current Position

Phase: 02 (design-system-information-architecture) — EXECUTING
Plan: 2 of 4
Status: Ready to execute
Last activity: 2026-08-06 — Phase 02 execution started

Progress: [███████░░░] 67%

## Performance Metrics

**Velocity:**

- Total plans completed: 5
- Average duration: - min
- Total execution time: 0 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 01 | 5 | - | - |

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

## Deferred Items

Items acknowledged and carried forward from previous milestone close:

| Category | Item | Status | Deferred At |
|----------|------|--------|-------------|
| v2 requirements | PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01 | Deferred | Requirements definition (2026-08-04) |

## Session Continuity

Last session: 2026-08-05T22:11:41.653Z
Stopped at: Completed 02-01-PLAN.md
Resume file: None
