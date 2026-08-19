---
phase: 03-content-trust-signal-build-out
plan: 05
subsystem: category 6 content + EU disclosure relocation + surge page
tags: [php52, content, seo, content-01, content-02, owner-questions, gated-publish]
status: complete

requires:
  - 03-01 torin_render_service_page(), the $page key contract, $site['warranty'], breadcrumbs
  - 03-02 site-config brand/badge keys, torin_render_evidence(), the deploy-time CSS comment stripper
  - categories.php kat-6 record with page => problem-stari.html, published => false
provides:
  - src/problem-stari.html — category 6 on its existing indexed URL, promotion still gated
  - src/tokov-udar.html — surge, phase-difference and power-supply damage
  - src/about.html — company history + the CONTENT-02 EU disclosure block
  - OWNER-QUESTIONS #28 — the company-name discrepancy in the funding disclosure
affects:
  - plan 03-07 (jack-side carryover of the legacy power-circuit material)
  - Phase 4 cutover (kat-6 publish flip; covid.html port must carry the wrong name verbatim)

tech-stack:
  added: []
  patterns:
    - a page may ship structure while its category stays unpublished — the URL is served, the promotion is gated
    - scope assumptions marked [ASSUMED] in source naming the open question, never in rendered copy
    - an unanswerable FAQ routes the visitor to a phone call instead of asserting a scope claim
    - a compliance artefact is flagged to the owner, never silently corrected

key-files:
  created: []
  modified:
    - src/problem-stari.html
    - src/tokov-udar.html
    - src/about.html
    - .planning/OWNER-QUESTIONS.md

decisions:
  - the drafted category-6 fixes list was cut to four provable capabilities; the plan's longer draft named work no source supports
  - category 6 ships with no evidence strip — stari1-3 prove battery/adapter defects, not work on the equipment class the category names
  - the About history does not name the distributed brands the legacy copy lists; a lapsed distributorship is the wrong claim on a credibility page
  - the h1 the plan specified for tokov-udar.html measures 50 code points, so the plan's own shorter fallback was taken

metrics:
  duration: ~1h
  completed: 2026-08-19

actuals:
  tokens: 11000
  tasks: 3
  commits: 3
---

# Phase 3 Plan 05: Category 6, the Surge Page and the EU Disclosure Relocation Summary

Category 6 now has a real page on the already-indexed URL D3-05 assigns it, written **without a single
invented factual claim** and with its category promotion still gated; the surge page carries the
shop's own explanation of phase differences and LAN surges; and the EU funding disclosure is reachable
from the About page while its one factual error sits on the owner's list rather than being quietly
edited away. **All live verification is unrun** — the deploy script remains denied to executor agents.

## What Was Built

**Task 1 — category 6 on `problem-stari.html`, promotion gated** (`e80727f`)

The page renders through `torin_render_service_page()` with `'cat_id' => 'kat-6'`, so the display name
and symptom line come from the record and can never drift. `published` stays `false`, so
`torin_category_href()` continues routing every card and nav entry to `index.html#kat-6` — verified
against the live homepage, which serves **zero** links to `problem-stari.html` and **two**
`index.html#kat-6` anchors.

The content discipline is the point of this task, so it is stated plainly: **every claim on the page
is provable from existing site content.** The two prose blocks are ported from
`site-current/problem-stari.html:148` and `:155` — deep-discharged packs drawing abnormal charge
current, low-quality aftermarket cells, and universal adapters with a manual voltage switch. The
capability claims are ones the site already makes elsewhere. Nothing about equipment types, example
jobs, out-of-scope limits, pricing or turnaround appears anywhere, because none of it exists in any
source. See **Category 6 — what is still missing** below, which is the deliverable the owner needs.

**Task 2 — `tokov-udar.html`** (`0db4f17`)

Ported from `site-current/tokov-udar.html:108-145` and lightly modernised, keeping the part no
competitor page has: which bridge dies in each case. Phase differences over HDMI/VGA to a television
and to powered speakers, ungrounded sockets, unearthed cable television, surges arriving on the LAN
cable, faulty USB peripherals — plus the source's own prevention advice as a six-item steps block. No
`cat_id`; the page supplies its own name and an `[ASSUMED]` symptom line against #16. Cross-links into
both `zalivane-technosti.html` and `problem-stari.html`.

**Task 3 — `about.html` and the CONTENT-02 relocation** (`c0255c4`)

