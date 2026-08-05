---
phase: 02-design-system-information-architecture
plan: 01
subsystem: design-system
tags: [css-tokens, typography, php-includes, theming, apache, ftps]
status: complete
requires: []
provides:
  - "src/css/{base,layout,components}.css — the production design-system token layer"
  - "src/css/theme-a.css — dev-only Theme A colour override (10 tokens)"
  - "src/includes/header.php — document head, per-page $torin_title/$torin_desc, dev gate"
  - "src/includes/footer.php — design-system footer shell"
  - "src/includes/icons.php — torin_icon(), 15 inline SVG icons"
  - "src/includes/dev-switcher.php — whitelist-validated theme selection"
  - "src/fonts/sofia-sans-{cyrillic,latin}.woff2 — self-hosted Sofia Sans"
  - "scripts/deploy-new.sh — FTPS upload to public_html/new/"
affects:
  - "All 16 pages: they include header.php/footer.php, so the design system reached every page at once"
tech-stack:
  added: []
  patterns:
    - "Flat CSS, no nesting, no @layer — cascade order is <link> order"
    - "Mobile-first, min-width media queries only (35rem / 56.25rem), never max-width"
    - "Amber is a surface token only; never a text/border colour"
    - "Whitelist-then-literal for every client-supplied value that reaches markup"
key-files:
  created:
    - src/css/base.css
    - src/css/layout.css
    - src/css/components.css
    - src/css/theme-a.css
    - src/includes/icons.php
    - src/includes/dev-switcher.php
    - src/fonts/sofia-sans-cyrillic.woff2
    - src/fonts/sofia-sans-latin.woff2
    - src/img/torin-logo.png
    - src/favicon.ico
    - scripts/deploy-new.sh
  modified:
    - src/includes/header.php
    - src/includes/footer.php
    - src/index.html
    - src/.htaccess
decisions:
  - "mod_deflate and mod_expires ARE available on bell.host.bg — the 20 KB CSS budget is a target, not a hard wire cost"
  - "FTPS uploads to bell.host.bg must cap the data channel at TLS 1.2; TLS 1.3 silently corrupts anything over ~16 KB"
  - "The hero CTA buttons cannot share a row at 360px (367.6px of button against a 328px content box) — the two-row CTA block is a fixed constraint, not a tuning knob"
metrics:
  duration: 55min
  completed: 2026-08-06
actuals:
  tokens: 9262
  tasks: 2
  commits: 3
---

# Phase 02 Plan 01: Design System Tracer Summary

Proved the entire Phase 2 design system end-to-end on the real host — self-hosted Sofia Sans with gate-verified Bulgarian letterforms, a complete Theme B token layer, rewritten PHP chrome, 15 inline SVG icons, and a dev-only theme switcher — with zero legacy vendor JavaScript surviving anywhere in the response.

## What Was Built

**The tracer slice wired every layer at once:** Apache config → font binary → CSS token layer → PHP utility layer → PHP shared chrome → page markup → live host. Because all 16 pages already include `header.php`/`footer.php`, the design system reached every page the moment the includes were deployed.

| Artifact | Raw | Gzipped on the wire |
|---|---:|---:|
| `css/base.css` | 6,138 | 2,857 |
| `css/layout.css` | 2,205 | 960 |
| `css/components.css` | 5,979 | 2,451 |
| **Production CSS total** | **14,322** | **6,268** |
| `css/theme-a.css` (dev only) | 511 | — |
| `includes/icons.php` (15 icons) | 5,194 | — |
| Fonts (cyrillic + latin woff2) | 65,940 | — |

## Explicitly Recorded Findings

### 1. Compression IS available — the CSS budget is a target, not a hard limit

`mod_deflate` and `mod_expires` both loaded successfully under their `<IfModule>` guards. The gzip probe returns `content-encoding: gzip`, and CSS now carries `cache-control: max-age=604800`.

This **reverses the research baseline** (02-RESEARCH §2c and the UI-SPEC §Design System note both assumed "this host serves no compression at all — every byte is wire cost"). Production CSS costs **6,268 bytes on the wire, not 14,322** — a 56% saving. **Plans 02-02 through 02-04 should size their CSS against the 20 KB figure as a target, not a hard wire-cost limit.** The `AddType font/woff2` line also worked: fonts are served as `content-type: font/woff2`.

### 2. Font binaries matched exactly — no mismatch to explain away

Both files matched 02-RESEARCH §1d's recorded sizes **exactly**: cyrillic **25,568 B** and latin **40,372 B**. No re-cut occurred, so the "a mismatch is a recorded note, not a failure" allowance was not needed. **A later reader should not treat these numbers as a regression signal** — they are a clean match.

