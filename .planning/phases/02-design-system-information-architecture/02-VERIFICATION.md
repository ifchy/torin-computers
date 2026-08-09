---
phase: 02-design-system-information-architecture
verified: 2026-08-09T14:58:25Z
status: passed
score: 4/4 success criteria verified
behavior_unverified: 0
overrides_applied: 0
provenance:
  - date: 2026-08-09
    change: |
      `02-RENDERED-VERIFICATION.md` was renamed to `02-RENDERED-CHECKS.md`, and
      the ten citations of it across this phase's PLAN/SUMMARY/UAT files were
      updated to match. Nothing about the measurements or the verdict changed.

      The rename is a tooling fix, not cosmetic: `gsd-tools query
      verification.status` globs `*-VERIFICATION.md`, alphabetically matched the
      RENDERED file first, found no `status:` field in it, and reported the whole
      phase as `missing` — "the verify step never completed" — while this report
      sat beside it reading `passed`. That false negative blocked
      `phase.complete` outright and would have cost a needless ~180k-token
      verifier re-run to "reproduce" a report already on disk.

      Recorded here because the failure is invisible from either file alone: each
      one is individually correct, and only their coexistence breaks the query.
uat_outcome:
  completed: 2026-08-09
  session: 02-UAT.md (round 2)
  note: |
    All four human_verification items below were answered; status moved
    human_needed -> passed. UAT also surfaced one new defect (G-02-5, the Viber
    button), which is NOT counted against this phase and the reasoning is
    recorded rather than assumed:

      - It fails none of the four success criteria, which are about the design
        system, the six-category IA, flat navigation and lang="bg".
      - The project had already scoped it to a later phase before it was found:
        OWNER-QUESTIONS #21 has read "Blocks: Phase 4 (CONTACT-02) cannot be
        verified without it" since it was filed during plan 02-03. Deferring it
        to Phase 4 is the pre-existing scope, not a convenient reclassification
        made to let this phase pass.
      - It has a recorded owner decision, a named owner action, and a tracked
        cutover gate — not an open unknown.

    Had it been reclassified only to clear the phase, this note would be the
    place that argument had to survive; it is written so a later reader can
    check it rather than trust it.
  human_items_resolved:
    - item: "Bulgarian letterforms"
      result: pass
      how: |
        Converted from judgment to measurement during testing. Same string, same
        font and size, only lang="bg" vs lang="ru" changed: advance widths
        IDENTICAL (1288.75px — locl substitutes glyph shapes, not widths) while
        the rendered PIXELS DIFFER. Visibly т->m, и->u, п->n, л->rounded ʌ,
        д->g-like, г->r. Had the two been byte-identical, no Bulgarian
        substitution would be happening — the distinction the eye cannot make
        without a reference.
    - item: "Desktop reload after the cache fix (closes UAT test 1)"
      result: pass
      how: |
        Owner confirmed on the same machine that reported it, with a NORMAL
        reload. This is what the headless reproduction could not establish: a
        headless browser always starts cold, so it cannot prove the invalidation
        mechanism works for a real visitor holding a warm cache.
    - item: "WR-11 call bar on an iPhone with a home indicator"
      result: pass
      how: |
        Both buttons fully tappable on a real device. The code fact stands —
        .callbar is position:fixed; bottom:0 with no safe-area allowance and
        `grep -rn 'env(' src/css/` returns zero hits — but the predicted
        practical consequence does not reproduce. Retained as a known risk, not
        a defect; worth re-checking if the bar's height or padding changes.
    - item: "problem-stari.html disposition"
      result: decided — RETIRE with a 301
      how: |
        Owner decision. Phase 4 must add
        `Redirect 301 /problem-stari.html /zalivane-technosti.html`.
        Target chosen on content, not convenience: despite its battery-themed
        title the page is about the motherboard's power section (Charger and
        StandBy processors, ACPI, fan and keyboard control), and kat-4 already
        carries the symptom "не зарежда". Topical proximity is load-bearing — a
        301 to an unrelated page can be treated as a soft 404 and not honoured.
        mod_rewrite confirmed active in src/.htaccess.
  new_gap_found:
    id: G-02-5
    severity: blocker at cutover, not for this phase
    summary: "The «Пишете във Viber» button is a dead link on all 16 pages — no shop number has a Viber account"
    status: deferred to Phase 4 with a recorded owner decision
    decision: |
      2026-08-09: the button stays on 088 945 8404 and the OWNER will provision a
      Viber account on that number. The code is correct and complete; what is
      missing is the account on the far end, which is not an engineering task.
      The number must NOT be changed while chasing this report.
    tracked_as: .planning/todos/pending/verify-viber-button-before-launch.md (resolves_phase 4)
    cutover_gate: "Before launch, press the button on a real handset with Viber installed and confirm it opens a conversation. If it still errors, it must not ship in this form."
    scheme_hypothesis: ELIMINATED — a control number known to have Viber opened a conversation through the identical viber://chat?number= href. Do not "fix" the scheme.
    still_open_separately: "What happens for a visitor with no Viber installed at all — a dead end today, unaffected by provisioning an account. OWNER-QUESTIONS #21, second half."
    why_no_check_caught_it: |
      Every automated check in this phase verified that the href was PRESENT,
      well-formed and single-sourced — which it is. Whether the number behind it
      has a Viber account is not a property of the markup and is unreachable
      from the origin; it needs a real handset with Viber installed. This is a
      genuine limit of the probe harness, not an oversight in it.
    escalated_to: "OWNER-QUESTIONS #21, reframed from 'which number' to a D-16 design decision"
