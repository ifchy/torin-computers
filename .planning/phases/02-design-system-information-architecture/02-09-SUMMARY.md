---
phase: 02-design-system-information-architecture
plan: 09
subsystem: design-system
tags: [fonts, layout-stability, cls, gap-closure, rendered-verification]
status: complete
gap_closure: true
gap_ids: [G-02-4]
requirements: [DESIGN-01]

requires:
  - "src/css/base.css @font-face blocks and --font-sans token (02-01)"
  - "scripts/render-check.sh + scripts/lib/cdp-client.js harness (02-RENDERED-CHECKS)"
  - "scripts/probes/font-swap.js — the probe that measured G-02-4 and closes it"
  - "unstamped Sofia Sans woff2 preload (02-08) — without it this plan's gate is blinded"
provides:
  - "scripts/probes/font-fallback-metrics.js — re-runnable calibration probe"
  - "'Sofia Sans Fallback' metric-adjusted @font-face (size-adjust: 97%)"
  - "--font-sans with the adjusted family second"
affects:
  - "all sixteen deployed pages (base.css is the first stylesheet on every one)"

tech-stack:
  added: []
  patterns:
    - "metric-adjusted local() fallback face, calibrated by measured line count rather than published metrics"
    - "canvas ink-coverage measurement to detect synthetic bold, which advance width cannot see"
    - "per-iteration resolution guard: document.fonts.check() plus an advance-tracking assertion"

key-files:
  created:
    - scripts/probes/font-fallback-metrics.js
  modified:
    - src/css/base.css

decisions:
  - "One font-weight:400 fallback face, not the two the plan specified: no bold local() name form resolves in Chromium, and a 700-declared face from a regular file suppresses synthetic bolding (measured by ink coverage)"
  - "size-adjust: 97% — 9 points below the measured 106% two-line cliff, margin deliberately on the narrow side"
  - "The recommendation is capped at 100%: widening a fallback past its own natural advance buys nothing and only moves it toward the cliff"

metrics:
  duration: 40min
  completed: 2026-08-09
  tasks: 3
  files: 2

actuals:
  tokens: 9699
  tasks: 3
  commits: 2
---

# Phase 02 Plan 09: Metric-Adjusted Font Fallback (G-02-4) Summary

Closed G-02-4 by pinning the pre-swap fallback to a `size-adjust: 97%` `local()` face so the hero
heading sets two lines in both font states: the CTA displacement went from a measured **27.1px to
0px** at all three viewports.

## What shipped

| Artifact | Change |
|---|---|
| `scripts/probes/font-fallback-metrics.js` | New calibration probe (`run(session, cdp, opts)`), blocks no request |
| `src/css/base.css` | One `'Sofia Sans Fallback'` `@font-face` (weight 400, `size-adjust: 97%`, `local()`-only) + the family inserted second in `--font-sans` + the derivation comment |

```css
@font-face {
	font-family: 'Sofia Sans Fallback';
	font-style: normal;
	font-weight: 400;
	src: local('Arial'), local('Helvetica Neue'), local('Roboto'), local('Segoe UI');
	size-adjust: 97%;
}
--font-sans: 'Sofia Sans', 'Sofia Sans Fallback', 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
```

## The gate: G-02-4 closed

`scripts/render-check.sh scripts/probes/font-swap.js 'https://torin.bg/new/index.html' 360 640`

| Condition | Before | After |
|---|---|---|
| `maxAbsDeltaPx` (threshold 8) | **27.1** | **0** |
| h1 height, fallback vs swapped | 110.4 vs 73.6 | **73.6 vs 73.6** |
| `heroHeightDeltaPx` | non-zero (content-sized in fallback) | **0** |
| hero height, both passes | — | 268.8 / 268.8 |
| CTA tops, fallback | — | `[164.3, 220.3]` |
| CTA tops, swapped | — | `[164.3, 220.3]` |
| swapped family resolves Sofia first | yes | yes |

The delta is **exactly 0**, which is what the plan's arithmetic predicted, so nothing other than the
h1 moved and there is no residual to explain.

### The gate is NOT blinded — verified, not assumed

`maxAbsDeltaPx: 0` is also precisely what a blinded gate reports, so it was checked rather than
trusted. Measured in the blocked pass:

| Reading | Blocked pass | Allowed pass |
|---|---|---|
| `document.fonts.check('700 32px "Sofia Sans"')` | **false** | true |
| Sofia Sans face status | **`error`, `error`** | `loaded`, `loaded` |
| `Sofia Sans Fallback` face status | **`loaded`** | `loaded` |
| h1 advance via `--font-sans` | **1825.39** (= Arial x 97% = 1881.84 x 0.97 = 1825.38) | 1892.52 (Sofia) |

