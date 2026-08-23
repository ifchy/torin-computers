---
phase: 03-content-trust-signal-build-out
plan: 03
subsystem: category-2 routing hub + first two child pages
tags: [php52, seo-01, trust-03, breadcrumbs, terminology, one-way-door, content]
status: complete

requires:
  - 03-01 torin_render_service_page(), the $page key contract, services.php child records
  - 03-02 brand row, rating badge (gated off), deploy-time CSS comment stripper
provides:
  - the APPROVED child slug set (amended) that plan 03-07 must build against
  - ekran-klaviatura-portove.html — the kat-2 routing hub
  - smyana-na-ekran.html / smyana-na-klaviatura.html — first two children
  - the «екран» over «матрица» terminology rule, recorded at the data layer
  - proof that the generalized renderer works with no category record (P-5)
affects:
  - 03-07 (creates panti/portove/buksa against the slug set below)
  - index.html card grid and the Услуги nav — kat-2 now routes to a real page
  - every later page authored in this cluster (terminology rule)

tech-stack:
  added: []
  patterns:
    - a hub's child list is BUILT by filtering the record set, never enumerated
    - a child page's sibling list includes only PUBLISHED siblings, so a gated
      one never renders as a second label pointing at the same hub
    - a page whose title IS its record name derives the title instead of
      retyping it; pages with keyword-first titles keep literals

key-files:
  created:
    - src/ekran-klaviatura-portove.html
    - src/smyana-na-ekran.html
    - src/smyana-na-klaviatura.html
  modified:
    - src/includes/services.php
    - src/includes/categories.php

decisions:
  - the screen slug is smyana-na-ekran.html, not smyana-na-matrica.html (user)
  - the diagnostic block heading is «Екран или видеочип?», not the plan's
    «Матрица или видеочип?» — the terminology rule governs h2 headings
  - the hub derives its title from the category record rather than using a literal
  - sibling lists are filtered to published records rather than rendering gated
    siblings as duplicate hub links

metrics:
  duration: ~2h (including the checkpoint pause)
  completed: 2026-08-23

actuals:
  tokens: 79000
  tasks: 3
  commits: 4
---

# Phase 3 Plan 03: Category-2 Hub + Screen and Keyboard Pages Summary

Split category 2 into a deliberately short routing hub and five child search entries, shipped the hub
plus the first two children, and applied the user's «екран» over «матрица» terminology decision across
the whole cluster — **with every live check unrun**, blocked on the same deploy permission gate plans
03-01 and 03-02 hit.

## The approved slug set (plan 03-07 builds against this)

Task 1's checkpoint resolved as **`amend-slugs`**. The set, verbatim:

| Slug | Display name | State after this plan |
|---|---|---|
| **`smyana-na-ekran.html`** ← amended | Смяна на екран на лаптоп | **published** |
| `smyana-na-klaviatura.html` | Смяна на клавиатура на лаптоп | **published** |
| `smyana-na-panti.html` | Смяна на панти на лаптоп | gated → 03-07 |
| `remont-na-portove.html` | Ремонт на USB и HDMI портове | gated → 03-07 |
| `smyana-na-buksa.html` | Смяна на захранваща букса | gated → 03-07 |

The amendment landed **before publication**, so nothing was ever indexed under `smyana-na-matrica.html`
and no redirect exists or is needed. The record id moved with it (`svc-matrica` → `svc-ekran`); grep
confirmed nothing outside `services.php` referenced the old id, so this cost zero consumer edits.

`grep -c "'published' *=> *true"` → **2** in `services.php`, **4** in `categories.php`, as gated.

## What Was Built

**Task 1 — the slug and terminology amendment** (`3b7850a`)

The user's decision was broader than a filename, so the rule is recorded **beside the data** in
`services.php` rather than in a plan document nobody re-reads: «екран» carries every customer-facing
surface; «матрица» survives only where it names the bare panel *as a part*. The comment states
explicitly that this **reverses live-site usage** (матриц- 14× vs екран 5×), because without that note
a later pass reading only the legacy pages would "correct" it back and silently undo the decision.

**Task 2 — the routing hub** (`9b81ddd`)