re_verification:
  previous_status: gaps_found
  previous_score: 2/4
  previous_verified: 2026-08-06
  gaps_closed:
    - "CR-01 trust-badge contrast (`.hero p` beating `.trust-badge`) — re-measured live at 10.14:1"
    - "CR-02 dark-surface focus ring — re-measured live, 28 production controls, 0 missing ring, worst 5.93:1"
    - "No-script navigation reachability — re-measured with scripting disabled: 5 top-level items and all 6 category links visible at 360 and 1440"
    - "covid.html zero inbound links — now linked from the footer legal line on all 16 deployed pages"
    - "WR-10 homepage phone hardcoding — exactly one CTA tel: value (+35929549710) across all 16 pages"
    - "G-02-1 stale-CSS cache — every stylesheet and script URL now version-stamped; committed bare-URL gate re-run by the verifier, exit 0"
    - "G-02-4 font-swap CTA displacement — re-measured 0px (was 27.1px), with the gate independently proven un-blinded"
    - "Backstop truth 'FOUT does not displace the hero CTAs' — no longer behaviour-unverified; measured"
  gaps_remaining: []
  regressions: []
deferred:
  - truth: "problem-stari.html is reachable from somewhere on the site (zero inbound links across all 16 deployed pages)"
    addressed_in: "Phase 3"
    evidence: "Phase 3 goal: 'All sixteen pages are rebuilt with correct, unique SEO metadata and surface the shop's genuine trust signals'. problem-stari.html is one of the sixteen and its survival is an undecided content question. Recorded as a Phase 3 content decision in 02-04-SUMMARY.md:308, 02-06-PLAN.md:530, 02-06-SUMMARY.md:503, 02-07-PLAN.md:217 and 02-07-SUMMARY.md:169 — disclosed five times, never as an oversight."
  - truth: "The six category icons are recognisable as the services they represent without reading the label (UAT G-02-1b)"
    addressed_in: "Phase 3"
    evidence: "Deferred by explicit owner decision 2026-08-09 ('нека да ги оставим за фаза 3'). Tracked as .planning/todos/pending/redraw-category-icons.md. Verified untouched: src/includes/icons.php last modified by commit 194bae1 (plan 02-01); neither 02-08 nor 02-09 touched it, as their prohibitions required."
human_verification:
  - test: "Load https://torin.bg/new/index.html and a category page, and read the Bulgarian headings and body copy at desktop and mobile width."
    expected: "Cyrillic renders in Bulgarian-convention letterforms (localized д, л, п, ц, ш), not Russian-convention defaults."
    why_human: "Judgment-tier prohibition from plan 02-01, and the entire reason Sofia Sans was chosen (D-06a). Substantially de-risked: CSS.getPlatformFontsForNode confirms the live h1 is painted by the self-hosted Sofia Sans (postScriptName SofiaSans-Regular_Bold, isCustomFont true), and D-06a's premise is that Bulgarian forms are that family's DEFAULT outlines. What remains is eyes on the glyph shapes, which no probe can judge."
  - test: "On the desktop machine that reported UAT test 1, load https://torin.bg/new/index.html with a NORMAL reload (not Cmd+Shift+R), then edit nothing and reload again a few minutes later."
    expected: "Navigation on one row, six category cards with card surfaces, icons at normal size — and it stays correct without a hard reload."
    why_human: "UAT test 1 is still recorded as `issue` / severity blocker in 02-UAT.md and has not been re-confirmed by the owner since 02-08 shipped. The verifier reproduced the correct rendering headlessly at 1440x900 (nav on one row, sub-menu display:none, 3-column card grid, 143px icons) and confirmed the invalidation mechanism works, but the owner's acceptance is what closes the UAT entry."
  - test: "Open https://torin.bg/new/index.html on an iPhone X or newer (any model with a home indicator) and try to tap the lower half of both sticky call-bar buttons."
    expected: "Both buttons are fully tappable; no part of either is intercepted by the system gesture region, and the footer is not occluded."
    why_human: "Review finding WR-11 is still UNMITIGATED and untriaged: `.callbar` is `position: fixed; bottom: 0; height: 56px` with no safe-area allowance, and `grep -rn 'env('` over src/css/ returns zero hits. The code fact is established; the practical impact is device-specific and cannot be measured under viewport emulation. The fix is already written out in 02-REVIEW.md:479."
  - test: "Decide whether problem-stari.html («Чести проблеми») should be linked from the new site, folded into other content, or retired."
    expected: "A recorded owner decision. If retired, Phase 4 must serve a 301 rather than a bare 404 (D-36)."
    why_human: "A content/product decision, not a code fact. The code fact is established: zero inbound links across all sixteen deployed pages."
---

# Phase 2: Design System & Information Architecture — Verification Report

**Phase Goal:** Visitors see a modern, mobile-responsive site organized around the six owner-priority service categories, replacing the outdated jQuery/parallax "Liquid" theme and its undifferentiated scroll of icon boxes.
**Verified:** 2026-08-09
**Status:** human_needed
**Re-verification:** Yes — this file replaces the stale 2026-08-06 verification, which predates plans 02-05 through 02-09.

Every finding below was re-derived against the deployed site and the current source. Nothing was inherited from the previous VERIFICATION.md, from 02-REVIEW.md, or from any SUMMARY. The two `failed` entries still standing in `02-UAT.md` were assessed as open questions, not as facts, and both are answered with measurement below.

**Method.** Sixteen live pages fetched with bare URLs (no cache-buster, no cache-defeating header). Six committed probes re-run in a real headless Chromium via `scripts/render-check.sh`. One committed shell gate re-run end to end. Two throwaway probes written only where a committed probe could not answer the question — one to prove the G-02-4 gate is not blinded, one to measure layout geometry at three viewports. Deployed CSS and JS diffed byte-for-byte against `src/`.

## Goal Achievement

