---
phase: 02-design-system-information-architecture
verified: 2026-08-06T00:00:00Z
status: gaps_found
score: 2/4 success criteria fully verified
behavior_unverified: 1
overrides_applied: 0
gaps:
  - truth: "Every page renders with the new responsive design system, with no dependency on ScrollMagic, pagePiling, or jQuery UI, and displays correctly on mobile and desktop viewports."
    status: partial
    reason: "The vendor-removal and design-system-everywhere halves are fully verified against all sixteen deployed responses. The 'displays correctly' half fails on two independently reproduced CSS specificity defects, both of which I confirmed by reading the cascade rather than by trusting 02-REVIEW.md."
    artifacts:
      - path: "src/css/components.css:48-51, 208-220"
        issue: "`.hero p` has specificity (0,1,1); `.trust-badge` has (0,1,0). The type selector wins regardless of source order, so the badge's `color: var(--c-on-brand)` (#16223a) is overridden by `--c-on-dark-muted` (#c9d6ea) on the `--c-brand` #ffc70a fill = 1.06:1. «Безплатна диагностика» — the D-15 conversion element — is effectively invisible above the fold on the homepage, in both themes. The comment at line 207 asserting 10.14:1 documents a value that never applies. The same collision also overrides the badge's `margin-block-start`, invalidating the hero-height arithmetic documented at components.css:23-31 and the ≥35rem override at line 733."
      - path: "src/css/base.css:157-168"
        issue: "`.hero :focus-visible` and `.btn--primary:focus-visible` are both (0,2,0); line 168 is declared later and therefore wins on dark surfaces too. Combined with `outline-offset: 2px`, the 3px ring is drawn entirely outside the button on the surrounding dark fill, never on the amber the comment reasons about. Ring `--c-on-brand` #16223a against `--c-ink-deep` #0e305d = 1.21:1 (hero CTAs) and against `--c-ink-deepest` #0a2547 = 1.03:1 (footer and call-bar CTAs). WCAG 2.1 SC 1.4.11 requires >=3:1. Six primary CTAs give keyboard users no visible focus. `--c-focus-on-dark` (#ffd84d) is already declared at base.css:67 and unused on these controls."
    missing:
      - "Scope the hero prose rule so it cannot reach the badge (e.g. `.hero__inner > p:not(.trust-badge)`), then re-verify the hero stack height because the badge's own --sp-sm/--sp-lg margins begin applying."
      - "Restate the on-dark focus-ring colour at higher specificity for `.hero .btn--primary`, `.site-footer .btn--primary` and `.callbar .btn--primary` — `.callbar` is inside neither `.hero` nor `.site-footer` and needs its own selector."
  - truth: "A visitor can reach any part of the site via a flat, shallow navigation structured around the six categories, with no dense mega-menu."
    status: partial
    reason: "The navigation SHAPE is fully verified — five top-level items, one single-level Услуги disclosure listing all six categories, no mega-menu, no hover-open path. Two reachability facts fall short of 'any part of the site', both confirmed against deployed responses rather than source."
    artifacts:
      - path: "src/css/components.css:452-474 with src/js/site.js"
        issue: "Below 56.25rem `.nav__list { display: none }` is reversed only by `[aria-expanded=\"true\"] + .nav__list`, and site.js is the sole writer of that attribute. With scripts blocked, the ENTIRE mobile navigation is hidden — all five top-level items, not only the six category links. The inline comment at src/includes/header.php:74-77 records the gap as 'the six category links are unreachable from the nav', which materially understates what ships. WR-08 is correct and the recorded gap is not."
      - path: "src/covid.html, src/problem-stari.html"
        issue: "Both return HTTP 200 and both have ZERO inbound links across all sixteen deployed responses (verified by grepping every fetched page for `href=\"covid.html\"` and `href=\"problem-stari.html\"`). The legacy site linked each from index.html, so this is a reachability regression. covid.html is a deliberate D-35 decision and problem-stari.html is disclosed in 02-04-SUMMARY.md:308 — neither is a hidden gap — but 'reach any part of the site' is not literally true today. covid.html is an EU-funding disclosure page, a category that usually carries a publication obligation; confirm with the owner before leaving it unlinked."
    missing:
      - "Correct the header.php:74-77 comment to state that the whole nav, not just the six category links, is hidden without JS — a recorded gap that understates itself will not be closed correctly by a later phase."
      - "Decide and record whether covid.html needs a footer link (EU funding disclosure obligation) — owner question."
      - "Optional, closes the no-JS gap structurally: a `:target`-based or `<details>`-based fallback, or ship the nav open by default and let JS collapse it on load."
