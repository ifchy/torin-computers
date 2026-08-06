---
phase: 02-design-system-information-architecture
plan: 06
subsystem: ui
tags: [css, progressive-enhancement, noscript, navigation, accessibility, aria, cascade]

requires:
  - phase: 02-design-system-information-architecture
    provides: "The three-file stylesheet system (base.css -> layout.css -> components.css) with the nav group and its two adjacent-sibling reveal rules; src/includes/header.php as the sole shared head for all sixteen pages; the proven edit -> FTPS deploy -> live re-fetch loop from 02-05"
provides:
  - "A fully navigable nav for user agents with scripting disabled — all five top-level items and all six category links, at every viewport, where the previously shipping behaviour below 56.25rem was ZERO nav links"
  - "src/css/no-js.css: a conditional fourth stylesheet, requested only via <noscript>, declaring display and position on five selectors that already exist in components.css and nothing else"
  - "A corrected inline record in header.php stating the real scope of the failure (the whole five-item list, not the six category links), naming the rule pair responsible, marking the <noscript> link load-bearing, and naming the one residual case"
  - "An accessible name on the category sub-list, so the six links keep their grouping when the disclosure button is hidden"
affects: [02-07, 03-content, 04-hardening-cutover]

actuals:
  tokens: 2100
  tasks: 2
  commits: 2

tech-stack:
  added: []
  patterns:
    - "A no-script fallback is delivered by <noscript> in <head>, never by shipping the enhanced-off state in markup and collapsing it with script — the latter has an inherent load-time transition, this has none by construction"
    - "The conditional override is emitted AFTER all unconditional stylesheets so cascade order stays link order; every rule in it is (0,1,0) and wins by documented source position, never by an importance flag, an id selector or a cascade layer"
    - "The override declares no selector for the disclosure state attribute: with scripting off there is no state, so introducing one would create a second source of truth invisible to anyone testing with scripting on"
    - "A control that announces a collapsed state is removed with display: none rather than left visible above an open panel — display: none takes it out of the accessibility tree, so nothing false is announced"

key-files:
  created:
    - src/css/no-js.css
  modified:
    - src/includes/header.php

key-decisions:
  - "<noscript> chosen over ship-open-and-collapse-with-JS: the latter has an inherent flash of an open nav collapsing on every one of sixteen page loads, and suppressing it needs a render-blocking inline capability marker in <head> — a third script element and a second writer of nav-adjacent state, exactly the dual-source-of-truth the project banned"
  - "<noscript> chosen over a <details>/:target rewrite: that moves the open state to [open] and requires a full rewrite of the verified 60-line site.js, whereas this leaves site.js byte-for-byte unchanged"
  - "The one honest weakness is registered rather than hidden: scripting ENABLED but site.js failing to load leaves the nav hidden below 56.25rem"
  - "Below 56.25rem the no-script nav renders IDENTICALLY to the panel the hamburger opens — same vertical list, same surface, same rows — so the degraded experience needs no separate visual contract and no second review. Cost accepted: a derived floor of ~476px of nav above the hero"
  - "The plan's prescribed 'flex: 1 0 100%' on the mid-list .nav__item--has-sub is implemented verbatim as specified and grep-asserted, but it does NOT produce the single horizontal row of top-level items the plan's own human-check wording expects — recorded as open, not absorbed into a pass"
  - "IA-02 deliberately NOT flipped to Complete: the rendered no-script checks are unrun, so asserting closure would be the exact false-verification failure this gap-closure pass exists to correct"

patterns-established:
  - "A recorded gap that understates itself is a defect in its own right — the correction ships in the same edit as the fix, because a later phase reading an understated record closes the wrong thing"
  - "Comment-vs-grep discipline: a plan-level grep that asserts a token absent from a file makes that token unwritable in that file's own comments, so the concept is named in prose instead (components.css:438-445 is the precedent this follows)"

requirements-completed: []

