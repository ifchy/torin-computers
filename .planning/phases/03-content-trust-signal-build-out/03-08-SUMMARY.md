---
phase: 03-content-trust-signal-build-out
plan: 08
subsystem: category content — the last three category pages
tags: [content, seo, trust-03, php52, publish-gate, cross-listing]
status: complete

requires:
  - torin_render_service_page() + the $page key contract (plan 03-01)
  - torin_service_by_id() / torin_service_href() child records (plan 03-01)
  - src/profilaktika-laptop.html and src/test-laptop.html (plan 03-04)
  - $site['warranty'] keyed set (plan 03-01)
provides:
  - src/pregryavane-ohlazhdane.html — category 5, previously no file at all
  - mehanichni-problemi.html and optimizatsiq.html at the D3-14 Definition of Done
  - kat-5 published; five of six categories now resolve to a real page
affects:
  - the homepage card grid and the Услуги dropdown (kat-5 stops routing to an anchor)
  - profilaktika-laptop.html — its kat-5 related link now resolves to a page, not an anchor
  - plan 03-09 (site-wide SEO ordering assertion) — the last two derived titles are gone

tech-stack:
  added: []
  patterns:
    - page data array given a page-specific name ($torin_meh_page / $torin_opt_page / $torin_ohl_page)
    - publish flag flipped in the SAME commit that lands the page file
    - cross-listing a service as a link, gated by a verbatim-duplication check
    - depth measured in Cyrillic words with comments stripped, not raw tokens

key-files:
  created:
    - src/pregryavane-ohlazhdane.html
  modified:
    - src/mehanichni-problemi.html
    - src/optimizatsiq.html
    - src/includes/categories.php

decisions:
  - the three photos on category 1 were opened and looked at before being captioned; meh-prob1 was rejected because it shows an intact drive and no honest caption could call it damage
  - the plan named smyana-na-matrica.html, which does not exist under the locked «екран» decision; resolved through torin_service_href('svc-ekran') so no filename is typed either way
  - the homepage anchor gate asserts one distinct CATEGORY, not one anchor occurrence — each unpublished category emits two anchors, one per consumer

metrics:
  duration: ~50m
  completed: 2026-08-26

actuals:
  tokens: 26000
  tasks: 3
  commits: 3
---

# Phase 3 Plan 08: The Last Three Category Pages Summary

Category 1 and category 3 went from two sub-service lines each to full pages, category 5 got the
file it never had, and the kat-5 publish flag flipped in the same commit that landed it — so five of
six categories now resolve to a real page and the sixth is still gated on its open owner question.
**All live verification is UNRUN**: `scripts/deploy-new.sh` is denied to this executor, and the live
site is still serving the pre-plan versions of all three URLs.

## What Was Built

**Task 1 — category 1, «Счупвания и механични повреди»** (`5f0eb29`)

The page kept `cat_id => 'kat-1'` (so the display name and symptom line are still read from the
record) and gained everything else: intro, three repair photographs, six fixes, two content blocks,
the shared warranty, a six-step process, four FAQ entries and related links.

The title is now a literal — `Ремонт на счупен лаптоп София · Торин` — rather than
`$torin_cat['name'] . ' · ТОРИН КОМПЮТЪРС'`. This was one of the last two pages still deriving its
title from the record, and decoupling it is the prerequisite for the keyword-first title D3-14 asks
for. The `h1` is a separate explicit override (`Ремонт на счупен лаптоп в София`, 31 chars).

Every child link resolves through `torin_service_href()`. No child filename is typed anywhere, which
matters more than usual here: plan 03-07 was publishing child records in a sibling worktree while
this ran, so any hand-typed href would have been a guess about a file that may or may not be live.
The accessor makes the question moot — an unpublished child routes to its parent hub.

Copy is ported from `site-current/mehanichni-problemi.html`: the head crash on a spinning drive
(:110), how little force a display panel tolerates (:113), the lid-mechanism argument that breakage
comes as often from a loosening hinge and poor design as from the fall itself (:115), the keyboard
passage (:117) and the gradual-damage argument for jacks and connectors (:157-159).

