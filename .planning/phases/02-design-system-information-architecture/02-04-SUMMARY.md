---
phase: 02-design-system-information-architecture
plan: 04
subsystem: site-wide-rollout-and-verification
tags: [php-includes, seo, category-template, responsive, verification, structured-data]
status: complete

requires:
  - phase: 02-01
    provides: "CSS token layer, header/footer PHP chrome, per-page $torin_title/$torin_desc mechanism, scripts/deploy-new.sh, and the mod_deflate finding that makes the CSS budget a wire figure"
  - phase: 02-02
    provides: "$torin_categories and torin_category_href() — the publish gate this plan's category template reads; the six homepage anchors the three unpublished categories fall back to"
  - phase: 02-03
    provides: "nav, contact-first footer and LocalBusiness JSON-LD on all 16 pages; the narrowed V3 PHP-5.2 lint pattern; the prohibition-comment wording convention"
provides:
  - "All sixteen locked URLs live on the new design system with their own distinct title and description — the per-page SEO mechanism Phase 3 needs, wired and proven against the deployed site"
  - "src/includes/category-page.php — the D-24 template: required spine plus four optional blocks that emit nothing at all when absent"
  - "torin_category_by_id() — record lookup by id, so a page never retypes its own category name"
  - "A full validation record (V2/V3/V4/V6/V7 + byte budget + SEO-04) taken against the deployed site rather than the repository"
affects:
  - "Phase 3 — CONTENT-01 owns the body copy on all fifteen pages; SEO-01 replaces the working titles and descriptions; D-25 fills the intro, гаранция, process, FAQ, related and prices slots; publishing the three remaining categories means creating three files and flipping three booleans"
  - "Phase 4 — DESIGN-02 inherits the byte baseline recorded below; the cutover surface is still two deletable files plus one marked fence"
  - "Any future page: the shape is `$torin_title`/`$torin_desc` above the header include, `<main class=\"section\"><div class=\"container\">` inside"

actuals:
  tokens: 10509
  tasks: 3
  commits: 3

tech-stack:
  added: []
  patterns:
    - "A page's title is derived from the shared record when the page has one, so single-sourcing covers the metadata too — not just the visible heading"
    - "Optional blocks are decided once, into named booleans, before any markup is emitted; an unmet guard produces no markup at all rather than a hidden element"
    - "Negated :has() for a conditional reserve: :not() is non-forgiving, so an unsupporting browser drops the rule and keeps the safe unconditional behaviour"
    - "Site-wide claims are asserted against every deployed response, never against the repository — a filename grep passes on a page still shipping the bundle"

key-files:
  created:
    - src/includes/category-page.php
  modified:
    - src/about.html
    - src/covid.html
    - src/laptopi.html
    - src/mehanichni-problemi.html
    - src/msg.html
    - src/optimizatsiq.html
    - src/problem-stari.html
    - src/profilaktika-laptop.html
    - src/rezervni-chasti.html
    - src/test-laptop.html
    - src/tokov-udar.html
    - src/uslovia.html
    - src/warrently.html
    - src/za-bateriite.html
    - src/zalivane-technosti.html
    - src/css/components.css

key-decisions:
  - "The three unpublished category page files are NOT created in this phase — D-23 gates publication on content, D-25 assigns depth to Phase 3, and torin_category_href() already routes both cards and dropdown to homepage anchors, so nothing is broken meanwhile"
  - "The intro and the warranty summary are guarded exactly like the optional blocks even though D-24 puts them in the required spine: their content is a Phase 3 deliverable, and a spine slot that ships an empty heading is the thin-content shape the publish gate exists to prevent"
  - "The three category pages derive $torin_title from the shared record, which makes their title assignment line textually identical — the source-level distinctness grep and the single-sourcing grep are mutually unsatisfiable, and the live assertion is the one that binds"
  - "<main class=\"section\"><div class=\"container\"> rather than <main class=\"container\">: `py-5` was vertical padding and .container carries none, so .section > .container is the faithful translation and matches index.html"
  - "The 56px call-bar reserve is scoped with body:not(:has(.callbar)) rather than body:has(.callbar) — :not() is non-forgiving, so a browser without :has() keeps the old unconditional reserve instead of letting the bar occlude the homepage footer"
  - "uslovia.html keeps the plan's «Общи условия» working title although the legacy page is headed «ДЕКЛАРАЦИЯ ЗА ПОВЕРИТЕЛНОСТ» — the discrepancy is recorded in the file for SEO-01 rather than silently resolved here"

patterns-established:
  - "ugrep is the `grep` on this machine and treats `$` as an anchor anywhere in a pattern, so every plan's `grep '$torin_title'` silently returns zero — escape it or use -F"
  - "A variable name is a grep surface: $torin_cat_id made `grep -c 'cat_id'` return 2 instead of 1, on a line that had nothing to do with the assertion"

requirements-completed: [SEO-02, DESIGN-01, IA-01]

