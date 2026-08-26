---
phase: 03-content-trust-signal-build-out
verified: 2026-08-26T00:00:00Z
status: human_needed
score: 5/7 success-criterion halves verified
behavior_unverified: 1
overrides_applied: 0
gated_by_decision: 2
requirements:
  satisfied: [TRUST-01, TRUST-03, DIFF-01, DIFF-02, DIFF-03, CONTENT-02, SEO-01]
  gated: [TRUST-02, CONTENT-01]
  orphaned: []
behavior_unverified_items:
  - truth: "A visitor sees a Google rating badge linking to Torin's Google Business Profile (TRUST-02)"
    test: "Set gbp_badge_enabled => true and fill gbp_rating, gbp_reviews, gbp_url in site-config.php on a staging copy; load index.html and any service page."
    expected: "One badge renders as the last child of the CTA block on both, links to the profile, opens in the same tab; jsonld.php begins emitting sameAs. Un-set any one of the three values and the badge disappears entirely with no layout shift."
    why_human: "No PHP runtime is available locally and staging serves the disabled branch, so the enabled render path has never executed anywhere. Presence of the function plus two wired call sites proves the code exists, not that it renders correctly."
human_verification:
  - test: "Read the live Google Business Profile and capture three values: rating, review count, and the profile share URL (OWNER-QUESTIONS #7)."
    expected: "Three owner-confirmed values written into site-config.php, then gbp_badge_enabled flipped to true."
    why_human: "The figures exist only on Google's property. Nothing in the repo can supply them, and the plan's own prohibition forbids a plausible-looking placeholder."
  - test: "Answer OWNER-QUESTIONS #3a-#3f — what «Сервиз на нестандартно ел. оборудване» actually covers."
    expected: "Confirmed scope for category 6, after which problem-stari.html gains its out-of-scope section and kat-6 flips to published => true."
    why_human: "No source material exists anywhere in the repo or on the legacy site. Every factual claim would be invention."
  - test: "Rule on OWNER-QUESTIONS #23 — whether the regenerated-battery warranty is a genuinely separate product term."
    expected: "A decision on whether warrently.html should carve out regenerated batteries from its «за всички сервизни дейности и услуги е 1 месец» clause."
    why_human: "The new battery page shows a 1-year summary whose «Пълни гаранционни условия» link lands on a page stating one month for everything. Inherited from the live site; harmonising it either way without the owner would publish a term the shop may not honour."
  - test: "Confirm OWNER-QUESTIONS #22 — which brands the shop actually services."
    expected: "The seven names in the row confirmed or narrowed (Apple is the flagged risk)."
    why_human: "The list came from requirements drafting, not the owner. Markup correctness is verified; factual accuracy is not verifiable from the repo."
  - test: "Confirm OWNER-QUESTIONS #16 — the customer phrasing behind every card symptom line."
    expected: "Owner-supplied wording replacing the [ASSUMED] symptom lines on the six category cards and the «Не откривате проблема си?» section."
    why_human: "Only the owner hears the real phrasing daily."
  - test: "Resolve OWNER-QUESTIONS #28 — «Венера-АКС ООД» in the EU funding disclosure results paragraph."
    expected: "A decision on whether the same wording is in the submitted project documentation."
    why_human: "A compliance artefact. Correctly ported byte-identical rather than fixed; the decision is the owner's."
  - test: "View index.html and two service pages at desktop and mobile widths and read the 23 titles and descriptions as a set."
    expected: "The tuned metadata reads as one site, not 23 independently written pages; no two siblings sound interchangeable."
    why_human: "Backstop truths in 03-01 and 03-09 — editorial coherence is not machine-inferable."
follow_ups:
  - "REQUIREMENTS.md was not touched during Phase 3 (last commit to it: phase 02). All nine Phase-3 IDs still read `[ ]` / Pending. Seven are now genuinely satisfied and should be marked Complete; TRUST-02 and CONTENT-01 must stay Pending."
---

# Phase 3: Content & Trust-Signal Build-Out Verification Report

**Phase Goal:** All sixteen pages are rebuilt with correct, unique SEO metadata and surface the shop's genuine trust signals and differentiators — assets no competitor currently offers — instead of leaving them buried in text or absent entirely.