**Task 2 — category 3, «Оптимизация»** (`75d2d54`)

The weakest of the six categories in the content inventory, and the one that needed the most new
writing. The prose block names the four causes of a slow machine one at a time, each ending in what
the corresponding work actually is: accumulated operating system, hardware never specified for what
is now asked of it, a failing drive, and a board fault that is checked last precisely because it is
rarest.

The failing-drive cause carries the phase's **second and last urgent-tone callout**, and it is
earned: it is the one of the four where waiting costs the visitor something that cannot be bought
back. The heading itself states the urgency (`Ако дискът отказва, данните са първи`), so colour is
not the sole carrier. The copy says plainly that the drive should be imaged before anything else is
attempted, that continuing to work on it reduces what can be recovered, and that what people
actually lose is photographs — the shop's own argument from :119, not a marketing line.

Профилактика is cross-listed as a link in both `fixes` and `related`. The duplication check
confirms it: of the 33 sentences over 90 characters on `profilaktika-laptop.html`, **zero** appear
on this page.

**Task 3 — category 5 published, «Прегряване и охлаждане»** (`94198d5`)

`src/pregryavane-ohlazhdane.html` created at the locked filename, and the kat-5 `published` flag
flipped to `true` **in the same commit**. That ordering is the T-03-38 mitigation and it is not
cosmetic: a flag flipped even one commit early puts a 404 into the card grid and the nav at once.

The page covers the symptom, not the procedure. The prose block explains that a manufacturer never
designs in exactly enough cooling but a margin above it, that the margin is consumed year on year as
paste dries and bearings wear, and that the failure sequence is predictable — the machine first
throttles itself (which people mistake for a software problem and try to fix with a reinstall), then
shuts down, and eventually the heat lifts a chipset off the board. A four-item `steps` block covers
what to do while waiting, including not vacuuming the cooling system.

No urgent-tone block, deliberately. An overheating laptop is a repairable condition, not an
emergency, and a third use of that colour would devalue the two places where it means something.

kat-6 was **not touched**. It is still `published => false` (T-03-39).

## Key Decisions

| Decision | Why |
|---|---|
| Rejected `meh-prob1.jpg` from the evidence strip | All nine `meh-prob*` photos were opened and looked at before captioning. meh-prob1 is an open hard drive in clean condition — it illustrates the head-platter geometry but shows no damage, and no honest caption could call it proof of impact damage. Used meh-prob2 (keycaps torn off, scissor mechanisms exposed), meh-prob3 (panel cracked from the corner outward) and meh-prob5 (hinge bracket torn out of the lid with the mounting posts stripped) instead — each shows real damage the page claims to repair. |
| `cat_id` written as a literal, not via `$torin_cat_key` | The plan's gate and acceptance criteria both assert `'cat_id' => 'kat-N'` literally. The Phase 2 pages passed it through a variable, which that grep cannot see. Written out with a comment; the name and symptom line still come from the record, so nothing is retyped that matters. |
| Depth measured in Cyrillic words, comments stripped | Inherited gate defect #2. The plan's `wc -w` gate returns 1457 / 1373 / 1217 for the three pages, but a large share of that is English comment prose. The honest Cyrillic-only counts are **1028 / 1070 / 955**, all above the 600 bar (`zalivane-technosti.html`, the reference page, measures 895 by the same method). |

## «екран» / «матрица» — checked, as required

Checked on all three pages. Result: **zero customer-facing uses of «матрица»**. The single `матриц`
match in each file is inside the TERMINOLOGY comment that records the locked decision and warns a
later pass not to revert it. All customer-facing copy — titles, `h1`s, fixes, prose, FAQ — uses
«екран» (8 occurrences on category 1, where the word actually belongs).

The legacy source for category 1 calls them «LCD матрици» at :121. That word was **not** ported. The
one legitimate exception — the bare LCD panel as a distinct part — does not arise on any of these
three pages, so the word appears nowhere in body copy.

## Deviations from Plan

**1. [Rule 1 — Bug] The homepage-anchor gate asserts a count that can never be 1**

