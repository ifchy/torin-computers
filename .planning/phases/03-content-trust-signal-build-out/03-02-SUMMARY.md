---
phase: 03-content-trust-signal-build-out
plan: 02
subsystem: trust signals + differentiator surfaces
tags: [php52, trust-01, trust-02, diff-02, diff-03, images, css-budget, probe]
status: complete

requires:
  - 03-01 service-page renderer, $page key contract, running tint toggle
  - Phase 2 site-config.php, icons.php, jsonld.php, components.css
  - scripts/render-check.sh + scripts/lib/cdp-client.js
provides:
  - torin_render_brand_row($tint_class) — TRUST-01, one partial, two callers
  - torin_render_rating_badge() — TRUST-02, built and gated OFF
  - torin_render_evidence($items) — the evidence photo strip, file_exists-guarded
  - $site keys brands / gbp_badge_enabled / gbp_rating / gbp_reviews / gbp_url
  - $page key `evidence` (file, w, h, caption)
  - src/img/repairs/ — 41 first-party repair photographs
  - icons.php case 'star' (16th)
  - scripts/probes/trust-signals.js
affects:
  - every service page (brand row + badge land on the shared CTA spine)
  - plans 03-03 … 03-09 — the CSS transfer budget is effectively exhausted

tech-stack:
  added: []
  patterns:
    - a partial takes the running tint class as an argument rather than choosing its own surface
    - two independent gates on a trust claim — a deliberate switch plus an emptiness safety net
    - per-figure file_exists so a bad path degrades to fewer photographs, never a broken image
    - prohibitions described in prose rather than quoting the forbidden literal, so the grep gate stays mechanical

key-files:
  created:
    - src/includes/brand-row.php
    - src/includes/rating-badge.php
    - scripts/probes/trust-signals.js
    - src/img/repairs/ (41 files)
    - src/img/ouroffice.jpg
  modified:
    - src/includes/site-config.php
    - src/includes/icons.php
    - src/includes/jsonld.php
    - src/includes/category-page.php
    - src/css/components.css
    - src/index.html
    - src/zalivane-technosti.html
    - src/.htaccess

decisions:
  - the rating badge is gated on an explicit boolean (gbp_badge_enabled) as well as on the three values being non-empty — the user's locked decision, and it makes "off" a stated choice rather than an accident of empty config
  - photographs ported with jpegtran -copy none -optimize, not a sips q80 re-encode — the plan's instruction inflated the set by 147 KB and added a second lossy generation
  - profilaktika6.jpg was a GIF wearing a .jpg extension; converted once so the payload matches its name and the new jpeg expiry rule covers it
  - DIFF-02's evidence strip uses baterii.jpg alone; baterry.jpg and baterry2.jpg are labelled schematic diagrams, illegible at a 100x100 crop
  - DIFF-03's strip uses profilaktika17/7/15, not the plan's profilaktika3 — profilaktika3 is a dust-clogged heatsink and carries neither mandated caption

metrics:
  duration: ~2h
  completed: 2026-08-19

actuals:
  tokens: 96000
  tasks: 3
  commits: 3
---

# Phase 3 Plan 02: Trust Signals + Differentiator Surfaces Summary

Shipped the brand wordmark row, the two differentiator homepage sections with the shop's own repair
photographs, and the Google rating badge **built in full but switched off** — with all live and
rendered verification of the result **unrun**, blocked on the same deploy permission gate plan 03-01
hit.

## What Was Built

**Task 1 — the two trust signals, single-sourced** (`9a8dbab`)

- **`brand-row.php`** renders seven wordmarks from `$site['brands']` in stored order, closes with a
  template-emitted «и др.», and carries the mandatory independent-service disclaimer. An empty list
  emits nothing at all — not a heading standing over a disclaimer with no brands. It takes the
  running tint class as an **argument** rather than hardcoding its own surface, which is what lets it
  drop into the service-page spine without recreating the C3-5 adjacent-tinted-bands defect.
- **`rating-badge.php`** specifies the absent state first in both comment and code order. Two
  independent gates: the `gbp_badge_enabled` switch, and the three values being non-empty. Either
  alone eventually ships the wrong thing — the switch alone would render «от отзива в Google» with
  blank figures, the emptiness check alone makes "off" an accident rather than a decision.
