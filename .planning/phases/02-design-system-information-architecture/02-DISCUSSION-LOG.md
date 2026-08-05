# Phase 2: Design System & Information Architecture - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-08-05
**Phase:** 2-design-system-information-architecture
**Areas discussed:** Visual identity & style, Homepage service layout, Navigation structure, Hero section treatment, Category page template, Mapping the 15 services, Footer structure

---

## Visual identity & style

### Brand color

| Option | Description | Selected |
|--------|-------------|----------|
| Keep mint-green accent | Preserve `#3ed2a7` for brand recognition | |
| Refresh the palette | Modernize colors entirely | |
| Keep green but darker | Deeper, more professional green | |
| **User correction** | *None of the above — the premise was wrong* | ✓ |

**User's choice:** Corrected the question. `#3ed2a7` is not the brand color — `#ffc70a` is, defined in `business.css`. Requested a dev-time theme switcher, and that the logo's colors drive the main theme.

**Notes:** Claude verified the correction: `business.css :root` defines `--color-primary: #ffc70a`, `--color-secondary: #0e305d`. `#3ed2a7` appears only in the `<meta name="theme-color">` tag — an unchanged template default, never used in styling, carried into Phase 1's `header.php`. Logo colors extracted programmatically: amber `#fbad03`, electric blue `#0547dc`. Notably the logo's blue is nothing like `business.css`'s navy secondary.

---

### Theme switcher lifecycle

| Option | Description | Selected |
|--------|-------------|----------|
| Dev-only, removed at cutover | Temporary comparison tool | ✓ |
| Ships live to visitors | Permanent visitor-facing toggle | |
| Dev-only now, decide later | Defer the call to cutover | |

**User's choice:** Dev-only. Two themes — one from the logo colors, one from the current `business.css` colors. Remaining palette details delegated to Claude.

**Notes:** Answered in free text alongside the logo-redraw decision.

---

### Logo redraw

**User's choice:** Yes, the redraw should happen; timing left to Claude's discretion.

**Notes:** Raised by Claude after finding `torin-logo.png` is only 150×80 raster and will look soft on retina displays. Whether an original vector exists became owner question #11.

---

### Typography

| Option | Description | Selected |
|--------|-------------|----------|
| Inter (Cyrillic-complete) | One modern variable family, self-hosted | ✓ |
| Two-font pairing | Display face + neutral body face | |
| System font stack | No webfont, native OS fonts | |

**User's choice:** Inter, self-hosted.

**Notes:** This question only arose because Claude verified that **none** of the site's fonts support Cyrillic — all three self-hosted WOFFs contain zero glyphs in U+0400–04FF (verified by parsing their `cmap` tables), and the Google Fonts URL in use serves only latin/latin-ext/vietnamese. The live Bulgarian site has therefore always rendered in OS fallback fonts. This reframed success criterion 4 from "preserve Cyrillic" to "establish it."

---

### Visual language for the six categories

| Option | Description | Selected |
|--------|-------------|----------|
| Icon-led, clean | Icons only, no photo dependency | |
| Photo-led | Real workshop photos anchor each category | |
| Hybrid — icons now, photos later | Photo-swappable slots | ✓ |

**User's choice:** Icon-led now since no photo material exists, with slots designed so icons can later be replaced by real workshop photos. Additionally requested two photo brief documents — one for the six category images, one for all other site images.

---

## Homepage service layout

### Do separate category pages add value? *(user clarification request)*

**User's question:** Whether separate pages per category are required and add value, versus a homepage loaded with all the detail.

**Claude's answer:** Separate pages win on search targeting — one page carries one `<title>`/meta description (SEO-01 requires unique ones per page), and a homepage anchor cannot. Most visitors arrive from Google searching a specific problem. But thin pages rank *badly*, so pages only pay off where real content exists. Crucially, three of the six categories already have strong existing pages that SEO-04 preserves regardless, so the question was narrower than it first appeared.

---

### Handling the 3-existing / 3-missing asymmetry

| Option | Description | Selected |
|--------|-------------|----------|
| Cards link out where a page exists | Inline sections for the gaps | |
| All six get their own page | Three new pages created | ✓ |
| Homepage holds everything | Existing pages kept only for URL preservation | |

**User's choice:** All six get their own page.

**Notes:** Claude flagged that Category 6 has no content and would launch as a stub, making owner input launch-blocking. This later drove the publish-gate decision.

---

### Where does everything outside the six go? *(user proposal)*

**User's choice:** The customer named those six as the work they do, so they stay as the prominent grid; everything else goes into a folded section holding all other options, for users who didn't find their problem in the main categories.

**Notes:** Claude confirmed folded content indexes normally in Google provided it's real HTML rather than JS-loaded. The user's own framing — *"in case the users didn't find their problem"* — later turned this section from a leftover-services bin into a symptom-organized finder.

---

### Required differentiators vs the folded section

