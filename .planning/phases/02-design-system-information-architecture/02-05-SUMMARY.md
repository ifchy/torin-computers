---
phase: 02-design-system-information-architecture
plan: 05
subsystem: ui
tags: [css, cascade, specificity, wcag, contrast, focus-visible, accessibility]

requires:
  - phase: 02-design-system-information-architecture
    provides: "The hand-authored three-file stylesheet system (base.css -> layout.css -> components.css), the :root token set including the unused --c-focus-on-dark, and the deployed staging tree at torin.bg/new/"
provides:
  - "«Безплатна диагностика» trust badge legible on the amber fill in both themes (10.14:1 / 9.80:1), replacing an effectively invisible 1.06:1 / 1.29:1"
  - "A visible focus ring on all six primary CTAs that sit on a dark surface (hero x2, site footer x2, sticky call bar x2), in both themes, at 4.50:1-11.09:1 against the surface the offset ring is actually painted on"
  - "Two corrected stylesheet comments that describe the cascade that actually applies and state why their rule must not be simplified away"
  - "A proven edit -> FTPS deploy -> live re-fetch -> recomputed-ratio loop that plans 02-06 and 02-07 ride"
affects: [02-06, 02-07, 03-content, 04-hardening-cutover]

actuals:
  tokens: 1983
  tasks: 2
  commits: 2

tech-stack:
  added: []
  patterns:
    - "Cascade collisions are closed by scoping the WINNING selector, never by escalating the loser with !important or an id selector"
    - "A focus ring drawn with an outline offset is measured against the surface BEHIND the control, never against the control's own fill"
    - "Contrast ratios asserted in a comment are recomputed at edit time, in both themes, and rewritten in the same edit as the rule they describe"

key-files:
  created: []
  modified:
    - src/css/components.css
    - src/css/base.css

key-decisions:
  - "CR-01 closed by scoping .hero p down to .hero__inner > p:not(.trust-badge) rather than raising .trust-badge — specificity discipline is the only cascade mechanism this codebase has (no @layer, no nesting, no preprocessor), so escalating the loser would invite the next escalation"
  - "The scoped hero prose rule matches zero elements in the deployed tree today and is kept deliberately inert, so a Phase 3 hero paragraph inherits --c-on-dark-muted instead of full-white --c-on-dark without anyone re-adding the unscoped form"
  - "CR-02 closed with a (0,3,0) per-dark-surface group rather than an inherited --c-focus-ring custom property: the explicit form keeps the reasoning in specificity, and the custom-property form would have changed the light-surface ring from #16223a (15.86:1) to --c-focus #0b4a9c, altering something already correct"
  - "02-REVIEW.md's 5.16:1 and 8.7:1 focus-ring figures are THEME A values presented as if they were the shipping theme; recomputed Theme B measures 9.49:1 and 11.09:1, so the review understated the fix by roughly 4 points"
  - "The fresh 360x640 hero-stack measurement could not be automated (no Chrome/Chromium/Playwright on the machine, Safari remote automation disabled), so the hero comment records 241.6px explicitly as DERIVED rather than asserting an unmeasured number as fact"

patterns-established:
  - "Comment integrity: a contrast ratio in a comment names the pair that genuinely applies after the cascade resolves, and carries the load-bearing 'do not simplify' instruction rather than a bare number"
  - "Region-scoped negative assertions (awk range + grep -c) are paired with a non-emptiness proof, so a range whose start delimiter stops matching cannot pass vacuously"

requirements-completed: [DESIGN-01]