**Verified:** 2026-08-26
**Status:** human_needed
**Re-verification:** No — initial verification
**Verified against:** the working tree at `8cfffd4` AND the live staging subtree `https://torin.bg/new/` (23 pages fetched and parsed independently of the project's own gates)

## Page-count discrepancy (noted, not a defect)

The ROADMAP goal says "sixteen pages". That was the legacy count. Inventory comparison:

- `site-current/` holds **17** `.html` files — 16 content pages plus `google1718743335455f1c.html` (a Search Console verification token, not a page).
- `src/` holds **23** `.html` files. All 16 legacy content URLs are present and unchanged (SEO-04 upheld); the 7 additions are the category-2 hub, its 5 children, and the category-5 page.
- The Search Console verification file is **not** in `src/` and is **not** served at `/new/`. That is MIGR-02's "must-carry" checklist item for Phase 4 cutover, outside this phase's scope, but it must not be forgotten.

The goal's intent — every page has unique, accurate metadata — is verified below over all 23, which is a strict superset of the 16.

## Goal Achievement

### Success Criteria

| # | Criterion | Status | Evidence |
|---|-----------|--------|----------|
| 1 | Brand-logo row confirming serviced hardware brands | ✓ VERIFIED | `<ul class="brand-row">` under `<h2>Обслужваме всички марки</h2>` on 17 served pages: Lenovo, HP, Dell, Asus, Acer, Apple, MSI + `и др.` closer + independent-service disclaimer. Absent on all four legal/utility pages and the two sales pages. `trust-signals.js` probe PASS at 360x640: `brandItemCount: 8`, `brandRowRows: 2`, no duplicates, no horizontal scroll. |
| 2 | Google rating badge linking to the GBP | ✗ NOT MET — deliberately gated | `grep -c rating-badge` over all 23 served pages returns zero on every one. Component built and correct; see the dedicated assessment below. |
| 3 | Warranty terms summarized on relevant service pages | ✓ VERIFIED | `svc__warranty` block on 14 service/category pages, rendered from the single keyed set in `site-config.php:142-153`. Service pages show «1 месец гаранция на всеки ремонт»; `za-bateriite.html` selects the `battery` key and shows «1 година гаранция на регенерирана батерия». Every block carries a «Пълни гаранционни условия» link to `warrently.html`. See WARNING W-1. |
| 4 | Self-diagnostic, battery-regeneration and BGA/chip expertise each visually prominent | ✓ VERIFIED | Three distinct top-level `<section>` elements on the homepage, each with its own `<h2>` and its own link into a depth page: «Какво можем, което другите отказват» → `zalivane-technosti.html` (3 evidence photos), «Регенерация на батерии за лаптоп» → `za-bateriite.html` (1 photo), «Тествай сам своя лаптоп» → `test-laptop.html`. All 4 evidence images return HTTP 200 and `evidenceAttrsHonest: true`. |
| 5a | No EU-project/COVID content competing for attention on the homepage | ✓ VERIFIED | Legacy `site-current/index.html` carried 8 EU/COVID body mentions. New homepage body carries **zero**; the only surviving occurrence is the footer legal line `<a href="covid.html">Проект BG16RFOP002-2.073</a>` — a required disclosure, not competing content. The full disclosure block now lives on `about.html` under «Европейски проекти», linking to `covid.html`. `covid.html` returns 200 and stays linked from every page's footer. |
| 5b | Dedicated content for the non-standard-electrical-equipment category as one of the six headline services | ◐ PARTIAL — page shipped, promotion gated | See the dedicated assessment below. |
| 6 | Every page has a unique `<title>` and `<meta name="description">` | ✓ VERIFIED | Independently re-derived, not taken from the project gate. See the SEO-01 section. |

**Score:** 5/7 criterion halves verified; 2 gated by explicit recorded decision.

## The two gated items — assessment

Both were flagged as known-incomplete-by-decision. I assessed each on the wording of its own requirement rather than on whether the engineering was done.

### TRUST-02 / criterion 2 — badge built, gated OFF

**What is actually true in the codebase:**
- `src/includes/rating-badge.php` defines `torin_render_rating_badge()` with four independent guards: the `gbp_badge_enabled` switch plus non-empty checks on `gbp_url`, `gbp_rating` and `gbp_reviews`.
- It is wired at **both** consumers: `src/index.html:285` and `src/includes/category-page.php:561` — so the flip reaches the homepage and every service page with no further edit.
- The absent state is genuinely absent: no skeleton, no placeholder pill. Confirmed on the wire — zero occurrences of the `rating-badge` substring across all 23 served pages.
- `site-config.php:211-219` holds `gbp_badge_enabled => false` and three empty strings.
- The related prohibitions hold: no `aggregateRating` and no `review` markup on any served page, and `jsonld.php` correctly omits `sameAs` entirely while `gbp_url` is empty.

**Verdict: the requirement is NOT satisfied, and this is the right outcome.** TRUST-02 is written as "User sees a Google rating badge…". No visitor sees one. Engineering completeness is not the same as the observable truth the requirement asserts, and calling this Complete would put a false entry in the traceability table. The judgement to ship absence rather than an unverified figure is correct — a wrong rating is disproved in one click on the one element whose entire job is to be believable.

It is **not** an implementation defect and must **not** go to gap-closure planning. What it needs is three owner-supplied values (rating, review count, **and** the profile share URL, which has never been captured in this repo) plus a boolean flip. TRUST-02 stays **Pending** in REQUIREMENTS.md.

One genuine verification limitation: the *enabled* branch has never executed anywhere. No PHP runtime is available locally and staging serves the disabled path, so the badge's rendered output, its position as last child of the CTA block, and the claim that the CTA block's vertical rhythm is unchanged are all present-and-wired but behaviorally unexercised. Recorded as the single `behavior_unverified` item.

### CONTENT-01 / criterion 5b — page real, category gated

**What is actually true in the codebase:**
- `problem-stari.html` is a real page, not a stub: 786 Cyrillic words in `<main>`, 9 `<h2>` sections (Какво ремонтираме / Защо старите захранващи вериги отказват / Универсалните адаптори с превключвател / Гаранция / Как работим / ЧЗВ / Свързани услуги / brand row / CTA), unique metadata, breadcrumbs with absolute-URL `BreadcrumbList` JSON-LD, the shared warranty summary, and outbound links to `tokov-udar.html`, `zalivane-technosti.html`, `za-bateriite.html`. `svc-page.js` probe: PASS, `adjacentTintedPairs: 0`, `h1Count: 1`, no horizontal scroll.
- The out-of-scope section is deliberately **omitted** rather than invented. No fabricated claim was found.
- `categories.php:102` keeps `kat-6` at `'published' => false`. `index.html#kat-6` is the **only** surviving homepage anchor across the whole site — confirmed by distinct-anchor count on both `index.html` and a service page.

**Verdict: partially satisfied, correctly.** The card *is* one of six headline services on the homepage — a visitor sees «Нестандартна техника» in the grid, with an icon and a symptom line. What they do not get is dedicated *content*: both the card's title link and the Услуги dropdown entry point at `index.html#kat-6`, so no navigation surface routes to the page. `problem-stari.html` has exactly **one** inbound link site-wide, from `tokov-udar.html`'s related-services list.

One consequence worth naming precisely, because it is invisible in the source: on the homepage the kat-6 card's title link is `<a href="index.html#kat-6">` sitting inside `<article id="kat-6">`. Clicking the sixth category card **on the homepage** navigates to the card itself — a visible no-op. From any other page the anchor is a meaningful destination. This is a property of the gate, not a coding error, and it disappears the moment `kat-6` flips to published. Flagged so nobody rediscovers it as a mystery bug.

Plan 03-05's refusal to mark CONTENT-01 complete was correct. It stays **Pending**.

## Requirements Coverage

Every ID declared in a Phase-3 PLAN frontmatter, cross-referenced against REQUIREMENTS.md. Union of the nine plans' `requirements:` fields = exactly the nine IDs REQUIREMENTS.md maps to Phase 3. **No orphaned requirements.**

| Requirement | Declared in | Status | Evidence |
|-------------|-------------|--------|----------|
| TRUST-01 | 03-02 | ✓ SATISFIED | Brand row live on 17 pages with the mandatory disclaimer. Brand list itself is `[ASSUMED]` against OWNER-QUESTIONS #22 — an accuracy question for the owner, not an implementation gap. |
| TRUST-02 | 03-02 | ✗ PENDING (gated) | Built, wired at both consumers, gated OFF. Zero badges served. See assessment above. |
| TRUST-03 | 03-01, 03-03, 03-06, 03-07, 03-08 | ✓ SATISFIED | Warranty summary on 14 pages, single-sourced from `site-config.php`, never retyped on a page. Full terms preserved on `warrently.html`, including the 5-6 h/day clause the D3-10 prohibition forbids dropping. See W-1. |
| DIFF-01 | 03-04 | ✓ SATISFIED | Own homepage section; `test-laptop.html` routes each of 4 symptom groups into service pages (`zalivane-technosti`, `optimizatsiq`, `ekran-klaviatura-portove`, `za-bateriite`, `mehanichni-problemi`, `pregryavane-ohlazhdane`). Instructions say USB stick, not optical disc — verified in served copy. |
| DIFF-02 | 03-02, 03-04 | ✓ SATISFIED | Homepage section with the shop's own evidence (spot-welding, HILUMIN, Panasonic cells) → `za-bateriite.html`, a 964-word depth page carrying the `battery` warranty key. No reference to the dead specialist battery domain anywhere in `src/`. |
| DIFF-03 | 03-02, 03-04 | ✓ SATISFIED | Homepage section «Какво можем, което другите отказват» with three loading evidence photos and three-tier chip-work explanation → `zalivane-technosti.html` (995 words) and `profilaktika-laptop.html` (1019 words). |
| CONTENT-01 | 03-05 | ◐ PENDING (gated) | Real page on the indexed URL; category unpublished; scope unconfirmed. See assessment above. |
| CONTENT-02 | 03-05 | ✓ SATISFIED | Homepage body EU/COVID mentions: legacy 8 → new 0. Disclosure relocated to `about.html`; `covid.html` live and footer-linked. |
| SEO-01 | 03-01, 03-03, 03-04, 03-05, 03-06, 03-07, 03-08, 03-09 | ✓ SATISFIED | 23/23 unique and non-empty in both source and served output — independently verified. |

## SEO-01 — verified independently of the project's own gate

`node scripts/seo-metadata-check.js` and `--live` both exit 0 over 23 pages. Because a passing self-authored gate is not evidence, I re-derived the assertion from the raw served HTML with my own parser:

- **23/23 non-empty `<title>`.** Longest 54 code points, shortest 28. None equals `header.php`'s fallback literal — the fallback survives at `header.php:58-63` and is provably unreached.
- **23/23 non-empty `<meta name="description">`.** Range 87–139 code points (the 87 is `msg.html`, the post-POST confirmation page).
- **23 distinct titles, 23 distinct descriptions, 23 distinct title leading segments.** Zero collisions — including across the five category-2 siblings and the two board-level pages, the stated collision risk.
- **Zero script-mixed words** (no Latin letter inside a Cyrillic word) in any title or description.
- **Exactly one `<h1>` per page**, and every title's leading segment shares vocabulary with its own `<h1>`.
- **Every one of the 23 source files assigns both `$torin_title` and `$torin_desc`** before the header include.
- Legacy all-caps brand suffix is gone from every title; homepage now leads with «Ремонт на лаптопи и компютри в София» rather than the brand.

**Exemption audit** (the plan prohibits widening an exemption list to force a pass): the gate carries four narrow lists — `EXEMPTIONS` (description *floor* only, lowered to 60 for `msg.html`), `LONG_SUFFIX_OK` (two formal documents), `BRAND_IS_SUBJECT` (`about.html`), `NO_CTA_EXPECTED` (four legal/confirmation pages). **None of them touches uniqueness or non-emptiness** — the two properties SEO-01 actually asserts. Every exemption carries a written reason. The fourth list is a documented deviation from 03-09-PLAN, correctly reasoned: widening `LONG_SUFFIX_OK` to cover `uslovia.html` would have changed a rendered suffix to satisfy an unrelated rule.

## Served-Output Integrity

The `$torin_page` global-collision incident makes HTTP 200 insufficient evidence. Checks run against the 23 fetched bodies:

| Check | Result |
|-------|--------|
| HTTP status, all 23 pages | 200, sizes 9.5 KB – 27.4 KB |
| `<b>Warning` / `<b>Notice` / `Fatal error` / `Parse error` / `Undefined variable\|index\|array key` | **0 pages** |
| Literal `<?php` or any `<?` leakage | **0 pages** |
| Cyrillic word count in `<main>`, service pages | 767–1165 — no page renders a truncated body |
| Every relative `href`/`src` across all 23 pages (45 distinct targets: pages, CSS, JS, fonts, 15 images) | **45/45 return 200 — zero broken links** |
| JSON-LD blocks | All parse. 17 valid `BreadcrumbList` blocks, every `item` URL absolute `https://`. `sameAs` correctly absent while `gbp_url` is empty. |
| `aggregateRating` / `review` markup | **absent everywhere** — prohibition honoured |
| `smyana-na-matrica.html` (the plan's original slug) | Never published; renamed to `smyana-na-ekran.html` pre-publication by user direction, documented in 03-03-SUMMARY. No redirect needed. |
| `phptest.html` | Removed from `src/`, returns 404 live — so "every `src/*.html`" and "every reachable page" are now the same set |

## Key Link Verification

| From | To | Via | Status |
|------|----|-----|--------|
| `index.html`, `category-page.php` | `rating-badge.php` | `torin_render_rating_badge()` at 2 call sites | ✓ WIRED (renders nothing while gated) |
| `brand-row.php` | `site-config.php` | `foreach` over `$site['brands']`; `и др.` emitted by template, not stored | ✓ WIRED |
| service pages | `site-config.php` | `warranty_key` → `$site['warranty'][$key]`; no page authors a warranty literal | ✓ WIRED |
| `za-bateriite.html` | `site-config.php` | `warranty_key => 'battery'` selects the 1-year term | ✓ WIRED |
| `ekran-klaviatura-portove.html` | 5 child pages | `torin_service_href()` over `$torin_services` filtered on `parent => kat-2` | ✓ WIRED — all 5 published, all 5 return 200 |
| `test-laptop.html` | category pages | `torin_category_href()` per symptom group | ✓ WIRED |
| `smyana-na-buksa.html` | `zalivane-technosti.html` | escalation block along the jack-to-board failure path; carries the `Charger` carryover | ✓ WIRED |
| `pregryavane-ohlazhdane.html` | `profilaktika-laptop.html` | cross-listed as a link, copy not duplicated | ✓ WIRED |
| `about.html` | `covid.html` | «Европейски проекти» disclosure block | ✓ WIRED |
| `seo-metadata-check.js` | `header.php`, `site-config.php` | fallback literals and `base_url` read at runtime | ✓ WIRED |

## Probe Execution

Run by me in my own process; SUMMARY PASS claims were not accepted as evidence.

| Probe | Command | Result | Status |
|-------|---------|--------|--------|
| SEO gate, source | `node scripts/seo-metadata-check.js` | 23 checked, 0 failing | PASS (exit 0) |
| SEO gate, live | `node scripts/seo-metadata-check.js --live` | 23 checked, 0 failing | PASS (exit 0) |
| Trust signals | `render-check.sh probes/trust-signals.js .../index.html 360 640` | `adjacentTintedPairs: 0`, `brandItemCount: 8`, `evidenceBoxesOk: true`, `evidenceAttrsHonest: true`, `h1Count: 1`, `emptyHeadings: 0`, `inconclusive: []` | PASS |
| Service page (urgent) | `SVC_EXPECT_URGENT=1 … svc-page.js .../zalivane-technosti.html 360 640` | `inconclusive: []` | PASS |
| Service page | `… svc-page.js .../smyana-na-buksa.html 360 640` | `breadcrumbLinkCount: 2`, `inconclusive: []` | PASS |
| Service page | `… svc-page.js .../problem-stari.html 360 640` | `inconclusive: []` | PASS |
| Service page | `… svc-page.js .../za-bateriite.html 360 640` | `inconclusive: []` | PASS |

**Probe-honesty audit.** `trust-signals.js:32-41` explicitly encodes the `[].every()` trap: an absent surface pushes to `inconclusive` and degrades the verdict rather than passing vacuously (`brandItems.length === 0`, `brandRowNote` absent, `evidenceBoxes.length === 0` are each named). `evidenceBoxesOk` is guarded by `length > 0 &&`. These PASS verdicts are therefore substantive, not vacuous. The one deliberate accommodation — `ratingBadgePresent: false` does not force INCONCLUSIVE — is documented at line 41 and matches the TRUST-02 gate.

## Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|------|------|---------|----------|--------|
| `src/includes/jsonld.php` | 18 | `\uXXXX` | ℹ️ Info | Not a debt marker — the literal notation for a JSON unicode escape, inside a comment. |

- **`TBD` / `FIXME` / real `XXX`: zero** across all files this phase touched.
- **`TODO` / `HACK` / `PLACEHOLDER`: zero** in `src/`.
- **Temporary-skeleton sentence: gone** from the whole tree. (Grep matches on «временно/временен» are the ordinary Bulgarian word for "temporary" inside real body copy — false positives, checked individually.)
- **Dead specialist battery domain: zero references** in `src/`, source comments included (D3-12 upheld).
- **Contact-fact duplication: none.** No page body hardcodes a phone number or working hours; both stay single-sourced in `site-config.php` (RESEARCH P-11).
- **Unescaped output:** four `echo $…` sites exist (`header.php:66,171`, `category-page.php:205`, `brand-row.php:64`). All four emit code-selected class/attribute literals chosen by whitelist branching — never a request value and never page data. No unescaped output path introduced.

## Warnings

**W-1 — the full warranty page and the battery summary disagree.** `za-bateriite.html` renders «1 година гаранция на регенерирана батерия» with a «Пълни гаранционни условия» link to `warrently.html`. `warrently.html` states «Гаранционният срок за всички сервизни дейности и услуги … е 1 месец» and contains **no mention of batteries at all**. A visitor following that link reads that everything is one month.

This is *inherited*, not introduced: the legacy `site-current/warrently.html` likewise never mentioned batteries, while the legacy `za-bateriite.html` claimed a year — which is precisely why `site-config.php` models the two as distinct keyed facts and marks **both** `[ASSUMED]` against OWNER-QUESTIONS #23. Phase 3 did the correct thing by refusing to reconcile them by invention, and STATE.md:140 records a source comment forbidding harmonisation in either direction until #23 is ruled on.

What Phase 3 *did* change is the exposure: the 1-year claim now sits on a page reachable in one click from the homepage, directly above a link to a page that contradicts it. It should not be fixed by guessing. It should be a named rider on OWNER-QUESTIONS #23 so that whoever answers #23 knows the answer must also say whether `warrently.html`'s «всички услуги» clause needs a battery carve-out.

**W-2 — REQUIREMENTS.md was never updated during Phase 3.** Its last commit predates this phase (`26c4927`, phase 02), and its footer still reads *"Last updated: 2026-08-04"*. All nine Phase-3 IDs still show `[ ]` and `Pending` in the traceability table, so the project's own requirement ledger currently understates Phase 3 by seven satisfied requirements. Seven should move to Complete (TRUST-01, TRUST-03, DIFF-01, DIFF-02, DIFF-03, CONTENT-02, SEO-01); TRUST-02 and CONTENT-01 must stay Pending. Flagged rather than edited — this report does not write to planning ledgers.

**W-3 — the dev theme switcher renders on all 23 staging pages.** `<div class="dev-switcher">Тема B / Тема A</div>` is the first element in `<body>` on every served page. This is a deliberate Phase-2 artefact (`dev-switcher.php`, guarded by its own file existence) with an already-documented Phase-4 cutover step: *"rm src/includes/dev-switcher.php"*. Not a Phase-3 defect and not a leak into production — recorded only so it is not mistaken for one during review of `torin.bg/new/`.

**W-4 — the Search Console verification file is absent from the new tree.** `site-current/google1718743335455f1c.html` has no counterpart in `src/` and 404s under `/new/`. That is MIGR-02 (Phase 4) and out of scope here, but losing it at cutover would silently break Search Console ownership.

## Gaps Summary

**No gaps requiring closure planning.** Every must-have artifact across the nine plans exists, is substantive, is wired, and carries real data through to the served HTML. Every declared requirement ID is accounted for; none is orphaned. All seven probe runs pass on their own merits, and the two headline assertions — 23 unique metadata pairs, and the trust/differentiator surfaces being present and linked — were re-derived independently of the project's own gates against the live staging output.

The phase does not reach `passed` for one reason: two requirements are **deliberately unmet pending specific owner input**, and each was correctly left unmarked rather than being claimed. Judged on their own wording, TRUST-02 ("user sees a badge") and CONTENT-01 ("user sees dedicated content … as one of the six headline categories") are not observably true for a visitor today. Both are three-values-and-a-boolean away from being true, and neither is blocked by anything an engineer can supply.

Six further items need human judgement rather than code — five open owner questions (#3, #7, #16, #22, #23, #28) and one editorial read of the metadata as a set. The single behaviourally-unverified item is the rating badge's *enabled* render path, which has never executed in any environment.

---

_Verified: 2026-08-26_
_Verifier: Claude (gsd-verifier)_