coverage:
  - id: N1
    description: "src/css/no-js.css exists and declares rules for exactly five selectors, all of which already exist in components.css; every selector is (0,1,0) with no descendant, id, or importance-escalated form"
    requirement: "IA-02"
    verification:
      - kind: other
        ref: "grep -c '^\\.nav__toggle { display: none; }' == 1; '^\\.nav__disclosure { display: none; }' == 1; '^\\.nav__list { display: block; }' == 1; '^\\.nav__sub { display: block; }' == 1; 'position: static' == 1; 'flex: 1 0 100%' == 1; brace balance 8/8"
        status: pass
      - kind: other
        ref: "each of nav__toggle/nav__disclosure/nav__list/nav__sub/nav__item--has-sub present in components.css (4/4/7/5/1 occurrences respectively)"
        status: pass
    human_judgment: false
  - id: N2
    description: "The override introduces no disclosure-state selector and no cascade escalation"
    requirement: "IA-02"
    verification:
      - kind: other
        ref: "grep -c 'aria-expanded' src/css/no-js.css == 0; '!important' == 0; '^#' == 0; '@layer' == 0; 'text-transform' == 0; 'max-width:' == 1 (the sub-list reset only); '@media (max-width' == 0; '@media (min-width: 56.25rem)' == 1"
        status: pass
      - kind: other
        ref: "curl -s 'https://torin.bg/new/css/no-js.css?v=<ts>' | grep -c 'aria-expanded' == 0 — asserted on the DEPLOYED bytes, not only the source"
        status: pass
    human_judgment: false
  - id: N3
    description: "The scripting-enabled rendering is provably unchanged: site.js remains the sole writer of the disclosure attribute and both reveal rules are intact"
    requirement: "IA-02"
    verification:
      - kind: other
        ref: "git diff --name-only fc189e6a93802216efcbe37dc4825e4852fc7551..HEAD -- src/js/site.js src/css/components.css == empty; git diff HEAD --name-only -- same paths == empty"
        status: pass
      - kind: other
        ref: "grep -c 'setAttribute' src/js/site.js == 1; line-anchored '^\\[aria-expanded=\"true\"\\] + \\.nav__list { display: block; }' == 1 and the .nav__sub equivalent == 1"
        status: pass
    human_judgment: false
  - id: N4
    description: "header.php emits the conditional link as one line at column zero, after all three unconditional stylesheets, with the corrected record present and PHP 5.2 safety intact"
    requirement: "IA-02"
    verification:
      - kind: other
        ref: "grep -cE '^<noscript><link rel=\"stylesheet\" href=\"css/no-js\\.css\"></noscript>$' == 1; awk line-order check prints ORDER-OK components=73 nojs=82; grep -c 'load-bearing' == 1"
        status: pass
      - kind: other
        ref: "grep -c 'aria-label=\"Услуги\"' == 1; 'echo htmlspecialchars(torin_category_href' == 1; 'torin_category_href' == 2 (call site + D-23 publish-gate comment); '__DIR__' == 0; '<?=' == 0; role=\"menu\" == 0; '<script' == 1; no BOM on either edited file"
        status: pass
    human_judgment: false
  - id: N5
    description: "All sixteen deployed pages serve the wiring, and SEO-02 does not regress"
    requirement: "SEO-02"
    verification:
      - kind: other
        ref: "Sixteen-row sweep: every page http=200 noscript=1 nojs=1 lang=1 php=0 with a distinct non-empty title. Full table in this summary."
        status: pass
    human_judgment: false
  - id: N6
    description: "The override asset serves live with the right bytes, and no page filename was added, renamed or retired"
    requirement: "SEO-04"
    verification:
      - kind: other
        ref: "curl 'https://torin.bg/new/css/no-js.css?v=<ts>' -> HTTP 200, len=5280; body contains 'nav__toggle { display: none; }' and 'nav__list { display: block; }' once each; base.css/layout.css/components.css/site.js all still 200"
        status: pass
      - kind: other
        ref: "diff of src/*.html basenames (less phptest.html) against site-current/*.html (less google*.html) is EMPTY"
        status: pass
    human_judgment: false
  - id: N7
    description: "With scripting disabled, all five top-level items and all six category links are visible and activatable at 360px, 900px and 1440px, and neither disclosure control is visible or Tab-reachable"
    requirement: "IA-02"
    verification: []
    human_judgment: true
    rationale: "No source read can observe a rendered nav or a focus order. No automatable browser exists on the build machine (no Chrome/Chromium/Edge, no Playwright, no Puppeteer, no Playwright browser cache; Safari remote automation is behind a GUI-only setting) — the identical blocker 02-05 hit. Routed to the end-of-phase UAT batch (workflow.human_verify_mode = end-of-phase). Recorded in .planning/WINDOWS.md as entry 8."
  - id: N8
    description: "With scripting disabled there is no horizontal scrollbar at 360px, 900px or 1440px — scrollWidth must not exceed innerWidth"
    requirement: "IA-02"
    verification: []
    human_judgment: true
    rationale: "Declared backstop. This plan CHANGES the desktop no-script nav layout, so the UI-SPEC 'overflow' row is RE-OPENED rather than inherited from its previous covered state. A rendered document width needs a browser with scripting disabled and cannot be derived from source. ABSTAINS to human_needed — explicitly NOT recorded as passing. Recorded in .planning/WINDOWS.md as entry 9."
  - id: N9
    description: "With scripting enabled, the nav behaves exactly as plan 02-03 verified, with no flash of an open nav collapsing on load at any width"
    requirement: "IA-02"
    verification:
      - kind: other
        ref: "Structural proof only: site.js and components.css carry a zero-file diff from the pinned baseline, and the <noscript> mechanism has no load-time state transition by construction (a scripting-capable UA never parses the element's contents, so the override is never requested)"
        status: pass
    human_judgment: true
    rationale: "The zero-diff proof is strong evidence the enabled path is unchanged, and the absence of a flash follows from the mechanism rather than from timing. The keyboard/visual confirmation itself still needs a browser. Routed to the end-of-phase UAT batch."

duration: 12 min
completed: 2026-08-06
status: complete
---

# Phase 02 Plan 06: No-Script Navigation (IA-02 / WR-08) Summary

**A conditional `<noscript>` stylesheet ships a fully navigable nav — five top-level items and all six category links, at every viewport — to visitors with scripting disabled, who previously got zero nav links below 56.25rem; the JavaScript-enabled path is proven byte-identical against a pinned baseline SHA; and the inline record that understated the failure is corrected in the same edit as the fix.**

## Performance

- **Duration:** 12 min
- **Started:** 2026-08-06T14:03:13Z
- **Completed:** 2026-08-06T14:15:00Z
- **Tasks:** 2
- **Files created:** 1 · **Files modified:** 1

## Pinned baseline (recorded so the untouched-path assertion stays auditable)

```
BASE_SHA = fc189e6a93802216efcbe37dc4825e4852fc7551
```

Captured with `git rev-parse HEAD` at wave start, **before Task 1 touched anything**. It resolves to `fc189e6 docs(02-05): complete CSS cascade defect closure plan (CR-01, CR-02)` — the final commit of plan 02-05.