coverage:
  - id: D1
    description: "«Безплатна диагностика» renders as dark on-brand ink on the amber fill in both themes, above the 4.5:1 WCAG 2.1 SC 1.4.3 floor"
    requirement: "DESIGN-01"
    verification:
      - kind: other
        ref: "grep -cE '^\\.hero__inner > p:not\\(\\.trust-badge\\)[[:space:]]*\\{' src/css/components.css == 1 && grep -cE '^\\.hero p[[:space:]]*\\{' == 0"
        status: pass
      - kind: other
        ref: "node -e WCAG relative-luminance: #16223a on #ffc70a = 10.14:1; #1a1200 on #fbad03 = 9.80:1; replaced #c9d6ea on #ffc70a = 1.06:1 (FAILS as expected)"
        status: pass
      - kind: other
        ref: "curl -s 'https://torin.bg/new/css/components.css?v=<ts>' contains the scoped rule once, the unscoped form zero times; index.html HTTP 200 with one class=\"trust-badge\" and zero raw <?php"
        status: pass
    human_judgment: false
  - id: D2
    description: "The badge keeps its own margin-block-start at both breakpoints (--sp-sm below 35rem, --sp-lg at and above)"
    requirement: "DESIGN-01"
    verification:
      - kind: other
        ref: "awk '/^\\.trust-badge \\{/,/^}/' src/css/components.css | grep -c 'margin-block-start: var(--sp-sm);' == 1; grep -c '.trust-badge { margin-block-start: var(--sp-lg); }' == 1"
        status: pass
    human_judgment: false
  - id: D3
    description: "The mobile hero content stack stays strictly under the resolved clamp(16rem, 42svh, 22rem) min-height, and the first category card's title keeps its above-the-fold headroom"
    requirement: "DESIGN-01"
    verification: []
    human_judgment: true
    rationale: "No source read can measure a rendered stack height. Browser measurement could not be automated on this machine (no Chrome/Chromium/Playwright installed; Safari remote automation requires manual enablement in Safari Settings). The 8px reduction is arithmetically certain in direction — the badge margin drops from --sp-md 16px to --sp-sm 8px, so the fold GAINS headroom — but the absolute figure is derived, not measured."
  - id: D4
    description: "All six primary CTAs on a dark surface draw a focus ring at >= 3:1 (WCAG 2.1 SC 1.4.11) against the surface the 2px-offset ring is actually painted on, in both themes"
    requirement: "DESIGN-01"
    verification:
      - kind: other
        ref: "grep -cE '^\\.callbar :focus-visible' / '^\\.hero \\.btn--primary:focus-visible' / '^\\.site-footer \\.btn--primary:focus-visible' / '^\\.callbar \\.btn--primary:focus-visible' src/css/base.css == 1 each"
        status: pass
      - kind: other
        ref: "node -e WCAG: #ffd84d = 9.49 / 5.93 / 4.69 / 11.09:1 (Theme B) and 5.16 / 4.50 / 8.38:1 (Theme A); replaced 1.21 / 1.03 / 2.60:1 all FAIL as expected"
        status: pass
      - kind: other
        ref: "curl -s 'https://torin.bg/new/css/base.css?v=<ts>' contains .callbar :focus-visible and .callbar .btn--primary:focus-visible once each; index/about/mehanichni-problemi all HTTP 200"
        status: pass
    human_judgment: false
  - id: D5
    description: "Light-surface primary CTAs are unchanged and keep --c-on-brand at 15.86:1 on white; --c-focus-on-dark (1.38:1 on white) reaches no light-surface selector"
    requirement: "DESIGN-01"
    verification:
      - kind: other
        ref: "grep -cE '^\\.btn--primary:focus-visible \\{ outline-color: var\\(--c-on-brand\\); \\}' src/css/base.css == 1; grep -c 'var(--c-focus-on-dark)' == 2 (both in surface-scoped groups)"
        status: pass
      - kind: other
        ref: "node -e WCAG: #16223a on #ffffff = 15.86:1 PASS; #ffd84d on #ffffff = 1.38:1 FAILS-AS-EXPECTED"
        status: pass
    human_judgment: false
  - id: D6
    description: "The indicator itself was not weakened to make the numbers pass, and no escalation (importance flag, id selector, @layer) was used to win either cascade"
    requirement: "DESIGN-01"
    verification:
      - kind: other
        ref: "grep -c 'outline: 3px solid var(--c-focus)' == 1; grep -c 'outline-offset: 2px' == 1; grep -cE 'outline:[[:space:]]*(none|0)' == 0; grep -cE '^#' == 0 in both files; components.css !important == 0, @layer == 0"
        status: pass
      - kind: other
        ref: "Region-scoped, with non-emptiness proof: awk '/^:focus-visible \\{/,/prefers-reduced-motion/' base.css | grep -c 'outline-offset: 2px' == 1 and | grep -c '!important' == 0; awk '/prefers-reduced-motion/,/^}/' | grep -c '!important' == 4 (unchanged)"
        status: pass
      - kind: other
        ref: "grep -c 'c-focus-on-dark' src/css/theme-a.css == 0; grep -cE '^\\t--c-' src/css/theme-a.css == 10 (dev-override tripwire intact)"
        status: pass
    human_judgment: false
  - id: D7
    description: "Keyboard-observed focus ring behaviour across the hero, footer, call bar and a category page, in both themes, with no ring clipped by the sticky call bar"
    requirement: "DESIGN-01"
    verification: []
    human_judgment: true
    rationale: "A focus ring's visibility under Tab navigation cannot be asserted from source or from a computed ratio — the ratios prove the colour pair, not that the ring paints unclipped at every control. Routed to the end-of-phase UAT batch (workflow.human_verify_mode = end-of-phase)."
  - id: D8
    description: "Web-font swap (FOUT) reflow does not visibly displace the two hero CTA buttons on a throttled connection"
    verification: []
    human_judgment: true
    rationale: "Carried-forward backstop from 02-UI-SPEC ## UI Considerations. This plan changes the hero stack height by 8px, so the check is RE-OPENED rather than inherited from 02-01. It remains unclosed — this plan does not claim to close it."

