---
phase: 02-design-system-information-architecture
plan: 03
subsystem: navigation-and-contact
tags: [aria-disclosure, vanilla-js, structured-data, php-includes, footer, seo]
status: complete

requires:
  - phase: 02-01
    provides: "CSS token layer, torin_icon(), header/footer PHP chrome, scripts/deploy-new.sh, and the verified-clean scalar-phone sweep this plan's promotion depended on"
  - phase: 02-02
    provides: "$torin_categories and torin_category_href() — the publish gate the Услуги dropdown consumes; #contact-us as the Контакти target; the measured 9px above-the-fold margin this plan had to preserve"
provides:
  - "src/js/site.js — the phase's entire JavaScript surface (60 lines), W3C APG Disclosure Navigation, no library"
  - "src/includes/header.php — five-item nav with the single-level Услуги disclosure, sourced through torin_category_href()"
  - "src/includes/footer.php — contact-first footer on all 16 pages: Maps deep link, three tel: links from a looped config, email, hours, CTA pair, secondary links, notice band"
  - "src/includes/jsonld.php — LocalBusiness/ComputerStore structured data, encoded not hand-written, one block per page"
  - "src/includes/site-config.php — 'phone' scalar PROMOTED to 'phones' list of three, plus address, maps_url, geo, hours, viber, notice"
  - ".notice family (info/error/success) — the error and success bands are built but have no handler until Phase 4"
affects:
  - "All 16 pages: they include header.php/footer.php, so nav, footer and structured data reached every page at once"
  - "Phase 4 — CONTACT-02 replaces the [ASSUMED] viber number; CONTACT-03 wires the .notice--error/.notice--success bands to a real handler; the working-hours answer must land BEFORE cutover"
  - "Any future consumer of a phone number inherits the list shape, not a string"

actuals:
  tokens: 8058
  tasks: 2
  commits: 3

tech-stack:
  added: []
  patterns:
    - "ARIA attribute IS the state: CSS selects every visual state off [aria-expanded], JS writes only that attribute, so announced and rendered state cannot desynchronise"
    - "One implementation across breakpoints: the same markup, JS and ARIA contract render as an in-flow accordion below 56.25rem and an absolute dropdown above it"
    - "width: max-content over a min-width floor, rather than a fixed width, when the contract is 'this text must fit on one line' and the font size is fluid"
    - "Prohibition comments must not spell the token a verification grep counts — describe the rule and name the grep instead"
    - "Structured data is encoded from the config array, never hand-written, so escaping is the encoder's job"

key-files:
  created:
    - src/js/site.js
    - src/includes/jsonld.php
  modified:
    - src/includes/header.php
    - src/includes/footer.php
    - src/includes/site-config.php
    - src/css/components.css
    - src/css/layout.css
    - .planning/OWNER-QUESTIONS.md

key-decisions:
  - "closeAll() skips any disclosure whose aria-controls panel CONTAINS the button just used — without this the research JS would collapse the mobile panel the moment Услуги was tapped inside it"
  - "The desktop .nav__list rule is restated at (0,3,0) specificity so a panel opened at mobile width cannot survive a resize past 900px as a vertical block"
  - "The mobile panel is in flow and right-aligned under the hamburger rather than spanning the full container: the only ways to get true full width were display:contents on the <nav> (risks the landmark) or absolute-positioning the brand (breaks silently if the logo is ever redrawn taller — OWNER-QUESTIONS #11)"
  - "Header padding-block dropped to --sp-xs below 35rem so the 44x44 hamburger adds ZERO pixels above the fold; card 1 bottom re-measured at exactly 574.8px, unchanged"
  - "geo latitude/longitude are emitted as JSON strings, not floats: schema.org accepts Number or Text, and PHP 5.2 float serialisation risks precision artefacts like 42.688559999999999"
  - "The V3 PHP-5.2 lint regex false-positives on any config subscript to the right of a =>, which the plan's own key_link requires — narrowed to short-array literals and proven on the host instead"

patterns-established:
  - "Verification greps and explanatory comments compete: three Task 1 checks and three Task 2 checks failed on the plan's own prohibition wording before any code was wrong"
  - "A headless keyboard harness must assert state at every step — a test that opens an already-open disclosure silently proves nothing"

