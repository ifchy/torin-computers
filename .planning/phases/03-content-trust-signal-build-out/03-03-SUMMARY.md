---
phase: 03-content-trust-signal-build-out
plan: 03
subsystem: category-2 hub + first two child pages
tags: [checkpoint, blocked, seo-01, trust-03, one-way-door, slugs]
status: blocked

requires:
  - 03-01 torin_render_service_page(), $page key contract, services.php child records
  - 03-02 brand row, evidence strip, deploy-time CSS comment stripper
provides: []
affects:
  - 03-07 (creates the remaining three category-2 children against the slug set decided here)

tech-stack:
  added: []
  patterns: []

key-files:
  created: []
  modified: []

decisions: []

metrics:
  duration: ~15m (context load only)
  completed: null

actuals:
  tokens: 0
  tasks: 0
  commits: 0
---

# Phase 3 Plan 03: Category-2 Hub + First Two Children — BLOCKED AT TASK 1

**No production code was written.** Plan 03-03 opens with a `checkpoint:decision` (Task 1) that the
plan itself places *before any file is created*, and GSD auto mode is off
(`workflow.auto_advance: false`, `workflow._auto_chain_active: false`), so the executor stopped
there as specified rather than selecting a permanent URL set on the user's behalf.

## Why this checkpoint was not auto-resolved

Task 1 carries `gate="blocking"`, which under GSD's auto-mode rules would normally auto-select the
first option. Auto mode is **not** active in this project's config, so standard checkpoint behaviour
applies: stop and return the decision to the user.

That is also the substantively correct outcome here. The plan's own objective states the reason:

> This is also where the phase's one genuinely one-way door sits. Every other structural decision in
> this phase is either reversible or already walked through by the user in CONTEXT. The five child
> filenames are not.

Publishing a slug and letting it be indexed cannot be cheaply undone — reverting needs a second
redirect layer and forfeits accumulated ranking signal twice (D3-01/SEO-04), and Search Console
access is still unavailable (OWNER-QUESTIONS #1), so nobody could observe whether a later correction
worked.

## Work deliberately NOT started

Tasks 2 and 3 are both downstream of the decision and neither was begun.

The one judgement call worth recording: `src/ekran-klaviatura-portove.html` (Task 2) is *invariant*
across all three options — it hand-types no child filename, resolving every href through
`torin_service_href()`, so amending a slug would not change one byte of it, and even the
`defer-children` option ships the hub. Authoring it early was therefore tempting. It was **not**
done, because the plan's objective explicitly sequences the decision "before any file is created,"
and because Task 2 also owns the two `published => true` flips that *are* the one-way door. Splitting
a task around a checkpoint would have produced a non-atomic commit and a page whose verification gate
(`grep -c "'published' *=> *true" src/includes/services.php` must return exactly 2) could not pass.

## Verified during context load

- **No slug collision.** None of the five proposed filenames appears in
  `01-URL-INVENTORY.md`'s sixteen frozen URLs. All five are new files; approving them retires
  nothing and introduces no redirect.
- **The records already exist and are correctly gated.** All five entries in
  `src/includes/services.php` carry `'published' => false`, and `torin_service_href()` routes each
  one through `torin_category_href()` on its `kat-2` parent, so nothing currently points at a file
  that would 404.
- **`kat-2` is still unpublished** in `src/includes/categories.php`, so the homepage card and the
  Услуги dropdown currently route to `index.html#kat-2`. Published category count is **3**
  (kat-1, kat-3, kat-4); Task 2 would take it to 4.

## Inherited defect confirmed (carried forward, not yet applied)

The plan's PHP 5.2 short-array gate — `grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` — is broken exactly as
plans 03-01 and 03-02 recorded: it matches any line *reading* an array value to the right of `=>`
(e.g. `'telephone' => $site['phone_e164'],`), so it fires on untouched, valid PHP 5.2 and can never
pass. The continuation agent must substitute the form both prior plans used:

```
grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['
```

## Known Stubs

None — nothing was built.

## Threat Flags

None — no files changed.

## Self-Check: PASSED

- Zero production commits claimed, zero found — consistent.
- `src/ekran-klaviatura-portove.html` — correctly ABSENT (not yet authored).
- `src/smyana-na-matrica.html` — correctly ABSENT.
- `src/smyana-na-klaviatura.html` — correctly ABSENT.
- `src/includes/services.php` published count — 0, unchanged from 03-01.
- `src/includes/categories.php` published count — 3, unchanged from Phase 2.