duration: 40min
completed: 2026-08-06
---

# Phase 02 Plan 04: Site-Wide Rollout and Phase Verification Summary

**All sixteen locked URLs now render on the new design system with their own distinct title and description, the D-24 category template renders a two-sub-service category and a four-sub-service category from one file while omitting six optional blocks without leaving a trace, and every site-wide claim this phase makes is proven against the deployed responses — sixteen rows of `http=200 vendor=0 lang=1 rawphp=0`, zero third-party hosts at runtime, and three globals reporting `undefined`.**

## Performance

- **Duration:** ~40 min
- **Tasks:** 3
- **Files:** 1 created, 16 modified

## Task Commits

1. **Task 1: per-page metadata and the design-system container on fifteen pages** — `c2225a8` (feat)
2. **Task 2: the D-24 category page template, instantiated thin and deep** — `b6d1e1e` (feat)
3. **Task 3: phase verification sweep** — no source change was required (see below); results recorded here and committed with this SUMMARY.

Task 3 lists `src/css/components.css` as modifiable **for exactly one reason** — trimming if the CSS came in over budget. It did not (13,020 B on the wire against a 20 KB target), so nothing was trimmed and the task produced no code diff. That is the intended outcome, not a skipped task.

## The sixteen-row live sweep, verbatim

Run against `https://torin.bg/new/<page>` after both tasks were deployed. `vendor` counts the legacy JavaScript stack **and** the D-08 font surface in one pattern: `jquery|modernizr|scrollmagic|pagepiling|theme-vendors|theme.min|assets1/|zdassets|fonts.googleapis|fonts.gstatic|basiersquare-|GlacialIndifference-|cerebri-sans|family=Barlow`.

```
index.html                 http=200 vendor=0 lang=1 rawphp=0
about.html                 http=200 vendor=0 lang=1 rawphp=0
laptopi.html               http=200 vendor=0 lang=1 rawphp=0
profilaktika-laptop.html   http=200 vendor=0 lang=1 rawphp=0
optimizatsiq.html          http=200 vendor=0 lang=1 rawphp=0
mehanichni-problemi.html   http=200 vendor=0 lang=1 rawphp=0
za-bateriite.html          http=200 vendor=0 lang=1 rawphp=0
tokov-udar.html            http=200 vendor=0 lang=1 rawphp=0
zalivane-technosti.html    http=200 vendor=0 lang=1 rawphp=0
rezervni-chasti.html       http=200 vendor=0 lang=1 rawphp=0
warrently.html             http=200 vendor=0 lang=1 rawphp=0
uslovia.html               http=200 vendor=0 lang=1 rawphp=0
covid.html                 http=200 vendor=0 lang=1 rawphp=0
test-laptop.html           http=200 vendor=0 lang=1 rawphp=0
problem-stari.html         http=200 vendor=0 lang=1 rawphp=0
msg.html                   http=200 vendor=0 lang=1 rawphp=0
```

Sixteen rows, no exceptions. **This — not the `lang="bg"` attribute sitting in `header.php` — is what proves SEO-02.** A page that silently stopped including the shared chrome would lose its language declaration, its design system and its noindex staging context while looking perfectly fine in the repository.

**Sixteen distinct titles, live:** extracting `<title>` from all sixteen deployed URLs and de-duplicating yields **16**. Sixteen distinct `<meta name="description">` values likewise. Before this plan, all sixteen legacy pages shared one string — verified directly against `site-current/`.

## The sixteen live titles

| URL | Live title |
|---|---|
| index.html | ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ |
| about.html | За нас · ТОРИН КОМПЮТЪРС |
| laptopi.html | Употребявани лаптопи · ТОРИН КОМПЮТЪРС |
| profilaktika-laptop.html | Профилактика на лаптоп · ТОРИН КОМПЮТЪРС |
| optimizatsiq.html | Оптимизация · ТОРИН КОМПЮТЪРС |
| mehanichni-problemi.html | Счупвания и механични повреди · ТОРИН КОМПЮТЪРС |
| za-bateriite.html | За батериите · ТОРИН КОМПЮТЪРС |
| tokov-udar.html | Токов удар и захранване · ТОРИН КОМПЮТЪРС |
| zalivane-technosti.html | Заливане и ремонт на дънни платки · ТОРИН КОМПЮТЪРС |
| rezervni-chasti.html | Резервни части · ТОРИН КОМПЮТЪРС |
| warrently.html | Гаранционни условия · ТОРИН КОМПЮТЪРС |
| uslovia.html | Общи условия · ТОРИН КОМПЮТЪРС |
| covid.html | Проект BG16RFOP002-2.073 · ТОРИН КОМПЮТЪРС |
| test-laptop.html | Тествай сам своя лаптоп · ТОРИН КОМПЮТЪРС |
| problem-stari.html | Чести проблеми · ТОРИН КОМПЮТЪРС |
| msg.html | Съобщение · ТОРИН КОМПЮТЪРС |

