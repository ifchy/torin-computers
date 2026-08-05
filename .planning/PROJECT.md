# Torin Computers Website Redesign

## What This Is

A redesign of torin.bg — the website for ТОРИН КОМПЮТЪРС, a Bulgarian computer/laptop/electronics repair shop. The current site is a static HTML page built years ago on a purchased jQuery/Bootstrap template ("Liquid") and looks visibly outdated next to competitors. This project modernizes the look and feel, closes feature/content gaps versus the top Bulgarian repair-shop competitors, and keeps the site fully in Bulgarian.

## Core Value

A visitor with a specific problem (cracked screen, spilled liquid, dead motherboard, drained battery...) must immediately see that Torin fixes exactly that, and find a clear path to contact the shop. If the redesign doesn't make that obvious faster than the competition does, it hasn't worked.

## Business Context

- **Customer**: Bulgarian consumers and small businesses needing laptop/PC/electronics repair, who land on torin.bg
- **Revenue model**: Paid repair services (not e-commerce) — the site drives inquiries/calls, not checkout
- **Success metric**: Visitors can immediately identify Torin can solve their specific repair problem and contact the shop
- **Strategy notes**: —

## Requirements

### Validated

<!-- The current live site already does the following — these are existing, working capabilities, not aspirational -->

- ✓ Site is live at torin.bg on shared hosting (bell.host.bg), reachable and indexed — existing
- ✓ Bulgarian-language content covering: laptops (laptopi.html), maintenance (profilaktika-laptop.html), optimization (optimizatsiq.html), mechanical problems (mehanichni-problemi.html), batteries (za-bateriite.html), power surge damage (tokov-udar.html), liquid damage (zalivane-technosti.html), spare parts (rezervni-chasti.html), warranty (warrently.html), terms (uslovia.html), about (about.html) — existing
- ✓ Contact form / mailer (mailer.php, msg.html) — existing

### Active

<!-- Redesign scope. All hypotheses until shipped and validated against the "core value" above. -->

- [ ] Visual design modernized to be competitive with leading Bulgarian repair-shop websites (research-informed gap list)
- [ ] Clear, prominent presentation of the services the owner wants stressed:
  - ремонт на счупвания (breakage repair)
  - смяна на матрици, клавиатури, USB портове, захранващи букси, панти (screen/keyboard/USB-port/power-jack/hinge replacement)
  - оптимизация (optimization)
  - заляти и повредени дънни платки (liquid-damaged / damaged motherboards)
  - смяна на вентилатори (fan replacement)
  - сервиз на нестандартно ел. оборудване (non-standard electrical equipment servicing)
- [ ] All content remains in Bulgarian (primary and only language)
- [ ] Mobile-responsive layout (current site's responsiveness has not been verified as adequate)
- [ ] Clear, prominent call-to-action / contact path on every page
- [ ] Redesign deploys to the existing FTP/shared hosting (bell.host.bg) — no hosting migration
- [ ] Competitive gap analysis against top Bulgarian computer/laptop repair competitors, used to drive what content/features get added

### Out of Scope

- Hosting/infrastructure migration — user confirmed staying on existing FTP/shared hosting (bell.host.bg)
- E-commerce / online checkout — business is service-based, not product sales
- Non-Bulgarian language versions — site is Bulgarian-only by explicit requirement

## Context

- **Live site**: torin.bg, hosted on shared hosting at `bell.host.bg` (Pure-FTPd), FTP user `torin`, remote path `public_html`
- **Current tech**: Static HTML/CSS/JS, built on a purchased "Liquid" theme (jQuery + Bootstrap-era vendor libraries: jQuery UI, Font Awesome, ScrollMagic, pagePiling, etc.)
- **Source pulled locally**: Full FTP mirror of the live site downloaded into `site-current/` (committed to git as the baseline reference) — 215 files, ~15MB, includes all HTML pages, CSS, JS, images, fonts
- **FTP access**: Credentials live in `filezilla-server-data.xml` in the project root (gitignored — contains real hosting passwords, never commit this file). Two saved server entries exist in that file; the one for torin.bg is named "TORIN" (host `bell.host.bg`, user `torin`)
- **Rebuild approach decided**: PHP-include restructuring on the existing host (shared `header.php`/`footer.php`/`site-config.php` via native PHP `include()`), not a Node/Astro rebuild — chosen because it fully solves the actual technical debt (duplicated markup across 16 pages) with zero new tooling/build step, and makes URL preservation the default behavior rather than something to configure around (the project's single biggest risk). Full rationale in `.planning/research/SUMMARY.md` §"Resolving the Stack/Architecture Tension". Proven live end-to-end on bell.host.bg in Phase 1.
- **Company name variants seen in source**: "ТОРИН КОМПЮТЪРС" (site title), domain torin.bg
- **Phase 1 complete (2026-08-05)**: Migration safety net proven — full 16-page URL inventory, pre-deploy backup script (exercised live, ~12MB snapshots), git-based rollback drill exercised, off-site GitHub mirror wired (`github.com/ifchy/torin-computers`), and the PHP-include foundation live-verified on bell.host.bg (`public_html/new/`, CloudLinux Alt-PHP handler `application/x-httpd-php52`). All 16 live filenames preserved byte-identical. Next: Phase 2 (design system/IA).

## Constraints

- **Language**: All content must be in Bulgarian — no other language versions in scope
- **Hosting**: Must deploy to the existing FTP/shared hosting at `bell.host.bg` — no infrastructure migration
- **Content emphasis**: The six service categories listed under Active Requirements must be prominently featured — this was explicit owner direction, not inferred

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Pulled live site via FTP into `site-current/` as a working baseline | Needed the real current source to redesign against, not assumptions | ✓ Good |
| `filezilla-server-data.xml` excluded from git via `.gitignore` | Contains real hosting passwords in reversible base64 encoding | ✓ Good |
| Rebuild approach: PHP-include reskin-in-place, not an Astro/Node rebuild | Fully solves the real technical debt (duplicated markup) with zero new tooling, and makes URL preservation (the biggest risk) the default rather than something to configure around | ✓ Good — proven live on bell.host.bg in Phase 1 |
| Keep existing FTP/shared hosting for deploy | Owner confirmed — avoids infra migration scope creep | ✓ Good |
| Competitor set to be identified by research, not owner-supplied | Owner has no specific competitors in mind, wants a market scan | — Pending |
| All Phase 1-4 build work happens in `public_html/new/` on the live host, swapped to root only at Phase 4 cutover | Lets the owner watch progress live at `torin.bg/new` without touching the real site (D-01/D-02, 01-CONTEXT.md) | ✓ Good |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd-transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd-complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-08-05 after Phase 1 completion*