duration: 20 min
completed: 2026-08-06
status: complete
---

# Phase 02 Plan 05: CSS Cascade Defect Closure (CR-01, CR-02) Summary

**Two specificity collisions closed by scoping the winning selector — the hero trust badge goes from 1.06:1 to 10.14:1, and all six dark-surface primary CTAs regain a focus ring at 4.50:1–11.09:1 — with both files re-fetched from the live staging origin and every ratio recomputed rather than inherited from the review.**

## Performance

- **Duration:** 20 min
- **Started:** 2026-08-06T13:38:00Z
- **Completed:** 2026-08-06T13:58:00Z
- **Tasks:** 2
- **Files modified:** 2

## Accomplishments

- **CR-01 closed.** `.hero p` (0,1,1) was beating `.trust-badge` (0,1,0) regardless of source order, repainting «Безплатна диагностика» in `--c-on-dark-muted` #c9d6ea on the `--c-brand` #ffc70a fill — 1.06:1, effectively invisible, above the fold, on the homepage, in both themes. Replaced with `.hero__inner > p:not(.trust-badge)` (0,2,1), which cannot reach the badge. The intended `--c-on-brand` pair now applies at 10.14:1 (Theme B) / 9.80:1 (Theme A).
- **The badge's own margins apply again.** The same collision was overriding `margin-block-start`, so the badge was taking `--sp-md` 16px instead of its own `--sp-sm` 8px (and `--sp-lg` 24px at ≥35rem). No edit was needed for this — it is the consequence of the scoping change, and it means the mobile fold *gains* 8px of headroom rather than losing it.
- **CR-02 closed.** `outline-offset: 2px` paints the ring entirely on the surface *behind* the button, never on the amber fill the old comment reasoned about. `.btn--primary:focus-visible` and the dark-surface group tied at (0,2,0), so the later one won everywhere and six primary CTAs gave keyboard users a ring at 1.03–1.21:1. Added `.callbar :focus-visible` to the dark-surface group, plus a new (0,3,0) group for `.hero`/`.site-footer`/`.callbar` `.btn--primary` that beats (0,2,0) unconditionally — by specificity, not by source order, so it survives a future reordering.
- **Both misleading comments corrected in the same edit as their rules.** Each now states the pair that genuinely applies, its recomputed ratio in both themes, and the load-bearing reason it must not be simplified away.
- **The edit → FTPS deploy → live re-fetch → recomputed-ratio loop is proven** (tracer). Plans 02-06 and 02-07 ride the same rails.