| Option | Description | Selected |
|--------|-------------|----------|
| Separate "why Torin" blocks | Both differentiators get feature blocks | |
| Everything else folded | Strict adherence to the proposal | |
| Self-test yes, battery folded | Partial | ✓ |

**User's choice:** Self-test tool gets its own homepage feature; battery regeneration goes into the folded section.

**Notes:** Claude flagged a conflict with locked requirements DIFF-01 and DIFF-02, both of which demand homepage-level prominence. The choice satisfies DIFF-01 but **deliberately downgrades DIFF-02** — recorded as a conscious trade-off requiring owner sign-off (owner question #9), so Phase 3 verification doesn't fail silently.

---

### Primary call-to-action

| Option | Description | Selected |
|--------|-------------|----------|
| Tap-to-call phone | Phone dominant | |
| Contact form | Form dominant | |
| Call + chat side by side | Equal-weight primary | ✓ |

**User's choice:** Call + Viber/WhatsApp as equal-weight primary, form secondary — with unresolved doubt about whether the form should exist at all.

**Notes:** This prompted the user to request a standing list of questions for the customer, which became `.planning/OWNER-QUESTIONS.md`.

---

## Navigation structure

### Top nav structure

| Option | Description | Selected |
|--------|-------------|----------|
| Услуги dropdown + 3 links | Six categories in one simple dropdown | ✓ |
| Six shortened labels in nav | Maximum flatness, truncated names | |
| Minimal nav, homepage carries it | Услуги jumps to the grid | |

**User's choice:** Услуги dropdown plus three links.

**Notes:** Driven by a hard measurement — the six Bulgarian category names total ~184 characters (~88 even shortened) against a desktop nav capacity of roughly 60–80. They cannot sit inline. Claude clarified that a simple six-item dropdown is not the "dense mega-menu" IA-02 prohibits.

---

### The sales line (used laptops + spare parts)

| Option | Description | Selected |
|--------|-------------|----------|
| Fifth nav item | Grouped as «Лаптопи и части» | ✓ |
| Inside Услуги dropdown | Below the six, with a divider | |
| Footer + folded only | Pages preserved, nav presence dropped | |

**User's choice:** Fifth nav item — and add it as a question for the customer.

**Notes:** Claude verified `laptopi.html` is "Употребявани лаптопи" and `rezervni-chasti.html` is "Резервни части" — a second business line, not repair. Became owner question #15.

---

### "Чести проблеми" — problem-first entry point

| Option | Description | Selected |
|--------|-------------|----------|
| Keep as homepage section | Standalone symptoms section | |
| Drop it | Six categories suffice | |
| Merge into category cards | Symptoms as card sub-text | |
| **Claude's recommendation** | *Both cards + folded section* | ✓ |

**User's choice:** Asked for Claude's recommendation, then accepted both parts of it.

**Notes:** The user's instinct — that customers don't know the *cause* of their problem — was correct and Claude backed it with a mapping showing that for roughly half the categories a customer cannot self-select (e.g. «изключва се сам» → вентилатори; «не се включва» → three possible categories). Recommendation: symptom lines on each of the six cards (translation at the point of choosing), plus re-purposing the already-agreed folded section as a symptom-organized «Не откривате проблема си?» block. Adds no new sections. Owner question #16 added for the real customer phrasing.

---

## Hero section treatment

### Hero size and content

| Option | Description | Selected |
|--------|-------------|----------|
| Compact — categories near fold | ~40–50vh, services visible immediately | ✓ |
| Symptom prompt hero | Leads with a problem question | |
| Full-width image, static | Image-led, parallax removed | |

**User's choice:** Compact hero.

**Notes:** Claude flagged that the current `fullheight` (100vh) hero means a mobile visitor sees zero services before scrolling — working directly against the project's core value.

---

### Hero background

| Option | Description | Selected |
|--------|-------------|----------|
| Brand gradient / solid | No asset dependency | ✓ |
| Keep a photo background | Existing bg.jpg, swapped later | |
| Subtle pattern / texture | Middle ground | |

**User's choice:** Brand gradient / solid.

---

## Category page template

### Template richness

| Option | Description | Selected |
|--------|-------------|----------|
| Core + optional sections | Required spine plus conditional blocks | ✓ |
| Lean fixed template | One short identical shape | |
| Rich fixed template | Full editorial shape, forces content | |

**User's choice:** Found the decision hard, but stated the criterion — *"i sure would like that there is no compromise with the SEO"* — and accepted the SEO-derived answer.

**Notes:** Claude scored all three against SEO: lean fixed would cap depth on the categories that could actually rank (заляти дънни платки, оптимизация), handicapping the winners to protect the weakest page; rich fixed has the best ceiling but ships visible stubs if content doesn't materialize; core + optional lets strong pages go deep while unused slots cost nothing in search. Claude then raised that the bigger SEO risk was publishing before content exists — and since the three new pages have no existing ranking to protect, waiting costs nothing. Both parts accepted.

---

## Mapping the 15 services

### Categories 1 and 2 overlap

| Option | Description | Selected |
|--------|-------------|----------|
| 1 = physical damage, 2 = parts | Split by cause vs component | |
| Ask the owner | Defer, use the split as working assumption | ✓ |
| Merge into five categories | Remove the ambiguity | |

**User's choice:** Ask the owner.

**Notes:** Claude found that "ремонт на счупвания" and "смяна на матрици, клавиатури…" describe the same jobs from different angles, leaving category 1 with no services of its own. Became owner question #17, with physical-damage-vs-parts as the working assumption.

---

### Профилактика placement

| Option | Description | Selected |
|--------|-------------|----------|
| Category 5 — with вентилатори | Cooling work | |
| Category 3 — with оптимизация | Customer intent | |
| Both — cross-listed | Appears under both | ✓ |

**User's choice:** Cross-listed, conditional on Claude confirming no SEO downside.

**Notes:** Claude resolved the condition: cross-listing a **link** to a single page carries no duplicate-content risk — one URL, one canonical — and internal links from two topical contexts are mildly beneficial. Only creating two similar pages would hurt. Since `profilaktika-laptop.html` already exists and is preserved by SEO-04, it stays the canonical home. This also fixes category 5's thinness.

---

### Category 5 naming *(user observation)*

**User's input:** Requested an owner question noting that category 5 is too thin, and that clients may not recognize it as a problem they're having — it names the *solution* they need rather than the *problem* they have.

**Notes:** Claude noted this generalizes the same cause-vs-symptom insight from the «Чести проблеми» discussion, and proposed «Прегряване и охлаждане» as a symptom-led rename that would also absorb профилактика. Became owner question #19.

---

## Footer structure

| Option | Description | Selected |
|--------|-------------|----------|
| Full sitemap footer | Four columns, all links | |
| Compact contact-first | Contact, hours, CTA + one link row | ✓ |
| Contact + map | Embedded map | |

**User's choice:** Compact contact-first footer.

**Notes:** Claude applied one refinement — a Google Maps *link* plus `LocalBusiness` JSON-LD instead of an embedded map iframe, capturing the local-SEO benefit without adding weight to all 16 pages (protecting DESIGN-02).

---

## EU / COVID content *(raised by user at wrap-up)*

**User's input:** Said the COVID part "could go away."

**Claude's finding:** Inspecting `covid.html` revealed it isn't marketing content but **EU grant publicity** — project BG16RFOP002-2.073, ОПИК 2014-2020, 10 000 лв, beneficiary ТОРИН КЪМПАНИ ООД, with ЕС/ЕФРР and ОПИК logo assets. EU structural-fund grants carry mandatory publicity obligations (Reg. EU 1303/2013 Art. 115 + Annex XII). The obligation has very likely lapsed for a 2020-era COVID measure under a closed programme period, but being wrong risks an audit finding against the company.

**Resolution:** Strip the content from the homepage (fully satisfying CONTENT-02, which only concerns homepage attention) while keeping `covid.html` live and unlinked — full benefit, zero compliance and zero SEO risk. Retire later with a 301 if the owner confirms. Became owner question #4. Claude also spotted a copy-paste error in the page naming a different company, "Венера-АКС ООД".

---

## Claude's Discretion

- Full palette derivation for both themes beyond the anchor colors — neutrals, surfaces, semantic/state colors, contrast ramps
- Component and density language — radius, shadows, spacing scale, button shapes, whether themes differ in character or only color
- Mobile menu behaviour and how the Услуги dropdown adapts
- Timing of the logo redraw
- Icon set and style for the six categories
- Filenames for the three new category pages
- CSS architecture and token organization

---

## Deferred Ideas

- Logo redraw to SVG/2× — agreed, timing flexible, partly depends on whether a vector source exists
- Photo replacement of category icons — slots built now, photography owner-supplied later
- Component & density deep-dive — offered, not discussed
- Mobile menu behaviour — offered, not discussed
- TRUST-03 гаранция wording on category pages — slot reserved, content is Phase 3
- v2 requirements (PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01) — the optional template blocks are designed to accept prices later without redesign

---

## Corrections Claude made to its own assumptions

Recorded because they materially changed the phase's direction:

1. **Brand color** — asserted `#3ed2a7` from the `theme-color` meta tag; user corrected to `#ffc70a`. Verification confirmed the user and revealed the meta tag as dead template residue that Phase 1 propagated into `header.php`.
2. **Typography scope** — assumed the task was preserving Cyrillic rendering; verification showed Cyrillic has never rendered in the intended fonts at all.
3. **Category page necessity** — initially framed as "create six new pages"; checking the existing site showed three already existed and were locked by SEO-04, narrowing the real question considerably.