Neither forbidden form was used. `HEAD~1` was not used (it would only have been meaningful had Task 1 produced exactly one commit, and this plan produced two). A commit-message `--grep` was not used; on this repository a `--grep='02-05'` resolves to `9b4cb36`/`f4273f5`, the **planning** commits for 02-05..02-07, which predate every line of executed work — a wrong baseline that the `:?` guard cannot catch, because an unset variable fires the guard but a wrong SHA passes silently.

## Task Commits

1. **Task 1 — conditional override stylesheet, wiring, corrected record** — `2262b5c` (fix)
2. **Task 2 — proof sweep and the residue record** — `d88f9b0` (docs; the sweep found no defect, so this task produces a ledger record rather than a source edit)

## Accomplishments

- **The real gap, closed.** Below 56.25rem `components.css:486` sets `.nav__list { display: none }`, and the *only* rules that reverse it are the two adjacent-sibling matches at `components.css:507-508` keyed on the disclosure state attribute — which `site.js` alone writes. With scripting blocked the **entire five-item navigation** was hidden, not merely the six category links. Only the logo and the five footer links remained. `no-js.css` reverses both hides unconditionally and removes the two controls that cannot function.
- **The controls are removed, and that is load-bearing.** Both the hamburger and the Услуги button carry a collapsed-state ARIA attribute in the served markup. Left visible above a list rendered open, they would announce the exact opposite of what a sighted visitor reads — confidently wrong, which is worse than absent. `display: none` takes them out of the accessibility tree, so nothing false is announced.
- **The desktop dropdown returns to the flow.** Left absolutely positioned it would be a permanently open floating panel overlaying page content on every desktop no-script page view. `position: static` plus a full-width row makes it an ordinary second nav row.
- **No load-time transition, by construction.** A scripting-capable user agent never parses the `<noscript>` element's contents as markup and never requests the override; a scripting-disabled one applies it from first paint. There is no timing involved, so there is no flash of an open nav collapsing on any of sixteen pages.
- **The mis-recording, corrected.** The old comment described a smaller failure than the one that shipped. That is a defect in its own right — a later phase reading it would have closed the wrong thing, which is precisely how this one survived a full code review.
- **The sub-list keeps its name.** When the disclosure button is hidden the word «Услуги» disappears with it, so the six links lose their grouping name. `aria-label="Услуги"` on the `<ul>` is correct in both renderings and costs one attribute with no conditional markup.

## `src/css/no-js.css` — complete text as shipped

```css
/* no-js.css — the navigation, for user agents with scripting disabled.
   Plan 02-06, closing the IA-02 gap that header.php had recorded as smaller
   than it actually was.

   REQUESTED ONLY WHEN SCRIPTING IS OFF. header.php wraps the link to this file
   in a noscript element: a scripting-capable user agent never parses that
   element's contents as markup and never fetches this file, while a user agent
   with scripting disabled applies it from first paint. There is no state
   transition on load either way, so no page ever flashes an open nav that then
   collapses.

   CASCADE POSITION IS THE WHOLE MECHANISM. The link is emitted AFTER all three
   unconditional stylesheets, so this file is last and cascade order is still
   link order. Every selector below is a single class — (0,1,0) — and beats its
   counterpart in components.css by that documented source position and by
   nothing else: no importance flag, no id selector, no cascade-layer construct.
   Move the link earlier in the head and these rules stop applying, silently.

   WHY THERE IS NO STATE SELECTOR HERE, AND WHY THERE NEVER MAY BE. With
   scripting off there is no state to select on — the nav is statically open.
   site.js stays the one and only writer of the disclosure state attribute for
   the scripting-enabled rendering, and components.css keys every visual state
   off that attribute, which is what makes the announced state and the rendered
   state incapable of disagreeing. That attribute's own name is asserted absent
   from THIS file by a plan-level grep, so it is not written out here — the same
   idiom components.css uses for the parallel open-state class it forbids. A
   mirror class, a data attribute, or a scripting-capability marker added later
   to "improve" this would re-create exactly the desynchronisation the nav
   contract exists to prevent, and would do it invisibly to anyone testing with
   scripting on.

   SCOPE. Display and position only, on five selectors that all already exist in
   components.css. No colours, no typography, no new class names, no new tokens.

   ACCEPTED COST, recorded rather than glossed. Below 56.25rem this renders the
   nav exactly as the panel the hamburger opens when scripting is on — same
   vertical list, same surface, same rows — so roughly eleven 44px rows sit
   above the hero. Deliberate: an identical shape needs no second visual
   contract and no second review, and these visitors currently get no navigation
   at all.

   RESIDUAL, NOT CLOSED HERE. If scripting is ENABLED but site.js fails to load
   or throws, this file is never requested and the nav stays hidden below
   56.25rem. Closing that would need a scripting-capability marker written
   before first paint, which this project deliberately does not have.        */

/* The two controls that cannot function without script are removed outright,
   and that is load-bearing rather than tidiness: each carries a collapsed-state
   ARIA attribute in the served markup, so leaving either visible above a list
   that is rendered open would announce the exact opposite of what a sighted
   visitor reads. display: none takes them out of the accessibility tree, so
   nothing false is announced.                                               */
.nav__toggle { display: none; }
.nav__disclosure { display: none; }

/* And the two panels those controls would have opened are simply open. At this
   width the sub-list is already static and already carries its own inset
   padding, so nothing further is needed.                                    */
.nav__list { display: block; }
.nav__sub { display: block; }

/* --- Breakpoint 2 — 56.25rem / 900px ------------------------------------
   The same value as components.css, reused verbatim, and min-width only —
   that is a convention of this stylesheet set, so no two adjacent breakpoints
   can both match or both miss.                                             */
@media (min-width: 56.25rem) {
	/* Restores the desktop horizontal row that the unconditional display: block
	   above would otherwise destroy for a no-script desktop visitor, and permits
	   the sub-list to wrap onto its own line. Only display needs restating —
	   every other desktop declaration on this list still comes from
	   components.css.                                                        */
	.nav__list {
		display: flex;
		flex-wrap: wrap;
	}

	/* Gives the Услуги item the full container width, so the six category links
	   have the whole row to lay out in and cannot push the nav past the
	   container.                                                             */
	.nav__item--has-sub {
		flex: 1 0 100%;
	}

	/* Returns the desktop dropdown to the document flow. Left absolutely
	   positioned it would be a permanently open floating panel overlaying page
	   content; in flow it is an ordinary second nav row. The existing
	   white-space: nowrap on desktop sub-links keeps each Bulgarian name on one
	   line, and the full-width row above is what makes that safe.            */
	.nav__sub {
		position: static;
		display: flex;
		flex-wrap: wrap;
		gap: 0 var(--sp-md);
		width: auto;
		min-width: 0;
		max-width: none;
		padding: 0;
		background: none;
		box-shadow: none;
	}
}
```