## Task Commits

1. **Task 1 (tracer): CR-01 trust-badge cascade** — `f8bab65` (fix)
2. **Task 2: CR-02 dark-surface focus ring** — `b527413` (fix)

## Files Created/Modified

- `src/css/components.css` — hero prose rule scoped to `.hero__inner > p:not(.trust-badge)`; trust-badge comment rewritten to state the applying pair in both themes and why the scoped selector must not collapse; hero padding comment rewritten to lead with the min-height invariant and name the badge margin as a term in the stack.
- `src/css/base.css` — `.callbar :focus-visible` added to the dark-surface ring group; new (0,3,0) group for `.hero`/`.site-footer`/`.callbar` `.btn--primary:focus-visible`; focus comment rewritten to reason about the surface the offset ring lands on rather than the button fill.

## Recomputed Contrast Ratios (all twelve, verbatim)

Computed locally with the WCAG 2.x relative-luminance formula from the declared token hexes. **Not copied from `02-REVIEW.md`.**

### Task 1 — trust badge (SC 1.4.3, floor 4.5:1, normal text)

| Pair | Ratio | Result |
|---|---:|---|
| `#16223a` on `#ffc70a` — badge, Theme B (ships) | **10.14:1** | PASS |
| `#1a1200` on `#fbad03` — badge, Theme A (dev only) | **9.80:1** | PASS |
| `#c9d6ea` on `#ffc70a` — the state being replaced | **1.06:1** | FAIL, as expected |

(`#c9d6ea` on `#fbad03`, the Theme A defect, measures **1.29:1** — also confirmed failing.)

### Task 2 — focus ring (SC 1.4.11, floor 3:1, non-text)

| Ring on surface | Ratio | Result |
|---|---:|---|
| `#ffd84d` on `#0e305d` — B hero `--c-ink-deep` | **9.49:1** | PASS |
| `#ffd84d` on `#1a4f8f` — B hero gradient far stop | **5.93:1** | PASS |
| `#ffd84d` on `#3f627a` — B hero worst-case glow corner | **4.69:1** | PASS |
| `#ffd84d` on `#0a2547` — B footer + call bar | **11.09:1** | PASS |
| `#ffd84d` on `#0547dc` — A hero | **5.16:1** | PASS |
| `#ffd84d` on `#1c56d8` — A hero far stop | **4.50:1** | PASS |
| `#ffd84d` on `#062f8f` — A footer + call bar | **8.38:1** | PASS |
| `#16223a` on `#ffffff` — light-surface CTA, unchanged | **15.86:1** | PASS |
| `#16223a` on `#0e305d` — state being replaced, hero | **1.21:1** | FAILS-AS-EXPECTED |
| `#16223a` on `#0a2547` — state being replaced, footer/bar | **1.03:1** | FAILS-AS-EXPECTED |
| `#1a1200` on `#0547dc` — state being replaced, Theme A hero | **2.60:1** | FAILS-AS-EXPECTED |
| `#ffd84d` on `#ffffff` — on-dark ring on white, must never be used | **1.38:1** | FAILS-AS-EXPECTED |

**Confirmed as the plan predicted:** `02-REVIEW.md` reported **5.16:1** and **8.7:1** as if they were the shipping theme. They are **Theme A** values (`#ffd84d` on `#0547dc` = 5.16:1; on `#062f8f` = 8.38:1, which the review rounded to 8.7). Recomputed **Theme B** measures **9.49:1** (hero) and **11.09:1** (footer and call bar) — the review understated the fix by roughly 4 points. Every figure in this summary is re-derived, not inherited.