deferred: []
behavior_unverified_items:
  - truth: "Web-font swap (FOUT): Bulgarian text is readable from first paint and the reflow between the fallback and Sofia Sans does not visibly displace the hero CTA buttons on a throttled connection."
    test: "Load https://torin.bg/new/index.html with network throttled to Slow 3G and watch the hero from first paint through webfont application."
    expected: "Bulgarian copy is readable immediately in the fallback, and when Sofia Sans swaps in the two hero CTA buttons do not visibly jump."
    why_human: "Declared `verification: backstop` in the 02-01 plan. font-display:swap and the Cyrillic-first preload are present and correct, but reflow magnitude is a timing-dependent visual property no file check can observe."
human_verification:
  - test: "Load the deployed site and read the Bulgarian headings and body copy at desktop and mobile width."
    expected: "Cyrillic renders in Bulgarian-convention letterforms (localized д, л, п, ц, ш, and the italic/upright forms Sofia Sans was chosen for), not Russian-convention defaults."
    why_human: "The 02-01 prohibition against Russian-convention letterforms is judgment-tier. Encoding correctness, the unicode-range split and font delivery are all verified programmatically; the actual glyph shapes require eyes on the rendered page. This is the entire reason this font was selected."
  - test: "Load https://torin.bg/new/index.html at 360px, 560px, 900px and 1440px viewport widths."
    expected: "No horizontal scrollbar; the six category cards break 1 / 2 / 3 / 3 columns with no orphan row; the sticky call bar does not obscure content or sit under the iOS home indicator."
    why_human: "Layout-geometry claims in 02-02-SUMMARY.md were measured by the executor and cannot be re-derived from source. WR-11 reports the call bar sitting under the iOS home indicator."
  - test: "With JavaScript disabled, load a category page (e.g. /new/mehanichni-problemi.html) at 360px."
    expected: "Confirm the intended degraded experience is acceptable: no nav at all, with the logo linking home and the footer offering five secondary links."
    why_human: "Whether this degradation is acceptable is a product decision, not a code fact. The code fact is established: the nav is entirely hidden."
  - test: "Web-font swap (FOUT) check — see behavior_unverified_items above."
    expected: "Bulgarian copy readable from first paint; hero CTAs do not displace on swap."
    why_human: "Declared backstop truth; timing-dependent visual behaviour."
---

# Phase 2: Design System & Information Architecture — Verification Report

**Phase Goal:** Visitors see a modern, mobile-responsive site organized around the six owner-priority service categories, replacing the outdated jQuery/parallax "Liquid" theme and its undifferentiated scroll of icon boxes.
**Verified:** 2026-08-06
**Status:** gaps_found
**Re-verification:** No — initial verification

Verification was performed against **the sixteen deployed responses at `https://torin.bg/new/`**, not against SUMMARY.md claims. Where a summary made a live assertion, I re-fetched and re-derived it. Where 02-REVIEW.md reported a defect, I reproduced it from the CSS cascade myself rather than inheriting the finding.

## Goal Achievement

### Observable Truths (ROADMAP Success Criteria)

| # | Truth | Status | Evidence |
|---|---|---|---|
| 1 | Every page renders with the new design system, no ScrollMagic/pagePiling/jQuery UI, displays correctly on mobile and desktop | ✗ PARTIAL | Vendor-free and design-system-everywhere: **fully verified**. Display correctness: **two confirmed defects** (CR-01, CR-02). See below. |
| 2 | Homepage presents six clearly distinct category sections instead of ~18 undifferentiated icon boxes | ✓ VERIFIED | Deployed homepage contains exactly 6 `<article class="cat-card">` with ids `kat-1`..`kat-6`, each with a title link, symptom line and media slot. No seventh peer item in the grid. |
| 3 | Visitor can reach any part of the site via a flat, shallow navigation around the six categories, no dense mega-menu | ✗ PARTIAL | Nav shape verified (5 items, one single-level disclosure, 6 categories, no mega-menu, no hover path). Reachability falls short: entire nav hidden without JS below 900px; `covid.html` and `problem-stari.html` have zero inbound links. |
| 4 | Every page declares `lang="bg"` and all Cyrillic text renders correctly in the new typography | ✓ VERIFIED | 16/16 deployed responses carry exactly one `<html lang="bg">`. Zero BOM in any source file. 16 distinct Cyrillic titles round-trip without mojibake. Glyph-shape correctness routed to human check. |