Real history ported from `site-current/about.html:283-284`: founded 1993 assembling configurations,
import and distribution from 1996, representation for a motherboard and laptop manufacturer, batteries
and regeneration from 2006, the shift into service work during the 2008 downturn. **No
repairs-completed counter was invented** — no such figure exists in any source, and the page whose job
is to establish credibility is the worst place to fabricate one.

CONTENT-02's relocation is the third block: one short paragraph naming the project and the programme,
with a link labelled `Проект BG16RFOP002-2.073` to `covid.html`. Compact by design — the disclosure is
findable without competing for attention, and the disclosure page itself carries the detail.

## Category 6 — what is still missing (OWNER-QUESTIONS #3)

This is the section to read before writing any further copy on that page. Each gap below is keyed to
the sub-question that would fill it. **Nothing was guessed to fill any of them.**

| # | Gap | What the page does instead | What a section needs |
|---|---|---|---|
| **3a** | No device classes are known. Not one sentence anywhere on the current site names what non-standard equipment the shop accepts. | The intro speaks only of «техника, която е на години, вече не се произвежда или просто не е масова» and never names a class. The FAQ entry «Приемате ли техника, която не е лаптоп или компютър?» routes the visitor to a phone call rather than answering — honest, and a real path to an answer today. | A concrete list of things customers have actually brought in. That single answer unlocks the intro, the fixes list and the h1 keyword. |
| **3b** | No confirmed work-performed list. | Four capabilities are drafted, all provable **for the equipment the site already describes** (see the list below). What is `[ASSUMED]` is that the same work is offered on this category's equipment class. | The three-to-five most common jobs; whether it is board-level only or also mechanical/rewiring/fabrication; whether it uses the same BGA station. |
| **3c** | **No out-of-scope section exists at all.** | Omitted entirely rather than guessed. This is the gap with the highest operational cost: the page will generate enquiries and there is nothing on it steering the wrong ones away. | What is not accepted, safety/legal limits, size or weight limits, and anything the page should actively discourage. |
| **3d** | No proof. No photographs of category-6 jobs exist, and no war story. | The evidence strip is deliberately **off**. `src/img/repairs/stari1-3.jpg` were available but prove battery and adapter defects — this page's ported material — not work on the equipment class the category names. Using them would let a reader read them as category-6 evidence. | Even phone snapshots of past non-standard jobs, plus one or two concrete war stories. Per #3d this outweighs any amount of general description. |
| **3e** | No commercial framing. | The process step says price follows the inspection — provable site-wide. No turnaround claim appears. `warranty_key => 'default'` is set as the plan and TRUST-03 require, marked `[ASSUMED]` in source because **#23 explicitly asks whether terms vary by work type** and #3e asks it again for this category. | Whether pricing is always quote-after-inspection; whether free diagnostics applies here (interacts with #24); whether the shared warranty term covers this work; realistic turnaround. |
| **3f** | The category name and symptom line are both developer-written. | Both are read from the record, never retyped on the page, so a rename is a one-line record edit with no page change. | Confirm or correct the display name and the symptom line. |

### The four services drafted under `[ASSUMED]` — confirm or correct item by item

These are the exact strings on the page. Each is a capability the site **already** advertises; the
assumption is that it extends to this category's equipment.

1. `Ремонт на захранващи вериги`
2. `Подмяна на дефектирали компоненти по платката`
3. `Ремонт на ниво чип по дънни платки`
4. `Безплатна оценка на техника, за която другаде са отказали`

The plan's draft also named *board-level repair on equipment with no spare-parts supply* and
*assessment of equipment other shops have declined* as separate items. The first was **omitted** — no
source supports a no-parts-supply capability claim, and it is precisely the plausible-sounding
specific that a customer would act on. The second was folded into item 4, which the category-4 page
already states in the shop's own words.

### The whole remaining action, once #3 is answered

**One boolean.** `'published' => false` → `true` on the `kat-6` record in
`src/includes/categories.php`. Nothing in `src/problem-stari.html` needs to change for the category to
be promoted; the card grid and the navigation pick it up from the record. If #3 comes back with a
scope this draft has wrong, the fix is a content edit on one page plus that boolean — never a
structural change.

## Disposition of the battery/adapter article on `problem-stari.html`

The user decision required that this content not be destroyed. **It was not, and it never was at
risk** — the confusion is worth spelling out because it is easy to re-derive wrongly:

| Copy | Where it is now | Status |
|---|---|---|
| The full original article | `site-current/problem-stari.html`, **untouched** | Preserved verbatim. This plan modified nothing in `site-current/`. `src/problem-stari.html` was a Phase-2 stub, not the article — the repurpose overwrote a stub. |
| The Charger / StandBy board explainer (`:109-142`) | `src/zalivane-technosti.html`, «Захранващи вериги и вътрешни повреди от захранването» | Carried by plan 03-01, verified present. |
| The old-equipment consequences (`:148`, `:150`, `:155`) — deep discharge, cheap aftermarket packs, universal adapters with a voltage switch | `src/problem-stari.html`, this plan's two blocks | Kept on this page, rewritten so no sentence over 90 characters is shared verbatim with the category-4 page (gate run, passes). |
| The jack-side share | not yet placed | Named carryover obligation on **plan 03-07**, which creates the page that receives it. Not asserted here. |
| The closing pointer to the dead specialist battery site (`:181`) | dropped | Per D3-12. The domain is deliberately not spelled anywhere in `src/`; gate passes. |

**What the owner still has to decide** is unchanged and is recorded in OWNER-QUESTIONS #3(a)(b)(c):
whether the battery/adapter article is worth keeping as a unit, where it should live if so, and
whether category 6 should move to a different slug instead. This plan's split is reversible — the
material exists in three places (`site-current/`, the category-4 page, this page) and no wording is
lost.

## The EU disclosure — what changed and what deliberately did not

| Item | State |
|---|---|
| `src/index.html` | **Unchanged.** Carries zero EU or pandemic content. CONTENT-02's homepage half was already structurally satisfied (RESEARCH C-1); this plan asserted it rather than editing. |
| `src/covid.html` | **Unchanged.** `git diff` on it is empty. Still live, still 200 on staging. |
| `src/includes/footer.php` | **Unchanged.** Still links `covid.html` (2 matches). |
| `src/about.html` | **New** «Европейски проекти» block — one paragraph plus a link. The disclosure is now reachable from a content page as well as from the footer legal line. |
| The «Венера-АКС ООД» sentence | **Not corrected.** Flagged as OWNER-QUESTIONS **#28**. |

**The new owner question is numbered 28.** It records that the results paragraph at
`site-current/covid.html:140` names Венера-АКС ООД while the rest of the page names ТОРИН КЪМПАНИ ООД,
frames it as a question about whether the submitted project documentation carries the same error, and
carries an explicit instruction to whoever ports `covid.html` later: **carry the paragraph across
verbatim, including the wrong name**, until the question is answered. Losing the error loses the
ability to answer the question.

`git diff --numstat .planning/OWNER-QUESTIONS.md` reports **40 additions, 0 deletions** — appended,
never replaced, no existing item renumbered.

> ⚠ **Numbering collision risk.** Three sibling plans (03-03, 03-04, 03-06) were executing in parallel
> worktrees while this number was taken, and 28 was the next unused number as of this worktree's base
> commit. If a sibling also appended a #28, the orchestrator should renumber one of them at merge.
> This summary and the item body are the only places #28 is referenced.

## Deviations from Plan

**1. [Rule 1 — Bug] The PHP 5.2 short-array gate regex is broken (inherited, third occurrence)**

- **Issue:** `(=>[^;]*\]|\[\s*[^]]*=>)` matches any line *reading* an array value to the right of
  `=>`, so it fires on untouched, valid PHP 5.2 and can never pass. Established by plan 03-01,
  re-confirmed by 03-02, and shipped unfixed into this plan's three verify blocks.
- **Fix:** substituted `(=>|=)[[:space:]]*\[|return[[:space:]]+\[`, which tests the same stated intent
  (no `[]` literals). Returns nothing on all three files.
- **Recommendation:** this regex is now wrong in five plan files. Fix it at the planner template, not
  once more per plan.

**2. [Rule 1 — Bug] Task 3's live EU gate contradicts Task 3's own footer gate**

- **Found during:** Task 3 verification, against the live homepage.
- **Issue:** the plan asserts both `grep -c 'covid.html' src/includes/footer.php >= 1` (the footer link
  survives) **and** `curl -s .../index.html | grep -ciE 'BG16RFOP|ОПИК|ЕФРР|COVID' == 0`. `footer.php`
  renders on every page including the homepage, and its link text is literally
  «Проект BG16RFOP002-2.073». The two gates cannot both pass. The local gate passes only because it
  reads `src/index.html`, where the footer lives in a separate include.