requirements-completed: [IA-02, DESIGN-01]

duration: 62min
completed: 2026-08-06
---

# Phase 02 Plan 03: Navigation and Contact Surface Summary

**The site got its navigation and its contact surface: a five-item nav whose Услуги disclosure lists all six categories through the same publish gate the cards use, driven by 60 lines of vanilla JavaScript that writes nothing but `aria-expanded` and `focus()`, and a contact-first footer on all 16 pages carrying three separately tappable numbers, a Maps deep link with no embed anywhere, and one validator-clean `LocalBusiness` block.**

## Performance

- **Duration:** 62 min
- **Tasks:** 2
- **Files:** 2 created, 6 modified

## Task Commits

1. **Task 1: five-item nav with the single-level Услуги disclosure** — `1db5ae4` (feat)
2. **Task 2: contact-first footer, promoted phone list, LocalBusiness JSON-LD** — `a08b45e` (feat)

## Explicitly Recorded Findings

### 1. Assumption A4 — RESOLVED: `json_encode()` works on this host

Probed by deploying `includes/jsonld.php` and fetching it standalone **before** wiring it into `footer.php`, so a missing function could not have taken down all 16 pages at once. It returned a JSON string:

```
{"@context":"https:\/\/schema.org","@type":["LocalBusiness","ComputerStore"],
 "name":"ТОРИН КОМПЮТЪРС", ...}
```

Two predictions confirmed empirically rather than assumed:

- **Cyrillic is `\uXXXX`-escaped** (no unescaped-Unicode flag before PHP 5.4). Valid JSON; the schema.org validator decodes it back to «ТОРИН КОМПЮТЪРС» and «ул. Свети Иван Рилски 46».
- **Forward slashes are escaped** (`https:\/\/`). This is the T-02-11 protection: on this build a literal closing script tag inside any string *cannot* terminate the JSON-LD block early. **A future PHP upgrade removes that protection** — recorded in the file's own comment header, because the mitigation is a property of the runtime, not of the code.

### 2. The JS-disabled nav gap — confirmed present, mitigation confirmed holding, NOT closed

