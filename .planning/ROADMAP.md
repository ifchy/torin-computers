# Roadmap: Torin Computers Website Redesign

## Overview

Torin.bg is being rebuilt in place on its existing FTP/shared host, moving from a dated jQuery/parallax "Liquid" theme to a modern, mobile-responsive PHP-include site — without losing a single day of the SEO ranking it has accumulated on its current URLs. The journey starts with a migration safety net (URL inventory, rollback discipline, proven technical foundation) before any visual work begins, because the single biggest risk in this project is a redesign that silently breaks what already ranks. From there, a new design system and six-category information architecture replace the undifferentiated icon-box scroll, all sixteen pages get rebuilt with correct SEO metadata and the shop's genuine (currently buried) trust signals and differentiators, and the project finishes with contact-path hardening, performance/SEO plumbing, and a carefully verified FTP cutover that confirms nothing broke.

## Phases

**Phase Numbering:**

- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Migration Safety Net & Foundation** - Lock down URL/ranking continuity and rollback discipline, and prove the PHP-include foundation on the real host, before any rebuild work touches the live site. (completed 2026-08-05)
- [x] **Phase 2: Design System & Information Architecture** - Replace the dated jQuery/parallax theme with a modern, mobile-responsive design organized around the six owner-priority service categories. (completed 2026-08-09)
- [x] **Phase 3: Content & Trust-Signal Build-Out** - Rebuild all sixteen pages with correct SEO metadata and surface the shop's genuine trust signals and differentiators that no competitor has. (delivered 2026-08-26; DIFF-02/DIFF-03 superseded 2026-09-11 — see Phase 3.5)
- [ ] **Phase 3.5: Content Truth Revision** - Remove every claim describing a discontinued service, retire three URLs by redirect, and correct the category boundaries so the site describes the business that exists today.
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

**Plans**: 9/9 plans executed
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

**Wave 8** *(gap closure from UAT — blocked on Wave 7; the two plans touch disjoint files and run in parallel)*

- [x] 02-08-PLAN.md — G-02-1 CSS cache invalidation: `?v=<filemtime>` on every stylesheet and script URL, staging-bounded cache lifetime, and a committed non-cache-busted gate over all sixteen pages
- [x] 02-09-PLAN.md — G-02-4 web-font swap reflow: probe-calibrated metric-adjusted fallback face so the hero heading sets the same line count before and after the swap

**UI hint**: yes

### Phase 3: Content & Trust-Signal Build-Out

**Goal**: All sixteen pages are rebuilt with correct, unique SEO metadata and surface the shop's genuine trust signals and differentiators — assets no competitor currently offers — instead of leaving them buried in text or absent entirely.

