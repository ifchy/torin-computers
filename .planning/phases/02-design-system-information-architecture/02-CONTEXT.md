# Phase 2: Design System & Information Architecture - Context

**Gathered:** 2026-08-05
**Status:** Ready for planning

<domain>
## Phase Boundary

Replace the purchased jQuery/parallax "Liquid" theme with a modern, mobile-responsive design system, and restructure the site's information architecture around the six owner-priority service categories. This phase establishes the reusable visual language (tokens, typography, components, page templates) and the navigation/content structure that Phase 3 fills with content and Phase 4 hardens and cuts over.

Delivers structure and system, not finished copy. Where a decision would require content that does not yet exist, this phase defines the slot and records the dependency rather than inventing the content.

</domain>

<decisions>
## Implementation Decisions

### Brand Colors & Theming

- **D-01:** The site's real primary color is `#ffc70a`, defined in `site-current/assets1/css/themes/business.css` `:root` (`--color-primary: #ffc70a`, `--color-secondary: #0e305d`, gradient `#ffc70a` → `#ffcd2b`). The value `#3ed2a7` appears ONLY in the `<meta name="theme-color">` tag and is an unchanged leftover from the purchased "Liquid" template — it is never used in actual styling. It was carried verbatim into Phase 1's `src/includes/header.php` line 16 and **must be corrected in this phase**.
- **D-02:** Build **two** themes, switchable during development:
  - **Theme A (logo-derived):** amber `#fbad03` + electric blue `#0547dc` — extracted directly from `site-current/assets1/img/torin-logo.png`
  - **Theme B (business.css-derived):** amber `#ffc70a` + navy `#0e305d` — the current applied theme
  Remaining palette details (neutrals, surfaces, semantic/state colors, contrast ramps) are Claude's discretion.
- **D-02a:** **Theme B (`#ffc70a` + `#0e305d`) is the default and the theme that ships live** at the Phase 4 cutover. Theme A remains available in the dev switcher as the alternative to compare against. Decided by the user 2026-08-05; resolves OWNER-QUESTIONS #10. Build Theme B first and treat it as the reference implementation — Theme A must not become the one that's better tested.
- **D-03:** The theme switcher is **dev-only** — scaffolding so the owner/user can compare themes live at `torin.bg/new`. It is removed before the Phase 4 cutover, with the chosen theme hard-baked. It must NOT ship to visitors. — **Reversibility:** reversible — removing a dev-only control is a deletion, no migration.
- **D-04:** Implement theming via CSS custom properties (design tokens) so the switch is a token swap, not duplicated stylesheets.
- **D-05:** The current CSS theme is a near-miss on the company's own logo — `business.css` secondary `#0e305d` (dark navy) is NOT the logo's blue (`#0547dc`, electric blue). Recorded so the discrepancy is a deliberate choice, not an accident.

### Typography

- **D-06:** ~~Use **Inter**, self-hosted~~ — **SUPERSEDED by D-06a (2026-08-05).** Original rationale ("complete Cyrillic coverage") proved half-right: Inter has full Bulgarian *glyph* coverage but zero Bulgarian *localization*. See D-06a.
- **D-06a:** Use **Sofia Sans**, self-hosted (OFL, Lettersoup / Botio Nikoltchev + Ani Petrova), as the single family for both headings and body. **Bulgarian letterforms are its default outlines** — they render correctly under `lang="bg"`, `lang="en"`, or no `lang` at all, so correctness does not depend on browser `locl` support. Payload is 66 KB (cyr+lat woff2) against Inter's 352 KB, which matters because this host serves **no compression at all** (verified live in Phase 2 research). Decided by the user 2026-08-05, resolving RESEARCH N-1. — **Reversibility:** costly — a font swap after content is written re-flows every page and re-tunes the whole type scale. This decision must hold through Phase 3.
- **D-07:** ~~Verify at implementation time that Bulgarian localized letterforms resolve via `locl` under `lang="bg"`~~ — **SETTLED NEGATIVE for Inter; MOOT under D-06a.** Verified three independent ways (fontTools GSUB parse, HarfBuzz shaping showing 0/15 letters differing between `bg` and `ru`, and open upstream issue `rsms/inter#562`): **Inter v4.1 contains no Cyrillic script record in its GSUB table whatsoever** — its `locl` feature serves only Romanian/Moldavian and Catalan Latin. `lang="bg"` is a no-op for Inter's typography. Under D-06a this is moot: Sofia Sans needs no `locl` at all. `lang="bg"` is still required for SEO-02 and remains a Phase 2 deliverable — it just is not the mechanism that produces the letterforms.
- **D-07a:** Because correctness no longer depends on `locl`, verification changes shape: the check is that **rendered Bulgarian glyph outlines are the Bulgarian forms**, not that a feature flag applied. Use the objective check in RESEARCH §V1 rather than visual inspection.
- **D-08:** Drop the current fonts entirely — Basier Square, Glacial Indifference, Cerebri Sans, and Google-hosted Barlow. None of them can render Bulgarian (see verified findings in `<specifics>`).

