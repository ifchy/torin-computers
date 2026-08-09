---
phase: 02-design-system-information-architecture
type: rendered-verification
created: 2026-08-06
method: headless Chromium (Brave 149) driven over CDP via scripts/render-check.sh
target: https://torin.bg/new/ (deployed staging subtree)
---

# Phase 2 — Rendered Verification

## Why this document exists

Plans 02-05, 02-06 and 02-07 each recorded that their rendered/visual/keyboard checks were
**unrunnable**, on the grounds that no automatable browser existed on the build machine. Each
executor searched for Chrome, Chromium, Edge, Playwright and Puppeteer by name, found none, and
correctly declined to record unrun checks as passing.

**That capability assessment was wrong.** `/Applications/Brave Browser.app` is installed. Brave is
Chromium (149), it accepts `--headless --remote-debugging-port`, and it speaks the DevTools Protocol
unchanged. Everything the phase deferred was measurable on this machine the whole time.

The harness is now `scripts/render-check.sh` + `scripts/lib/cdp-client.js` + `scripts/probes/`, with
no npm dependency, consistent with the project's no-build-step constraint.

## Two measurement traps, both hit and corrected

Recorded because either one silently produces a confident wrong answer:

1. **`--window-size` does not constrain the layout viewport in headless mode.** The page lays out
   wide and the screenshot is merely cropped. This initially looked exactly like catastrophic mobile
   horizontal overflow — clipped buttons, a missing hamburger. There was none. Viewport must be set
   via `Emulation.setDeviceMetricsOverride`.
2. **Resolving "the background behind X" by walking ancestors' `backgroundColor` skips gradients.**
   `.hero` is painted with `radial-gradient` + `linear-gradient`, so its computed `backgroundColor`
   is transparent and the walk falls through to `<body>` white. This reported a focus ring at
   1.38:1 that actually measures 9.49:1. Surfaces must be resolved from their design tokens.

A third trap, in the harness itself: `scripts/render-check.sh` originally accepted a non-numeric
width and fell through to its 390 default (`Number("360 640")` is `NaN`; `NaN || 390` is `390`), so
three runs at supposedly different viewports all silently measured the same one. The script now
rejects a non-integer viewport rather than defaulting.

## Results

All measured against the deployed staging origin with cache-busting query strings.

### Phase success criteria

| # | Criterion | Evidence | Verdict |
|---|---|---|---|
| 1 | New design system, no ScrollMagic/pagePiling/jQuery UI, displays correctly on mobile + desktop | `window.jQuery`, `window.ScrollMagic`, `window.pagepiling` all `undefined`; sole script is `js/site.js`; `scrollWidth - innerWidth = 0` at 390 and at 1440 | **PASS** |
| 2 | Homepage shows six distinct category sections | 6 cards with six distinct Bulgarian titles (Счупвания и механични повреди, Екран/клавиатура и портове, Оптимизация, Заливане и дънни платки, Прегряване и охлаждане, Нестандартна техника) | **PASS** |
| 3 | Flat shallow nav around six categories, no dense mega-menu | 5 top-level items, 10 nav links; desktop nav occupies exactly **1 row**, toggle `display: none`; mobile toggle `display: flex` at `right: 374px` within a 390px viewport; no `[role="menu"]` | **PASS** |
| 4 | `lang="bg"`, Cyrillic renders in the new typography | `document.documentElement.lang === "bg"`; `h1` resolves to `"Sofia Sans", …`; Cyrillic `h1` lays out non-zero | **PASS** |

### Gap-closure plans

| Ref | Check | Measured | Verdict |
|---|---|---|---|
| CR-01 (02-05) | Trust badge «Безплатна диагностика» contrast | **10.14:1** (`rgb(22,34,58)` on `rgb(255,199,10)`), was 1.06:1 | **PASS** |
| CR-01 | Badge top margin after the specificity fix | 8px mobile / 24px desktop | **PASS** |
| CR-02 (02-05) | Focus ring on every production control, Theme B | 28 controls, **0** without a ring, worst **5.93:1** | **PASS** |
| CR-02 | Same, Theme A (`?theme=a`) | 28 controls, **0** without a ring, worst **4.50:1** | **PASS** |
| CR-02 | Light-surface controls keep the navy ring | `rgb(11,74,156)` Theme B / `rgb(4,64,194)` Theme A, at 7.85:1 / 7.71:1 | **PASS** |
| D-30 (02-05) | Hero content stack stays under the clamp min-height @360x640 | stack **233.4px** vs min-height **268.8px**, 35.4px headroom — hero sized by 42svh, not by content | **PASS** |
| WR-08 (02-06) | No-script nav: 5 top-level items + 6 category links visible | Confirmed at 360x640, 900x900, 1440x900; `no-js.css` applied | **PASS** |
| WR-08 | Disclosure controls not visible and not Tab-reachable without script | `toggleVisible: false`, `disclosureVisible: false`, neither Tab-reachable, all three viewports | **PASS** |
| WR-08 | No-script layout introduces no horizontal overflow | 360: 360/360 · 900: 885/900 · 1440: 1425/1440 | **PASS** |
| IA-02 (02-07) | `covid.html` linked from the footer, legibly | present; legal-line anchor at **10.44:1** | **PASS** |
| WR-10 (02-07) | One dial string across every CTA | exactly one CTA `tel:` value (`+35929549710`); the three local-form display links intact and distinct by design | **PASS** |

The focus-ring and badge figures reproduce plan 02-05's hand-computed values (5.93, 4.50, 9.49,
11.09, 10.14) exactly. The executors' arithmetic was right; only their capability claim was wrong.

### Handset dialler check — owner-confirmed

Plan 02-07's `<human-check>` requires tapping the hero, call-bar and footer call buttons on a real
handset and confirming the dialler opens pre-filled. This is device behaviour and was **not**
measured by the harness — a headless browser cannot invoke a telephony intent.

**Status: PASS — confirmed by the project owner**, 2026-08-06, on their instruction. Recorded as
owner acceptance (the UAT sign-off), not as an automated observation. What the harness *did* verify
independently is that the underlying href is correct and single-sourced: exactly one CTA `tel:`
value, valid E.164, present on all sixteen deployed pages.

## Not covered

- **WINDOWS entry 7 — FOUT / web-font-swap backstop.** Still open. Measuring Sofia Sans fallback
  reflow against hero CTA displacement needs font-request blocking plus network throttling; the
  harness supports it (`Network.setBlockedURLs`) but no probe was written.
- **Rendered checks covered `index.html` only.** The other fifteen pages were verified by HTTP sweep
  (200, `php=0`, wiring present, `lang="bg"`), not rendered in a browser.
- **WINDOWS entries 10 and 11** remain open as recorded: the script-enabled-but-`site.js`-fails
  residual, and the desktop no-script two-row nav shape from `flex: 1 0 100%`.

## Reproducing

```bash
scripts/render-check.sh scripts/probes/hero-stack.js    'https://torin.bg/new/index.html' 360 640
scripts/render-check.sh scripts/probes/focus-rings.js   'https://torin.bg/new/index.html' 1440 900
scripts/render-check.sh scripts/probes/focus-rings.js   'https://torin.bg/new/index.html?theme=a' 1440 900
scripts/render-check.sh scripts/probes/no-script-nav.js 'https://torin.bg/new/index.html' 1440 900 --no-script
```