These are **working** titles carrying the mechanism. SEO-01 in Phase 3 owns the search-tuned copy; each file says so in a comment.

## Explicitly Recorded Findings

### 1. Planner call: the three new category page files are deferred to Phase 3

`ekran-klaviatura-portove.html`, `pregryavane-ohlazhdane.html` and `nestandartna-technika.html` **do not exist as files**, and that is deliberate.

- **D-23** gates publication on genuine content. These three pages are new, so there is no accumulated ranking to protect by launching early — nothing is lost by waiting, and a thin page crawled early can drag site-wide quality signals.
- **D-25** assigns content-depth targets to Phase 3, which is where the content that would justify publication is written.
- **02-RESEARCH Open Question 4 / A10** asked for an explicit call either way and recommended creating each file at publish time.

Nothing is broken meanwhile, verified live: the dropdown on every page serves `index.html#kat-2`, `index.html#kat-5` and `index.html#kat-6`, and the homepage carries all six stable anchors. Publishing is: create the file, flip one boolean. **Zero edits in any consumer** — the cards, the dropdown and the sitemap all read `torin_category_href()`.

The alternative — empty shells — is strictly worse: it puts three thin pages in front of a crawler in exchange for nothing.

### 2. The category template: thin and deep, measured

One file renders both shapes. Measured live at 360×640 and 900×800:

| | `mehanichni-problemi` (thin, 2 sub-services) | `zalivane-technosti` (deep, 4) |
|---|---|---|
| Sections in `<main>` | 3 | 3 |
| Section tops @360 | 57 · 211 · 500 | 57 · 211 · 572 |
| Section heights @360 | 154 · 289 · 329 | 154 · 361 · 329 |
| Contiguous, no gap between sections | 57+154=211 ✓ 211+289=500 ✓ | 57+154=211 ✓ 211+361=572 ✓ |
| Headings rendered | Счупвания и механични повреди · Какво ремонтираме · Свържете се с нас | Заливане и ремонт на дънни платки · Какво ремонтираме · Свържете се с нас |
| Empty headings | **0** | **0** |
| Horizontal overflow @360/900 | none | none |

Absent optional blocks leave **no trace at all** on the thin page: `grep -ciE 'svc__process|svc__faq|svc__related|svc__prices'` returns **0**, `grep -c 'Как работим'` returns **0**, `grep -c 'svc__intro'` returns **0**, and there is no `<h2>Гаранция</h2>`. Six blocks omitted, zero residue — the class names and the heading strings exist only inside the include, never in a served response that has no content for them.

`zalivane-technosti.html` renders its four D-26 sub-services, «Ребоулинг на BGA чипове» among them, and its category name from the shared record. `grep -c 'Счупвания и механични повреди' src/mehanichni-problemi.html` returns **0** — the page never retypes its own name.

### 3. The intro and the warranty summary are guarded like optional blocks — deliberately

D-24 puts «title, intro, what we fix, symptoms, warranty summary, CTA» in the **required** spine. But D-25 assigns the *content* of the intro and the TRUST-03 warranty summary to Phase 3.

Rendering them unconditionally today would ship `<h2>Гаранция</h2>` with nothing under it on three pages — which is exactly the thin-content signal D-23 exists to prevent, and worse than the section simply being absent. So both are guarded by the same `isset` + non-empty test as the four optional blocks, and the dependency is recorded in the include's own comment header so a later reader does not mistake the guard for an omission.

`torin_has_content()` treats a present-but-empty value exactly like an absent key. Without that, Phase 3 assigning `'intro' => ''` would quietly reintroduce the empty heading.

### 4. Runtime check — the one a grep cannot make (02-RESEARCH V2 / Pitfall 1)

Headless Chrome 148 against `https://torin.bg/new/index.html`, cache disabled:

