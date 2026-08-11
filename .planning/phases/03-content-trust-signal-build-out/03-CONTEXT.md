# Phase 3: Content & Trust-Signal Build-Out - Context

**Gathered:** 2026-08-11
**Status:** Ready for planning

<domain>
## Phase Boundary

Fill the Phase 2 structure with real Bulgarian content, and surface the shop's genuine trust
signals and differentiators. Phase 2 built the shell — tokens, components, six-category IA, nav,
footer, the D-24 category template. This phase writes what goes in it.

The scan that opened this discussion found the job is materially larger than the roadmap's six
success criteria imply: **twelve of sixteen pages in `src/` are still stubs** reading «Тази
страница е временен скелет», and the three "published" category pages are deliberately thin
(`mehanichni-problemi.html` carries two `fixes` lines and nothing else). Real copy for the stubs
exists only in `site-current/`, at 250–550 lines per page.

Delivers content and trust signals. Does NOT deliver contact-path mechanics, performance work,
`robots.txt`/`sitemap.xml`, or the cutover — all Phase 4.

</domain>

<decisions>
## Implementation Decisions

A user-supplied SEO/conversion task list (`torin-new-build-tasklist.md`) was introduced during
this discussion and is the largest single input to it. It was explicitly framed by the user as
**"just a suggestion that is supposed to help improving what is already been planned"** — advisory,
not a spec. Several of its structural proposals collide with completed work; every collision was
resolved by explicit user decision below. **Where this file and the task list disagree, this file
wins.** Read the task list for its content ideas and shop facts, not for its page/URL structure.

### URL Structure and Page Set

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

### Requirement Scope

- **D3-06: PRICE-01 and REVIEWS-01 stay in v2.** The task list's Workstream 3 (prices) and
  Workstream 4 (reviews ingestion) assume both are arriving now. They are not being promoted.
  Consequences, recorded so they are known gaps rather than oversights:
  - No `/ceni/` page, no `price-teaser` block, no Offer/PriceSpecification schema this phase.
  - The category template's `prices` slot (D-24) stays unused — by design; it accepts numbers
    later without redesign.
  - **`smyana-na-matrica` ships without its stated winning argument.** The task list's case for
    that page is "competitors publish a number here; a page without one loses." With PRICE-01 in
    v2 the page ships without one. Accepted knowingly.
- **D3-07: TRUST-02 ships as a static badge only.** TRUST-02 (badge) is v1/Phase 3; REVIEWS-01
  (live widget) is v2 — a distinction the task list blurs. Ship a hardcoded rating + review count
  in `site-config.php`, linking to the Google Business Profile. **No** Places API, **no** per-service
  review quotes, **no** `/otzivi/` page. The real number must be confirmed once (OWNER-QUESTIONS #7)
  before it ships — the task list reports aggregators showing 128–146 across crawl dates, which
  substantially answers the "does the profile exist and is it healthy" half of that question.
  AggregateRating schema must NOT be emitted, since no reviews are visible on-page.

### Handling Open Owner Questions

- **D3-08: Draft, flag, ship what is unblocked.** Three OPEN owner questions gate real content:
  #3 (cat-6 scope, launch-blocking), #16 (real customer phrasing), #7 (review count). Write
  everything that does not depend on them. For the three gaps, draft a best-effort version marked
  `[ASSUMED]` **in-source**, following the convention already established in `categories.php` and
  `site-config.php`, and list them for owner review. Category 6 stays unpublished behind D-23
  regardless of how good the draft is — D-23 exists precisely so a thin page cannot ship on
  optimism. Do not quote drafted symptom lines back to anyone as confirmed shop language.

### Trust Signals

- **D3-09: TRUST-01 is a designed row of text wordmarks, not logo images.** Six to eight brands,
  headed «Обслужваме всички марки» and closed with «и др.». Decided against logo images on
  evidence gathered live during this discussion across eight competitors:

  | Competitor | Format | Brands | Framing |
  |---|---|---|---|
  | ITServiz | text, bulleted list | 47 | «СЕРВИЗ НА МАРКИТЕ:» |
  | SofiaComputers | text links | 80+ | «По марка:» |
  | ACS | text, inline | 17 | «Работим с всички марки» |
  | RemontLaptop.bg | text, inline | 15+ | «Извънгаранционен сервиз за лаптопи Apple MacBook Pro и MacBook Air, Acer, Toshiba, ASUS, DELL, IBM, Lenovo, HP…и др.» |
  | Plasico | text, in prose | 10 | body paragraph |
  | Cros Computers | text, inline | 7 | «…за всички марки и модели, включително Asus, HP, Dell, Lenovo, Acer, Apple и други.» |
  | Computer-Serviz | text | 1 | — |
  | **Trierra Soft** | **none at all** | **0** | organizes by device type instead |

  **Zero of eight use logo images.** None claims authorized status; several position explicitly as
  «извънгаранционен сервиз», which is the safe framing to copy. The long lists (47, 80+) read as
  keyword stuffing rather than trust. And Trierra Soft — prior research's "most modern/complete
  competitor found" — omits brands entirely, so this is table stakes among the dated sites and
  skipped by the best one. A short designed row of styled text is therefore visually ahead of every
  competitor's comma-separated prose while carrying none of the trademark exposure that logo images
  would (brand *names* are protected by nominative fair use in a way logos are not, which is the
  likeliest reason nobody in this market risks them). Build the slot so logos could drop in later
  without layout change, same pattern as D-38's photo slots.
  - **Corrects TRUST-01's premise.** The requirement says "an 'all brands serviced' row (Lenovo,
    HP, Dell…)" and prior research recorded "Universal claim; ITServiz shows 40+ brand names as a
    list." What is universal is the **claim**, not a logo row. The requirement inherited a format
    the market does not use.
