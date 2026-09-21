---
phase: 04-hardening-cutover
plan: 08
subsystem: performance-seo
tags: [webp, picture-element, sitemap, robots, cache-lifetimes, htaccess]
status: complete

requires:
  - 04-07 ($torin_robots emitter shipping, so msg.html's noindex is live)
  - 04-06 (torin_asset_url ?v=<filemtime> stamp — the precondition for one-year lifetimes)
provides:
  - src/robots.txt and src/sitemap.xml (neither has ever existed on this site)
  - scripts/gen-sitemap.sh, scripts/sitemap-check.sh
  - 43 WebP siblings behind <picture>, originals retained as fallback
  - one-year cache lifetimes for stamped CSS/JS, with two documented exceptions
affects:
  - 04-09 (cutover: MUST re-run gen-sitemap.sh after base_url flips; MUST update robots.txt)
  - scripts/asset-version-check.sh (Check C now expects max-age exactly 31536000)

tech-stack:
  added: []
  patterns:
    - "picture element with the original <img> retained verbatim as fallback"
    - "derive the page set from the tree; never hard-code a slug list"
    - "static artefact + a check proved able to fail (RESEARCH A-6)"

key-files:
  created:
    - src/robots.txt
    - src/sitemap.xml
    - scripts/gen-sitemap.sh
    - scripts/sitemap-check.sh
    - src/img/**/*.webp (43 files)
  modified:
    - src/includes/category-page.php
    - src/includes/header.php
    - src/favicon.ico
    - src/.htaccess
    - scripts/asset-version-check.sh

decisions:
  - "Homepage listed at the directory root, not as index.html — with no rel=canonical in the tree yet, the sitemap is the only signal choosing between the two duplicates."
  - "HTML kept at 0 seconds rather than raised to minutes: these pages read settings.txt at request time and the owner must see an edit take effect."
  - "Images held at 30 days, not a year — image URLs carry no ?v= stamp."
  - "robots.txt disallows nothing; msg.html is deliberately NOT disallowed so the crawler can read its noindex."

metrics:
  duration: ~2h
  completed: 2026-09-21

actuals:
  tokens: 9700
  tasks: 3
  commits: 3
---

# Phase 04 Plan 08: Performance polish and the two SEO artefacts — Summary

WebP siblings behind `<picture>` cut the image set in half (1,223,371 → 614,846 B)
with every fallback intact and zero layout movement measured; `robots.txt` and
`sitemap.xml` exist for the first time in this site's history, guarded by a check
proved able to fail; stamped CSS/JS now cache for a year with the font and the
contact page excluded on purpose.

## Commits

| Task | Commit | What |
|------|--------|------|
| 1 | `8657d35` | WebP siblings behind `<picture>`, JPEG/PNG fallback intact, favicon rebuilt |
| 2 | `fa391dd` | robots.txt, sitemap.xml, gen-sitemap.sh, sitemap-check.sh |
| 3 | `54aa8e5` | One-year lifetimes for stamped assets, two exceptions |

## Task 1 — images

**43/43 conversions kept; none measured larger.** The measure-and-discard branch is
in the generator anyway, because a Phase 3 re-encode instruction *grew* three PNGs
by 16 KB. Format was decided by magic bytes, never extension — Phase 3 recorded a
GIF wearing a `.jpg` extension (`profilaktika6.jpg`); it is a genuine JPEG today,
verified here rather than assumed.

| | before | after | saved |
|---|---|---|---|
| image set (43 files) | 1,223,371 B | 614,846 B | 608,525 B (49.7%) |
| `favicon.ico` | 31,662 B | 3,571 B | 28,091 B (88.7%) |
| `torin-logo.png` → `.webp` | 19,147 B | 5,482 B | 13,665 B (71%), on all 19 pages |

The favicon was a **single uncompressed 100×100 24bpp BMP** inside an ICO. Re-emitted
as a PNG-compressed 16/32/48 ICO via a hand-written encoder (Node core + zlib; no
package installed). Its AND mask was measured first — **0 of 10,000 pixels
transparent** — so dropping alpha loses nothing. Rendered and eyeballed at 48×48.

**The `<picture>` wrapper was measured, not assumed.** `<picture>` is `display:inline`
and the brand link is a flex item, so a descender contribution would have grown the
56px header row. Measured in Brave through `render-check.sh` against a `file://`
fixture of the real CSS:

| viewport | figure/img/caption boxes | header/inner/brand | `currentSrc` |
|---|---|---|---|
| 360×640 | identical, `diffs: []` | — | `.webp` |
| 390×844 | identical, `diffs: []` | 57 / 56 / 40 identical | `.webp` |
| 1440×900 | — | 65 / 64 / 40 identical | `.webp` |

`currentSrc` proves the WebP branch is genuinely taken rather than the fallback
quietly winning; `naturalWidth` still reports true intrinsic pixels (257, 200, 150×80),
so the CLS contract holds. **No CSS change was needed** — `.evidence img` and
`.site-header__logo` are descendant/class selectors that survive the wrapper.

**Alt text byte-identical.** Diff filtered to alt attributes shows each string once
added and once removed, unchanged — they appear only because the `<img>` line moved
inside the wrapper:

```
added:   alt="<?php echo torin_esc($torin_ev_alt); ?>"   alt="ТОРИН КОМПЮТЪРС"
removed: alt="<?php echo torin_esc($torin_ev_alt); ?>"   alt="ТОРИН КОМПЮТЪРС"
```

Both real `<img>` tags retain `width`/`height`/`alt`. Logo stays eager (first
viewport); evidence strip stays `loading="lazy"` (below the fold).

## Task 2 — robots.txt and sitemap.xml

`gen-sitemap.sh` **derives** the page set: every `src/*.html` is a candidate, excluded
when it assigns `$torin_robots` containing `noindex` — the same fact `header.php`'s meta
emitter reads, so the sitemap and the page directive cannot drift apart. A hard-coded
slug list is exactly how `asset-version-check.sh` came to name six pages this tree no
longer has.

Result: **19 indexable pages listed**, `msg.html` excluded, `kontakti.html` included.
`base_url` read from `site-config.php`; no host literal in either script.

**The check was proved able to fail before being trusted.** Every branch run against a
deliberately corrupted input, via `--sitemap`/`--robots` overrides added so no tracked
file is ever mutated to test it:

| case | result |
|---|---|
| one extra + one missing URL | exit 1, **both** named |
| `msg.html` listed | exit 1, contradiction named |
| stale base URL (missed cutover) | exit 1, drift named |
| robots.txt pointing at wrong host | exit 1, both values printed |
| robots.txt with no sitemap directive | exit 1, named |
| empty sitemap | exit 1, all 19 missing + "no entries" |
| **control: the real files** | **exit 0, PASS** |

**Two defects found that way, neither visible by reading:**

1. `grep` exits 1 on zero matches, so under `set -euo pipefail` the empty-sitemap and
   missing-directive cases **aborted the script** — exiting non-zero having printed no
   reason at all, indistinguishable from a crash. Four sites fixed with `|| true`.
2. The generated header comment contained a literal `<loc>` tag, and `robots.txt` named
   the sitemap keyword in prose. A plain substring count therefore returned **one more
   than the truth** (20 and 2), which would have let a counting gate pass for a reason
   unrelated to the sitemap. Both reworded so each appears exactly once.

## Task 3 — cache lifetimes

Stylesheets and first-party scripts: **5 minutes → 1 year**. Verified first that every
one of those URLs is stamped — all four stylesheets and all three first-party scripts
go through `torin_asset_url()`; the only unstamped JS is the third-party Umami tracker
on another origin. Images stay at **30 days, not a year**, because image URLs carry no
stamp. The stale staging rationale was replaced, not left standing beside a new value.

Both exceptions are present and argued in place: the **unstamped Cyrillic font** (a stamp
would break the byte match with `base.css`'s `@font-face` and defeat `font-swap.js`'s
`*.woff2` glob) and **`kontakti.html` never cached** (signed render timestamp).

**Closed a gap this plan's own Task 1 opened:** `image/webp` and both `.ico` media types
matched **no** expiry rule and fell to undeclared heuristic caching — the identical defect
`js/site.js` had before Phase 2 named it and the JPEGs had before 03-02 named it. Making
it a third time, in the plan that adds the files, is how it would have shipped.

### Critical-path wire cost — 36.8 KB, unchanged by this plan

Measured with `Accept-Encoding: gzip` against the live origin (wire bytes, not disk):

| asset | wire | encoding |
|---|---|---|
| index.html | 5,292 | gzip |
| base.css | 1,696 | gzip |
| layout.css | 546 | gzip |
| components.css | 3,233 | gzip |
| site.js | 1,175 | gzip |
| analytics.js | 236 | identity |
| **sofia-sans-cyrillic.woff2** | **25,568** | identity |
| **total** | **37,746 B = 36.8 KB** | vs ~37 KB pre-phase baseline |

**The font is 68% of the entire critical path.** This plan does not move that number —
markup grows by ~66 raw bytes for the logo's `<source>` tag, well under a gzip block.
Its wins are deliberately *outside* the critical path: −608 KB of images and −28 KB of
favicon. Anyone planning further critical-path work should start at the font, not the
CSS: stripping every design-reasoning comment from all four stylesheets could not
recover a tenth of what the font costs.

### The font-swap probe measures something real

`font-swap.js` returns `maxAbsDeltaPx: 0`, `PASS`. A zero is exactly the shape the plan
warns about, so it was checked independently rather than accepted:

```
blocked: realFontAvailable=false  cyrillicAdvancePx=876.19  "Sofia Sans:error"
loaded:  realFontAvailable=true   cyrillicAdvancePx=899.91  "Sofia Sans:loaded"
advanceDeltaPx: 23.72   blockProven: true
```

The two passes genuinely render different fonts (23.72px of horizontal advance on a
Cyrillic string). The 0px *vertical* delta is therefore the designed result of
`base.css`'s `size-adjust: 97%` fallback, not a vacuous check. The font URL carries
**0** query strings live, so the block glob still matches.

*(Method note: the first attempt at this proof reported "BLOCK DID NOTHING" — because
it omitted `Network.enable`, without which `Network.setBlockedURLs` is silently a
no-op. The defect was in the proof, not in the project's probe. Recorded because the
same omission would make any future replication produce the same false alarm.)*

## Deviations from Plan

**1. [Rule 2] `src/includes/header.php` modified — outside declared `files_modified`.**
The logo `<img>` lives there, and `src/img/torin-logo.png` *is* declared. Wrapping it is
the only way to realise the largest per-visit win in the plan (13,665 B × 19 pages). The
edit is one line plus comments; geometry measured identical at both viewports.

**2. [Rule 3] `scripts/asset-version-check.sh` modified — outside declared `files_modified`.**
Its Check C asserted `max-age <= 600` and its own failure text read *"Phase 4 raises this,
DESIGN-02"*. DESIGN-02 is this plan's requirement, so this is that moment; left alone the
check would fail forever once the new `.htaccess` deploys. Changed to assert an **exact**
31536000 rather than a bound — see the gate finding below.

**3. HTML kept at 0 seconds, not raised to "minutes".** D4-34 says *"HTML stays at minutes"*;
the actual current value was 0 seconds, and 0 satisfies "short". Raising it would delay
owner edits to `settings.txt` (holiday banner, hours) from immediate to five minutes —
a user-facing regression for a repeat-view benefit. Flagged as a judgement call.

**4. Sitemap lists 19 URLs; the plan's gate demands ≥20.** Not padded. The tree has 20
page files, exactly one of which (`msg.html`) is no-indexed, so 19 is arithmetically
correct — and all 19 return **200** on the live origin, independently confirming the set.
The gate's threshold was hand-derived against an assumed page count.

## Gate findings (gates that do not measure what they claim)

Recorded because a gate passing for the wrong reason is worse than one that fails.

1. **`curl -sI .../base.css | grep 'cache-control:.*max-age=3'` is vacuous.** The deployed
   value is `max-age=300` and the new value is `max-age=31536000` — `max-age=3` is a
   substring of **both**. It reports green before and after the change it exists to guard.
   Replaced in `asset-version-check.sh` Check C with an exact comparison.
2. **`grep -o '<img[^>]*>' | grep -vc 'width='` is structurally wrong against PHP.** The
   `[^>]*` class terminates at the `>` of an embedded `?>`, truncating every real tag
   mid-attribute; and it counts `<img>` written as prose inside `//` comments. It reported
   **9 failures against markup that has none**. Re-checked with a small parser that skips
   `<?php … ?>` spans and requires a `src=`: **2 real tags, 0 missing width/height/alt.**
3. **`grep -c '<loc>'` and `grep -c 'Sitemap:'` count prose.** Both inflated by one until
   the comments were reworded. Fixed at the source rather than by changing the gates.
4. **`scripts/render-check.sh` accepts `file://` URLs** — which is how the `<picture>`
   layout question was answered locally despite there being no PHP runtime. Useful
   technique for any future CSS question that does not need the server.

## Known Gaps (ledger-worthy — for the orchestrator to file)

1. **`unrun-verify` — every live gate in Task 3 is unrun against this work.** There is no
   PHP binary and no Docker daemon on this machine, and `scripts/deploy-new.sh` is denied
   to subagents. `.htaccess`, the WebP files, `robots.txt` and `sitemap.xml` are **not on
   the server**. Confirmed live right now: `robots.txt` **404**, `sitemap.xml` **404**,
   `img/torin-logo.webp` **404**, stylesheets still `max-age=300`. Every live number in
   this summary is a **pre-deploy baseline of the currently-deployed build, not a pass**.
   After deploy, re-run: `scripts/sitemap-check.sh --live`, `scripts/asset-version-check.sh`,
   and `render-check.sh scripts/probes/trust-signals.js` at 360×640 and 1440×900.
2. **`unrun-verify` — PHP syntax of the two edited includes is unverified.** No `php -l`
   exists here. `category-page.php` and `header.php` were traced by hand (tag balance,
   5.2-safe dialect — `preg_replace`, no closures). STATE.md already records that for this
   project **the deploy is the only real syntax check**. A PHP parse error in `header.php`
   breaks all 19 pages at once, so this is the highest-risk unrun item in the plan.
3. **`unmet-truth` — `asset-version-check.sh` fails for 16 pre-existing reasons unrelated
   to this plan.** Its hard-coded slug list still names `covid`, `laptopi`,
   `rezervni-chasti`, `za-bateriite`, which now 301-redirect. Not caused here and not
   fixed here (out of scope); it is the same stale-list defect `gen-sitemap.sh` was
   written to avoid. Its Check C will also correctly report WRONG until the deploy lands.
4. **Confirms ledger #45 with direct evidence.** `css/theme-a.css` — deleted from the tree
   by 04-07 — still returns **200** on the server and is still picked up by
   `asset-version-check.sh`. The server does not mirror the tree; `deploy-new.sh` never
   deletes. 04-09's cutover checklist owns the removal pass. **Note the 43 new `.webp`
   files make this worse, not better**: a later reversal of this plan would leave 43
   orphans on the server.
5. **31 of the 39 photographs are unreachable dead weight.** Only 8 are referenced by any
   page (`meh-prob2/3/5`, `profilaktika1/2/3`, `zalivane1/2`); `ouroffice.jpg` and the
   three EU logo PNGs are referenced nowhere in the tree. They are still uploaded by
   `deploy-new.sh`, and this plan has now doubled their file count. Deliberately **not**
   deleted here — outside scope and not the sanctioned skip reason — but a future cleanup
   plan should own it. Sizes: the 35 unreferenced originals are ~1.0 MB of the 1.2 MB set.
6. **`src/index.html` contains no `<img>` at all.** Three of the plan's Task 1 gates target
   it (`grep -c '<picture>' src/index.html`, the first-viewport lazy check). The homepage
   hero and category cards are icon/SVG-driven. The gates are not wrong so much as aimed
   at a page that has no photographs; the real image surface is `header.php` (logo, every
   page) and `category-page.php` (evidence strip).
7. **Search Console submission deliberately not done** — correctly deferred to the cutover
   plan by the plan's own `planner_assumptions`. Submitting now would point a crawler at
   `/new/` URLs about to move. **`robots.txt` is also inert until cutover**: it is honoured
   only at a domain root, never at `/new/robots.txt`.
8. **`gen-sitemap.sh` MUST be re-run after the cutover**, and `robots.txt`'s absolute
   sitemap URL hand-updated. Both encode the `/new/` segment. `sitemap-check.sh` Checks D
   and E fail loudly if either is forgotten — both demonstrated above.

## Self-Check: PASSED

Files verified present on disk: `src/robots.txt`, `src/sitemap.xml`, `scripts/gen-sitemap.sh`,
`scripts/sitemap-check.sh`, 43 × `src/img/**/*.webp`, `src/favicon.ico` (3,571 B).
Commits verified in `git log`: `8657d35`, `fa391dd`, `54aa8e5`.
Working tree clean; no tracked file deleted by any of the three commits.
