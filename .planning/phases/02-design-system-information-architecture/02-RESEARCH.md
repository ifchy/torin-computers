# Phase 2: Design System & Information Architecture — Research

**Researched:** 2026-08-05
**Domain:** Hand-authored responsive CSS design system + Bulgarian Cyrillic web typography + PHP 5.2 include templating, zero build tooling, FTP/shared hosting
**Confidence:** HIGH on typography and vendor-stack findings (verified by direct font-binary parsing, HarfBuzz shaping, and live HTTP probes); MEDIUM on IA/SEO guidance (official docs + cross-corroborated web sources)

---

## User Constraints (from CONTEXT.md)

`.planning/phases/02-design-system-information-architecture/02-CONTEXT.md` is canonical and carries 42 numbered decisions (D-01…D-42). The planner MUST read it in full. The following are the hard constraints this research treated as non-negotiable — research answers **how**, never **whether**:

### Locked Decisions (do not relitigate)

1. **Zero build tooling.** Plain HTML/CSS/JS + PHP `include()`, deployed by direct FTP file placement. No Node, no bundler, no npm, no Sass, no PostCSS. `.claude/CLAUDE.md` in this repo describes an Astro/Tailwind stack — **that recommendation was NOT taken**; the project went PHP-include (see `.planning/research/SUMMARY.md` §"Resolving the Stack/Architecture Tension" and REQUIREMENTS.md "Out of Scope": *"CMS/WordPress or Node/Astro build pipeline"*). Astro and Tailwind build pipelines are out of scope for this phase.
2. **Host runs PHP 5.2.17** (live-reconfirmed this session — see Environment Availability). All PHP must be 5.2-safe: no short array syntax `[]`, no namespaces, no closures, no `??`, no `::class`, no `<?=` unless `short_open_tag` is confirmed On.
3. **The 16 page filenames/URLs are locked** by SEO-04. The design system applies to already-scaffolded files; it does not create the page set. Three NEW category pages are additive and allowed (D-22).
4. **Theme B (`#ffc70a` amber + `#0e305d` navy) is the default and ships live** (D-02a). Theme A (`#fbad03` + `#0547dc`) is a dev-only comparison alternative. Theming via CSS custom properties so switching is a token swap (D-04).
5. **Single family for headings and body**, self-hosted, chosen for complete Cyrillic coverage (D-06). *(See Decision N-1 — a verified technical finding materially affects which family this should be.)*
6. **No parallax, no ScrollMagic, no pagePiling, no jQuery, no jQuery UI, no Modernizr** anywhere (D-32, DESIGN-01).
7. **Build target is `public_html/new/`** on `bell.host.bg`, previewed at `torin.bg/new`.
8. **D-11:** the folded catch-all «Не откривате проблема си?» must be **real HTML in the page**, not JS-loaded on click, so it indexes normally.
9. **D-23 publish gate:** no category page goes live until it has genuine content; until then its card links to the corresponding homepage section.
10. **D-34:** address links out to Google Maps + `LocalBusiness`/`ElectronicsStore` JSON-LD — **no embedded map iframe**.

### Claude's Discretion (verbatim from CONTEXT.md)

- Full palette derivation for both themes beyond the anchor colors — neutrals, surfaces, semantic/state colors, contrast ramps.
- Component and density language — corner radius, shadow depth, spacing scale, button shapes, and whether the two themes differ in character or only in color.
- Mobile menu behaviour and how the Услуги dropdown becomes an accordion/overlay.
- Timing of the **logo redraw** to SVG or 2× raster. Current `torin-logo.png` is 150×80 and will look soft on retina; the redraw should happen, but which phase it lands in is Claude's call.
- Exact icon set/style for the six categories, provided each slot is photo-swappable per D-37.
- New page filenames for the three missing categories, following the existing transliterated-Latin convention.
- CSS architecture and how tokens are organized.

### Deferred Ideas (OUT OF SCOPE)

- **Logo redraw to SVG / 2×** — agreed it should happen; timing left to Claude's discretion, not necessarily this phase. Depends partly on whether an original vector source exists (OWNER-QUESTIONS #11).
- **Photo replacement of category icons** — the design must make icon slots photo-swappable now (D-38), but actual photography is owner-supplied and lands in a later phase.
- **Component & density language deep-dive** (radius, shadows, spacing scale, whether themes differ in character) — offered but not discussed; left as Claude's discretion.
- **Mobile menu behaviour** — offered but not discussed; Claude's discretion.
- **TRUST-03 гаранция summary wording** on category pages — the template reserves the slot (D-24); the content is Phase 3.
- **v2 requirements** — PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01.

---

## Decisions the Planner Must Make

Research surfaced seven things CONTEXT.md did not settle. **N-1 is a direct conflict with a locked decision and must be surfaced to the user, not silently resolved.**

### N-1 — ⚠ CONFLICT WITH D-06/D-07: Inter cannot render Bulgarian localized letterforms. At all.

**D-07 states:** *"Verify at implementation time that Bulgarian localized letterforms … resolve correctly under `lang="bg"` via the OpenType `locl` feature."*