- **D3-10: TRUST-03 is one shared warranty summary, reframed.** A single block sourced from
  `site-config.php` and reused across all category pages — never retyped per page. The live
  warranty page's actual condition is unusual: it requires the customer to use the laptop 5–6
  hours a day during the warranty period, to accumulate 150–200 hours of test time. Read
  charitably that is a statement of confidence that the repair holds under real use; read as a
  customer would, it looks like a way to void a claim. **Reframe it as the former.** Do not
  silently drop it — it is a term the shop operates under — and do not reproduce wording that
  reads as a trap.

### Differentiators

- **D3-11: DIFF-02 is owned properly — SUPERSEDES D-13's folded downgrade.** Battery regeneration
  gets the distinct, prominent treatment the requirement actually asks for, with
  `za-bateriite.html` (a locked, indexed URL) as its depth page. This **resolves OWNER-QUESTIONS #9**,
  which had asked the owner to sign off on the downgrade and never received an answer. DIFF-02
  moves from "knowingly unmet" to met.
- **D3-12: SmartBattery.eu is dead — every reference must be removed or fixed.** Stated by the
  user during this discussion. This is also what forces D3-11: with the specialist site gone there
  is nowhere left to link out to, so `za-bateriite.html` must carry the regeneration story itself.
  Four references exist, all in `site-current/` — **none has been ported into `src/` yet**, so this
  is caught before it enters the new build:

  | File | What it is | Treatment |
  |---|---|---|
  | `site-current/index.html:963` | contact block lists `office@smartbattery.eu` as a second email | remove — dead address on a dead domain |
  | `site-current/za-bateriite.html:158` | «посетете нашия специализиран сайт… www.SmartBattery.eu» | remove; replaced by the D3-11 content |
  | `site-current/problem-stari.html:181` | the same battery paragraph, duplicated verbatim | does not survive the D3-05 repurpose |
  | `site-current/uslovia.html:109` | privacy declaration names `www.smartbattery.eu` as a site it covers | **careful edit, not deletion** — this is a legal text currently making commitments on behalf of a site that no longer exists |

- **DIFF-01** needs no new structural decision — the self-diagnostic already has its homepage
  feature block (D-12). What Phase 3 owes is real content on `test-laptop.html` plus routing links
  from each symptom to the matching service page.
- **DIFF-03** stays inside category 4 (D-14). What changes is that it now has concrete evidence to
  present, harvested from the task list — see `<specifics>`.

### Content Production

- **D3-13: Rewrite service pages, port the rest — improving while porting.** Service and content
  pages (`profilaktika-laptop`, `za-bateriite`, `test-laptop`, `tokov-udar`, `about`) are rewritten
  against the adapted Definition of Done. Legal and utility pages (`uslovia`, `warrently`, `msg`,
  `laptopi`, `rezervni-chasti`) are ported near-verbatim — but the user was explicit that porting
  still means **fixing what is visibly wrong**: dead links, the `усливията` → `условията` typo, the
  SmartBattery references, stale contact details. A port is not a copy.