- **Measured:** the live homepage returns **exactly one** match, and it is line 303 — the
  `site-footer__legal` line. Nothing else.
- **Fix:** gated on the stated intent instead — no EU content on the homepage *outside the footer legal
  line*, which is exactly the compliant minimum RESEARCH C-1 endorsed and which CONTENT-02 requires to
  survive:
  `curl -s .../index.html | grep -viE 'site-footer__legal' | grep -ciE 'BG16RFOP|ОПИК|ЕФРР|COVID|пандеми'` → **0**. **PASS.**

**3. [User decision — overrides the plan] The category-6 fixes list was cut from five items to four**

The plan drafted *board-level repair on equipment with no spare-parts supply* as a fixes entry. The
locked user decision forbids inventing services and requires that unprovable sections be omitted rather
than filled with something that reads convincingly. No source supports a no-parts-supply capability
claim, and it is exactly the kind of specific a customer acts on. Omitted, and recorded under #3b above
so it can be confirmed rather than re-derived.

**4. [User decision] The category-6 FAQ answers scope questions by routing, not by claiming**

The plan specifies an FAQ entry on whether non-laptop equipment is accepted. No source answers it. The
entry ships, but its answer asks the visitor to call and describe the device — true, useful, and it
asserts nothing about scope. The alternative readings (answer «да», or drop the entry) would either
fabricate or leave the most likely visitor question unaddressed.

**5. [Rule 1 — Bug] The plan's `h1` for `tokov-udar.html` breaches its own cap**

`Ремонт след токов удар и проблеми със захранването` measures **50** Unicode code points against a
48-character cap. The plan anticipated this and named the fallback; taken as written.
`Ремонт след токов удар и захранване` — 35.

**6. [Rule 2 — Missing critical functionality] No evidence strip on either new service page**

`torin_render_evidence()` skips any photograph absent from the server and emits nothing if none
survive (03-02, `category-page.php:265`). `tok1-6.jpg` and `stari1-3.jpg` are ported into
`src/img/repairs/` but have **not** been uploaded to the staging origin, and the deploy is denied to
this agent — so a strip added here would be an unverifiable claim about rendering. For
`problem-stari.html` there is a second and stronger reason: `stari1-3` prove battery/adapter defects,
not category-6 work, and presenting them under a category-6 heading is the fabrication this plan
exists to avoid. Both omissions are recorded as follow-ups rather than shipped unverified.

## Live and rendered verification — NOT RUN

`scripts/deploy-new.sh` is denied to executor agents by the environment permission classifier —
established in plans 03-01 and 03-02 and not retried here. **None of the three pages authored by this
plan is being served.** The staging origin still returns the Phase-2 stub for
`problem-stari.html` (200, 8,596 B, stub sentinel present), which is the negative control confirming
these results describe un-deployed code.

Every check below is recorded as **NOT RUN**, never optimistically as passing.

| Check | Status |
|---|---|
| PHP 5.2 syntax validity of all three pages (only obtainable by serving them) | **NOT RUN** |
| `problem-stari.html` 200, one `<h1`, one warranty term line, zero empty headings | **NOT RUN** |
| `tokov-udar.html` 200, one `<h1`, ≥4 `<h2>`, zero empty headings | **NOT RUN** |
| `about.html` 200, one `<h1`, zero empty headings, **zero** warranty sections, ≥1 `covid.html` link | **NOT RUN** |
| Human read-through of the About page (the disclosure block reads as present, not competing) | **NOT RUN** |
| `svc-page.js` / `trust-signals.js` probes at 360×640 and 1440×900 | **NOT RUN** |

### The exact command that unblocks all of the above

```bash
scripts/deploy-new.sh problem-stari.html tokov-udar.html about.html
```

Then, in order:

```bash
for U in problem-stari tokov-udar about; do
  curl -sf -o /dev/null -w "$U %{http_code}\n" "https://torin.bg/new/$U.html"
done
curl -s https://torin.bg/new/about.html | grep -c 'covid.html'                        # >= 1
curl -s https://torin.bg/new/about.html | grep -c 'svc__warranty'                     # 0
curl -s https://torin.bg/new/tokov-udar.html | grep -c '<h2'                          # >= 4
for U in problem-stari tokov-udar about; do
  curl -s "https://torin.bg/new/$U.html" | grep -cE '<h[23][^>]*>[[:space:]]*</h[23]>' # 0 each
done
scripts/render-check.sh scripts/probes/svc-page.js https://torin.bg/new/problem-stari.html 360 640
```

