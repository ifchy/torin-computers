# Project Research Summary

**Project:** Torin Computers Website Redesign (torin.bg)
**Domain:** Small local-business service website (Bulgarian computer/laptop/electronics repair shop), content-heavy, no e-commerce, redesign of an existing static site on plain-FTP shared hosting
**Researched:** 2026-08-04
**Confidence:** MEDIUM-HIGH

## Executive Summary

Torin.bg is a ~16-page Bulgarian-language brochure site for a repair shop, currently built on a dated jQuery/Bootstrap "Liquid" template with heavy parallax/scroll-hijacking effects, zero prices, zero brand/trust signals, undifferentiated service content, and a form-letter-duplicated `<head>`/nav/footer copy-pasted into every page. Competitor research across 9 real Bulgarian repair-shop sites confirms the current site is genuinely behind the market: it's missing table-stakes features (categorized services, price ranges, brand list, review signals, mobile-first performance) that most competitors already have, while sitting on unique, currently-buried assets (a working self-diagnostic tool, a real battery-regeneration story, and deep BGA/chip-level repair expertise) that no competitor offers at all. The redesign's job is as much about information architecture and trust-signal surfacing as it is about visual polish.

Two structurally different implementation approaches were researched (STACK.md leans toward a full Astro/Tailwind static-site rebuild; ARCHITECTURE.md leans toward a lighter PHP-include restructuring of the existing template) and this summary resolves that tension below with a concrete recommendation. Both are FTP-deployable to the existing `bell.host.bg` shared host, so hosting is not the deciding factor — effort, maintainability, and risk to existing SEO are the deciding factors, and this project has one dominant risk that both approaches must be built around: **the site is already indexed and has years of accumulated Google ranking signal on its current URLs, and PITFALLS.md's #1 finding is that redesigns routinely destroy this by not treating the rebuild as a migration.** A URL-preservation and redirect-mapping discipline is non-negotiable regardless of which technical path is chosen.

The recommended approach is the **PHP-include reskin/restructure**, not the Astro rebuild — full reasoning below. In short: this is a ~16-page, low-content-velocity brochure site where the actual maintainability defect (copy-pasted header/footer) is fully solved by PHP `include()`, which the host already runs today (proven by working `mailer.php`), with zero new tooling, zero build step to forget, and the lowest possible risk to the URL structure that Pitfall #1 identifies as this project's single biggest threat. An Astro rebuild is a legitimate, higher-ceiling alternative if the team wants to invest in a proper build pipeline for future growth (e.g., a blog), but it is not required to hit this project's actual goals and adds a build-and-deploy discipline (Anti-Pattern 4 in ARCHITECTURE.md) this small team hasn't needed before. Key risks across either path: URL/SEO regression during cutover, Cyrillic font/encoding breakage in a "modern" redesign, unstaffed trust-signal dead ends (empty testimonials, dead chat widgets), and carrying forward the current site's zero-differentiation, zero-price, zero-meta-description content gaps into a new visual shell.

## Key Findings

### Recommended Stack

**Chosen path: PHP-include restructuring on the existing host, not a Node/Astro build pipeline.** The host (`bell.host.bg`, cPanel + Apache + PHP) already runs PHP successfully via the live `mailer.php`, and PHP's native `include()` fully solves the site's actual technical debt (duplicated header/nav/footer across ~16 pages) with zero new tooling, no build step, and instant-on-upload edits. See "Resolving the Stack/Architecture Tension" below for the full reasoning against the alternative (Astro).

**Core technologies:**
- PHP `include()` (native, already proven on host) — shared `header.php`/`footer.php`/`site-config.php` eliminates the 16-page duplication problem that is the site's real maintainability issue
- Bootstrap 5 (upgrade path from the current Bootstrap 4-era markup) or a lean hand-rolled CSS layer — modernizes the visual/grid system with a low-effort migration path from what's already there; Tailwind is a valid but not necessary upgrade
- Alpine.js — replaces jQuery/jQuery UI/ScrollMagic/pagePiling for the handful of interactive widgets (mobile nav, accordions), ~7-15KB vs. the current heavy vendor stack
- PHPMailer 6.x + authenticated SMTP — replaces bare `mail()` in `mailer.php`, directly protects the site's core conversion path (contact form deliverability)
- Hand-authored JSON-LD `ElectronicsStore`/`ProfessionalService` schema — local SEO structured data, one shared partial