**Score: 2/4 fully verified** (1 backstop truth behaviour-unverified)

### Criterion 1 — detail

**The vendor-dependency claim survives Pitfall 1.** The phase warned that grepping for vendor filenames proves nothing because the legacy site bundled ScrollMagic, Isotope, imagesLoaded, Velocity and jQuery UI into one ~530 KB file. I did not rely on name-grepping. Instead I enumerated **every** `<script>` element and **every** absolute URL across the whole tree and across all sixteen deployed responses:

- The entire `src/` tree contains exactly two `<script>` tags: `js/site.js` (60 lines, which I read in full) and the JSON-LD block in `jsonld.php`.
- All sixteen deployed responses reference exactly one script: `js/site.js`. Nothing else.
- Zero external URLs anywhere in the tree (excluding `schema.org`, `w3.org`, the Maps deep link and `torin.bg` itself). No Google Fonts CDN, no `assets1/`.

This is stronger evidence than the plan's approach: a bundled vendor file would still need a `<script src>` to load, and there is exactly one, pointing at a 2,476-byte local file whose contents I verified. **The legacy stack is genuinely gone.** Plan 02-04's runtime-globals-are-`undefined` evidence is consistent with this but was not needed to reach the verdict.

**Design system on every page:** all 16 pages `require_once` both `includes/header.php` and `includes/footer.php` (0 exceptions among the sixteen). All three stylesheets, the script and the Cyrillic font subset serve HTTP 200 from the deployed origin. All 16 pages return HTTP 200 as parsed HTML, not raw PHP source — the versioned CloudLinux handler line survived the compression edit.

**Where it fails — "displays correctly":** CR-01 and CR-02 are both real and both reproduced independently. CR-01 puts an above-the-fold homepage conversion element at 1.06:1 (invisible) in both themes. CR-02 removes the visible focus indicator from the site's six most important controls. Neither is cosmetic polish; both are the literal subject of "displays correctly". This is what moves criterion 1 from pass to partial.

### Criterion 2 — detail

Verified live. Six cards, one per owner-priority category, each carrying the plain-language symptom line D-10 requires. The publish gate works as designed: three published categories link to real pages (`mehanichni-problemi.html`, `optimizatsiq.html`, `zalivane-technosti.html`) and three unpublished ones link to their own stable homepage anchors (`index.html#kat-2`, `#kat-5`, `#kat-6`) — no card is visually degraded or omitted for lacking a page. The catch-all «Не откривате проблема си?» section is native `<details>` with real HTML bodies present in the served response, not JS-injected. The self-diagnostic block and the repeated CTA block are present. Nothing appears as a seventh peer item.

### Criterion 3 — detail

**Nav shape is correct.** Five top-level items (Начало, Услуги, Лаптопи и части, Тествай сам, Контакти) with one single-level disclosure containing all six categories, resolved through the same `torin_category_href()` accessor the cards use — so nav and cards cannot disagree. `site.js` writes only `aria-expanded`, and every visual state in the CSS is selected off that attribute, so announced and rendered state cannot desynchronise. Escape-to-close with focus restore, focus-out close and click-outside close are all implemented. No hover-open rule exists at any width. This satisfies IA-02 as written.

**The no-JS gap is worse than recorded.** `.nav__list { display: none }` below 56.25rem is reversed only by `[aria-expanded="true"] + .nav__list`. With scripts off, the whole list — all five items — is hidden, not just the six category links as `header.php:74-77` claims. The 02-03 prohibition ("MUST NOT make the six categories reachable only through a JavaScript-dependent control without an equivalent non-JavaScript path") is nonetheless **satisfied**: the homepage card grid is real HTML, and the brand logo sits outside `.nav__list`, so every page stays reachable within two hops via logo → homepage. The degradation is bounded — but the recorded gap understates it, which is itself a defect worth fixing.