### Homepage Structure

- **D-09:** The homepage presents the **six owner-priority categories** as the prominent primary grid. The owner explicitly named these six as the work they do — the grid must not be diluted with a seventh peer item.
- **D-10:** Each of the six category cards carries a short **symptom line** in plain customer language beneath the title (e.g. «пукнат екран, не свети, петна по дисплея»). Rationale: the six categories are named by *cause/solution*, but customers arrive knowing only *symptoms*; for roughly half the categories a customer cannot correctly self-select. The symptom line is the translation layer, placed at the moment of choosing.
- **D-11:** Everything outside the six goes into a **folded/collapsed catch-all section**, organized **by symptom** and framed as «Не откривате проблема си?». It handles ambiguous symptoms (e.g. «не се включва») that legitimately map to several categories and cannot be filed under one card. Folded content must be **real HTML in the page**, not JS-loaded on click, so it indexes normally.
- **D-12:** The self-diagnostic tool («Тествай сам своя лаптоп») gets **its own homepage feature block** — satisfies DIFF-01.
- **D-13:** **Регенерация на батерии goes into the folded section — DIFF-02 is DELIBERATELY DOWNGRADED, not dropped.** DIFF-02 as written requires it be "surfaced as a distinct differentiator"; a folded accordion does not satisfy that. Recorded as a conscious trade-off requiring **owner sign-off** (OWNER-QUESTIONS #9). Phase 3 verification must treat DIFF-02 as knowingly unmet rather than silently failing.
- **D-14:** DIFF-03 (BGA/chip-level repair expertise) needs no special homepage handling — it lives naturally inside category 4 (заляти и повредени дънни платки), which is where those services map.
- **D-15:** **Безплатна диагностика** is treated as a trust/conversion element (hero + repeated near CTAs), not as a line item in any service list.

### Call to Action

- **D-16:** Primary CTA pattern is **phone call + Viber/WhatsApp chat as equal-weight primary actions**, with the contact form secondary. The design system defines the pattern; Phase 4 (CONTACT-01/02) wires the mechanics — so the Phase 2 design ships with a known gap to fill.
- **D-17:** Open question carried to the owner: **should the contact form exist at all**, given call + chat cover the channels (OWNER-QUESTIONS #2). Design the CTA block so removing the form is a subtraction, not a redesign.

### Navigation

- **D-18:** Top navigation is five items: **`Начало · Услуги ▾ · Лаптопи и части · Тествай сам · Контакти`**
- **D-19:** The six categories live in a **single-level dropdown under Услуги** at full readable length. Driven by a hard constraint: the six Bulgarian category names total ~184 characters (~88 even aggressively shortened), against a desktop nav capacity of roughly 60–80 including the logo. They cannot sit inline. A simple six-item dropdown is NOT the "dense mega-menu" IA-02 prohibits.
- **D-20:** **Лаптопи и части** is a fifth nav item grouping the sales line — `laptopi.html` (употребявани лаптопи) and `rezervni-chasti.html` (резервни части). These are a second business line, not repair services, and would otherwise vanish from navigation. Whether the sales line is still active is an owner question (OWNER-QUESTIONS #15).
- **D-21:** Displaced current nav items: **Запитване** folds into Контакти; **Полезни** folds into the catch-all section; **Чести проблеми** dissolves into the per-card symptom lines (D-10) plus the «Не откривате проблема си?» block (D-11).

### Category Pages

- **D-22:** All six categories get **their own page** — three already exist and are preserved by SEO-04 (`mehanichni-problemi.html`, `optimizatsiq.html`, `zalivane-technosti.html` + `tokov-udar.html`); three must be created. New pages follow the site's existing transliterated-Latin slug convention. New pages are additive, so SEO-04 is not violated.
- **D-23:** **Publish-gate: no category page goes live until it has genuine content.** Thin pages do not rank neutrally — they rank badly and can drag site-wide quality signals. These three pages are new, so there is no existing ranking to protect by launching early; nothing is lost by waiting. Until a page earns publication, its card links to the corresponding homepage section. — **Reversibility:** reversible — publishing later is additive.
- **D-24:** Category page template is **core + optional sections**. Required spine every page must fill: title, intro, what we fix (sub-services), symptoms, warranty summary (TRUST-03), CTA. Optional blocks appear only when content exists: process, FAQ, related work, prices (v2). Rationale is explicitly SEO-driven — a lean fixed template would cap depth on the strongest categories (заляти дънни платки, оптимизация) which are the ones that could actually rank; unused template slots cost nothing in search.
- **D-25:** Content-depth targets per category page are a **Phase 3 requirement** — the publish-gate (D-23) needs a defined bar to gate against.

### Service Mapping (the 15 existing icon-boxes)

- **D-26:** Mapping of the current 15 `#our-services` items:

  | Category | Services |
  |---|---|
  | 4 · Заляти и повредени дънни платки | Дозапояване на отпоени BGA чипове · Ребоулинг на BGA чипове · Подмяна на чипсет · Сервиз на захранващи вериги |
  | 2 · Матрици, клавиатури, USB, букси, панти | Подмяна на LCD дисплеи и клавиатури · Подмяна на лампа на матрица · Подмяна/ремонт на подсветка · Подмяна на USB, HDMI, аудио жакове · Подмяна на захранващ жак |
  | 3 · Оптимизация | Безплатни съвети за бързодействие · Профилактика (cross-listed) |
  | 5 · Прегряване и охлаждане | Смяна на вентилатор · Профилактика (cross-listed) |
  | 1 · Счупвания и механични повреди | Счупени панти *(moved from cat 2, D-41)* · корпус/шаси damage — largely new content |
  | 6 · Нестандартна техника | *no existing content* |
  | Folded catch-all | Регенерация на батерии · Подмяна на кабел на адаптори |
  | Not a service | Безплатна диагностика → trust signal (D-15) |

- **D-27:** **Categories 1 and 2 overlap materially.** "Ремонт на счупвания" and "Смяна на матрици, клавиатури, USB портове, захранващи букси, панти" describe the same jobs from different angles — a cracked screen is both. As written, category 1 has no services that are not already in category 2. **Resolved by the split in D-40** (user delegated the naming call 2026-08-05); OWNER-QUESTIONS #17 stays open as a *confirmation*, not a blocker.
- **D-28:** **Профилактика is cross-listed** under both category 3 and category 5, as a **link to the single existing page** `profilaktika-laptop.html` — never as duplicated page content. One URL, one canonical, no duplicate-content exposure; internal links from two topical contexts are mildly beneficial. This also resolves category 5's thinness (it would otherwise hold exactly one service).
- **D-29:** Category 5 was both **thin and named as a solution rather than a problem** — customers experience «прегрява»/«шуми»/«изключва се сам», not «нужна ми е смяна на вентилатор». **Resolved by the rename in D-40.**

### Category Naming

- **D-40:** Category names are set as follows (user delegated this call to Claude, 2026-08-05). The **symptom line under each card (D-10) carries the recognition load**, so names stay close to the owner's original intent rather than being rewritten into symptom phrases — except where a rename also fixes a structural problem.

  | # | Name | Change from owner's original | Why |
  |---|---|---|---|
  | 1 | **Счупвания и механични повреди** | tightened; now owns impact damage only | Makes the D-27 split legible and matches the existing `mehanichni-problemi.html` page it maps to |
  | 2 | **Екран, клавиатура и портове** | shortened from 66 chars | The original is unusable as a card title; covers матрици, клавиатури, USB, букси |
  | 3 | **Оптимизация** | unchanged | Already short and clear; the symptom line handles «бавен е» |
  | 4 | **Заливане и ремонт на дънни платки** | lightly tightened | Keeps «заливане» — the one word customers actually use — while naming the specialism |
  | 5 | **Прегряване и охлаждане** | **renamed from «Смяна на вентилатори»** | Fixes two problems at once: customers recognise the symptom, and it naturally absorbs профилактика so the category is no longer thin |
  | 6 | **Нестандартна техника** | shortened | Content still undefined (OWNER-QUESTIONS #3); a broad name keeps options open |

- **D-41:** **Панти (hinges) move from category 2 to category 1**, following the D-27 physical-damage split. This is a **deviation from the owner's original grouping**, which listed панти under category 2. Flag it at the next owner review (OWNER-QUESTIONS #17) — it is a deliberate consequence of the split, not an oversight.
- **D-42:** These names are the working set for Phase 2 structure and Phase 3 content. They are **not** URL slugs — the three new page filenames remain Claude's discretion under the transliterated-Latin convention, and the three existing pages keep their locked filenames regardless of what the category is called on screen.

### Hero

- **D-30:** Hero is **compact (~40–50vh)**: one clear line of what Torin does, the call + Viber/WhatsApp buttons, and the top of the six-category grid visible above the fold on mobile. The current `fullheight` (100vh) hero means a mobile visitor sees zero services before scrolling, which works directly against the project's core value.
- **D-31:** Hero background is a **brand gradient / solid brand surface** — no photography. Zero asset dependency, instant render, and it gives the two themes a genuinely different feel, which is what makes the dev theme switcher useful.
- **D-32:** No parallax, no ScrollMagic, no pagePiling anywhere (DESIGN-01).

### Footer

- **D-33:** **Compact contact-first footer**: contact details (address, three phones, email), working hours, and a call/Viber CTA prominently, with a single row of secondary links beneath (за нас, гаранция, условия, резервни части, лаптопи). Renders on all 16 pages via `footer.php`.
- **D-34:** Address links out to Google Maps and the page carries `LocalBusiness`/`ElectronicsStore` JSON-LD structured data — **no embedded map iframe**, which would add weight to all 16 pages and work against DESIGN-02. Captures the local-search benefit at near-zero cost.

### EU/COVID Content

- **D-35:** **Strip the EU-project/COVID content from the homepage** (satisfies CONTENT-02) but **keep `covid.html` live and unlinked** (or linked only from the footer). The page is EU grant publicity for project **BG16RFOP002-2.073** (ОПИК 2014-2020, 10 000 лв, beneficiary ТОРИН КЪМПАНИ ООД) and EU structural-fund grants carry mandatory publicity obligations (Reg. EU 1303/2013 Art. 115 + Annex XII). The obligation has very likely lapsed for a 2020-era COVID measure under a closed programme period, but the downside of being wrong is an audit finding against the company. CONTENT-02 only requires the content stop competing for attention on the homepage — it does not require deleting the page. This gets the full benefit at zero compliance and zero SEO risk. — **Reversibility:** reversible — retiring the page later is a one-line change plus a 301.
- **D-36 [deferred]:** If the owner later confirms the obligation has expired, retire `covid.html` with a **301 redirect**, never a bare 404. — **Deferred to Phase 3/4, pending owner sign-off.** This decision is conditional on an owner confirmation that has not happened; STATE.md carries `covid.html`'s fate as an open pending-owner-decision blocker. Phase 2 retires no URLs at all — URL preservation is a standing Phase 1 guarantee (SEO-04, MIGR-01) — so there is deliberately no Phase 2 plan work for D-36. The decision stands and is tracked; it is actionable only once the owner answers, in the phase that handles `covid.html`'s content move (CONTENT-02, Phase 3) or the cutover (MIGR-02, Phase 4). Recorded here so the Phase 2 decision-coverage gate resolves without fabricating out-of-phase work.

### Claude's Discretion

- Full palette derivation for both themes beyond the anchor colors — neutrals, surfaces, semantic/state colors, contrast ramps.
- Component and density language — corner radius, shadow depth, spacing scale, button shapes, and whether the two themes differ in character or only in color.
- Mobile menu behaviour and how the Услуги dropdown becomes an accordion/overlay.
- Timing of the **logo redraw** to SVG or 2× raster. Current `torin-logo.png` is 150×80 and will look soft on retina; the redraw should happen, but which phase it lands in is Claude's call.
- Exact icon set/style for the six categories, provided each slot is photo-swappable per D-37.
- New page filenames for the three missing categories, following the existing transliterated-Latin convention.
- CSS architecture and how tokens are organized.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Project & Requirements
- `.planning/PROJECT.md` — project context, constraints, the six owner-priority categories, hosting details
- `.planning/REQUIREMENTS.md` — DESIGN-01, IA-01, IA-02, SEO-02 map to this phase; DIFF-01/02/03, TRUST-03, CONTENT-02 constrain its structure
- `.planning/ROADMAP.md` §Phase 2 — phase goal and the four success criteria
- `.planning/OWNER-QUESTIONS.md` — living list of items awaiting shop-owner input; several block or reshape this phase's output

### Prior Phase
- `.planning/phases/01-migration-safety-net-foundation/01-CONTEXT.md` — D-01/D-02 (all build work in `public_html/new/`, Claude handles FTP), D-07 (canonicalize to `https://torin.bg`)
- `.planning/phases/01-migration-safety-net-foundation/01-URL-INVENTORY.md` — the 16 locked URLs; `covid.html` and `problem-stari.html` marked pending-owner-decision

### Research
- `.planning/research/SUMMARY.md` §"Resolving the Stack/Architecture Tension" — why PHP-include over Astro; load-bearing for how this design system is built
- `.planning/research/ARCHITECTURE.md` — PHP-include pattern, deploy workflow
- `.planning/research/PITFALLS.md` — URL/ranking continuity risks that constrain any IA change

### Live Site Baseline
- `site-current/assets1/css/themes/business.css` — the actual applied theme; source of D-01's `#ffc70a`/`#0e305d`
- `site-current/assets1/img/torin-logo.png` — source of Theme A's `#fbad03`/`#0547dc`; 150×80, needs redrawing
- `site-current/index.html` — current hero (lines ~227-270), nav (lines ~126-210), `#our-services` 15 icon-boxes (lines ~508+)
- `site-current/covid.html` + `site-current/covid-19/` — EU grant publicity content and ЕС/ЕФРР/ОПИК logo assets (D-35)
- `site-current/laptopi.html`, `site-current/rezervni-chasti.html` — the sales line grouped under D-20

### Phase 1 Foundation (what this phase builds on)
- `src/includes/header.php` — shared head + contact chrome; **line 16 carries the stale `#3ed2a7` theme-color that D-01 corrects**; already declares `lang="bg"`
- `src/includes/footer.php` — shared footer, rebuilt per D-33/D-34
- `src/includes/site-config.php` — contact values (phone, email) pulled by the includes
- `src/.htaccess` — `AddHandler application/x-httpd-php52 .html .htm` (CloudLinux Alt-PHP; version-specific handler name is required)

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `src/includes/header.php` / `footer.php` / `site-config.php` — the PHP-include foundation proven live in Phase 1. The design system plugs into these; contact values are already externalized to `site-config.php` rather than hardcoded.
- All 16 page filenames already scaffolded under `public_html/new/` and live-verified — the design system applies to existing files, it does not create the page set.
- `site-current/covid-19/` — ЕС/ЕФРР and ОПИК logo images, must be retained while `covid.html` lives.

### Established Patterns
- Zero build tooling. Plain HTML/CSS/JS + PHP `include()`, deployed by direct FTP file placement. The design system must preserve this — no Node build step, no bundler.
- Host runs **PHP 5.2.17** (live-confirmed in Phase 1). Any PHP written must be 5.2-safe — no short array syntax, no namespaces, no closures.
- Slug convention is transliterated Latin (`zalivane-technosti`, `mehanichni-problemi`, `profilaktika-laptop`) — new category pages follow it.
- Bootstrap-4-era grid/utility classes throughout the current markup; the redesign replaces this layer.

### Integration Points
- `public_html/new/` on `bell.host.bg` — the live preview target for all Phase 2 work, visible at `torin.bg/new`
- `mailer.php` — contact form handler, untouched this phase (hardened in Phase 4 / CONTACT-03)
- The `<meta name="theme-color">` in `header.php` — the single line D-01 corrects

### What Must Be Removed
- jQuery, jQuery UI, ScrollMagic, pagePiling, Modernizr, `theme-vendors.js`, `theme.min.js`
- Two external CDN script tags in `index.html` (`ajax.googleapis.com` jQuery, `cdnjs.cloudflare.com` Modernizr) — both currently loaded over plain `http://`
- All `data-parallax`, `data-custom-animations`, `data-split-text`, `data-fittext` attributes

</code_context>

<specifics>
## Specific Ideas

### Verified findings from this discussion (evidence, not assumption)

- **No current font can render Bulgarian.** All three self-hosted fonts contain **zero** Cyrillic glyphs (U+0400–04FF) — verified by parsing the WOFF `cmap` tables of `basiersquare-regular-webfont.woff`, `GlacialIndifference-Regular.woff`, and `cerebri-sans.woff`. The Google Fonts URL in use (`css?family=Barlow:600,700`) serves only `latin`, `latin-ext`, `vietnamese` — no Cyrillic `unicode-range` — verified by fetching the CSS.
  **Consequence:** the live Bulgarian-only site currently renders **all** text, headings and body, in whatever generic OS fallback sans-serif the visitor has. The purchased template's typography has never applied to this site's actual content. This **reframes phase success criterion 4** — Cyrillic typography must be *established*, not merely *preserved*.
- **Logo colors extracted** from `torin-logo.png` (150×80, colortype 6): amber `#fbad03` (251,173,3) and electric blue `#0547dc` (5,71,220).
- **Current `#our-services` is 15 flat icon-boxes** (ROADMAP says ~18). Категория 6 has zero existing items; Регенерация на батерии and Безплатна диагностика map to none of the six.
- **`covid.html` contains a copy-paste error** — the results paragraph names "Венера-АКС ООД", a different company, while the beneficiary is ТОРИН КЪМПАНИ ООД. Worth flagging to the owner.

### User framing worth preserving
- On the folded section: *"in case the users didn't find their problem in the main categories"* — this framing is what turned it from a leftover-services bin into a symptom-organized finder (D-11).
- On category 5: *"clients may not recognize it as a problem they are having but it is rather the solution that they need"* — the cause-vs-symptom insight that drove D-29, and which generalizes to how all six categories are named.
- On the category page template: *"i sure would like that there is no compromise with the SEO"* — this criterion is what selected D-24 and produced the publish-gate in D-23.

</specifics>

<deferred>
## Deferred Ideas

- **Logo redraw to SVG / 2×** — agreed it should happen; timing left to Claude's discretion, not necessarily this phase. Depends partly on whether an original vector source exists (OWNER-QUESTIONS #11).
- **Photo replacement of category icons** — the design must make icon slots photo-swappable now (D-37 below), but actual photography is owner-supplied and lands in a later phase. Two photo briefs produced this phase specify what is needed.
- **Component & density language deep-dive** (radius, shadows, spacing scale, whether themes differ in character) — offered but not discussed; left as Claude's discretion.
- **Mobile menu behaviour** — offered but not discussed; Claude's discretion.
- **TRUST-03 гаранция summary wording** on category pages — the template reserves the slot (D-24); the content is Phase 3.
- **v2 requirements** — PRICE-01 (price ranges), GALLERY-01 (before/after gallery), TURNAROUND-01, REVIEWS-01, BLOG-01. The category template's optional blocks (D-24) are designed to accept prices later without redesign.

### Reviewed Todos (not folded)
None — no pending todos matched this phase.

</deferred>

<phase_artifacts>
## Additional Artifacts This Phase Produces

Beyond the design system itself, this phase produces three documents requested during discussion:

- **D-37:** `.planning/OWNER-QUESTIONS.md` — **project-level** (spans all phases), a living list of everything needing shop-owner input, so the user can batch questions instead of contacting the customer repeatedly. Seeded with 19 items. Must be appended to, not replaced, as new questions surface in later phases.
- **D-38:** **Photo brief #1 — the six category images.** For each of the six categories, what the replacement photo should depict, with recommended contents, framing and any must-show details. Every category card's visual slot must be built so an icon can be swapped for a photo **without layout changes**.
- **D-39:** **Photo brief #2 — all other site images.** Every remaining image slot the redesign creates (hero, trust/about, workshop, team, etc.), same level of detail.

</phase_artifacts>

---

*Phase: 2-Design System & Information Architecture*
*Context gathered: 2026-08-05*