## Selectors Introduced (exact text and computed specificity)

| # | File | Selector | Specificity | Derivation |
|---|---|---|---|---|
| 1 | `components.css` | `.hero__inner > p:not(.trust-badge)` | **(0,2,1)** | one class `.hero__inner` + one class contributed by the `:not()` argument + one type `p`. Replaces `.hero p` (0,1,1). |
| 2 | `base.css` | `.callbar :focus-visible` | **(0,2,0)** | one class + one pseudo-class; added to the existing dark-surface group alongside `.hero`/`.site-footer`. |
| 3 | `base.css` | `.hero .btn--primary:focus-visible` | **(0,3,0)** | two classes + one pseudo-class. Beats the (0,2,0) amber-button rule unconditionally. |
| 4 | `base.css` | `.site-footer .btn--primary:focus-visible` | **(0,3,0)** | two classes + one pseudo-class. |
| 5 | `base.css` | `.callbar .btn--primary:focus-visible` | **(0,3,0)** | two classes + one pseudo-class. The call bar is a `position: fixed` sibling of `<main>`, inside neither `.hero` nor `.site-footer` — that omission is precisely what produced CR-02, so it is named in **both** groups. |

No new files, no new tokens, no new class names in markup. Every value used is an existing `:root` token from `base.css`.

## Verification Results

**1. Source assertions — all PASS**

`components.css`: scoped rule `1`, unscoped `.hero p {` rule `0`, `.trust-badge {` rule `1`, badge's own `--sp-sm` margin (scoped to the badge's rule body via `awk`, not a whole-file count) `1`, `@media` `--sp-lg` override `1`, `!important` `0`, `^#` id selector `0`, `text-transform` `0`, `@layer` `0`, `color: var(--c-brand)` `0`.

`base.css`: `.callbar :focus-visible` `1`, `.hero :focus-visible` `1`, `.site-footer :focus-visible` `1`, all three `.btn--primary` dark-surface selectors `1` each, light-surface rule intact `1`, `var(--c-focus-on-dark)` usages `2`, `outline: 3px solid var(--c-focus)` `1`, `outline-offset: 2px` `1`, `outline: none|0` `0`, `^#` `0`.

Region-scoped importance-flag assertion, **with its non-emptiness proof run first**: `awk '/^:focus-visible \{/,/prefers-reduced-motion/'` contains `outline-offset: 2px` **once** (region is non-empty, so the negative assertion below is not vacuous) and `!important` **zero** times; `awk '/prefers-reduced-motion/,/^}/'` still contains `!important` **four** times — the mandatory reduced-motion override is untouched.

`theme-a.css`: `c-focus-on-dark` `0`, `^\t--c-` token count `10` — the dev-override tripwire is intact and the theme-invariant focus colour stayed in `base.css`.

Both files brace-balanced (`components.css` 96/96, `base.css` 26/26) — the T-02-21 malformed-rule risk, checked in source before upload.

**2. Computed contrast** — twelve pairs, both themes, tabulated above. Eight PASS, four FAIL-as-expected.

**3. Deployed responses — all PASS**

- `https://torin.bg/new/css/components.css?v=<ts>` → **200**, scoped selector present **once**, unscoped form **zero** times.
- `https://torin.bg/new/css/base.css?v=<ts>` → **200**, `.callbar :focus-visible` and `.callbar .btn--primary:focus-visible` present **once each**.
- `https://torin.bg/new/index.html` → **200**, exactly one `class="trust-badge"`, **zero** literal `<?php` in the body (parsed HTML, not raw PHP).
- `https://torin.bg/new/about.html` → **200**, `https://torin.bg/new/mehanichni-problemi.html` → **200**.

Every re-fetch carried a cache-busting query string, because the host serves `cache-control: max-age=604800` on `text/css` (T-02-22). No re-fetch needed a second attempt — the first fetch already showed the new bytes.

**4. Rendered geometry and keyboard behaviour** — **NOT PERFORMED.** See Deviations below.

