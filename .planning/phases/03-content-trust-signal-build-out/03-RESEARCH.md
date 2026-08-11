# Phase 3: Content & Trust-Signal Build-Out — Research

**Researched:** 2026-08-11
**Domain:** Bulgarian-language content production on a zero-build PHP 5.2 static site; local-SEO metadata; Google structured data eligibility; trust-signal presentation without third-party runtime dependencies
**Confidence:** HIGH on structured-data eligibility and in-repo mechanics (both read from source this session); MEDIUM on SEO copy guidance; LOW on anything the shop owner alone can supply

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

*Copied verbatim from `03-CONTEXT.md` §Implementation Decisions. Where the user-supplied
`torin-new-build-tasklist.md` and CONTEXT.md disagree, CONTEXT.md wins.*

**URL Structure and Page Set**

- **D3-01: SEO-04 stands — no URL changes, no 301 map.** The task list's Workstream 0 proposed
  rewriting all sixteen URLs (`mehanichni-problemi.html` → `/remont-na-schupvaniya/`,
  `optimizatsiq.html` → `/optimizatsiya/`, …) behind a redirect map. **Rejected.** SEO-04 is
  marked Complete, Phase 1 success criterion 2 reads "no visitor-facing link will change through
  the redesign," and an entire phase was built to make URL preservation the default. The upside is
  marginal — keyword-in-URL is a small ranking factor and 301s pass essentially full PageRank — while
  the downside is a sixteen-URL migration on the project's single largest stated risk, with
  Search Console access still unavailable (OWNER-QUESTIONS #1 OPEN), meaning nobody could observe
  whether it worked. — **Reversibility:** one-way — once new slugs are published and indexed,
  reverting needs a second 301 layer and forfeits signal twice.
- **D3-02: New pages are additive and follow the existing `.html` transliterated-Latin
  convention** (D-42), NOT the task list's trailing-slash directory style. The host's
  `AddHandler application/x-httpd-php52 .html .htm` is the proven mechanism; a directory-style URL
  set would need separate handling for no visitor-visible gain. New pages create no redirects.
- **D3-03: Cluster 2 splits into five child pages.** The task list's reasoning was accepted:
  панти, матрица, клавиатура, USB/HDMI портове and захранваща букса are five distinct searches
  with their own price intent, and one page competing for all five loses all five.
  «Екран, клавиатура и портове» (`ekran-klaviatura-portove.html`) becomes a routing hub —
  deliberately short (~350 words), passing authority to the children, and **not** targeting a
  competitive keyword of its own. Children link up to the hub and across to two or three siblings.
- **D3-04: All six categories stay.** The task list parked Прегряване и охлаждане and
  Нестандартна техника in its backlog. **Rejected** — PROJECT.md carries the six categories as a
  hard constraint ("explicit owner direction, not inferred"), IA-01 is Complete on six sections,
  and Phase 3 success criterion 5 requires cat-6 content. Category 5 ships (профилактика
  cross-lists into it per D-28, which is what resolved its thinness). Category 6 stays behind the
  D-23 publish gate until OWNER-QUESTIONS #3 is answered.
- **D3-05: `problem-stari.html` is repurposed as the category 6 page.** Its slug reads as «стари»
  and category 6's own symptom line in `categories.php` is «нестандартна или стара техника, която
  другаде не приемат» — the semantics fit. This puts Нестандартна техника on an **existing indexed
  URL** rather than a new slug, inheriting whatever authority it holds, needing no new file, and
  resolving OWNER-QUESTIONS #5 without retiring anything. Still gated by D-23 until #3 is answered.
  Note its current content duplicates `za-bateriite.html` verbatim (see D3-12) — that paragraph
  does not survive the repurpose.

**Requirement Scope**

- **D3-06: PRICE-01 and REVIEWS-01 stay in v2.** No `/ceni/` page, no `price-teaser` block, no
  Offer/PriceSpecification schema this phase. The category template's `prices` slot (D-24) stays
  unused — by design. **`smyana-na-matrica` ships without its stated winning argument.** Accepted
  knowingly.
- **D3-07: TRUST-02 ships as a static badge only.** Ship a hardcoded rating + review count in
  `site-config.php`, linking to the Google Business Profile. **No** Places API, **no** per-service
  review quotes, **no** `/otzivi/` page. The real number must be confirmed once (OWNER-QUESTIONS #7)
  before it ships. AggregateRating schema must NOT be emitted, since no reviews are visible on-page.

**Handling Open Owner Questions**

- **D3-08: Draft, flag, ship what is unblocked.** Three OPEN owner questions gate real content:
  #3 (cat-6 scope, launch-blocking), #16 (real customer phrasing), #7 (review count). Write
  everything that does not depend on them. For the three gaps, draft a best-effort version marked
  `[ASSUMED]` **in-source**, following the convention already established in `categories.php` and
  `site-config.php`, and list them for owner review. Category 6 stays unpublished behind D-23
  regardless of how good the draft is. Do not quote drafted symptom lines back to anyone as
  confirmed shop language.

**Trust Signals**

- **D3-09: TRUST-01 is a designed row of text wordmarks, not logo images.** Six to eight brands,
  headed «Обслужваме всички марки» and closed with «и др.». Zero of eight competitors examined use
  logo images; none claims authorized status; several position explicitly as «извънгаранционен
  сервиз», which is the safe framing to copy. Build the slot so logos could drop in later without
  layout change, same pattern as D-38's photo slots. **Corrects TRUST-01's premise** — what is
  universal is the *claim*, not a logo row.
- **D3-10: TRUST-03 is one shared warranty summary, reframed.** A single block sourced from
  `site-config.php` and reused across all category pages — never retyped per page. The live warranty
  page's actual condition is unusual: it requires the customer to use the laptop 5–6 hours a day
  during the warranty period, to accumulate 150–200 hours of test time. **Reframe it** as a
  statement of confidence. Do not silently drop it, and do not reproduce wording that reads as a trap.

**Differentiators**

- **D3-11: DIFF-02 is owned properly — SUPERSEDES D-13's folded downgrade.** Battery regeneration
  gets distinct, prominent treatment, with `za-bateriite.html` (a locked, indexed URL) as its depth
  page. This **resolves OWNER-QUESTIONS #9**. DIFF-02 moves from "knowingly unmet" to met.
- **D3-12: SmartBattery.eu is dead — every reference must be removed or fixed.** Four references
  exist, all in `site-current/`, none yet ported into `src/`:
  `site-current/index.html:963` (remove `office@smartbattery.eu`),
  `site-current/za-bateriite.html:158` (remove; replaced by D3-11 content),
  `site-current/problem-stari.html:181` (does not survive the D3-05 repurpose),
  `site-current/uslovia.html:109` (**careful edit, not deletion** — legal text making commitments on
  behalf of a site that no longer exists).
- **DIFF-01** needs no new structural decision — the self-diagnostic already has its homepage
  feature block (D-12). What Phase 3 owes is real content on `test-laptop.html` plus routing links
  from each symptom to the matching service page.
- **DIFF-03** stays inside category 4 (D-14), now with concrete evidence to present.

**Content Production**

- **D3-13: Rewrite service pages, port the rest — improving while porting.** Service and content
  pages (`profilaktika-laptop`, `za-bateriite`, `test-laptop`, `tokov-udar`, `about`) are rewritten
  against the adapted Definition of Done. Legal and utility pages (`uslovia`, `warrently`, `msg`,
  `laptopi`, `rezervni-chasti`) are ported near-verbatim — but porting still means **fixing what is
  visibly wrong**: dead links, the `усливията` → `условията` typo, the SmartBattery references,
  stale contact details. A port is not a copy.
- **D3-14: Adapted Definition of Done, 600–1000 words, is the D-25 depth bar.**

  | Applies now | Deferred to a v2 checklist |
  |---|---|
  | One `<h1>` with primary keyword + «София» where it reads naturally | Price teaser / «от X лв» block |
  | `<title>` ≤ 60 chars, keyword first | Reviews block (rating + quotes) |
  | Meta description ≤ 155 chars, ending in a CTA | 2–3 real repair photos with descriptive alt text |
  | Symptom block — 4–6 bullets in customer language | Offer / PriceSpecification schema |
  | Process block — numbered steps | |
  | Warranty block (D3-10) | |
  | 3–5 FAQ answering real objections | |
  | Internal links: up to hub, across to 2–3 siblings, out to one related page | |
  | Schema: Service + FAQPage + BreadcrumbList | |
  | CTA above the fold and repeated after the process block | |
  | 600–1000 words Bulgarian | |
  | Mobile check: tap targets ≥ 44px, no horizontal scroll | |

### Claude's Discretion

- Page filenames for the five new child pages and the two remaining new category pages, following
  the transliterated-Latin `.html` convention (D-42).
- How child pages are represented in data. `categories.php` holds the six categories and is the
  wrong home for children — the structure is the planner's call, but the D-23 publish-gate pattern
  and the "no href ever hand-typed" rule must extend to them.
- Where the brand wordmark row sits on the page, and which six to eight brands appear given the
  list is unconfirmed (see OWNER-QUESTIONS #22).
- Schema implementation. `jsonld.php` already emits LocalBusiness; Service, FAQPage and
  BreadcrumbList are additions, emitted from the template rather than hand-written per page.
- Whether the EU/COVID content moves to About as prose or as a compact block (CONTENT-02).

### Deferred Ideas (OUT OF SCOPE)

**To Phase 4:** Workstream 5 in full (photo-upload quote form, sticky mobile call bar, real
thank-you page, conversion events, contact-form hardening — CONTACT-01/02/03/04). Workstream 7 in
full (content freeze, move `/new` to root, sitemap generation and submission, crawl, Rich Results
validation, GBP website URL update, Core Web Vitals, 404-log monitoring — SEO-03, MIGR-02,
DESIGN-02). Removing the staging `noindex` at cutover. Google Search Console + Analytics setup.

**To v2:** PRICE-01 (full price table, `/ceni/`, per-page price teasers, Offer schema, dual лв/€
display — *worth verifying whether dual-currency display is now a legal requirement*). REVIEWS-01
(Places API ingestion, per-service review quotes, `/otzivi/` page, AggregateRating schema,
review-generation flow). GALLERY-01 / TURNAROUND-01. `/optimizatsiya/upgrade-ssd/` as a child page.
Standalone реболинг/BGA page, per-brand pages, English `/en/` layer with hreflang,
neighbourhood-targeted local pages, blog cadence beyond two ported articles.

**Recorded but not acted on:** Express-service tier. Free-diagnostics declined-repair policy detail.
Logo redraw to SVG/2×.

**Reviewed todos, not folded:** `redraw-category-icons.md`, `verify-viber-button-before-launch.md`.
</user_constraints>

---

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| **TRUST-01** | User sees an "all brands serviced" row | §Architecture Patterns Pattern 4 (wordmark row markup + CLS/a11y); §Common Pitfalls P-7 (trademark exposure, EUTMR Art. 14 referential-use analysis); Open Question OQ-1 (brand list unconfirmed) |
| **TRUST-02** | User sees a Google rating badge linking to the GBP | §Don't Hand-Roll DH-1 (Places API cost/key exposure, third-party widget cost); §Common Pitfalls P-1 (self-serving AggregateRating is *categorically* ineligible, not conditionally); §Code Examples E-4 (static badge, `rel="noopener"`, `sameAs`); Open Question OQ-2 |
| **TRUST-03** | Warranty terms summarized on relevant service pages | §Architecture Patterns Pattern 3 (`warranty` slot already exists, is plain-text-only); §Content Inventory row `warrently.html` (verbatim source terms + the 5–6h condition); Open Question OQ-3 |
| **DIFF-01** | Self-diagnostic tool surfaced as a homepage feature | §Content Inventory row `test-laptop.html` — the tool is a 4-section static symptom→cause guide, ~1,400 words, already good; homepage block already built (D-12). Work is routing links, not authoring |
| **DIFF-02** | Battery-regeneration story surfaced as a distinct differentiator | §Content Inventory rows `za-bateriite.html` / `index.html:654-656` — the Panasonic-cell claim, the HILUMIN spot-welding process, the 1-year warranty claim and the "we stopped importing new batteries" positioning all exist verbatim in Bulgarian today; §Common Pitfalls P-6 (the 1-year battery warranty contradicts the 1-month service warranty) |
| **DIFF-03** | BGA/chip-level expertise with clear visual hierarchy | §Content Inventory rows `index.html:595-640` (three-tier BGA offering) and `profilaktika-laptop.html:398-411` (IR machine, AMTECH flux, 90%, 10 °C, know-how). CONTEXT attributes this evidence to the task list; it is in the live site, in the shop's own words |
| **CONTENT-01** | Dedicated content for non-standard electrical equipment | Blocked on OWNER-QUESTIONS #3; §Common Pitfalls P-8 (the D3-05 repurpose silently discards ~1,700 words of good board-level power-circuit copy — decide where it goes) |
| **CONTENT-02** | EU/COVID content no longer competes on the homepage | §Compliance Findings C-1 — the publicity obligation is scoped to *«по време на изпълнението на проекта»*; the project ended 04.11.2020; the website element was conditional and never required homepage placement. Phase 2's footer link already exceeds the compliant minimum. **CONTENT-02 is already structurally satisfied in `src/`** — what remains is the About-page relocation |
| **SEO-01** | Unique `<title>` and `<meta name="description">` per page | §SEO Metadata Architecture — measured Cyrillic pixel widths, the brand-suffix budget problem, the 16-page formula table, and the two mechanical facts (`$torin_title` set before the header include; the three category pages derive their title from the record) |
</phase_requirements>

---

## Summary

Phase 3 is a **content-authoring phase running on an already-built machine**, and the single most
useful thing this research can do for the planner is state exactly where that machine's seams are
and which of them do *not* fit the content the phase intends to put through them. Phase 2 delivered
`src/includes/category-page.php` — a guarded, eight-slot template whose every text slot passes
through `torin_esc()`. That is correct and safe, and it also means **no Phase 3 content block can
contain an inline link, an emphasis, or a second paragraph** unless the template gains a new slot.
Three of the phase's best content ideas (the liquid-damage first-aid list, the «матрица или
видеочип?» diagnostic, the «кога не си струва» honesty block) do not fit any existing slot. A
second, sharper collision: D3-14 requires an `<h1>` carrying the primary keyword plus «София», but
the template's `<h1>` is `torin_esc($cat['name'])` — the category display name, read from the record
precisely so it cannot drift. «Оптимизация» is eleven characters and contains neither a keyword
phrase nor a city. These two are the phase's first planning decisions and they are structural, not
editorial.

The second finding reverses part of the phase's own schema plan. **Google's FAQ rich result stopped
appearing on 2026-05-07 and its documentation has been deleted**, and there has never been a
`Service` rich result at all. Of the three schema types D3-14 names — Service, FAQPage,
BreadcrumbList — **only BreadcrumbList earns anything in search**, and it happens to be the one the
D3-03 hub/child hierarchy makes genuinely cheap. Separately, Google's self-serving-review rule is
*categorical*: an entity marking up reviews of itself under `LocalBusiness` or `Organization` is
ineligible for the star feature regardless of whether the reviews are visible on the page. D3-07
reaches the right conclusion for a reason that is narrower than the actual rule, and the correction
matters because it also invalidates v2's plan to add AggregateRating once REVIEWS-01 lands.

The third finding is that **the phase is materially better resourced than CONTEXT assumes.** The
DIFF-02 and DIFF-03 evidence CONTEXT credits to the task list is present verbatim in the live site,
in the shop's own Bulgarian, at `site-current/index.html:595-660` and
`site-current/profilaktika-laptop.html:398-411`. And `site-current/assets1/img/` holds roughly forty
real repair photographs — corroded boards, torn USB jacks, hot-air-gun damage, the reflow machine —
none of which has been ported into `src/`. D3-14 defers repair photos to v2 on the strength of
OWNER-QUESTIONS #12/13, but those questions ask for *new* photography; these already exist. They are
small (mostly 200×200 px) and therefore evidence-strip material rather than hero imagery, but a page
about board-level repair that shows three photographs of board-level repair is a different page from
one that does not. Finally, the EU-publicity worry behind CONTENT-02 resolves cleanly: the ОПИК
obligation is scoped to the implementation period, which ended 04.11.2020, the website element was
conditional on the firm having a site at all, and it never specified homepage placement — Phase 2's
footer link is already above the compliant minimum.

**Primary recommendation:** Sequence the phase as (0) resolve the two template collisions — h1
override and a rich-text-capable slot — before authoring any page; (1) port the existing Bulgarian
copy and the forty repair photos, fixing what is visibly wrong; (2) author the new child pages;
(3) do the SEO-01 metadata pass *last*, against finished body copy, using a shortened brand suffix.
Emit BreadcrumbList as the one schema addition that pays, and emit Service/FAQPage only as entity
signals with no verification step attached to a rich result that cannot appear.

---

## Architectural Responsibility Map

There is no runtime tier on this project beyond "Apache + PHP 5.2 on shared hosting" and "the
browser." The meaningful decomposition is therefore *which file owns which fact*, and the whole of
Phase 2's design turns on getting that right. Assigning a Phase 3 fact to the wrong file is this
phase's characteristic failure mode.

| Capability | Primary Tier / File | Secondary | Rationale |
|------------|---------------------|-----------|-----------|
| Brand wordmark list (TRUST-01) | `src/includes/site-config.php` (data) | homepage + category template (render) | It is a site-wide fact that will change when the owner answers OWNER-QUESTIONS #22. Same rule as `phones`/`hours`: one writer, many readers |
| Google rating + review count + GBP URL (TRUST-02) | `src/includes/site-config.php` | badge partial; `jsonld.php` for `sameAs` | D3-07 says hardcoded. A number retyped on two pages is a number that will disagree with itself |
| Warranty summary (TRUST-03) | `src/includes/site-config.php` | `category-page.php`'s existing `warranty` slot | D3-10 is explicit: one shared block, never retyped. The slot already exists and already guards emptiness |
| Category display names + symptom lines | `src/includes/categories.php` (already) | — | Established; do not add a second writer |
| Child-page records (D3-03) | **new** data file, e.g. `includes/services.php` | hub page, child pages, Phase 4 sitemap | `categories.php`'s six-record integrity is asserted by plan 02-02's greps; adding five non-category records there breaks that assertion |
| Per-page `<title>` / `<meta description>` | the page file, above the header include | `header.php` renders + escapes | Mechanism already built and proven across all 16 pages |
| BreadcrumbList / Service / FAQPage JSON-LD | **new** emitter in `includes/jsonld.php` (or a sibling) | called from `category-page.php` | D3-14 and CONTEXT both require emission from the template, never hand-written per page |
| LocalBusiness JSON-LD | `src/includes/jsonld.php` (already) | `footer.php` includes it | Established |
| Body copy | the page file's `$torin_*_page` array | `category-page.php` renders | Established by the three published category pages |
| Repair photographs | `src/img/` (new subdirectory) | page files | `src/img/` currently holds only `torin-logo.png`; a `src/img/repairs/` namespace keeps the port reviewable |
| EU-publicity disclosure | `src/covid.html` + `footer.php:111` link (already) | `about.html` if CONTENT-02's relocation is prose | Compliance artefact, not a service page — see C-1 |

---

## Project Constraints (from CLAUDE.md)

`./.claude/CLAUDE.md` is unusual for this project: its "Technology Stack" section documents an
Astro/Tailwind/Node recommendation that the project **explicitly rejected**. `REQUIREMENTS.md`
§Out of Scope reads: *"CMS/WordPress or Node/Astro build pipeline — Disproportionate for a ~16-page,
low-change-velocity site."* The binding directives are therefore the non-stack ones, plus the
decisions recorded in the planning set.

| Directive | Source | Effect on Phase 3 |
|---|---|---|
| All content must be in Bulgarian; no other language versions in scope | CLAUDE.md §Constraints | Every string this phase authors is Bulgarian. No `hreflang`, no `/en/`, no language switcher |
| Must deploy to existing FTP/shared hosting at `bell.host.bg`; no infrastructure migration | CLAUDE.md §Constraints | No build step may become a deploy prerequisite. Anything the phase adds must be uploadable as files |
| The six service categories must be prominently featured — explicit owner direction, not inferred | CLAUDE.md §Constraints | Reinforces D3-04. No seventh peer item in the card grid |
| Do NOT use jQuery / ScrollMagic / pagePiling | CLAUDE.md §What NOT to Use | The forty photos port as plain `<img>`; no lightbox library, no carousel |
| Do NOT perpetuate copy-paste of shared elements across pages | CLAUDE.md §What NOT to Use | Warranty summary, brand row and rating badge must each be single-sourced |
| Start work through a GSD command; no direct repo edits outside a GSD workflow | CLAUDE.md §GSD Workflow Enforcement | Process constraint on the executor |
| Conventions/Architecture sections are unpopulated | CLAUDE.md | The real conventions live in `02-PATTERNS.md` and in the source comments — treat those as the convention record |

**Superseding project conventions (from `02-PATTERNS.md`, verified against source this session):**
PHP 5.2-safe only — `array()` never `[]`, `dirname(__FILE__)` never `__DIR__`, no closures, no
namespaces, no `??`/`?:`, no `<?=`, no `f()[0]`. Tabs, not spaces. Every value carries a provenance
comment. `htmlspecialchars()` always receives an explicit `'UTF-8'` charset. A page file owns
`<main>` and nothing outside it.

---

## Standard Stack

This phase installs **no packages in any ecosystem**. The site has no `package.json`, no build step,
and no dependency manifest of any kind; `find src -type f` returns CSS, JS, PHP includes, two woff2
subsets and one PNG. Everything below is either already in the tree or is a hand-authored file.

### Core

| Component | Version | Purpose | Why Standard |
|---|---|---|---|
| PHP `include()` templating | 5.2.17 on host | Shared layout + data files | Already the project's whole architecture; proven live [VERIFIED: STATE.md:124-125, live `phpversion()` probe recorded there] |
| `includes/category-page.php` | in-repo | Eight-slot guarded category template | Already renders intro, fixes, warranty, process, FAQ, related, prices, CTA [VERIFIED: src/includes/category-page.php:93-239] |
| Native `<details>` disclosure | HTML | FAQ accordions | Zero JS, content is real HTML in the served response, native find-in-page reveal [VERIFIED: src/includes/category-page.php:170-185] |
| JSON-LD via `json_encode()` | PHP 5.2 | Structured data | Already the pattern; the 5.2 build's mandatory `/` escaping is a deliberate safety property against early `</script>` termination [VERIFIED: src/includes/jsonld.php:16-27] |
| Plain `<img>` with explicit `width`/`height` | HTML | Repair photographs | No library needed; explicit intrinsic size is what prevents CLS |

### Supporting

| Component | Purpose | When to Use |
|---|---|---|
| `schema.org/BreadcrumbList` | The one schema addition that earns a rich result | On every child page and every category page once D3-03's hierarchy exists |
| `schema.org/Service` (+ `hasOfferCatalog`) | Entity understanding only | Emit if cheap; attach **no** verification step to a rich result [VERIFIED: developers.google.com search gallery — no Service feature listed] |
| `schema.org/FAQPage` | Entity understanding only | Same caveat, stronger — the feature is gone (see P-2) |
| `sameAs` → Google Business Profile URL | Entity disambiguation for the LocalBusiness block | Add alongside the TRUST-02 badge; both read one config key |
| `sips` (macOS, built-in) / `cwebp` | Re-encode / strip metadata on the forty ported JPEGs | Build-machine only. Never a host dependency [VERIFIED: `sips-316` and `cwebp` present on this machine] |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|---|---|---|
| Static hardcoded rating badge | Google Places API, build-time fetch | `rating` is a Pro/Enterprise-tier field; a static FTP site has no build step to fetch at, and a client-side key is public. See DH-1 |
| Static hardcoded rating badge | Third-party widget (Elfsight, Trustindex, SociableKit, Shapo) | Third-party JS on 16 pages, a GDPR processor relationship for a Bulgarian firm, and direct conflict with DESIGN-02. Rejected |
| New template slot rendering raw HTML | Structured sub-arrays rendered by the template | Strongly preferred — keeps every string escaped. See Pattern 2 |
| Five child pages as new records in `categories.php` | A separate `includes/services.php` | Separate file preferred: plan 02-02's record-count greps assert exactly six category records |
| Porting the forty JPEGs as-is | Regenerating from originals the owner may hold | Owner-blocked (OWNER-QUESTIONS #13). Port what exists; the slot pattern lets better files drop in later |

**Installation:** none. There is nothing to install for this phase.

---

## Package Legitimacy Audit

**Not applicable — this phase installs no external packages.**

| Package | Registry | Verdict | Disposition |
|---|---|---|---|
| *(none)* | — | — | — |

**Packages removed due to `[SLOP]` verdict:** none.
**Packages flagged as suspicious `[SUS]`:** none.

The only third-party *code* the phase could plausibly acquire is a Google-reviews embed widget, and
D3-07 already forbids it. If a later change reopens that, treat any such widget as an unvetted
third-party script executing on all 16 pages with access to visitor data, and gate it behind
`checkpoint:human-verify`.

---

## Architecture Patterns

### System Architecture Diagram

```
                        ┌─────────────────────────────────────────┐
   VISITOR REQUEST ────►│ Apache on bell.host.bg                  │
   GET /new/xxx.html    │   .htaccess:                            │
                        │    · https+apex canonicalize (1 hop)    │
                        │    · X-Robots-Tag noindex (staging)     │
                        │    · AddHandler …php52 .html .htm  ◄── makes .html run PHP
                        └────────────────┬────────────────────────┘
                                         │
                                         ▼
                        ┌─────────────────────────────────────────┐
                        │ PAGE FILE  (e.g. zalivane-technosti.html)│
                        │  1. $torin_title / $torin_desc   ← SEO-01
                        │  2. $torin_*_page = array( … )    ← body copy lives HERE
                        │  3. require header.php                  │
                        │  4. <main> torin_render_*() </main>     │
                        │  5. require footer.php                  │
                        └───┬──────────────┬──────────────┬───────┘
                            │              │              │
        ┌───────────────────┘              │              └──────────────────┐
        ▼                                  ▼                                 ▼
┌──────────────────┐        ┌────────────────────────────┐      ┌─────────────────────┐
│ header.php       │        │ category-page.php          │      │ footer.php          │
│  · <head>        │        │  torin_render_category_page│      │  · notice band      │
│  · title/desc    │        │   ├ h1  ← cat['name'] ⚠︎    │      │  · contact + hours  │
│    (escaped)     │        │   ├ intro     (esc)        │      │  · 5 secondary links│
│  · 3 stylesheets │        │   ├ fixes     (esc, link)  │      │  · covid.html link  │
│  · nav (6 cats)  │        │   ├ warranty  (esc) ← D3-10│      │  · include jsonld   │
│  · font preload  │        │   ├ process   (esc)        │      └──────────┬──────────┘
└────────┬─────────┘        │   ├ faq       (esc, 1 <p>) │                 │
         │                  │   ├ related   (esc, link)  │                 ▼
         │                  │   ├ prices    (esc) unused │      ┌─────────────────────┐
         │                  │   └ CTA       (from $site) │      │ jsonld.php          │
         │                  └────────────┬───────────────┘      │  LocalBusiness      │
         │                               │                      │  + BreadcrumbList ⬅ NEW
         └──────────────┬────────────────┴──────────────────────┤  + Service (weak)   │
                        ▼                                       │  + FAQPage (weak)   │
              ┌──────────────────────┐    ┌─────────────────┐   └─────────────────────┘
              │ categories.php       │    │ site-config.php │
              │  6 records           │    │  phones, e164   │
              │  name/symptoms/page  │    │  email, address │
              │  published (D-23)    │    │  geo, maps_url  │
              │  torin_category_href │    │  hours, viber   │
              └──────────┬───────────┘    │  notice         │
                         │                │  ⬅ NEW: brands  │
                         ▼                │  ⬅ NEW: rating  │
              ┌──────────────────────┐    │  ⬅ NEW: warranty│
              │ services.php   ⬅ NEW │    │  ⬅ NEW: gbp_url │
              │  5 child records     │    └─────────────────┘
              │  (D3-03 hub/child)   │
              │  same publish gate   │
              └──────────────────────┘

⚠︎ = the h1 collision (see Pattern 1). Every text slot marked (esc) is plain text only.
```

### Recommended Project Structure

```
src/
├── index.html                      # + brand row (TRUST-01), + rating badge (TRUST-02),
│                                   #   + DIFF-02 block, + «Какво можем, което другите отказват»
├── {16 existing .html pages}       # 12 stubs to fill, 3 thin category pages to deepen
├── ekran-klaviatura-portove.html   # NEW — cat-2 hub (~350 words, D3-03)
├── pregryavane-ohlazhdane.html     # NEW — cat-5
├── {5 child pages}                 # NEW — панти/матрица/клавиатура/портове/букса
├── includes/
│   ├── categories.php              # MOD — kat-6 'page' → problem-stari.html (D3-05)
│   ├── services.php                # NEW — the five child records + publish gate
│   ├── category-page.php           # MOD — h1 override + rich-block slot + breadcrumbs
│   ├── site-config.php             # MOD — brands, rating, review_count, gbp_url, warranty
│   ├── jsonld.php                  # MOD — BreadcrumbList emitter
│   ├── header.php                  # UNCHANGED (metadata mechanism already correct)
│   └── footer.php                  # UNCHANGED
└── img/
    └── repairs/                    # NEW — ~40 JPEGs ported from site-current/assets1/img/
```

### Pattern 1: The `<h1>` collision — resolve before authoring

**What:** `torin_render_category_page()` emits exactly one `<h1>`, and its content is fixed:

```php
<h1><?php echo torin_esc($cat['name']); ?></h1>
```
[VERIFIED: src/includes/category-page.php:114]

The name comes from the category record and is deliberately never retyped, so that a D-40 rename
cannot strand a stale heading. The six values are, verbatim
[VERIFIED: src/includes/categories.php:29,42,49,58,73,84]:

> `'Счупвания и механични повреди'` · `'Екран, клавиатура и портове'` · `'Оптимизация'` ·
> `'Заливане и ремонт на дънни платки'` · `'Прегряване и охлаждане'` · `'Нестандартна техника'`

D3-14 requires *"One `<h1>` with primary keyword + «София» where it reads naturally."* None of the
six contains «София»; «Оптимизация» is a bare noun. **The requirement and the mechanism are in
direct conflict and one of them must give.**

**Three resolutions, in preference order:**

1. **Add an optional `h1` key to `$page`, defaulting to `$cat['name']`.** Preserves the guarantee for
   any page that does not set it, gives full keyword control to the pages that do. Costs one guard.
   The drift risk D-24 protected against becomes opt-in and visible in one place per page.
2. **Add an optional `h1_suffix`** (e.g. `' в София'`), concatenated after the record name. Cheaper,
   keeps the record as the stem, but cannot fix «Оптимизация» → «Оптимизация и ускоряване на лаптоп».
3. **Accept the record name as `<h1>` and carry the keyword in the intro paragraph.** Zero template
   change. Weakest for SEO-01, and it means D3-14's first checklist row simply cannot pass.

**Recommendation:** option 1, with the default preserved and a comment stating why the default exists.
Whichever is chosen, the decision must be made *before* any page is authored — it changes what
sixteen `<h1>`s say.

### Pattern 2: Every text slot is plain text — plan for it or add a slot

**What:** all eight slots escape. Verbatim, in slot order
[VERIFIED: src/includes/category-page.php:116,132,148,160,179,194,208]:

```php
<p class="svc__intro"><?php echo torin_esc($page['intro']); ?></p>
<li><?php torin_render_svc_item($torin_fix); ?></li>
<p><?php echo torin_esc($page['warranty']); ?></p>
<li><?php echo torin_esc($torin_step); ?></li>
<div class="disc__body"><p><?php echo torin_esc($torin_qa['a']); ?></p></div>
<li><?php torin_render_svc_item($torin_rel); ?></li>
<li><?php echo torin_esc($torin_price); ?></li>
```

and `torin_render_svc_item()` renders a *whole-item* link only:

```php
if (isset($item['href']) && trim($item['href']) !== '') {
	echo '<a href="' . torin_esc($item['href']) . '">' . torin_esc($item['text']) . '</a>';
	return;
}
echo torin_esc($item['text']);
```
[VERIFIED: src/includes/category-page.php:85-91]

**Consequences the planner must design around:**

- `intro` is **one paragraph**. A two-paragraph intro is impossible without a template change.
- FAQ answers are **one paragraph each**, no list, no link. Several of the phase's intended FAQ
  answers ("is it worth repairing?", "do you have the part?") naturally want a link to a sibling page.
- `process` steps are plain strings inside `<ol>` — no bold on the step name, no inline link.
- **The three highest-value content ideas in `<specifics>` fit none of these slots:** the
  liquid-damage first-aid block (an urgent ordered list that wants emphasis and a «не включвайте»
  warning treatment), the «матрица или видеочип?» diagnostic (a decision block that must link to
  `test-laptop.html` mid-sentence), and the «кога не си струва» honesty block (multi-paragraph prose).

**Recommendation:** add **structured** slots, not a raw-HTML slot. E.g. a `blocks` list whose entries
carry `array('kind' => 'callout'|'steps'|'prose', 'heading' => …, 'items' => …, 'href' => …)`, each
rendered by a `switch` that escapes every leaf. This keeps the project's single most valuable
security property — *there is no unescaped output path anywhere in `src/`* — while unblocking the
content. A `'html'` passthrough slot would be the project's first raw-output sink and its first real
XSS surface; see §Security Domain.

### Pattern 3: Slots that already exist and must not be rebuilt

`category-page.php` already implements roughly what the task list's Workstream 0 asks to be built.
Do **not** create `faq-block.php`, `warranty-block.php` or `cta-block.php`. What is genuinely absent:
a breadcrumbs renderer, the BreadcrumbList emitter, and the two slot changes in Patterns 1–2.

The `warranty` slot for TRUST-03 exists and already guards emptiness
[VERIFIED: src/includes/category-page.php:105,143-151]. D3-10's shared summary is therefore:
one new `site-config.php` key, read into `$page['warranty']` on each category page. `torin_has_content()`
treats a present-but-empty string exactly like an absent key
[VERIFIED: src/includes/category-page.php:65-70], so assigning `''` omits the section rather than
shipping an empty «Гаранция» heading.

### Pattern 4: Brand wordmark row (TRUST-01)

Text wordmarks, not images, per D3-09. The accessible and CLS-safe shape:

```php
<?php // TRUST-01. Brands are a site-wide fact read from one place (OWNER-QUESTIONS #22).
      // The container is deliberately shaped so <img> logos could replace the <li>
      // contents later without a layout change (same slot discipline as D-38). ?>
<section class="section brands" aria-labelledby="brands-h">
	<div class="container">
		<h2 id="brands-h">Обслужваме всички марки</h2>
		<ul class="brand-row">
<?php foreach ($site['brands'] as $torin_brand) { ?>
			<li class="brand-row__item"><?php echo torin_esc($torin_brand); ?></li>
<?php } ?>
			<li class="brand-row__item brand-row__item--more">и др.</li>
		</ul>
		<p class="brand-row__note">Извънгаранционен сервиз. Не сме оторизиран сервиз на нито един производител.</p>
	</div>
</section>
```

The `<p>` is not decoration — it is the wording that converts a bare brand list into unambiguous
referential use (see P-7). Zero images means zero CLS and zero lazy-loading decisions; that is a
direct benefit of D3-09 beyond the trademark one.

### Anti-Patterns to Avoid

- **A raw-HTML slot in the template.** Solves Pattern 2 in five minutes and introduces the project's
  first unescaped sink. Use structured sub-arrays.
- **Retyping the warranty summary, the rating figure or a brand name on any page.** The project's
  operating rule; violating it is how the phone number desynchronised before Phase 2 fixed it.
- **Adding the five child records to `categories.php`.** Plan 02-02's integrity greps count
  single-quoted key literals to assert six records. Use a sibling file.
- **Emitting `AggregateRating`.** Categorically ineligible; see P-1.
- **Planning a verification step that asserts an FAQ or Service rich result appears.** Neither can.
- **Deleting `covid.html` or its content.** See C-1 — the compliant minimum is already met and the
  downside of being wrong is asymmetric.
- **Rewriting the shop's Bulgarian from the task list's English summaries.** The originals are better
  and are the shop's own voice; see §Content Inventory.

---

## Content Inventory — what exists, where, and what must be authored

Every row was read from `site-current/` this session. Line numbers are exact.

| Target page | Source in `site-current/` | Lines | Reusable? | What Phase 3 owes |
|---|---|---|---|---|
| `index.html` (DIFF-03 block) | `index.html` — «Дозапояване на отпоени BGA чипове», «Ребоулинг на BGA чипове», «Подмяна на чипсет с нов» | 595, 615, 633 | **Yes — verbatim-grade** | Condense the three tiers into a homepage «Какво можем, което другите отказват» block; full depth goes on cat-4 |
| `index.html` (DIFF-02 block) | `index.html:656` — «…предлагаме единствено пълна регенрация на старите батерии… в последно време предлагаме и япоски елементи Panasonic» | 654-656 | **Yes** | This is where REQUIREMENTS' "Panasonic-cell" claim comes from. Fix `регенрация`→`регенерация`, `япоски`→`японски` |
| `za-bateriite.html` | `za-bateriite.html` — HILUMIN spot-welding, no-soldering-iron rationale, TI-certified cells, **1-year battery warranty**, 4-factor pricing explainer, Li-ion chemistry | full page, ~1,900 words | **Yes, with edits** | Remove line 158 (SmartBattery). Restructure into DoD shape. Reconcile the 1-year claim with the 1-month service warranty (P-6) |
| `zalivane-technosti.html` (cat-4) | `zalivane-technosti.html` — a **three-step first-aid list already exists in the shop's own words**: unplug the adapter, remove the battery, go to a service centre; plus the "hairdryer/oven" warning | 34-40 | **Yes — this is the first-aid block** | CONTEXT calls the first-aid block "the strongest single idea in the task list." It is already written. Extend it, don't invent it |
| `mehanichni-problemi.html` (cat-1) | `mehanichni-problemi.html` — HDD head-crash on impact, LCD fragility, hinge/lid mechanism, keyboard, **and the jack-escalation argument** ("захранващият жак… започва да искри… резултатът е увреждане на дънната платка") | 7-16 | **Yes** | The «защо не отлагате» escalation argument for the power-jack child page is here verbatim |
| `optimizatsiq.html` (cat-3) | `optimizatsiq.html` — 4 causes: OS bloat, spec limits, failing HDD (with the "your photos are priceless" data-loss angle), board fault | 56-63 | **Yes** | Weakest of the six; needs the most new writing. Add SSD before/after (owner-blocked) |
| `profilaktika-laptop.html` | BGA ball-grid explainer; hot-air-gun damage critique with photos; **the IR reflow machine (German, non-visible IR, know-how)**; **AMTECH flux**; **10 °C lower temps**; **90% durability** | 320, 361, 380, 398, 411 | **Yes — this is DIFF-03's evidence** | CONTEXT credits this to the task list. It is the shop's own copy, in Bulgarian, since ~2019 |
| `test-laptop.html` (DIFF-01) | Four symptom sections: power-on behaviour (4 cases), boot/OS (6 steps incl. Ubuntu-live/MEMTEST/HWMonitor + the 75-80 °C stop rule), audio/WiFi/USB (3), battery/adapter (3) | full page, ~1,400 words | **Yes — genuinely good** | Add routing links from each symptom to the matching service page. Modernise «стартиращо CD/DVD на UBUNTU» → USB stick |
| `tokov-udar.html` | Phase-difference mechanism (TV/HDMI, speakers), surge/lightning via LAN, defective USB devices; concrete prevention advice | 79-90 | **Yes** | Port with light edits; strong internal-link target from cat-4 |
| `problem-stari.html` | StandBy/Charger processor explainer, universal-adapter voltage-switch hazard, deep-discharged battery inrush, counterfeit-battery melt risk | 107-129, ~1,700 words | **Yes — but see P-8** | D3-05 repurposes this URL for category 6. This copy has no other home unless the planner gives it one |
| `warrently.html` | 1-month service warranty; the 5–6 h/day, 150–200 h test-time condition; >90% success; micro-crack rationale; cooling-system correction; free warranty service at the office; voiding conditions | 113-129 | **Yes — port near-verbatim (D3-13)** | Extract the D3-10 shared summary from this; reframe the 5–6 h condition |
| `about.html` | Founded 1993; assembly → distribution 1996; Elitegroup representative for Bulgaria; batteries from 2006; service from the 2008 crisis; B2B + government customers | 431-line page, ~600 words | **Yes** | «Since 1993», the equipment, the engineers — this is the substance CONTEXT wanted. No counter figure exists |
| `uslovia.html` | Privacy declaration naming **both** `torin.bg` and `smartbattery.eu` | 109 | Port with a **careful legal edit** | D3-12: this is a live legal commitment referencing a dead site |
| `covid.html` | Full ОПИК project detail; **project ran 04.08.2020 – 04.11.2020**; contains the «Венера-АКС ООД» copy-paste error | 140, 154-155 | Port near-verbatim | See C-1. Fix or flag the wrong company name |
| `laptopi.html`, `rezervni-chasti.html`, `msg.html` | thin sales/utility pages | — | Port | Blocked in spirit on OWNER-QUESTIONS #15 (is the sales line active?) |

### Photographic assets — present, unported, and small

`site-current/assets1/img/` contains real repair photography that has **never been ported to `src/`**
(`src/img/` holds only `torin-logo.png`) [VERIFIED: `ls src/img/` — single file `torin-logo.png`].

| Set | Files | Dimensions | Total | Subject |
|---|---|---|---|---|
| `profilaktika1-17.jpg` | 17 | mostly 200×200; `profilaktika3` 585×155 | ~540 KB | BGA balls, pad damage, hot-air-gun melt damage, the reflow machine |
| `meh-prob1-9.jpg` | 9 | 181–300 px sq. | ~177 KB | Crashed platters, broken keys, cracked matrices, snapped hinges, torn jacks |
| `tok1-6.jpg` | 6 | 200×200 | ~105 KB | Surge / phase-difference board damage |
| `zalivane1-3.jpg` | 3 | 200–257 px sq. | ~50 KB | Corroded traces, liquid under chipsets |
| `stari1-3.jpg` | 3 | 200–250 px sq. | ~48 KB | StandBy/Charger circuit defects, universal adapters, bad batteries |
| `baterry.jpg`, `baterry2.jpg` | 2 | 554×357, 528×353 | 55 KB | Battery pack block diagram, Li-ion cell structure |
| `ouroffice.jpg` | 1 | 693×424 | 147 KB | Shop premises |

**Two constraints on their use.** They are small — a 200 px image displayed at 200 CSS px is soft on
every 2× phone, so they are an *evidence strip* (≈100 px rendered, 2× effective) or inline thumbnails,
never a hero or a full-width figure. And `src/.htaccess`'s `mod_expires` block declares
`image/png` and `image/svg+xml` but **not `image/jpeg`** [VERIFIED: src/.htaccess, `ExpiresByType`
lines], so ported JPEGs fall to heuristic caching; add `ExpiresByType image/jpeg` in the same edit.

`site-current/covid-19/` holds the ЕС/ЕФРР and ОПИК logos, but two of the three filenames contain
Cyrillic characters and `+` signs (`ЕС+ЕФРР+496х379.png`). **Rename to ASCII on port** — Cyrillic
path segments require percent-encoding and are a known FTP/Apache hazard on shared hosting.

---

## SEO Metadata Architecture (SEO-01)

### The mechanism is already built — do not rebuild it

A page assigns `$torin_title` and `$torin_desc` **before** the header include; `header.php` falls
back to site-level defaults and escapes both [VERIFIED: src/includes/header.php:49-54, 65, 67]:

```php
if (!isset($torin_title)) {
	$torin_title = 'ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ';
}
if (!isset($torin_desc)) {
	$torin_desc = 'ТОРИН КОМПЮТЪРС — ремонт на лаптопи в София: счупвания, екран и клавиатура, оптимизация, заливане и дънни платки, прегряване, нестандартна техника. Безплатна диагностика.';
}
```

All sixteen pages already carry unique working values [VERIFIED: `grep -n '$torin_title|$torin_desc' src/*.html` — 16 distinct titles, 16 distinct descriptions]. **SEO-01 is a tuning pass, not a creation pass**, exactly as CONTEXT states. Three of the sixteen derive their title from the category record
(`$torin_title = $torin_cat['name'] . ' · ТОРИН КОМПЮТЪРС';`) [VERIFIED: src/mehanichni-problemi.html:19,
src/optimizatsiq.html:19, src/zalivane-technosti.html:18] — changing those to literals is what
decouples the title from the h1 and is a prerequisite for keyword-first titles.

### The measured problem: the brand suffix, not the copy

Title pixel widths, measured this session by parsing Arial's `hmtx`/`cmap` tables at 20 px (Google's
approximate desktop SERP title rendering), against the widely cited ~600 px desktop truncation point:

| String | Chars | Width @20px Arial |
|---|---:|---:|
| ` · ТОРИН КОМПЮТЪРС` (current suffix on all 16 pages) | 18 | **228 px** |
| ` · Торин Компютърс` | 18 | 185 px |
| ` · Торин` | 8 | **74 px** |
| `Заливане и ремонт на дънни платки · ТОРИН КОМПЮТЪРС` (current cat-4 title) | 51 | **566 px** |
| `ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ` (current homepage title) | 43 | 549 px |

[VERIFIED: measured locally against `/System/Library/Fonts/Supplemental/Arial.ttf`, upem 2048]

**The all-caps brand suffix consumes 38% of the entire desktop title budget.** Dropping to
` · Торин` frees **154 px ≈ 15 characters of real keyword copy** on every one of sixteen pages, at
the cost of nothing a visitor can perceive.

Per-character averages, same measurement:

| Script | px/char @20px Arial |
|---|---:|
| Latin lowercase | 9.79 |
| **Cyrillic lowercase** | **11.51** (+17.6%) |
| Latin UPPERCASE | 13.55 |
| **Cyrillic UPPERCASE** | **14.42** (+47% vs Latin lowercase) |

On realistic mixed-case sentences the gap narrows to ~13% (BG 9.97 px/char vs EN 8.83 px/char),
because spaces and punctuation dilute it. Implied budgets:

| | Bulgarian | English |
|---|---:|---:|
| Title @600 px | **≈60 chars** | ≈68 chars |
| Description, desktop @920 px | **≈132 chars** | ≈149 chars |
| Description, mobile @680 px | **≈97 chars** | ≈110 chars |

**Verdict on D3-14's limits:** `≤ 60 chars` for the title is *correct for mixed-case Bulgarian* and
*wrong the moment the all-caps suffix is included* — 51 chars already reaches 566 px. `≤ 155 chars`
for the description is too generous: it truncates on desktop at ~132 and on mobile at ~97. Recommend
the phase adopt **title ≤ 55 chars including a short suffix, front-loaded; description 120–140 chars
with the CTA in the first 95** so the call-to-action survives mobile truncation.

### How much of this matters

Google rewrites meta descriptions **62–71% of the time** across independent studies (Portent: 71%
mobile / 68% desktop; Ahrefs: ~63% across 20,000 keywords) [CITED: portent.com, ahrefs.com/blog/meta-description-study]. Titles are rewritten at similar or higher rates, with the lowest rewrite rate
(~40%) observed for titles in the 51–55 character band [CITED: zyppy.com/title-tags]. Two
consequences: (a) the 51–55 char band is an empirically supported target, not a stylistic one;
(b) the description's job is CTR when it *is* used, so budget effort accordingly — this is a
one-pass task, not a page-by-page agonising session.

### Reusable formula for the 16 + 7 pages

```
TITLE       {primary keyword phrase}[ София] · Торин
            ↳ keyword first; «София» only where it reads naturally (D3-14) and fits;
              suffix ` · Торин` = 74 px, leaving ~520 px ≈ 50 chars for the phrase.

DESCRIPTION {what we fix, in customer words} {one differentiator} {CTA}.
            ↳ 120–140 chars; CTA must land inside the first ~95 chars.
              Differentiator vocabulary already earned: «безплатна диагностика»,
              «над 90% успеваемост», «от 1993 г.», «регенерация на батерии»,
              «ремонт на дънни платки на ниво чип».
```

Measured candidates, all comfortably inside 600 px:

| Page | Candidate title | px | Chars |
|---|---|---:|---:|
| cat-1 | `Ремонт на счупен лаптоп София · Торин` | 381 | 37 |
| cat-2 hub | `Екран, клавиатура и портове на лаптоп · Торин` | 442 | 45 |
| cat-3 | `Оптимизация и ускоряване на лаптоп · Торин` | 426 | 42 |
| cat-4 | `Ремонт на залят лаптоп и дънна платка · Торин` | 447 | 45 |
| cat-5 | `Прегряване на лаптоп и профилактика · Торин` | 437 | 43 |
| child: матрица | `Смяна на матрица на лаптоп София · Торин` | 415 | 40 |
| child: клавиатура | `Смяна на клавиатура на лаптоп София · Торин` | 442 | 43 |
| child: букса | `Смяна на захранваща букса на лаптоп · Торин` | 437 | 43 |
| child: портове | `Ремонт на USB и HDMI портове на лаптоп · Торин` | 470 | 46 |
| child: панти | `Смяна на панти на лаптоп София · Торин` | 390 | 38 |
| `za-bateriite` | `Регенерация на батерии за лаптоп · Торин` | 401 | 40 |
| `test-laptop` | `Тествай сам лаптопа си — диагностика · Торин` | 443 | 44 |
| `warrently` | `Гаранционни условия · Торин Компютърс` | 388 | 37 |

[VERIFIED: each measured with the same Arial parser; these are candidates for the planner to refine,
not locked copy]

### Canonical / OG / hreflang decisions

- **`hreflang`: not needed.** One language, no alternates. CLAUDE.md's Bulgarian-only constraint
  makes this a settled non-question. Do not add it.
- **`rel=canonical`: recommended, self-referencing, absolute.** `.htaccess` already canonicalises the
  four protocol/host variants in one hop [VERIFIED: src/.htaccess RewriteCond/RewriteRule block], so
  duplicate-URL exposure is low — but the D3-03 hub/child structure creates near-duplicate topical
  pages, and a self-referencing canonical is the cheapest insurance. **Caution:** it must emit the
  *production* URL, and at cutover the `/new/` path segment disappears. Either derive it from a single
  `site-config.php` base-URL key (one edit at cutover) or defer canonicals to Phase 4 entirely. Do
  **not** hardcode `/new/` into sixteen pages.
- **Open Graph: optional, low value here.** OG tags matter for social sharing; a Sofia repair shop's
  traffic is search and direct. If added, `og:title`/`og:description` should reuse `$torin_title` /
  `$torin_desc` from the same variables — never a third copy of the same fact.
- **Do not add `<meta name="keywords">`.** Ignored by Google for two decades.

---

## Structured Data: what earns rich results in 2026 vs. what is merely valid

This is the section that changes D3-14.

| Type | Valid schema.org? | Earns a Google rich result in 2026? | Verdict for this phase |
|---|---|---|---|
| `LocalBusiness` / `ComputerStore` | Yes | **Yes** — knowledge panel | Already emitted. Keep, extend with `sameAs` → GBP |
| `BreadcrumbList` | Yes | **Yes** — breadcrumb trail in the SERP | **The one addition worth building.** D3-03's hub/child hierarchy makes it real rather than cosmetic |
| `FAQPage` | Yes | **No — feature removed 2026-05-07** | Emit only as an entity signal. **Attach no verification step** |
| `Service` / `OfferCatalog` / `makesOffer` | Yes | **No — no such feature has ever existed** | Same |
| `AggregateRating` on LocalBusiness | Yes | **No — categorically ineligible when self-served** | **Do not emit.** See P-1 |
| `Offer` / `PriceSpecification` | Yes | Not for a LocalBusiness page | v2/PRICE-01 anyway |

**FAQ, precisely.** Google's deprecation notice (8 May 2025) stated *"This feature will no longer
appear in Google Search starting May 7, 2026"*; the documentation page was removed on 15 June 2025
"because the FAQ rich result feature is no longer shown in Google Search results"
[VERIFIED: developers.google.com/search/docs/appearance/structured-data/faqpage]. The August-2023
restriction to "well-known, authoritative government and health websites" is now moot — those sites
lost it too on 7 May 2026 [CITED: searchengineland.com/faq-schema-rise-fall-seo-today-463993,
searchenginejournal.com/google-drops-faq-rich-results-from-search/574429/]. FAQPage remains a valid
schema.org type and Google still parses structured data it does not render; unused structured data
does not harm Search.

**Why still emit FAQPage and Service at all?** Two honest reasons and one dishonest one to reject.
Honest: (a) they are essentially free once the template emits from data that already exists, and
(b) LLM-driven answer surfaces consume schema.org markup independently of Google's rich-result
pipeline. Dishonest and to be rejected: "it might come back." Plan the *verification* around
BreadcrumbList only.

**The native `<details>` FAQ stays regardless.** Its value was never the rich result — it is that
every answer ships as real HTML in the served response, opens with no script, and stays
find-in-page-reachable [VERIFIED: src/includes/category-page.php:167-185]. That value is unchanged
by the deprecation.

---

## Compliance Findings

### C-1: The EU/COVID publicity obligation (CONTENT-02)

**Verified facts.** The project is BG16RFOP002-2.073 under ОПИК 2014-2020, contract
BG16RFOP002-2.073-6307-C01, 10 000 лв (8 500 EU / 1 500 national), beneficiary ТОРИН КЪМПАНИ ООД,
ЕИК 831452399, **начало 04 август 2020 г., край 04 ноември 2020 г.**
[VERIFIED: site-current/covid.html:142,154-155].

**The obligation, from the Managing Authority's own notice for this exact procedure.** Beneficiaries
must (a) display **at minimum one A3 poster with project information at a visible public place**,
e.g. the entrance of the building where the project is implemented — this is the *mandatory* element;
and (b) *if the enterprise maintains an internet page*, include **a short description of the project,
its objectives and results, highlighting the EU financial support**. The obligations are framed as
applying **during project implementation**; the notice specifies no post-completion end date and no
homepage-placement requirement [CITED: opic.bg — "Информация за задължението на бенефициентите за
осигуряване на информираност и публичност … по процедура BG16RFOP002-2.073"]. The general framework
is Reg. (EU) 1303/2013 Art. 115 + Annex XII, whose beneficiary duties are likewise tied to the
implementation period; Art. 71 durability periods (5 years, 3 for SME job/investment maintenance)
attach to infrastructure and productive investment, **not** to a working-capital grant
[CITED: eur-lex.europa.eu CELEX:32013R1303].

**Conclusion for the planner, stated plainly.**

1. **Removing the content from the homepage carries no compliance risk** — homepage placement was
   never required. CONTENT-02's literal wording is therefore already satisfied in `src/`: the Phase 2
   homepage contains no EU/COVID block at all, and `footer.php:111` carries a single link
   `<a href="covid.html">Проект BG16RFOP002-2.073</a>` [VERIFIED: src/includes/footer.php:111].
2. **The compliant minimum is "a description on the website, if there is a website."** A live
   `covid.html` reachable from a footer link exceeds that. Keep it. Do not retire it, do not 404 it,
   and do not gate it behind anything (D-35/D-36 already say this; this research supplies the
   citation D-35 hedged on).
3. **What remains for CONTENT-02 is purely editorial:** REQUIREMENTS words it as "moved to About
   page." The About page is currently a stub. Whether the About page gains a compact «Европейски
   проекти» block linking to `covid.html`, or nothing at all, is CONTEXT's stated Claude's-discretion
   item. Either passes; a compact block is marginally better because it makes the disclosure reachable
   from a content page rather than only from the legal line.
4. **Flag to the owner, not to fix silently:** `covid.html:140` names **«Венера-АКС ООД»** in the
   results paragraph — a different company. A port must either correct it to ТОРИН КЪМПАНИ ООД or
   leave it and flag it; silently rewriting a sentence in a funding disclosure is the one place in
   this phase where "improving while porting" (D3-13) should stop and ask.

### C-2: `uslovia.html` names a domain that no longer exists

`site-current/uslovia.html:109` reads, verbatim: *«Чрез използването на сайтoвете www.torin.bg и
www.smartbattery.eu и формите им за въпроси, Вие се съгласявате със събирането, обработването,
използването на Вашите лични данни…»* [VERIFIED: site-current/uslovia.html:109]. This is a GDPR
privacy declaration extending consent-and-processing commitments to a site that is gone. It is not a
dead link to delete — it is a live legal statement to narrow. The minimal correct edit removes the
second domain and its conjunction, leaving the declaration scoped to `www.torin.bg`. Note also that
the declaration says data *"ще бъдат изтрити от нашите регистри след като удовлетворим Вашите
изисквания"* — a retention commitment the Phase 4 contact-form work (CONTACT-03) must not contradict.

---

## Runtime State Inventory

*Included because D3-12 is a string-removal operation across the tree and D3-05 is a URL repurpose —
both have state that a file-level grep does not reach.*

| Category | Items Found | Action Required |
|---|---|---|
| **Stored data** | None — the site has no database, no session store and no datastore of any kind. Verified by absence: `find src -type f` returns only CSS/JS/PHP/fonts/one PNG, and `mailer.php` (the only stateful endpoint) lives in `site-current/` and is Phase 4's concern | none |
| **Live service config** | **Google Business Profile** holds the shop's website URL, NAP and hours *outside* this repo. The TRUST-02 badge links to it and `sameAs` will point at it; if GBP's NAP disagrees with `site-config.php` the entity signal is weakened. GBP's website URL still points at the pre-redesign root — a Phase 4 cutover item already deferred | Phase 4; record NAP parity as a Phase 3 check |
| **OS-registered state** | None. No cron, no scheduled task, no process manager — deploy is FTP file placement | none |
| **Secrets / env vars** | None in scope. FTP credentials live in `filezilla-server-data.xml` (gitignored). Phase 3 introduces no key of any kind — this is a direct benefit of D3-07's no-API decision | none |
| **Build artifacts** | None — zero build tooling. But note the **asset cache**: `header.php` version-stamps CSS/JS via `?v=<filemtime>` while `.htaccess` caps staging CSS/JS at 5 minutes; **ported JPEGs match no `ExpiresByType` rule** and fall to heuristic caching | Add `ExpiresByType image/jpeg` when the photos land |
| **Cross-file string state (D3-12)** | Four `smartbattery` references, all in `site-current/`, **none in `src/`** — confirmed by `grep -rni smartbattery src/` returning nothing. So D3-12 is a *do-not-import* rule, not a removal task | Guard the port; add a grep gate |

**The D3-05 repurpose has one piece of unstated state:** `src/includes/categories.php:87` currently
reads `'page' => 'nestandartna-technika.html'` for kat-6 [VERIFIED: src/includes/categories.php:87].
D3-05 puts category 6 on `problem-stari.html`. That record must change, or the nav and card grid will
route to a file that will never exist. It is one line, and it is easy to miss because the category is
`published => false` and therefore currently routes to `index.html#kat-6` via `torin_category_href()`
[VERIFIED: src/includes/categories.php:101-106] — meaning **the wrong value is invisible until the
moment the page is published.**

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---|---|---|---|
| **DH-1** Live Google rating | Places API integration, build-time fetcher, or a scraper | Hardcoded `rating` + `review_count` + `gbp_url` in `site-config.php` (D3-07) | `rating` is a Pro/Enterprise-tier Place Details field — adding it moves a call out of the Essentials SKU and into paid tiers [CITED: developers.google.com/maps/documentation/places/web-service/usage-and-billing]. There is no build step to fetch at, and a client-side key on a static page is public and abusable. A scraper breaks silently and violates ToS |
| **DH-2** Reviews display | A third-party reviews widget (Elfsight / Trustindex / SociableKit / Shapo) | Nothing, this phase | Third-party JS on 16 pages against DESIGN-02; creates a GDPR processor relationship; and the widget's own `AggregateRating` would be self-serving anyway (P-1) |
| **DH-3** FAQ accordion | A JS accordion, or the task list's proposed `faq-block.php` | The existing `<details name="svc-faq">` in `category-page.php` | Already built, already indexable, zero JS [VERIFIED: src/includes/category-page.php:170-185] |
| **DH-4** CTA / warranty / breadcrumb partials | `cta-block.php`, `warranty-block.php` per the task list | Existing template slots + one new breadcrumbs renderer | Task list was written without sight of Phase 2's output; CONTEXT says so explicitly |
| **DH-5** Escaping | A "safe HTML" allowlist filter to solve Pattern 2 | Structured sub-arrays with per-leaf `torin_esc()` | Writing an HTML sanitiser on PHP 5.2 with Cyrillic input is a genuinely hard problem with a long CVE history. Don't |
| **DH-6** Brand logo assets | Sourcing/converting 8 manufacturer SVGs | Text wordmarks (D3-09) | Sidesteps asset sourcing, licensing, CLS, lazy-loading and the trademark question in one decision |
| **DH-7** Image optimisation pipeline | A Node/Sharp build step | `sips` or `cwebp` invoked once, by hand, on ~40 files | Forty files is a one-off. A build step contradicts the project's out-of-scope list |

**Key insight:** every hand-rolled solution this phase might reach for exists to fetch, render or
sanitise data that *nobody has asked for dynamically*. The site's content changes a few times a year.
Hardcoding a rating and editing it when it moves is not a compromise here — it is the correct
engineering answer for the actual change velocity, and it is the one that keeps zero secrets on a
shared host.

---

## Common Pitfalls

### P-1: Emitting `AggregateRating` for the shop's own Google reviews
**What goes wrong:** The rating never shows as stars; at worst it draws a structured-data manual action.
**Why it happens:** The markup validates, so every tool says "valid" — the ineligibility is a *policy*,
not a syntax rule.
**The actual rule, verbatim:** *"If the entity that's being reviewed controls the reviews about itself,
their pages that use `LocalBusiness` or any other type of `Organization` structured data are ineligible
for star review feature."* [VERIFIED: developers.google.com/search/docs/appearance/structured-data/review-snippet]
**How to avoid:** Do not emit `aggregateRating` or `review` on `jsonld.php`'s LocalBusiness block, ever.
**Correction to D3-07:** its stated reason — *"since no reviews are visible on-page"* — is narrower
than the rule. Reviews being visible would not cure it. This matters **now**, because it means v2's
REVIEWS-01 plan to "add AggregateRating schema" once quotes are on-page is also invalid, and someone
should learn that before building it. `Product` schema is the documented exception, and a repair shop
has no products.
**Warning sign:** anyone reporting "Rich Results Test says it's valid" as evidence that it will work.

### P-2: Planning verification against an FAQ rich result
**What goes wrong:** A Phase 3 or Phase 4 verification step asserts FAQ accordions appear in the SERP.
It cannot pass, and the executor burns a repair budget chasing correct markup.
**Why it happens:** D3-14's DoD names FAQPage schema, and every SEO article written before mid-2026
treats it as a win.
**How to avoid:** Emit if convenient; verify only that the JSON-LD parses. Verify BreadcrumbList
separately — that one can actually appear.
**Warning sign:** the word "rich result" next to "FAQ" anywhere in a plan.

### P-3: The `<h1>` / D3-14 collision (Pattern 1)
**What goes wrong:** Sixteen pages are authored, then someone notices no category `<h1>` contains a
keyword or «София», and the fix touches the template plus every category page at once.
**Why it happens:** The template's h1-from-record rule is *correct* and was chosen deliberately; the
DoD row was written without reference to it.
**How to avoid:** Decide the resolution in the first plan of the phase, before any page is authored.
**Warning sign:** a plan that authors category-page copy without having touched `category-page.php`.

### P-4: Assigning `''` to a slot and expecting an empty section
**What goes wrong:** Nothing visible — which is the point — but a planner who *wants* a placeholder
heading will not get one.
**Why:** `torin_has_content()` returns false for both an absent key and a present-but-empty string
[VERIFIED: src/includes/category-page.php:65-70]. This is deliberate anti-thin-content design.
**How to avoid:** Understand that "ship the section empty for now" is impossible by construction. Good.

### P-5: A category page whose `cat_id` does not resolve renders an empty `<main>`
**What goes wrong:** `torin_render_category_page()` opens with `if ($cat === null) { return; }`
[VERIFIED: src/includes/category-page.php:96-99]. A typo'd id, or a **child page reusing the category
renderer**, produces a page with a full header, a full footer, and literally nothing between them —
which still returns HTTP 200 and is exactly the thin-content shape D-23 exists to prevent.
**Why it matters now:** D3-03 introduces five child pages that are *not* categories. If they reach for
`torin_render_category_page()` they will silently render nothing.
**How to avoid:** Give children their own renderer (or a non-category mode with its own guard), and add
a build-time check that every published page emits at least one `<h2>`.
**Warning sign:** a plan reusing `torin_render_category_page()` for a non-category page.

### P-6: The warranty numbers contradict each other across pages
**What goes wrong:** TRUST-03's shared summary says one thing; `za-bateriite.html` says another; a
customer quotes whichever is better.
**The two verbatim claims:** *«Гаранционният срок за всички сервизни дейности и услуги, предлагани от
фирма „Торин Къмпани” ООД е 1 месец»* [VERIFIED: site-current/warrently.html:125] versus
*«Това ни дава свобода да поддържаме 1 година гаранция, която е по-голяма дори от батериите на
новопродадените лаптопи»* [VERIFIED: site-current/za-bateriite.html:129].
**Why it happens:** They are not necessarily inconsistent — one is a *service* warranty, the other a
*product* warranty on regenerated batteries. But the site never says so, and D3-10's single shared
block will be rendered on a page that also claims a year.
**How to avoid:** State the distinction explicitly in the shared summary, and make it an OWNER-QUESTIONS
item (#23 already asks whether terms vary by service type — this is the concrete instance).

### P-7: Naming manufacturer brands without the disclaimer that makes it referential use
**What goes wrong:** A bare list of eight brand names on a repair site reads, to a trademark holder,
as an implied-authorisation claim.
**The law:** Art. 14(1)(c) EUTMR permits a third party to use an EU trade mark *"for the purpose of
identifying or referring to goods or services as those of the proprietor… in particular where the use
of that trade mark is necessary to indicate the intended purpose of a product or service"*, **provided
the use accords with honest practices in industrial or commercial matters**. There is no separate
"right to repair" defence; the CJEU read the limitation strictly in *Audi v GQ* (C-334/22, Jan 2024)
[CITED: intellectual-property-helpdesk.ec.europa.eu; fieldfisher.com; cms.law].
**How to avoid:** The honest-practices condition is satisfied cheaply by wording, and D3-09's competitor
evidence shows the market's own answer: several competitors position explicitly as «извънгаранционен
сервиз». Ship the brand row with a sentence that (a) states the shop is an independent, out-of-warranty
service and (b) disclaims authorisation. **This is not optional garnish — it is the element that makes
the row defensible.** Text wordmarks are materially safer than logos: a figurative mark reproduced
verbatim carries an origin-indication risk that a plain word does not.
**Warning sign:** a brand row shipped without the disclaimer sentence because it "looked cleaner."

### P-8: The D3-05 repurpose silently destroys ~1,700 good words
**What goes wrong:** `problem-stari.html` becomes the Нестандартна техника page, and its current
content — the StandBy/Charger processor explainer, the universal-adapter voltage-switch hazard, the
deep-discharge inrush mechanism, the counterfeit-battery melt risk — has nowhere to go.
**Why it happens:** CONTEXT's justification for D3-05 is the *slug* («стари») and the category's symptom
line. That reasoning is sound for the URL. But the page's actual **subject is board-level power
circuitry**, which maps to category 4 or to `tokov-udar.html`, and not at all to "non-standard
equipment." CONTEXT's summary — that the page "duplicates `za-bateriite.html` verbatim" — is true of
**one closing sentence** (the SmartBattery pointer, `problem-stari.html:181` ≡ `za-bateriite.html:158`),
not of the body [VERIFIED: both lines read, both are the same single sentence].
**How to avoid:** Decide the destination for this copy in the same plan that repurposes the URL. The
strongest home is cat-4's «захранващи вериги» depth or a section of the power-jack child page — both
of which the phase is writing anyway.
**Warning sign:** a plan that repurposes `problem-stari.html` and mentions its existing content only as
"replaced."

### P-9: Porting `site-current/` HTML rather than its text
**What goes wrong:** Bootstrap-4 utility classes, `<i class="fa …">` icons with no icon font loaded,
inline `style` attributes and 604 KB of legacy CSS assumptions leak into the new tree.
**Why it happens:** "Port near-verbatim" (D3-13) is about *copy*, and it is easy to read as *markup*.
**How to avoid:** Port the *strings*, into PHP arrays, rendered by the template. The legacy markup is
the thing Phase 2 removed.
**Warning sign:** any `class="font-size-`, `class="mb-`, `class="col-md-` or `<i class="fa` in `src/`.

### P-10: No local PHP interpreter — syntax errors surface only on the live host
**What goes wrong:** A stray quote in a Cyrillic string in a 60-line PHP array takes the whole staging
subtree to a 500, discovered by FTP round-trip.
**The fact:** `command -v php` returns nothing on this build machine [VERIFIED: probed this session];
Phase 2 worked around it with regex-based lint greps.
**How to avoid:** Keep the regex lint gates, add a quote-balance check for the new data files, and
deploy the new data file **before** the pages that consume it so a failure is isolated to one URL.
Node 20.18.0 is available if a checker needs a scripting host.

### P-11: The `notice` band and the hours contradict a page the phase is porting
**What goes wrong:** `site-config.php['notice']` and `['hours']` both say 8:00–16:00 (marked
`[ASSUMED]`, OWNER-QUESTIONS #20) while `site-current/profilaktika-laptop.html` says 9:00–17:00, and
`profilaktika-laptop` is one of the pages D3-13 rewrites.
**How to avoid:** Strip the hours sentence from the ported body entirely — hours are a footer fact with
one writer. Do not port a second copy of a fact `site-config.php` already owns.

---

## Code Examples

### E-1: BreadcrumbList emitted from data, PHP 5.2-safe

```php
<?php
// includes/jsonld.php (addition) — BreadcrumbList (D3-03 hierarchy).
// The ONLY schema addition this phase that earns a Google rich result.
// Same encoding rules as the LocalBusiness block above: json_encode() only,
// never hand-written JSON; this 5.2 build escapes "/" and non-ASCII, which is
// valid JSON and is also what stops a literal closing script tag inside any
// string from terminating the block early.
//
// $torin_crumbs is a list of array('name' => …, 'url' => …), assigned by the
// page BEFORE footer.php is required. Absent => no block, exactly like the
// template's optional slots.
if (isset($torin_crumbs) && count($torin_crumbs) > 0) {
	$torin_crumb_items = array();
	$torin_pos = 1;
	foreach ($torin_crumbs as $torin_crumb) {
		$torin_crumb_items[] = array(
			'@type'    => 'ListItem',
			'position' => $torin_pos,
			'name'     => $torin_crumb['name'],
			'item'     => $torin_crumb['url']
		);
		$torin_pos = $torin_pos + 1;
	}
	$torin_bc = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $torin_crumb_items
	);
?>
<script type="application/ld+json">
<?php echo json_encode($torin_bc); ?>
</script>
<?php
}
?>
```

The `item` URLs must be absolute for Google to resolve them, which reopens the `/new/` base-URL
question from §SEO Metadata Architecture — resolve both with one `site-config.php` key.

### E-2: A structured rich block that keeps every leaf escaped (Pattern 2)

```php
<?php
// includes/category-page.php (addition). Solves the liquid-damage first-aid
// block, the «матрица или видеочип?» diagnostic and the «кога не си струва»
// honesty block WITHOUT introducing a raw-HTML sink. Every leaf still goes
// through torin_esc(); the structure, not the string, carries the formatting.
//
// $page['blocks'] entries:
//   kind    'callout' | 'steps' | 'prose'
//   heading string
//   tone    optional, 'urgent' — styling hook only, never interpolated raw
//   items   list of strings (steps/prose) — each its own <li> or <p>
//   link    optional array('text' => …, 'href' => …)
if (isset($page['blocks']) && torin_has_content($page['blocks'])) {
	foreach ($page['blocks'] as $torin_block) {
		$torin_tone = (isset($torin_block['tone']) && $torin_block['tone'] === 'urgent') ? ' svc__block--urgent' : '';
?>
	<section class="section svc__block<?php echo $torin_tone; ?>">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2><?php echo torin_esc($torin_block['heading']); ?></h2>
<?php		if ($torin_block['kind'] === 'steps') { ?>
			<ol>
<?php			foreach ($torin_block['items'] as $torin_it) { ?>
				<li><?php echo torin_esc($torin_it); ?></li>
<?php			} ?>
			</ol>
<?php		} else { ?>
<?php			foreach ($torin_block['items'] as $torin_it) { ?>
			<p><?php echo torin_esc($torin_it); ?></p>
<?php			} ?>
<?php		} ?>
<?php		if (isset($torin_block['link'])) { ?>
			<p><a href="<?php echo torin_esc($torin_block['link']['href']); ?>"><?php echo torin_esc($torin_block['link']['text']); ?></a></p>
<?php		} ?>
		</div>
	</section>
<?php
	}
}
?>
```

Usage, with the first-aid copy already written by the shop
[source: site-current/zalivane-technosti.html:34-40]:

```php
'blocks' => array(
	array(
		'kind'    => 'steps',
		'tone'    => 'urgent',
		'heading' => 'Ако сте залели лаптопа — направете това веднага',
		'items'   => array(
			'Изключете адаптера от лаптопа веднага.',
			'Извадете батерията и не я връщайте обратно.',
			'Не включвайте лаптопа, за да „проверите дали работи“.',
			'Не сушете със сешоар и не поставяйте лаптопа във фурна.',
			'Обадете се в сервиз възможно най-бързо — корозията започва веднага.',
		),
		'link'    => array('text' => 'Обадете се сега', 'href' => 'index.html#contact-us'),
	),
),
```

### E-3: New `site-config.php` keys, following the file's provenance convention

```php
	// TRUST-01. [ASSUMED] OWNER-QUESTIONS #22. The roadmap's list came from
	// requirements drafting, NOT from the owner. Naming a brand the shop does
	// not service is a promise it cannot keep. Six to eight entries; the row
	// closes with «и др.» rendered by the template, never stored here.
	'brands' => array('Lenovo', 'HP', 'Dell', 'Asus', 'Acer', 'Apple', 'MSI'),

	// TRUST-02. [ASSUMED] OWNER-QUESTIONS #7. Aggregators reported 128-146
	// reviews across crawl dates, which settles that the profile exists and is
	// healthy; it does NOT settle the number. Pull rating and count from GBP
	// itself before this ships — a wrong figure here is wrong on every page.
	// D3-07: static badge only. No Places API, no widget, and NO
	// aggregateRating in jsonld.php: Google rules a business marking up its own
	// reviews ineligible for the star feature under LocalBusiness/Organization,
	// whether or not the reviews are visible on the page.
	'gbp_rating'  => '4.8',
	'gbp_reviews' => '128',
	'gbp_url'     => 'https://www.google.com/maps/place/?q=place_id:REPLACE_ME',

	// TRUST-03 / D3-10. ONE shared summary, rendered into the category
	// template's existing `warranty` slot on every category page and never
	// retyped. Sourced from site-current/warrently.html:113-127. The 5-6 h/day
	// condition is reframed as a statement of confidence in the repair, which
	// is what it is; it is NOT dropped, because it is a term the shop operates
	// under. NOTE the unresolved conflict with the 1-year battery warranty at
	// site-current/za-bateriite.html:129 — OWNER-QUESTIONS #23.
	'warranty_summary' => '1 месец гаранция на всеки ремонт, безплатно гаранционно обслужване в сервиза. Съветваме ви да ползвате лаптопа активно през този месец — така сме сигурни, че ремонтът държи при реална употреба.',
```

### E-4: The static rating badge (TRUST-02)

```php
<?php // TRUST-02. Static badge, D3-07. Links out to the Google Business
      // Profile — rel="noopener" because target-blank-less external links can
      // still be navigated by scripts on some engines, and the habit costs
      // nothing. NO aggregateRating JSON-LD accompanies this: self-served
      // ratings under LocalBusiness/Organization are categorically ineligible
      // for the star feature. The link itself is the honest signal. ?>
<a class="rating-badge" href="<?php echo torin_esc($site['gbp_url']); ?>" rel="noopener">
	<span class="rating-badge__score"><?php echo torin_esc($site['gbp_rating']); ?></span>
	<span class="rating-badge__label">от <?php echo torin_esc($site['gbp_reviews']); ?> отзива в Google</span>
</a>
```

And in `jsonld.php`'s `$torin_ld`, the one *permitted* GBP signal:

```php
	// Entity disambiguation, not a rating. sameAs pointing at the shop's own
	// Google Business Profile helps Google tie this markup to that entity.
	'sameAs' => array($site['gbp_url']),
```

### E-5: kat-6 record change for D3-05

```php
	array(
		// D3-05: category 6 lands on the EXISTING indexed URL problem-stari.html
		// rather than a new slug — it inherits whatever authority that page holds,
		// needs no new file, and retires nothing (so no redirect, no ranking risk).
		// The old value here was 'nestandartna-technika.html', a file that will
		// now never exist. That mistake would have been INVISIBLE until the day
		// this record's `published` flipped, because torin_category_href() routes
		// unpublished categories to index.html#kat-6 instead.
		'id'        => 'kat-6',
		'name'      => 'Нестандартна техника',
		'symptoms'  => 'нестандартна или стара техника, която другаде не приемат', // [ASSUMED] #16
		'page'      => 'problem-stari.html',
		'icon'      => 'cat-6',
		'published' => false,   // D-23 gate; OWNER-QUESTIONS #3 is launch-blocking
	),
```

---

## State of the Art

| Old approach | Current approach | When changed | Impact on this phase |
|---|---|---|---|
| `FAQPage` markup earns an FAQ rich result | It earns nothing; feature removed for all site categories | 2026-05-07 (announced 2025-05-08; docs deleted 2025-06-15) | D3-14's schema row is one-third obsolete. Emit for entity understanding only |
| FAQ restricted to authoritative government/health sites | Restriction moot — those sites lost it too | 2026-05-07 | Removes the "we're not a .gov so never mind" reasoning entirely |
| Mark up your Google reviews for stars | Self-serving reviews ineligible under LocalBusiness/Organization | Sept 2019, still enforced | Invalidates v2/REVIEWS-01's AggregateRating plan, not just this phase's |
| "Write a good meta description and Google will use it" | Google rewrites 62–71% of descriptions | ongoing, measured 2020→2025 | Treat SEO-01 as a single tuning pass, not a per-page agony |
| "Title ≤ 60 characters" | Pixel budget ~600 px; 51–55 chars is the empirically lowest-rewrite band | ongoing | For Bulgarian, chars are ~13% wider and the all-caps brand suffix eats 38% of budget |
| `<meta name="keywords">` | Ignored | ~2009 | Do not add |
| Google Maps `<iframe>` embed for location | Deep link + `geo`/`hasMap` in JSON-LD | Phase 2 (D-34), for DESIGN-02 | Already done; do not reintroduce an embed with the photos |

**Deprecated / outdated in the source copy being ported:**
- *«Със стартиращо CD/DVD на UBUNTU»* (`site-current/test-laptop.html`) — bootable USB now.
- *«www.SmartBattery.eu»* — domain dead (D3-12).
- *«TORIN Company Ltd. © 2019 г.»* — `footer.php` already uses `date("Y")`.
- Contact details and hours embedded in body copy across several pages — `site-config.php` owns these.

---

## Assumptions Log

| # | Claim | Section | Risk if wrong |
|---|---|---|---|
| A1 | Google's desktop SERP title link renders at approximately Arial 20 px and truncates near 600 px | SEO Metadata Architecture | Absolute px figures shift; the **relative** findings (caps suffix = 38% of budget; Cyrillic 13–18% wider than Latin) hold regardless of the exact font/size, because they are ratios from one measurement |
| A2 | Meta-description pixel budgets of ~920 px desktop / ~680 px mobile | SEO Metadata Architecture | Derived from secondary SEO sources, not Google documentation. Affects the recommended 120–140 char band by perhaps ±10 chars |
| A3 | The ОПИК publicity obligation for BG16RFOP002-2.073 ended with the implementation period (04.11.2020) and imposes no ongoing website duty | C-1 | The recommendation is unaffected: keep `covid.html` live regardless. Only the *confidence* with which the owner could retire it later depends on this |
| A4 | The 1-month service warranty and the 1-year battery warranty are two different warranties, not a contradiction | P-6 | If actually a contradiction, the shared TRUST-03 summary would state a term the shop does not honour. OWNER-QUESTIONS #23 covers it |
| A5 | The five D3-03 child pages will not reuse `torin_render_category_page()` | P-5 | If they do and nobody adds a guard, they render an empty `<main>` at HTTP 200 |
| A6 | Displaying six to eight brand *names* (not logos) with an explicit independent-service disclaimer falls within Art. 14(1)(c) EUTMR referential use | P-7 | This is a legal-risk assessment from secondary sources, not legal advice. The mitigation (the disclaimer sentence) is cheap and is what the competitor set already does |
| A7 | The ~40 ported JPEGs are acceptable at ~100 CSS px (2× effective) as an evidence strip | Content Inventory | If the design wants larger figures they will look soft; the slot pattern lets better files replace them without layout change |
| A8 | `site-current/` is a faithful mirror of what is live at `torin.bg` today | throughout | Ported copy could differ from what visitors currently see. Low risk — Phase 1 captured this inventory deliberately |
| A9 | The `sameAs` → GBP link is a useful and permitted entity signal on LocalBusiness | E-4 | Google's LocalBusiness docs do not enumerate `sameAs`; it is valid schema.org and standard practice. Worst case it is ignored |

---

## Open Questions

### OQ-1: Which brands does the shop actually service? *(→ OWNER-QUESTIONS #22, OPEN)*
- **What we know:** the roadmap's list (Lenovo, HP, Dell, Asus, Acer, Apple, MSI) came from
  requirements drafting, not the owner. Competitors list 1–80+ brands; zero of eight use logos.
  `site-current/about.html` says only *«ремонт на всички марки преносими компютри»*.
- **What's unclear:** the real list, and Apple/MacBook specifically (different parts and tooling).
- **Recommended default:** ship the roadmap's seven, marked `[ASSUMED]` in `site-config.php`, with the
  «и др.» closer and the independent-service disclaimer. Every one is a mainstream PC brand a Sofia
  repair shop would service; Apple is the single riskiest and is the one to ask about first.

### OQ-2: The live GBP rating, review count, and profile URL *(→ OWNER-QUESTIONS #7, OPEN)*
- **What we know:** aggregators report 128–146 reviews across crawl dates — enough to settle that the
  profile exists and is healthy.
- **What's unclear:** the exact rating, the exact count, and the canonical profile URL for the badge
  and for `sameAs`.
- **Recommended default:** author the badge component and the config keys with `[ASSUMED]` values, but
  **gate the badge's rendering on a non-empty `gbp_url`** so it cannot ship pointing at a placeholder.
  That converts an unanswered question from a wrong-number risk into an absent-component outcome.

### OQ-3: Do warranty terms vary by repair type? *(→ OWNER-QUESTIONS #23, OPEN)*
- **What we know:** `warrently.html:125` states one month *«за всички сервизни дейности и услуги»*;
  `za-bateriite.html:129` claims one year on regenerated batteries.
- **What's unclear:** whether the year is a distinct product warranty (likely) or stale copy.
- **Recommended default:** shared summary states one month for repairs and names the battery exception
  in one clause. Both facts already exist on the site; the site simply never reconciled them.

### OQ-4: Where does `problem-stari.html`'s existing 1,700 words go? *(new — planner decision)*
- **What we know:** the copy is board-level power-circuit material of good quality (P-8).
- **What's unclear:** whether the owner considers it current.
- **Recommended default:** fold into cat-4's «захранващи вериги» depth and the power-jack child page.
  Do not discard it as a side effect of a URL decision.

### OQ-5: The canonical base URL, given `/new/` disappears at cutover *(new — planner decision)*
- **What we know:** BreadcrumbList `item` URLs and any `rel=canonical` must be absolute; the site is
  currently served from `/new/`.
- **Recommended default:** one `site-config.php` base-URL key, `[ASSUMED]`-marked, changed once at
  cutover. Alternatively defer both canonicals and absolute breadcrumb URLs to Phase 4. Do **not**
  hardcode `/new/` into 23 pages.

### OQ-6: The `«Венера-АКС ООД»` error in the funding disclosure *(→ extends OWNER-QUESTIONS #4)*
- **What we know:** `covid.html:140` names a different company in the results paragraph.
- **What's unclear:** whether the error also exists in the submitted project documentation.
- **Recommended default:** flag to the owner; do not silently rewrite a funding disclosure. This is the
  one place D3-13's "improve while porting" should stop.

### OQ-7: Is the sales line still active? *(→ OWNER-QUESTIONS #15, OPEN)*
- **Impact on this phase:** `laptopi.html` and `rezervni-chasti.html` are two of the sixteen pages
  needing content and metadata. If the line is dormant, both are near-empty ports and their metadata
  should not chase commercial keywords.
- **Recommended default:** port thin, honest, call-to-confirm-availability copy. Cheap either way.

---

## Environment Availability

| Dependency | Required by | Available | Version | Fallback |
|---|---|---|---|---|
| PHP (host) | Everything | ✓ | 5.2.17 on `bell.host.bg` | — |
| PHP (build machine) | Local syntax checking | **✗** | — | Regex lint gates (Phase 2 precedent) + deploy data files before consumers. See P-10 |
| Node.js | Optional local scripting/lint | ✓ | 20.18.0 | — |
| npm | not needed | ✓ | 11.0.0 | — |
| Python 3 | Local text/asset scripting | ✓ | 3.13.5 | — |
| curl (FTPS) | Deploy to staging | ✓ | 8.7.1 | Must cap the data channel at TLS 1.2 (STATE.md: TLS 1.3 silently fails uploads >16 KB) |
| `sips` | Re-encode / strip metadata on ported JPEGs | ✓ | sips-316 | — |
| `cwebp` | Optional WebP variants | ✓ | present | Skip — `<picture>` adds markup complexity for ~40 small files |
| ImageMagick / jpegoptim / optipng | Image optimisation | ✗ | — | `sips` covers it |
| Automatable browser (Chrome/Playwright) | Rendered visual checks | **✗** on the build machine per STATE.md | — | ⚠ Project memory records that **Brave + CDP is available** and that Phase 2's "no automatable browser" claim is wrong. Re-probe before accepting the blocker |
| Google Search Console | Verify metadata / crawl | ✗ | — | OWNER-QUESTIONS #1, blocking; Phase 4 concern |
| Google Places API | Live rating | n/a — deliberately not used | — | Static badge (D3-07) |

**Missing dependencies with no fallback:** Search Console access (does not block Phase 3 delivery,
only its measurement).
**Missing dependencies with fallback:** local PHP (regex lint + staged deploy); browser automation
(re-probe Brave/CDP before treating as blocked).

---

## Security Domain

`security_enforcement: true`, ASVS level 1. This phase authors content on a site with no
authentication, no session, no database and no user input — so most ASVS categories are genuinely
not applicable, and saying so explicitly is more useful than inventing controls.

### Applicable ASVS Categories

| ASVS category | Applies | Standard control |
|---|---|---|
| V2 Authentication | No | No login exists anywhere in `src/` |
| V3 Session Management | No | No session is started; no cookie is set except the dev-only theme switcher, which is deleted at cutover |
| V4 Access Control | No | Every page is public by design |
| V5 Validation, Sanitisation & Encoding | **Yes** | `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')` via `torin_esc()` on every leaf; `rawurlencode()` for URL query components. Already the project's universal pattern |
| V6 Cryptography | No | Nothing is encrypted or hashed by this code |
| V7 Error Handling & Logging | Marginal | An unresolved `cat_id` returns silently (P-5) — a correctness issue, not a security one |
| V12 Files & Resources | Marginal | ~40 static images placed by FTP; no upload path exists until Phase 4's CONTACT-01..04 |
| V14 Configuration | **Yes** | `.htaccess` `X-Robots-Tag` staging exclusion; `AddHandler …php52`; the dev-switcher file-existence gate |

### Threat Patterns for this stack

| Pattern | STRIDE | Mitigation |
|---|---|---|
| **A raw-HTML template slot introduced to solve Pattern 2** | Tampering / Information disclosure | **The phase's single real security decision.** `src/` currently has *no* unescaped output path. Use structured sub-arrays (E-2), never an `'html'` passthrough. If one is added anyway, it must be a documented, grep-asserted exception with a stated content-authorship trust boundary |
| Cyrillic mis-encoded into ISO-8859-1 by a bare `htmlspecialchars()` | Tampering (mojibake / broken escaping) | PHP 5.2 defaults to ISO-8859-1; the explicit `'UTF-8'` argument is mandatory. Already enforced by `torin_esc()` [VERIFIED: src/includes/category-page.php:76-78] |
| A literal `</script>` inside a JSON-LD string terminating the block early | Injection | This PHP 5.2 build always escapes `/` in `json_encode()` output, which prevents it structurally. **The new BreadcrumbList emitter must use `json_encode()` for the same reason** — a hand-written JSON block loses the protection [VERIFIED: src/includes/jsonld.php:16-27] |
| External link to the Google Business Profile | Tampering (reverse tabnabbing) | `rel="noopener"`; the URL is a developer-authored literal, never assembled from a request value |
| Third-party review widget script | Information disclosure / third-party trust | Excluded by D3-07/DH-2. If reopened, gate behind `checkpoint:human-verify` |
| API key committed for a live rating fetch | Information disclosure | Excluded by D3-07 — the phase introduces **no secret of any kind** |
| Ported legacy markup carrying an inline `on*` handler or `javascript:` href | Injection | Port strings, not markup (P-9). A grep gate over `src/` for `on[a-z]+=` and `javascript:` closes it mechanically |

---

## Sources

### Primary (HIGH confidence)

**In-repo, read this session with exact line citations:**
- `src/includes/category-page.php` — template slots, escaping, guards, h1 (lines 32-40, 51-99, 103-239)
- `src/includes/categories.php` — six records, publish gate (lines 26-106; kat-6 `page` at 87)
- `src/includes/site-config.php` — all keys and their `[ASSUMED]` markers (lines 12-122)
- `src/includes/jsonld.php` — LocalBusiness block and the 5.2 encoding rationale (lines 16-72)
- `src/includes/header.php` — metadata mechanism and defaults (lines 33, 49-54, 65-67)
- `src/includes/footer.php` — covid link, JSON-LD include (lines 91-118)
- `src/.htaccess` — handler, X-Robots-Tag, `ExpiresByType` set (no `image/jpeg`)
- `src/index.html`, `src/mehanichni-problemi.html`, `src/za-bateriite.html`, `src/covid.html`
- `site-current/` — `index.html` (595, 615, 633, 654-656, 963, 1079-1104), `profilaktika-laptop.html`
  (320, 361, 380, 398, 411), `warrently.html` (113-129), `za-bateriite.html` (129, 158),
  `zalivane-technosti.html` (34-40), `mehanichni-problemi.html` (7-16), `optimizatsiq.html` (56-63),
  `tokov-udar.html` (79-90), `problem-stari.html` (107-129, 181), `test-laptop.html`,
  `about.html`, `uslovia.html` (109), `covid.html` (140, 154-155)
- `site-current/assets1/img/`, `site-current/covid-19/` — asset inventory and measured dimensions
- Planning set: `03-CONTEXT.md`, `REQUIREMENTS.md`, `STATE.md`, `OWNER-QUESTIONS.md`,
  `02-PATTERNS.md`, `02-UI-SPEC.md` §Copywriting Contract, `torin-new-build-tasklist.md`

**Official documentation:**
- [Google — FAQPage structured data](https://developers.google.com/search/docs/appearance/structured-data/faqpage) — deprecation dates, verbatim notice
- [Google — Review snippet (Review, AggregateRating)](https://developers.google.com/search/docs/appearance/structured-data/review-snippet) — the self-serving restriction, verbatim
- [Google — Structured data search gallery](https://developers.google.com/search/docs/appearance/structured-data/search-gallery) — full supported-feature list; no `Service` feature
- [Google — Local business structured data](https://developers.google.com/search/docs/appearance/structured-data/local-business) — required/recommended properties
- [Google — Places API usage and billing](https://developers.google.com/maps/documentation/places/web-service/usage-and-billing) — field-tier SKU model

**Measured locally this session:**
- Cyrillic vs Latin advance widths and candidate title widths, parsed from
  `/System/Library/Fonts/Supplemental/Arial.ttf` (`hmtx`/`cmap` format 4, upem 2048)
- Build-machine tool availability (`php` absent; node/python3/curl/sips/cwebp present)
- Image dimensions for all 54 legacy JPEG/PNG assets

### Secondary (MEDIUM confidence)

- [Search Engine Land — The rise and fall of FAQ schema](https://searchengineland.com/faq-schema-rise-fall-seo-today-463993) — corroborates the deprecation timeline
- [Search Engine Journal — Google drops FAQ rich results](https://www.searchenginejournal.com/google-drops-faq-rich-results-from-search/574429/)
- [Portent — How often Google ignores our meta descriptions](https://portent.com/blog/seo/how-often-google-ignores-our-meta-descriptions.htm) — 71% mobile / 68% desktop
- [Ahrefs — How often does Google rewrite meta descriptions](https://ahrefs.com/blog/meta-description-study/) — ~62.8% over 20,000 keywords
- [Zyppy — The ideal SEO title tag length](https://zyppy.com/title-tags/meta-title-tag-length/) — 51–55 char lowest-rewrite band
- [ОПИК — Publicity obligations for BG16RFOP002-2.073](https://opic.bg/news/uo-na-opik-2014-2020-publikuva-informatsiya-otnosno-zadlzhenieto-na-benefitsientite-za-spavane-na-iziskvaniyata-za-informirane-i-publichnost-pri-izplnenie-na-proekti-po-protsedura-bg16rfop002-2073-pod) — the A3 poster requirement and the conditional website element
- [EUR-Lex — Regulation (EU) No 1303/2013](https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32013R1303) — Art. 71 durability periods
- [European Commission IP Helpdesk — CJEU on referential use (ZARA and AUDI)](https://intellectual-property-helpdesk.ec.europa.eu/news-events/news/cjeu-rules-trade-mark-referential-use-exception-zara-and-audi-cases-2024-02-09_en)
- [Fieldfisher — Audi v GQ, C-334/22](https://www.fieldfisher.com/en/services/intellectual-property/intellectual-property-blog/running-rings-around-referential-use-of-trade-marks-the-cjeus-decision-in-audi-v-gq)
- [BrightLocal — Can local businesses use review schema?](https://www.brightlocal.com/learn/review-schema/)

### Tertiary (LOW confidence — flagged, not relied on)

- Meta-description pixel budgets (~920 px desktop / ~680 px mobile) — SEO-vendor blogs, no primary source
- Third-party Google-reviews widget landscape (Elfsight, Trustindex, SociableKit, Shapo) — vendor and
  listicle sources; cited only to reject the category
- Sofia market price benchmarks (~26 € matrix labour, ~26 € board repair, ~47 € liquid damage) —
  from `torin-new-build-tasklist.md`, unverified, and out of scope while PRICE-01 is v2

---

## Metadata

**Confidence breakdown:**

| Area | Level | Reason |
|---|---|---|
| In-repo mechanics (template slots, escaping, data files, metadata mechanism) | **HIGH** | Every claim read from source this session with line citations and verbatim quotes |
| Content inventory (what exists in `site-current/`, where) | **HIGH** | Full-text extraction of 17 pages plus asset dimension measurement |
| Structured-data eligibility (FAQ, Service, AggregateRating, Breadcrumb) | **HIGH** | Google's own documentation, quoted verbatim, cross-checked against three independent secondary sources |
| Cyrillic title/description pixel arithmetic | **HIGH** for the ratios, **MEDIUM** for the absolute px budgets | Ratios measured from font tables; the 600/920/680 px budgets are secondary-source figures (A1, A2) |
| EU publicity obligation analysis | **MEDIUM** | Managing-Authority notice for this exact procedure is authoritative on *what* is required; silence on post-completion duration is inference (A3). The recommendation is safe either way |
| Trademark referential-use analysis | **MEDIUM** | Correct statement of Art. 14(1)(c) and *Audi v GQ*; not legal advice (A6) |
| Brand list, GBP figures, warranty variance, cat-6 scope | **LOW — owner-blocked** | OWNER-QUESTIONS #22, #7, #23, #3. Defaults recommended, all marked `[ASSUMED]` |
| SEO copy guidance (formula, candidates) | **MEDIUM** | Grounded in measured widths and rewrite-rate studies; the specific Bulgarian phrasing is a starting point for the planner, not locked copy |

**Sections deliberately omitted:** *Validation Architecture* — `workflow.nyquist_validation` is
explicitly `false` in `.planning/config.json`.

**Research date:** 2026-08-11
**Valid until:** 2026-09-10 for the structured-data and SEO findings (Google changes these on its own
schedule; the FAQ deprecation is three months old and the AggregateRating rule is seven years stable).
In-repo findings are valid until the source files change.