| Assertion | Result |
|---|---|
| `typeof window.jQuery` | **`undefined`** |
| `typeof window.$` | **`undefined`** |
| `typeof window.ScrollMagic` | **`undefined`** |
| `typeof window.Modernizr` | **`undefined`** |
| `typeof window.pagepiling` | **`undefined`** |
| Requests into the legacy asset directory or any named vendor file | **0** |
| Distinct request hosts | **`["torin.bg"]`** — zero third-party hosts of any kind |
| `document.scripts` | `js/site.js` + one inline (the dev switcher's, deleted at cutover) |
| Total requests / transferred | **9 requests, 104,410 B** |

This is the assertion Pitfall 1 exists for: the naive filename grep passes on a page still shipping half a megabyte of ScrollMagic, Isotope, imagesLoaded, Velocity and jQuery UI, because they were never separate script tags — they lived bundled inside one file. Nine same-origin requests and five `undefined` globals is the proof that grep cannot give.

### 5. D-08 font surface — swept by name *and* by shape

The vendor pattern set was extended with both Google Fonts hostnames and the four dropped webfont filenames (`basiersquare-`, `GlacialIndifference-`, `cerebri-sans`, `family=Barlow`). Static sweep over `src/*.html`, `src/includes/*.php` and `src/js/*.js`: **no output**. Live sweep over all sixteen responses: **0 on every row**.

Plus one structural check that catches a re-added remote font by *shape* rather than by name, which is what survives a family being renamed upstream:

```
grep -nE "@import|url\(['\"]?https?:" src/css/*.css     → no match
```

No stylesheet references a remote asset of any kind. All typography resolves from the two self-hosted Sofia Sans subsets (D-06a), both served 200 at their recorded sizes (25,568 B and 40,372 B — unchanged, so no upstream re-cut).

### 6. Byte budget — measured, and inside it on the wire

| File | Raw | On the wire (gzip, as served) |
|---|---:|---:|
| `css/base.css` | 6,138 | 2,857 |
| `css/layout.css` | 3,958 | 1,666 |
| `css/components.css` | 24,852 | 8,497 |
| **Production CSS total** | **34,948** | **13,020** |
| `css/theme-a.css` (dev only, deleted at cutover) | 511 | — |
| `js/site.js` | 2,476 | 1,175 |
| `includes/icons.php` (server-side, never sent as a file) | 5,194 | — |
| `fonts/sofia-sans-cyrillic.woff2` | 25,568 | — (already compressed) |
| `fonts/sofia-sans-latin.woff2` | 40,372 | — |

**Wire cost is 13,020 B — 64% of the 20 KB target, ~7.3 KB of headroom.** `content-encoding: gzip` and `cache-control: max-age=604800` both confirmed live on `components.css`.

The 20 KB figure was written on the premise *"this host serves no compression — every byte is wire cost."* Plan 02-01 disproved that premise (`mod_deflate` is live), so the wire figure is the one that binds. **Nothing was trimmed to chase the raw number** — the per-rule provenance comments are this project's documented convention, and deleting documentation to satisfy a budget whose stated rationale no longer holds is a bad trade.

This plan's own contribution: `components.css` grew **21,971 → 24,852 raw** and **7,549 → 8,497 gzipped** (+948 B on the wire) for the nine `.svc` rules and the call-bar reserve fix.

### 7. Responsive pass — four viewports, measured against the research table

Live, headless, `index.html`:

| | 360×640 | 560×800 | 900×800 | 1440×900 |
|---|---|---|---|---|
| `scrollWidth ≤ innerWidth` | ✓ 360 | ✓ 560 | ✓ 900 | ✓ 1440 |
| Hero | 269px = **42.0%** | 336px = 42.0% | 336px = 42.0% | 352px = 39.1% |
| Card 1 bottom edge | **574.8px** vs 584px usable | — | — | — |
| Grid columns / row sizes | 1 · [1,1,1,1,1,1] | 2 · **[2,2,2]** no orphan | 3 · [3,3] | 3 · [3,3] |
| Hamburger / nav list | `flex` / `none` | `flex` / `none` | **`none`** / `flex`, 5 items inline | `none` / `flex`, 5 inline |
| Call bar | `flex` | `flex` | `none` | `none` |
| Container | 360 (full) | 560 | 900 | **capped at 1152**, not full-bleed |
| Body measure | — | — | — | **689px = 66ch exactly** |

**Above-the-fold budget (D-30) is unchanged at 574.8px against 584px usable — the same 9.2px of margin plans 02-02 and 02-03 measured.** Nothing this plan added touches the homepage.

**Dropdown at 900×800:** box 358.8px, `position: absolute`, and all six names render **one text line each** (`Range.getClientRects().length === 1` for every item, none clipped) at 29, 27, 11, 33, 22 and 20 characters.

### 8. Keyboard-only pass — re-run against the finished site

900×800, every row an observed state transition:

| Step | `aria-expanded` | Sub-list | Focus |
|---|---|---|---|
| load | false | hidden | BODY |
| Tab ×5 | false | hidden | **uslugiBtn** |
| Enter | **true** | visible | uslugiBtn |
| Tab | true | visible | first sub link («Счупвания и механични…») |
| **Escape** | **false** | hidden | **uslugiBtn — focus restored** |
| Space | **true** | visible | uslugiBtn |

WCAG 1.4.13 dismissal and focus return both hold on the finished site.

### 9. JavaScript-disabled pass — the gap is confirmed still open, and still mitigated

Script execution disabled in a real browser; the hamburger pressed through raw CDP input dispatch (Puppeteer's own `click()` needs page JS):

| | Result |
|---|---|
| `aria-expanded` after a genuine mouse press on the toggle | stays **`"false"`** |
| Nav panel visible | **no** |
| Category links reachable **from the nav** | **0 of 6** |
| Category links reachable **from the homepage card grid** | **6 of 6**, publish-gate hrefs intact (`mehanichni-problemi.html`, `index.html#kat-2`, `optimizatsiq.html`, `zalivane-technosti.html`, `index.html#kat-5`, `index.html#kat-6`) |
| `<details>` disclosures present | 4 |

**Newly verified this plan:** a category page with JavaScript disabled renders its entire spine — h1 from the shared record, all four sub-services, the symptom line, both CTA hrefs (`tel:029549710`, `viber://chat?number=%2B35929549710`) and its single JSON-LD block. The category template introduces **no** JavaScript dependency.

This remains an **accepted, recorded gap**, not a solved problem. No CSS-checkbox fallback was built: it would fork the ARIA contract for a case the card grid already covers, and the homepage is one click away via «Начало».

### 10. V7 structured data — validated on the newly created page type

`https://validator.schema.org/validate` against the deployed `zalivane-technosti.html` (URL fetch; the `text`/`code` payload forms return `fetchError: NOT_FOUND` and must not be mistaken for a pass — they report `numObjects: 0` with zero errors):

```
numObjects=1  totalNumErrors=0  totalNumWarnings=0
types=[LocalBusiness, ComputerStore]  numErrors=0
name = ТОРИН КОМПЮТЪРС      telephone = +35929549710
```

Cyrillic decodes correctly in the parsed view. Exactly one block per page, parsed successfully on `index.html`, `zalivane-technosti.html` and `about.html`, each carrying `name`, `address`, `email`, `geo`, `hasMap`, `openingHoursSpecification`, `telephone`, `url`.

### 11. PHP 5.2 safety and encoding

All six V3 lint greps return **no output** across every include and every page file — short-array literals (narrowed per 02-03 Finding 5), closures, short echo tags, 5.3+ language constructs, the 5.4 JSON constants, and the 5.3 magic directory constant.

The **original** V3 short-array pattern still fires on four lines in `jsonld.php` (`'email' => $site['email']` and friends). These are array subscripts valid since PHP 4, not 5.4 short-array literals — the known false positive 02-03 documented. It produced **zero** hits on this plan's new file, which was written to avoid the shape entirely. `category-page.php` is clean under **both** patterns.

Encoding sweep over every `.php`, `.html`, `.css` and `.js` under `src/`: no BOM anywhere, every file `utf-8` or `us-ascii`. Live Cyrillic round-trip confirmed on the homepage («ТОРИН КОМПЮТЪРС») and on a Task 1 page («Информация за литиево-йонни батерии»).

### 12. SEO-04 and the Phase 4 cutover surface

- **Filename diff between `src/` and `site-current/` is empty** (excluding the local-only `phptest.html` spike and the Google verification file). Asserted twice — after Task 1 and again in the sweep. No filename added, renamed or retired.
- `covid.html` and `problem-stari.html` are both present, both deployed, both returning 200, and **linked from no navigation surface on any of the sixteen pages** (grepped every response for `href="covid.html"` and `href="problem-stari.html"` — zero hits). Each file carries an inline note explaining why it stays and that any later retirement must be a 301, never a bare 404 (D-36).
- `src/css/theme-a.css` and `src/includes/dev-switcher.php` both exist; the `DEV-ONLY` fence appears 4× in `header.php`. **The cutover is still two file deletions plus removing the marked lines**, and this plan edited no token file — `base.css`, `layout.css`, `theme-a.css`, `dev-switcher.php`, `site-config.php`, `categories.php`, `header.php`, `footer.php`, `jsonld.php` and `icons.php` are all untouched by its two commits.
- `X-Robots-Tag: noindex, nofollow` intact on the staging subtree.

### 13. `grep` on this machine is `ugrep`, and it broke six plan verifications silently

`grep --version` reports **ugrep 7.5.0**. ugrep treats `$` as an end-of-line anchor **anywhere** in a pattern, not only at the end as POSIX BRE requires. So every check written as `grep '$torin_title' src/*.html` returns **zero matches on files that plainly contain the string** — a false *failure* that looks exactly like the metadata never being assigned.

Verified directly: `grep -c '$torin_title' src/about.html` → **0**; `grep -cF '$torin_title' src/about.html` → **1**.

Use `grep -F` or escape as `'\$torin_title'`. This affects the plan-level greps in 02-04 and will affect any future plan that greps a PHP variable name on this machine. Worth carrying forward alongside 02-03's Finding 6 (a prohibition comment and its asserting grep cannot share vocabulary): **the verification greps in these plans have now failed for two distinct reasons that had nothing to do with the code.**

### 14. A variable name is a grep surface

`grep -c 'cat_id' src/mehanichni-problemi.html` expected **1** and returned **2**. The second hit was `$torin_cat_id = $torin_cat['id'];` — an intermediate variable whose *name* contains the asserted substring, on a line that has nothing to do with the assertion. Renamed to `$torin_cat_key`; the count is now 1 on all three category pages.

Same class as 02-03's Finding 6, one level down: it is not only comments that collide with grep-asserted tokens, it is identifiers too.

## Deviations from Plan

### 1. [Rule 1 — Bug] 56px of dead space below the footer on fifteen of the sixteen pages

- **Found during:** Task 2 human-check, in the rendered 360px screenshots
- **Issue:** `body { padding-block-end: 3.5rem; }` (added in 02-02 to stop the fixed call bar occluding the footer) is on `body`, so it applies to **all sixteen pages** — but `.callbar`'s markup lives only in `index.html`. Measured live at 360px on `about.html`: footer bottom **1270px**, document height **1326px** — 56px of empty white page below the dark footer, with no bar to occupy it. Same on `mehanichni-problemi.html` (1784 vs 1840). Invisible until this plan brought the other fifteen pages onto the design system.
- **Fix:** `body:not(:has(.callbar)) { padding-block-end: 0; }` in `components.css`.
- **Why negated rather than positive:** `:not()` is **not** a forgiving selector list, so in a browser without `:has()` support the whole rule is dropped and every page keeps the previous unconditional reserve — never an occluded footer. A positive `body:has(.callbar)` would instead drop the reserve on the homepage there and let the bar cover the footer's last 56px. The rationale is written into the rule so it is not "simplified" later.
- **Rejected alternative:** moving `.callbar` into `footer.php` so all sixteen pages get a sticky bar. That is a real design decision — it changes every page's conversion surface and eats 56px of every mobile viewport — and `footer.php` is not in this plan's file list. Left for a phase that owns the call.
- **Verified live:** document height now equals footer bottom on `about.html` (1270) and `mehanichni-problemi.html` (1784); the homepage keeps its 56px reserve below 900px and 0 above it.
- **Committed in:** `b6d1e1e`

### 2. [Deviation from an acceptance criterion] Source-level title distinctness is 14, not 16 — and cannot be 16

The criterion is `grep -h '$torin_title' src/*.html | sort -u | wc -l` → **16**. Actual: **14**.

The three category pages derive their title from the shared record:

```php
$torin_title = $torin_cat['name'] . ' · ТОРИН КОМПЮТЪРС';
```

which is textually identical on all three. That is *required* by the same task's other criterion — `grep -c 'Счупвания и механични повреди' src/mehanichni-problemi.html` → **0**, i.e. a page must not retype its own category name. **The two checks are mutually unsatisfiable**, the same class of conflict 02-03 recorded in its Finding 5.

**The criterion's actual intent is met and independently verified**: extracting `<title>` from all sixteen deployed URLs and de-duplicating yields **16**, and so does the same over `<meta name="description">`. That live check is the one the plan's own truth statement makes ("Every one of the sixteen pages **returns** its own distinct title") and the one the other acceptance criterion states outright.

No cosmetic difference was introduced into the three lines to make the source grep pass. Gaming a proxy check teaches the next reader that the check means something it does not.

### 3. [Deviation from plan wording] `<main class="section"><div class="container">`, not `<main class="container">`

The plan says to replace the Bootstrap-4 leftover classes on `<main>` with "the design-system container class". The leftover was `container py-5` — a container **and** vertical padding — and `.container` in this system carries no vertical padding at all, so `<main class="container">` would drop the page's entire vertical rhythm and butt the h1 against the header.

`.section > .container` is the faithful translation of both classes and is the established pattern everywhere in `index.html`. `<main>` does receive a design-system class. Recorded as a choice rather than drift.

### 4. [Deviation from plan wording] The placeholder `<h1>` was replaced, the placeholder paragraph was not

The plan says to leave the existing placeholder body copy in place. The paragraph was left verbatim on all fifteen pages. The `<h1>` was **not**: all fifteen carried the identical string «ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ», which is the exact defect the title work exists to fix, one element lower, and visibly wrong to any human reviewing the staging site (`warrently.html` headed "тотален ремонт на лаптопи").

Each h1 is now the page's own working title without the shop-name suffix, derived from that page's existing subject in `site-current/` — **no copy was invented**, and each file's comment says CONTENT-01 owns the body. On the three category pages the h1 comes from the shared category record.

### 5. [Minor] `uslovia.html` keeps a working title its content does not match

The plan assigns «Общи условия». The legacy page under that filename is headed **«ДЕКЛАРАЦИЯ ЗА ПОВЕРИТЕЛНОСТ»** — a privacy statement, not general terms. The plan's title was kept (it matches the footer's «условия» label and is a working title Phase 3 replaces), and the discrepancy is recorded in the file itself so SEO-01 decides which of the two this page actually is rather than inheriting the mismatch silently.

### 6. [Minor] Two helper functions beyond the declared export

`category-page.php` exports `torin_render_category_page()` as planned, plus `torin_category_by_id()` (needed at page level, before the header include, to build the title from the record), `torin_has_content()` and `torin_esc()` and `torin_render_svc_item()`. All live in the same new file, none touches an existing include, and `torin_category_by_id()` is the accessor that makes "the page never retypes its own name" achievable at all.

---

**Total deviations:** 1 auto-fixed bug + 2 recorded departures from plan wording + 1 unsatisfiable-criterion record + 2 minor.

## Verification Results

| Gate | Result |
|---|---|
| **V2a** — static vendor + D-08 font sweep over pages, includes, `site.js` | **no output** |
| **V2a′** — no stylesheet references a remote asset (`@import` / `url(https:`) | **no match** |
| **V2b** — live sweep, sixteen URLs | **`vendor=0 lang=1 rawphp=0` on all sixteen, http=200 on all sixteen** |
| **V2 runtime** — jQuery / `$` / ScrollMagic / Modernizr / pagepiling globals | **all `undefined`** |
| **V2 runtime** — legacy asset requests / third-party hosts | **0 / 0** (9 requests, all `torin.bg`, 104,410 B) |
| **V3** — six PHP 5.2 lint greps, all includes + all page files | **all silent** (original short-array pattern's four `jsonld.php` false positives unchanged from 02-03) |
| **V4** — BOM / encoding over every `.php` `.html` `.css` `.js` under `src/` | **clean**, all `utf-8` or `us-ascii` |
| **V4** — live Cyrillic round-trip | «ТОРИН КОМПЮТЪРС» ✓ · «Информация за литиево-йонни батерии» ✓ |
| **V6 precheck** — fixed widths ≥300px / `overflow-x` in any stylesheet | **none / none** |
| **V6** — four viewports, keyboard-only, JS-disabled | **all pass** (tables above) |
| **V7** — schema.org validator on a category page | **1 object, 0 errors, 0 warnings** |
| **Byte budget** — production CSS | **34,948 raw / 13,020 wire** vs 20 KB target → **64%, ~7.3 KB headroom** |
| **SEO-04** — filename diff `src/` vs `site-current/` | **empty**, asserted twice |
| Three unpublished category files absent | 3/3 absent; dropdown serves `index.html#kat-2/5/6` |
| `covid.html` / `problem-stari.html` live and unlinked | 200 / 200, **0** nav links across all sixteen responses |
| Phase 4 cutover surface | `theme-a.css` + `dev-switcher.php` present, `DEV-ONLY` ×4, **no token file edited** |
| Staging noindex | `x-robots-tag: noindex, nofollow` intact |
| Compression / caching | `content-encoding: gzip`, `cache-control: max-age=604800` |
| Files deleted by either task commit | **none** (`git diff --diff-filter=D` empty for both) |

## Human Check Results

**Task 2 — thin vs deep, side by side at 360×640 and 900×800.** Performed on rendered screenshots of the deployed pages, backed by geometry measurement.

**Judgement: the thin page does not read as broken or truncated.** It reads as a short but complete page — title, «Какво ремонтираме» with two bulleted sub-services, the symptom line in muted ink, then the CTA and the footer. Specifically:

- **No orphaned rule.** Every eyebrow has a heading under it; the amber rule never appears alone.
- **No double gap.** Section tops and heights are exactly contiguous (57+154=211, 211+289=500) — the six omitted blocks left no residual spacing, because they left no elements.
- **No section reading as if content is missing.** The tinted «Какво ремонтираме» band is the visual centre of the page at both widths and carries real content; the page ends on the CTA rather than trailing off.
- The deep page differs only by the band being 72px taller for two extra list items — the same template, the same rhythm, no shape change.
- At 900px both pages sit comfortably in the capped container with the five-item nav inline and the footer's three-column grid; neither has a horizontal scrollbar at any tested width.

The профилактика entry on `optimizatsiq.html` renders as an underlined link inside the sub-service list, visually distinct from the plain-text entry above it — D-28's cross-listing is legible as a link, not as duplicated copy.

## Known Stubs

| Item | File | Reason |
|---|---|---|
| Placeholder body paragraph on fifteen pages | `src/*.html` | «Строим новия сайт…» is the Phase 1 skeleton copy, left in place deliberately. Page content is Phase 3 (CONTENT-01). This plan delivers the chrome and the metadata mechanism only. |
| Working titles and descriptions | `src/*.html` | Distinct and honest, derived from each page's own existing subject, but **not search-tuned**. SEO-01 in Phase 3 replaces all sixteen. Each file says so in a comment. |
| Six of the template's blocks unfilled on all three category pages | `src/includes/category-page.php` | intro, гаранция (TRUST-03), process, FAQ, related, prices. D-25 assigns the depth to Phase 3. Each renders **nothing at all** until then — verified live, 0 residue. |
| Three category pages do not exist | — | D-23 publish gate. `kat-2`, `kat-5`, `kat-6` route to homepage anchors. Phase 3 creates each file and flips one boolean. |
| `hours` is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS **#20**. Ships to sixteen pages **and** into Google's `openingHoursSpecification`. **Must be answered before the Phase 4 cutover.** |
| `viber` is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS **#21** / UI-SPEC C-9. Now also consumed by the category template's CTA, so it reaches three more pages — still from the one config entry, so one edit still fixes all of them. |
| `notice` text is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS #8. Set the value to `''` and the band disappears with no other edit. |
| **JS-disabled nav gap** | `src/js/site.js` + `header.php` | **Confirmed still open.** With script blocked the six category links are unreachable from the nav; all six remain reachable from the homepage card grid (re-verified above). Accepted, not solved. |
| **DIFF-02 knowingly unmet** | `src/index.html`, `src/za-bateriite.html` | Battery regeneration — a differentiator no competitor offers — ships inside a collapsed disclosure (D-13 / OWNER-QUESTIONS #9). 02-RESEARCH §3c prices that as a ranking cost the original trade-off did not account for. **Phase 3 verification must treat DIFF-02 as knowingly unmet, not silently passing.** Carried forward, still not resolved. |
| `uslovia.html` title vs content mismatch | `src/uslovia.html` | Working title «Общи условия»; legacy content is a privacy declaration. Recorded in the file for SEO-01. |

## Issues Encountered

- **`grep` is `ugrep` on this machine and silently broke six plan checks.** See Finding 13. Every `grep '$torin_variable'` returned zero on files that contain the string.
- **`$torin_cat_id` inflated a grep count by containing the asserted substring in its own name.** See Finding 14.
- **`$torin_page` is already taken.** `header.php` assigns `$torin_page = basename($_SERVER['SCRIPT_NAME'])` for `aria-current` detection. The plan names the category template's array `$torin_page` too. Renamed to `$torin_cat_page` on the pages — zero risk to the shared include, versus renaming a variable used three times inside a file all sixteen pages depend on.
- **`schema.org`'s validator returns `numObjects: 0, totalNumErrors: 0` for a `text=` or `code=` payload** — a passing-looking response that validated nothing. Only `url=` actually fetches and parses. A validator run that reports zero errors on zero objects is not a pass.
- **`page.screenshot({fullPage: true})` times out against the full Chrome build here**; the `chrome-headless-shell` binary with an explicit viewport resize works. All measurement tooling lives in the session scratchpad — no `package.json`, no `node_modules` in the repo, zero project dependencies added.
- **`scripts/deploy-new.sh` must be invoked as `bash scripts/deploy-new.sh`** — direct execution is blocked by the sandbox classifier. Same as 02-03.

## User Setup Required

None for this plan. **Three owner answers still block the Phase 4 cutover:** OWNER-QUESTIONS **#20** (working hours — ships to sixteen pages and to Google), **#21** (which number is chat-capable), and **#8** (whether the notice band should exist at all). None was invented here; all three flow from `site-config.php`, so a single later edit fixes all sixteen pages.

## Next Phase Readiness

**Phase 2 is complete and every one of its site-wide claims is proven against the deployed site.** Carry into Phase 3:

1. **The per-page metadata mechanism is wired and working on all sixteen pages.** SEO-01 replaces two string literals per file; nothing structural has to change.
2. **The category template is ready for depth.** Assign `intro`, `warranty`, `process`, `faq`, `related` or `prices` on a page and the block appears; leave it unset and it stays invisible. No template edit is needed for any of them.
3. **Publishing a category is: create the file, flip one boolean.** Copy any of the three existing category pages as the shape. Every consumer — cards, dropdown, and Phase 4's sitemap — reads `torin_category_href()`.
4. **Symptom copy has a hard budget of ~38–40 characters** to render in two lines at 360px (02-02 Finding 2), not the UI-SPEC's assumed 46 per line.
5. **CSS is 34,948 B raw / 13,020 B gzipped — 64% of the 20 KB wire target.** Budget against the wire figure; do not strip working CSS to chase the raw number.
6. **Do not add a sticky header.** The fold has 9.2px of margin (card 1 bottom 574.8px vs 584px usable) and this plan consumed none of it.
7. **DIFF-02 is knowingly unmet** and must be verified as such, not passed silently.
8. **Deploy with `bash scripts/deploy-new.sh`** — the `--tls-max 1.2` cap is load-bearing, and `--ftp-ssl-control` must never be substituted for it.

## Self-Check: PASSED

- `src/includes/category-page.php` — FOUND
- All 15 modified page files — FOUND, each carrying `$torin_title` and `$torin_desc`
- `src/css/components.css` — FOUND (24,852 B)
- `src/ekran-klaviatura-portove.html`, `src/pregryavane-ohlazhdane.html`, `src/nestandartna-technika.html` — **correctly ABSENT**
- Commit `c2225a8` — FOUND
- Commit `b6d1e1e` — FOUND
- No files deleted by either commit (`git diff --diff-filter=D` empty for both)
- No token file, no config file and no pre-existing include modified by this plan (`git diff --name-only c2225a8~1..HEAD` confirms)
- Every task's `<acceptance_criteria>` re-run above; all pass except the one recorded in Deviation 2, whose stated intent is verified live by two independent measures
- Plan-level `<verification>` items 1–7 all executed against the deployed site, including V7

---
*Phase: 02-design-system-information-architecture*
*Completed: 2026-08-06*