- **Found during:** Task 3 verification, against the live homepage.
- **Issue:** the plan asserts `test "$(printf '%s' "$H" | grep -oc 'index.html#kat-')" -eq 1`. Two
  consumers render every category — the card grid and the Услуги dropdown — so each unpublished
  category emits **two** anchors. Measured on the live homepage right now, with kat-5 and kat-6 both
  unpublished: `2 index.html#kat-5`, `2 index.html#kat-6`, total 4. After this plan deploys the
  correct value is **2**, not 1, and the gate as written would fail on correct output.
- **Fix:** assert the plan's stated intent — exactly one *category* still routes to an anchor —
  independently of how many consumers render it:
  `test "$(curl -s "$U" | grep -o 'index.html#kat-[0-9]' | sort -u | wc -l)" -eq 1`
  and confirm the survivor is kat-6:
  `curl -s "$U" | grep -o 'index.html#kat-[0-9]' | sort -u` → `index.html#kat-6`.
- **Files:** none changed; verification method corrected.

**2. [Rule 3 — Blocking] The plan names `smyana-na-matrica.html`, which does not exist**

- **Found during:** Task 1.
- **Issue:** the plan's `related` instruction and its negative grep both name
  `smyana-na-matrica.html`. Under the locked «екран» decision, plan 03-03 published that child as
  `smyana-na-ekran.html` (record `svc-ekran`). A literal link would have been a 404.
- **Fix:** the related entry resolves through `torin_service_href(torin_service_by_id('svc-ekran'))`
  and its label comes from the record's `name`, so no filename is typed and the stale slug never
  appears. The plan's negative grep was extended to cover `smyana-na-ekran.html` as well, and
  returns 0.

**3. [Rule 1 — Bug] The plan's short-array gate regex fires on valid PHP 5.2**

- **Found during:** all three tasks.
- **Issue:** `grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` matches any line reading an array value to the
  right of `=>`, so it cannot pass on correct code. Already recorded by plan 03-01.
- **Fix:** substituted the intent-preserving form
  `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['`, which returns 0 on all four files.

**4. [Rule 2 — Missing critical functionality] Both meta descriptions were under the stated length**

- **Found during:** Tasks 1 and 2, on measurement.
- **Issue:** as first written the descriptions measured 109 and 113 characters against a stated
  120-140 band — short enough that the CTA carried less context in a result snippet.
- **Fix:** extended to 121 and 120 characters. All three now sit in band (121 / 120 / 122) with the
  call-to-action verb beginning at character 76 / 75 / 86, inside the 95-character margin.

## Verification

### Local gates — RUN AND PASSED

| Check | mehanichni | optimizatsiq | pregryavane |
|---|---|---|---|
| `cat_id` literal present | 1 | 1 | 1 |
| Title no longer derived from the record | 0 matches | 0 matches | n/a (new file) |
| Category display name not retyped | 0 | n/a | 0 |
| `h1` override present | 1 | 1 | 1 |
| `warranty_key => 'default'` (TRUST-03) | 1 | 1 | 1 |
| Urgent-tone blocks | 0 | 1 | 0 |
| Child filenames hand-typed | 0 | 0 | 0 |
| Price / turnaround / guaranteed-outcome strings | 0 | 0 | 0 |
| Legacy markup, inline handlers, `__DIR__` | 0 | 0 | 0 |
| Short-array syntax (corrected gate) | 0 | 0 | 0 |
| Single-quote balance | 258 EVEN | 208 EVEN | 196 EVEN |
| Paren balance | 0 | 0 | 0 |
| Cyrillic words (comments stripped) | **1028** | **1070** | **955** |
| Sentences >90 chars shared with profilaktika | n/a | **0 of 33** | **0 of 33** |
| Title / desc / h1 length | 37 / 121 / 31 | 42 / 120 / 42 | 43 / 122 / 40 |

Record integrity on `src/includes/categories.php`:

- `grep -c "'published' => true"` → **5** (was 4)
- kat-6 still `published => false` → **confirmed**
- all six `page` values name a file that exists on disk → **confirmed** (T-03-38)
- single-quote count **139, identical to HEAD** — pre-existing odd parity from English comment
  apostrophes, exactly as the file-specific warning describes. Not "fixed"; the edit was verified by
  reading it rather than by a balance gate, which is useless on this file.

