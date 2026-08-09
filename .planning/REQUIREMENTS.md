# Requirements: Torin Computers Website Redesign

**Defined:** 2026-08-04
**Core Value:** A visitor with a specific repair problem immediately sees that Torin fixes it, and finds a clear path to contact the shop.

## v1 Requirements

Requirements for the redesign launch. Each maps to roadmap phases.

### Design & Performance

- [x] **DESIGN-01**: User sees a modern, mobile-responsive layout across all pages, replacing the current heavy jQuery/parallax "Liquid" theme
- [ ] **DESIGN-02**: User experiences fast page loads on mobile (images optimized/compressed, ScrollMagic/pagePiling/jQuery UI removed)

### Information Architecture

- [ ] **IA-01**: User sees services organized into the six owner-priority categories as clear, distinct sections — not one undifferentiated scroll of ~18 icon-boxes
- [ ] **IA-02**: User can navigate via a flat, shallow nav structured around the six categories (not a dense mega-menu)

### Trust Signals

- [ ] **TRUST-01**: User sees an "all brands serviced" row (Lenovo, HP, Dell, Asus, Acer, Apple, MSI, etc.)
- [ ] **TRUST-02**: User sees a Google rating badge linking to the shop's Google Business Profile reviews
- [ ] **TRUST-03**: User sees warranty terms summarized directly on relevant service pages, not only buried in a separate warranty page

### Differentiators

- [ ] **DIFF-01**: User sees the self-diagnostic tool ("Тествай сам своя лаптоп") surfaced as a homepage-level feature, not buried in nav
- [ ] **DIFF-02**: User sees the battery-regeneration story (Panasonic-cell regeneration vs. new-battery resale) surfaced as a distinct differentiator
- [ ] **DIFF-03**: User sees the shop's BGA/chip-level repair expertise presented with clear visual hierarchy alongside liquid/motherboard-damage content

### Content

- [ ] **CONTENT-01**: User sees dedicated content for "нестандартно ел. оборудване" (non-standard electrical equipment) servicing as one of the six headline categories (scope to be confirmed with owner during phase work)
- [ ] **CONTENT-02**: User no longer sees EU-project/COVID content competing for attention on the homepage (moved to About page)

### Contact & Conversion

- [ ] **CONTACT-01**: User can tap-to-call any of the shop's phone numbers on mobile
- [ ] **CONTACT-02**: User can click to start a WhatsApp or Viber chat
- [ ] **CONTACT-03**: User's contact form submission goes through a hardened handler (honeypot + authenticated SMTP/PHPMailer) instead of the current unprotected bare `mail()`
- [ ] **CONTACT-04**: User no longer sees the unstaffed Zendesk chat widget

### SEO & Technical Hygiene

- [ ] **SEO-01**: Every page has a unique `<title>` and `<meta name="description">` (currently identical/empty across all 16 pages)
- [ ] **SEO-02**: Every page declares `lang="bg"` instead of the current `lang="en"`
- [ ] **SEO-03**: Site has a `robots.txt` and `sitemap.xml`, submitted to Search Console
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
| IA-01 | Phase 2 | Gaps Found |
| IA-02 | Phase 2 | Gaps Found |
| TRUST-01 | Phase 3 | Pending |
| TRUST-02 | Phase 3 | Pending |
| TRUST-03 | Phase 3 | Pending |
| DIFF-01 | Phase 3 | Pending |
| DIFF-02 | Phase 3 | Pending |
| DIFF-03 | Phase 3 | Pending |
| CONTENT-01 | Phase 3 | Pending |
| CONTENT-02 | Phase 3 | Pending |
| CONTACT-01 | Phase 4 | Pending |
| CONTACT-02 | Phase 4 | Pending |
| CONTACT-03 | Phase 4 | Pending |
| CONTACT-04 | Phase 4 | Pending |
| SEO-01 | Phase 3 | Pending |
| SEO-02 | Phase 2 | Gaps Found |
| SEO-03 | Phase 4 | Pending |
| SEO-04 | Phase 1 | Complete |
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
