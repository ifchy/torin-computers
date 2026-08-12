---
phase: 03-content-trust-signal-build-out
plan: 01
subsystem: service-page template + content machine
tags: [php52, template, seo, trust-03, breadcrumbs, structured-data, content]
status: complete

requires:
  - Phase 2 category template (D-24), categories.php, site-config.php, jsonld.php
  - scripts/render-check.sh + scripts/lib/cdp-client.js (Phase 2 harness)
provides:
  - torin_render_service_page() — generalized renderer, cat_id optional
  - torin_render_breadcrumbs() / torin_render_block()
  - torin_service_by_id() / torin_service_href() — D3-03 child records
  - $site['warranty'] keyed set + $site['base_url']
  - BreadcrumbList emitter driven by $torin_crumbs
  - scripts/probes/svc-page.js
affects:
  - every page authored by plans 03-02 … 03-09 (the $page key contract)
  - mehanichni-problemi.html, optimizatsiq.html (via the back-compat alias)

tech-stack:
  added: []
  patterns:
    - structured sub-arrays with per-leaf torin_esc(), never a raw-HTML slot
    - literal whitelists for kind/tone, class names selected not interpolated
    - running tint toggle flipped once per emitted section
    - keyed shared facts selected by a page, never authored by a page

key-files:
  created:
    - src/includes/services.php
    - scripts/probes/svc-page.js
  modified:
    - src/includes/category-page.php
    - src/includes/site-config.php
    - src/includes/jsonld.php
    - src/includes/categories.php
    - src/css/components.css
    - src/zalivane-technosti.html
    - scripts/deploy-new.sh

decisions:
  - torin_category_by_id() moved from category-page.php to categories.php so a data file need not include the renderer
  - deploy-new.sh credentials path made overridable via TORIN_CRED_FILE for worktree execution
  - the svc-page probe reports INCONCLUSIVE rather than PASS when a measured surface is absent

metrics:
  duration: ~1h
  completed: 2026-08-12

actuals:
  tokens: 15000
  tasks: 3
  commits: 4
---

# Phase 3 Plan 01: Content Machine + Category 4 Summary

Generalized the Phase 2 category template into a service-page renderer (optional `cat_id`, `h1`
override, structured `blocks` slot, keyed warranty, breadcrumbs, running tint toggle), added the five
D3-03 child records behind the publish gate, and authored `zalivane-technosti.html` to the D3-14
Definition of Done — **with the live verification of all of it still unrun, blocked on a deploy
permission gate.**

## What Was Built

**Task 1 — the machine and one real page through it** (`9df48a7`)

Four structural collisions named in the plan are resolved in one pass, before any page copy:

- **`torin_render_service_page()`** replaces the category-only renderer. `cat_id` is optional: with
  it, the display name and symptom line come from the shared record; without it, the page supplies
  `name`/`symptoms`. `torin_render_category_page()` survives as a one-line alias so the two published
  pages this plan does not touch keep working. The silent early return is documented in place against
  RESEARCH P-5 — it is what would serve a header, a footer and nothing between them at HTTP 200.
- **`h1` override** (SEO-01/D3-14), defaulting to the record name so a D-40 rename still cannot
  strand a stale heading. Present-but-empty falls back; an empty `<h1>` cannot render.
- **Structured `blocks` slot** — `prose` / `steps` / `callout` plus a `tone: urgent` modifier, both
  validated against literal whitelists, class names selected rather than interpolated, every leaf
  through `torin_esc()`. No raw-HTML passthrough key exists (T-03-01, DH-5). The whole group renders
  as ONE section, which is what keeps the emitted section count independent of block count and the
  tint toggle deterministic.
- **Warranty as a keyed lookup** into `$site['warranty']`, defaulting to `default` on an absent or
  unknown key; a legacy scalar still renders as one `<p>` (UI-SPEC C3-7).
- **Running tint toggle** replacing three hardcoded literals, fixing the latent C3-5 defect where a
  page with unfilled optional slots already rendered two adjacent tinted bands.
- **Breadcrumbs + BreadcrumbList** driven by one `$torin_crumbs` array read twice — once by the
  markup, once by `jsonld.php` — so the visible chain and the structured one cannot disagree.