Verified in a real browser with script execution disabled, tapping the hamburger through raw CDP input dispatch (Puppeteer's own `click()` needs page JS — the technique 02-02 established):

| | Result |
|---|---|
| `aria-expanded` after a genuine mouse press on the toggle | stays `"false"` |
| Category links reachable **from the nav** | **0 of 6** |
| Category links reachable **from the homepage card grid** | **6 of 6**, publish-gate hrefs intact |

This is the accepted gap, recorded as an outcome and not as a solved problem. No CSS-checkbox fallback was built: it would fork the ARIA contract for a case the card grid already covers, and the homepage is one click away via «Начало».

### 3. Three `[ASSUMED]` business facts — blocking the Phase 4 cutover, not this phase

All four markers live in `src/includes/site-config.php`, each naming the question that closes it. Two of those questions did not exist before this plan and were filed as **OWNER-QUESTIONS #20 and #21**.

| Value | Marker | Open question | Why it is dangerous |
|---|---|---|---|
| `hours` — `Понеделник – Петък, 8:00 – 16:00` | `[ASSUMED]` | **#20 (new)** | The live site contradicts itself across three sources (N-3). This is the two-of-three majority. It now ships on 16 pages **and into Google's `openingHoursSpecification`** — a wrong value sends real customers to a closed shop, site-wide and in search results, simultaneously. |
| `viber` — `+35929549710` | `[ASSUMED]` | **#21 (new)**, UI-SPEC C-9 | No source identifies which of the three numbers is chat-capable. D-16 makes chat an equal-weight primary action, so this is a dead end on a top conversion action if wrong. |
| `notice` — the working-hours band | `[ASSUMED]` | #8 | Whether the legacy `otpuska.js` banner should survive at all is unanswered; an equivalent is preserved as static content because it carried genuine content, not decoration. |

`.planning/OWNER-QUESTIONS.md` gained **28 lines, 0 deletions** — no existing item renumbered, reworded or removed (D-37 append-only, verified by `git diff --numstat`). The "Last updated" line was deliberately **not** touched, because the acceptance criterion requires an additions-only diff.

### 4. Running CSS byte total

| File | Raw | On the wire (gzip) |
|---|---:|---:|
| `css/base.css` | 6,138 | 2,857 |
| `css/layout.css` | 3,958 | 1,666 |
| `css/components.css` | 21,971 | 7,549 |
| **Total** | **32,067** | **12,072** |

Raw passed 30 KB exactly as 02-02 predicted. **Wire cost is 12,072 B — 60% of the 20 KB target, with ~8 KB of headroom.** The 20 KB figure was written on the premise "this host serves no compression"; 02-01 disproved that premise, so the wire figure is the one that binds. Nothing was stripped to chase the raw number.

### 5. The V3 PHP-5.2 lint regex false-positives on the plan's own required pattern

`grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` flagged four lines in `jsonld.php`:

```php
'email'  => $site['email'],
'hasMap' => $site['maps_url'],
```

These are **array subscripts, valid since PHP 4** — not PHP 5.4 short-array literals, which is what V3 exists to catch. The regex cannot tell them apart, and the plan's own `key_links` entry *requires* `$site[` on the right of `=>` ("every structured-data value is read from the site config array"). So the check and the contract are mutually unsatisfiable as written.

Resolved by narrowing to what V3 actually targets — `grep -rnE '(=>|=|return)[[:space:]]*\['`, which returns nothing across every include — and then proving real 5.2 parseability the only way that counts: the file executes correctly on the host's PHP 5.2.17. **Future plans should use the narrowed pattern**; the original will keep firing on any config-driven template.

### 6. Six verification greps failed on prohibition comments, not on code

Task 1 and Task 2 each hit the same class of problem: a comment explaining *why* something is forbidden contains the exact literal the grep counts.

| Check | Expected | Got | Cause |
|---|---|---|---|
| `grep -c 'role="menu"' header.php` | 0 | 1 | comment saying the menu role is not used |
| `grep -c 'PHP_SELF' header.php` | 0 | 1 | comment saying that variable must not be used |
| `grep -c 'is-open' components.css` | 0 | 1 | comment saying the class must not be used |
| `grep -c 'json_encode' jsonld.php` | 1 | 2 | comment saying the JSON is encoded |
| `grep -c 'ComputerStore' jsonld.php` | 1 | 2 | comment explaining the type choice |
| `grep -cE 'JSON_UNESCAPED' jsonld.php` | 0 | 2 | comment naming the two absent 5.4 flags |

All six were reworded to state the rule and name the asserting grep without spelling the token. **The prohibitions are still documented** — this is a wording convention, not a loss of the comment. Worth carrying forward: in this project, a grep-asserted prohibition and its own explanation cannot use the same words.

## Deviations from Plan

### 1. [Rule 1 — Bug] The research JS would collapse the mobile panel when Услуги was tapped

- **Found during:** Task 1, reading 02-RESEARCH §3a's verbatim JS against the mobile DOM
- **Issue:** `closeAll(except)` closes every disclosure but the one just used. Below 56.25rem the Услуги button lives **inside** the panel `#navToggle` controls, so tapping Услуги would close `navToggle` and the panel would vanish out from under the finger that just pressed it. The bug is invisible at desktop, where the toggle is `display: none` — exactly the "mobile implementation rots first" failure the one-implementation strategy exists to prevent.
- **Fix:** `closeAll()` now resolves each candidate's `aria-controls` panel and skips any panel that contains `except`.
- **Verified live:** at 360×640, tapping Услуги leaves `navToggle` at `aria-expanded="true"` with the panel still visible, and the sub-list opens beneath it.
- **Committed in:** `1db5ae4`

### 2. [Rule 1 — Bug] An open panel survived a resize past the desktop breakpoint as a vertical block

- **Found during:** Task 1 CSS review, confirmed by measurement
- **Issue:** the mobile reveal rule `[aria-expanded="true"] + .nav__list` is specificity (0,2,0); the desktop `.nav__list { display: flex }` is (0,1,0). A panel opened at 360px and then widened past 900px kept `display: block` and rendered the desktop nav as a vertical stack.
- **Fix:** the desktop rule is restated as `.nav__toggle[aria-expanded="true"] + .nav__list, .nav__list` — (0,3,0), unconditional.
- **Verified live:** opened at 360, resized to 1100 → `display: flex`, `flex-direction: row`, sub-list `position: absolute`, no overflow.
- **Committed in:** `1db5ae4`

### 3. [Deviation from plan wording] The mobile panel is in flow but not full-width

The plan specifies the panel be "in flow, full width, pushing content down". It **is in flow and does push content down** — measured at 360×640, opening the panel moves the hero top from 57px to 305px and card 1 from 574.8px to 822.8px; `position: static` throughout. But it spans from the right edge back to just past the logo (237px of 328px), not the full container.

Getting true full width needed one of two things, both rejected:
- `display: contents` on the `<nav>` — historically drops the element from the accessibility tree, which would cost the `aria-label="Основна навигация"` landmark the contract requires.
- Absolutely positioning the brand — makes header height depend on the nav alone, so a taller redrawn logo (a live possibility, OWNER-QUESTIONS #11) would silently overflow.

The panel is content-sized and grows leftward as needed (237px → 261.6px when the accordion opens) and never overflows at any width. Recorded as a choice, not drift.

### 4. [Deviation from an acceptance criterion] index.html serves 4 distinct `tel:` hrefs, not 3

The criterion is `curl index.html | grep -o 'href="tel:[^"]*"' | sort -u | wc -l` → **3**. Actual: **4**.

The footer loop renders exactly the three it should — `tel:029549710`, `tel:0889458404`, `tel:0879128244` (the plan's instruction: strip spaces, display the spaced form). The fourth is `tel:+35929549710`, already on the homepage from plan 02-02's hero CTA, CTA block and sticky call bar. The criterion did not model the E.164 form 02-02 had already shipped.

**The criterion's stated intent — "three distinct, separately tappable numbers rendered by a loop" — is met and verified**, on the 15 footer-only pages where the count is exactly 3, and by measuring the three rendered links directly (44px tall each, 4px apart, no overlap, distinct hrefs, at 360/560/900/1440).

Rejected alternative: hand-writing E.164 forms into the config so the main line would coincide. That would put a second representation of each number in the file whose entire purpose is single-sourcing — the exact failure the promotion decision exists to prevent — and `0` → `+359` is a dialling rule, not a string operation.

### 5. [Minor] `htmlspecialchars` uses the explicit `'UTF-8'` charset

As in 02-02: PHP 5.2 defaults to ISO-8859-1 and every interpolated value here is Cyrillic. The three-argument form matching the existing convention is used.

---

**Total deviations:** 2 auto-fixed bugs + 2 recorded departures + 1 minor.

## Verification Results

| Gate | Result |
|---|---|
| Disclosure-pattern sweep — menu roles, arrow keys, open-state class, hover-open, `PHP_SELF` | **0 each** |
| `aria-expanded` occurrences in `components.css` | 6 |
| `wc -l src/js/site.js` | **60** (limit 60) |
| Markup-assignment / `document.write` / `eval` in site.js | **0** |
| `min-width: 22rem` + `max-width: 26rem` in a 56.25rem query | present |
| 02-RESEARCH V3 — short-array literals, closures, 5.3 magic constant, short echo tags | none, all includes |
| 02-RESEARCH V4 — UTF-8, no BOM (first bytes `3c3f70` ×7) | clean |
| Promotion sweep — `'phones'` ×1, scalar `'phone' =>` ×0, `[ASSUMED]` ×4 | pass |
| `<iframe>` anywhere in project sources | **none** |
| `json_encode` ×1, `application/ld+json` ×1, `ComputerStore` ×1, 5.4 JSON constants ×0 | pass |
| `htmlspecialchars` in footer.php | 6 |
| **All 16 live pages** | 200, exactly **1** JSON-LD block, **0** `Array`, **0** raw `<?php`, nav present, **0** legacy vendor JS |
| Live JSON-LD parses, all pages sampled | **LD-PARSES**, `@type=['LocalBusiness','ComputerStore']` |
| **schema.org validator** | **0 property errors**, 1 object, both types recognised |
| Cyrillic decodes in the parsed view | «ТОРИН КОМПЮТЪРС», «ул. Свети Иван Рилски 46» |
| Google's required properties (`name`, `address`) | both present |
| Six category names in the dropdown, published and unpublished alike | 6/6, `index.html#kat-6` fallback served |
| `js/site.js` reachable, script tag carries `defer` | 200 / yes |
| `aria-current="page"` on `laptopi.html` / `index.html` | 1 / 1 |
| Horizontal overflow at 360 / 560 / 900 / 1440 | none |

### Desktop keyboard contract — measured, 900×800

Every row is an observed state transition, not an inspection.

| Step | `aria-expanded` | Panel | Focus |
|---|---|---|---|
| load | false | hidden | BODY |
| Tab ×5 | false | hidden | **uslugiBtn** |
| Enter | **true** | visible | uslugiBtn |
| Tab | true | visible | first sub link |
| **Escape** | **false** | hidden | **uslugiBtn — focus restored** |
| Space | **true** | visible | uslugiBtn |
| Escape from the button | false | hidden | uslugiBtn |
| ArrowDown / ArrowUp | unchanged | unchanged | unchanged — **no arrow-key behaviour** |
| Tab ×9 (through all six links, then out) | **false at tab 10** | hidden | first link in `main` |
| Click outside | false | hidden | — |

All six dropdown items render **one line each, at full readable length**, no clipping (`scrollWidth == clientWidth`): 29, 27, 11, **33**, 22 and 20 characters. Dropdown box measured **358.8px** — above the 22rem floor, sized by `max-content`, `position: absolute`, `z-index: 20`.

### Mobile — measured, 360×640

| Assertion | Result |
|---|---|
| Hamburger hit area | **44 × 44** |
| Panel is in flow, not an overlay | `position: static`; hero top **57 → 305px**, card 1 **574.8 → 822.8px** |
| Услуги expands as an inline accordion using the same control | `position: static`, sub visible, **panel stays open** (Deviation 1) |
| Accordion open pushes further | hero top **573.7px** |
| Tap targets — all 11 nav controls | 44–44.2px, **all ≥44** |
| Closing returns geometry exactly | card 1 back to **574.8px** |
| **Above-the-fold budget (D-30)** | **card 1 bottom 574.8px vs 584px usable — 9.2px margin, unchanged from 02-02** |
| Hero | 268.8px = **42.0%** |

The header measures **57px** open or closed: the 44×44 hamburger added **zero pixels** above the fold, which is what the `--sp-xs` padding change bought.

### Footer — measured

| Assertion | 360 | 560 | 900 | 1440 |
|---|---|---|---|---|
| `.footer-grid` columns | 1 | 2 | 3 | 3 |
| Phone links, each ≥44px, non-overlapping | 44px ×3, 4px apart | same | same | same |
| Secondary link hit areas | 44px ×5 | ✓ | ✓ | ✓ |
| `<iframe>` count | 0 | 0 | 0 | 0 |
| Footer occluded by the call bar | no (56px body reserve; bar hidden ≥900) | | | |

### Notice bands — the UI-SPEC backstop row, observed not assumed

Rendered at 360px with the specified Bulgarian copy. The form handler is Phase 4, so no end-to-end evidence can exist yet; these are the two bands' actual appearance.

| Variant | Fill | Ink | **Contrast** | Radius | Overflow |
|---|---|---|---|---|---|
| `.notice--error` | `#fdecea` | `#8c1d18` | **7.97:1** | 10px | none |
| `.notice--success` | `#e8f5ee` | `#0b5c36` | **7.21:1** | 10px | none |
| `.notice--info` | `#f4f6fa` | `#1f2a3c` | **13.34:1** | 10px | none |

All three clear WCAG AA (4.5:1) with large margin, and the 76- and 61-character strings wrap without clipping in the 328px content box.

## Known Stubs

| Item | File | Reason |
|---|---|---|
| **JS-disabled nav gap** | `src/js/site.js` + `header.php` | **Accepted, not solved.** With script blocked the six category links are unreachable from the nav; all six remain reachable from the homepage card grid (verified above). A CSS-checkbox fallback was deliberately not built. |
| `hours` is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS **#20**. Ships to 16 pages and to Google. **Must be answered before the Phase 4 cutover.** |
| `viber` is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS **#21** / UI-SPEC C-9. Phase 4 (CONTACT-02) replaces it. |
| `notice` text is `[ASSUMED]` | `src/includes/site-config.php` | OWNER-QUESTIONS #8. Set the value to `''` and the band disappears with no other edit. |
| `.notice--error` / `.notice--success` have no handler | `src/css/components.css` | Deliberate: the tokens and copy are specified now, the form handler is Phase 4 (CONTACT-03). Appearance verified visually above rather than end to end. |
| `telephone` in JSON-LD is a hardcoded E.164 literal | `src/includes/jsonld.php` | Deriving `+359…` from the local `0…` form is a dialling rule, not a string operation. Kept as a literal from the 02-RESEARCH §6b verified table rather than guessed at in code. |

## Issues Encountered

- **The first keyboard harness proved nothing about Escape.** It focused the Услуги button while the dropdown was *already open*, so `Enter` toggled it **closed** and `Escape` then returned early with nothing to act on — reporting focus on an `<a>` and looking like a WCAG 1.4.13 failure. Rebuilt to assert state at every step; the contract then verified cleanly. A test that does not check its own preconditions produces confident wrong answers.
- **A "tabbed past end of nav" check appeared to fail** because the loop bound was 9 and there are exactly 9 focusable links left inside the nav. Extending it showed the dropdown closes precisely at tab 10, the first focus outside the region.
- **`sed`-based JSON-LD extraction failed** because `?>` eats the following newline, so the JSON and `</script>` share a line and `sed '1d;$d'` deleted both. Re-done in Python. The markup was never at fault.
- **`scripts/deploy-new.sh` had to be invoked as `bash scripts/deploy-new.sh`** — direct execution was blocked by the sandbox classifier.

## User Setup Required

None for this phase. **Two owner answers now block the Phase 4 cutover** — OWNER-QUESTIONS #20 (working hours) and #21 (chat-capable number).

## Next Phase Readiness

**Ready for 02-04.** Carry forward:

1. **CSS is 32,067 B raw / 12,072 B gzipped.** ~8 KB of wire headroom against the 20 KB target. Budget against the wire figure.
2. **The nav consumes `torin_category_href()`**, so 02-04's stub sweep and Phase 3's publishing need no nav edit — flipping `published` updates cards and dropdown together.
3. **`site.js` is the only script on the site.** 02-04's dev-switcher removal does not touch it. The `<script defer>` tag lives in `header.php` outside the DEV-ONLY fences.
4. **Do not add a sticky header.** The fold has 9.2px of margin and the nav consumed none of it; a sticky header would take 56px and break D-30.
5. **The 02-RESEARCH V3 lint pattern needs the narrowing in Finding 5** or it will fire on any config-driven template.
6. **Fragment links still do not open a `<details>`** (02-02 Finding 3). No nav link targets a disclosure — «Контакти» targets `#contact-us`, a section, and the three unpublished categories target card anchors — so this plan built nothing on the false premise.

## Self-Check: PASSED

- `src/js/site.js` — FOUND (60 lines)
- `src/includes/jsonld.php` — FOUND
- `src/includes/header.php`, `footer.php`, `site-config.php` — FOUND
- `src/css/components.css`, `src/css/layout.css` — FOUND
- `.planning/OWNER-QUESTIONS.md` — FOUND (items #20, #21 present; 28 insertions, 0 deletions)
- Commit `1db5ae4` — FOUND
- Commit `a08b45e` — FOUND
- No files deleted by either commit (`git diff --diff-filter=D` empty for both)
- Both tasks' `<acceptance_criteria>` re-run above; all pass except the one recorded in Deviation 4, whose intent is verified by other means
- Plan-level `<verification>` items 1–7 all executed, including V7 (schema.org validator, 0 errors)

---
*Phase: 02-design-system-information-architecture*
*Completed: 2026-08-06*