The authoritative check is the shaping gate, and it passed: **`glyphs differing bg vs ru: 15/15` → `PASS`**. The `bg` run shapes to unsuffixed default glyphs and the `ru` run to `.loclRUS` variants, confirming Bulgarian letterforms are Sofia Sans's *default* outlines (D-06a) rather than something dependent on browser `locl` support.

### 3. No consumer of the scalar phone config value survives

`grep -rn "site\['phone'\]" src/` returns **no match**. The legacy secondary-bar echo is gone and `header.php` emits no phone value at all. This is the precondition **plan 02-03 asserts before promoting `site-config.php`'s `phone` key from a scalar to a list** (D-33) — that promotion is now safe to make without a coupled edit to `header.php`.

### 4. Web-font swap backstop — observed, and NOT clean

**Cold-cache first paint shows a brief flash of invisible text; warm cache shows nothing. No layout shift was observed.**

The user's first report was "jumping", corrected on reflection to "blinking". Resolved from the code rather than the recollection:

- `font-display: **swap**` is set on **both** `@font-face` blocks — verified in the live CSS. This is **not** `block`/`auto`, so it is not the classic 3-second FOIT. The blink is `swap`'s short (~100 ms) block period before the fallback paints.
- The Cyrillic subset is preloaded with `crossorigin` present and matching the anonymous CORS mode `@font-face` always uses; the Latin subset is deliberately not preloaded (per UI-SPEC). So the invisible window is not being lengthened by a preload mismatch.

Measured objectively with a headless browser, cache disabled: **CLS = 0 before icons were added, and 0.00053 after** — roughly 200× under the 0.1 "good" threshold, and attributable to the `1.25em`-sized inline SVGs settling as the font resolves.

**This is recorded as an observed, unmitigated first-visit paint effect, not as "no issue".** `font-display` was deliberately **not** changed: UI-SPEC specifies `swap` and the code matches it, and moving to `optional`/`block` trades the blink for either skipped branding or true FOIT. A later phase may choose to size-adjust the fallback stack (`size-adjust`/`ascent-override` on a local fallback `@font-face`), which is the change that would close this properly.

## Deviations from Plan

### 1. [Rule 1 — Bug] Mobile hero overflowed D-30's above-the-fold budget

- **Found during:** Task 1 human-check (a)
- **Issue:** The hero measured **369 px = 57.7%** of a 640 px viewport against D-30/UI-SPEC's ≲42% (≲269 px).
- **Root cause (diagnosed, not shaved):** Measured the real Sofia Sans advance widths at `--fs-body`/700 — the two CTA labels are **178.2 px** and **189.4 px**, so against a 328 px content box at 360 px they **cannot** share a row even with reduced padding. The CTA block is intrinsically two rows (104 px). With `padding-block: var(--sp-2xl)` the content stack totalled ~369 px and **overflowed** the 268.8 px `min-height`, so `min-height` never bound and the hero was sized by content. UI-SPEC's own above-the-fold arithmetic had assumed `min-height` would bind and did not model the wrapped CTA row or the trust badge.
- **Fix:** Compact mobile-first spacing (`padding-block: --sp-sm`, CTA gap `--sp-sm`, CTA margin `--sp-md`, badge margin `--sp-sm`) brings the stack to 249.6 px, back under `min-height`. UI-SPEC's `--sp-2xl` padding and the `--sp-lg`/`--sp-xl` rhythm are **restored at ≥35 rem**, where both buttons fit one row. Stays `min-width`-only, so no adjacent-breakpoint gap.
- **Verified live:** 360×640 → **268.8 px = 42.0%**; 560×800 and 900×800 → 42.0%; `scrollWidth <= innerWidth` at all three.
- **Commit:** `69c4ca2`

### 2. [Rule 3 — Blocking] FTPS uploads silently corrupted over TLS 1.3

- **Found during:** Task 1 deploy
- **Issue:** Every file larger than a single ~16 KB TLS record was rejected with `451 Error during read from data connection` — **after** curl reported the bytes fully sent. Small text files appeared to succeed while every font/image binary failed. A deploy script without this fix reports partial success and leaves the site referencing assets that were never uploaded.
- **Fix:** `scripts/deploy-new.sh` caps the data channel at `--tls-max 1.2`. This keeps the transport **fully encrypted** and the `--pinnedpubkey` pin intact.
- **Rejected alternative:** `--ftp-ssl-control`, which would have put file bytes on the wire in the clear — an unnecessary downgrade of T-02-06 when a TLS-version cap fixes it without loss.

### 3. [Rule 3 — Blocking] Missing local assets and no upload tooling