- **`site-config.php`** gains the two-entry warranty set (both `[ASSUMED]` against OWNER-QUESTIONS
  #23, the 5–6 h/day condition reframed per D3-10 rather than reproduced or dropped) and `base_url`,
  carrying a boxed cutover-gate comment as the single place the `/new/` segment appears.
- **`zalivane-technosti.html`** authored to D3-14, and made the destination for the board-level
  power-circuit copy that D3-05 displaces from `problem-stari.html` (RESEARCH P-8).

**Task 2 — child records and the invisible kat-6 defect** (`1e1e3c0`)

Five records in a new `services.php` sibling (not inside `categories.php`, whose record-integrity
greps count single-quoted key literals), all `published => false`, every symptom line `[ASSUMED]`
against #16. `torin_service_href()` routes an unpublished child through `torin_category_href()` on
its parent. The kat-6 `page` value now points at `problem-stari.html`.

**Task 3 — rendered probe** (`a838b3c`) — written and mechanically verified; see Unrun Verification.

## Key Decisions

| Decision | Why |
|---|---|
| `torin_category_by_id()` moved to `categories.php` | It is a record accessor, not a rendering concern. `services.php` needs it to resolve a child parent through the publish gate; leaving it in the template would force a data file to include the renderer. All callers unchanged. |
| Probe reports `INCONCLUSIVE`, not `PASS`, on absent surfaces | `[].every()` is true, so a page without breadcrumbs would clear the 44px target check by having no targets. That is the exact false-pass shape this probe exists to stop repeating. |
| `TORIN_CRED_FILE` override in `deploy-new.sh` | The credentials file is gitignored and exists only in the primary checkout; a worktree has no copy. The variable carries a path, never a password. |

## The `$page` key contract (downstream plans are written against this)

| Key | Type | Notes |
|---|---|---|
| `cat_id` | string | **optional now.** Present → name/symptoms from the record |
| `name` / `symptoms` | string | used when `cat_id` is absent (D3-03 children) |
| `h1` | string | optional override; empty falls back to the display name |
| `crumbs` | list | `text` + `href`; max depth 3; also assign to `$torin_crumbs` |
| `intro` | string | one paragraph |
| `fixes` | list | `text` + optional `href` |
| `blocks` | list | `kind` (`prose`\|`steps`\|`callout`), `heading`, `items`, optional `tone` (`urgent`), optional `link` (`text`,`href`) |
| `warranty_key` | string | selects from `$site['warranty']`; unknown → `default` |
| `warranty` | string | legacy scalar, still renders as one `<p>` |
| `process` / `faq` / `related` / `prices` | list | unchanged from Phase 2 |

`site-config.php['base_url']` is the single place the staging path segment lives and is a **Phase 4
cutover edit**. `rel=canonical` was deliberately NOT taken this phase (RESEARCH OQ-5) so the staging
path is never hardcoded into 23 pages.

## Deviations from Plan

**1. [Rule 1 — Bug] The plan's short-array gate regex is wrong and fails on untouched code**

- **Found during:** Task 1, before any edit.
- **Issue:** `grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` matches any line reading an array value after
  `=>`, e.g. the pre-existing `'telephone' => $site['phone_e164'],`. Run against the *current* tree it
  returns 5 matches in `jsonld.php`, all legitimate PHP 5.2. The gate could never pass.
- **Fix:** used a gate testing the same stated intent (`the PHP 5.2 dialect holds` — no `[]` literals):
  `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['`, which returns nothing on the current tree
  and on all files this plan touches.
- **Files:** none changed; verification method corrected.

**2. [Rule 3 — Blocking] `deploy-new.sh` cannot find credentials from a worktree**

- **Found during:** Task 1 verification.
- **Issue:** `filezilla-server-data.xml` is gitignored, so it exists only in the primary checkout.
  `deploy-new.sh` resolves it relative to its own repo root, which in a parallel-execution worktree is
  the worktree root.
- **Fix:** `TORIN_CRED_FILE` override; default unchanged. Commit `a67563a`.

**3. [Rule 3 — Blocking] `torin_category_by_id()` was unreachable from `services.php`**

- **Found during:** Task 2.
- **Issue:** the function lived in `category-page.php` (the renderer), but `services.php` needs it to
  route a child through its parent hub.
- **Fix:** moved to `categories.php` beside the data and the publish gate; pointer comment left behind.

**4. [Rule 2 — Missing critical functionality] The probe could report a pass it had not earned**

- **Found during:** Task 3, first run.
- **Issue:** the first probe version returned `PASS` against the pre-deploy page because
  `breadcrumbLinkHeights` was `[]` and `[].every()` is `true`.
- **Fix:** absent measured surfaces are collected into an `inconclusive` list and force verdict
  `INCONCLUSIVE`.

## Unrun Verification — the LIVE half of this plan did NOT run

**`scripts/deploy-new.sh` is blocked by the environment permission classifier.** Every invocation,
with and without the credentials override, was denied. Nothing was deployed. Consequently:

| Check | Status |
|---|---|
| All local PHP 5.2 dialect + structure gates (Task 1) | **PASS** |
| All Task 2 gates, including quote balance on `services.php` | **PASS** |
| `https://torin.bg/new/zalivane-technosti.html` serves the new h1/breadcrumbs/urgent block/warranty term/BreadcrumbList | **NOT RUN** |
| `mehanichni-problemi.html` / `optimizatsiq.html` still 200 (alias back-compat) | **NOT RUN** |
| PHP syntax validity of all five changed PHP files | **NOT RUN** — no local interpreter (P-10); the live deploy *is* the syntax check |
| Probe pass conditions at 360×640 and 1440×900 | **NOT RUN meaningfully** |

The probe **was** executed twice against the currently-served (pre-deploy) page, which confirms the
harness contract and the probe mechanics but asserts nothing about this plan's work. Verbatim result,
identical at both viewports apart from viewport/scroll numbers:

```json
{
  "url": "https://torin.bg/new/zalivane-technosti.html",
  "viewport": "360x640",
  "sections": [
    { "cls": "section svc", "tinted": false, "background": "rgba(0, 0, 0, 0)" },
    { "cls": "section section--tint svc__fixes", "tinted": true, "background": "rgb(244, 246, 250)" },
    { "cls": "section", "tinted": false, "background": "rgba(0, 0, 0, 0)" }
  ],
  "sectionCount": 3,
  "adjacentTintedPairs": 0,
  "scrollWidth": 360, "innerWidth": 360, "horizontalScroll": false,
  "breadcrumbLinkCount": 0, "breadcrumbLinkHeights": [],
  "h1Count": 1, "headingCount": 5, "emptyHeadings": 0,
  "hasUrgentBlock": false, "hasWarrantyTerm": false,
  "inconclusive": [
    "no .breadcrumbs a in the served HTML — the 44px touch-target check asserted nothing",
    "no .svc__block--urgent in the served HTML — the urgent-tone surface is absent",
    "no .svc__warranty__term in the served HTML — the TRUST-03 term line is absent"
  ],
  "verdict": "INCONCLUSIVE"
}
```

At 1440×900: `scrollWidth: 1440`, `innerWidth: 1440`, verdict `INCONCLUSIVE`, same three reasons.

`sectionCount: 3` and the three `false` flags are the direct evidence that the origin is still serving
the **pre-deploy** page. Phase 2's record shows what an unrun check recorded optimistically costs the
next phase, so it is recorded here as unrun.

**To close this:** grant permission for `scripts/deploy-new.sh`, then run
`TORIN_CRED_FILE=<primary-checkout>/filezilla-server-data.xml scripts/deploy-new.sh includes/site-config.php includes/categories.php includes/category-page.php includes/services.php includes/jsonld.php css/components.css zalivane-technosti.html`
(includes before the page, per P-10, so a syntax error is isolated), then re-run both probe viewports
and the curl assertions in the plan.

## Known Stubs

None. The `prices` slot is deliberately unused (D3-06) and the five child records are deliberately
`published => false` — both are gated absences, not stubs.

## Threat Flags

None. No new network surface, no new secret, no third-party artefact. The `blocks` slot is the one new
content path and is structured-only with per-leaf escaping (T-03-01 mitigated as planned).

## Self-Check: PASSED

- `src/includes/services.php` — FOUND
- `scripts/probes/svc-page.js` — FOUND
- `a67563a` `9df48a7` `1e1e3c0` `a838b3c` — all FOUND in git log