**Explicitly avoid:** jQuery/jQuery UI/ScrollMagic/pagePiling (dated, actively hurts mobile Core Web Vitals), raw PHP `mail()` (poor deliverability), any full CMS/WordPress (disproportionate for a ~16-page, low-change-velocity site), and — per this synthesis's resolution below — a Node/Astro build pipeline (not needed to fix the actual problem, adds process risk this team doesn't currently need).

### Expected Features

**Must have (table stakes) — currently missing or broken on torin.bg:**
- Clear, categorized service sections replacing the current single long scroll of ~18 undifferentiated icon-boxes
- Static indicative price ranges ("от X лв.") — torin currently shows zero prices anywhere; every serious competitor has at least a price page
- Brand-name/logo row ("Lenovo, HP, Dell, Asus, Acer, Apple, MSI...") — currently absent
- Google rating badge/link (verify a Google Business Profile exists first) — currently absent
- Mobile-responsive, fast-loading layout — current heavy parallax theme is likely the single biggest driver of the "looks dated" complaint
- Warranty terms surfaced on service pages (content exists in `warrently.html`, just buried)
- Click-to-call + WhatsApp/Viber contact options

**Should have (differentiators — genuinely unique, zero competitors have these):**
- Surfaced self-diagnostic tool (`test-laptop.html` already exists, buried in nav)
- Surfaced battery-regeneration story (Panasonic-cell regeneration vs. new-battery resale — already in content, buried in text)
- Deep BGA/chip-level repair detail as visible expertise proof (already in content, needs visual hierarchy)
- Non-standard electrical equipment servicing as an explicit headline category (owner-requested category 6; needs net-new content, no dedicated content exists today)
- Before/after repair photo gallery (needs owner-supplied photos, ongoing not one-time)

**Defer (v2+):** interactive price calculator, embedded live Google Reviews widget, courier pickup/delivery (contingent on owner operational decision), staffed live chat (only if staffing commitment exists). **Never:** e-commerce/cart, multi-language switcher, dense mega-menus, un-staffed chat widgets, empty testimonials sections.

### Architecture Approach

Restructure the ~16-page site around a `src/` tree with `includes/header.php`, `includes/footer.php`, and `includes/site-config.php` (single source of truth for phone, hours, nav, service list). Pages keep their existing `.html` filenames/URLs (parsed as PHP via `.htaccess` `AddType` — spike-verify against `bell.host.bg` first) so no URL changes are needed and Pitfall #1 (broken URLs/lost rankings) is avoided by construction rather than by redirect-mapping. `mailer.php` is hardened with a honeypot and PHPMailer/SMTP. Deploy stays FTP/FileZilla, previewed locally via `php -S localhost:8000`, with git as the local source of truth and a pre-deploy full-mirror backup as the de facto rollback mechanism (no CI/CD, no staging environment beyond an optional password-protected subfolder).

**Major components:**
1. `includes/header.php` + `includes/footer.php` — shared page chrome, included once per page, eliminates the current 16x-duplicated `<head>`/nav/footer
2. `includes/site-config.php` — plain PHP array of phone/hours/nav/service-list facts, edited once, propagates everywhere
3. Page templates (`laptopi.html`, `za-bateriite.html`, etc.) — page-specific content only, same URLs as today
4. `mailer.php` — hardened contact-form handler (honeypot + PHPMailer/SMTP), the site's single most business-critical data flow

### Critical Pitfalls