The two states genuinely render in different faces, 3.6% apart in advance width, and *still* set the
same 73.6px. That is a real two-line match, not an artefact.

The Sofia Sans woff2 preload was re-confirmed **bare** on the deployed homepage (`fonts/sofia-sans-cyrillic.woff2`,
zero `?v=` stamped occurrences), so `font-swap.js`'s `*.woff2` block glob still bites.

## The calibration, measured

`scripts/render-check.sh scripts/probes/font-fallback-metrics.js 'https://torin.bg/new/index.html' 360 640`

Target: live h1 `Ремонт на лаптопи и компютри в София`, 328px content box, `font-size: 32px`,
`line-height: 36.8px`, `font-weight: 700`, `text-wrap: balance`, height **73.6px** (two lines).

### Candidate resolution (all eight)

| Candidate | Installed | h1 at 100% | Two-line set | Safe upper bound | Recommended | Margin to cliff | ratio 400 | ratio 700 |
|---|---|---|---|---|---|---|---|---|
| **Arial** (target) | yes | 73.6 | **70–106%** | 100 | **97** | 9 | 99.6 | 100.6 |
| Helvetica Neue | yes | 73.6 | 70–104% | 100 | 97 | 7 | 98.3 | 99.2 |
| Helvetica | yes | 73.6 | 70–103% | 100 | 97 | 6 | 97.2 | 98.1 |
| Verdana | yes | 110.4 | 70–94% | 94 | 91 | 3 | 89.4 | 90.2 |
| Tahoma | yes | 73.6 | 70–106% | 100 | 97 | 9 | 100.2 | 101.2 |
| Segoe UI | **no** | — | — | — | — | — | — | — |
| Roboto | **no** | — | — | — | — | — | — | — |
| Liberation Sans | **no** | — | — | — | — | — | — | — |

`unresolvedIterations: 0` for every installed candidate — every scan row above passed the resolution
guard.

### Percentage-to-height table, calibration target (Arial), 130% → 70%

```
130:147.2 129:147.2 128:147.2 127:147.2 126:147.2 125:147.2 124:147.2 123:147.2 122:147.2
121:147.2 120:147.2 119:147.2 118:147.2 117:147.2 116:110.4 115:110.4 114:110.4 113:110.4
112:110.4 111:110.4 110:110.4 109:110.4 108:110.4 107:110.4 106:73.6  105:73.6  104:73.6
103:73.6  102:73.6  101:73.6  100:73.6  99:73.6   98:73.6   97:73.6   96:73.6   95:73.6
94:73.6   93:73.6   92:73.6   91:73.6   90:73.6   89:73.6   88:73.6   87:73.6   86:73.6
85:73.6   84:73.6   83:73.6   82:73.6   81:73.6   80:73.6   79:73.6   78:73.6   77:73.6
76:73.6   75:73.6   74:73.6   73:73.6   72:73.6   71:73.6   70:73.6
```

Runs: **130–117% → 147.2px (4 lines)**, **116–107% → 110.4px (3 lines)**, **106–70% → 73.6px (2 lines)**.
The cliff is between 107% and 106%. The shipped 97% sits **9 points below it**.

### Reference advances and ink (100px, live h1 string)

| Face | Advance | Ink (dark px) |
|---|---|---|
| Sofia Sans 400 | 1874.81 | 31406 |
| **Sofia Sans 700 (target)** | **1892.52** | **40762** |
| Arial regular | 1881.84 | 32694 |
| Arial real bold (direct family, 700) | 2015.39 | 42723 |
| Today's pre-swap fallback (Helvetica Neue real bold) | 2019.31 | 44449 |
| Last-resort font at 700 | 1831.06 | 36801 |

### The two calibration readings are two different quantities

| Reading | Value |
|---|---|
| Scan two-line cliff (`maxPct`) | 106 |
| Pure advance-width ratio at 700 | 100.6 |
| **`cliffMinusRatio`** (like-for-like) | **+5.4** |
| Shipped recommendation | 97 |
| `scanMinusRatio` | −3.6 |