**Note on the head comment's own wording:** it says "roughly eleven 44px rows". The derived count is **ten** visible rows (four top-level links plus six category links) — the disclosure button is `display: none` in this rendering and contributes no row. The comment's figure is the plan's estimate, carried through; the corrected count is recorded here rather than silently left to stand. See the measured-cost section below.

## `src/includes/header.php` — the corrected record, verbatim as shipped

The conditional link and its position comment, emitted immediately after `css/components.css` and before the script tag:

```php
<link rel="stylesheet" href="css/components.css">
<?php // The conditional override (plan 02-06), requested ONLY by a user agent
      // with scripting disabled — a scripting-capable one never parses this
      // element's contents as markup and never fetches the file, so there is no
      // state transition on load and no flash of an open nav collapsing.
      // Emitted HERE, after all three unconditional stylesheets, so cascade
      // order is still link order and every (0,1,0) rule in that file wins its
      // target by source position rather than by any escalation. Its contents
      // are static markup; no PHP reaches inside it. ?>
<noscript><link rel="stylesheet" href="css/no-js.css"></noscript>
```

The corrected record itself, replacing the four-line comment that understated the gap:

```php
<?php // The site's ONLY script (plan 02-03). defer, so it never blocks the
      // parser and runs after the nav markup exists.
      //
      // CORRECTED RECORD (plan 02-06). This comment used to say that with the
      // script blocked "the six category links are unreachable from the nav".
      // That understated the failure. Below 56.25rem components.css hides the
      // WHOLE list, and the only rules that reveal it are the two
      // adjacent-sibling matches on the disclosure state attribute that this
      // script alone writes — so with scripting blocked the entire five-item
      // navigation was hidden, not merely the six category links. Only the logo
      // and the footer links remained. A later phase reading the old wording
      // would have closed the wrong defect, which is how this one survived a
      // full code review; a record that is wrong gets corrected, never dropped.
      //
      // It is closed by the conditional override linked immediately above. That
      // link is load-bearing and must NOT be deleted as a redundant fourth copy
      // of the three links preceding it.
      //
      // The guarantee is unaffected: this script remains the sole writer of the
      // disclosure state attribute, and the override touches only display and
      // position. It declares no selector for that attribute and introduces no
      // second source of truth, so the announced state and the rendered state
      // still cannot disagree.
      //
      // One residual case, named rather than omitted: if scripting is ENABLED
      // but this file fails to load or throws, the nav stays hidden below
      // 56.25rem. Closing that would require a scripting-capability marker
      // written before first paint, which this project deliberately does not
      // have. ?>
<script src="js/site.js" defer></script>
```

And the sub-list's accessible name, with its reason recorded beside it:

```php
						// The list carries its own accessible name because the
						// button above it is removed in the no-script rendering
						// (plan 02-06) — without it the six links would lose
						// their grouping name there. Correct in both renderings:
						// a named sub-list inside a named nav.
						?>
						<ul class="nav__sub" id="uslugiList" aria-label="Услуги">
```

The six links themselves are untouched: still looped from `$torin_categories`, still resolved through `torin_category_href()`, so no category filename is typed in this file and the nav and the cards cannot disagree about a destination (D-23).

## Sixteen-URL live sweep — verbatim

Run twice (Task 1 and Task 2). Identical both times; the Task 2 run with titles is reproduced.