1. **Redesign silently breaks URLs and kills existing rankings/backlinks** — freeze a complete URL inventory (16 pages) cross-checked against Google Search Console before any rebuild work starts; keep existing `.html` URLs unchanged (this is the strongest argument for the PHP-include path over any URL-restructuring rebuild); 301-redirect anything genuinely retired.
2. **`.htaccess` redirects and Google Search Console verification file get lost during FTP cutover** — build a "must-carry root files" checklist (`.htaccess`, `google1718743335455f1c.html`, `robots.txt`, favicon) before touching anything; treat `.htaccess` as an append target, not a rewrite.
3. **FTP-only deploy with no CI/CD leaves no rollback path** — git as local source of truth, pre-deploy full-mirror backup every time, test complete builds locally before uploading.
4. **Cyrillic-specific rendering/SEO breakage from new fonts/templates** — verify Cyrillic subset coverage on any new webfont before adopting it; fix `<html lang="en">` → `lang="bg"` (currently wrong on every page); do a manual glyph-by-glyph QA pass after redesign.
5. **Duplicate titles / missing meta descriptions carried forward into the new template** — every one of the 16 current pages ships the identical `<title>` and zero `<meta name="description">`; require unique title/description per page as an explicit, enforced build deliverable, not something that silently falls back to a shared default.

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Migration Safety Net & Foundation
**Rationale:** PITFALLS.md's top two critical pitfalls (broken URLs, lost `.htaccess`/GSC verification) must be locked down *before* any rebuild work touches the live site, and the PHP-include foundation (Pattern 1 in ARCHITECTURE.md) is the structural prerequisite everything else builds on.
**Delivers:** Full URL inventory cross-checked against Search Console; must-carry root-files checklist archived; `src/` project structure scaffolded with `includes/header.php`, `footer.php`, `site-config.php`; `.htaccess` `AddType`-for-`.html` spike verified against `bell.host.bg`; local PHP preview workflow (`php -S`) established.
**Addresses:** N/A (foundation, not user-facing feature)
**Avoids:** Pitfall 1 (broken URLs), Pitfall 2 (lost must-carry files), Pitfall 3 (no rollback path — establish git + pre-deploy backup discipline here)

### Phase 2: Design System & Information Architecture
**Rationale:** Visual/font decisions and the 6-category service restructuring are foundational to every subsequent page and must be locked before content pages are rebuilt individually — and this is the natural point to fix Cyrillic font coverage and `lang="bg"` before they propagate into every template.
**Delivers:** New visual design system (typography with verified Cyrillic coverage, color/spacing, component library replacing Bootstrap 4/jQuery-era markup); homepage IA restructured around the 6 owner-priority categories as distinct cards/sections; nav restructured to match.
**Addresses:** Mobile-responsive modern layout (P1), 6-category service restructure (P1)
**Avoids:** Pitfall 4 (Cyrillic font/lang gaps) — build the Cyrillic-coverage check into acceptance criteria here, not discovered late

### Phase 3: Content & Trust-Signal Build-Out
**Rationale:** With IA and design system in place, rebuild each of the 16 pages with unique titles/meta descriptions and surface the currently-buried differentiators (self-diagnostic tool, battery regeneration, BGA expertise) plus the missing table-stakes trust signals (prices, brands, reviews) identified in FEATURES.md.
**Delivers:** All service pages rebuilt on the new PHP-include template with unique per-page `<title>`/`<meta description>`/OG tags; static price ranges; brand-logo row; Google rating badge; surfaced self-diagnostic tool and battery-regeneration content; net-new content for category 6 (non-standard electrical equipment, needs owner input); stale content (`covid.html`) reviewed and retired/redirected.
**Uses:** PHP-include layout pattern, `site-config.php` shared facts
**Implements:** Page templates component, shared-fact propagation pattern

### Phase 4: Forms, Performance & SEO Hardening
**Rationale:** These are cross-cutting hardening items (form security, image performance, SEO plumbing) best done as a dedicated pass once all pages exist, rather than piecemeal per-page — matches PITFALLS.md's framing of these as build-checklist items, not one-offs.
**Delivers:** Hardened `mailer.php` (honeypot + PHPMailer/SMTP); image optimization pass (resize/compress/WebP + explicit dimensions + lazy-load) across all ~81 images; `robots.txt` + `sitemap.xml` added and submitted to Search Console; holiday-banner logic (`otpuska.js`) decision resolved with owner.
**Implements:** Hardened contact form pattern (Pattern 2 in ARCHITECTURE.md)
**Avoids:** Pitfall 6 (slow images), Pitfall 7 (unprotected form), Pitfall 8 (missing robots.txt/sitemap), Pitfall 10 (stale holiday logic)

### Phase 5: Cutover & Post-Launch Verification
**Rationale:** The actual FTP swap from `site-current/` to the new build is the highest-risk single event in the project (Pitfall 2, Pitfall 3) and deserves its own dedicated phase with an explicit rollback plan, not a "final step" tacked onto content work.
**Delivers:** Staged review in a password-protected subfolder if feasible; pre-cutover full backup of live `public_html`; full-build FTP upload; post-launch Search Console monitoring for 404s/ranking drops; re-verification of GSC ownership via a second method (DNS/Analytics) as a belt-and-suspenders step. Live redesigned site with URL/ranking continuity confirmed.