> **⚠ SUPERSEDED IN PART, 2026-09-11.** Phase 3 was executed in full and live-verified: 23 pages, all
> 200, zero PHP warnings, SEO-01 closed, every probe passing. The work is not in question — the
> *specification* changed underneath it. Battery regeneration and BGA/chip-level repair were
> discontinued by the business (OWNER-QUESTIONS #31), retiring **DIFF-02** and **DIFF-03**, both of
> which had been verified Complete on 2026-08-26. Success criterion 4 below therefore describes two
> services the shop no longer offers. **Phase 3.5 carries out the revision**; this phase closes on
> what it actually delivered, and is not re-opened.

**Depends on**: Phase 2
**Requirements**: TRUST-01, TRUST-02, TRUST-03, DIFF-01, ~~DIFF-02~~, ~~DIFF-03~~, CONTENT-01, CONTENT-02, SEO-01
**Success Criteria** (what must be TRUE):

  1. A visitor sees a brand-logo row (Lenovo, HP, Dell, Asus, Acer, Apple, MSI, etc.) confirming which hardware brands Torin services.
  2. A visitor sees a Google rating badge linking to Torin's Google Business Profile reviews.
  3. A visitor sees warranty terms summarized directly on relevant service pages, not only buried in the standalone warranty page.
  4. ~~A visitor sees the self-diagnostic tool, the battery-regeneration story, and the BGA/chip-level repair expertise each surfaced as distinct, visually prominent content rather than buried in nav or paragraph text.~~ — **delivered as specified, then two thirds superseded 2026-09-11.** Only the self-diagnostic tool (DIFF-01) remains a live claim; the other two describe discontinued services and are removed in Phase 3.5.
  5. A visitor sees dedicated content for the non-standard-electrical-equipment category as one of the six headline services, and no longer sees EU-project/COVID content competing for attention on the homepage.
  6. Every page has a unique `<title>` and `<meta name="description">` that accurately reflects its own content, replacing the current identical/empty values across all 16 pages.

**Plans**: 9/9 plans executed

Plans:
**Wave 1**

- [x] 03-01-PLAN.md — Generalize the category template into a service-page renderer (h1 override, structured blocks, keyed warranty, breadcrumbs) and prove it end-to-end on category 4

**Wave 2** *(blocked on Wave 1 completion)*

- [x] 03-02-PLAN.md — Brand row, Google rating badge, and the battery and chip-level differentiator surfaces, single-sourced across the homepage and every service page

**Wave 3** *(blocked on Wave 2 completion)*

- [x] 03-03-PLAN.md — Split category 2 into a routing hub plus five children; ship the hub and the first two
- [x] 03-04-PLAN.md — The three differentiator depth pages: battery regeneration, self-diagnostic routing, chip-level repair evidence
- [x] 03-05-PLAN.md — Category 6 on its existing indexed URL, the surge-damage page, and About with the EU disclosure relocated off the homepage
- [x] 03-06-PLAN.md — Port the six legal and utility pages, fixing what is visibly wrong and leaving the compliance text alone

**Wave 4** *(blocked on Wave 3 completion)*

- [x] 03-07-PLAN.md — The remaining three category-2 child pages; open the publish gate on all five
- [x] 03-08-PLAN.md — The last three category pages to the Definition of Done; publish category 5

**Wave 5** *(blocked on Wave 4 completion)*

- [x] 03-09-PLAN.md — Site-wide SEO-01 closure: the uniqueness/ordering/non-empty gate over every served page, the homepage's metadata, and the shortened brand-suffix pass

**Cross-cutting constraints:**

- Each of the three pages shows the shared warranty summary on the page itself (TRUST-03)

**UI hint**: yes

### Phase 3.5: Content Truth Revision

**Goal**: Every factual claim on the site describes a service the shop actually offers today. No page advertises battery regeneration, BGA/reballing or product sales; the three retired URLs redirect rather than 404; and the category boundaries match how the shop actually divides the work.
**Depends on**: Phase 3
**Requirements**: DIFF-04, CONTENT-01, SEO-05, TRUST-01 (correction), TRUST-02 (enable)
**Success Criteria** (what must be TRUE):

  1. No page makes a **battery-regeneration** claim, and no page makes a **BGA / reballing / chip-level** claim.
     > **Figure corrected 2026-09-13 (03.5-TRUTH-AUDIT.md).** The bare "57 instances across 9 files" that stood here carried no token set and no method — the exact defect this phase exists to fix. Reproducible forms: **57 in 9 files** with `grep -roiE 'BGA|реболинг|ребол|чипсет|видеочип|инфрачервен|AMTECH|дозапояване|северен мост|южен мост' --include='*.html'` over `src/` at `98994e3`; **95 in 11 files** adding `|ниво чип|регенерац`; **165 in 21 files** from `node scripts/truth-gate.js`, whose wider token set and 42-file scope are printed on every run. All three are correct for their token set **and method** — case folding alone moves the 95 to 83.


  2. **Category 4** («Заливане и ремонт на дънни платки») describes what the shop still does: liquid cleaning, corrosion removal, non-BGA component-level soldering, and board replacement where the case calls for it — and reads as a complete service page, not a page with holes where claims were cut.
  3. The three **reballing photographs** (`profilaktika17.jpg` infrared station, `profilaktika7.jpg` pads before new balls, `profilaktika15.jpg` hot-air-gun damage) no longer appear on any page, and no evidence strip renders short or empty as a result.
  4. `za-bateriite.html`, `laptopi.html` and `rezervni-chasti.html` each return a **301 redirect** to a relevant destination — never a 404 — and no internal link still points at them.
  5. **Category 1 and 2** are divided by failure type, not component: category 1 is «има видима повреда» (physical/mechanical), category 2 is «изглежда здрав, но не работи» (electronic). The `svc-panti` parent inconsistency and the «пукнат екран» misplacement are both resolved, and the five component child pages are reachable from whichever category applies.
  6. The brand row no longer names **Apple** or **Chromebook**.
  7. «Безплатна диагностика» reads «безплатна **първоначална** диагностика» everywhere, and both it and the warranty summary state that **category 6 is excluded**.
  8. The **Google rating badge is live** — 4.7, a rounded review count with a «+», and the verified profile URL — on the homepage and every service page.
  9. Every page still returns 200 with zero PHP warnings, and the SEO-01 uniqueness gate still passes across the whole tree.

**Cross-cutting constraints:**

- Retirement is **301, never deletion** — all three URLs are among the original 16 indexed pages (SEO-04's intent).
- **Invent nothing.** Where a cut leaves a page thin, either restructure honestly or record the gap against OWNER-QUESTIONS — do not fill it with plausible-sounding replacement copy. This is the rule Category 6 was built under and it holds here.
- The **live deploy is the only PHP check** — no local interpreter exists. A page returning 200 proves nothing on its own.

**Plans**: 7/7 plans executed — **the phase is still NOT complete; see the note below**

Plans:
**Wave 1**

- [x] 03.5-01-PLAN.md — Tracer: rating badge live, brand row corrected, three URLs retired by 301, and `scripts/truth-gate.js` — proven end-to-end through config, shared chrome and `.htaccess`

**Wave 2** *(blocked on Wave 1 completion)*

- [x] 03.5-02-PLAN.md — Homepage: the two retired differentiator sections removed, the category 1/2 boundary redrawn, the evidence gate made opt-in
- [x] 03.5-03-PLAN.md — Category 4 re-scoped to liquid cleaning, corrosion removal, non-BGA component soldering and board replacement
- [x] 03.5-04-PLAN.md — The two thermal pages, and the three reballing photographs withdrawn from the repository
- [x] 03.5-05-PLAN.md — About, surge damage and category 6: component-level vocabulary, the category-6 carve-outs, and the gaps filed against OWNER-QUESTIONS
- [x] 03.5-06-PLAN.md — The component pages, the last reworded diagnostics claims, and the gate-safe stylesheet comment

**Wave 3** *(blocked on Wave 2 completion)*

- [~] 03.5-07-PLAN.md — Tree-wide truth audit, the full live deploy and verification sweep, and `03.5-TRUTH-AUDIT.md`
  - **Task 1 COMPLETE** — every tree-wide static assertion run; `03.5-TRUTH-AUDIT.md` written.
  - **Task 2 COMPLETE — the blocking human checkpoint is CLOSED (2026-09-15).** The deploy is denied to subagents *and* to the orchestrator, so **the user ran it** (24 files); the orchestrator ran all eight live measurements. 19/19 pages at `200 warn=0`; served Class-A tokens **0** on all 19; all four redirects **301 → 200 in one hop**; every evidence strip correct; badge and brand row live on three pages; the keyed category-6 warranty carve-out rendering; all six rendered probes at expectation.
  - **Task 3 COMPLETE** — `03.5-TRUTH-AUDIT.md`, `REQUIREMENTS.md`, `ROADMAP.md` and `STATE.md` all reconciled to the measured evidence.

> **⚠ PHASE 3.5: NINE OF NINE SUCCESS CRITERIA MET. One requirement pair remains open.**
> SC-3, SC-7, SC-8 and SC-9 were promoted from PARTIAL on measurement. **SC-1 was closed by gap-closure plan 03.5-08** (see below). What still keeps the phase from closing is not a measurement:
>
> 1. ✅ **CLOSED — the open defect is fixed and live-verified (03.5-08, 2026-09-15).** `src/remont-na-portove.html:103` had published a claim to chip replacement, discontinued under D3.5-01, on a page no plan owned and that `truth-gate.js` reports zero on. The remedy clause was replaced with board replacement — wording reused from `test-laptop.html` and `zalivane-technosti.html`, nothing composed — while the mechanism, the cause and the escalation warning survived intact. **Live after redeploy: cure-claim served = 0, page `200 warn=0`, `svc-page` probe PASS, tree-wide chip-replacement claims = 0.** The declined-bridge sweep reads **1**, not 0: that is the surviving legitimate *cause*, the same rule under which `tokov-udar.html` reads 3 and is MET. **SC-1 is MET.**
> 2. **DIFF-04 and CONTENT-01 remain ADVANCED, NOT MET.** `kat-6` is still `'published' => false`, blocked on OWNER-QUESTIONS #3a–#3f plus riders #32 and #33. The page renders correctly live; a page that renders perfectly and is not published is still not published. **This is an owner decision, not engineering work.**
>
> See `03.5-TRUTH-AUDIT.md` for the full evidence record — every figure with the exact command that emitted it — and the **thirteen** findings (F1–F13; the one live-published defect among them is closed by 03.5-08). **Seven of the thirteen are defects in verification commands rather than in the site**, and four of those fabricate a defect on a page that is correct.

### Phase 4: Hardening & Cutover

**Goal**: The contact path is secure and frictionless, page performance and SEO plumbing are solid, and the redesigned site goes live on the existing host with zero loss of URL or ranking continuity.
**Depends on**: Phase 3
**Requirements**: CONTACT-01, ~~CONTACT-02~~, CONTACT-03, CONTACT-04, CONTACT-05, CONTACT-06, OWNER-01, OWNER-02, ANALYTICS-01, DESIGN-02, SEO-03, MIGR-02
**Success Criteria** (what must be TRUE):

  1. A visitor can tap-to-call any of the shop's phone numbers from mobile. ~~and click to start a WhatsApp or Viber chat~~ — **Viber dropped 2026-09-11** in favour of the contact form (CONTACT-02 retired); no Viber affordance ships, and the form is the written-contact path.
  2. A visitor's contact-form submission — carrying name, phone, email, device model, fault description and photographs — is protected by a honeypot, gated by explicit privacy consent, and delivered via authenticated SMTP/PHPMailer, and the unstaffed Zendesk chat widget is gone from every page.
  2a. The shop owner can change published **working hours** and schedule a **holiday banner** by editing a file, with the banner disabled by default and auto-expiring.
  2b. The shop can see which **contact actions** are used and which **service pages** are visited.

  3. A visitor experiences fast mobile page loads, with all images optimized/compressed and no jQuery/ScrollMagic/pagePiling overhead remaining anywhere on the site.
  4. `robots.txt` and `sitemap.xml` exist on the live site and have been submitted to Search Console.
  5. The redesigned site is live at torin.bg on `bell.host.bg` at the same URLs, with `.htaccess`, the Search Console verification file, favicon, and `robots.txt` all confirmed intact post-cutover, and Search Console shows no new 404s or ranking drops in the days following launch.

**Plans**: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 3.5 → 4

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Migration Safety Net & Foundation | 5/5 | Complete    | 2026-08-05 |
| 2. Design System & Information Architecture | 9/9 | Complete    | 2026-08-09 |
| 3. Content & Trust-Signal Build-Out | 9/9 | Built — partly superseded | 2026-08-26 |
| 3.5. Content Truth Revision | 6.5/7 | In Progress — audit delivered; live sweep + 1 open defect outstanding |  |
| 4. Hardening & Cutover | 0/TBD | Not started | - |
