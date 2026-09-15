# Requirements: Torin Computers Website Redesign

**Defined:** 2026-08-04
**Core Value:** A visitor with a specific repair problem immediately sees that Torin fixes it, and finds a clear path to contact the shop.

## v1 Requirements

Requirements for the redesign launch. Each maps to roadmap phases.

### Design & Performance

- [x] **DESIGN-01**: User sees a modern, mobile-responsive layout across all pages, replacing the current heavy jQuery/parallax "Liquid" theme
- [ ] **DESIGN-02**: User experiences fast page loads on mobile (images optimized/compressed, ScrollMagic/pagePiling/jQuery UI removed)

### Information Architecture

- [x] **IA-01**: User sees services organized into the six owner-priority categories as clear, distinct sections — not one undifferentiated scroll of ~18 icon-boxes
- [x] **IA-02**: User can navigate via a flat, shallow nav structured around the six categories (not a dense mega-menu)

### Trust Signals

- [x] **TRUST-01**: User sees an "all brands serviced" row — **Apple and Chromebook excluded** by the owner 2026-09-11 (OWNER-QUESTIONS #22). **CORRECTED AND LIVE-VERIFIED 2026-09-12, RE-VERIFIED 2026-09-15** (Phase 3.5): brand row is six names plus the closer; `brandItemCount: 7`, `brandDuplicates: []` at both viewports; **served element count 7 and `excluded=0` on `index`, `zalivane-technosti` and `optimizatsiq`** (2026-09-15); zero occurrences of either excluded manufacturer anywhere in `src/`, comments included. ⚠ **The obvious check is wrong:** `grep -o 'brand-row__item' | wc -l` returns **8**, because the closer carries a modifier class and matches the substring twice — count elements, not substrings (audit finding F12)
- [x] **TRUST-02**: User sees a Google rating badge linking to the shop's Google Business Profile reviews — **LIVE-VERIFIED 2026-09-12, coverage broadened 2026-09-15** (Phase 3.5): `ratingBadgePresent: true`, `ratingBadgeHeight: 44` at both viewports, on the homepage and a service page, from one config source. First execution of this render path anywhere. **2026-09-15: `badge=1` on three served pages, and all 19 pages return `200 warn=0` — including the 13 that had never been parsed by PHP — so the «a page that fatals renders no badge» exposure is measured absent tree-wide.** A per-page badge count on the remaining 16 was **not** run; their coverage is a stated inference from one renderer, not a measurement. ⚠ **A `PASS` from `trust-signals.js` is NOT evidence the badge rendered** — `scripts/probes/trust-signals.js:39-41` exempts it from the verdict; read `ratingBadgePresent` out of the JSON. Values: 4,7 · `150+` (deliberately rounded per D3.5-07 — do **not** "correct" to 157) · verified profile URL
- [x] **TRUST-03**: User sees warranty terms summarized directly on relevant service pages, not only buried in a separate warranty page

### Differentiators

- [x] **DIFF-01**: User sees the self-diagnostic tool ("Тествай сам своя лаптоп") surfaced as a homepage-level feature, not buried in nav
- ~~**DIFF-02**~~: **RETIRED 2026-09-11** — battery regeneration is discontinued by the business (OWNER-QUESTIONS #31). Was built and verified Complete 2026-08-26; the service no longer exists, so the requirement is withdrawn rather than failed.
- ~~**DIFF-03**~~: **RETIRED 2026-09-11** — BGA/reballing/chip-level repair is discontinued by the business (OWNER-QUESTIONS #31). Same status: built, verified, then withdrawn with the service.
- [x] **DIFF-04**: User sees that the shop services **medical and industrial equipment** — work no competitor in the researched set offers — presented as a distinct capability rather than a line in a list — **ADVANCED, NOT MET** (Phase 3.5): the page describes work the shop actually does, in vocabulary promising nothing withdrawn, carrying both category-6 carve-outs. **It is not promoted:** `kat-6` remains `'published' => false`. **Blocker: OWNER-QUESTIONS #3a–#3f**, plus riders #32 and #33

> **Why DIFF-04 exists.** Retiring DIFF-02 and DIFF-03 left DIFF-01 as the only differentiator, which
> does not carry the phase goal ("assets no competitor currently offers"). Category 6 was confirmed as
> medical and industrial equipment on 2026-09-11, and competitor research in Phase 3 found none of
> eight competitors touching it. DIFF-04 promotes that from a category to a differentiator.
> It cannot be met until the remaining OWNER-QUESTIONS #3 sub-answers arrive.

### Content

- [x] **CONTENT-01**: User sees dedicated content for **medical and industrial equipment** servicing as one of the six headline categories — scope answered 2026-09-11 (OWNER-QUESTIONS #3); page is authored and gated, still awaiting the 3a-3f specifics before publication — **ADVANCED, NOT MET** (Phase 3.5): same page, same state, same blocker as DIFF-04. The page is authored and gated; it is not published
- [x] **CONTENT-02**: User no longer sees EU-project/COVID content competing for attention on the homepage (moved to About page)

### Contact & Conversion

- [ ] **CONTACT-01**: User can tap-to-call any of the shop's phone numbers on mobile
- ~~**CONTACT-02**~~: **RETIRED 2026-09-11** — the Viber button is dropped in favour of the contact form (OWNER-QUESTIONS #21/#2). All three published numbers were tested and none had a Viber account; rather than provision one, the shop chose the form as the written-contact path. Reverses D-16.
- [ ] **CONTACT-03**: User's contact form submission goes through a hardened handler (honeypot + authenticated SMTP/PHPMailer) instead of the current unprotected bare `mail()`
- [ ] **CONTACT-04**: User no longer sees the unstaffed Zendesk chat widget
- [ ] **CONTACT-05**: User can submit an enquiry carrying **name, phone, email, device model, fault description and one or more photographs** of the damage (OWNER-QUESTIONS #2)
- [ ] **CONTACT-06**: User giving personal data through the form sees a privacy note and gives explicit consent, and the terms page states what happens to their data and their device (OWNER-QUESTIONS #2/#27)

### Owner Self-Service

- [ ] **OWNER-01**: Shop owner can change the published **working hours** by editing one file, without a developer, and the site renders from it (OWNER-QUESTIONS #20)
- [ ] **OWNER-02**: Shop owner can schedule a **holiday/absence banner** from one file — disabled by default, and **auto-hiding once the period ends** rather than needing to be switched off (OWNER-QUESTIONS #8)

### Analytics

- [ ] **ANALYTICS-01**: Shop can see **which contact actions are used** (call, form submit) and **which service pages are visited**, so service demand is observable (OWNER-QUESTIONS #1)

### SEO & Technical Hygiene

- [x] **SEO-01**: Every page has a unique `<title>` and `<meta name="description">` (currently identical/empty across all 16 pages)
- [x] **SEO-02**: Every page declares `lang="bg"` instead of the current `lang="en"`
- [ ] **SEO-03**: Site has a `robots.txt` and `sitemap.xml`, submitted to Search Console
- [x] **SEO-05**: Every **retired** page URL returns a **301 redirect to a relevant destination, never a 404** — covers `za-bateriite.html`, `laptopi.html`, `rezervni-chasti.html` and `covid.html` (OWNER-QUESTIONS #31/#4). **LIVE-VERIFIED 2026-09-12 AND RE-MEASURED 2026-09-15** (Phase 3.5): all four 301 in **one hop to a 200**, every redirect followed rather than header-grepped, at both dates and across a redeploy of `.htaccess`; zero internal links to any retired filename tree-wide. Required a `RewriteBase` fix (`82deb04`) — the first deploy 301'd all four to a 404 with the server filesystem path in the `Location`. ⚠ **The `.htaccess` block is CUTOVER-BLOCKING and needs TWO edits at Phase 4** (canonicalisation target *and* `RewriteBase`); without promotion, four indexed URLs 404 silently
- [x] **SEO-04**: All existing page URLs are preserved unchanged through the redesign (no slug/filename changes)

### Migration Safety

- [x] **MIGR-01**: A complete URL inventory of all 16 live pages is captured and cross-checked against Search Console before rebuild work starts
- [ ] **MIGR-02**: A "must-carry" checklist (`.htaccess`, Search Console verification file, favicon, `robots.txt`) is preserved through the FTP cutover
- [x] **MIGR-03**: A pre-deploy full backup of the live site is taken before every FTP upload, with git as local source of truth for rollback

## v2 Requirements

Deferred to future release. Tracked but not in current roadmap.

### Trust & Content Growth

- **PRICE-01**: Static indicative price ranges ("от X лв.") shown per service category — deferred until owner supplies real numbers
- **GALLERY-01**: Before/after repair photo gallery — needs owner-supplied photos over time
- **TURNAROUND-01**: Explicit turnaround-time commitments per service type — needs owner-confirmed realistic numbers
- **REVIEWS-01**: Embedded live Google Reviews widget (beyond the static v1 badge)
- **BLOG-01**: Expanded guide/blog content building on existing profilaktika/zalivane/optimizatsiq pages

## Out of Scope

Explicitly excluded. Documented to prevent scope creep.

| Feature | Reason |
|---------|--------|
| E-commerce parts checkout (cart/payment) | Business is service-based, not product sales (explicit in PROJECT.md) |
| Interactive price calculator | No competitor has a real one; risks pricing disputes when quotes drift from reality — static ranges (v2) are sufficient |
| Multi-language switcher | Site is Bulgarian-only by explicit requirement |
| Courier pickup/delivery service | Operational/business decision not yet confirmed by owner |
| Staffed live chat | No staffing commitment confirmed — replaced with WhatsApp/Viber click-to-chat instead |
| CMS/WordPress or Node/Astro build pipeline | Disproportionate for a ~16-page, low-change-velocity site — PHP `include()` (already proven on host) fully solves the actual maintainability problem at far lower risk to existing SEO |
| Hosting migration | Staying on existing FTP/shared hosting (bell.host.bg) — owner confirmed |

## Traceability

Which phases cover which requirements. Populated during roadmap creation.

| Requirement | Phase | Status |
|-------------|-------|--------|
| DESIGN-01 | Phase 2 | Complete |
| DESIGN-02 | Phase 4 | Pending |
| IA-01 | Phase 2 | Complete |
| IA-02 | Phase 2 | Complete |
| TRUST-01 | Phase 3 → corrected 3.5 | Complete — live-verified 2026-09-12 **and re-verified 2026-09-15 by two independent methods** (DOM `brandItemCount: 7`; served element count 7 on three pages, `excluded=0`) |
| TRUST-02 | Phase 3 → enabled 3.5 | Complete — live-verified 2026-09-12; **2026-09-15 `badge=1` on three served pages, and all 19 pages parse at `200 warn=0` so no page fatals before the badge injects** |
| TRUST-03 | Phase 3 | Complete |
| DIFF-01 | Phase 3 | Complete |
| DIFF-02 | Phase 3 | RETIRED 2026-09-11 — service discontinued |
| DIFF-03 | Phase 3 | RETIRED 2026-09-11 — service discontinued |
| CONTENT-01 | Phase 3.5 | Advanced, NOT met — blocked on OWNER-QUESTIONS #3a-#3f; `kat-6` still unpublished |
| CONTENT-02 | Phase 3 | Complete |
| CONTACT-01 | Phase 4 | Pending |
| CONTACT-02 | Phase 4 | RETIRED 2026-09-11 — Viber dropped for the form |
| CONTACT-03 | Phase 4 | Pending |
| CONTACT-04 | Phase 4 | Pending |
| SEO-01 | Phase 3 | Complete |
| SEO-02 | Phase 2 | Complete |
| SEO-03 | Phase 4 | Pending |
| SEO-04 | Phase 1 | Complete |
| SEO-05 | Phase 3.5 | Complete — all four URLs live-verified 2026-09-12 **and re-measured 2026-09-15 against the redeployed tree: 301 → 200 in one hop, every redirect followed**; ⚠ `.htaccess` promotion is CUTOVER-BLOCKING (two edits) |
| DIFF-04 | Phase 3.5 | Advanced, NOT met — blocked on OWNER-QUESTIONS #3a-#3f; `kat-6` still unpublished |
| CONTACT-05 | Phase 4 | Pending |
| CONTACT-06 | Phase 4 | Pending |
| OWNER-01 | Phase 4 | Pending |
| OWNER-02 | Phase 4 | Pending |
| ANALYTICS-01 | Phase 4 | Pending |
| MIGR-01 | Phase 1 | Complete |
| MIGR-02 | Phase 4 | Pending |
| MIGR-03 | Phase 1 | Complete |

**Coverage:**

- v1 requirements: 23 total
- Mapped to phases: 23
- Unmapped: 0 ✓

---
*Requirements defined: 2026-08-04*
*Last updated: 2026-08-04 after roadmap creation (4 phases, full coverage)*