### Phase Ordering Rationale

- Foundation (Phase 1) must precede everything because it's the safety net that prevents the project's single largest risk (lost SEO/URLs) — this is not sequenced for developer convenience, it's sequenced because PITFALLS.md is explicit that redesigns fail here when treated as an afterthought.
- Design system before content build-out (Phase 2 → 3) because rebuilding 16 pages against an unstable visual system means redoing work; Cyrillic font verification specifically must happen before it's baked into every template.
- Content (Phase 3) before hardening (Phase 4) because per-page metadata, image optimization, and sitemap generation all need the final page set to exist first.
- Cutover (Phase 5) last and isolated, because it's a distinct, higher-risk event (live traffic, no staging environment) that benefits from its own rollback checklist rather than being bundled into content work.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 1:** The `.htaccess` `AddType`-for-`.html`-as-PHP behavior should be spike-verified directly against `bell.host.bg` before committing the whole site to it — ARCHITECTURE.md flags this as standard cPanel behavior but unconfirmed for this specific host.
- **Phase 3:** Category 6 (non-standard electrical equipment) has no existing dedicated content and needs owner input on scope before content can be written — not a technical research gap, but a blocking content dependency worth flagging for planning.

Phases with standard patterns (skip research-phase):
- **Phase 2:** Cyrillic font selection and PHP-include design-system patterns are well-documented, established practices (multiple corroborating sources in STACK.md/PITFALLS.md).
- **Phase 4:** Honeypot/PHPMailer/image-optimization/robots.txt-sitemap patterns are all standard, widely-documented shared-hosting practices.

## Resolving the Stack/Architecture Tension: PHP-Include Reskin, Not Astro Rebuild

STACK.md and ARCHITECTURE.md reached different conclusions — STACK.md recommends a full Astro/Tailwind static rebuild, ARCHITECTURE.md recommends a PHP-include restructuring of the existing template. Both are 100% FTP-deployable to `bell.host.bg`, so hosting compatibility doesn't decide this. The decision comes down to whether the extra investment of a Node/Astro build pipeline buys anything this project actually needs, weighed against risk to the project's most fragile asset (existing SEO ranking on 16 indexed URLs).

**Decision: PHP-include restructuring.** Reasoning:

1. **The actual technical debt is fully solved by PHP `include()` alone.** The stated maintainability problem — 17 pages hand-duplicating header/nav/footer markup — is not specific to lacking a build pipeline; it's specific to lacking *any* shared-include mechanism. PHP `include()`, which the host already runs today (`mailer.php` proves it), solves 100% of that problem with zero new tooling. An SSG doesn't fix this problem more thoroughly, it just fixes it with a heavier toolchain.
2. **Lower risk to the project's single biggest threat (SEO/URL continuity).** Keeping the existing `.html` filenames and URL structure exactly as-is, with zero migration required, is trivial in the PHP-include path (same filenames, just parsed as PHP) and requires deliberate care in any rebuild path (Astro's idiomatic routing conventions, trailing-slash behavior, and content-collection URL patterns all invite subtle URL drift unless explicitly fought against). PITFALLS.md identifies broken URLs as the #1 critical risk — the path that makes URL preservation the *default* behavior rather than something to configure around is the lower-risk choice.
3. **No new deploy discipline to establish and maintain.** ARCHITECTURE.md's Anti-Pattern 4 explicitly warns that introducing a Node/SSG build step without a matching deploy discipline (forgetting to rebuild before FTP upload, or uploading source instead of built output) is a common, real failure mode for exactly this kind of small-team, no-CI/CD, FTP-only project. The PHP-include path has no build step to forget — edit, preview locally, upload, done — which matches how this team already works today (FileZilla, manual FTP, no CI).
4. **Content velocity doesn't justify the investment.** This is a ~16-page brochure site that changes a handful of times a year (per ARCHITECTURE.md's own scaling analysis). Astro's content-collections and component-island model earn their complexity on sites with many similar pages or frequent content additions (e.g., a blog) — neither applies here today.
5. **The full rebuild remains a legitimate future path, not a wrong idea.** If the site later adds a blog/tips section for SEO content (a plausible v2 direction per both FEATURES.md's "expanded guide/blog content" and ARCHITECTURE.md's own scaling note), that's the trigger point to revisit an SSG — looping over many similar content files by hand in PHP becomes tedious at that scale in a way it isn't at 16 pages. Treat this as an explicit "re-evaluate later" flag, not a rejected option.