`ekran-klaviatura-portove.html`, 317 Bulgarian words against a ~350 target — short on purpose, and the
head comment says so, since the obvious "improvement" to this page is the one thing that would break
it. The child list is **built** by filtering `$torin_services` on `parent`, with hrefs from
`torin_service_href()`; no child filename appears anywhere in the file. Three publish flips: `kat-2`,
`svc-ekran`, `svc-klaviatura`.

**Task 3 — the two children** (`08209a7`)

Both carry **no category record** — the D3-03 variant, and the first real exercise of the P-5 path.
721 and 676 Bulgarian words. Depth-3 breadcrumbs, shared warranty by key, no price or turnaround claim
on either. The screen page's argument is the «Екран или видеочип?» diagnostic linking the
self-diagnostic; the keyboard page cross-links category 4 along the real liquid-damage failure path.

## Key Decisions

| Decision | Why |
|---|---|
| The hub **derives** its title from the record (`$torin_cat['name'] . ' на лаптоп · Торин'`) | RESEARCH says decouple title from record — but that reasoning is about *keyword-first* titles that must differ from the nav label. This hub deliberately chases no keyword, so its title genuinely **is** the record name plus a qualifier. Deriving produces the exact mandated string, means a D-40 rename cannot strand a stale title, and satisfies the "name never retyped" gate without special-casing it. |
| Sibling lists include only **published** siblings | A gated sibling resolves through `torin_service_href()` back to the hub, so listing all four would render two entries with different labels pointing at the same page — which reads as a bug. Filtering also means 03-07 publishing the other three makes them appear on both child pages with **zero edits to those files**. |
| The diagnostic heading is «Екран или видеочип?» | See deviation 2 — a judgement call, and a one-string reversal if the user disagrees. |

## Deviations from Plan

**1. [Inherited, confirmed] The plan's PHP 5.2 short-array gate can never pass**

`grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` matches any line *reading* an array value right of `=>`.
Substituted the form plans 03-01 and 03-02 both used — `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['`
— which returns clean on all five files this plan touches.

**2. [User decision — supersedes the plan] The diagnostic heading uses «екран»**

The plan mandates the exact heading `Матрица или видеочип?` and gates on it. The terminology rule lists
h2 headings as customer-facing, so the heading is **`Екран или видеочип?`** and the gate becomes
`grep -c 'Екран или видеочип?'`.

This is the one place the rule was genuinely arguable — the heading contrasts the *panel as a part*
against a chip, which is the sanctioned «матрица» exception. It went to «екран» because the rule names
headings explicitly, because a visitor thinks «екран», and because 03-04's worked example puts «екран»
in the customer-facing sentence and keeps «матрица» for the part reference inside. **The older search
term is not lost:** it appears twice in the screen page's copy — in the external-monitor test («в
самата матрица или в кабела към нея») and in the backlight FAQ («лампата или инверторът на
матрицата»), both genuine part references. That is exactly the "introduce it once as a synonym so both
terms are present for search" instruction. Reversing this is a one-string edit if the user prefers.

**3. [Rule 1 — Bug] The hub's expected self-link count is 4, not the plan's 3**

The plan expects `curl … | grep -c 'ekran-klaviatura-portove.html'` to return **3** ("the three
still-gated children route back to the hub"). It will return **4**: `header.php` renders the Услуги
dropdown from `$torin_categories` through `torin_category_href()`, so the moment `kat-2` is published
the nav on *every* page — including the hub itself — emits one more occurrence. The plan's count omits
it. The stated intent is unaffected; only the number is wrong. Corrected expectation and a
gate scoped to the intent are in the deploy section below.

**4. [Rule 2 — Missing critical functionality] Straight apostrophes break the quote-balance gate**

This project writes apostrophes in comments as U+2019 precisely so the single-quote balance check stays
meaningful. Three straight ones in my `services.php` comment, one on the hub and one on each child page
each flipped the file to an odd count. Converted to U+2019; all four files now balance. Worth recording
as a convention for plans 03-04 … 03-09: **an English possessive in a comment must use `’`, not `'`**.

**5. [Rule 2] The keyboard page needed a fifth FAQ to honestly clear the depth bar**

It first measured **595** Bulgarian words — over the plan's mechanical gate (which counts English
comments too, giving 918) but under the real D3-14 600-word bar. Rather than pad, added a fifth FAQ
answering a real objection («Може ли клавиатурата просто да се почисти?») with the honest answer that
cleaning works for crumbs and does not work for dried liquid between the membrane layers. 676 words.