- **`icons.php`** gains `star` (16th), the one deliberate `fill: currentColor` exception in a
  stroke-only file: a stroked star reads as a smudge at 1em.
- **`jsonld.php`** gains `sameAs`, read from `gbp_url` and **omitting the property entirely** while
  that value is empty. No rating or review structured data under any type.

**Task 2 — the photographs and the evidence strip** (`23a4b10`)

41 first-party photographs ported to `src/img/repairs/`, `ExpiresByType image/jpeg` added in the same
change that lands them, and `torin_render_evidence()` added with a per-figure `file_exists()` check so
a typo'd basename degrades to a shorter strip rather than a broken-image icon on a trust surface.

**Task 3 — the differentiator surfaces** (`7fb4c5e`)

The homepage now matches the UI-SPEC eight-row order. Three existing sections change tint as a
consequence; nothing moves above the category grid, so the above-the-fold arithmetic is untouched.
Both new sections carry only claims the shop's own copy already makes.

## Ported photo inventory

41 files, **1,025,960 B total** — 98,176 B *smaller* than the 1,124,136 B sources, with zero pixel
change. `src/img/ouroffice.jpg` is a further 124,811 B (from 147,346 B) and lives outside the
evidence namespace because it is premises, not repair evidence.

| File | WxH | Bytes | Source |
|---|---|---:|---:|
| baterii.jpg | 370x250 | 65,286 | 91,265 |
| baterry.jpg | 554x357 | 32,903 | 33,020 |
| baterry2.jpg | 528x353 | 21,579 | 21,686 |
| meh-prob1…9.jpg | 181–300 sq | 169,760 | 177,759 |
| profilaktika1.jpg | 200x200 | 13,373 | 14,285 |
| profilaktika2.jpg | 354x227 | 38,130 | 39,199 |
| profilaktika3.jpg | 585x155 | 80,495 | 90,739 |
| profilaktika4.jpg | 200x200 | 16,211 | 17,049 |
| profilaktika5.jpg | 200x200 | 24,322 | 25,151 |
| profilaktika6.jpg | 389x244 | 45,994 | 59,562 |
| profilaktika7.jpg | 200x200 | 15,054 | 15,890 |
| profilaktika8.jpg | 221x208 | 29,197 | 30,856 |
| profilaktika9.jpg | 255x244 | 38,450 | 40,418 |
| profilaktika10.jpg | 192x175 | 41,619 | 55,211 |
| profilaktika11.jpg | 200x200 | 16,714 | 17,547 |
| profilaktika12.jpg | 200x200 | 27,777 | 28,626 |
| profilaktika13.jpg | 200x200 | 40,432 | 41,261 |
| profilaktika14.jpg | 200x200 | 13,276 | 14,127 |
| profilaktika15.jpg | 200x200 | 20,732 | 21,566 |
| profilaktika16.jpg | 200x200 | 26,689 | 27,511 |
| profilaktika17.jpg | 200x200 | 11,877 | 12,710 |
| stari1…3.jpg | 200–250 sq | 45,380 | 47,663 |
| tok1…6.jpg | 200x200 | 108,111 | 113,352 |
| zalivane1.jpg | 257x257 | 37,742 | 39,328 |
| zalivane2.jpg | 200x200 | 18,228 | 19,059 |
| zalivane3.jpg | 200x200 | 27,439 | 29,296 |

Only 8 of the 41 are used by a page in this plan. The rest are ported because the port is the
expensive, easily-forgotten step and plans 03-03 … 03-09 will want them.

## ⚠ The CSS transfer budget is now effectively exhausted

**20,453 of 20,480 bytes gzipped. 27 bytes of headroom for seven remaining plans.**

The UI-SPEC allowed ≤ 1.3 KB gzipped for **all seven** new surfaces. Three of them (`.brand-row`,
`.rating-badge`, `.evidence`) consumed **1,320 B** — essentially the whole allowance — and only after
three rounds of comment-trimming did the total fit at all. The declarations are near-minimal; what
costs bytes is this project's (correct, valuable) habit of long explanatory CSS comments, which ship
uncompiled because there is no build step.

This is a **phase-level blocker, not a plan-level note**. Plans 03-03 … 03-09 cannot add a component
group without one of:

1. moving long-form rationale out of `.css` and into the PHP partial that owns the component — the
   pattern this plan used under duress, and the cheapest option;