**Net effect on the roadmap above:** phases are written around the PHP-include path (Astro/Node tooling, `npm run build`, and content-collections are *not* part of the roadmap). If, during planning, the team decides the Astro path is worth the extra investment after all, Phases 2-3 would need re-scoping around Astro's component/content model, but Phase 1's URL-preservation and Phase 5's cutover-safety work remain valid regardless of which technical path is chosen — that discipline is not stack-dependent.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | MEDIUM-HIGH | Current library versions verified via web search; PHP-include pattern is a long-established, well-corroborated approach; Astro alternative also well-sourced from official docs |
| Features | MEDIUM-HIGH | Based on direct live-fetch inspection of 9 real competitor sites plus one consumer-facing "how to choose" article; no traffic/revenue data to rank competitors by actual market share |
| Architecture | MEDIUM | Cross-checked against widely-documented patterns; the `.htaccess` `AddType` behavior specifically needs a live spike against `bell.host.bg` before full commitment |
| Pitfalls | MEDIUM-HIGH (HIGH for site-verified findings) | Many findings are direct, primary-source inspection of `site-current/` (duplicate titles, missing meta descriptions, `lang="en"`, no robots.txt, image sizes) — highest-confidence category; general SEO-migration/Cyrillic findings are MEDIUM, cross-corroborated across multiple independent sources |

**Overall confidence:** MEDIUM-HIGH

### Gaps to Address

- **Category 6 content (non-standard electrical equipment):** No existing dedicated content exists for this owner-requested category — needs direct owner input on scope before Phase 3 content can be written. Not a research gap; a blocking business input.
- **Owner-supplied data dependencies:** Real price ranges, before/after repair photos, and turnaround-time commitments all require the owner's direct input, not further research — flag these early in planning so they don't silently block Phase 3/4.
- **Google Business Profile status:** Unverified whether torin.bg currently has an active Google Business Profile with reviews to feature — confirm before committing to a "Google rating badge" feature in Phase 3.
- **`.htaccess` `AddType`-for-`.html`-as-PHP on `bell.host.bg` specifically:** Standard cPanel behavior per research, but not confirmed against this exact host — spike-verify in Phase 1 before the whole site depends on it.
- **PHP version and Composer availability on `bell.host.bg`:** Needed to confirm whether PHPMailer can be installed via Composer or must be vendored manually — check via a `phpinfo()` probe or hosting control panel early in Phase 1.
- **Holiday-banner feature (`otpuska.js`) intent:** Needs an explicit owner decision (keep with a maintained equivalent, or drop) rather than defaulting either way — flagged in Phase 4.

## Sources

### Primary (HIGH confidence)
- Direct inspection of `/Users/alabala/Documents/projects/torin/site-current/` (215 files, live FTP mirror) — HTML structure, `mailer.php`, `.htaccess`, image sizes, duplicate titles, missing meta descriptions, `lang="en"`, absence of `robots.txt`/`sitemap.xml`
- `/Users/alabala/Documents/projects/torin/.planning/PROJECT.md` — project scope, constraints, owner-stated priorities
- 9 live competitor site fetches (remontlaptop.bg, croscomputers.net, itserviz.com, acs-bg.net, adminbg.net, sofiacomputers.net, computer-serviz.bg, trierrasoft.com, plasico.bg)

### Secondary (MEDIUM confidence)
- Astro.js official docs and deploy guides (astro.build, docs.astro.build) — SSG build/deploy pattern
- 11ty.dev official docs — alternative SSG comparison
- Multiple independent SEO-migration checklists (Mindesigns, Brand Vision, SEOptimer, WebFX, Salt Agency) — cross-corroborated on URL/redirect risk
- Multiple independent Cyrillic/mojibake/tofu-font sources (SimpleLocalize, Wikipedia, Simple Machines community) — cross-corroborated on font/encoding risk
- PHP include header/footer pattern (Treehouse Community, CSS-Tricks) — established shared-hosting pattern
- Honeypot + PHPMailer/SMTP hardening (multiple dev.to/GitHub reference implementations)
- 088support.bg consumer-advice article — Bulgarian buyer decision criteria

### Tertiary (LOW confidence)
- Search-snippet-only cross-references for some Bulgarian-language competitor searches (not independently verified against every live page)

---
*Research completed: 2026-08-04*
*Ready for roadmap: yes*