**If the evidence strips are wanted later**, the photographs must be named in the deploy command or
they vanish silently: `tok1.jpg tok2.jpg tok3.jpg` for the surge page. `stari1-3.jpg` should **not**
be deployed for category 6 — see deviation 6.

## Live checks that WERE run (they need no deploy)

These assert state this plan did not change, so the pre-deploy origin is a valid subject.

| Check | Result |
|---|---|
| Live `index.html` links to `problem-stari.html` | **PASS** — 0 matches. The publish gate is genuinely closed. |
| Live `index.html` carries the category-6 homepage anchor | **PASS** — 2 × `index.html#kat-6` |
| Live `index.html` EU/pandemic content outside the footer legal line | **PASS** — 0 (see deviation 2) |
| Live `covid.html` still served | **PASS** — 200 |
| `src/covid.html` unedited | **PASS** — `git diff` empty |
| `src/includes/footer.php` still links the disclosure | **PASS** — 2 matches |
| `.planning/OWNER-QUESTIONS.md` appended, not rewritten | **PASS** — 40 insertions, **0** deletions |

## Local gates — ALL RUN AND PASSED

| Gate | problem-stari | tokov-udar | about |
|---|---|---|---|
| Stub sentinel removed | ✔ | ✔ | ✔ |
| Words in data array (≥600) | 1226 | 1046 | 836 |
| Single-quote balance even | ✔ (180) | ✔ (196) | ✔ (158) |
| No `[]` short arrays / no `__DIR__` | ✔ | ✔ | ✔ |
| No legacy markup (`on*=`, `javascript:`, Bootstrap classes) | ✔ | ✔ | ✔ |
| `h1` ≤ 48 code points | 38 | 35 | 18 |
| `title` / `desc` within budget | 46 / 136 | 49 / 137 | 54 / 134 |
| Plan-specific | `cat_id => kat-6` ×1; `Нестандартна техника` ×0; `ASSUMED` ×4; `OWNER-QUESTIONS #3` ×4; `D3-12` ×1 | no `cat_id`; `warranty_key => default` ×1; zero contact facts | `covid.html` ×3; `BG16RFOP002-2.073` ×2; `1993` ×5; no evidence/warranty key; no street address |

Cross-file: `grep -rniE 'smartbattery' src/` → **0 matches**. No sentence over 90 characters is shared
verbatim between `problem-stari.html`, `tokov-udar.html` and `zalivane-technosti.html` — checked
pairwise, all clean. `kat-6` remains `'published' => false`.

## Known Stubs

**1. Category 6 ships drafted, not confirmed — and unpublished.** This is the *specified* state, not
an unfinished one, but it is the phase's largest content gap. See the #3a–#3f table above. The page is
not thin — 1,226 words in the data array, real ported technical content, full SEO metadata,
breadcrumbs, structured data and CTAs — but everything it says about the *category* is drafted. The
promotion gate is what makes that safe.

**2. `src/covid.html` is still a Phase-2 stub.** Not this plan's file. The About block links to it, so
until it is ported the disclosure link lands on a skeleton. Owned by whichever plan ports the legal
pages; #28 records the constraint on that port.

**3. No evidence strip on `tokov-udar.html`.** `tok1-3.jpg` are genuine photographs of exactly this
damage and are already in `src/img/repairs/`. Deferred only because the deploy is blocked — see
deviation 6.

## Threat Flags

None. No new network surface, no secret, no third-party artefact, no package installed. T-03-22
(disclosure not deleted, not unlinked, not silently rewritten; the error appended as a question),
T-03-23 (scope claims marked and the category gated), T-03-24 (no invented history figure), T-03-25
(strings ported, markup left behind — gate passes on all three files) and T-03-26 (zero deletions in
the owner-question diff) are all mitigated as planned.

## Self-Check: PASSED

- `src/problem-stari.html` — FOUND
- `src/tokov-udar.html` — FOUND
- `src/about.html` — FOUND
- `.planning/OWNER-QUESTIONS.md` item 28 — FOUND
- `e80727f` `0db4f17` `c0255c4` — all FOUND in git log