```
index.html               http=200 noscript=1 nojs=1 lang=1 php=0 title=ТОРИН КОМПЮТЪРС - ТОТАЛ
about.html               http=200 noscript=1 nojs=1 lang=1 php=0 title=За нас · ТОРИН КОМПЮТЪР
laptopi.html             http=200 noscript=1 nojs=1 lang=1 php=0 title=Употребявани лаптопи ·
profilaktika-laptop.html http=200 noscript=1 nojs=1 lang=1 php=0 title=Профилактика на лаптоп
optimizatsiq.html        http=200 noscript=1 nojs=1 lang=1 php=0 title=Оптимизация · ТОРИН КОМ
mehanichni-problemi.html http=200 noscript=1 nojs=1 lang=1 php=0 title=Счупвания и механични п
za-bateriite.html        http=200 noscript=1 nojs=1 lang=1 php=0 title=За батериите · ТОРИН КО
tokov-udar.html          http=200 noscript=1 nojs=1 lang=1 php=0 title=Токов удар и захранване
zalivane-technosti.html  http=200 noscript=1 nojs=1 lang=1 php=0 title=Заливане и ремонт на дъ
rezervni-chasti.html     http=200 noscript=1 nojs=1 lang=1 php=0 title=Резервни части · ТОРИН
warrently.html           http=200 noscript=1 nojs=1 lang=1 php=0 title=Гаранционни условия · Т
uslovia.html             http=200 noscript=1 nojs=1 lang=1 php=0 title=Общи условия · ТОРИН КО
covid.html               http=200 noscript=1 nojs=1 lang=1 php=0 title=Проект BG16RFOP002-2.07
test-laptop.html         http=200 noscript=1 nojs=1 lang=1 php=0 title=Тествай сам своя лаптоп
problem-stari.html       http=200 noscript=1 nojs=1 lang=1 php=0 title=Чести проблеми · ТОРИН
msg.html                 http=200 noscript=1 nojs=1 lang=1 php=0 title=Съобщение · ТОРИН КОМПЮ
```

Sixteen rows, sixteen HTTP 200s, sixteen `<noscript>` elements, sixteen wired override links, sixteen `<html lang="bg">` declarations (the **SEO-02 non-regression guard** on its sole emitter), zero literal PHP open tags, and sixteen distinct non-empty titles.

## Verification Results

**1. Source assertions on `no-js.css` — all PASS**

| Assertion | Expected | Actual |
|---|---:|---:|
| `^\.nav__toggle { display: none; }` | 1 | **1** |
| `^\.nav__disclosure { display: none; }` | 1 | **1** |
| `^\.nav__list { display: block; }` | 1 | **1** |
| `^\.nav__sub { display: block; }` | 1 | **1** |
| `@media (min-width: 56.25rem)` | 1 | **1** |
| `position: static` | 1 | **1** |
| `flex: 1 0 100%` | 1 | **1** |
| `aria-expanded` | 0 | **0** |
| `!important` | 0 | **0** |
| `^#` (id selector at column 0) | 0 | **0** |
| `@layer` | 0 | **0** |
| `max-width:` | 1 (sub-list reset only) | **1** |
| `@media (max-width` | 0 | **0** |
| `text-transform` | 0 | **0** |
| brace balance | equal | **8 / 8** |
| BOM | absent | **absent** |

Every selector the override touches already exists in `components.css`: `nav__toggle` 4, `nav__disclosure` 4, `nav__list` 7, `nav__sub` 5, `nav__item--has-sub` 1. The override introduces **no new class name, no new token, no new page URL**.

**2. Untouched-path assertions — all PASS, from the pinned baseline**

```
baseline: fc189e6a93802216efcbe37dc4825e4852fc7551
git diff --name-only "$BASE_SHA"..HEAD -- src/js/site.js src/css/components.css   -> empty
git diff HEAD --name-only  -- src/js/site.js src/css/components.css               -> empty
```

Both forms were run, and they are complementary rather than redundant: `BASE_SHA..HEAD` compares commits and never inspects the working tree, while `git diff HEAD` (the `HEAD` form, not bare `git diff`) catches a staged-but-uncommitted edit as well. Both empty.

- `grep -c 'setAttribute' src/js/site.js` = **1** — still the sole writer.
- `^\[aria-expanded="true"\] + \.nav__list { display: block; }` = **1** (line-anchored; the unanchored count is 3 because of the desktop restatement at `components.css:813` and the comment above it, so an unanchored assertion would have been vacuous).
- `^\[aria-expanded="true"\] + \.nav__sub { display: block; }` = **1**.

**3. `header.php` wiring, ordering and PHP 5.2 safety — all PASS**

| Assertion | Expected | Actual |
|---|---:|---:|
| `^<noscript><link rel="stylesheet" href="css/no-js\.css"></noscript>$` | 1 | **1** |
| `load-bearing` (the corrected record exists) | 1 | **1** |
| `awk` line-order check | ORDER-OK | **ORDER-OK** (components=73, no-js=82) |
| `aria-label="Услуги"` | 1 | **1** |
| `echo htmlspecialchars(torin_category_href` | 1 | **1** |
| `torin_category_href` (call site + D-23 comment) | 2 | **2** |
| `__DIR__` | 0 | **0** |
| `<?=` | 0 | **0** |
| `role="menu` | 0 | **0** |
| `<script` (no script element added) | 1 | **1** |
| BOM | absent | **absent** |

The `<noscript>` element's contents are static markup with zero PHP interpolation.

**4. Deployed assets — all PASS**

- `https://torin.bg/new/css/no-js.css?v=<ts>` → **HTTP 200, 5,280 bytes**; body contains `nav__toggle { display: none; }` **once**, `nav__list { display: block; }` **once**, and `aria-expanded` **zero** times. Fetched with a cache-busting query string because the origin sends `cache-control: max-age=604800` on `text/css`. A `<noscript>` link pointing at a 404 would pass every source grep and fail silently for exactly the visitors it exists to serve — this is the assertion that rules it out.
- `css/base.css`, `css/layout.css`, `css/components.css`, `js/site.js` → all still **200**.
- Deployed homepage script sources: exactly one, `js/site.js`. Adding the conditional stylesheet added no script element.
- Deployed homepage nav shape: `class="nav__link"` **10** (four top-level + six categories), `class="nav__disclosure"` **1**, `aria-label="Услуги"` **1**, menu roles **0** — the D-18/D-19 shape IA-02 was verified against is unchanged.