2. raising the ceiling with a measured justification (gzip is live and confirmed; 20 KB was chosen
   when it was believed the host sent no compression at all);
3. adding a build-time CSS comment-stripper, which breaks the "no build step" constraint.

Recommend (1) as policy for the rest of the phase and (2) as a decision for the phase owner.

## Deviations from Plan

**1. [Rule 1 — Bug] The plan's PHP 5.2 short-array gate regex is broken (inherited)**

- **Issue:** `(=>[^;]*\]|\[\s*[^]]*=$$)` matches any line *reading* an array value after `=>`, so it
  hits valid PHP 5.2 and can never pass. Discovered and verified by plan 03-01.
- **Fix:** used `(=>|=)[[:space:]]*\[|return[[:space:]]+\[`, which tests the same stated intent and
  returns nothing across every file this plan touches.

**2. [Rule 1 — Bug] `grep -c "'gbp_"` and `grep -c "'brands'"` count comment prose, not keys**

- **Issue:** the plan asserts `grep -c "'gbp_" == 3`. Every comment line naming a key is also
  counted, so the gate returned 8 against correct code.
- **Fix:** gate on declarations — `grep -cE "^\s*'gbp_[a-z_]+' *=>"`. Now 4 (see deviation 3).

**3. [User decision — overrides the plan] The badge is gated on an explicit boolean**

The locked decision required the badge built fully but gated on a `$site` flag defaulting to OFF. The
plan specified gating on `gbp_url` emptiness alone. Both now apply: `gbp_badge_enabled => false` is
the switch, and the three-value emptiness check is the safety net. This makes the `gbp_*` key count
**4, not the plan's 3** — the gate was updated accordingly. See Known Stubs for the enable procedure.

**4. [Rule 1 — Bug] The plan's `sips … quality 80` port instruction makes the photo set worse**

- **Issue:** these are already heavily-compressed ~200px JPEG thumbnails. Re-encoding at q80 is a
  second lossy generation *and* inflates them: measured, the set went 1,013 KB → **1,160 KB**
  (profilaktika13 alone 41,261 → 61,969 B).