**Two pages are unreachable from anywhere.** `covid.html` and `problem-stari.html` have zero inbound links across all sixteen responses. Both were linked from the legacy homepage. `covid.html` is deliberate per D-35 and carries an inline note; `problem-stari.html` is disclosed at 02-04-SUMMARY.md:308. `msg.html` is also unlinked but that is correct — it is the `mailer.php` POST target, not a navigation destination.

### Criterion 4 — detail

Verified against deployed responses, not by observing the attribute in the shared include: 16/16 pages carry exactly one `<html lang="bg">`, and `header.php:52` is the sole emitter site-wide (one `lang=` occurrence in the entire tree). No source file carries a UTF-8 BOM. All sixteen titles are distinct and render Bulgarian Cyrillic cleanly through the live response, which also confirms the per-page metadata mechanism Phase 3 depends on. Typography: two self-hosted Sofia Sans subsets with a correct Cyrillic/Latin `unicode-range` split, `font-display: swap` on both, Cyrillic subset preloaded with `crossorigin`. **No `text-transform` declaration exists anywhere in the stylesheets**, so the 02-01 prohibition against uppercasing Bulgarian copy holds structurally. Bulgarian-convention letterform rendering is routed to human verification.

### Required Artifacts

| Artifact | Expected | Status | Details |
|---|---|---|---|
| `src/includes/header.php` | Head, metadata mechanism, nav | ✓ VERIFIED | 8,372 B, wired by 16/16 pages, sole `lang` emitter |
| `src/includes/footer.php` | Contact-first footer, no map iframe | ✓ VERIFIED | Three tel: links looped from config, Maps deep link, no iframe anywhere |
| `src/includes/categories.php` | Six-category single source of truth | ✓ VERIFIED | 6 records, `torin_category_href()` consumed by both cards and nav |
| `src/includes/category-page.php` | D-24 template, optional blocks omit | ✓ VERIFIED | 9,696 B; thin (`mehanichni-problemi`) and deep (`zalivane-technosti`) instances both render |
| `src/includes/jsonld.php` | LocalBusiness JSON-LD from config | ✓ VERIFIED | Single-sourced off `$site[...]`, emitted once per page |
| `src/includes/site-config.php` | Phones promoted to list, hours, geo | ✓ VERIFIED | Scalar phone key has no surviving reader |
| `src/includes/icons.php` | 15 inline SVG icons | ✓ VERIFIED | `torin_icon()` consumed across pages |
| `src/includes/dev-switcher.php` | Dev-only theme control | ✓ VERIFIED | `file_exists` sentinel gate; live on staging as intended |
| `src/js/site.js` | Entire JS surface, <=60 lines | ✓ VERIFIED | 60 lines, 2,476 B, serves 200 |
| `src/css/base.css` | Tokens, fonts, type scale, focus | ⚠️ DEFECT | Present and wired; focus-ring cascade defect (CR-02) |
| `src/css/layout.css` | Container, rhythm, shells | ✓ VERIFIED | Serves 200 |
| `src/css/components.css` | Hero, cards, nav, footer | ⚠️ DEFECT | Present and wired; trust-badge cascade defect (CR-01) |
| `src/css/theme-a.css` | 10-token dev override | ✓ VERIFIED | Exists as data-attribute override |
| `src/fonts/*.woff2` | Self-hosted subsets | ✓ VERIFIED | Cyrillic 25,568 B serves 200; correct unicode-range |
| `src/.htaccess` | Compression + versioned handler | ⚠️ PARTIAL | Handler line survived (pages parse). No `content-encoding` observed on the response — WR-13 stands. |
| 15 restyled pages + `src/index.html` | Site-wide rollout | ✓ VERIFIED | 16/16 deployed 200 with distinct titles |

### Key Link Verification