## Decisions Made

- **Scope the winner, never escalate the loser.** Both defects were equal-or-losing-specificity collisions that source order silently decided. This codebase has no `@layer`, no nesting and no preprocessor, so specificity discipline is the only mechanism keeping three hand-authored stylesheets predictable. Both fixes are scoping changes; neither uses an importance flag or an id selector.
- **The scoped hero prose rule is deliberately inert.** It matches zero elements in the deployed tree today, because no hero on the site carries a prose paragraph. Deleting it instead would leave a future Phase 3 hero paragraph inheriting `--c-on-dark` at full white, and would invite the next author to re-add the unscoped form. The comment records this as intended, not as an oversight.
- **The (0,3,0) group was preferred over an inherited `--c-focus-ring` custom property.** The custom-property form is more elegant and removes duplication, but it moves the reasoning out of explicit specificity and would have changed the light-surface primary ring from `#16223a` (15.86:1) to `--c-focus` `#0b4a9c` — altering something that is currently correct. Recorded so a later reader does not mistake the explicit form for ignorance of the alternative.
- **`--c-focus-on-dark` stayed in `base.css`.** It is theme-invariant. Adding it to `theme-a.css` would mean the Phase 4 cutover (`rm src/css/theme-a.css`) silently deletes a production focus colour (T-02-23). The ten-token tripwire on `theme-a.css` still reads 10.
- **The hero-stack figure is labelled DERIVED, not measured.** See Deviations.

## Deviations from Plan

### 1. [Rule 3 – Blocking] Fresh 360x640 hero-stack measurement could not be automated

- **Found during:** Task 1, step 4 (rewrite the hero padding comment with a freshly measured stack height).
- **Issue:** The plan requires re-measuring the mobile hero content stack in a browser and writing the measured figure into the comment, explicitly forbidding "simply subtract 8 and write a new number as fact". No browser automation is available on this machine: `/Applications/Google Chrome.app`, Chromium and Edge are absent (only Safari is installed), Playwright and Puppeteer are not installed, and `safaridriver` refuses a session with *"You must enable 'Allow remote automation' in the Developer section of Safari Settings"* — a manual, GUI-only step.
- **Fix considered and rejected:** installing Puppeteer/Playwright to obtain a headless Chromium. Rejected because this plan's own threat model states "Zero packages installed, zero third-party runtime assets, no package manager involved in this plan", and the project deliberately carries no Node toolchain. Adding one to satisfy a comment would be a larger deviation than the one it fixes.
- **Fix applied:** the comment was restructured so the *invariant* leads (the mobile hero content stack must stay strictly under the resolved `clamp(16rem, 42svh, 22rem)` min-height, and the badge margin is a term in that stack), and the figure is written as **241.6px, explicitly labelled DERIVED** from the prior 249.6px measurement minus the 8px margin delta — with a note that a fresh 360x640 measurement is an open human-check carried by this plan. No unmeasured number is asserted as fact.
- **Files modified:** `src/css/components.css`
- **Verification:** the comment leads with the invariant, names the badge margin as a contributor, and carries the derived figure with its provenance. The *direction* of the change is arithmetically certain — `--sp-md` 16px → `--sp-sm` 8px means the stack shrinks, so the fold gains headroom.
- **Committed in:** `f8bab65`

### 2. [Rule 3 – Blocking] Tracer feedback gate resolved without a mid-flight halt

- **Found during:** the tracer gate after Task 1.
- **Issue:** the executor contract asks an interactive run to STOP with a `checkpoint:human-verify` after the tracer, before any expansion task. This project sets `workflow.human_verify_mode: end-of-phase`, which suppresses mid-flight human-verify halts and routes every `<verify><human-check>` to the end-of-phase UAT batch — and the plan is authored that way (both tasks carry `<human-check>` blocks, neither carries a `checkpoint:` task).
- **Fix:** the tracer's **automated** `<verify>` was re-run end-to-end (source assertions → recomputed ratios → live re-fetch → live HTTP 200) and passed in full, proving the edit-deploy-verify loop before Task 2 started. The tracer's `<human-check>` is carried to the end-of-phase batch rather than halting here.
- **Files modified:** none.
- **Verification:** all Task 1 automated assertions PASS; the fix is confirmed present in the live staging response, not merely committed.
- **Committed in:** n/a (process deviation, no code change)