Recorded because the mechanical gate would have passed a page that missed the actual requirement — the
gate counts the wrong thing, and any plan relying on it should measure Cyrillic words separately.

## Live and rendered verification — NOT RUN

**Nothing in this section has been executed. No live check is claimed as passing.**

`scripts/deploy-new.sh` is denied to executor agents by the environment permission classifier
(established in 03-01 and 03-02); it was not retried. Every gate below is therefore **NOT RUN**, not
"assumed passing" — the discipline Phase 2's `abd5ba8` revert established. There is **no local PHP
interpreter**, so deploying and fetching the URL is the only PHP syntax check available and it has not
happened. As a partial proxy, paren/brace balance outside string literals is `+0` on all three new
pages, and single-quote balance is even on all five touched files.

### Deploy command (no photo files needed — none of these pages uses an evidence strip)

```
scripts/deploy-new.sh includes/categories.php includes/services.php \
  ekran-klaviatura-portove.html smyana-na-ekran.html smyana-na-klaviatura.html
```

Includes first, then pages (P-10 ordering, so a parse error isolates to one URL).

### Checks to run afterwards

| Check | Expected |
|---|---|
| `curl -sf -o /dev/null -w '%{http_code}' …/ekran-klaviatura-portove.html` | 200 |
| same for `smyana-na-ekran.html`, `smyana-na-klaviatura.html` | 200 |
| zero `<?php` in any served body; zero parse/fatal/warning strings | 0 |
| hub: links to `smyana-na-ekran.html` / `smyana-na-klaviatura.html` | 1 each |
| hub: `grep -c 'ekran-klaviatura-portove.html'` | **4** (1 nav + 3 gated children) — see deviation 3 |
| hub, intent-scoped: occurrences inside the `svc__fixes` section only | 3 |
| each page: `grep -c '<h1'` | 1 |
| each page: `class="breadcrumbs"` / `BreadcrumbList` | 1 / 1 |
| children: `svc__warranty__term` / `brand-row__item` | 1 / 8 |
| children: `grep -c '<h2'` | ≥ 4 (this is the P-5 assertion — an empty main would return 0) |
| all three: empty headings | 0 |
| `index.html` links the kat-2 card to the hub | ≥ 1 |
| `mehanichni-problemi.html`, `optimizatsiq.html`, `zalivane-technosti.html` still 200 | 200 |
| `scripts/render-check.sh scripts/probes/svc-page.js <child URL> 360 640` | PASS, `inconclusive: []` |

The probe should be run against a **child** page — that is the configuration this plan actually adds
(no category record, depth-3 breadcrumb) and the one no prior plan has measured.

## Known Stubs

None from this plan. Three of the five children remain unpublished, which is a **gated absence, not a
stub**: plan 03-07 creates their files and flips their flags in the same change, and until then
`torin_service_href()` routes them to the hub, so no link 404s.

## Deferred / out of scope

- **`src/includes/categories.php` has an odd single-quote count (139) at HEAD, pre-existing.** My change
  added zero quotes and the count is unchanged. It is a straight apostrophe in a pre-existing comment
  («category's own homepage anchor»), not a broken string literal — but it means the quote-balance check
  is currently useless on that one file. Not fixed here: out of scope for this plan's files, and a
  sibling agent may be editing the same file this wave. One-character fix for a later pass.

## Threat Flags

None. No new network surface, no secret, no third-party artefact, no package installed. T-03-13
(BreadcrumbList injection) is inherited unchanged — crumb names reach the structured data through
`json_encode()` only. T-03-14 (legacy markup), T-03-15 (hand-typed child hrefs) and T-03-16 (implied
commitments) are all gated and clean on all three new pages.

## Self-Check: PASSED

- `src/ekran-klaviatura-portove.html` — FOUND
- `src/smyana-na-ekran.html` — FOUND
- `src/smyana-na-klaviatura.html` — FOUND
- `3f4d046`, `3b7850a`, `9b81ddd`, `08209a7` — all FOUND in git log
- Working tree clean; no untracked files; no deletions in any commit
- Live checks — correctly recorded as NOT RUN, not as passing