### Live verification — NOT RUN

`scripts/deploy-new.sh` is denied to this executor, so **nothing was deployed and no live assertion
in any of the three tasks was executed.** These are recorded as NOT RUN, never as passing.

Read-only checks against the origin confirm the pre-plan state and serve as the positive control for
whoever deploys:

| URL | Now | Meaning |
|---|---|---|
| `/new/mehanichni-problemi.html` | 200, `<h1>Счупвания и механични повреди</h1>`, 5 `<h2>` | still the OLD page — the record name as heading, not this plan's keyword `h1` |
| `/new/optimizatsiq.html` | 200 | still the old page; the back-compat alias is intact |
| `/new/pregryavane-ohlazhdane.html` | **404** | expected — the file exists only in git |
| `/new/index.html` | `2 × #kat-5`, `2 × #kat-6` | kat-5 still routing to its anchor, as it must until deploy |

Pre-deploy probe baseline, `scripts/render-check.sh scripts/probes/svc-page.js
https://torin.bg/new/mehanichni-problemi.html 360 640`:

```json
{ "sectionCount": 4, "adjacentTintedPairs": 0, "horizontalScroll": false,
  "breadcrumbLinkCount": 0, "h1Count": 1, "headingCount": 6, "emptyHeadings": 0,
  "hasUrgentBlock": false, "hasWarrantyTerm": false, "verdict": "INCONCLUSIVE" }
```

The probe correctly returned `INCONCLUSIVE` rather than a vacuous pass. After deploy this page
should move to `sectionCount` 9 (hero, evidence-carrying fixes, blocks, warranty, process, faq,
related, brands, contact), `breadcrumbLinkCount` 1, `hasWarrantyTerm` true, `hasUrgentBlock` false.
A `sectionCount` still at 4 means the upload did not land.

**Deploy command for the human, exactly as it must be run** (from the primary checkout, which is the
only place `filezilla-server-data.xml` exists — it is present and readable, so the Task 3
precondition is met; only the script invocation is blocked):

```bash
scripts/deploy-new.sh \
  includes/categories.php \
  mehanichni-problemi.html \
  optimizatsiq.html \
  pregryavane-ohlazhdane.html \
  index.html \
  img/repairs/meh-prob2.jpg \
  img/repairs/meh-prob3.jpg \
  img/repairs/meh-prob5.jpg
```

The three photographs are named explicitly because `torin_render_evidence()` **silently emits
nothing** when a referenced file is absent on the server (`category-page.php:265`) — the evidence
strip would just not be there, at HTTP 200, with no error anywhere. `index.html` is included because
the kat-5 card and the Услуги dropdown both change from an anchor to a page link, and
`includes/categories.php` goes first so a page never renders against a stale record.

## Known Stubs

None. `prices` is deliberately unset on all three pages (D3-06, PRICE-01 is v2) and kat-6 is
deliberately unpublished (OWNER-QUESTIONS #3) — both are gated absences, not stubs.

## Threat Flags

None. No new network surface, no new secret, no third-party artefact. The three pages add only
structured content through the existing `blocks` slot, which escapes every leaf and has no
raw-HTML passthrough key.

T-03-38 (publish gate opened ahead of a file) and T-03-39 (category 6 promoted early) are both
mitigated as planned and asserted locally; the live half of the T-03-38 sweep is NOT RUN.
T-03-40 (duplicate-content exposure) is mitigated and fully verified locally — the verbatim
duplication check is a local check and needed no deploy.

## Self-Check: PASSED

- `src/pregryavane-ohlazhdane.html` — FOUND
- `src/mehanichni-problemi.html` — FOUND (178 lines)
- `src/optimizatsiq.html` — FOUND (163 lines)
- `src/includes/categories.php` — FOUND, kat-5 published, kat-6 gated
- `5f0eb29` `75d2d54` `94198d5` — all FOUND in git log
- no file deletions in any of the three commits
- `services.php` untouched — the sibling agent owns it