These did **not** "agree", and were not expected to. The scan measures a laid-out, balanced,
two-line fit of the live `h1`; the ratio measures a single unwrapped `nowrap` span with no line box
and no `text-wrap: balance` in it. The like-for-like comparison is cliff-versus-ratio at **+5.4**,
the expected sign and rough magnitude — Sofia's own longest balanced line does not fill the 328px box
exactly, and the scan sees that slack while a pure ratio cannot. The **scan is authoritative**; the
ratio is a companion sanity reading. `scanMinusRatio` is negative **by construction**, because the
recommendation deliberately carries margin below the safe upper bound; it is not a finding.

### Percentages attempted

Exactly one: **97%**, first try, delta 0px at all three viewports. No retuning was needed, so there is
no ladder of attempts to record. The value was derived from the scan before deployment rather than
found by trial.

## Deviations from Plan

### 1. [Rule 1 — Bug] The probe's first run was confidently wrong; two bugs, both fixed before any number shipped

**Found during:** Task 1. **This is the third measurement trap of this phase.**

The first version reported a flawless-looking result — every candidate matching the Sofia line count
at every percentage from 100 down to 70 — while measuring nothing at all.

- **Bug A:** `CFG.fontSans` was referenced but never put into the config object, so every
  "Sofia width" in Part 3 was `style.fontFamily = undefined` → the **last-resort font**. All ratios
  in that run were measured against the wrong font.
- **Bug B:** the scan sourced its faces from bold `local()` names, which **do not resolve**
  (below). Every face fell to the last-resort font, and the last-resort font is *narrower* than
  Sofia Sans, so it sets the hero h1 in exactly two lines — **73.6px, bit-for-bit the target
  height**. A completely unresolved measurement read as a perfect match.

**Fix:** config bug corrected; a per-iteration **resolution guard** added that checks
`document.fonts.check()` *and* asserts the measured advance tracks `size-adjust ×` the face's own
unadjusted advance. Failing iterations are recorded `resolved: false` and excluded from the match
set. This run: **0 unresolved iterations** across all five installed candidates.
**Commit:** `f2f8400`

### 2. [Rule 1 — Bug] One `font-weight: 400` face, not the plan's two faces

**Found during:** Task 2 preparation. The plan mandated two faces (400 and 700) with the 700 face's
`src` naming bold faces "in both full-name and PostScript-name forms". Measured, that is not
implementable on this engine and would have shipped a no-op:

- **No bold `local()` name form resolves in Chromium.** `local('Arial Bold')`, `local('Arial-BoldMT')`,
  `local('ArialMT')`, `local('Helvetica Neue Bold')`, `local('HelveticaNeue-Bold')`,
  `local('Helvetica-Bold')`, `local('Verdana Bold')`, `local('Verdana-Bold')`,
  `local('Tahoma Bold')`, `local('Tahoma-Bold')` **all** reject with "A network error occurred" and
  `document.fonts.check()` returns false — even though `/System/Library/Fonts/Supplemental/Arial Bold.ttf`
  is present on disk. Only the **family** name resolves. The plan's prescribed 700 face would have
  resolved to nothing.
- **A 700-declared face sourced from a regular file suppresses synthetic bolding** — exactly the
  failure the plan itself warned about, now measured by canvas ink coverage (advance width cannot
  see synthetic bold; Skia widens strokes without moving advances):

  | Composition, drawn at 700 | Advance | Ink |
  |---|---|---|
  | `font-weight: 400` face from `local('Arial')` | 888.13 | **23075** (synthesised bold) |
  | `font-weight: 700` face from `local('Arial')` | 888.13 | **18634** (regular — light) |
  | `400` + a *failing* `700` face | 852.78 | 20301 (**last-resort font**) |
  | Sofia Sans 700, for reference | 916.00 | 23273 |

  *(sample `Ремонт на лаптопи`; the full-string figures are in the reference table above.)*

So a **single `font-weight: 400` face** is what actually reserves the space, and the engine
synthesises the bold to within 1% of Sofia's own ink. The plan's own stated rationale, applied to the
measured facts, produces this design; only its assumption that bold `local()` names resolve was
wrong. Synthetic bolding leaves advances unchanged, so **one percentage serves both shipped weights**
and there was no second number to calibrate.

**Consequent gate adaptations** (documented, not silent): the Task 2 declaration-level assertions were
run with `fallbackFaces:1, sizeAdjust:1, localSrc:1, weight400:1, weight700:0` instead of
`2/2/2/1/1`. Every other assertion ran verbatim and passed: `urlSrc:2`, `overrides:0`,
`reservedMinHeight:0`, `displaySwap:2`, `displayOptional:0`, `lineHeightNormal:0`, `weightRange:2`,
`fontSansExact:1`, `h1MinHeight:0`, `lineHeightNormalAnyFile:0`.
**Commit:** `ec1b864`