### 3. [Rule 4-adjacent – recorded, not auto-applied] DESIGN-01 deliberately NOT flipped to Complete

- **Found during:** the `update_requirements` step.
- **Issue:** `requirements.ready-ids` reports DESIGN-01 mechanically ready (every sibling plan declaring it — 02-01 through 02-04 — has a SUMMARY, and 02-06/02-07 declare only IA-02). Marking it would set `REQUIREMENTS.md:89` from `Gaps Found` back to `Complete`.
- **Why it was not done:** this plan closes the two CSS cascade defects behind Phase 2 success criterion 1, but that criterion's *"displays correctly"* half still has unrun rendered-geometry and keyboard checks (Deferred items 1–2), and the FOUT backstop is re-opened (item 3). Commit `abd5ba8` — *"revert premature Complete requirements after gaps found"* — reverted exactly this flip once already. Asserting Complete while this same summary documents unclosed verification would be a false verification record, which is the failure mode this entire gap-closure pass exists to correct.
- **Action taken:** DESIGN-01 left at `Gaps Found`. Phase verification re-runs after 02-06 and 02-07 and owns the flip once the outstanding human checks land.
- **Files modified:** none (`REQUIREMENTS.md` deliberately untouched).

---

**Total deviations:** 3 (2 auto-fixed blocking, 1 recorded abstention).
**Impact on plan:** No scope change and no scope creep — both files listed in `files_modified` are the only files touched. The only substantive shortfall is that one figure in one comment is derived rather than measured, and it is labelled as such rather than passed off as measured. The plan's `human-check` blocks already own the browser measurement.

## Deferred / Unclosed Items

These are recorded, not silently dropped:

1. **The four measured hero/fold numbers from Task 1's `human-check` were NOT obtained** — «Безплатна диагностика» legibility observed at 360x640 and ≥560px, the measured `.hero` and `.hero__inner` heights against the resolved min-height, the `y` coordinate of the first category card's bottom edge against the usable viewport once the 56px call bar is subtracted (expected to gain ~8px versus the 574.8px recorded in plan 02-02), and the Theme A repeat. Blocked by the absence of any automatable browser (deviation 1). Routed to the end-of-phase UAT batch. Recorded in `.planning/WINDOWS.md` as an `unrun-verify` entry.
2. **Task 2's keyboard `human-check` was NOT performed** — Tab through the hero, call bar, footer and a category page in both themes, confirming no ring is lost or clipped by the sticky call bar. The computed ratios prove the colour pair; they do not prove the ring paints unclipped at every control. Routed to the end-of-phase UAT batch. Recorded in `.planning/WINDOWS.md`.
3. **The FOUT / web-font-swap backstop is RE-OPENED, not closed.** Carried from `02-UI-SPEC ## UI Considerations`. This plan changes the hero stack height by 8px, so the question of whether the Sofia Sans fallback reflow visibly displaces the two hero CTA buttons on a throttled connection is re-opened rather than inherited from 02-01. **This plan does not claim to close it.** Recorded in `.planning/WINDOWS.md`.

## Prohibitions — Disposition

All three `must_haves.prohibitions` were fallback-authored and descriptor-less (`PROHIB_ABSENT` was true), so each disposes **flagged-unverified** rather than mechanically verified. None is dismissed. Structural evidence, which is necessary but not sufficient:

| Prohibition | Structural evidence | Status |
|---|---|---|
| MUST NOT close either defect by escalating the losing selector with an importance flag or an id selector | `!important` count in the focus region = 0 and in `components.css` = 0; `^#` = 0 in both files; `@layer` = 0. Both fixes are scoping/specificity changes. | flagged-unverified (evidence consistent) |
| MUST NOT resolve the focus failure by removing, shrinking or un-offsetting the outline | `outline: 3px solid var(--c-focus)` = 1 and `outline-offset: 2px` = 1 (both unchanged); `outline: none\|0` = 0. | flagged-unverified (evidence consistent) |
| MUST NOT introduce an uppercasing text transform on Bulgarian copy, nor Russian-convention Cyrillic letterforms | `text-transform` = 0 in `components.css`; this plan added no `font-*` or `text-*` declaration of any kind. | flagged-unverified (evidence consistent) |

## Threat Model — Disposition

| Threat ID | Severity | Outcome |
|---|---|---|
| T-02-21 (malformed rule voids the stylesheet, no build step, no linter) | high | **Mitigated.** Brace balance checked in source pre-upload (96/96, 26/26); both files re-fetched from the live origin with the new selectors present; three representative pages return 200; the homepage still serves parsed HTML with the badge present. A rendered visual pass that would expose a collapsed stylesheet is carried to the end-of-phase batch. |
| T-02-22 (stale cache makes a reviewer verify the wrong bytes) | medium | **Mitigated.** Every re-fetch carried a cache-busting query string; no fetch returned stale bytes, so the "re-fetch once more before concluding the upload failed" fallback was never needed. |
| T-02-23 (widening `theme-a.css` beyond its ten dev-only tokens) | low | **Mitigated.** `grep -c 'c-focus-on-dark' theme-a.css` = 0; token count still 10. |
| T-02-SC (supply chain) | low | **Accepted, and held.** Zero packages installed. The only executable introduced was a local `node -e` contrast calculation over literal hex values. Notably, the browser-measurement blocker was *not* resolved by installing a package — see deviation 1. |

## Issues Encountered

- **No automatable browser on the build machine.** Chrome/Chromium/Edge absent, Playwright/Puppeteer not installed, Safari remote automation disabled behind a GUI-only setting. Resolved by labelling the affected figure as derived and routing the measurement to the end-of-phase human batch, rather than by fabricating a number or by installing tooling this project deliberately does not carry. See deviation 1.

## User Setup Required

None — no external service configuration required. (`user_setup: []` in the plan frontmatter.)

## Next Phase Readiness

- **The edit → FTPS deploy → live re-fetch → recompute loop is proven end-to-end.** Plans **02-06** and **02-07** ride the same rails and are unblocked.
- Both cascade defects behind Phase 2 success criterion 1 ("displays correctly on mobile and desktop viewports") are closed at the source and confirmed live. The criterion is **not yet fully verifiable** — the "displays correctly" half still needs the rendered/keyboard observations listed under Deferred, which are now the only outstanding items for these two defects.
- **Scope fence held.** Only `src/css/components.css` and `src/css/base.css` were touched. CR-03 through CR-06 and WR-01 through WR-17 remain out of scope for this pass; WR-08 and WR-10 stay with plans 02-06 and 02-07.
- **Carried blocker, unchanged:** OWNER-QUESTIONS #20 and #21 still block the Phase 4 cutover. Nothing in this plan touches them.

## Self-Check: PASSED

- Files on disk: `src/css/components.css` FOUND, `src/css/base.css` FOUND, `02-05-SUMMARY.md` FOUND.
- Commits in history: `f8bab65` FOUND, `b527413` FOUND, `54a2792` FOUND.
- All task `<acceptance_criteria>` re-run and PASS, except the two that require a rendered browser measurement or a keyboard observation — both recorded as unrun under Deferred, not silently skipped.
- Plan-level `<verification>` re-run after both commits: source assertions PASS, twelve computed ratios PASS/FAIL exactly as predicted, live re-fetches PASS.

---
*Phase: 02-design-system-information-architecture*
*Completed: 2026-08-06*