| From | To | Via | Status |
|---|---|---|---|
| 16 pages | `includes/header.php` | `require_once` | ✓ WIRED (16/16) |
| 16 pages | `includes/footer.php` | `require_once` | ✓ WIRED (16/16) |
| `index.html` | `categories.php` | `torin_category_href()` in card loop | ✓ WIRED |
| `header.php` | `categories.php` | same accessor in Услуги dropdown | ✓ WIRED |
| `components.css` | `header.php` | `[aria-expanded]` attribute selectors only | ✓ WIRED |
| `site.js` | `header.php` | `[aria-expanded][aria-controls]` query | ✓ WIRED |
| `jsonld.php` | `site-config.php` | `$site[...]` reads | ✓ WIRED |
| `footer.php` | `site-config.php` | `foreach` over phone list | ✓ WIRED |
| `base.css` | `fonts/*.woff2` | `@font-face src` + unicode-range | ✓ WIRED (200 live) |
| `index.html` CTAs | `site-config.php` | — | ✗ NOT WIRED — WR-10 confirmed: homepage hardcodes `+35929549710` and bypasses site-config entirely, so the homepage and footer can drift apart |

### Behavioural Spot-Checks

| Behaviour | Command | Result | Status |
|---|---|---|---|
| All 16 URLs live and parsed | `curl` each of 16 | 16x HTTP 200, 0 raw PHP | ✓ PASS |
| `lang="bg"` on every page | grep each response | 16/16 exactly one | ✓ PASS |
| No legacy vendor asset | grep 9 vendor patterns x 16 | 0 hits | ✓ PASS |
| Exactly one script site-wide | enumerate `<script src>` | `js/site.js` only, 16/16 | ✓ PASS |
| Distinct per-page titles | extract `<title>` x 16 | 16 distinct | ✓ PASS |
| Six category cards | count `class="cat-card"` | 6 | ✓ PASS |
| Assets serve | `curl` 3 CSS + JS + font | 5x HTTP 200 | ✓ PASS |
| Staging is noindex | `curl -I` | `x-robots-tag: noindex, nofollow` | ✓ PASS (CR-06's concern does not reproduce live) |
| No BOM in source | byte check all files | 0 | ✓ PASS |
| No `text-transform` | grep all CSS | 0 | ✓ PASS |
| Inbound-link audit | cross-grep 16x16 | `covid`, `problem-stari`, `msg` at 0 | ✗ FAIL (see criterion 3) |
| Response compression | `curl -I` | no `content-encoding` | ✗ FAIL (WR-13) |

No test suite, no package.json and no build step exist in this project, so there is nothing to enumerate or run; verification is by deployed-response inspection, which is the appropriate substitute here.

### Requirements Coverage

| Requirement | Source Plan | Description | Status | Evidence |
|---|---|---|---|---|
| DESIGN-01 | 02-01, 02-02, 02-03, 02-04 | Modern mobile-responsive layout replacing the jQuery/parallax theme | ⚠️ PARTIAL | Legacy stack fully removed and design system on all 16 pages; CR-01/CR-02 break "displays correctly" |
| IA-01 | 02-02, 02-04 | Six owner-priority categories as distinct sections | ✓ SATISFIED | 6 cards, symptom lines, publish gate, no seventh peer |
| IA-02 | 02-03 | Flat, shallow nav around the six categories, no mega-menu | ✓ SATISFIED | 5 items + one single-level disclosure; the reachability caveats are roadmap-criterion-3 scope, not IA-02 as written |
| SEO-02 | 02-01, 02-04 | Every page declares `lang="bg"` | ✓ SATISFIED | 16/16 deployed responses |
| SEO-04 | 02-04 (prohibition) | All existing URLs preserved | ✓ SATISFIED | 16 filenames byte-for-byte what Phase 1 locked; no page added, renamed or retired |

**Orphaned requirements: none.** REQUIREMENTS.md maps exactly four IDs to Phase 2 (DESIGN-01, IA-01, IA-02, SEO-02) and all four are claimed by plan frontmatter. SEO-04 is a Phase 1 requirement re-asserted here as a prohibition and remains intact.

**Note:** REQUIREMENTS.md already marks all four as `Complete`. DESIGN-01 should not stand as Complete while CR-01/CR-02 are open.

### Prohibitions

| # | Prohibition | Tier | Status |
|---|---|---|---|
| 1 | No Russian-convention Cyrillic letterforms; no uppercase transform on Bulgarian copy (DESIGN-01) | judgment | ⚠️ FLAGGED — no `text-transform` anywhere (structurally satisfied); letterform shapes need human review |
| 2 | No unverified business facts presented as unqualified fact (SEO-02) | judgment | ✓ Every `[ASSUMED]` value carries an inline marker naming its OWNER-QUESTIONS number |
| 3 | No seventh peer item; no owner category demoted into the catch-all (IA-01) | judgment | ✓ Exactly six cards; all six also in the nav |
| 4 | Symptom lines must carry a visible provenance marker while placeholder (IA-01) | judgment | ✓ All six marked `[ASSUMED]` in `categories.php` pending OWNER-QUESTIONS #16 |
| 5 | No unconfirmed hours/chat number/address as unqualified fact in footer or structured data (IA-02) | judgment | ⚠️ FLAGGED — markers present in source, but the value still ships to 16 pages and into `openingHoursSpecification`. See open facts below. |
| 6 | Six categories must have a non-JS path (IA-02) | judgment | ✓ SATISFIED — homepage card grid is real HTML; logo is outside the hidden nav list |
| 7 | No page filename renamed, added or retired (SEO-04) | judgment | ✓ 16/16 unchanged |

Per the fail-closed default, judgment-tier prohibitions carry a non-authoritative verdict and the two flagged rows are recommended for human review.

### Knowingly Unmet / Open Items

These are recorded, not silent gaps:

- **DIFF-02 (battery regeneration placement)** — a deliberate, documented downgrade, placed in the folded catch-all rather than given the treatment D-13 contemplated. Recorded in an inline comment in `index.html` and in 02-02-SUMMARY.md:230-234. Carried to Phase 3, **not** counted as a gap here.
- **Three `[ASSUMED]` facts open for Phase 4** — all three are correctly marked in source:
  - Opening hours (OWNER-QUESTIONS #20) — **highest exposure**: ships to all 16 footers *and* into `openingHoursSpecification` in the LocalBusiness JSON-LD, so a wrong value reaches Google's results and sends customers to a closed shop. WR-09 additionally reports the value is stored twice and can silently disagree.
  - Chat-capable number (#21) — the Viber CTA is one of two equal-weight primary conversion actions; a dead link kills the site's core value.
  - Notice band text (#8).
- **Dev theme switcher and `phptest.html` are live on staging** — intended pre-cutover, but `phptest.html` discloses the exact EOL PHP build (CR-03) and the theme cookie is domain-scoped including production (WR-17). Both must be resolved at the Phase 4 cutover.
- **CR-05** — following the `.htaccess` Phase-4 promotion instruction literally would deindex torin.bg. Not a Phase 2 defect, but it is a live landmine in a Phase 2 artifact and must be rewritten before cutover.

### Gaps Summary

The phase substantially achieves its goal. The legacy jQuery/ScrollMagic/pagePiling stack is genuinely and completely gone — verified by exhaustive enumeration rather than name-matching, so the phase's own Pitfall 1 does not undermine the result. The six-category IA is real, data-driven from one file, and consistent between the cards and the nav. `lang="bg"` and clean Cyrillic are proven on all sixteen deployed URLs.

Two things stop it from passing.

**First, "displays correctly" is not yet true.** Two CSS specificity collisions — both of which I reproduced from the cascade rather than taking on trust — leave an above-the-fold conversion badge at 1.06:1 (invisible) and strip the visible focus ring from the six most important CTAs at 1.03–1.21:1. Both are small, well-understood fixes with the correct tokens already declared. Both live in files whose comments assert the passing ratios that the cascade then overrides, which is precisely why a documentation-level check would have missed them.

**Second, "reach any part of the site" is not literally true**, in two distinct ways. The no-JS mobile gap is real and is *worse than the codebase records it*: `header.php` says the six category links are unreachable; in fact the entire five-item nav is. That mis-recording matters more than the gap itself, because a later phase reading that comment would close the wrong thing. Separately, `covid.html` and `problem-stari.html` are reachable from nowhere on the new site, having been linked from the legacy homepage — disclosed in the summary, but a reachability regression nonetheless, and `covid.html` is an EU-funding disclosure page that may carry a publication obligation.

One additional wiring break worth fixing in the same pass: the homepage CTAs hardcode a phone number instead of reading `site-config.php`, so the homepage and footer can silently drift to different numbers — exactly the class of defect the single-source-of-truth pattern was adopted to prevent.

---

_Verified: 2026-08-06_
_Verifier: Claude (gsd-verifier)_