**5. SEO-04 guard — PASS.** `diff` of `src/*.html` basenames (less the local-only `phptest.html`) against `site-current/*.html` (less the Google verification file) is **empty**. The new stylesheet is an asset, not a page.

**6. Rendered behaviour — NOT PERFORMED.** See Deviations and Residual Items.

## Measured cost of the mobile no-script shape — DERIVED, not measured

The plan requires a measured pixel figure for how much the permanently open nav adds above the hero at 360px. **No browser measurement was possible** (see Deviation 1), so this is derived arithmetically from the shipped CSS and is labelled as such — the same discipline 02-05 applied to its 241.6px hero figure, rather than asserting an unmeasured number as fact.

At 360px, `--gutter` resolves to `clamp(1rem, 4vw, 2rem)` → 4vw = 14.4px → clamped up to the 16px floor.

| Term | Source | px |
|---|---|---:|
| `.nav__list` `margin-block: var(--sp-sm)` | `components.css:489` | 8 + 8 = **16** |
| `.nav__list` `padding: var(--sp-sm)` | `components.css:490` | 8 + 8 = **16** |
| 4 visible top-level links × `min-height: 44px` | `components.css:521` | **176** |
| `.nav__disclosure` (hidden by the override) | `no-js.css` | **0** |
| 6 category links × `min-height: 44px` | `components.css:521` | **264** |
| `.nav__sub` `padding-block-end: var(--sp-xs)` | `components.css:500` | **4** |
| **Total added above the hero** | | **≈ 476** |

**This is a FLOOR, not a point estimate.** The 44px terms are `min-height`, and at 360px the text column inside a sub-list link measures 360 − 32 (gutter) − 16 (list padding) − 16 (sub-list inset) − 32 (link padding-inline) = **264px**, against `--fs-body` resolving to ≈17.0px. The longest Bulgarian category name, «Заливане и ремонт на дънни платки» (33 characters), very likely wraps to two lines there and would exceed 44px. The real figure is therefore **≥476px**, not exactly 476px.

**Ten visible rows, not eleven** — the head comment in `no-js.css` says "roughly eleven", counting the disclosure button, which this rendering hides. Corrected here rather than left to stand.

**Accepted** in exchange for the no-script rendering being byte-for-byte the same shape as the panel the hamburger opens: an identical shape needs no separate visual contract and no second review, and these visitors currently get **zero** navigation.

## Deviations from Plan

### 1. [Rule 3 — Blocking] Every rendered measurement in both `human-check` blocks is UNRUN

- **Found during:** Task 1 verification, and again at Task 2 step 4.
- **Issue:** Both tasks require browser measurements with scripting disabled — the hero `y` offset at 360px, `scrollWidth`/`innerWidth` at 900px and 1440px, the header focus order under Tab, and visual confirmation that all eleven links are visible and activatable at three widths.
- **Blocker (re-confirmed this run, not inherited):** `/Applications/Google Chrome.app`, `Chromium.app` and `Microsoft Edge.app` are all absent; no `chrome`/`chromium` on PATH; `require.resolve('playwright')` and `require.resolve('puppeteer')` both fail; `~/Library/Caches/ms-playwright` does not exist. Safari remote automation is behind a GUI-only setting. Identical to the blocker 02-05 documented.
- **Fix considered and rejected:** installing Playwright or Puppeteer for a headless Chromium. Rejected on the same grounds 02-05 rejected it — this plan's own threat model records `T-02-SC` as *"Zero packages installed, zero third-party runtime assets, no package manager involved"*, and the project deliberately carries no Node toolchain. Installing one to satisfy a measurement would be a larger deviation than the one it fixes.
- **Fix applied:** the mobile stack cost is recorded as an explicitly **DERIVED FLOOR** with its full arithmetic and its wrap caveat, never as a measurement. The `scrollWidth`/`innerWidth` backstop **abstains to `human_needed`** and is explicitly NOT recorded as passing, exactly as the plan requires. All rendered checks are routed to the end-of-phase UAT batch (`workflow.human_verify_mode = end-of-phase`) and recorded in `.planning/WINDOWS.md` as entries 8 and 9.
- **Files modified:** none.
- **Committed in:** `d88f9b0` (the ledger record).

### 2. [Rule 4-adjacent — recorded, NOT auto-fixed] The desktop row shape does not match the plan's own `human-check` wording

