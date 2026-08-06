# Roadmap: Torin Computers Website Redesign

## Overview

Torin.bg is being rebuilt in place on its existing FTP/shared host, moving from a dated jQuery/parallax "Liquid" theme to a modern, mobile-responsive PHP-include site — without losing a single day of the SEO ranking it has accumulated on its current URLs. The journey starts with a migration safety net (URL inventory, rollback discipline, proven technical foundation) before any visual work begins, because the single biggest risk in this project is a redesign that silently breaks what already ranks. From there, a new design system and six-category information architecture replace the undifferentiated icon-box scroll, all sixteen pages get rebuilt with correct SEO metadata and the shop's genuine (currently buried) trust signals and differentiators, and the project finishes with contact-path hardening, performance/SEO plumbing, and a carefully verified FTP cutover that confirms nothing broke.

## Phases

**Phase Numbering:**

- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Migration Safety Net & Foundation** - Lock down URL/ranking continuity and rollback discipline, and prove the PHP-include foundation on the real host, before any rebuild work touches the live site. (completed 2026-08-05)
- [ ] **Phase 2: Design System & Information Architecture** - Replace the dated jQuery/parallax theme with a modern, mobile-responsive design organized around the six owner-priority service categories.
- [ ] **Phase 3: Content & Trust-Signal Build-Out** - Rebuild all sixteen pages with correct SEO metadata and surface the shop's genuine trust signals and differentiators that no competitor has.
- [ ] **Phase 4: Hardening & Cutover** - Harden the contact path, tighten performance and SEO plumbing, and cut over the live site with verified URL/ranking continuity.

## Phase Details

### Phase 1: Migration Safety Net & Foundation

**Goal**: Before any rebuild work touches the live site, a complete safety net guarantees URL/ranking continuity and rollback capability, and the PHP-include technical foundation is proven to work on the actual host.
**Depends on**: Nothing (first phase)
**Requirements**: MIGR-01, MIGR-03, SEO-04
**Success Criteria** (what must be TRUE):

  1. A complete inventory of all 16 live page URLs exists and has been cross-checked against Google Search Console, with each URL's fate (kept as-is / retired) documented.
  2. The new page templates are built to use the exact same filenames/URLs as the live site — no visitor-facing link will change through the redesign.
  3. A pre-deploy backup-and-rollback process is established and proven: a full local backup of the live site exists, git is the source of truth, and the process has been exercised at least once before real content work begins.
  4. The PHP-include foundation (shared `header.php`/`footer.php`/`site-config.php`, `.htaccess` behavior for serving `.html` as PHP) is scaffolded and verified working via local preview against the real host's configuration.

**Plans**: 5/5 plans executed
Plans:
**Wave 1**

- [x] 01-01-PLAN.md — Tracer: PHP-include foundation, .htaccess spike + canonicalization, live-verified on bell.host.bg
- [x] 01-02-PLAN.md — Full 16-page URL inventory with per-page disposition (GSC-substitute method)
- [x] 01-03-PLAN.md — Backup script exercised once + git-based rollback drill
- [x] 01-04-PLAN.md — Wire private GitHub remote as off-site backup (checkpoint: repo creation)

**Wave 2** *(blocked on Wave 1 completion)*

- [x] 01-05-PLAN.md — Scaffold + live-verify the remaining 15 page filenames

**UI hint**: no

### Phase 2: Design System & Information Architecture

**Goal**: Visitors see a modern, mobile-responsive site organized around the six owner-priority service categories, replacing the outdated jQuery/parallax "Liquid" theme and its undifferentiated scroll of icon boxes.
**Depends on**: Phase 1
**Requirements**: DESIGN-01, IA-01, IA-02, SEO-02
**Success Criteria** (what must be TRUE):

  1. Every page renders with the new responsive design system, with no dependency on ScrollMagic, pagePiling, or jQuery UI, and displays correctly on mobile and desktop viewports.
  2. The homepage presents services as six clearly distinct category sections instead of one long scroll of roughly 18 undifferentiated icon boxes.
  3. A visitor can reach any part of the site via a flat, shallow navigation structured around the six categories, with no dense mega-menu.
  4. Every page declares `lang="bg"` and all Cyrillic text renders correctly in the new typography.

**Plans**: 7/7 plans executed
Plans:
**Wave 1**

- [x] 02-01-PLAN.md — Tracer: design system foundation (font, tokens, CSS, shared chrome, dev theme switcher) proven end-to-end on the live homepage