### Observable Truths (ROADMAP Success Criteria)

| # | Truth | Status | Evidence |
|---|---|---|---|
| 1 | Every page renders with the new responsive design system, no ScrollMagic/pagePiling/jQuery UI, and displays correctly on mobile and desktop viewports | ✓ VERIFIED | 16/16 pages HTTP 200, exactly two `<script>` elements each (`js/site.js` + JSON-LD), zero vendor-name hits, zero third-party origins. Zero horizontal overflow at 360/768/1440. Card grid 1/2/3 columns with no orphan row. Both cascade defects re-measured closed. Cache invalidation now works. Font swap displaces the CTAs by 0px. **One open warning: WR-11 (iOS safe area) is unmitigated — see below.** |
| 2 | Homepage presents six clearly distinct category sections instead of ~18 undifferentiated icon boxes | ✓ VERIFIED | Deployed homepage: exactly 6 `<article class="cat-card">` with ids `kat-1`..`kat-6`, 6 `<h3>` titles, each with a symptom line and a media slot. Catch-all is a native `<details>`, not a seventh peer card. |
| 3 | Visitor can reach any part of the site via a flat, shallow navigation around the six categories, no dense mega-menu | ✓ VERIFIED | Identical nav on 16/16 pages: 4 top-level links + one single-level Услуги disclosure holding all 6 categories (10 `nav__link`, 1 `nav__sub`, 1 `nav__toggle` per page). No mega-menu, no hover-open. With scripting disabled: 5 top-level items and all 6 category links visible and activatable at 360 and 1440. covid.html now linked from all 16 footers. |
| 4 | Every page declares `lang="bg"` and all Cyrillic text renders correctly in the new typography | ✓ VERIFIED | 16/16 deployed responses carry exactly one `lang="bg"`. 16 distinct Cyrillic titles, zero mojibake markers, zero BOMs in source. The live h1 is painted by the self-hosted Sofia Sans (`isCustomFont: true`). No `text-transform` anywhere. Glyph-shape judgment routed to human. |

**Score: 4/4 verified** (0 behaviour-unverified). Four items routed to human verification; two items deferred to Phase 3.

---

## The two UAT entries the briefing asked me to re-assess

`02-UAT.md` still records G-02-1 and G-02-4 as `status: failed`. **Both are genuinely closed.** I did not take either closure on trust; each was re-measured, and for G-02-4 I additionally falsified the specific way its gate can lie.

### G-02-1 — a deployed stylesheet change can now invalidate a cached copy: **CLOSED**

I ran the committed gate myself, from the repo root, against the live origin:

```
bash scripts/asset-version-check.sh   →  exit 0, "asset-version-check: PASS"
```

- **All 16 pages, bare-URL fetch:** 5 stamped asset URLs each, `0` unstamped, `0` `?v=0` sentinels, `0` PHP leakage, `1` site.js, `1` woff2 preload, `0` stamped woff2.
- **Token correctness:** each of the six assets' tokens equals that asset's own `Last-Modified` epoch at the origin — `base.css` 1786284536, `layout.css` 1785985631, `components.css` 1786040571, `no-js.css` 1786025202, `theme-a.css` 1785967706, `site.js` 1786283329.
- **Freezing is ruled out more strongly than the plan claimed.** 02-08 argued only a byte-identical redeploy can detect a frozen stamp. That is over-cautious: **five distinct token values, each independently equal to its own file's `Last-Modified`**, cannot be produced by a frozen constant. Combined with `torin_asset_url()` calling `filemtime()` at request time (read in full — no caching, no constant), the token is live.
- **Not cache defeat:** two fetches of `index.html` two seconds apart returned the identical token.
- **Lifetime bounded:** `cache-control: max-age=300` on `components.css` (was 604800). HTML stays `max-age=0`.
- **The pre-existing stale holders are also fixed** — this is the point that matters and is worth stating plainly. A visitor who cached `css/components.css` (bare URL) before 02-08 shipped now receives HTML that requests `css/components.css?v=1786040571`, a different cache key, so the stale copy is bypassed rather than waited out.
- **The verification blindness is closed durably:** the gate is a committed script that fetches bare URLs, so the class of defect that every earlier `?v=<timestamp>` probe run stepped over is now detectable on every future run.

### G-02-4 — the web-font swap no longer displaces the hero CTAs: **CLOSED, and the gate is not blinded**

`scripts/render-check.sh scripts/probes/font-swap.js https://torin.bg/new/index.html 360 640`:

```
maxAbsDeltaPx: 0        (was 27.1; threshold 8)
heroHeightDeltaPx: 0
fallback h1Height 73.6  ==  swapped h1Height 73.6
verdict: PASS
```

`maxAbsDeltaPx: 0` is exactly the reading both wave-8 plans warned would appear if the gate were blinded, so I refused to accept it on its face and falsified the blinding hypothesis directly. A throwaway probe re-ran both passes and asked Chromium which font actually painted the `h1`:

| Pass | `document.fonts` state | `CSS.getPlatformFontsForNode` on h1 | h1 height |
|---|---|---|---|
| woff2 blocked | `Sofia Sans/error`, `Sofia Sans/error`, `Sofia Sans Fallback/loaded` | **Arial** (`ArialMT`, `isCustomFont: false`) | 73.6px |
| woff2 allowed | `Sofia Sans/loaded` ×2 | **Sofia Sans** (`SofiaSans-Regular_Bold`, `isCustomFont: true`) | 73.6px |

The two passes render in genuinely different faces and still produce the same block height. The measurement is real. Supporting facts:

- The Sofia Sans preload is bare on all 16 pages (`WOFF2Q = 0`), so the probe's `*.woff2` block glob still matches — the coupling both plans documented is intact.
- The shipped `size-adjust: 97%` reproduces from the committed calibration probe: `scanRecommendedPct: 97`, two-line cliff at `106%`, `marginToCliffPct: 9`, `0` unresolved scan iterations across five installed candidates. Every number in the shipped `base.css` comment is re-derivable in one command, as the plan required.
- **D-30 non-regression re-measured, not assumed:** `hero-stack.js` at 360x640 reports content stack `233.4px` under resolved min-height `268.8px`, headroom `35.4px` — bit-identical to the recorded baseline.
- `font-display: swap` survives on both Sofia faces; `font-display: optional` appears nowhere except inside the comment explaining why it was rejected. No `min-height` on any `h1`. No `ascent-override`/`descent-override`/`line-gap-override`. The two Sofia `@font-face` blocks are unchanged by commit `ec1b864`.
- **Residual, stated rather than absorbed:** the calibration is against Arial. `Segoe UI` and `Roboto` are not installed on the measuring machine and were **not** measured. Windows is covered (Arial ships there and is first in the `local()` list); **Android is the unmeasured platform**. `base.css:93-101` records this honestly as a reasoned expectation. The mechanism can only no-op if `local()` resolves nothing, so it cannot regress.

### G-02-1b — icon abstractness: correctly deferred, correctly untouched

Deferred to Phase 3 by explicit owner decision on 2026-08-09 and tracked as `.planning/todos/pending/redraw-category-icons.md` (file present). Both wave-8 plans carried a prohibition against touching `src/includes/icons.php`; `git log` confirms its last modification is commit `194bae1` from plan 02-01. The prohibition held.

**`02-UAT.md` itself has not been updated** and still shows `passed: 20 / issues: 2`. That file is now stale in the same way the previous VERIFICATION.md was. Its two `failed` gap entries should be marked closed with a reference to this report.

---

## Criterion detail

### Criterion 1 — new design system everywhere, legacy stack gone, displays correctly