- `header.php` references `favicon.ico` and `img/torin-logo.png`, neither of which existed under `src/`. Both copied in from `site-current/`.
- Phase 1 left only a *download* script (`backup-live-site.sh`). Created `scripts/deploy-new.sh` reusing its exact credential pattern (base64 decode inside a short-lived Python process into a chmod-600 netrc, `curl --netrc-file`, trap cleanup, pubkey pin), refusing any path outside `public_html/new/`.

### 4. [Minor] Mobile CTA gap uses `--sp-sm`, not `--sp-lg`

UI-SPEC §Spacing Scale notes `--sp-lg` as the "CTA button gap". Below 35 rem this is `--sp-sm`; `--sp-lg` is restored at ≥35 rem. D-30 is a locked decision and the hero's entire purpose, so it outranks a spacing usage note. Recorded rather than silently applied.

## Verification Results

| Gate | Result |
|---|---|
| 02-RESEARCH V1 — Bulgarian letterform shaping | **15/15 PASS** |
| 02-RESEARCH V3 — PHP 5.2 lint (all includes + index) | all greps silent |
| 02-RESEARCH V4 — UTF-8, no BOM | clean |
| 02-RESEARCH V8 — compression probe | **`content-encoding: gzip`** |
| `.htaccess` handler line survived / `<IfModule>` balanced | 1 / 4 open = 4 close |
| Live homepage | 200, `lang="bg"` ×1, charset in first 1024 B, `<?php` ×0 |
| Legacy vendor sweep (live) | **0** |
| `X-Robots-Tag: noindex, nofollow` | intact |
| Empty case (`about.html`, untouched stub) | 200, default title, both shells, 0 diagnostics, 0 empty wrappers |
| All 16 pages | 200 with `lang="bg"` exactly once |
| Theme flip: default / `?theme=a` / `?theme=b` | 0 / 1 / 0 `data-theme` |
| XSS via query param | no injected `script>`, no reflected attribute |
| **Cookie persists to a different page** | `about.html` renders `data-theme="a"`, flips back on reselect |
| **Forged cookie (`zzz`, and a script payload)** | 0 `data-theme`, no injected `script>` |
| Hero above-the-fold (live, measured) | 268.8 px = **42.0%** at 360×640 |
| Horizontal overflow | none at 360 / 560 / 900 |

## Human Check Results

| Item | Result |
|---|---|
| (a) Hero ≤42% of viewport, not a full-screen splash | **FAILED at first review (57.7%) → fixed and re-measured live at 42.0%** |
| (b) `scrollWidth <= innerWidth` | PASS |
| (c) Bulgarian oval letterforms vs the legacy site | PASS |
| (d) Font-swap reflow on throttled connection | Brief **flash of invisible text** on cold cache only; no layout movement (CLS 0.00053). Recorded as unmitigated — see Finding 4 |

## Known Stubs

| Item | File | Reason |
|---|---|---|
| Viber `href` is `[ASSUMED]` | `src/index.html` | Which of the three shop numbers is chat-capable is an open owner question (UI-SPEC C-9). Marked with an inline provenance comment as the transparency prohibition requires. |
| Nav placeholder | `src/includes/header.php` | An HTML comment, not an empty element, so the empty-wrapper assertion stays clean. Plan 02-03 inserts the Услуги disclosure nav. |
| Footer is a shell | `src/includes/footer.php` | Legal line only; the contact-first footer with three `tel:` links, hours and JSON-LD is plan 02-03 (D-33/D-34). |
| 13 of 15 icons unused so far | `src/includes/icons.php` | `cat-1`…`cat-6` are consumed by plan 02-02's category grid; `mail`/`pin`/`clock` by 02-03's footer; `chevron-down`/`menu`/`close` by 02-03's nav. |

## Notes for Future Plans

- **02-02 / 02-03 / 02-04:** the 20 KB CSS figure is a **target, not a hard wire cost** — real wire cost is ~44% of raw. Current headroom: 14,322 raw / 6,268 gzipped.
- **02-03:** the scalar-phone sweep is clean, so promoting `site-config.php`'s `phone` to a list is safe.
- **02-04 (Phase 4 cutover):** removal is `rm src/css/theme-a.css`, `rm src/includes/dev-switcher.php`, and deleting the two marked `DEV-ONLY` fences in `header.php` plus the `$torin_extra_head` echo. No token file is edited and there is no production CSS diff.
- `about.html` still carries the Bootstrap leftover `py-5` class; it is inert (undefined in the new CSS). Plan 02-04's stub sweep removes it across all pages.
- Verification tooling (`/tmp/fontcheck` venv, `/tmp/heightcheck` puppeteer) lives entirely outside the repo — no `package.json`, no `node_modules`, zero project dependencies added, as the phase constraint requires.

## Self-Check: PASSED