**Wave 2** *(blocked on Wave 1)*

- [x] 02-02-PLAN.md — Six-category information architecture: categories data file, card grid, catch-all disclosure, self-diagnostic block, CTA

**Wave 3** *(blocked on Wave 2)*

- [x] 02-03-PLAN.md — Five-item nav with Услуги disclosure, contact-first footer, promoted phone list, LocalBusiness JSON-LD

**Wave 4** *(blocked on Wave 3)*

- [x] 02-04-PLAN.md — Site-wide rollout: 15-page metadata pass, category page template, full live verification sweep

**Wave 5** *(gap closure — blocked on Wave 4)*

- [x] 02-05-PLAN.md — Tracer: close the two CSS cascade defects (trust-badge contrast CR-01, dark-surface focus-ring contrast CR-02), proven against the live response

**Wave 6** *(gap closure — blocked on Wave 5)*

- [x] 02-06-PLAN.md — No-script navigation: conditional override stylesheet making all five items and all six categories reachable without JavaScript, plus the corrected record (WR-08)

**Wave 7** *(gap closure — blocked on Wave 6)*

- [x] 02-07-PLAN.md — Reachability and contact single-sourcing: discreet footer link to covid.html (D-35), one E.164 phone key read by every CTA and the structured data (WR-10)

**UI hint**: yes

### Phase 3: Content & Trust-Signal Build-Out

**Goal**: All sixteen pages are rebuilt with correct, unique SEO metadata and surface the shop's genuine trust signals and differentiators — assets no competitor currently offers — instead of leaving them buried in text or absent entirely.
**Depends on**: Phase 2
**Requirements**: TRUST-01, TRUST-02, TRUST-03, DIFF-01, DIFF-02, DIFF-03, CONTENT-01, CONTENT-02, SEO-01
**Success Criteria** (what must be TRUE):

  1. A visitor sees a brand-logo row (Lenovo, HP, Dell, Asus, Acer, Apple, MSI, etc.) confirming which hardware brands Torin services.
  2. A visitor sees a Google rating badge linking to Torin's Google Business Profile reviews.
  3. A visitor sees warranty terms summarized directly on relevant service pages, not only buried in the standalone warranty page.
  4. A visitor sees the self-diagnostic tool, the battery-regeneration story, and the BGA/chip-level repair expertise each surfaced as distinct, visually prominent content rather than buried in nav or paragraph text.
  5. A visitor sees dedicated content for the non-standard-electrical-equipment category as one of the six headline services, and no longer sees EU-project/COVID content competing for attention on the homepage.
  6. Every page has a unique `<title>` and `<meta name="description">` that accurately reflects its own content, replacing the current identical/empty values across all 16 pages.

**Plans**: TBD
**UI hint**: yes

### Phase 4: Hardening & Cutover

**Goal**: The contact path is secure and frictionless, page performance and SEO plumbing are solid, and the redesigned site goes live on the existing host with zero loss of URL or ranking continuity.
**Depends on**: Phase 3
**Requirements**: CONTACT-01, CONTACT-02, CONTACT-03, CONTACT-04, DESIGN-02, SEO-03, MIGR-02
**Success Criteria** (what must be TRUE):

  1. A visitor can tap-to-call any of the shop's phone numbers and click to start a WhatsApp or Viber chat directly from mobile.
  2. A visitor's contact-form submission is protected by a honeypot and delivered via authenticated SMTP/PHPMailer, and the unstaffed Zendesk chat widget is gone from every page.
  3. A visitor experiences fast mobile page loads, with all images optimized/compressed and no jQuery/ScrollMagic/pagePiling overhead remaining anywhere on the site.
  4. `robots.txt` and `sitemap.xml` exist on the live site and have been submitted to Search Console.
  5. The redesigned site is live at torin.bg on `bell.host.bg` at the same URLs, with `.htaccess`, the Search Console verification file, favicon, and `robots.txt` all confirmed intact post-cutover, and Search Console shows no new 404s or ranking drops in the days following launch.

**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Migration Safety Net & Foundation | 5/5 | Complete    | 2026-08-05 |
| 2. Design System & Information Architecture | 7/7 | In Progress|  |
| 3. Content & Trust-Signal Build-Out | 0/TBD | Not started | - |
| 4. Hardening & Cutover | 0/TBD | Not started | - |