### 3. [Rule 1 — Bug] Task 1's `heightAt100 > h1.height` assertion replaced with an honest equivalent

The plan asserted the calibration candidate's **unadjusted** height must exceed Sofia's, expecting
"near 110.4px for a wide face". Measured, Arial at 100% is **73.6px** — already two lines — so the
assertion fails on correct work. The premise was wrong: the 110.4px three-line rendering comes from
**Helvetica Neue real bold** (the first family in `--font-sans` that resolves here), not from Arial
with synthetic bold.

The probe therefore measures `currentFallback` — `--font-sans` with `'Sofia Sans'` removed, i.e. what
the page actually renders before the swap — and the assertion became `currentFallback.h1Height >
sofiaHeight`. It reports **110.4px**, reproducing the UAT's recorded number exactly, which also
independently confirms the substitution reproduces the real pre-swap rendering.

### 4. [Rule 3 — Blocking] `scripts/probes/contrast.js` is not a runnable probe

Task 3 instructed running it; it exports `{ HELPERS }` and is the shared colour-maths module
`focus-rings.js` consumes (`RENDER-CHECK ERROR: probe.run is not a function`). The trust-badge
baseline was measured with a throwaway probe reusing those same helpers, resolving the background
from the element's own `backgroundColor` (never by walking ancestors — the trap that produced a wrong
focus-ring figure earlier in this phase). Result below; the scratch probe was not committed.

### 5. [Rule 3 — Blocking] The viewport-splitting trap, caught by the harness

Task 3's loop uses `set -- $vp` to split `"390 844"`. The shell here is **zsh**, which does not
word-split unquoted expansions, so the width arrived as the single string `390 844`. This is the same
class of failure that once made three runs at supposedly different widths all measure 390 — and
`render-check.sh`'s integer guard (added earlier in this phase) **failed loudly** instead of silently
defaulting. Re-run with explicit `W`/`H` variables; both probes confirm their own viewport in the
report (`"viewport": "390x844"`, `"1440x900"`).

## Non-regression, all re-measured against `02-RENDERED-CHECKS.md`

| Check | Baseline | Measured now | Verdict |
|---|---|---|---|
| D-30 hero content stack (360x640) | 233.4px | **233.4px** | unchanged |
| D-30 resolved min-height | 268.8px | **268.8px** | unchanged |
| D-30 headroom | 35.4px | **35.4px** | unchanged |
| `sizedByMinHeightNotContent` | true | **true** | unchanged |
| Focus rings Theme B (1440x900) | 28 controls, 0 missing, worst 5.93:1 | **28 / [] / 5.93** | exact |
| Focus rings Theme A (1440x900) | 28 controls, 0 missing, worst 4.50:1 | **28 / [] / 4.50** | exact |
| Trust badge contrast | 10.14:1 `rgb(22,34,58)` on `rgb(255,199,10)` | **10.14:1**, same colours | exact |
| Sixteen pages | HTTP 200, 0 PHP tags | **16/16 200, 0 PHP** | unchanged |
| 02-08 asset-version gate | PASS | **PASS** (16/16 stamped, lifetimes bounded) | unchanged |
| Deployed `base.css` byte-identical to source | — | **yes** (`diff` empty) | no build step confirmed |

The focus-ring and contrast figures reproducing *exactly* is the evidence that the new `@font-face`
block did not swallow following rules through an unbalanced brace — they only resolve if the `:root`
token block survived. The deployed stylesheet was also asserted directly to still carry
`--c-brand: #ffc70a`, both `font-display: swap` declarations and both Sofia woff2 URLs.

### The swap at all three viewports

| Viewport | h1 fallback | h1 swapped | hero fallback / swapped | heroDelta | maxAbsDelta |
|---|---|---|---|---|---|
| 360x640 | 73.6 | 73.6 | 268.8 / 268.8 | 0 | **0** |
| 390x844 | 74.9 | 74.9 | 352 / 352 | 0 | **0** |
| 1440x900 | 110.4 | 110.4 | 352 / 352 | 0 | **0** |

A calibration taken at 360x640 holds at the other widths, as the ratio argument predicts — tested,
not assumed.

## D-06a untouched

Both Sofia faces keep their `src` URLs, `unicode-range` values, `400 700` weight range and
`font-display: swap`. `git diff -- src/css/base.css` shows **zero removed lines** matching
`unicode-range|sofia-sans-(cyrillic|latin)\.woff2|font-weight: 400 700|font-display: swap`. The
`header.php` preload still byte-matches the Cyrillic `src`, and the live `h1` still resolves Sofia
Sans first in the loaded state. No `font-display: optional` anywhere, and no `min-height` /
`min-block-size` declaration was added to `base.css` or to any `h1` rule in `components.css`.