**Legacy stack: gone.** Not established by name-grepping (which the phase's own Pitfall 1 warns is worthless against a bundled vendor file), but by enumerating every `<script>` element in all sixteen deployed responses: exactly two per page, `js/site.js?v=1786283329` and one `application/ld+json` block. Zero third-party origins anywhere in the sixteen responses except one `google.com/maps` deep link in the footer. No font CDN — D-06a's self-hosting holds.

**Design system on every page:** 16/16 pages HTTP 200 as parsed HTML with zero PHP leakage, each emitting the same five stylesheets and the same nav markup from the shared head.

**Deployed == committed == verified.** `css/base.css`, `layout.css`, `components.css`, `no-js.css`, `theme-a.css` and `js/site.js` were fetched from the origin and diffed against `src/` — **all six byte-identical**. `git status` shows no uncommitted changes under `src/` or `scripts/`. There is no gap between what I measured, what is in the repo, and what a visitor gets.

**Displays correctly — measured, at three viewports, with the layout viewport properly constrained** (`Emulation.setDeviceMetricsOverride`, not `--window-size`; the documented false-overflow trap was avoided):

| Viewport | Overflow | Overflowing elements | Card columns | Nav | Hero | Icon |
|---|---|---|---|---|---|---|
| 360x640 | 0px | none | 1 (6 rows) | collapsed, `aria-expanded=false`, no flash | 269px (42.0% of 640) | 34x34 |
| 768x1024 | 0px | none | 2 (3 rows) | collapsed | 352px | 142x142 |
| 1440x900 | 0px | none | 3 (2 rows) | **one row**, all 5 items at `top: 10`, height 44px, sub-menu `display: none` | 352px | 143x143 |

The 1440 row independently refutes every symptom the owner reported in UAT test 1 (column-stacked nav, permanently-expanded Услуги, no card treatment, full-viewport icons). The stale-cache diagnosis is confirmed by measurement, not inherited. The desktop icon size is by design, not a defect: `.cat-card__media` is an `aspect-ratio: 16/10` panel at full card width above the breakpoint and the SVG is 42% of it — the D-38 photo-swap slot.

**Both cascade defects re-measured closed:**

- **CR-01 trust badge:** live computed `color: rgb(22,34,58)` on own `background: rgb(255,199,10)` = **10.14:1**, `margin-block-start: 8px` correctly applied. The hero prose rule is now `.hero__inner > p:not(.trust-badge)` (0,2,1), which outranks `.trust-badge` (0,1,0) only for genuine prose.
- **CR-02 focus rings:** `focus-rings.js` at 1440x900, real Tab key events — 30 tabbed, **28 production controls, 0 missing a ring**, worst ratio **5.93:1** against `--c-ink-deep-2`, ring colour `rgb(255,216,77)` = `--c-focus-on-dark`. Zero SC 1.4.11 failures.

**Open warning — WR-11 (iOS safe area) is unmitigated and was never triaged.** `.callbar` is still `position: fixed; bottom: 0; height: 56px`, and `grep -rn 'env('` over `src/css/` returns **zero hits**. On any iPhone since the X the lower band of both sticky call-bar buttons falls in the system gesture region. This is the component whose entire justification is keeping the two conversion actions reachable on a small screen, and every other interactive element in this stylesheet carries an explicit 44px floor. The code fact is certain; the impact is device-specific and cannot be settled under viewport emulation, so it is routed to human verification rather than asserted either way. The two-line fix is already written out in `02-REVIEW.md:479`. It is not counted as a blocker because nothing measurable fails and the owner's own phone UAT passed — but it must not be carried into Phase 3 unnoticed.

### Criterion 2 — six distinct category sections

Exactly six `<article class="cat-card">` with ids `kat-1`..`kat-6`, six `<h3>` titles, each with a symptom line and a media slot. Four `<details>` elements form the catch-all, which is real HTML in the served response rather than JS-injected. Section headings on the homepage: «Какво ремонтираме», «Не откривате проблема си?», «Тествай сам своя лаптоп», «Свържете се с нас», «Работно време». No seventh peer item.

The D-23 publish gate works as designed: three published categories link to real pages (`mehanichni-problemi.html`, `optimizatsiq.html`, `zalivane-technosti.html`), three unpublished ones link to their own homepage anchors (`index.html#kat-2`, `#kat-5`, `#kat-6`) via `torin_category_href()`. No card is degraded or omitted for lacking a page. Phase 3 flips three booleans.

### Criterion 3 — flat, shallow, six-category navigation

**Shape:** identical on 16/16 pages — 4 top-level links plus one single-level Услуги disclosure containing all six categories (10 `nav__link`, 1 `nav__sub`, 1 `nav__toggle`). No mega-menu, no hover-open rule at any width.

**No-script reachability — the defect the previous verification called worse than recorded is closed.** `no-script-nav.js` with `Emulation.setScriptExecutionDisabled`:

| Viewport | no-js.css applied | Top-level items | Category links | Overflow | Verdict |
|---|---|---|---|---|---|
| 360x640 | true | 5 | all 6, named | 0px | PASS |
| 1440x900 | true | 5 | — | -15px | PASS |

**No flash of an open nav with scripting on:** `nav-enhanced.js` reports `navListDisplayAtLoad: "none"`, `aria-expanded: "false"`, `no-js.css` not applied.

**Reachability:** `covid.html` is now linked from the footer legal line on **all 16** pages (was 0). `msg.html` is correctly unlinked — it is the `mailer.php` POST target. `problem-stari.html` remains at zero inbound links; deferred to Phase 3 (see `deferred` above) and surfaced as an owner decision.

**Contact single-sourcing (WR-10):** exactly one CTA `tel:` value across the whole site — `tel:+35929549710`, 22 occurrences. The three footer display numbers (`029549710`, `0879128244`, `0889458404`) appear on 16 pages each, looped from `site-config.php`. Homepage and footer can no longer drift.

### Criterion 4 — `lang="bg"` and correct Cyrillic typography

16/16 deployed responses carry exactly one `lang="bg"`; `header.php` is the sole emitter. All 16 `<title>` values are distinct Bulgarian Cyrillic, zero mojibake markers (`Ð`, `Ñ`, `â€`), `<meta charset="utf-8">`, zero BOMs across every `.php`/`.html`/`.css`/`.js` file in `src/`.

Typography verified beyond the attribute: `CSS.getPlatformFontsForNode` reports the live `h1` painted by **Sofia Sans** (`SofiaSans-Regular_Bold`, `isCustomFont: true`, 35 glyphs) — the self-hosted subset, not a system substitute. Two subsets with a correct Cyrillic/Latin `unicode-range` split, `font-display: swap` on both, Cyrillic subset preloaded with `crossorigin`. In the font-blocked pass the Bulgarian string renders legibly in Arial, so first-paint readability — the stated reason `swap` was chosen — holds. **No `text-transform` anywhere in the stylesheets**, so the 02-01 prohibition against uppercasing Bulgarian copy holds structurally.

---

### Required Artifacts

| Artifact | Expected | Status | Details |
|---|---|---|---|
| `src/includes/asset-version.php` | `torin_asset_url()`, the single stamping point | ✓ VERIFIED | 4,035 B, PHP 5.2-safe (`dirname(dirname(__FILE__))`), `?v=0` sentinel on stat failure, emits nothing on include |
| `src/includes/header.php` | Five stamped asset URLs, bare font preload | ✓ VERIFIED | 5 `torin_asset_url()` calls; preload bare with both reasons recorded; cascade order base → layout → components, no-js override last |
| `src/includes/dev-switcher.php` | Stamped theme-a link, `file_exists` gate | ✓ VERIFIED | Stamped via the same helper. Known pre-existing defect (link emitted unconditionally) deliberately not fixed — see below |
| `src/.htaccess` | Bounded staging cache, AddHandler intact | ✓ VERIFIED | `text/css` and both JS media types at 5 minutes; `AddHandler application/x-httpd-php52` untouched at line 20; every directive inside an `<IfModule>` guard. Live: `max-age=300`, `content-encoding: gzip` on CSS, `x-robots-tag: noindex, nofollow` |
| `scripts/asset-version-check.sh` | Durable bare-URL gate | ✓ VERIFIED | Re-run by the verifier: exit 0, PASS across Checks A/B/C |
| `scripts/probes/font-fallback-metrics.js` | Source of every shipped font number | ✓ VERIFIED | 507 lines, re-run: 0 unresolved iterations, reproduces 97% / cliff 106% |
| `src/css/base.css` | Sofia faces + metric-adjusted fallback | ✓ VERIFIED | One `font-weight: 400` fallback face, `size-adjust: 97%`, family second in `--font-sans`; Sofia faces unchanged; byte-identical to the deployed copy |
| `src/css/components.css` | Hero, cards, nav, footer, callbar | ⚠️ WARNING | Cascade defect CR-01 closed and re-measured. `.callbar` has no safe-area allowance (WR-11, unmitigated) |
| `src/css/layout.css`, `no-js.css`, `theme-a.css` | Shells, no-script override, dev theme | ✓ VERIFIED | All serve 200 and are byte-identical to source |
| `src/js/site.js` | Entire JS surface | ✓ VERIFIED | Sole script site-wide, stamped, serves 200 |
| `src/includes/categories.php` | Six-category single source of truth | ✓ VERIFIED | 6 records; `torin_category_href()` consumed by both cards and nav |
| `src/includes/footer.php`, `jsonld.php`, `site-config.php`, `icons.php`, `category-page.php` | Shared chrome | ✓ VERIFIED | Wired by 16/16 pages; `icons.php` untouched since 02-01 as required |
| 16 deployed pages | Site-wide rollout | ✓ VERIFIED | 16/16 HTTP 200, distinct Cyrillic titles, identical nav and head |

### Key Link Verification

| From | To | Via | Status |
|---|---|---|---|
| `header.php` | `asset-version.php` | `require_once` + 5 `torin_asset_url()` calls | ✓ WIRED — 5 stamped hrefs on 16/16 deployed pages |
| `dev-switcher.php` | `asset-version.php` | `torin_asset_url('css/theme-a.css')` in scope from header | ✓ WIRED — stamped theme-a href on 16/16 |
| `asset-version.php` | deployed files on disk | `filemtime()` at request time | ✓ WIRED — 5 distinct tokens, each equal to its own asset's `Last-Modified` |
| `scripts/asset-version-check.sh` | `https://torin.bg/new/` | bare-URL fetches ×16 + per-asset `Last-Modified` compare | ✓ WIRED — re-run, exit 0 |
| `base.css size-adjust: 97%` | `font-fallback-metrics.js` | probe-reported value, invocation recorded in the comment | ✓ WIRED — probe re-run, reproduces 97% |
| `base.css --font-sans` | `'Sofia Sans Fallback'` face | family listed immediately after `'Sofia Sans'` | ✓ WIRED — `fallbackAvail: true`, Arial painted in the blocked pass |
| `font-swap.js` | `https://torin.bg/new/index.html` | existing G-02-4 gate at 360x640 | ✓ WIRED — and independently proven un-blinded |
| `header.php` preload | `base.css @font-face src` | byte-match, both unstamped | ✓ WIRED — `WOFF2Q = 0` on 16/16 |
| 16 pages | `header.php` / `footer.php` | `require_once` | ✓ WIRED — 16/16 |
| `index.html` + nav | `categories.php` | `torin_category_href()` | ✓ WIRED — cards and nav cannot disagree |
| all CTAs | `site-config.php` | one E.164 key | ✓ WIRED — exactly one CTA `tel:` value site-wide (previously NOT WIRED) |
| `components.css` | `header.php` | `[aria-expanded]` attribute selectors | ✓ WIRED |

### Data-Flow Trace (Level 4)

| Artifact | Data variable | Source | Produces real data | Status |
|---|---|---|---|---|
| `index.html` card grid | `$torin_categories` | `includes/categories.php` (6 literal records) | Yes — 6 rendered cards with names, symptom lines, hrefs | ✓ FLOWING |
| `header.php` nav sub-list | `$torin_categories` via `torin_category_href()` | same file | Yes — same 6 names in the deployed nav | ✓ FLOWING |
| `header.php` asset hrefs | `filemtime()` per asset | real files on the server | Yes — 5 distinct tokens matching origin `Last-Modified` | ✓ FLOWING |
| `footer.php` phone list | `$site['phones']` | `includes/site-config.php` | Yes — 3 numbers × 16 pages | ✓ FLOWING |
| `jsonld.php` | `$site[...]` | `includes/site-config.php` | Yes — one JSON-LD block per page | ✓ FLOWING |
| `.cat-card__media` | inline SVG | `includes/icons.php` | Yes — 6 icons, sized 34px mobile / 143px desktop | ✓ FLOWING |

### Behavioural Spot-Checks

| Behaviour | Command | Result | Status |
|---|---|---|---|
| 16 URLs live, parsed, one `lang="bg"` | bare-URL `curl` ×16 | 16× 200, 16× lang=1, 0 PHP leakage | ✓ PASS |
| Vendor stack absent | enumerate all `<script>` ×16 | 2 per page (site.js + JSON-LD), 0 vendor hits, 0 third-party origins | ✓ PASS |
| Six category cards | count `class="cat-card"` | 6, ids kat-1..kat-6 | ✓ PASS |
| Deployed == source | fetch + `diff` 6 assets | 6/6 byte-identical | ✓ PASS |
| Cache invalidation gate | `bash scripts/asset-version-check.sh` | exit 0, PASS (A/B/C) | ✓ PASS |
| Token is not request-varying | two fetches 2s apart | identical token | ✓ PASS |
| Font-swap CTA displacement | `font-swap.js` @360x640 | `maxAbsDeltaPx: 0` (threshold 8) | ✓ PASS |
| Font-swap gate not blinded | `CSS.getPlatformFontsForNode` both passes | Arial vs Sofia Sans — genuinely different faces | ✓ PASS |
| Font calibration reproducible | `font-fallback-metrics.js` | 97% recommended, cliff 106%, 0 unresolved | ✓ PASS |
| D-30 above-the-fold | `hero-stack.js` @360x640 | 233.4px under 268.8px, headroom 35.4px | ✓ PASS |
| Focus rings on all controls | `focus-rings.js` @1440x900 | 28 controls, 0 missing, worst 5.93:1 | ✓ PASS |
| Trust-badge contrast | computed style on live `.trust-badge` | 10.14:1 | ✓ PASS |
| No-script nav | `no-script-nav.js` @360 and @1440 | 5 items + 6 categories, 0 overflow | ✓ PASS |
| No flash of open nav | `nav-enhanced.js` @360 | collapsed at load | ✓ PASS |
| Layout at 3 viewports | throwaway layout probe | 0 overflow, 1/2/3 columns, desktop nav one row | ✓ PASS |
| Cyrillic integrity | 16 titles + BOM scan | 16 distinct, 0 mojibake, 0 BOM | ✓ PASS |
| iOS safe-area handling | `grep -rn 'env(' src/css/` | 0 hits — WR-11 unmitigated | ✗ FAIL (routed to human, device-specific) |

No test suite, no `package.json` and no build step exist in this project, so there is nothing to enumerate or run; verification is by live measurement, which is the correct substitute here.

### Probe Execution

| Probe | Command | Result | Status |
|---|---|---|---|
| `scripts/asset-version-check.sh` | `bash scripts/asset-version-check.sh` | exit 0 — "asset-version-check: PASS" | PASS |
| `scripts/probes/font-swap.js` | `render-check.sh … 360 640` | `maxAbsDeltaPx: 0` | PASS |
| `scripts/probes/font-fallback-metrics.js` | `render-check.sh … 360 640` | 97% inside measured 70–106% two-line range | PASS |
| `scripts/probes/hero-stack.js` | `render-check.sh … 360 640` | 233.4 < 268.8 | PASS |
| `scripts/probes/focus-rings.js` | `render-check.sh … 1440 900` | 0 missing, worst 5.93:1 | PASS |
| `scripts/probes/no-script-nav.js` | `render-check.sh … 360 640 --no-script`, `… 1440 900 --no-script` | 5 items + 6 categories, 0 overflow | PASS |
| `scripts/probes/nav-enhanced.js` | `render-check.sh … 360 640` | collapsed at load | PASS |
| `scripts/probes/contrast.js` | — | Not runnable: exports `{ HELPERS }`, no `run`. Known and logged in `.planning/WINDOWS.md`. Its two questions were answered by `focus-rings.js` and a direct computed-style measurement instead | MISSING_PROBE (worked around, not skipped) |

The `render-check.sh` viewport guard was exercised inadvertently and works: a mis-split argument was rejected with `ERROR: width must be a positive integer, got '360 640'` rather than silently falling through to 390. The trap that produced three identical "different-width" runs earlier in this phase is genuinely closed.

### Requirements Coverage

| Requirement | Source plans | Description | Status | Evidence |
|---|---|---|---|---|
| DESIGN-01 | 02-01…02-09 (8 plans) | Modern mobile-responsive layout replacing the jQuery/parallax theme | ✓ SATISFIED | Legacy stack absent from all 16 responses; design system on 16/16; 0 overflow at three viewports; both cascade defects closed; cache invalidation working; font swap 0px. Residual: WR-11 (human), icon redraw (deferred to Phase 3 by owner) |
| IA-01 | 02-02, 02-04 | Six categories as distinct sections, not ~18 icon boxes | ✓ SATISFIED | 6 cards, 6 titles, symptom lines, catch-all as `<details>`, no seventh peer |
| IA-02 | 02-03, 02-06, 02-07 | Flat shallow nav around the six categories, no mega-menu | ✓ SATISFIED | 5 top-level + one single-level disclosure on 16/16, works without JS, covid.html linked |
| SEO-02 | 02-01, 02-04 | Every page declares `lang="bg"` | ✓ SATISFIED | 16/16 deployed responses, one emitter |
| SEO-04 | 02-04 (prohibition) | All existing URLs preserved | ✓ SATISFIED | 16 filenames unchanged; nothing added, renamed or retired |

**Orphaned requirements: none.** REQUIREMENTS.md maps exactly four IDs to Phase 2 (DESIGN-01, IA-01, IA-02, SEO-02) and all four are claimed by plan frontmatter and verified above.

**⚠️ REQUIREMENTS.md is internally inconsistent and is wrong in both directions.** This is a bookkeeping defect, not a goal defect, but it should be corrected before the phase is closed:

- **DESIGN-01 was flipped to `Complete` by commit `55805da` (the 02-09 completion commit) with no rationale recorded anywhere in `02-09-SUMMARY.md`.** That is the third occurrence of this pattern: it was flipped prematurely once and reverted in `abd5ba8`, then 02-08 explicitly declined to flip it and wrote out three reasons for declining (Deviation 3 in `02-08-SUMMARY.md`), and then 02-09 flipped it silently anyway. My own conclusion is that DESIGN-01 **is** now substantively satisfied — but it was marked complete *before* any verification ran, on the strength of one gap closure, which is exactly the reasoning 02-08 rejected. The conclusion happens to be right; the process that produced it would have been equally confident had it been wrong.
- **IA-01, IA-02 and SEO-02 still read `Gaps Found` with unchecked boxes**, even though all three are fully satisfied by the measurements above. The three requirements that *are* verifiably done are marked undone, and the one that was still open at the time was marked done.

### Prohibitions

| # | Prohibition | Plan | Tier | Status |
|---|---|---|---|---|
| 1 | MUST NOT version the Sofia Sans preload href | 02-08 | test | ✓ HELD — `WOFF2Q = 0` on 16/16; the block glob still matches, proven by the un-blinding check |
| 2 | MUST NOT make the version token request-time-varying | 02-08 | test | ✓ HELD — identical token across two fetches; `filemtime()` in source |
| 3 | MUST NOT restate the retracted cutover reasoning | 02-08 | judgment | ✓ HELD — grep over `src/` and `scripts/` finds no restatement |
| 4 | MUST NOT touch the AddHandler line or add an unguarded directive | 02-08 | test | ✓ HELD — line 20 intact; every directive inside `<IfModule>`; 16/16 pages still 200 |
| 5 | MUST NOT touch the six category icons | 02-08, 02-09 | test | ✓ HELD — `icons.php` unchanged since commit `194bae1` |
| 6 | MUST NOT change `font-display: swap` / introduce `optional` | 02-09 | test | ✓ HELD — both faces `swap`; `optional` appears only inside an explanatory comment |
| 7 | MUST NOT reach for a `min-height` on the hero h1 | 02-09 | test | ✓ HELD — no `min-height`/`min-block-size` on any `h1` |
| 8 | MUST NOT ship an estimated font metric | 02-09 | test | ✓ HELD — 97% reproduced by re-running the committed calibration probe |
| 9 | MUST NOT add `ascent-override`/`descent-override`/`line-gap-override` | 02-09 | test | ✓ HELD — none present |
| 10 | MUST NOT alter the Sofia woff2 subsets, `src`, `unicode-range` or weight range | 02-09 | test | ✓ HELD — unchanged by `ec1b864` |
| 11 | No uppercase transform on Bulgarian copy; no Russian-convention letterforms | 02-01 | judgment | ⚠️ FLAGGED — `text-transform` absent everywhere (structurally satisfied); glyph shapes routed to human |
| 12 | No unverified business facts presented as unqualified fact | 02-01/02-03 | judgment | ⚠️ FLAGGED — `[ASSUMED]` markers present in source, but the opening-hours value still ships to 16 footers and into `openingHoursSpecification` |
| 13 | No seventh peer item; no category demoted into the catch-all | 02-02 | judgment | ✓ HELD — exactly six cards, all six also in the nav |
| 14 | Six categories must have a non-JS path | 02-03 | test | ✓ HELD — measured with scripting disabled at two viewports |
| 15 | No page filename renamed, added or retired | 02-04 | test | ✓ HELD — 16/16 unchanged |

Judgment-tier prohibitions carry a non-authoritative verdict per the fail-closed default; rows 11 and 12 are flagged for human review.

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|---|---|---|---|---|
| — | — | `TBD` / `FIXME` / `XXX` | — | **Zero across all of `src/` and `scripts/`.** No unreferenced debt markers; the debt-marker gate does not fire |
| — | — | `TODO` / `HACK` / `PLACEHOLDER` (code) | — | Zero |
| `src/includes/categories.php` | 30, 41, 50, 59, 74, 85 | `[ASSUMED] Placeholder` symptom lines | ℹ️ Info | Content placeholders pending OWNER-QUESTIONS #16, each with a visible provenance marker. **Owner explicitly accepted them as-is** in UAT test 2 («нека да останат така засега»). Not a stub — real Bulgarian copy renders on all six cards |
| `src/includes/dev-switcher.php` | 47 | `theme-a.css` link emitted unconditionally, outside the `if ($torin_theme === 'a')` branch | ⚠️ Warning | One wasted request per page for a stylesheet whose only rules sit inside `[data-theme="a"]` and are inert on the default Theme B. Observed and deliberately not fixed by 02-08; the whole file is deleted at the Phase 4 cutover. Disclosed in the plan's own must-haves rather than discovered here |
| `src/css/components.css` | 364-373 | `.callbar` fixed to `bottom: 0` with no `env(safe-area-inset-bottom)` | ⚠️ Warning | WR-11, unmitigated and untriaged. Routed to human verification |

### Known Open Items Carried Forward (recorded, not silent)

- **WR-11** — iOS safe area on the sticky call bar. Untriaged since 02-REVIEW. Fix already written.
- **Three `[ASSUMED]` facts for Phase 4** — opening hours (highest exposure: ships to 16 footers *and* into `openingHoursSpecification`), chat-capable number, notice-band text. All correctly marked in source.
- **DIFF-02 (battery regeneration placement)** — deliberate documented downgrade, carried to Phase 3.
- **Dev theme switcher and `phptest.html` live on staging** — intended pre-cutover; `phptest.html` discloses the PHP build (CR-03) and the theme cookie is domain-scoped (WR-17). Both must be resolved at the Phase 4 cutover.
- **CR-05** — following the `.htaccess` Phase-4 promotion instruction literally would deindex torin.bg. Not a Phase 2 defect, but a live landmine in a Phase 2 artifact.
- **Phase 4 obligations created by 02-08** — raise the `text/css`/JS lifetimes once the stamp is proven in production, and delete `dev-switcher.php`.
- **Android font-fallback coverage unmeasured** — `Roboto` did not resolve on the measuring machine. Recorded honestly in `base.css`; the mechanism can only no-op.

### Gaps Summary

**There are no gaps.** All four ROADMAP success criteria are verified against the deployed site by live measurement, and the two UAT entries the briefing flagged as possibly-stale are genuinely closed — G-02-1 by a committed gate I ran myself, and G-02-4 by a measurement whose specific failure mode (a blinded gate reporting a flawless zero) I falsified directly rather than assumed away.

What stops this phase from reading `passed` is four human-verification items, not defects:

1. **Bulgarian-convention letterforms** — a judgment-tier prohibition; substantially de-risked by confirming the live `h1` is painted by the self-hosted Sofia Sans, but glyph shapes need eyes.
2. **Owner re-confirmation of the desktop rendering** — UAT test 1 is still recorded as a blocker-severity `issue` and predates 02-08. I reproduced the correct rendering headlessly and confirmed the invalidation mechanism, but the owner's acceptance is what closes that entry.
3. **WR-11 on a real iPhone** — the only code-level finding still standing. Untriaged, two-line fix known, device-specific impact unmeasurable here.
4. **`problem-stari.html`'s disposition** — an owner content decision, deferred to Phase 3 with five independent in-repo disclosures.

Two documentation corrections should accompany phase closure: `02-UAT.md` still records G-02-1 and G-02-4 as `failed` and should be updated, and `REQUIREMENTS.md` is wrong in both directions — DESIGN-01 was flipped to `Complete` without rationale by the 02-09 completion commit (the third occurrence of a pattern this project already reverted once), while IA-01, IA-02 and SEO-02 still read `Gaps Found` despite being verifiably satisfied.

---

_Verified: 2026-08-09T14:58:25Z_
_Verifier: Claude (gsd-verifier)_