- **D3-14: Adapted Definition of Done, 600–1000 words, is the D-25 depth bar.** The task list's DoD
  assumes prices, reviews and real repair photos on every page; none are available (v2, v2, and
  OWNER-QUESTIONS #12/13 respectively). **A page publishes when the adapted list passes** — this is
  the bar D-23 has been gating against since Phase 2 without one.

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
  list is unconfirmed (see OWNER-QUESTIONS, new item).
- Schema implementation. `jsonld.php` already emits LocalBusiness; Service, FAQPage and
  BreadcrumbList are additions, emitted from the template rather than hand-written per page.
- Whether the EU/COVID content moves to About as prose or as a compact block (CONTENT-02).

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### This Phase's Primary Input
- `torin-new-build-tasklist.md` — **user-supplied, advisory.** Read for content ideas, shop facts
  and per-page angles. Do NOT follow its URL map (D3-01), its four-hub structure (D3-04), its
  Workstream 0 template tasks (already built in Phase 2), or its opening robots.txt item (already
  live via a better mechanism — see `<code_context>`). Its Workstreams 5 and 7 are Phase 4.

### Project & Requirements
- `.planning/PROJECT.md` — the six categories as a hard owner constraint; hosting; core value
- `.planning/REQUIREMENTS.md` — TRUST-01/02/03, DIFF-01/02/03, CONTENT-01/02, SEO-01 map here;
  SEO-04 (Complete) is what D3-01 upholds; PRICE-01/REVIEWS-01 are the v2 items D3-06 keeps deferred
- `.planning/ROADMAP.md` §Phase 3 — goal and the six success criteria
- `.planning/OWNER-QUESTIONS.md` — #3, #7, #16 gate content here; #5 and #9 are resolved by D3-05
  and D3-11; append new items, never replace

### Prior Phases
- `.planning/phases/02-design-system-information-architecture/02-CONTEXT.md` — D-09/D-40
  (category names), D-23 (publish gate), D-24 (core+optional template), D-25 (this phase owes the
  depth bar — D3-14 supplies it), D-26 (service mapping), D-28 (профилактика cross-listing),
  D-35 (covid.html), D-13 (superseded by D3-11)
- `.planning/phases/02-design-system-information-architecture/02-UI-SPEC.md` §Copywriting Contract
  — the category name table `categories.php` is sourced from
- `.planning/phases/02-design-system-information-architecture/02-PHOTO-BRIEF-CATEGORIES.md` and
  `02-PHOTO-BRIEF-SITE.md` — what owner-supplied photography is specified to depict
- `.planning/phases/01-migration-safety-net-foundation/01-URL-INVENTORY.md` — the sixteen locked
  URLs; `problem-stari.html`'s disposition is resolved by D3-05

### Research
- `.planning/research/FEATURES.md` — nine-competitor feature landscape; §Competitors Examined is
  the source list the D3-09 brand evidence extends
- `.planning/research/PITFALLS.md` — URL/ranking continuity risks; Pitfall C is why staging uses
  a header rather than robots.txt

### Live Site Baseline (source of ported copy)
- `site-current/warrently.html` — the 5–6 hours/day warranty condition D3-10 reframes
- `site-current/za-bateriite.html`, `site-current/problem-stari.html` — the duplicated battery
  paragraph and SmartBattery references (D3-12)
- `site-current/uslovia.html` — privacy text naming the dead domain (D3-12)
- `site-current/index.html` — `office@smartbattery.eu` at line 963 (D3-12)
- `site-current/test-laptop.html`, `site-current/profilaktika-laptop.html`,
  `site-current/tokov-udar.html`, `site-current/about.html` — copy to rewrite against D3-14

### Phase 2 Foundation (what this phase fills)
- `src/includes/categories.php` — six records, `[ASSUMED]` symptom lines, `published` booleans,
  `torin_category_href()`
- `src/includes/category-page.php` — the D-24 template; recognised `$page` keys are documented in
  its header comment
- `src/includes/site-config.php` — contact values; the home for the D3-10 warranty summary and the
  D3-07 rating figure
- `src/includes/jsonld.php` — LocalBusiness today; Service/FAQPage/BreadcrumbList to add
- `src/.htaccess` — the `.html`-as-PHP handler D3-02 depends on

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- **`includes/category-page.php` already implements roughly 80% of what the task list's
  Workstream 0 asks for.** It renders intro, fixes, warranty, process, FAQ (as native `<details>`),
  related, prices and CTA, each behind a guard that emits nothing at all when unset. The task list
  requests `faq-block.php`, `warranty-block.php`, `cta-block.php` and a service-page template as
  new work — it was written without sight of Phase 2's output. **Do not rebuild these.** What is
  genuinely missing is Service/FAQPage/BreadcrumbList schema and a `breadcrumbs` renderer.
- `torin_category_href()` routes cards and nav through the publish gate, so publishing a category
  page is create-a-file plus flip-a-boolean, with zero edits in any consumer.
- `torin_render_svc_item()` renders a list entry as a link when it carries an `href` — this is the
  mechanism that lets профилактика be cross-listed into category 5 as a link rather than
  duplicated copy (D-28).
- `torin_esc()` / `torin_has_content()` — escaping and the present-but-empty guard. An empty string
  assigned to a spine key must omit its block, or Phase 3 reintroduces the empty headings D-23
  exists to prevent.

### Established Patterns
- **PHP 5.2.17.** No short array syntax, no closures, no namespaces, `dirname(__FILE__)` not the
  5.3+ magic constant, and `htmlspecialchars()` always passed an explicit UTF-8 charset (5.2
  defaults to ISO-8859-1 and every string here is Cyrillic).
- Zero build tooling. Plain HTML/CSS/JS + PHP `include()`, deployed by direct FTP placement.
- `[ASSUMED]` in a source comment is the established marker for a value awaiting owner
  confirmation — D3-08 extends it rather than inventing a new convention.
- Facts are single-sourced. The E.164 phone key is read by every CTA and by the structured data;
  category names are read from the record, never retyped on a page. The D3-10 warranty summary and
  the D3-07 rating figure must follow the same rule.

### Integration Points
- `public_html/new/` on `bell.host.bg` — live preview at `torin.bg/new`
- `index.html` — hero, six-card grid, catch-all disclosure (`sym-*` ids), self-diagnostic block,
  CTA. The brand wordmark row (D3-09) and the DIFF-02 block (D3-11) land here.
- The three unpublished categories have no page file at all — that is deliberate, not an omission.

### Already Done — Do Not Redo
- **Staging is already excluded from Google's index**, and by a better mechanism than the task
  list's opening item proposes. `src/.htaccess` sets `X-Robots-Tag "noindex, nofollow"`, verified
  live 2026-08-10 across HTML pages, CSS and images, with the live root correctly carrying no such
  header. The task list asks for `Disallow: /new/` in robots.txt **plus** a meta noindex; that
  combination is actively worse, because a robots.txt disallow prevents Google fetching the page
  and therefore from ever seeing the noindex. Leave the header mechanism alone.
- **SEO-01 is roughly 80% complete.** All sixteen pages already carry unique `$torin_title` and
  `$torin_desc` from Phase 2's metadata pass. They are labelled *working* values written before the
  body copy existed. What remains is tuning them against real content and the D3-14 length limits —
  not creating them.

</code_context>

<specifics>
## Specific Ideas

### Shop facts recorded nowhere else in the planning set
Harvested from `torin-new-build-tasklist.md`. These are the reason the document is worth keeping
even where its structure was rejected:

- **Board-level repair evidence** — IR reflow machine, AMTECH flux, 90% durable-repair rate, 10 °C
  lower operating temperatures, ultrasonic cleaning. DIFF-03 previously had no concrete proof to
  present; this is it. Belongs in category 4 as conversion evidence, not as a keyword target.
- **«Since 1993»**, the equipment, the engineers, a repairs-completed counter, B2B/government
  history — real About-page substance replacing a stub.
- **Google reviews reported at 128–146** across aggregator crawl dates. Substantially answers the
  "does the profile exist and is it healthy" half of OWNER-QUESTIONS #7. Pull the live figure from
  GBP before shipping; use only that.
- **Sofia market benchmarks** — ~26 € matrix labour, ~26 € board repair, ~47 € liquid damage.
  Not actionable while PRICE-01 is v2, but preserved for when it isn't.

### Page angles worth keeping
- **Liquid-damage first-aid block** (power off, remove battery, do not charge, no hairdryer) on
  category 4. The strongest single idea in the task list — high-urgency, high-value snippet bait,
  and genuinely useful to a panicking visitor.
- **«кога не си струва» honesty block** — an explicit statement of when a board is not worth
  recovering and what the alternatives cost. Framed as a trust differentiator, not a lost sale.
- **«матрица или видеочип?» diagnostic** on the matrix page, cross-linked to `test-laptop.html` —
  the differentiator against competitors who only quote a price.
- **«защо не отлагате» block** on the power-jack page, linking to category 4 — a loose jack that
  damages the board is a genuine escalation, and the internal link follows the real failure path.
- **«Какво можем, което другите отказват»** as a short homepage block — positions the board-level
  work without giving it a standalone page.
- **Category 4 urgency CTA** — «Не включвайте лаптопа — обадете се веднага».

### User framing worth preserving
- On the task list itself: *"all that is in the file is just a suggestion that is supposed to help
  improving what is already been planned here"* — the framing that made it an input to reconcile
  rather than a spec to execute, and the reason four of its structural proposals could be rejected
  without conflict.
- On porting: *"still make sure when porting improvements will be done where needed"* — a port is
  not a copy. This is what D3-13 encodes.
- On SmartBattery.eu: *"does not exist anymore and anything related to it should be fixed or
  removed"* — which turned DIFF-02 from a placement question into a content obligation.

### Verified during this discussion
- Twelve of sixteen `src/` pages are stubs; the three published category pages are thin by design.
- Zero of eight competitors examined use brand logo images (D3-09 table).
- `problem-stari.html` duplicates `za-bateriite.html`'s battery paragraph verbatim — confirming
  OWNER-QUESTIONS #5's overlap suspicion, but against a different page than it assumed.
- `torin.bg/new` returns `x-robots-tag: noindex, nofollow` on pages, CSS and images; the live root
  correctly returns none.

</specifics>

<deferred>
## Deferred Ideas

### To Phase 4 (from the task list)
- Workstream 5 in full — photo-upload quote form, sticky mobile call bar, real thank-you page,
  conversion events, contact-form hardening (CONTACT-01/02/03/04)
- Workstream 7 in full — content freeze, move `/new` to root, sitemap generation and submission,
  Screaming-Frog-style crawl, Rich Results validation, GBP website URL update, Core Web Vitals,
  404-log monitoring (SEO-03, MIGR-02, DESIGN-02)
- **Removing the staging `noindex` at cutover** — already filed as
  `.planning/todos/pending/strip-staging-noindex-at-cutover.md` (`resolves_phase: 4`). The task
  list independently flags this as "easy to forget, catastrophic if missed," which is corroboration
  from a second source.
- Google Search Console + Analytics setup on the new structure (blocked on OWNER-QUESTIONS #1)

### To v2
- PRICE-01 — the full price table, `/ceni/`, per-page price teasers, Offer schema, dual лв/€
  display. **Worth verifying whether dual-currency display is now a legal requirement rather than a
  competitive convention**, given Bulgaria's euro adoption — the task list treats it as the latter.
- REVIEWS-01 — Places API ingestion, per-service review quotes, `/otzivi/` page, AggregateRating
  schema, review-generation flow (cards/SMS at collection), responding to reviews in GBP
- GALLERY-01 / TURNAROUND-01 — before/after photography and turnaround commitments
- `/optimizatsiya/upgrade-ssd/` as a child page — the task list recommends it and notes it has its
  own search volume
- Standalone реболинг/BGA page, per-brand pages, English `/en/` layer with hreflang,
  neighbourhood-targeted local pages, blog cadence beyond two ported articles

### Recorded but not acted on
- **Express-service tier** with a stated surcharge and turnaround — an operational decision for the
  owner before it can be a page element.
- **Free-diagnostics policy detail** — what happens when a customer declines the repair.
  Competitors charge a declined-repair fee; if Torin does not, that is a bullet worth having.
  Needs owner confirmation.
- **Logo redraw to SVG/2×** — carried from Phase 2, still Claude's discretion on timing, still
  dependent on OWNER-QUESTIONS #11.

### Reviewed Todos (not folded)
- `redraw-category-icons.md` — matched at 0.2 on the word "phase" alone. A visual quality upgrade
  with no content dependency; nothing in Phase 3 is blocked by icon fidelity.
- `verify-viber-button-before-launch.md` — explicitly `resolves_phase: 4`; it is a cutover gate
  requiring a real handset, not phase-3 work.

</deferred>

<phase_artifacts>
## Additional Artifacts This Phase Produces

- **New OWNER-QUESTIONS items** — append, never replace. At minimum: which brands Torin actually
  services (D3-09 needs a confirmed six-to-eight, and the roadmap's Lenovo/HP/Dell/Asus/Acer/Apple/MSI
  list came from requirements drafting, not from the owner); whether warranty terms genuinely vary
  by service type (D3-10 assumes one shared set); the declined-repair diagnostics policy; and
  confirmation of the live GBP rating and review count for D3-07.
- **Mark OWNER-QUESTIONS #5 and #9 resolved** — by D3-05 and D3-11 respectively, with the reasoning
  recorded inline per that file's convention.

</phase_artifacts>

---

*Phase: 3-Content & Trust-Signal Build-Out*
*Context gathered: 2026-08-11*