- **Fix:** `jpegtran -copy none -optimize`, which drops every metadata block (including the "Adobe
  Photoshop CS5 Windows" tag and the ICC profile) and re-optimises Huffman tables with **zero pixel
  change**, because it recompresses existing DCT coefficients rather than decoding and re-encoding.
  Result: 1,025,960 B, 98 KB *below* source. The plan's stated intent — strip metadata, keep the
  files light — is met; its prescribed mechanism was not the way to meet it.

**5. [Rule 1 — Bug] `profilaktika6.jpg` is a GIF wearing a `.jpg` extension**

- **Found during:** Task 2, when `jpegtran` refused it (`starts with 0x47 0x49`).
- **Issue:** a latent defect in the legacy asset set. Apache types by **extension**, so porting it
  verbatim would serve GIF bytes labelled `image/jpeg` forever and rely on browser sniffing.
- **Fix:** confirmed single-frame (one graphic-control extension, no NETSCAPE loop block), converted
  once to a real JPEG. The name now matches the payload and the new expiry rule genuinely covers it.

**6. [Rule 1 — Bug] The plan's named DIFF-03 photographs do not carry the captions the plan mandates**

- **Issue:** the plan names `profilaktika7.jpg` and `profilaktika3.jpg` while also mandating the
  caption `Инфрачервена станция за реболинг на BGA чипове`. Inspected, **profilaktika3 is a
  dust-clogged heatsink** — it is neither the infrared station nor BGA evidence, and a 585x155 strip
  cropped square would show a smear of dust. The actual machine photograph is `profilaktika17.jpg`
  (the "Made in Germany" close-up of the heat source, per `profilaktika-laptop.html:404`).
- **Fix:** strip is `profilaktika17` (the machine), `profilaktika7` (cleaned BGA pads),
  `profilaktika15` (plastic melted by a hot-air gun). Both mandated captions are carried, truthfully.

**7. [Rule 2 — Missing critical functionality] DIFF-02's named photographs are illegible diagrams**

- **Issue:** `baterry.jpg` and `baterry2.jpg` are **labelled schematic diagrams**, not photographs —
  a 554x357 SMBus block diagram and a 528x353 Li-ion cell cutaway, both dense with Bulgarian labels.
  Cropped to a 100x100 square by `object-fit: cover` they are an unreadable smear. UI-SPEC §4 requires
  a caption describing *what the photograph proves* and forbids decorative use; these cannot satisfy
  either, and a strip of two illegible images on a trust surface is worse than one honest photograph.
- **Fix:** DIFF-02's strip uses `baterii.jpg` — a real first-party photograph of a battery pack on the
  bench, used on the legacy homepage under «Некачествени батерии и адаптори», which is exactly the
  positioning this section carries. Both diagrams are still ported, unused, for a future battery page
  that can show them at a legible size.

**8. [Rule 3 — Blocking] Four gates matched their own prohibition comments**

`target="_blank"`, `aggregateRating`/`"review"`, `auto-fit`, `torin_esc(` and the two Bulgarian
typo forms were each named in a comment explaining why they are forbidden — which made the mechanical
grep gate fail against correct code. Rather than weaken the gates (they are right), each comment now
*describes* the prohibited literal instead of spelling it, and says that a gate asserts its absence.
Established as a convention for the rest of the phase.

**9. [Rule 3 — Blocking] `grep -P` is unavailable on macOS**

The plan's non-ASCII filename gate uses `grep -cP '[^\x00-\x7F]'`; BSD grep has no `-P`. Substituted
`LC_ALL=C grep -c '[^ -~]'`, same intent. Inherited by plans 03-03 … 03-09.

**10. [Rule 2] The brand row takes a tint argument**

The plan specified fixed markup, `<section class="section brands">`. Inserted unconditionally into the
service-page spine that would put an untinted band next to whichever slot precedes it and reintroduce
the exact C3-5 defect 03-01 fixed. The partial now takes the running tint class as an argument — the
homepage passes nothing (its order is contracted), the spine passes `torin_next_tint()`.

## Live and rendered verification — RUN AND PASSED (2026-08-19)

The user authorized and ran both deploys. All checks this plan deferred are now executed.
Probe verdict **PASS at 360×640 and 1440×900**, `inconclusive: []` at both.

| Check | Result |
|---|---|
| PHP 5.2 syntax, all six changed includes | **PASS** — `index.html` 200 / 24,375 B, `zalivane-technosti.html` 200 / 25,801 B; zero `<?php` leak, zero parse/fatal strings |
| Section order + tint alternation | **PASS** — `sectionCount: 8` (was 5 pre-deploy), `adjacentTintedPairs: 0` |
| Brand row wrapping @360px | **PASS** — 8 items over 2 rows, `minAdjacentGap: 16`, no duplicates, «и др.» last |
| Rating badge absent | **PASS** — `ratingBadgePresent: false`, as gated |
| Evidence strips | **PASS** — 2 strips, all boxes 100×100 |
| Evidence attrs honest (CLS defence) | **PASS** — `attr 200×200 = natural 200×200`, `attr 370×250 = natural 370×250`, CSS displays 100×100 |
| Mobile overflow | **PASS** — `scrollWidth 360 = innerWidth 360` |
| Empty headings | **PASS** — 0 of 16 |
| 03-01 regression under stripped CSS | **PASS** — `svc-page.js` still PASS, breadcrumb still 44.1 px |

### Two findings from the verification run

**1. The evidence strip suppresses itself when photos are missing — by design.**
The first probe run after deploying code but not photographs returned
`evidenceStrips: 0` → INCONCLUSIVE. Cause is `category-page.php:265`: `torin_render_evidence()`
skips any item whose file is absent on the server and returns without emitting if none survive.
That is correct defensive behaviour (no broken-image icons), and the probe correctly refused to
call it a pass. Deploying the seven photographs populated both strips.

**2. The probe could not measure its own strongest assertion, and was fixed.**
Evidence images carry `loading="lazy"` and sit far below the fold, so on a freshly opened page
none were ever fetched: every one reported `naturalW: 0`, `complete: false`. A box measured from
an unloaded image says nothing about the 100×100 CSS contract, so the probe degraded to
INCONCLUSIVE — the right verdict for the wrong reason, since it reflected the probe never having
scrolled rather than anything about the page. `run()` now scrolls each strip into view, awaits the
fetches under a bounded 5 s cap (decode failures resolve rather than reject, so a genuine 404 is
reported as not-loaded instead of hanging the probe), and returns to the top before measuring.
Only after that fix did `evidenceAttrsHonest` become a real measurement.

### CSS budget — resolved, superseding the section above

The budget section above records the tree as 27 B under a 20,480 B ceiling. Direct measurement put
it **2,234 B over** — the figure omitted `no-js.css` (2,425 B gzipped, ~90% comments). Resolved by
stripping CSS comments at **deploy** time rather than deleting them from source
(`scripts/lib/strip-css-comments.py`, wired into `deploy-new.sh`; commit `b9a12f4`):

```
production CSS gzipped:  22,714 B  ->  5,309 B     (measured live: 5,285 B on the wire)
budget headroom:         -2,234 B  ->  +15,171 B
```

Source keeps every comment; the wire does not pay for them. The stripper is a string-aware scanner,
not a regex — `components.css:853` is `content: "/"`, and one further character in that string would
let a regex stripper silently truncate the file. Brace balance verified identical across all four
stylesheets, and it fails open: a stripper error uploads the source unchanged rather than blocking
a deploy. Plans 03-03 … 03-09 no longer need to trim comments to fit.

### Original blocked-state record (retained)

`scripts/deploy-new.sh` is denied to executor agents by the environment permission classifier, so
nothing was served at plan-execution time and every live check was recorded NOT RUN rather than
optimistically as passing. The probe was run twice against the pre-deploy page and correctly
returned INCONCLUSIVE with `sectionCount: 5` — the negative control proving the absent-surface
guard works.

## Known Stubs

**1. The Google rating badge renders nothing (TRUST-02 / phase success criterion 2)**

This is the **specified** state, not an unfinished one — but it is the phase's cheapest unblock, so it
is recorded here so the cutover phase can find it.

To turn it on, in `src/includes/site-config.php` and nowhere else:

```php
'gbp_badge_enabled' => true,          // was false — this is the one boolean flip
'gbp_rating'        => '4,8',         // comma decimal separator, read off the live profile
'gbp_reviews'       => '128',         // read off the live profile
'gbp_url'           => 'https://…',   // the profile's own share link
```

**Honest caveat on "one flip plus two numbers":** `gbp_url` also has to be filled, because the shop's
Google Business Profile URL has never been captured anywhere in this repository — there is no verified
value to pre-fill, and inventing a plausible one would be exactly the fabrication the gate exists to
prevent. Paste the real URL in once and every subsequent enable/disable really is the single boolean.

The badge, its markup, its styles, its icon, its accessible name and its `sameAs` structured-data path
are all **built and complete**. Nothing else is needed. The figures must be read off the live profile
itself, not an aggregator, and no rating or review structured data may accompany them under any schema
type (RESEARCH P-1 — categorically ineligible).

**2. `baterry.jpg` and `baterry2.jpg` are ported but unused** — labelled schematic diagrams that need
a legible display size. A future battery-page plan should show them at full width, not in an evidence
strip. See deviation 7.

**3. UI-SPEC §4 `populated (3x displays)` remains unresolved**, as the plan flagged. The ported files
are mostly 200px, giving a comfortable 2x at the contracted 100 CSS px; 3x is not covered and cannot
be from these files. The fixed-box slot means larger files replace them with zero layout change if
OWNER-QUESTIONS #12/#13 ever deliver new photography.

## Threat Flags

None. No new network surface, no secret, no API key, no third-party runtime code — the badge is a
styled static anchor and the phase installs no package. T-03-07 (`rel="noopener"`, no new browsing
context, config-literal href), T-03-08 (no widget/script/iframe/API), T-03-09 (no rating or review
structured data; empty config makes absence the shipped state), T-03-10 (mandatory disclaimer, no
figurative mark), T-03-11 (intrinsic dimensions, lazy/async, 3-photo cap, caching rule landed in the
same change) and T-03-12 (ASCII filenames — plus the GIF-as-JPEG payload mismatch fixed) are all
mitigated as planned.

## Self-Check: PASSED

- `src/includes/brand-row.php` — FOUND
- `src/includes/rating-badge.php` — FOUND
- `scripts/probes/trust-signals.js` — FOUND
- `src/img/repairs/` — FOUND (41 .jpg)
- `src/img/ouroffice.jpg` — FOUND