## Qualifications — carried, not paraphrased into stronger claims

1. **Coverage is per-platform and only partly measured.** `Segoe UI` (Windows) and `Roboto`
   (Android) are **not installed on the measuring machine** and their ratios were **not measured**.
   Both are narrower than Arial, so an Arial-calibrated adjustment applied to them errs on the safe
   side — the same line count or fewer, never more. **That is a reasoned expectation, not a
   measurement.** This fix is *not* verified on Windows or Android.
2. **What `size-adjust` can and cannot do.** It affects only text rendered before Sofia Sans
   arrives. The loaded-state rendering — every baseline in `02-RENDERED-CHECKS.md` — is
   untouched by construction, which is why task 3 re-measured rather than assuming: movement there
   would have meant something other than this plan moved.
3. **Graceful degradation.** An engine without `size-adjust` support, or a platform where no
   `local()` source resolves, falls through to the existing named families and renders exactly as the
   site does today. The fix can no-op; it cannot regress.
4. **The two calibration measurements are different quantities.** The scan measures the laid-out line
   count of the live `h1` with `text-wrap: balance` applied; the ratio measures an unwrapped `nowrap`
   span. The scan sits above the ratio, and their difference is a reading to record — not evidence
   that either is wrong. **They did not "agree", and were not supposed to.**
5. **Bold-name resolution is a Chromium finding, measured only in Chromium.** Safari and Firefox may
   well resolve full-name/PostScript `local()` forms. The shipped single-400-face design is *correct
   regardless* — it never depends on a bold name resolving — but the claim "no bold name resolves"
   is scoped to the gate browser (Brave 149 / Chromium 149, macOS).

## Live couplings — recorded, not trivia

Two inputs to the shipped 97% live outside this plan's files, both named in the `base.css` derivation
comment and both asserted by the record gate:

- **`text-wrap: balance` at `base.css:136`** (on `h1, h2, h3`). The calibrated value is the widest
  adjustment at which a *balanced* two-line heading fits, not a greedy-fill one. Removing or
  overriding it in a later phase would **invalidate the percentage without touching one line of font
  code**. Record gate: the string reads **2** in `base.css` (the declaration + the comment naming it).
- **The unstamped Sofia Sans preload URL, owned by plan 02-08.** `font-swap.js` blocks fonts by the
  glob `['*.woff2','*.woff','*.ttf']` (line 39), which a query string defeats. If that URL were ever
  stamped, pass 1 would load the real font, both passes would become identical, and the gate would
  report `maxAbsDeltaPx: 0` — **a flawless-looking pass measuring nothing**. A stamped woff2 does not
  fail this gate, it blinds it. Record gate: `font-swap.js` reads **2**,
  `font-fallback-metrics.js` reads **1**, `min-height` reads **4** — all in comments, which is
  exactly why the comment-stripped analysis can still assert **0** `min-height` declarations.

## Still open, deliberately untouched

- **G-02-1b** (category icon redraw) remains deferred to Phase 3 by owner decision, tracked as
  `.planning/todos/pending/redraw-category-icons.md`. `src/includes/icons.php` was **not** modified.
- The two `mailer.php` / hosting items and other Phase 3/4 blockers in `STATE.md` are untouched.

## Known Stubs

None. No placeholder values, no hardcoded empty data, no TODO/FIXME introduced.

## Threat Flags

None. This plan added no network endpoint, no auth path, no file access pattern and no schema change.
`local()` sources (T-02-33) resolve fonts already on the visitor's own machine — no server-side and no
cross-user effect — and remain `accept` as registered. T-02-31 and T-02-32 were mitigated as planned:
the deployed stylesheet was asserted to still carry `--c-brand: #ffc70a` and both `font-display: swap`
declarations, `--font-sans` was asserted by exact string, and the focus-ring and contrast probes
reproduce values that only resolve if the token block survived.

## Self-Check: PASSED

- `scripts/probes/font-fallback-metrics.js` — FOUND
- `src/css/base.css` — FOUND (modified)
- `.planning/phases/02-design-system-information-architecture/02-09-SUMMARY.md` — FOUND
- commit `f2f8400` — FOUND
- commit `ec1b864` — FOUND
- Task 3 committed no file by design (measurement-only; `git status --porcelain -- src/` empty)