**Verified answer: they do not, and cannot.** Inter v4.1 — the latest release, published 2024-11-16 — contains **no Cyrillic script record in its GSUB table whatsoever**. Its `locl` feature exists but serves only Romanian/Moldavian and Catalan Latin. Setting `lang="bg"` does nothing. Full evidence in [Q1](#q1--bulgarian-cyrillic-typography-the-highest-risk-unknown).

This is not a "verify at implementation time" item any more — it is a settled negative. **D-06's stated rationale ("Chosen specifically for complete Cyrillic coverage") is half-right**: Inter has complete Cyrillic *glyph* coverage (248 codepoints, full Bulgarian alphabet) but zero Bulgarian *localization*. Bulgarian text in Inter renders in Russian-convention letterforms.

**The planner must put this decision to the user. Three viable paths:**

| Option | Font | Bulgarian forms | Risk | Payload (cyr+lat woff2) |
|---|---|---|---|---|
| **A — recommended** | **Sofia Sans** | **Default outlines are Bulgarian.** Correct with `lang="bg"`, `lang="en"`, or no `lang` at all. | **None** — no dependency on browser `locl` support | **66 KB** (25.6 + 40.4) |
| **B** | Manrope / Montserrat / Commissioner | Proper `cyrl/BGR` `locl`, 22–23 substitutions | Depends on the browser applying `locl` from `lang`; sources conflict on Safari | 14.5 KB cyr subset (Manrope) |
| **C** | Keep Inter (honour D-06 as written) | None — Russian-convention forms | Zero technical risk; a typographic/brand compromise, not a bug | 352 KB (unsubset VF) |

**Research recommends Option A (Sofia Sans).** Reasons, in order of weight:
1. It is the **only option that removes the entire browser-`locl`-support risk class** — the Bulgarian shapes are the default outlines, so nothing has to work for them to appear.
2. It was designed by **Lettersoup (Botio Nikoltchev) and Ani Petrova** and is licensed OFL. Nikoltchev is the author of the reference article on Bulgarian `.loclBGR` that the whole field cites. It is also, by name and origin, the Sofia typeface — a genuine brand fit for a Sofia repair shop.
3. Payload is **66 KB vs Inter's 352 KB**, which matters more than usual here because **this host serves no compression at all** (verified — see Q5).

**Option C is not unreasonable and should be presented honestly.** Per Denis Moyogo Jacquerye on the Inter issue thread: *"There is no requirement, as such, to have that style in Bulgarian. It's just a popular style so it's nice to have."* Most Bulgarian sites today use Russian-convention forms. The counterweight, per Stefan Peev on the same thread, is that the oval forms were codified as the official printed alphabet by the Bulgarian Language Institute (BAS) via the online «Официален правописен речник на българския език», and adoption in Bulgaria has accelerated over the last several years.

**D-06's "costly to reverse" note applies with full force** — this decision must land *before* Phase 3 writes content, because Sofia Sans has a materially smaller x-height than Inter (488 vs 546 per 1000 em) and needs a different type scale.

### N-2 — Which Google-Maps-linked JSON-LD `@type`?

D-34 says "`LocalBusiness`/`ElectronicsStore`". All of `ElectronicsStore`, `ComputerStore`, and `ProfessionalService` are real schema.org types (verified: all return HTTP 200 at schema.org). `ComputerStore` (Thing > Organization > LocalBusiness > Store > ComputerStore) is the most specific match for a shop that both repairs and sells laptops/parts, and there is **no repair-specific schema.org type**. Planner should pick one and record it; research recommends `ComputerStore` with `LocalBusiness` in the `@type` array as a fallback for consumers that don't know the subtype.

### N-3 — Working hours conflict on the live site (blocks the footer and the JSON-LD)

The live site states **two different sets of hours**:

| Source | Hours |
|---|---|
| `site-current/index.html`, `site-current/about.html` | Понеделник-Петък **8:00-16:00** |
| `site-current/profilaktika-laptop.html` | Понеделник-Петък **9:00-17:00** |
| `site-current/otpuska.js` (the "НОВО работно време" banner) | Понеделник-Петък **8:00 до 16:00** |

Two of three sources agree on 8:00–16:00 and the banner is labelled «НОВО», so `profilaktika-laptop.html` is almost certainly stale. But D-33 puts working hours in the footer on all 16 pages and D-34 puts them in `openingHoursSpecification`, so shipping the wrong value is site-wide. **Add to OWNER-QUESTIONS.md as a new item.** Interim: use 8:00–16:00 and mark it `[ASSUMED]`.

### N-4 — Is a `categories.php` data file in scope for this phase?

Six category names, symptom lines, slugs, and a `published` flag are consumed by **at least four** places: the homepage card grid (D-09/D-10), the Услуги dropdown (D-19), the category page templates (D-24), and — in Phase 4 — `sitemap.xml`. D-23's publish gate means each card's `href` flips between a real page and a homepage anchor based on one boolean. Research strongly recommends a single `src/includes/categories.php` array as a Phase 2 deliverable. The planner should decide whether this is its own plan or folds into the homepage plan.

### N-5 — Does the design system also fix `header.php`'s structural defects?

Three defects verified in the Phase 1 scaffold that the redesign cannot avoid touching:
- **`src/includes/header.php` emits no `<body>` tag at all.** Line 20 closes `</head>`; line 22 opens `<div id="wrap">`. `footer.php` line 17 closes `</body>`. Browsers auto-insert `<body>`, so nothing visibly breaks — but it is invalid HTML and any `<body class="…">` hook the design system wants does not exist.
- **`header.php` has no stylesheet `<link>` at all** and no `<meta name="description">`.
- **`header.php` emits `<i class="fa fa-phone">` and `<i class="fa fa-envelope-o">`** (Font Awesome classes) with no Font Awesome loaded — those icons currently render as nothing on all 16 scaffolded pages.
- The `<title>` is hardcoded to one string for all 16 pages. SEO-01 (per-page titles) is Phase 3, but the *mechanism* — `header.php` accepting per-page variables — must exist in Phase 2 or Phase 3 has nowhere to put them.

Planner should decide whether the per-page-metadata mechanism lands here (recommended: yes, it is design-system plumbing) or in Phase 3.

### N-6 — Add compression + caching to `.htaccess` in this phase, or defer to Phase 4?

**Verified live:** this host currently returns **no `Content-Encoding` and no `Cache-Control`/`Expires` header** on any asset. CSS and HTML are served uncompressed. `mod_headers` **is** confirmed working (the `/new/` `X-Robots-Tag` proves it). `mod_deflate`/`mod_expires` availability is unverified. This is nominally DESIGN-02 (Phase 4), but the design system's CSS budget is set here, and a wrong assumption about compression changes CSS architecture decisions. Research recommends a small Wave-0 spike task in this phase to prove `mod_deflate` works, with the actual tuning left to Phase 4.

### N-7 — Alpine.js: in or out?

CLAUDE.md's stack table lists Alpine.js as a supporting library. **Research recommends leaving it out entirely.** Total interactivity this phase needs is a nav disclosure, a mobile menu toggle, and native `<details>` accordions — roughly 40–60 lines of vanilla JS. Alpine is ~15 KB gzipped, and **this host serves nothing gzipped**, so it would ship as ~44 KB of raw JS to do less than 60 lines' work. The planner should record this as a deliberate rejection of the CLAUDE.md suggestion, not an oversight.

---

## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| **DESIGN-01** | Modern, mobile-responsive layout replacing the heavy jQuery/parallax "Liquid" theme | Q4 gives the verified vendor-stack inventory with per-item disposition and the three items whose behaviour must be re-provided. Q5 gives the responsive grid/hero patterns and a concrete browser baseline derived from Bulgarian StatCounter data. Q3 gives the vanilla-JS replacements for the removed jQuery behaviours. |
| **IA-01** | Six distinct category sections, not one undifferentiated icon-box scroll | Q4 maps the verified 15 existing `#our-services` icon-boxes; Q5 gives the six-card grid with photo-swappable media slot (D-38); Q3 gives the `<details>`-based folded catch-all (D-11) with evidence on how Google treats it. N-4 recommends the `categories.php` data file that keeps grid, dropdown, and pages in sync. |
| **IA-02** | Flat, shallow nav around the six categories, no dense mega-menu | Q3 gives the W3C-APG Disclosure Navigation pattern (explicitly *not* `role="menu"`), full ARIA/keyboard contract, and a single-implementation mobile/desktop strategy. |
| **SEO-02** | Every page declares `lang="bg"` | Q1 (typography actually renders Bulgarian) and Q6 (`lang` correctness, charset placement, encoding hygiene, and what SEO-02 needs beyond the attribute). `lang="bg"` is already present at `src/includes/header.php:8`. |

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|---|---|---|---|
| Page assembly / shared chrome | PHP (server, 5.2.17) | — | `include()` is the whole templating layer; there is no build step and no client-side templating |
| Design tokens & theming | CSS custom properties (browser) | PHP (emits `data-theme` attr) | Token swap is a CSS concern; only the *selection* of the theme is server-side, and only in dev |
| Dev theme switcher | PHP (server) | Browser (cookie) | Must be provably absent from production — server-side file-existence gate is verifiable by inspecting the deployed file list |
| Responsive layout | CSS (browser) | — | Grid/flex + media queries; no JS layout |
| Typography / Bulgarian letterforms | Font binary + browser shaping | HTML `lang` attribute | The font's own default outlines are the reliable layer (N-1 Option A); `lang` is the fallback trigger |
| Nav disclosure + mobile menu | Vanilla JS (browser) | CSS (presentation only) | ~50 lines; one JS implementation, media queries change presentation |
| Folded catch-all accordion | HTML `<details>` (browser-native) | — | Zero JS; content in DOM so it indexes; browsers auto-expand for find-in-page |
| Structured data (JSON-LD) | PHP (server, from `site-config.php`) | — | Rendered once in `footer.php`/`header.php`, values single-sourced |
| Category data (names, slugs, published flag) | PHP array (`categories.php`) | — | Feeds grid, dropdown, page templates, and later `sitemap.xml` from one place |
| Compression / caching | Apache `.htaccess` | — | No CDN, no origin app server; `mod_deflate`/`mod_expires` are the only levers |

---

## Q1 — Bulgarian Cyrillic typography: the highest-risk unknown

*(D-06, D-07, SEO-02, success criterion 4)*

### 1a. Does Inter ship Bulgarian `locl`? **No. Verified three independent ways.**

**Evidence 1 — Inter's own GSUB table.** Downloaded `Inter-4.1.zip` from `github.com/rsms/inter/releases/download/v4.1/` (tag `v4.1`, published `2024-11-16T00:27:07Z`, confirmed the latest release via the GitHub API), extracted `InterVariable.ttf`, parsed with fontTools:

```
SCRIPT 'DFLT' LANGSYS []
SCRIPT 'latn' LANGSYS ['CAT ', 'MOL ', 'ROM ']
GSUB FEATURES: [aalt, calt, case, ccmp, cv01…cv14, dlig, dnom, frac, locl, numr,
                ordn, pnum, salt, sinf, ss01…ss08, subs, sups, tnum, zero]
has locl: True
Cyrillic codepoints: 248
```

**There is no `cyrl` script record.** The `locl` feature's entire substitution content is:

```
locl lookups → {'Scedilla': 'uni0218', 'scedilla': 'uni0219', 'uni0163': 'uni021B'}
             + one ChainContextSubst (Catalan punt volat)
```

Romanian/Moldavian comma-below and Catalan. Nothing Cyrillic. `[VERIFIED: fontTools parse of Inter-4.1 InterVariable.ttf, this session]`

**Evidence 2 — HarfBuzz shaping** (the actual engine Chrome, Firefox, and Android use). Shaping `вгджзийклптцшщю` with `script=Cyrl`:

```
Inter 4.1     differ bg-vs-ru: 0/15
   bg: uni0432 uni0433 uni0434 uni0436 uni0437 uni0438 uni0439 …
   ru: uni0432 uni0433 uni0434 uni0436 uni0437 uni0438 uni0439 …
```

Identical glyph runs. `lang="bg"` is a no-op for Inter. `[VERIFIED: uharfbuzz shaping, this session]`

**Evidence 3 — upstream issue status.** `rsms/inter` issue **#562** ("Request: Localized Cyrillic for Bulgarian, Macedonian & Serbian") is **still OPEN**: created `2023-04-22`, 11 comments, last comment `2025-03-20`, `closed_at: None`. Opening line: *"Currently, Inter uses default traditional Cyrillic for all Cyrillic languages."* rsms's own reply (2023-04-29) asks for glyph-level specs, i.e. no implementation exists. `[VERIFIED: GitHub API rsms/inter#562]`

> **Inter DOES cover Bulgarian.** All 60 letters of the Bulgarian alphabet are present. Text renders correctly — it just renders in Russian-convention letterforms. This is a typographic/brand issue, not a legibility or mojibake issue.

### 1b. What are the real options?

Every candidate below was verified the same two ways (fontTools GSUB parse + HarfBuzz `bg` vs `ru` shaping) against the actual binary served by Google Fonts.

| Font | Cyrillic cps | `cyrl` langsys | BG substitutions | bg-vs-ru glyphs differ | Mechanism |
|---|---|---|---|---|---|
| **Inter 4.1** | 248 | **none** | **0** | **0/15** | — |
| **Sofia Sans** | 104 | `RUS` | 30 `.loclRUS` | **15/15** | **Default = Bulgarian**; `locl` switches *to* Russian |
| **Manrope** | 104 | `BGR`, `SRB` | 23 `.loclBGR` | 15/15 | `locl` under `lang="bg"` |
| **Montserrat** | 222 | `BGR`,`BSH`,`CHU`,`MKD`,`SRB` | 23 `.loclBGR` | 15/15 | `locl` under `lang="bg"` |
| **Commissioner** | 222 | `BGR`,`BSH`,`CHU`,`SRB` | 22 `.loclBGR` | 15/15 | `locl` under `lang="bg"` |
| Fira Sans *(Google Fonts build)* | 240 | **none** | **0** | 0/15 | — ⚠ see note |
| Onest | 82 | `cyrl` present, no langsys | 0 | 0/15 | — |

All six confirmed to contain the **complete Bulgarian alphabet** (`АБВГДЕЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЬЮЯ` + lowercase) — no missing glyphs anywhere.

⚠ **Fira Sans caveat worth recording:** the widely-cited claim that "Fira Sans has 62 Bulgarian alternates" refers to the **upstream bBoxType/FiraGO build**, not the font Google Fonts serves. The Google Fonts `FiraSans-Regular.ttf` has **no `cyrl` script at all**. Do not adopt Fira Sans on the strength of the reputation without checking the specific binary you plan to ship. `[VERIFIED: fontTools parse of google/fonts ofl/firasans/FiraSans-Regular.ttf]`

**The decisive property of Sofia Sans** — verified by shaping the same three letters under four language settings:

```
Sofia Sans   lang=bg    ['uni0432', 'uni0433', 'uni0434']          ← Bulgarian
Sofia Sans   lang=ru    ['uni0432.loclRUS', …]                     ← Russian
Sofia Sans   lang=en    ['uni0432', 'uni0433', 'uni0434']          ← Bulgarian
Sofia Sans   lang=None  ['uni0432', 'uni0433', 'uni0434']          ← Bulgarian

Manrope      lang=bg    ['uni0432.loclBGR', …]                     ← Bulgarian
Manrope      lang=ru    ['uni0432', 'uni0433', 'uni0434']          ← Russian-convention
Manrope      lang=en    ['uni0432', 'uni0433', 'uni0434']          ← Russian-convention
Manrope      lang=None  ['uni0432', 'uni0433', 'uni0434']          ← Russian-convention
```

Sofia Sans renders Bulgarian correctly **even if `lang` is wrong, missing, or the browser ignores `locl` entirely**. Every `locl`-based font fails open to Russian forms. `[VERIFIED: uharfbuzz shaping, this session]`

**Sofia Sans metadata** `[VERIFIED: google/fonts ofl/sofiasans/METADATA.pb + name table]`:
- Designers: Lettersoup, **Botio Nikoltchev**, Ani Petrova. License **OFL**. Version 4.101.
- Variable, single `wght` axis **1–1000**, default 400. Italic companion available.
- Subsets: latin, latin-ext, cyrillic, cyrillic-ext, greek.

### 1c. What actually triggers `locl` in browsers?

**Sources conflict, and the conflict is the finding.** Report it as such rather than picking a side:

- **`lang="bg"` on `<html>` is the correct and semantically right trigger.** `locl` is an *always-on-by-default* OpenType feature; the shaping engine reads the language from the element's language and applies the matching `cyrl/BGR` langsys. `[CITED: medium.com/@mevbg — "Bulgarian Cyrillic on the Web"]`
- **`font-feature-settings: "locl" 1` cannot help.** It only toggles a feature that is already on; it has no way to express *which language's* `locl`. There is no CSS property that selects a `locl` language. `[ASSUMED — reasoned from the OpenType/CSS Fonts model, not verified in a browser this session]`
- **`font-variant-alternates` is not the right property either** — `locl` is not exposed through the `font-variant-*` family. `[ASSUMED]`
- **`-webkit-locale: "bg"`** is the CSS-side escape hatch in Blink/WebKit when the `lang` attribute is unavailable. `[CITED: medium.com/@mevbg]`
- **The conflict:** Typotheque's "OpenType features in CSS" article states localized forms are *"not supported at all in Safari (Mac or iOS) or on Android"*, while the mevbg article (much more recent, and Bulgarian-specific) treats `lang="bg"` as *"the most reliable and semantically correct method"* with no Safari caveat. The Typotheque piece is old enough that it likely predates current CoreText behaviour. **Neither claim was verified in a live browser this session.** `[CITED: typotheque.com/articles/opentype-features-in-css]` vs `[CITED: medium.com/@mevbg]`

Safari is **11.6%** of Bulgarian browser traffic (July 2026, StatCounter — see Q5), so an unresolved Safari `locl` question would affect roughly one visitor in nine. **Choosing Sofia Sans makes the question moot**, which is the single strongest argument for Option A.

### 1d. Self-hosting mechanics with no build step

**The subsetting problem has a zero-build solution.** Google Fonts' `css2` API already serves per-script pre-subset woff2 files. Fetch the CSS with a modern browser UA, take the two `src:` URLs you need, download them, and rewrite the paths. That is the whole "build step".

```bash
# One-off, run once on the dev machine. Not a project dependency.
UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'
curl -s -A "$UA" 'https://fonts.googleapis.com/css2?family=Sofia+Sans:wght@400..800&display=swap'
# → yields one @font-face per subset with a stable fonts.gstatic.com URL each
curl -o src/fonts/sofia-sans-cyrillic.woff2 'https://fonts.gstatic.com/s/sofiasans/v20/Yq6R-LCVXSLy9uPBwlATrOV6kigt.woff2'
curl -o src/fonts/sofia-sans-latin.woff2    'https://fonts.gstatic.com/s/sofiasans/v20/Yq6R-LCVXSLy9uPBwlATrOF6kg.woff2'
```

**Verified: the Google Fonts subsets keep the OpenType layout tables.** This was a real risk — subsetters routinely strip GSUB — so it was checked:

| File | Size | Glyphs | `fvar` axes | `cyrl` langsys | BG alphabet |
|---|---|---|---|---|---|
| `sofia-sans-cyrillic.woff2` | **25,568 B** | 171 | `wght 1–1000` | `RUS` (30 `.loclRUS`) | complete |
| `sofia-sans-latin.woff2` | **40,372 B** | 276 | `wght 1–1000` | — | n/a |
| `manrope-cyrillic.woff2` *(option B)* | **14,500 B** | — | `wght` | `BGR`,`SRB` (23 `.loclBGR`) | complete |

`[VERIFIED: fontTools parse of the downloaded woff2 files, this session]`

**Total font payload for Option A: 66 KB, variable weight 400–800 in two files.** Compare Inter self-hosted from rsms: `InterVariable.woff2` is **352,240 bytes** unsubset. On a host that serves nothing compressed (verified in Q5), that difference is real.

**`@font-face` block** — put this in `base.css`, above everything else:

```css
/* Sofia Sans — self-hosted, OFL. Subsets pulled from Google Fonts css2 v20. */
@font-face {
  font-family: 'Sofia Sans';
  font-style: normal;
  font-weight: 400 800;          /* variable range; browser picks along wght axis */
  font-display: swap;            /* text is visible immediately in the fallback */
  src: url('../fonts/sofia-sans-cyrillic.woff2') format('woff2');
  unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
}
@font-face {
  font-family: 'Sofia Sans';
  font-style: normal;
  font-weight: 400 800;
  font-display: swap;
  src: url('../fonts/sofia-sans-latin.woff2') format('woff2');
  unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA,
                 U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122,
                 U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
```

The `unicode-range` values above are copied verbatim from the Google Fonts response `[VERIFIED: fonts.googleapis.com/css2 response, this session]`. Keeping them means a Bulgarian-only page downloads the 25 KB Cyrillic file and *not* the 40 KB Latin file until Latin characters actually appear (they will — phone numbers, "USB", "HDMI", "BGA" — so budget for both).

**`font-display`:** use `swap`. Rationale: this is a business whose core value is *"immediately see that Torin fixes exactly that"* — invisible text during font load works directly against that. `optional` would be defensible but causes the font to be skipped entirely on slow first loads, producing inconsistent branding.

**Preload:** preload **only the Cyrillic file** — it is what renders the visible headline text on a Bulgarian page. Preloading both doubles the critical-path bytes for no first-paint benefit.

```html
<link rel="preload" href="fonts/sofia-sans-cyrillic.woff2" as="font" type="font/woff2" crossorigin>
```

The `crossorigin` attribute is **required even for same-origin font preloads** — fonts are always fetched in CORS mode, and omitting it causes a double download. `[ASSUMED — well-established behaviour, not re-verified this session]`

**Fallback stack** — must be Cyrillic-capable on every platform:

```css
--font-sans: 'Sofia Sans', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
```

### 1e. Metric consequences for the type scale

Measured from the actual binaries (per 1000 em units) `[VERIFIED: fontTools OS/2 + hmtx, this session]`:

| Font | x-height | cap height | `н` adv | `о` adv | `м` adv |
|---|---|---|---|---|---|
| Inter | 546 | 728 | 588 | 600 | 770 |
| **Sofia Sans** | **488** | 655 | 544 | 536 | 626 |
| Montserrat *(light)* | 517 | 700 | 663 | 627 | 775 |

Sofia Sans's x-height is **~11% smaller than Inter's** and it is **~8% narrower**. Two concrete instructions for the planner:

1. Set the base body size **one step larger** than an Inter-derived scale would suggest — target ~17px rather than ~16px — so apparent text size matches.
2. Bulgarian Cyrillic lowercase has fewer ascenders/descenders than Latin, which reduces word-shape variety. Combined with the lower x-height, use a **generous body line-height (1.6–1.7)** and cap measure at **60–70 characters**. Do not carry over a tight Latin-tuned `line-height: 1.45`.

---

## Q2 — CSS custom-property theming with a dev-only switcher, no build step

*(D-02, D-02a, D-03, D-04)*

### 2a. The pattern that makes Phase 4 a deletion, not a refactor

**Do not put both themes in one tokens file.** If Theme A lives in the same file as Theme B, Phase 4's "hard-bake the chosen theme and delete the switcher" means *editing* the token file — a refactor with a diff to review. Instead:

```
src/css/
  base.css        ← :root tokens = Theme B, unconditional. SHIPS.
  layout.css      ← SHIPS.
  components.css  ← SHIPS.
  theme-a.css     ← ONLY  [data-theme="a"] { … }  — DEV ONLY, deleted at cutover
src/includes/
  dev-switcher.php ← DEV ONLY, deleted at cutover
```

`base.css`:

```css
:root {
  /* ── Theme B (business.css-derived) — DEFAULT, SHIPS LIVE per D-02a ───── */
  --c-brand:          #ffc70a;   /* D-01: verified in business.css :root      */
  --c-brand-hi:       #ffcd2b;   /* D-01: gradient stop                        */
  --c-ink-deep:       #0e305d;   /* D-01: --color-secondary                    */
  --c-on-brand:       #16223a;   /* text on amber — amber is a light surface   */
  /* neutrals, surfaces, semantic colours: Claude's discretion */
}
```

`theme-a.css` — the *entire* file:

```css
/* DEV ONLY. Theme A = logo-derived (D-02). Deleted at Phase 4 cutover. */
[data-theme="a"] {
  --c-brand:    #fbad03;
  --c-brand-hi: #ffc22e;
  --c-ink-deep: #0547dc;
  --c-on-brand: #1a1200;
}
```

Phase 4 cutover then = **delete two files, delete three lines from `header.php`.** Zero token edits, nothing to diff-review in the production CSS. This is the answer to "clean deletion rather than a refactor".

> **Build Theme B first and treat it as the reference implementation** (D-02a explicitly warns that Theme A must not become the better-tested one). Because Theme B is the unconditional `:root` and Theme A is a 6-line override, that property is structurally enforced rather than merely intended.

### 2b. Keeping the switcher provably dev-only

Three candidate guards were considered:

| Guard | Provable? | Verdict |
|---|---|---|
| Path check (`strpos($_SERVER['REQUEST_URI'], '/new/')`) | Weak — depends on a runtime value, and `REQUEST_URI` is client-influenced | **Reject.** Trusting a request value to gate a feature is the wrong shape, even for a benign feature. |
| Constant in `site-config.php` (`define('TORIN_DEV', true)`) | Weak — `site-config.php` is a file you *do* upload to production; the flag can be forgotten | **Reject** |
| **File-existence sentinel (`file_exists('includes/dev-switcher.php')`)** | **Strong** — the guard is "is this file on the server", verifiable by listing the deployed directory | **Recommend** |

The file-existence guard is the right one because the verification is *"does `includes/dev-switcher.php` exist in `public_html/`"* — a question answerable by an FTP listing or a single `curl`, with no code reading required.

`src/includes/header.php` (three dev lines, marked for deletion):

```php
<?php
require_once(dirname(__FILE__) . '/site-config.php');

// ── DEV-ONLY THEME SWITCHER (D-03) — delete these 3 lines at Phase 4 cutover ──
$torin_html_attr  = '';
$torin_extra_head = '';
$torin_dev_switcher = dirname(__FILE__) . '/dev-switcher.php';
if (file_exists($torin_dev_switcher)) { include($torin_dev_switcher); }
// ── END DEV-ONLY ─────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="bg"<?php echo $torin_html_attr; ?>>
<head>
<meta charset="utf-8">
...
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
<?php echo $torin_extra_head; ?>
</head>
```

`src/includes/dev-switcher.php` (PHP 5.2-safe — `array()`, no closures, no `[]`, no `<?=`):

```php
<?php
// DEV ONLY (D-03). Never upload to public_html root. Deleted at Phase 4 cutover.
// Selects Theme A or B and renders the comparison control at torin.bg/new.

$torin_allowed_themes = array('a', 'b');
$torin_theme = 'b';                                    // D-02a: Theme B is default

if (isset($_COOKIE['torin_theme']) && in_array($_COOKIE['torin_theme'], $torin_allowed_themes, true)) {
    $torin_theme = $_COOKIE['torin_theme'];
}
if (isset($_GET['theme']) && in_array($_GET['theme'], $torin_allowed_themes, true)) {
    $torin_theme = $_GET['theme'];
    setcookie('torin_theme', $torin_theme, time() + 2592000, '/');
}

// Whitelist-selected literal, never a reflected request value → no XSS surface.
if ($torin_theme === 'a') {
    $torin_html_attr = ' data-theme="a"';
}
$torin_extra_head = '<link rel="stylesheet" href="css/theme-a.css">';

function torin_render_theme_switcher($current) {
    echo '<div class="dev-switcher" role="group" aria-label="Тема (само за разработка)">';
    echo '<a href="?theme=b"' . ($current === 'b' ? ' aria-current="true"' : '') . '>Тема B</a>';
    echo '<a href="?theme=a"' . ($current === 'a' ? ' aria-current="true"' : '') . '>Тема A</a>';
    echo '</div>';
}
?>
```

**Security note (ASVS V5.1, Output Encoding):** `$_GET['theme']` is validated against a hardcoded whitelist **before** any use, and the value written into the HTML attribute is a **literal string chosen by the code**, never the request value. This is the difference between safe and a reflected-XSS hole; if a future edit changes it to `echo ' data-theme="' . $_GET['theme'] . '"'`, that is an injection. Flag this in the plan so the executor does not "simplify" it.

**Note on `theme-a.css` loading:** it is loaded unconditionally in dev (whenever `dev-switcher.php` exists), and the `[data-theme="a"]` selector simply never matches when Theme B is active. This keeps the toggle a single attribute flip with no re-request, which is exactly what makes side-by-side comparison usable.

### 2c. CSS architecture for hand-authored, no-build CSS

**Verified constraint that decides this:** `bell.host.bg` serves **HTTP/2** (`curl -s -o /dev/null -w "%{http_version}" https://torin.bg/` → `2`). Multiplexing means the classic "concatenate everything to reduce requests" pressure is gone. `[VERIFIED: live curl probe, 2026-08-05]`

**Counter-constraint, also verified:** the host sends **no `Content-Encoding`** and **no `Cache-Control`/`Expires`** on any asset. CSS ships raw and is only heuristically cached from `Last-Modified`. So the pressure is on **total bytes**, not request count.

**Recommended: three production files + one dev file**, linked in order (cascade order = link order; no `@layer` needed and no build step to enforce it):

| File | Contains | Rough budget |
|---|---|---|
| `base.css` | `@font-face`, modern reset, `:root` tokens (Theme B), element defaults, fluid type scale | ≤ 6 KB |
| `layout.css` | container, grid utilities, header/footer shells, section rhythm | ≤ 4 KB |
| `components.css` | nav + disclosure, hero, category card, accordion, buttons, CTA block, trust row | ≤ 10 KB |
| `theme-a.css` *(dev only)* | `[data-theme="a"]` overrides | < 0.5 KB |

**Total production CSS target: under 20 KB raw.** For scale, the theme being replaced is **604,610 bytes** of `theme.min.css` + **119,076 bytes** of `theme-vendors.min.css` — a ~36× reduction, all of it uncompressed on this host. `[VERIFIED: ls -la site-current/assets1/css/]`

CSS **nesting** and `@layer` are both Baseline in 2026 and would work with no build step `[CITED: web.dev/baseline]`, but research recommends **not** using nesting for the initial build: flat selectors are easier to grep, easier to hand-edit from FTP in an emergency, and this project has no linter to catch a mis-nested block.

---

## Q3 — Replacing jQuery-era interactivity with vanilla JS

*(DESIGN-01, IA-02, D-11, D-18, D-19, D-32)*

**Total JS this phase needs: one file, ~50 lines.** Recommended: `src/js/site.js`, loaded with `defer`.

### 3a. The Услуги dropdown — use the Disclosure pattern, NOT `role="menu"`

The W3C ARIA Authoring Practices Guide is explicit that navigation dropdowns are a **Disclosure Navigation** pattern, not a menu: *"the disclosure pattern does not use the WAI-ARIA menu role because it does not provide the complex functionality that assistive technologies expect in a widget that has the menu role."* `[CITED: w3.org/WAI/ARIA/apg/patterns/disclosure/examples/disclosure-navigation]`

Getting this wrong is a common and damaging mistake: `role="menu"` makes screen readers announce a desktop-application menu and puts arrow keys in charge of navigation, which is not what six links in a nav are.

**Markup** (D-18's five items, D-19's single-level dropdown):

```html
<nav class="nav" aria-label="Основна навигация">
  <button class="nav__toggle" id="navToggle"
          aria-expanded="false" aria-controls="navList">
    <span class="visually-hidden">Меню</span>
    <svg aria-hidden="true" focusable="false" …><!-- hamburger --></svg>
  </button>

  <ul class="nav__list" id="navList">
    <li><a href="index.html">Начало</a></li>

    <li class="nav__item--has-sub">
      <button class="nav__disclosure" id="uslugiBtn"
              aria-expanded="false" aria-controls="uslugiList">
        Услуги
        <svg class="nav__chevron" aria-hidden="true" focusable="false" …></svg>
      </button>
      <ul class="nav__sub" id="uslugiList">
        <!-- six items, rendered from includes/categories.php (N-4) -->
        <li><a href="mehanichni-problemi.html">Счупвания и механични повреди</a></li>
        <li><a href="index.html#kat-2">Екран, клавиатура и портове</a></li>
        <li><a href="optimizatsiq.html">Оптимизация</a></li>
        <li><a href="zalivane-technosti.html">Заливане и ремонт на дънни платки</a></li>
        <li><a href="index.html#kat-5">Прегряване и охлаждане</a></li>
        <li><a href="index.html#kat-6">Нестандартна техника</a></li>
      </ul>
    </li>

    <li><a href="laptopi.html">Лаптопи и части</a></li>
    <li><a href="test-laptop.html">Тествай сам</a></li>
    <li><a href="index.html#contact-us">Контакти</a></li>
  </ul>
</nav>
```

Note the `href` mix: published pages link to the page, unpublished ones to the homepage section — **exactly D-23's publish gate**, and the reason the `href` must come from `categories.php` rather than be typed in the nav (N-4).

**Required keyboard contract** `[CITED: W3C APG Disclosure Navigation]`:

| Key | Behaviour |
|---|---|
| `Enter` / `Space` on the button | Toggle the dropdown |
| `Escape` | Close the open dropdown **and return focus to its button** |
| `Tab` / `Shift+Tab` | Move among buttons and links normally; **moving focus out of the nav region closes the dropdown** |
| Arrow keys | Optional enhancement — do not implement, it invites menu-role expectations |

The APG notes the Escape behaviour is **required for WCAG 2.1 SC 1.4.13 (Content on Hover or Focus)** — it is not optional polish.

**JS** (~35 lines, no dependencies):

```js
// src/js/site.js — loaded with <script src="js/site.js" defer></script>
(function () {
  'use strict';
  var nav = document.querySelector('.nav');
  if (!nav) return;

  var disclosures = nav.querySelectorAll('[aria-expanded][aria-controls]');

  function setExpanded(btn, open) {
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  }
  function closeAll(except) {
    Array.prototype.forEach.call(disclosures, function (b) {
      if (b !== except) setExpanded(b, false);
    });
  }

  Array.prototype.forEach.call(disclosures, function (btn) {
    btn.addEventListener('click', function () {
      var open = btn.getAttribute('aria-expanded') === 'true';
      closeAll(btn);
      setExpanded(btn, !open);
    });
  });

  // Escape closes and restores focus to the controlling button (WCAG 1.4.13)
  nav.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var open = nav.querySelector('[aria-expanded="true"][aria-controls]');
    if (!open) return;
    setExpanded(open, false);
    open.focus();
  });

  // Focus leaving the nav closes any open dropdown
  document.addEventListener('focusin', function (e) {
    if (!nav.contains(e.target)) closeAll(null);
  });

  // Click outside closes
  document.addEventListener('click', function (e) {
    if (!nav.contains(e.target)) closeAll(null);
  });
})();
```

**CSS drives all visual state off `aria-expanded`** — never off a separate `.is-open` class. This makes the ARIA state and the visual state structurally impossible to desynchronise, which is the single most common bug in hand-rolled dropdowns:

```css
.nav__sub                          { display: none; }
[aria-expanded="true"] + .nav__sub { display: block; }
.nav__chevron                      { transition: transform .18s ease; }
[aria-expanded="true"] .nav__chevron { transform: rotate(180deg); }
```

**No-JS behaviour:** with JS disabled, `aria-expanded` stays `"false"` and the six category links are unreachable from the nav. Mitigate by ensuring **every category is also reachable from the homepage card grid**, which it is by construction (IA-01). Acceptable; note it in the plan rather than building a CSS-checkbox fallback.

### 3b. Mobile nav — recommendation: **one implementation, CSS-only difference**

*(Claude's discretion per CONTEXT.md)*

**Recommended: hamburger → full-width in-flow panel, with Услуги as an inline accordion using the same disclosure button.**

Rationale:
- **One JS path, one ARIA contract, one set of bugs.** A separate mobile drawer component means two implementations of the same disclosure semantics, and the mobile one always rots first. The markup above already works on mobile; only the CSS changes.
- **An in-flow panel avoids the focus-trap problem.** A true overlay/drawer needs focus trapping, `inert`/`aria-hidden` on the background, and scroll locking — three more things to get right, for a five-item nav. An in-flow expanding panel needs none of them.
- **Touch behaviour of Услуги:** on touch, the disclosure is a `<button>`, so tap = toggle. There is deliberately **no hover-to-open** anywhere, including desktop — hover-opened menus are the classic source of the "menu opens when I'm just moving my cursor past it" complaint and are unusable on touch. Click/tap only, both breakpoints.

```css
/* Mobile-first: panel hidden, revealed by the toggle */
.nav__list { display: none; }
[aria-expanded="true"] + .nav__list { display: block; }

@media (min-width: 56.25rem) {          /* 900px — see Q5 breakpoints */
  .nav__toggle { display: none; }
  .nav__list   { display: flex; gap: var(--sp-4); align-items: center; }
  .nav__sub    { position: absolute; min-width: 20rem; z-index: 20; }
}
```

At ≥900px the sub-list becomes absolutely positioned (a dropdown); below it stays in flow (an accordion). Same DOM, same JS, same ARIA. **The `min-width: 20rem` on the desktop dropdown is the direct answer to D-19's constraint** — the six names total ~184 characters and cannot sit inline, but they fit comfortably at full readable length in a 320px-wide dropdown column.

### 3c. The folded catch-all «Не откривате проблема си?» — use `<details>`

**Recommendation: native `<details>`/`<summary>`, one per symptom group.**

**On indexability (the D-11 requirement):** Google's position, stated repeatedly by Gary Illyes and John Mueller, is that content hidden on initial view is treated as normal content, provided it is **present in the HTML**. Content in the DOM at load — even under `display: none` — is crawled and indexed. `[CITED: searchenginejournal.com/ranking-factors/tabbed-content/, lumar.io/office-hours/hidden-content/, varn.co.uk]`

`<details>` puts the content in the served HTML unconditionally, so it satisfies D-11's "real HTML in the page, not JS-loaded" exactly.

**The honest caveat, which the planner must carry forward:** indexed is not the same as weighted. Multiple SEO practitioners report that collapsed content, while indexed, appears to be **given less ranking weight than visible content**, and OuterBox's testing found visible content outperforming tabbed/toggled content. `[CITED: outerboxdesign.com, seoexamples.com]` — MEDIUM confidence; this is practitioner consensus, not a Google statement.

**This materially strengthens the D-13 / OWNER-QUESTIONS #9 concern.** D-13 places the battery-regeneration story (DIFF-02) inside the folded section as a deliberate downgrade. This research adds a second cost to that trade-off: it is not only less *visible*, it is plausibly less *rankable*. Battery regeneration is a genuine differentiator no competitor offers — i.e. exactly the kind of content that could rank on its own. The planner should note this alongside D-13 rather than treat the trade-off as already fully priced.

**Why `<details>` rather than a JS-toggled section:**

| | `<details>` | JS-toggled div |
|---|---|---|
| Content in HTML | Yes | Yes (if built correctly) |
| Works with JS off | Yes | No |
| Find-in-page reveals content | **Yes — all engines auto-expand `<details>` on find-in-page** | No |
| Fragment link (`#id`) opens it | Yes, natively | Needs code |
| Keyboard + screen reader | Native, correct, zero code | Must be hand-built |
| Lines of JS | **0** | ~20 |
| Styleable/animatable | Yes — `::details-content` is Baseline Newly available (Sept 2025), `transition-behavior: allow-discrete` Baseline since Aug 2024 | Yes |

`[CITED: developer.mozilla.org/en-US/blog/html-details-exclusive-accordions, developer.chrome.com/docs/css-ui/exclusive-accordion]`

**Do NOT use `hidden="until-found"`.** It solves the find-in-page problem for non-`<details>` collapsibles, but it only reached Baseline (all engines) in **December 2025** — Firefox shipped it May 2025, WebKit around September 2025. Too new to be load-bearing for a small-business site, and `<details>` already provides the behaviour natively everywhere. `[CITED: developer.chrome.com/docs/css-ui/hidden-until-found, MDN beforematch]`

**On `<details name="…">` (exclusive accordion):** Chrome/Edge 120+, Firefox 130+, Safari 17.2+ — Baseline Newly available, and it degrades perfectly (older browsers just let multiple sections stay open). Safe to use as progressive enhancement; do not depend on the exclusivity. `[CITED: MDN, Chrome for Developers]`

**Give each `<details>` a stable `id`** so the homepage card grid, the nav, and future internal links can deep-link into a specific symptom group — the browser opens the `<details>` automatically on fragment navigation.

```html
<section class="catch-all" aria-labelledby="ne-otkrivate-h">
  <h2 id="ne-otkrivate-h">Не откривате проблема си?</h2>

  <details id="sym-ne-se-vklyuchva" name="symptoms">
    <summary>Не се включва / няма реакция</summary>
    <div class="catch-all__body">
      <!-- real content, always in the HTML -->
    </div>
  </details>

  <details id="sym-baterii" name="symptoms">
    <summary>Батерията не държи</summary>
    <div class="catch-all__body"><!-- DIFF-02 / D-13 --></div>
  </details>
</section>
```

### 3d. Alpine.js — recommendation: **no**

See N-7. Total interactivity is ~50 lines of vanilla JS. Alpine is ~15 KB gzipped, and this host serves nothing gzipped, so it lands as ~44 KB raw — roughly **2.5× the entire production CSS budget** to replace 50 lines. It also reintroduces exactly the class of thing DESIGN-01 exists to remove: an attribute-driven runtime framework that has to boot before the UI works. Vanilla JS.

---

## Q4 — Safe removal of the Liquid template's vendor stack

*(DESIGN-01, success criterion 1)*

### 4a. Verified inventory — what the current pages actually load

Every figure below is from direct inspection of `site-current/`, not from the template's documentation.

**Scripts (`grep -oE 'src="[^"]*\.js[^"]*"' *.html` across all 16 pages):**

| Asset | On how many pages | Size | Loaded how | Disposition |
|---|---|---|---|---|
| `assets1/vendors/jquery.min.js` | **16/16** | 97,183 B | local | **DELETE** |
| `assets1/js/theme-vendors.js` | **16/16** | **529,711 B** | local | **DELETE** |
| `assets1/js/theme.min.js` | **16/16** | 186,459 B | local | **DELETE** |
| `assets1/vendors/modernizr.min.js` | **16/16** | 5,974 B | local, `async` | **DELETE** (D-32) |
| `static.zdassets.com/ekr/snippet.js` (Zendesk) | **16/16** | remote | 3rd-party CDN | **DELETE** — CONTACT-04, Phase 4; removing in Phase 2 is free |
| `otpuska.js` | 15/16 (all but `about.html`) | 550 B | local | **DECIDE** — see below |
| `https://ajax.googleapis.com/…/jquery/2.2.4/jquery.min.js` | **index.html only** | remote | CDN | **DELETE** |
| `http://cdnjs.cloudflare.com/…/modernizr/2.8.3/modernizr.js` | **index.html only** | remote, **plain HTTP** | CDN | **DELETE** — active mixed-content/MITM issue today |
| `header.js` | 0 (commented out at `index.html:224`) | 8,942 B | — | **DELETE** — dead code |

`[VERIFIED: grep + ls across site-current/, this session]`

Two CDN tags load over plain `http://` on an `https://` page — `cdnjs.cloudflare.com/…/modernizr.js` is fetched over an unauthenticated channel. Modern browsers block it as mixed active content, so it is currently *failing*, but it is a live script-injection surface if it ever loads. Removing it is a security fix as well as a performance one.

**ScrollMagic, pagePiling, and jQuery UI are not separate `<script>` tags** — they exist as directories under `assets1/vendors/` **and are bundled inside `theme-vendors.js`**. String scan of that one 530 KB file finds: `isotope` ×51, `lazyload` ×23, `imagesLoaded` ×18, `velocity` ×7, `ScrollMagic` ×6, `jQuery UI` ×2. **Practical consequence: deleting the three vendor directories does nothing on its own — the single `theme-vendors.js` line is what must go.** A verification that only greps for `scrollmagic.js` filenames will produce a false pass. `[VERIFIED: grep -oiE over site-current/assets1/js/theme-vendors.js]`

**Stylesheets (index.html):**

| Asset | Size | Disposition |
|---|---|---|
| `assets1/css/theme.min.css` | **604,610 B** | **DELETE** |
| `assets1/css/theme-vendors.min.css` | 119,076 B | **DELETE** |
| `assets1/vendors/liquid-icon/liquid-icon.min.css` | icon font | **DELETE** — replace with inline SVG |
| `assets1/vendors/font-awesome/css/font-awesome.min.css` | icon font | **DELETE** — replace with inline SVG (⚠ see 4b) |
| `assets1/css/themes/business.css` | 1,787 B | **DELETE** — but harvest `:root` first (D-01) |
| `assets1/css/animation.css` | 1,276 B | **DELETE** |
| `assets1/css/preloader.css` | 419 B | **DELETE** |
| `fonts.googleapis.com/css?family=Barlow:600,700` | remote | **DELETE** — serves no Cyrillic (already verified in CONTEXT.md `<specifics>`) |

**Decorative data-attributes (counts across all 16 pages):**

| Attribute | Occurrences | What it does | Disposition |
|---|---|---|---|
| `data-parallax` | 56 | scroll-linked background offset | **Pure decoration — vanishes** |
| `data-custom-animations` | 55 | entrance animations via anime.js | **Pure decoration — vanishes** |
| `data-fittext` | 36 | JS-computed responsive font size | **Replaced by `clamp()`** (Q5) |
| `data-split-text` | 32 | splits text into per-char/word spans to animate | **Pure decoration — vanishes**, and actively harmful for Cyrillic (see 4c) |
| `data-plugin-options` | 16 | icon hover-animation config | **Pure decoration — vanishes** |
| `data-inview` | 4 | viewport trigger | **Pure decoration — vanishes** |

`[VERIFIED: per-attribute grep counts across site-current/*.html]`

### 4b. Behaviour that something in the new design MUST still provide

Five items. Everything else is decoration that simply disappears.

1. **⚠ Font Awesome icon classes are already load-bearing in the Phase 1 scaffold.** `src/includes/header.php` lines 37 and 48 emit `<i class="fa fa-phone">` and `<i class="fa fa-envelope-o">`, and `header.php` links **no stylesheet at all**. Those icons render as nothing today on all 16 scaffolded pages. The redesign must replace them with **inline SVG**, not by adding Font Awesome — a full FA CSS + webfont is ~75 KB+ uncompressed on a host with no gzip, to draw two glyphs. `[VERIFIED: src/includes/header.php:33-52]`

2. **The mobile nav toggle** (`#main-header-collapse`, Bootstrap-4-era collapse) is real behaviour, not decoration. Replaced by §3b.

3. **`otpuska.js` — the holiday/hours banner.** 550 bytes, **already plain vanilla JS with no jQuery dependency**. It sets `#rssBlock` display and `#rssContent` innerHTML from a `const OTPUSKA` string, currently reading `В А Ж Н О !!! НОВО Работно време: понеделник до петък 8:00 до 16:00 часа`. This is genuine content, not decoration — and it is the third corroborating source for the working-hours conflict in N-3. **OWNER-QUESTIONS #8 asks whether to keep it.** Since it needs no library, the safe default is to preserve an equivalent (a static PHP-rendered notice band driven from `site-config.php`) rather than drop it. `[VERIFIED: site-current/otpuska.js:1-16]`

4. **The contact form.** `index.html:310` is `<form action="mailer.php" method="post">` — a plain HTML POST that **needs no JavaScript**. `liquidAjaxContactForm.min.js` exists in `assets1/js/` but is **not loaded by any page**. Removing the JS stack does not break the form. `[VERIFIED: grep of form tags + script tags]`

5. **The Google Maps iframe** at `index.html:940` — D-34 removes it deliberately, replaced by a Maps link + JSON-LD. Not a regression, but it is the current mechanism for "where are you", so the replacement must actually ship in the same change. Two useful values are recoverable from that iframe URL and should be reused (see Q6).

### 4c. Load-bearing CSS from the template

Scanned `theme.min.css` for anything the redesign keeps: `@font-face` blocks exist only for **Glacial Indifference** (`GlacialIndifference-{Regular,Bold}.{woff,woff2}`), which CONTEXT.md already verified has **zero Cyrillic glyphs**. Named families in the CSS are `Glacial Indifference`, `Roboto`, `Scheherazade`, `FontAwesome`, `liquid-icon`. **Nothing in the 604 KB is load-bearing for content the redesign keeps.** `[VERIFIED: grep of font-family and @font-face in site-current/assets1/css/]`

One genuine hazard worth flagging: **`data-split-text` wraps each character in its own `<span>`.** Applied to Cyrillic that is worse than merely decorative — it breaks text selection, copy/paste, screen-reader word boundaries, and find-in-page. Its removal is a correctness improvement, not just a cleanup.

---

## Q5 — Responsive layout on a hand-authored CSS baseline

*(DESIGN-01, D-30, D-31, D-38, success criterion 1)*

### 5a. Concrete browser baseline the planner can hold implementers to

**StatCounter, Bulgaria, all devices, July 2026** `[VERIFIED: gs.statcounter.com/browser-market-share/all/bulgaria, fetched 2026-08-05]`:

| Browser | Share |
|---|---|
| Chrome | **75.62%** |
| Safari | 11.60% |
| Firefox | 4.03% |
| Samsung Internet | 3.46% |
| Edge | 3.12% |
| Opera | 1.19% |

Every one of these is an evergreen engine. **Baseline "Widely available" is a safe target; Baseline "Newly available" is safe as progressive enhancement only.**

| Feature | Status | Use it? |
|---|---|---|
| CSS custom properties | Widely available (2016) | **Yes — load-bearing (D-04)** |
| Grid, Flexbox, `gap` | Widely available | **Yes** |
| `clamp()` / `min()` / `max()` | Widely available | **Yes — load-bearing** |
| `aspect-ratio` | Widely available | **Yes — load-bearing (D-38)** |
| `svh` / `dvh` viewport units | Widely available | **Yes (D-30)** |
| `:has()` | Baseline Newly available Dec 2023; universal by 2026 | Yes, but **never load-bearing** |
| Container queries | Baseline Widely available since 2024 | **Not needed** — see below |
| CSS Nesting | Baseline Newly available Aug 2023 | **No** — no linter to catch mistakes |
| Logical properties | Widely available | Optional; site is LTR-only, no benefit |
| `<details name>` exclusive accordion | Newly available (Chrome 120+/FF 130+/Safari 17.2+) | Yes, as enhancement |
| `::details-content` | Baseline Newly available Sept 2025 | Enhancement only |
| `hidden="until-found"` | Baseline Dec 2025 | **No** — too new; `<details>` covers it |

`[CITED: web.dev/baseline, blog.logrocket.com/container-queries-2026, developer.chrome.com/docs/css-ui/exclusive-accordion]`

**On container queries specifically:** they are Baseline and would work, but this layout has exactly one reusable card that appears in exactly one container context. Container queries buy nothing here and add a concept the next maintainer has to learn. Media queries.

### 5b. Six-category card grid (IA-01, D-38)

`repeat(auto-fit, minmax(…))` is the reflexive choice but is **wrong for a fixed set of six**: at intermediate widths it produces a 4+2 or 5+1 orphan row. With a known count, use explicit breakpoints so the six always break 6 → 3+3 → 2+2+2:

```css
.cat-grid {
  display: grid;
  gap: var(--sp-5);
  grid-template-columns: 1fr;                 /* < 35rem  : 1 up */
}
@media (min-width: 35rem)  { .cat-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 56.25rem) { .cat-grid { grid-template-columns: repeat(3, 1fr); } }
```

Breakpoints: **560px** and **900px** (`35rem` / `56.25rem`). 900px is also the nav's desktop breakpoint (§3b) — using one value for both keeps the system coherent.

**The photo-swappable media slot (D-38) — this is the pattern that makes the icon→photo swap a zero-layout-change edit:**

```css
.cat-card {
  display: flex;
  flex-direction: column;
  background: var(--c-surface);
  border-radius: var(--r-lg);
  overflow: hidden;
}
.cat-card__media {
  aspect-ratio: 16 / 10;        /* fixes the box independently of its contents */
  display: grid;
  place-items: center;
  background: var(--c-surface-2);
  overflow: hidden;
}
.cat-card__media > svg  { width: 38%; height: auto; color: var(--c-brand); }
.cat-card__media > img  { width: 100%; height: 100%; object-fit: cover; display: block; }
```

Because `aspect-ratio` sizes the box and the two child rules are mutually exclusive, replacing

```html
<div class="cat-card__media"><svg …>…</svg></div>
```

with

```html
<div class="cat-card__media"><img src="img/kategoria-1.jpg" alt="" width="640" height="400"></div>
```

changes **nothing** about the grid, the card height, or the surrounding rhythm. That is D-38 satisfied structurally rather than by convention. Always ship `width`/`height` on the `<img>` so the aspect box is correct before the image loads.

**Card body (D-10's symptom line):**

```html
<article class="cat-card" id="kat-1">
  <div class="cat-card__media">…</div>
  <div class="cat-card__body">
    <h3 class="cat-card__title">
      <a href="mehanichni-problemi.html">Счупвания и механични повреди</a>
    </h3>
    <p class="cat-card__symptoms">паднал лаптоп, счупен корпус, разхлабени панти</p>
  </div>
</article>
```

The `<a>` inside the `<h3>` (rather than wrapping the whole card) keeps the accessible name short and specific; use a stretched-link pseudo-element if the whole card should be clickable:

```css
.cat-card { position: relative; }
.cat-card__title a::after { content: ""; position: absolute; inset: 0; }
```

### 5c. Compact hero (D-30, D-31)

```css
.hero {
  min-height: clamp(18rem, 46svh, 24rem);   /* ~40–50vh, capped both ends  */
  display: grid;
  align-content: center;
  background: linear-gradient(135deg, var(--c-ink-deep) 0%, #14488a 55%, var(--c-brand) 165%);
  color: #fff;
  padding-block: var(--sp-7);
}
```

Three deliberate choices:

- **`svh`, not `vh`.** On mobile, `100vh` is the *large* viewport (URL bar retracted), so a `vh`-sized hero is taller than the visible area on first paint — the exact failure D-30 is correcting. `svh` is the small viewport.
- **`clamp()` with a hard `24rem` (384px) cap.** Fixes the above-the-fold arithmetic: on a 360×780 phone the CSS viewport is roughly 640px. Header ≈ 64px + hero ≤ 384px = 448px, leaving ~190px — enough that the first card's media box and title are visible without scrolling. Without the cap, a tall desktop-ish device pushes the grid off-screen and D-30's whole purpose is lost.
- **Gradient, not photography (D-31).** Zero asset dependency, instant render, and — the point D-31 makes — it is what gives the two themes a genuinely different feel in the dev switcher, since `--c-ink-deep` differs sharply between navy `#0e305d` and electric `#0547dc`.

**Contrast warning for Theme A:** amber-on-white and amber text generally will fail WCAG AA. `#ffc70a` on white is roughly 1.6:1. **Amber is a surface colour, not a text colour.** Buttons must be dark text on amber (`--c-on-brand: #16223a` on `#ffc70a` ≈ 11:1 — comfortable AA/AAA). The token set must encode this so the rule cannot be violated by accident. `[ASSUMED — contrast ratios estimated, not computed with a checker this session; the planner should add an explicit contrast-verification step]`

### 5d. Fluid type scale for a Cyrillic body face

```css
:root {
  /* fluid between 22.5rem (360px) and 90rem (1440px) viewports */
  --fs-900: clamp(2.00rem, 1.55rem + 2.00vw, 3.00rem);  /* hero h1     */
  --fs-800: clamp(1.60rem, 1.35rem + 1.11vw, 2.20rem);  /* section h2  */
  --fs-700: clamp(1.25rem, 1.14rem + 0.49vw, 1.50rem);  /* card h3     */
  --fs-500: clamp(1.0625rem, 1.03rem + 0.15vw, 1.125rem);/* body       */
  --fs-400: 0.9375rem;                                   /* small      */

  --lh-tight: 1.18;   /* headings */
  --lh-body:  1.65;   /* body — see below */
  --measure:  66ch;
}
body { font: var(--fs-500)/var(--lh-body) var(--font-sans); }
h1, h2, h3 { line-height: var(--lh-tight); text-wrap: balance; }
p, li { max-width: var(--measure); }
```

Two Cyrillic-specific adjustments, both derived from the measured metrics in Q1e:

1. **Body base is ~17px (1.0625rem), not 16px.** Sofia Sans's x-height is 488/1000 against Inter's 546 — an Inter-tuned 16px scale renders visibly smaller in Sofia Sans.
2. **Body line-height 1.65, not 1.45.** Bulgarian Cyrillic lowercase has markedly fewer ascenders and descenders than Latin, producing a flatter, more uniform text texture; combined with the lower x-height, tight leading hurts scanability. Note that the *current* site's `business.css` already uses `line-height: 1.7em` on body — the previous designer landed on the same conclusion. `[VERIFIED: site-current/assets1/css/themes/business.css:18]`

`text-wrap: balance` on headings is a genuine win here because Bulgarian category names are long (D-19: ~184 characters across six names) and produce ugly one-word last lines. It degrades to nothing where unsupported.

---

## Q6 — SEO / structural plumbing this phase must not get wrong

*(SEO-02, D-23, D-34)*

### 6a. `lang="bg"` and what SEO-02 actually needs beyond it

**Already correct:** `src/includes/header.php:8` reads `<html lang="bg">`. `[VERIFIED: src/includes/header.php:8]` Because all 16 pages include this file, SEO-02 is satisfied site-wide the moment the pages use the include. **The verification must confirm all 16 pages actually include `header.php`** — not that `header.php` contains the attribute.

Beyond the attribute, five things matter:

1. **`<meta charset="utf-8">` must appear in the first 1024 bytes.** It currently sits at line 13, *after* the favicon `<link>` at line 11 — byte-safe today, but fragile. Move it to be the first element in `<head>`. `[VERIFIED: src/includes/header.php:11-13]`
2. **`dir` is not needed.** Bulgarian is LTR; the default is correct. Do not add `dir="ltr"`.
3. **Drop `<meta http-equiv="X-UA-Compatible" content="IE=edge">`** (line 14) — obsolete, targets a browser that no longer exists.
4. **File encoding hygiene — the classic PHP-include mojibake trap.** Every `.php` and `.html` file must be UTF-8 **without BOM**. A BOM before `<?php` in an include emits three stray bytes before any output, which both corrupts rendering and breaks any later `setcookie()`/`header()` call with "headers already sent". **Currently clean** — all four Phase 1 files verified `utf-8` with first bytes `3c3f70` (`<?p`) / `3c666f` (`<fo`), no BOM. `[VERIFIED: file --mime-encoding + xxd on src/includes/*.php, src/index.html]` This must be re-checked after every new file, and it is a one-line shell check (see Validation).
5. **The `<title>` must become per-page.** Hardcoded to one string today. SEO-01 is Phase 3, but the mechanism belongs to the design system (see N-5).

Also correct at this point: `<meta name="theme-color" content="#3ed2a7">` at line 16 is the stale Liquid-template leftover D-01 identifies. Change to `#ffc70a`. `[VERIFIED: src/includes/header.php:16]`

### 6b. `LocalBusiness` JSON-LD for a Bulgarian repair shop (D-34)

**Google's required properties** for LocalBusiness structured data are `name` and `address` (a `PostalAddress` with `streetAddress`, `addressLocality`, `addressRegion`, `postalCode`, `addressCountry`). **Recommended:** `telephone`, `url`, `openingHoursSpecification`, `geo` (≥5 decimal places), `priceRange`, `aggregateRating`, `review`. `[CITED: developers.google.com/search/docs/appearance/structured-data/local-business]`

**Values recoverable from the existing site — all verified, none invented:**

| Field | Value | Source |
|---|---|---|
| `name` | `ТОРИН КОМПЮТЪРС` | Decoded from the Maps embed URL base64 segment `0KLQntCg0JjQnSDQmtCe0JzQn9Cu0KLQqtCg0KE` at `site-current/index.html:940` |
| `streetAddress` | `ул. Свети Иван Рилски 46` | `site-current/index.html` hero block |
| `postalCode` | `1606` | same |
| `addressLocality` | `София` | same |
| `latitude` | `42.68856` | Maps embed `!3d42.688559979166236` |
| `longitude` | `23.30806` | Maps embed `!2d23.308061315466727` |
| `telephone` | `02 9549710, 088 9458404, 087 9128244` | `src/includes/site-config.php:7` |
| `email` | `office@torin.bg` | `src/includes/site-config.php:8` |
| Maps place ref | `0x40aa851691c8fe29:0x61b8fb4ba7cf9fc0` | Maps embed `!1s…` — use for the `hasMap` link (D-34) |
| `openingHours` | **8:00–16:00 Mon–Fri — ⚠ see N-3** | conflicting sources |

`[VERIFIED: site-current/index.html:940 + hero block; src/includes/site-config.php:6-9]`

**PHP 5.2 rendering — two facts that make this easy and one that would trip you up:**

- `json_encode()` **is available in PHP 5.2.0+** (bundled ext/json). `[ASSUMED — well-established, not probed on this host this session; the plan should include a one-line live probe]`
- **`JSON_UNESCAPED_UNICODE` does NOT exist until PHP 5.4.** Cyrillic will therefore be emitted as `ТОРИН…`. **This is valid JSON and Google parses it correctly** — do not "fix" it by hand-writing the JSON.
- **`JSON_UNESCAPED_SLASHES` also does not exist until 5.4, which is a *safety benefit* here:** on 5.2, `json_encode()` always escapes `/` as `\/`, so a literal `</script>` inside a string can never terminate the `<script type="application/ld+json">` block early. On PHP 5.4+ with unescaped slashes that becomes a real XSS vector. Record this so a future PHP upgrade doesn't silently introduce it.

`src/includes/jsonld.php` (PHP 5.2-safe — `array()` only, no `[]`, no closures, no `<?=`):

```php
<?php
// includes/jsonld.php — LocalBusiness structured data (D-34). PHP 5.2-safe.
require_once(dirname(__FILE__) . '/site-config.php');

$torin_ld = array(
  '@context' => 'https://schema.org',
  '@type'    => array('LocalBusiness', 'ComputerStore'),   // N-2
  'name'     => 'ТОРИН КОМПЮТЪРС',
  'url'      => 'https://torin.bg/',
  'telephone' => '+35929549710',
  'email'    => $site['email'],
  'address'  => array(
    '@type'           => 'PostalAddress',
    'streetAddress'   => 'ул. Свети Иван Рилски 46',
    'addressLocality' => 'София',
    'addressRegion'   => 'София-град',
    'postalCode'      => '1606',
    'addressCountry'  => 'BG'
  ),
  'geo' => array(
    '@type'     => 'GeoCoordinates',
    'latitude'  => 42.68856,
    'longitude' => 23.30806
  ),
  'openingHoursSpecification' => array(
    array(
      '@type'     => 'OpeningHoursSpecification',
      'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday'),
      'opens'     => '08:00',
      'closes'    => '16:00'
    )
  )
);
?>
<script type="application/ld+json">
<?php echo json_encode($torin_ld); ?>
</script>
```

Include it once from `footer.php` so it renders on all 16 pages. `dayOfWeek` values are **English schema.org enums even on a Bulgarian page** — do not translate them.

**The Maps link that replaces the iframe (D-34):**

```html
<a href="https://www.google.com/maps/search/?api=1&query=42.68856%2C23.30806&query_place_id="
   rel="noopener">ул. Свети Иван Рилски 46, София 1606</a>
```

D-34's rationale is sound and worth restating with numbers: a Google Maps iframe pulls several hundred KB of third-party JS per page view, on 16 pages, on a host with no compression — directly against DESIGN-02.

### 6c. How the D-23 publish gate shapes the templates *now*

D-23 says a category page does not go live until it has genuine content, and until then its card links to a homepage section instead. **That is a per-category boolean that at least four consumers must agree on.** If it is expressed as hand-typed `href`s, they will drift the first time a page publishes.

Structure it as one data file (N-4):

```php
<?php
// includes/categories.php — single source of truth for the six categories (D-40).
// Consumed by: homepage card grid, Услуги dropdown, category page templates,
// and (Phase 4) sitemap.xml. PHP 5.2-safe.
$torin_categories = array(
  array(
    'id'        => 'kat-1',
    'title'     => 'Счупвания и механични повреди',
    'symptoms'  => 'паднал лаптоп, счупен корпус, разхлабени панти',
    'page'      => 'mehanichni-problemi.html',   // existing, SEO-04-locked
    'published' => true
  ),
  array(
    'id'        => 'kat-2',
    'title'     => 'Екран, клавиатура и портове',
    'symptoms'  => 'пукнат екран, не свети, липсващи клавиши, не се зарежда',
    'page'      => 'ekran-klaviatura-portove.html',  // NEW — Claude's discretion
    'published' => false                              // D-23 gate
  )
  /* … kat-3 optimizatsiq.html (existing), kat-4 zalivane-technosti.html (existing),
       kat-5 NEW, kat-6 NEW … */
);

function torin_category_href($cat) {            // named function, not a closure (5.2)
    return $cat['published'] ? $cat['page'] : ('index.html#' . $cat['id']);
}
?>
```

Two structural consequences the templates must honour:

- **Every category needs a homepage section with a stable `id`** (`#kat-1`…`#kat-6`) whether or not its page is published — that is the fallback target.
- **The category page template (D-24) must render even when `published` is false**, because the file exists (it may already be scaffolded) and must not 404 or show a broken shell. Simplest correct behaviour: an unpublished category page is not yet created at all, and only becomes a file when it publishes. Since these three are *new* pages with no existing rankings (D-23's own argument), that is safe — and it avoids any risk of a thin page being crawled early. The planner should make this explicit either way.

D-24's "core + optional sections" means the template should render optional blocks conditionally on content presence, not emit empty headings:

```php
<?php if (isset($page['process']) && $page['process'] !== '') { ?>
  <section class="svc-process"><h2>Как работим</h2><?php echo $page['process']; ?></section>
<?php } ?>
```

An empty `<h2>Как работим</h2>` with no body is worse than an absent section — it is exactly the thin-content signal D-23 exists to avoid.

---

## Standard Stack

There are **no package-manager dependencies in this phase.** Nothing is installed; nothing has a version to pin except the font.

| Component | Version / source | Purpose | Why this one |
|---|---|---|---|
| **Sofia Sans** *(pending N-1)* | Google Fonts `v20`, upstream `lettersoup/Sofia-Sans` v4.101, **OFL** | Single family, headings + body (D-06) | Only candidate whose **default** outlines are Bulgarian — removes the entire browser-`locl` risk class. 66 KB for both subsets. |
| PHP `include()` | PHP 5.2.17 (host) | Templating | Proven live in Phase 1; zero new tooling |
| CSS custom properties | Baseline | Theming (D-04) | Makes the theme swap a token override, and Phase 4 a file deletion |
| Native `<details>`/`<summary>` | Baseline Widely available | Folded catch-all (D-11) | Content in HTML → indexes; zero JS; native keyboard/AT/find-in-page |
| Vanilla JS (`site.js`) | — | Nav disclosure + mobile toggle | ~50 lines; a library would cost ~44 KB raw on this no-gzip host |
| Inline SVG | — | Icons | Replaces Font Awesome + liquid-icon webfonts; no extra request, themeable via `currentColor` |
| Apache `mod_headers` | **confirmed active on host** | `X-Robots-Tag` on `/new/` | Already proven by Phase 1's `.htaccess` |

**Alternatives considered:**

| Instead of | Could use | Tradeoff |
|---|---|---|
| Sofia Sans | Manrope / Montserrat / Commissioner | Proper `cyrl/BGR` `locl` (22–23 subs, verified). Correct **only** when `lang="bg"` reaches the shaper and the browser honours `locl`. Fails open to Russian forms. Manrope's Cyrillic subset is the smallest at 14.5 KB. |
| Sofia Sans | Keep Inter (D-06 as written) | Zero technical risk, zero migration; Bulgarian renders in Russian-convention letterforms and the payload is 352 KB. |
| Vanilla JS | Alpine.js (per CLAUDE.md) | ~15 KB gzipped / **~44 KB raw on this host** to replace ~50 lines. Rejected — see N-7. |
| `<details>` | JS-toggled section | Needs ~20 lines of JS, breaks with JS off, and loses native find-in-page reveal. Rejected. |
| Media queries | Container queries | Baseline and would work, but one card in one container context — no benefit, extra concept. Rejected. |
| Explicit breakpoints | `repeat(auto-fit, minmax())` | With a fixed six items, auto-fit produces orphan rows (4+2, 5+1) at intermediate widths. Rejected. |
| Ordered `<link>` files | `@layer` | Baseline and build-free, but ordered links are simpler to hand-edit and grep with no linter present. |

**Installation:** none. Two `curl` commands to fetch the font subsets (Q1d); everything else is hand-authored files.

---

## Package Legitimacy Audit

**Not applicable — this phase installs zero external packages.** No npm, PyPI, or crates dependency is introduced; the constraint is explicitly "zero build tooling".

The only third-party artefact introduced is a **font binary**, audited on the same principle:

| Artefact | Source | Provenance | License | Verdict | Disposition |
|---|---|---|---|---|---|
| `sofia-sans-cyrillic.woff2`, `sofia-sans-latin.woff2` | `fonts.gstatic.com` (Google Fonts `v20`), upstream `github.com/lettersoup/Sofia-Sans` | Designers Lettersoup / Botio Nikoltchev / Ani Petrova, listed in `google/fonts` `METADATA.pb`, added 2022-11-17 | **OFL** — self-hosting permitted | **OK** | Approved. Binaries were parsed this session and match the upstream description. |

The tools used *for research* (`fonttools`, `brotli`, `uharfbuzz` in a throwaway venv) are **dev-machine verification tools, not project dependencies** and must not appear in any deliverable.

---

## Don't Hand-Roll

| Problem | Don't build | Use instead | Why |
|---|---|---|---|
| Bulgarian letterform localization | A CSS class that swaps in styled spans, or per-character overrides | A font whose defaults are Bulgarian (Sofia Sans) | The substitution is 30 glyphs deep in the font's GSUB table; there is no CSS-level equivalent |
| Font subsetting | A Python/pyftsubset step (would introduce build tooling) | Google Fonts' pre-subset `css2` woff2 files | Verified to retain the `locl` tables; two `curl` commands, zero tooling |
| Collapsible sections | A JS show/hide with hand-built ARIA | `<details>`/`<summary>` | Native keyboard, AT semantics, **find-in-page auto-expand**, fragment-link auto-open, and works with JS off — all free |
| Nav dropdown roles | `role="menu"` / `role="menuitem"` | W3C APG **Disclosure Navigation** pattern | `role="menu"` makes screen readers announce a desktop app menu and hands navigation to arrow keys — wrong for six links |
| Responsive headline sizing | `data-fittext`-style JS measurement (36 uses on the current site) | `clamp()` | No layout thrash, no JS, no FOUC |
| Icon delivery | Font Awesome / liquid-icon webfonts | Inline SVG | ~75 KB+ uncompressed on a no-gzip host to draw a handful of glyphs; SVG inherits `currentColor` and themes for free |
| "Is this dev or prod?" | A runtime path/host check | `file_exists()` sentinel on `dev-switcher.php` | The guard becomes verifiable by listing the deployed files, not by reading code |
| Map display | Google Maps `<iframe>` | Maps deep link + JSON-LD `geo`/`address` | Several hundred KB of third-party JS × 16 pages, uncompressed (D-34) |

**Key insight:** every hand-rolled option in this table costs bytes on a host that serves **no compression**, and every one of them has a native platform equivalent that shipped years ago. The Liquid template exists because those platform features didn't exist in 2019. They do now.

---

## Common Pitfalls

### Pitfall 1 — Verifying "no jQuery" by grepping for filenames

**What goes wrong:** the verification greps for `scrollmagic.js` / `pagepiling.js` / `jquery-ui.js` in the page source, finds none, and passes — while ScrollMagic, Isotope, imagesLoaded, Velocity, and jQuery UI are all still shipping inside the single bundled `theme-vendors.js`.
**Why it happens:** the vendor directories under `assets1/vendors/` are decoys; nothing loads them directly.
**How to avoid:** verify by asserting that **no `<script>` tag references `assets1/` at all**, and that `window.jQuery`/`window.$` are undefined at runtime.
**Warning signs:** a "removed" check that passes on a page still loading 530 KB of vendor JS.

### Pitfall 2 — Assuming `lang="bg"` produces Bulgarian letterforms

**What goes wrong:** `lang="bg"` is set (already is), typography looks fine, everyone assumes D-07 is satisfied. It is not — Inter has no Bulgarian localization at all.
**Why it happens:** the difference between Russian-convention and Bulgarian д ж з и й л п т ц ш щ is invisible to a non-Bulgarian reader and easy to miss even for a Bulgarian one at small sizes.
**How to avoid:** run the HarfBuzz shaping check (Validation V1) as a **font-selection gate**, before any content is written. It is deterministic and takes seconds.
**Warning signs:** "the Cyrillic looks fine" as the entire evidence for success criterion 4.

### Pitfall 3 — Theme A becomes the better-tested theme

**What goes wrong:** the dev switcher makes Theme A the interesting one; it gets clicked through constantly, and Theme B — the one that ships (D-02a) — accumulates unreviewed contrast and spacing bugs.
**Why it happens:** the novel option attracts attention; the default is assumed fine.
**How to avoid:** the architecture in Q2a makes Theme B the unconditional `:root` and Theme A a six-line override, so Theme B is what everything is authored against by construction. Additionally: every visual review checkpoint must review Theme B **first**.
**Warning signs:** screenshots in review artefacts showing `data-theme="a"`.

### Pitfall 4 — Amber used as a text colour

**What goes wrong:** `#ffc70a` on white is roughly 1.6:1 — well below WCAG AA's 4.5:1. It reads as "brand colour" and gets applied to links, headings, and small text.
**How to avoid:** encode it in the tokens: `--c-brand` is a **surface**; text on it is `--c-on-brand` (dark). Provide a separate `--c-link` that is *not* the brand amber.
**Warning signs:** any rule matching `color: var(--c-brand)` on body-sized text.

### Pitfall 5 — A BOM sneaks into a PHP include

**What goes wrong:** an editor saves `header.php` or `dev-switcher.php` as UTF-8-with-BOM. Three stray bytes are emitted before `<!DOCTYPE>`, and `setcookie()` in the theme switcher fails with "headers already sent".
**Why it happens:** invisible; only shows up as a subtle rendering glitch plus a broken cookie.
**How to avoid:** the one-line check in Validation V4, run after every new file. Currently all four Phase 1 files are clean.
**Warning signs:** the theme switcher "works" on the first click but does not persist across pages.

### Pitfall 6 — PHP 5.4+ syntax written from muscle memory

**What goes wrong:** `[]` short arrays, `??`, `::class`, closures, or `<?=`. On PHP 5.2 these are **parse errors**, which means a **blank white page or HTTP 500** — and since deploy is FTP-to-live-preview with no local PHP, the failure appears only after upload.
**How to avoid:** the grep-based 5.2 lint in Validation V3, run before every FTP push.
**Warning signs:** a page that returns 200 with zero bytes, or 500, immediately after a deploy.

### Pitfall 7 — Believing a local preview proves anything

**What goes wrong:** the design looks right in a local browser (opening the `.html` files directly or via a static server), so it is assumed to work. But **`php` is not installed on the dev machine** — the `include()`s never execute locally, so header/footer/nav/JSON-LD are simply absent from what was reviewed.
**How to avoid:** all verification goes through FTP to `torin.bg/new/` + `curl`, exactly as Phase 1 established. Installing PHP 8 locally would be *worse than nothing* — it would run the includes but silently accept 5.2-invalid syntax.
**Warning signs:** a review that says "looks good locally" with no `/new/` URL.

### Pitfall 8 — Folded content assumed to rank as well as visible content

**What goes wrong:** D-13 places DIFF-02 (battery regeneration) in the folded section on the basis that it will still be indexed. It will be — but practitioner evidence suggests collapsed content is weighted lower for ranking.
**How to avoid:** do not silently treat this as solved. Carry it to OWNER-QUESTIONS #9 with this extra cost attached (see §3c).

### Pitfall 9 — Working hours shipped site-wide from the wrong source

**What goes wrong:** the footer (D-33) and JSON-LD (D-34) render hours on all 16 pages from `site-config.php`. The live site currently disagrees with itself (N-3). Picking the wrong one publishes an error 16 times and feeds it to Google's structured data.
**How to avoid:** resolve N-3 with the owner before Phase 4 cutover; until then mark the value `[ASSUMED]` in `site-config.php` with an inline comment.

---

## Validation Architecture

`workflow.nyquist_validation` is `false` in `.planning/config.json`, and this project has **no test runner, no build tooling, and no local PHP**. The checks below are therefore all either shell one-liners or manual browser steps with exact assertions — nothing here requires installing a dependency into the project.

### V1 — Bulgarian letterform rendering is objectively correct *(success criterion 4)*

**This is a font-selection gate: run it before content is written, not after.** It uses HarfBuzz — the shaper Chrome, Firefox, and Android actually use — so it answers the question directly rather than by eyeballing.

```bash
# One-off, in a throwaway venv on the dev machine (NOT a project dependency)
python3 -m venv /tmp/fontcheck && /tmp/fontcheck/bin/pip -q install fonttools brotli uharfbuzz
/tmp/fontcheck/bin/python - <<'PY'
import uharfbuzz as hb
from fontTools.ttLib import TTFont
SRC = 'src/fonts/sofia-sans-cyrillic.woff2'
TTF = '/tmp/fontcheck/font.ttf'
f = TTFont(SRC); f.flavor = None; f.save(TTF)      # hb can't read woff2 directly

def shape(lang):
    face = hb.Face(hb.Blob.from_file_path(TTF)); font = hb.Font(face)
    buf = hb.Buffer(); buf.add_str('вгджзийклптцшщю'); buf.guess_segment_properties()
    buf.language = lang; buf.script = 'Cyrl'
    hb.shape(font, buf)
    return [font.glyph_to_string(i.codepoint) for i in buf.glyph_infos]

bg, ru = shape('bg'), shape('ru')
diff = sum(1 for a, b in zip(bg, ru) if a != b)
print('glyphs differing bg vs ru: %d/%d' % (diff, len(bg)))
assert diff == len(bg), 'FAIL: font does not distinguish Bulgarian from Russian forms'
print('PASS')
PY
```

**Expected:** `glyphs differing bg vs ru: 15/15` → `PASS`.
**Reference results from this research** (so a regression is recognisable): Inter 4.1 → `0/15` **FAIL**; Sofia Sans → `15/15` PASS; Manrope/Montserrat/Commissioner → `15/15` PASS.

### V2 — No jQuery / ScrollMagic / pagePiling / Modernizr anywhere, all 16 pages

**Static check** (source of truth):

```bash
# Must return NOTHING.
grep -rniE 'jquery|modernizr|scrollmagic|pagepiling|theme-vendors|theme\.min|assets1/|zdassets|ajax\.googleapis|cdnjs\.cloudflare' \
  src/*.html src/includes/*.php src/js/*.js
```

**Live check across all 16 locked URLs** (the real assertion — proves what is deployed, not what is committed):

```bash
for p in index about laptopi profilaktika-laptop optimizatsiq mehanichni-problemi \
         za-bateriite tokov-udar zalivane-technosti rezervni-chasti warrently \
         uslovia covid test-laptop problem-stari msg; do
  n=$(curl -s "https://torin.bg/new/$p.html" \
      | grep -ciE 'jquery|modernizr|scrollmagic|pagepiling|theme-vendors|theme\.min|assets1/|zdassets')
  printf '%-24s %s\n' "$p.html" "$([ "$n" -eq 0 ] && echo PASS || echo "FAIL ($n hits)")"
done
```

**Runtime check** (catches anything bundled): in DevTools console on `torin.bg/new/`, `typeof jQuery` and `typeof $` must both be `"undefined"`, `typeof ScrollMagic` must be `"undefined"`, and the Network tab must show **zero** requests to `assets1/`.

**Also assert the include is actually used** (this is what proves SEO-02, not the presence of the attribute in `header.php`):

```bash
for p in index about laptopi profilaktika-laptop optimizatsiq mehanichni-problemi \
         za-bateriite tokov-udar zalivane-technosti rezervni-chasti warrently \
         uslovia covid test-laptop problem-stari msg; do
  curl -s "https://torin.bg/new/$p.html" | grep -q '<html lang="bg"' \
    && echo "PASS $p" || echo "FAIL $p"
done
```

### V3 — PHP 5.2 syntax safety (run before every FTP push)

```bash
# Each must return NOTHING.
grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)' src/includes/*.php   # short array syntax
grep -rn  'function *(' src/includes/*.php                 # closures
grep -rn  '<?='          src/includes/*.php src/*.html     # short echo tags
grep -rnE 'namespace |::class|\?\?|\.\.\.|yield ' src/includes/*.php
grep -rn  'JSON_UNESCAPED\|JSON_PRETTY' src/includes/*.php # 5.4+ constants
```

### V4 — Encoding hygiene (UTF-8, no BOM) — run after adding any file

```bash
for f in $(find src -name '*.php' -o -name '*.html' -o -name '*.css' -o -name '*.js'); do
  enc=$(file -b --mime-encoding "$f")
  bom=$(head -c 3 "$f" | xxd -p)
  [ "$bom" = "efbbbf" ] && echo "FAIL BOM  $f"
  case "$enc" in utf-8|us-ascii) ;; *) echo "FAIL ENC $enc $f" ;; esac
done
echo "encoding check done"
```

Plus a live mojibake check — fetch a page and confirm a known Cyrillic string round-trips:

```bash
curl -s https://torin.bg/new/index.html | grep -c 'ТОРИН КОМПЮТЪРС'   # expect ≥ 1
```

### V5 — Dev-only switcher is provably absent from production *(D-03, Phase 4 gate)*

```bash
# On the /new/ preview these SHOULD exist (200); after Phase 4 cutover at the
# root they MUST 404 and the attribute must be gone.
curl -s -o /dev/null -w "dev-switcher.php: %{http_code}\n" https://torin.bg/includes/dev-switcher.php
curl -s -o /dev/null -w "theme-a.css:      %{http_code}\n" https://torin.bg/css/theme-a.css
curl -s https://torin.bg/ | grep -c 'data-theme'          # must be 0
curl -s https://torin.bg/ | grep -c 'dev-switcher'        # must be 0
```

### V6 — Responsive behaviour (documented manual check — no harness available)

**Automatable precheck** — catch the most common overflow causes:

```bash
grep -rnE 'width: *[0-9]{3,}px|min-width: *[0-9]{3,}px' src/css/*.css   # review each hit
grep -rn 'overflow-x' src/css/*.css                                     # no horizontal hacks
```

**Manual, in Chrome DevTools device toolbar, against `https://torin.bg/new/`.** Exact viewports and exact assertions:

| Viewport | Assertions |
|---|---|
| **360 × 640** (mobile floor) | ① No horizontal scrollbar — `document.documentElement.scrollWidth <= window.innerWidth`. ② Hamburger visible, nav list hidden. ③ Hero occupies ≤ 384px. ④ **At least the first category card's media box and title are visible without scrolling** (D-30's actual purpose). ⑤ Category grid is 1 column. |
| **560 × 800** | Category grid is 2 columns; no orphan single card in a row of its own except the trailing pair. |
| **900 × 800** (desktop floor) | ① Hamburger hidden, five nav items inline. ② Category grid is 3 columns → 3+3. ③ Услуги dropdown opens on click, shows all six names **at full length on one line each** (D-19). |
| **1440 × 900** | ① Content container capped, not full-bleed. ② Body measure ≤ ~66 characters. |

**Keyboard-only pass at 900×800** (this is the ARIA contract, and it is not optional):
`Tab` to Услуги → `Enter` opens (`aria-expanded="true"`) → `Tab` walks the six links → `Esc` closes **and focus returns to the Услуги button** → `Tab` away from the nav closes any open dropdown.

**JS-disabled pass:** all six categories are still reachable from the homepage card grid; every `<details>` in the catch-all still opens.

### V7 — Structured data

Paste the rendered page source into Google's Rich Results Test and Schema.org validator. Expect zero errors on the LocalBusiness block. Confirm the `\uXXXX`-escaped Cyrillic decodes to the correct strings in the tool's parsed view — this is the check that catches "someone hand-wrote the JSON and broke it".

### V8 — Compression / caching spike *(N-6)*

```bash
curl -sI -H 'Accept-Encoding: gzip' https://torin.bg/new/css/base.css | grep -i content-encoding
```
**Current baseline (verified 2026-08-05): returns nothing — no compression on this host.** After adding a `mod_deflate` block to `.htaccess`, this must return `content-encoding: gzip`. If it does not, `mod_deflate` is unavailable and the CSS byte budget in Q2c becomes a hard limit rather than a target.

---

## Environment Availability

| Dependency | Required by | Available | Version | Fallback |
|---|---|---|---|---|
| PHP on host | All templating | ✓ | **5.2.17** (`x-powered-by: PHP/5.2.17`, live 2026-08-05) | — |
| `.html`-as-PHP handler | 16 locked `.html` URLs | ✓ | `AddHandler application/x-httpd-php52` | — |
| HTTP/2 on host | CSS file-count strategy | ✓ | `http_version: 2` | — |
| Apache `mod_headers` | `X-Robots-Tag` on `/new/` | ✓ | proven by live response | — |
| Apache `mod_deflate` | Compression (N-6) | **? unverified** | — | Hard CSS byte budget; spike task |
| Apache `mod_expires` | Cache headers | **? unverified** | — | `Header set Cache-Control` via `mod_headers` (confirmed available) |
| `curl` (dev machine) | All live verification | ✓ | system | — |
| `python3` (dev machine) | V1 font check only | ✓ | 3.13 (Homebrew) | — |
| **`php` (dev machine)** | Local preview of includes | **✗** | — | **None. Deploy to `/new/` + `curl`, as Phase 1 established.** Installing PHP 8 locally would mask 5.2 syntax errors — actively harmful. |
| `node` / `npm` (dev machine) | *not used* | ✓ (v20.18.0) | — | Irrelevant — zero build tooling by constraint |
| `lftp` | FTP deploy | ✗ | — | `curl --ftp-ssl -k` per Phase 1's established pattern |

**Missing with no fallback:** none blocking.
**Missing with fallback:** local PHP (fallback: FTP preview — already the project's proven workflow); `mod_deflate`/`mod_expires` (fallback: `mod_headers`, plus a strict byte budget).

---

## Security Domain

`security_enforcement: true`, `security_asvs_level: 1`.

### Applicable ASVS categories

| ASVS category | Applies | Control in this phase |
|---|---|---|
| V2 Authentication | No | No auth surface introduced |
| V3 Session Management | Marginal | The dev switcher sets a non-sensitive `torin_theme` cookie; no session data. Path-scoped `/`, no `HttpOnly` needed (no secret), removed at cutover. |
| V4 Access Control | **Yes** | The dev switcher must be unreachable in production. Control: `file_exists()` sentinel + V5 verification. Do **not** gate on a request-derived value. |
| V5 Input Validation / Output Encoding | **Yes** | `$_GET['theme']` validated against a hardcoded whitelist **before** use; the value written to HTML is a code-chosen literal, never the request value. |
| V6 Cryptography | No | None used |
| V12 Files | No | No upload/download surface |
| V14 Configuration | **Yes** | `.htaccess` correctness; the `noindex` header on `/new/` must stay until cutover |

### Known threat patterns for this stack

| Pattern | STRIDE | Mitigation |
|---|---|---|
| Reflected XSS via `?theme=` echoed into the `<html>` attribute | Tampering | Whitelist-then-literal (Q2b). Flag in the plan so a future "simplification" doesn't reintroduce it. |
| `</script>` breakout from JSON-LD | Tampering | `json_encode()` on PHP 5.2 always escapes `/` (no `JSON_UNESCAPED_SLASHES` before 5.4). **Record that a PHP upgrade would remove this protection.** |
| **Mixed active content** — `http://cdnjs.cloudflare.com/…/modernizr.js` on an HTTPS page | Tampering / MITM | Currently live on `index.html`. Removed by DESIGN-01. **This phase closes an existing script-injection surface.** |
| Third-party script on all 16 pages (Zendesk `zdassets.com`) | Info disclosure | Formally CONTACT-04 (Phase 4), but removing it here is free and reduces the third-party surface immediately. |
| Staging subtree indexed by Google | — | `X-Robots-Tag: noindex, nofollow` on `/new/` — confirmed working live. Must survive any `.htaccess` edit this phase makes. |
| Host-header injection in redirects | Spoofing | Already mitigated in Phase 1 — `.htaccess` uses a hardcoded literal target, never `%{HTTP_HOST}`. **Do not "improve" this.** |

---

## Assumptions Log

| # | Claim | Section | Risk if wrong |
|---|---|---|---|
| A1 | `font-feature-settings: "locl" 1` cannot select *which* language's `locl`, and `font-variant-alternates` doesn't expose `locl` at all | Q1c | Low — if wrong, a CSS-only fallback exists that this research says doesn't. Moot under Option A. |
| A2 | Safari's current `locl` behaviour — sources conflict (Typotheque says unsupported; mevbg implies supported). Not verified in a live browser. | Q1c | Medium under Option B (≈11.6% of BG traffic). **Zero under Option A.** |
| A3 | `crossorigin` is required on same-origin font preloads | Q1d | Low — a double download, not a failure |
| A4 | `json_encode()` is available on this host's PHP 5.2.17 build | Q6b | **Medium — would break the JSON-LD entirely.** Add a one-line live probe before relying on it. |
| A5 | Working hours are 8:00–16:00 Mon–Fri | N-3, Q6b | **Medium — wrong hours on 16 pages + in structured data.** Owner confirmation needed. |
| A6 | Contrast ratios (amber ≈1.6:1 on white; `#16223a` on `#ffc70a` ≈11:1) estimated, not computed with a checker | Q5c | Medium — a WCAG AA failure in the shipped theme. Add an explicit contrast-check step. |
| A7 | Collapsed content is *weighted lower* for ranking though indexed — practitioner consensus, not a Google statement | Q3c | Medium — affects the D-13 / DIFF-02 trade-off assessment |
| A8 | `mod_deflate` / `mod_expires` availability on `bell.host.bg` | N-6, V8 | Low — spike resolves it; fallback via `mod_headers` (confirmed available) |
| A9 | `addressRegion: "София-град"` is the right value for a Sofia postal address | Q6b | Low — Google lists `addressRegion` as required but tolerates approximations |
| A10 | The 3 new category pages should not exist as files until published (D-23) | Q6c | Low — the alternative (empty shells) is worse; planner should make it explicit either way |

---

## Open Questions

1. **Which font ships? (N-1)** — the single highest-impact unresolved item.
   - *Known:* Inter has zero Bulgarian localization (definitively verified). Sofia Sans's defaults are Bulgarian. Manrope/Montserrat/Commissioner have proper `BGR locl`.
   - *Unclear:* whether the user/owner values authentic Bulgarian letterforms enough to override D-06.
   - *Recommendation:* surface all three options with the payload and risk table; recommend Sofia Sans. **Must be decided before Phase 3 writes content** (D-06 is flagged "costly to reverse", and the x-height difference re-tunes the whole type scale).

2. **Working hours — 8:00–16:00 or 9:00–17:00? (N-3)**
   - *Known:* two of three sources plus the «НОВО» banner say 8:00–16:00.
   - *Unclear:* whether `profilaktika-laptop.html` is stale or the others are.
   - *Recommendation:* add to OWNER-QUESTIONS.md; ship 8:00–16:00 marked `[ASSUMED]` in `site-config.php`.

3. **Is `otpuska.js`'s banner kept?** (OWNER-QUESTIONS #8) — it needs no library, so preserving an equivalent is nearly free. Recommendation: rebuild as a PHP-rendered notice band driven from `site-config.php`, so it is content, not a script.

4. **Do the three new category pages exist as files before they publish?** (A10 / D-23) — recommendation: no; create the file at publish time. Needs an explicit planner call.

5. **`mod_deflate` availability** (N-6) — resolve with a Wave-0 spike; changes the CSS budget from a target to a hard limit.

---

## Sources

### Primary — HIGH confidence (direct verification this session)

- **Font binaries parsed with fontTools** — `Inter-4.1/InterVariable.ttf`; `google/fonts` `ofl/{sofiasans,manrope,montserrat,commissioner,onest,firasans}`; Google Fonts `v20` woff2 subsets for Sofia Sans and Manrope. GSUB ScriptList/LangSys/FeatureList and `locl` substitution mappings; `cmap` Bulgarian-alphabet coverage; OS/2 x-height & cap-height; `hmtx` advance widths.
- **HarfBuzz shaping via `uharfbuzz`** — `вгджзийклптцшщю` under `script=Cyrl`, `language ∈ {bg, ru, en, unset}`, across all candidate fonts.
- **GitHub API** — `repos/rsms/inter/releases/latest` (tag `v4.1`, published 2024-11-16); `repos/rsms/inter/issues/562` (state `open`, created 2023-04-22, 11 comments, last 2025-03-20) and its comment thread.
- **Live HTTP probes of `bell.host.bg` / `torin.bg`** — `x-powered-by: PHP/5.2.17`; `http_version: 2`; **no `content-encoding`** on CSS/HTML with `Accept-Encoding: gzip`; **no `Cache-Control`/`Expires`**; `X-Robots-Tag: noindex, nofollow` active on `/new/` (proves `mod_headers`).
- **Direct inspection of `site-current/` and `src/`** — per-page script/link inventory across all 16 HTML files; `data-*` attribute counts; library string-scan of `theme-vendors.js`; CSS/JS file sizes; `business.css` `:root`; the 15 `#our-services` titles; Maps-embed coordinates and base64 place name; working-hours occurrences; `otpuska.js` and `header.js` contents; BOM/encoding check on `src/includes/*.php`.
- `https://fonts.googleapis.com/css2?family=Sofia+Sans:wght@400..800` — verbatim `@font-face` blocks and `unicode-range` values.
- `google/fonts` `ofl/sofiasans/METADATA.pb` — designers, license, subsets, date added.
- `https://schema.org/{ComputerStore,ElectronicsStore,ProfessionalService}` — all HTTP 200; `ComputerStore` hierarchy Thing > Organization > LocalBusiness > Store > ComputerStore.
- `https://gs.statcounter.com/browser-market-share/all/bulgaria` — Bulgaria, July 2026 shares.

### Secondary — MEDIUM confidence (official docs / cross-corroborated)

- [W3C ARIA APG — Disclosure Navigation Menu](https://www.w3.org/WAI/ARIA/apg/patterns/disclosure/examples/disclosure-navigation/) — pattern, ARIA attributes, keyboard contract, WCAG 1.4.13 rationale
- [Google Search Central — Local business structured data](https://developers.google.com/search/docs/appearance/structured-data/local-business) — required vs recommended properties, `openingHoursSpecification` syntax
- [MDN — HTML details exclusive accordions](https://developer.mozilla.org/en-US/docs/Web/API/Element/beforematch_event) and [Chrome for Developers — Exclusive Accordion](https://developer.chrome.com/docs/css-ui/exclusive-accordion) — `<details name>` and `::details-content` support
- [Chrome for Developers — hidden=until-found](https://developer.chrome.com/docs/css-ui/hidden-until-found) — Baseline Dec 2025; Firefox 139 (May 2025), WebKit ~Sept 2025
- [web.dev — Baseline](https://web.dev/baseline) and [LogRocket — Container queries in 2026](https://blog.logrocket.com/container-queries-2026/) — 2026 CSS baseline status
- [Bulgarian Cyrillic on the Web — Martin Metodiev (mevbg)](https://medium.com/@mevbg/bulgarian-cyrillic-on-the-web-techniques-for-authentic-font-rendering-bec82c24e39f) — the three delivery mechanisms; `lang="bg"` as the trigger; `-webkit-locale`
- [Lettersoup — What shall be done for Bulgarian Cyrillic .loclBGR](https://www.lettersoup.de/what-shall-be-done-for-bulgarian-cyrillic-loclbgr/) — the reference article on `.loclBGR`, by Sofia Sans's designer
- [Search Engine Journal — Tabbed content ranking factor](https://www.searchenginejournal.com/ranking-factors/tabbed-content/), [Lumar — hidden content](https://www.lumar.io/office-hours/hidden-content/), [Varn](https://varn.co.uk/insights/can-google-crawl-content-accordions-seo/) — Google indexes in-DOM collapsed content
- [OuterBox — tabbed & accordion content for SEO](https://www.outerboxdesign.com/articles/seo/should-i-use-tabbed-and-accordion-content-for-seo/) — practitioner evidence that visible content outperforms collapsed

### Tertiary — LOW confidence (single source, flagged)

- [Typotheque — OpenType features in CSS](https://www.typotheque.com/articles/opentype-features-in-css) — claims `locl` is unsupported in Safari and on Android. **Conflicts with more recent sources; likely stale.** Recorded as A2.

---

## Metadata

**Confidence breakdown:**

| Area | Level | Reason |
|---|---|---|
| Inter has no Bulgarian `locl` | **HIGH** | Three independent verifications: GSUB parse, HarfBuzz shaping, upstream issue still open |
| Alternative font capabilities | **HIGH** | Every candidate parsed and shaped from the exact binary that would ship |
| Google Fonts subsets retain `locl` | **HIGH** | Parsed the downloaded woff2 files directly |
| Vendor-stack inventory & disposition | **HIGH** | Direct grep/inspection of all 16 pages plus the bundle contents |
| Host capabilities (PHP 5.2.17, HTTP/2, no gzip, no cache headers, `mod_headers`) | **HIGH** | Live HTTP probes, 2026-08-05 |
| Browser baseline for Bulgaria | **HIGH** | StatCounter July 2026 + Baseline status |
| Nav/disclosure ARIA pattern | **HIGH** | W3C APG, normative |
| JSON-LD shape | **MEDIUM** | Google docs are authoritative; the Bulgarian `addressRegion` convention is not |
| `locl` browser trigger mechanics | **MEDIUM** | Sources conflict on Safari; not verified live. **Moot under the recommended option.** |
| Collapsed-content ranking weight | **MEDIUM** | Practitioner consensus, not a Google statement |
| Contrast ratios | **LOW** | Estimated, not computed with a checker |

**Research date:** 2026-08-05
**Valid until:** ~2026-11-05 (90 days). The font findings are stable — Inter issue #562 has been open for 3+ years and Sofia Sans's design is fixed. The Baseline-status items move fastest; re-check if planning slips past ~30 days.