- **Found during:** Task 1, reasoning about the prescribed declarations before writing them.
- **Issue:** The plan mandates `.nav__item--has-sub { flex: 1 0 100%; }` and grep-asserts it. The `.nav__item--has-sub` list item sits **third of five in DOM order** (Начало, Услуги, Лаптопи и части, Тествай сам, Контакти). In a wrapping flex container a `flex-basis: 100%` item cannot share a line, so the desktop no-script nav resolves to **three rows**: `Начало` alone, then the six category links full-width, then `Лаптопи и части / Тествай сам / Контакти`. The plan's own `human-check` item 5 expects "the five top-level items still read as a horizontal row and the six category links sit on their own row beneath".
- **Why it was NOT auto-fixed:** every available fix is barred. Reordering with `order` would desynchronise visual order from focus order — a real accessibility defect, and worse than the cosmetic one. Moving the item in the markup would change the D-18 locked nav order. Adding any further declaration is forbidden by the plan's explicit *"Do not declare anything else"*. And dropping `flex: 1 0 100%` fails a grep assertion and re-opens the overflow risk that declaration exists to prevent.
- **What the plan's stated *intent* asks for, and gets:** *"gives the Услуги item its own full-width row, so the six category links have the whole container to lay out in and cannot push the nav row past the container width."* That is satisfied. The nav is fully navigable, entirely in flow, and nothing overlays page content.
- **Action taken:** implemented **verbatim as specified**, and the discrepancy recorded as **open** in `.planning/WINDOWS.md` (entry 11) and in Residual Items below — not absorbed into a pass. A cosmetic follow-up (e.g. moving Услуги last in D-18's order, which is a content/IA decision rather than a CSS one) belongs to whoever owns the nav order, not to a scope-fenced gap-closure pass.
- **Files modified:** none beyond the plan's prescription.

### 3. [Recorded, not auto-applied] IA-02 deliberately NOT flipped to Complete

- **Found during:** the requirements-update step.
- **Issue:** this plan is the mechanism half of IA-02's remaining gap, and every automated assertion passes.
- **Why it was not done:** the plan's own success criteria are stated in terms of *rendered* behaviour ("visible and activatable at 360px, 900px and 1440px"; "neither disclosure control is visible or focusable"), and every one of those observations is unrun (Deviation 1). Commit `abd5ba8` — *"revert premature Complete requirements after gaps found"* — reverted exactly this class of flip once already in this phase. Asserting Complete while this same summary documents unclosed rendered verification would be a false verification record, which is the failure mode this entire gap-closure pass exists to correct.
- **Action taken:** `REQUIREMENTS.md` deliberately untouched. Phase verification re-runs after 02-07 and owns the flip once the end-of-phase UAT batch lands.

---

**Total deviations:** 3 (1 blocking with a recorded workaround, 1 recorded-not-fixed, 1 recorded abstention).
**Impact on plan:** no scope change and no scope creep. Exactly the two files in `files_modified` were touched. `site.js`, `components.css` and all sixteen page files are provably untouched.

## Residual / Open Items — stated as OPEN, not absorbed into the pass

1. **Scripting ENABLED but `site.js` fails to load or throws** → the nav stays hidden below 56.25rem. This is the `<noscript>` approach's one honest weakness and it was known before the file was written, not discovered after. It is narrower than scripts-blocked (the file is same-origin, 2,476 bytes, and its absence would surface as a 404 in the deployed-asset sweep, which currently returns 200). Closing it requires a scripting-capability marker written before first paint — the render-blocking inline script the plan's decision table rejected, which would add a third script element and a second writer of nav-adjacent state. **NOT CLOSED.** `WINDOWS.md` entry 10.
2. **The document-width backstop at 900px and 1440px with scripting disabled** — `scrollWidth <= innerWidth`. This plan **changes** the desktop no-script layout, so the UI-SPEC `overflow` row is RE-OPENED rather than inherited from its previously covered state. It cannot be derived from source. **ABSTAINS to `human_needed`. Explicitly NOT recorded as passing.** `WINDOWS.md` entry 9.
3. **`problem-stari.html` still has zero inbound links** across all sixteen deployed pages (disclosed in `02-04-SUMMARY.md:308`). It is a Phase 3 content decision — does the page survive at all, and if so where does it belong — not a nav-mechanism defect. **Deliberately untouched**, recorded so it is not later mistaken for an oversight of this pass. `covid.html` is handled by plan 02-07 under D-35.
4. **The desktop row shape** — see Deviation 2. The visible top-level links wrap across two rows rather than reading as one. **OPEN.** `WINDOWS.md` entry 11.
5. **All rendered no-script observations** (five top-level items and six category links visible/activatable at three widths; neither disclosure control visible or Tab-reachable; the header focus order; the confirmation that the scripting-enabled nav shows no flash on load). **UNRUN**, routed to the end-of-phase UAT batch. `WINDOWS.md` entry 8.

Items 3 and 5 of the plan's Task 2 `human-check` — the measured `scrollWidth`/`innerWidth` pairs and the recorded header focus order — **have no measured values to report**. They are absent rather than estimated.

## Prohibitions — Disposition

All four `must_haves.prohibitions` were fallback-authored and descriptor-less (`PROHIB_ABSENT`), so each disposes **flagged-unverified** rather than mechanically verified. None is dismissed. Structural evidence, necessary but not sufficient:

| Prohibition | Structural evidence | Status |
|---|---|---|
| MUST NOT delete, soften, or relocate the inline record instead of correcting it | The record is longer and more specific than what it replaced: it names the true scope (whole five-item list), the rule pair, the load-bearing link, and the residual case. `grep -c 'load-bearing'` = 1 asserts it exists rather than inferring it from a token count. | flagged-unverified (evidence consistent) |
| MUST NOT introduce a second source of truth for the nav's open state | `grep -c 'aria-expanded' no-js.css` = 0, asserted on the deployed bytes too; `setAttribute` in `site.js` = 1; `site.js` and `components.css` zero-diff from the pinned baseline; `<script` count in `header.php` unchanged at 1 — no inline capability marker was added. | flagged-unverified (evidence consistent) |
| MUST NOT leave a focusable control announcing a collapsed state over a visibly open panel | Both `.nav__toggle` and `.nav__disclosure` are `display: none` in the override, which removes them from the accessibility tree entirely. The *rendered* Tab order confirming it is unrun (Residual 5). | flagged-unverified (evidence consistent; rendered confirmation OPEN) |
| MUST NOT add a JavaScript library, framework, or polyfill | Zero packages installed. `header.php` still emits exactly one script element, `js/site.js`, confirmed on the deployed homepage. The fix is 8 CSS declarations across 3 rule groups. | flagged-unverified (evidence consistent) |

## Threat Model — Disposition

| Threat ID | Severity | Outcome |
|---|---|---|
| T-02-24 (malformed edit to the sole shared head = a sixteen-page outage that looks like a one-line diff) | high | **Mitigated.** Five-part source lint (`__DIR__` 0, `<?=` 0, menu roles 0, no BOM, one script element) plus the sixteen-URL live sweep asserting HTTP 200, one `<html lang="bg">` and zero literal PHP open tags on **every** page. No local `php` binary exists for a `-l` syntax check, so the live sweep is the syntax proof — and it is the stronger one, since it exercises the host's actual PHP 5.2.17 handler. |
| T-02-25 (a `<noscript>` link pointing at an asset that does not serve) | medium | **Mitigated.** `css/no-js.css` fetched from the live origin with a cache-busting query string in **both** tasks, asserting HTTP 200 **and** specific rule bodies. Every source grep would have passed while the override 404'd. |
| T-02-26 (a second writer of nav state creeping in to close the residual case) | medium | **Mitigated.** Zero disclosure-state selectors in the override (asserted on source **and** deployed bytes); `setAttribute` in `site.js` still 1; `site.js`/`components.css` zero-diff from the pinned baseline; no inline head script added. The residual case is recorded as open rather than closed by a marker. |
| T-02-27 (a new published file disclosing host or platform detail in comments) | low | **Mitigated.** `no-js.css` comments discuss cascade position and ARIA reasoning only. They name no host, no server software and no version. Confirmed by reading the file back from the live origin (5,280 bytes served). |
| T-02-28 (recording the residual case or the width check as closed) | medium | **Mitigated.** Five residual items are recorded as OPEN here and four as `open` entries in `.planning/WINDOWS.md`. The document-width check abstains to `human_needed` and is stated as NOT passing. The mobile pixel figure is labelled DERIVED FLOOR with its arithmetic shown. The plan's own comment estimate of "eleven rows" is corrected to ten rather than left to stand. |
| T-02-SC (supply chain) | low | **Accepted, and held.** Zero packages installed, zero third-party runtime assets, no package manager invoked. Notably the browser-measurement blocker was again **not** resolved by installing anything — see Deviation 1. |

## Issues Encountered

- **No automatable browser on the build machine**, re-confirmed this run rather than inherited. Resolved by deriving the one required figure with its arithmetic shown and its floor status stated, and by routing every genuine observation to the end-of-phase batch — not by fabricating numbers and not by installing tooling this project deliberately does not carry.
- **The plan's prescribed desktop declaration conflicts with the plan's own human-check wording** (Deviation 2). Implemented as specified, discrepancy recorded as open.

## User Setup Required

None — `user_setup: []` in the plan frontmatter, and nothing in this plan requires external service configuration.

## Next Phase Readiness

- **Plan 02-07 is unblocked.** It touches different files (`covid.html` inbound linking under D-35) and rides the same proven edit → FTPS deploy → live re-fetch rails.
- **The scope fence held.** Only `src/css/no-js.css` (new) and `src/includes/header.php` were touched. **WR-07** (disclosures staying open across same-document navigation) and **WR-15** (no skip-to-content link) are real defects in this same file and remain deliberately out of scope, as the plan declared.
- **D-36 stays deferred** — no URL added, renamed or retired.
- **Carried blockers, unchanged:** OWNER-QUESTIONS #20 (working hours) and #21 (chat-capable number) still block the Phase 4 cutover. Nothing here touches them.
- **The end-of-phase UAT batch now carries five rendered items** from 02-05 and 02-06 combined. Phase verification cannot close IA-02 or DESIGN-01 without it.

## Self-Check: PASSED

- Files on disk: `src/css/no-js.css` **FOUND**, `src/includes/header.php` **FOUND**, `.planning/WINDOWS.md` **FOUND**, `02-06-SUMMARY.md` **FOUND**.
- Commits in history: `2262b5c` **FOUND**, `d88f9b0` **FOUND**, baseline `fc189e6` **FOUND**.
- Post-commit deletion check on `2262b5c`: **no tracked files deleted**.
- All automated `<acceptance_criteria>` from both tasks re-run and **PASS**. Every criterion requiring a rendered browser observation is recorded as **unrun** under Deviations and Residual Items, not silently skipped and not recorded as passing.
- Plan-level `<verification>` items 1–7 re-run after both commits: **PASS**. Item 8 (rendered behaviour) is **routed to human-check and abstains**, exactly as the plan specifies.

---
*Phase: 02-design-system-information-architecture*
*Completed: 2026-08-06*

---

## Correction (2026-08-06, post-execution)

This summary states that its rendered/visual/keyboard checks could not be run because no
automatable browser exists on this machine. **That is wrong.** Brave is installed, Brave is
Chromium, and it drives over CDP; the checks were runnable throughout. The search that produced the
claim looked for Chrome/Chromium/Edge/Playwright/Puppeteer by name and stopped there.

The deferred checks have since been measured against the deployed staging origin and **pass**,
reproducing this plan's hand-computed ratios exactly. See `02-RENDERED-VERIFICATION.md` for the
figures, the two measurement traps that produce false results, and how to re-run them
(`scripts/render-check.sh`).

The arithmetic and the refusal to record unrun checks as passing were both correct. Only the
capability assessment was wrong.
