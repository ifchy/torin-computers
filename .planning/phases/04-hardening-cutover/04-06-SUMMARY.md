---
phase: 04-hardening-cutover
plan: 06
subsystem: config
tags: [php, settings-file, structured-data, json-ld, schema-org, css, owner-editable, fail-safe-parsing]

requires:
  - phase: 04-01
    provides: PHP 8.5 runtime on /new/, the measured host capability record (mbstring present), and the FastCGI artefacts this plan denies over HTTP
  - phase: 04-04
    provides: site-config.php's post-Viber shape and the components.css baseline this plan's CSS delta is measured against
  - phase: 02
    provides: the .notice grid, the dark-surface focus-ring groups, the provenance-comment and [ASSUMED]-marker conventions
provides:
  - "A plain-text settings file the owner edits in cPanel File Manager, parsed by a hand-rolled loop that cannot warn, halt or throw"
  - "Per-key validation with per-key fallback, plus a served-page sentinel that makes a fallback detectable rather than merely survivable"
  - "One working-hours value driving four consumers: the footer hours line, the footer notice band, the contact page and the structured data"
  - "A self-expiring, non-dismissible holiday closure strip with zero JavaScript, measured at 172.3px against a 180px gate"
  - "The weekend closure stated explicitly in the structured data per Google's documented shape, rather than inferred from omission"
  - "docs/naruchnik-nastroyki.md — the Bulgarian owner guide, including the Google Business Profile caveat"
  - "The jsonld.php runtime-upgrade escaping re-check, discharged by measurement and converted into a standing prohibition"
affects: [04-07, 04-09, 04-10, cutover, analytics, owner-handover]

actuals:
  tokens: 22752
  tasks: 3
  commits: 3

tech-stack:
  added: []
  patterns:
    - "Owner-editable plain-text config with per-key validation and per-key fallback, never whole-file"
    - "Degraded-state sentinel emitted into the served page so a remote check can assert on it (the ?v=0 idiom, applied to config)"
    - "Controlled token + lookup row, so a human-readable form and a machine-readable form cannot drift"
    - "Date-gated partial called once from the shared header, emitting nothing when off"

key-files:
  created:
    - src/includes/settings.php
    - src/includes/banner.php
    - src/settings.txt.example
    - docs/naruchnik-nastroyki.md
    - scripts/settings-selftest.php
    - scripts/probes/holiday-banner.js
  modified:
    - src/includes/site-config.php
    - src/includes/jsonld.php
    - src/includes/footer.php
    - src/includes/header.php
    - src/css/components.css
    - src/css/base.css
    - src/.htaccess
    - scripts/probes/focus-rings.js

key-decisions:
  - "hours_days is a controlled token drawn from a three-entry allowlist, not free text bounded at 20 characters — a length bound alone would let the page and the search engine disagree, which is the exact drift OWNER-01 exists to remove"
  - "vacation_message is capped at 120 CODE POINTS, not the plan action's 300 characters: 300 makes the 180px banner backstop arithmetically unmeetable and halves the real allowance for Cyrillic"
  - "The banner renders only while the closure is CURRENT; the structured data publishes the period as soon as it is scheduled. Deliberate asymmetry, argued in banner.php's header, flagged as an owner question"
  - "The jsonld.php slash escaping is unchanged on PHP 8.5 but is now a DEFAULT rather than a structural property — recorded as a standing prohibition on passing any encoding flag, which is what the existing grep gate now enforces"
  - "settings.txt lives inside the document root (so the owner reaches it in File Manager) and is denied over HTTP; the path is computed from includes/ so the cutover needs no edit"
  - "src/settings.txt is deliberately NOT gitignored — gitignoring it would let a developer create one invisibly, and deploy-new.sh uploads by filesystem walk regardless of git"

patterns-established:
  - "Per-key fallback with a served-page sentinel: the degraded state is made visible rather than silent, mirroring asset-version.php's ?v=0"
  - "One lookup row carries every form of a fact (Bulgarian sentence-case, Bulgarian lower-case, open-day enums, closed-day enums) so the forms are columns of one row rather than readings of one string"
  - "A runtime-upgrade warning left in a source file is discharged by MEASURING the upgraded runtime and rewriting the comment to the measured result, never by deleting the warning"

requirements-completed: []

coverage:
  - id: D1
    description: "Owner-editable settings file with a parser that drops one bad key and never white-screens a page"
    requirement: OWNER-01
    verification:
      - kind: unit
        ref: "scripts/settings-selftest.php (12 behaviour assertions)"
        status: unknown
      - kind: other
        ref: "node harness over the shipped regexes/bounds/tokens extracted from src/includes/settings.php — 12/12"
        status: pass
      - kind: e2e
        ref: "corrupt settings.txt on staging, re-measure all 20 pages for HTTP 200 + zero warnings"
        status: unknown
    human_judgment: false
  - id: D2
    description: "settings.txt, .user.ini and the panel's FastCGI artefacts denied over HTTP"
    requirement: OWNER-01
    verification:
      - kind: e2e
        ref: "curl https://torin.bg/new/settings.txt (and .user.ini, php.fcgi) expecting 403/404"
        status: unknown
    human_judgment: false
  - id: D3
    description: "Self-expiring, non-dismissible closure strip, off by default, bounded at 180px on mobile"
    requirement: OWNER-02
    verification:
      - kind: automated_ui
        ref: "scripts/render-check.sh scripts/probes/holiday-banner.js <harness> 360 640 — 172.3px, 5 lines, inset keyline, position static, 0 interactive descendants"
        status: pass
      - kind: automated_ui
        ref: "scripts/render-check.sh scripts/probes/focus-rings.js <harness> 360 640 — ring #ffd84d at 11.09:1 on the banner fill"
        status: pass
      - kind: unit
        ref: "scripts/settings-selftest.php date-gate assertions (off / current / end-date-today / expired / not-yet-started)"
        status: unknown
      - kind: other
        ref: "node harness over the shipped date comparison in src/includes/banner.php — 10/10 boundary cases"
        status: pass
      - kind: e2e
        ref: "served page with dates set on staging: present twice, gone when expired"
        status: unknown
    human_judgment: false
  - id: D4
    description: "One hours value reaching the footer line, the footer notice band, the contact page and the structured data"
    requirement: OWNER-01
    verification:
      - kind: unit
        ref: "scripts/settings-selftest.php — 3 entries with no closure, 4 with a current one, back to 3 when past"
        status: unknown
      - kind: e2e
        ref: "one settings.txt edit on staging, three served values re-read"
        status: unknown
    human_judgment: false
  - id: D5
    description: "Bulgarian owner guide shipping with the file"
    requirement: OWNER-01
    verification: []
    human_judgment: true
    rationale: "Whether a non-technical Bulgarian reader can follow these instructions unaided is not something a grep can answer. It needs the owner to open cPanel with the guide in front of him."

duration: 25min
completed: 2026-09-20
status: complete
---

# Phase 4 Plan 06: Owner-editable hours and a self-expiring closure banner — Summary

**The working hours stopped being a PHP literal written in three places and became one plain-text value the owner can mistype without consequence; the closure strip measures 172.3px against a 180px gate and expires itself; every live gate in the plan is unrun because deploy and PHP are both denied in this executor.**

## Performance

- **Duration:** ~25 min
- **Started:** 2026-09-20T19:02+03:00
- **Completed:** 2026-09-20T19:23+03:00
- **Tasks:** 3 of 3
- **Files created:** 6 · **Files modified:** 8

## Accomplishments

- **`src/includes/settings.php`** — a hand-rolled parser that cannot warn, halt or throw. Per-key validation, per-key fallback, Europe/Sofia set at the loader, and a code-point-safe length counter. An absent file is a non-error, which is what makes shipping only the example safe.
- **The hours are single-sourced for the first time.** They were written in three places (`site-config.php`'s `hours`, `jsonld.php`'s clock literal, `site-config.php`'s `notice` band). All three are now composed from three machine-form keys. Four consumers, one value.
- **`src/includes/banner.php`** — the footer-notice idiom with a date gate instead of a non-empty gate. Off emits nothing at all. No script, no storage, no close button, not sticky. **Measured at 172.3px** at 360×640 with a full 120-code-point message.
- **The weekend is stated, not inferred.** Three `OpeningHoursSpecification` entries (weekday range + explicit Saturday + explicit Sunday at `00:00`/`00:00`), four while a closure is scheduled.
- **The runtime-upgrade escaping warning in `jsonld.php` is discharged** by measuring the live 8.5 runtime rather than reasoning about it — and the conclusion is sharper than "nothing changed" (see Decisions).
- **`docs/naruchnik-nastroyki.md`** — the Bulgarian guide, including the line that is easy and expensive to omit: changing the hours here does not change them in the Google Business Profile.

## Task Commits

1. **Task 1: A file the owner can break without breaking the site** — `8814ac3` (feat)
2. **Task 2: A closure strip that turns itself off** — `cd98cef` (feat)
3. **Task 3: One hours value, three consumers, and a guide in Bulgarian** — `688d8a0` (feat)

## Files Created/Modified

**Created**

- `src/includes/settings.php` — the parser, the validator, the code-point counter, the day-token map, the Bulgarian month array and the date-range formatter.
- `src/settings.txt.example` — the shipped example, commented in Bulgarian. `src/settings.txt` does **not** exist in the repository and must not.
- `src/includes/banner.php` — `torin_render_banner($site)`, one function, emits nothing on include.
- `docs/naruchnik-nastroyki.md` — the owner guide (178 lines, Bulgarian).
- `scripts/settings-selftest.php` — 22 assertions covering Task 1's behaviour block, Task 2's date gate and Task 3's entry counts. **Never executed** (see Known Gaps).
- `scripts/probes/holiday-banner.js` — rendered measurement of the strip: height, line count, grid tracks, keyline, position, interactive descendants.

**Modified**

- `src/includes/site-config.php` — six new keys, the settings merge, the derived block, `settings_path`, `settings_fallbacks`. Hours `[ASSUMED]` marker removed; the other two kept.
- `src/includes/jsonld.php` — clock literal removed, hours read from settings, weekend stated, closure appended conditionally, escaping paragraph rewritten to the measured result.
- `src/includes/footer.php` — notice-band comment corrected to name the composition; settings sentinel emitted.
- `src/includes/header.php` — one `require_once`, one call site, first child of `#wrap`.
- `src/css/components.css` — the `.holiday-banner` block, reusing `.notice`'s four grid declarations verbatim.
- `src/css/base.css` — `.holiday-banner` added to the dark-surface focus group.
- `src/.htaccess` — one module-guarded deny block for `settings.txt`, `.user.ini`, `php.fcgi`, `php*-fcgi.ini`.
- `scripts/probes/focus-rings.js` — taught that `.holiday-banner` is a dark surface.

## What Was Actually Measured

Everything in this section is a number read off a running browser or a live HTTP response. Nothing here is derived.

### Banner geometry — `scripts/probes/holiday-banner.js` at 360×640

Run against a local HTTP harness serving the real `src/` stylesheets and the real Cyrillic font subset, with the exact markup `banner.php` emits and a **120-code-point** message (paragraph total 152 cp including the 31-cp date prefix).

| Measured | Value | Contract |
|---|---:|---|
| Banner height | **172.3px** | ≤ 180px — **7.7px of headroom** |
| Lines | 5 | 5 predicted |
| Resolved line-height | 28.08px | 28.1 predicted |
| Grid tracks | `21.2656px 298.734px` | `.notice`'s 21.3 + 298.7 — icon column has **not** drifted |
| Keyline | `rgb(255,199,10) 0 -3px 0 0 inset` | inset shadow, never a border |
| `border-block-end-width` | `0px` | no border |
| `position` | `static` | not sticky |
| Interactive descendants | 0 | not dismissible |
| `<script>` descendants | 0 | no JavaScript at all |
| First child of `#wrap` | `true` | placement contract |
| Fill / ink | `rgb(10,37,71)` / `rgb(255,255,255)` | `--c-ink-deepest` / `--c-on-dark` |

**The fold consequence, stated rather than glossed.** Phase 2's D-30 arithmetic puts the homepage's first category card bottom at 574.8px with no banner, against 584px of usable viewport. With the strip at its cap: **747.1px** — below the fold, exactly as the UI contract predicted (747px). This is accepted for the duration of a closure and is not a defect.

### Focus ring inside the banner — `scripts/probes/focus-rings.js` at 360×640

Measured against a harness page with a link forced into the strip (the shipped strip has none).

```
outlineColor: rgb(255, 216, 77)   outlineWidth: 3px   outlineOffset: 2px
against --c-ink-deepest (the banner's own fill): 11.09:1
verdict: PASS — every production control shows a ring at >=3:1 on its own surface
```

The backstop truth is closed with a measurement rather than left vacuous on the grounds that the strip has no links today.

### The structured-data escaping re-check — live PHP 8.5, `https://torin.bg/new/index.html`, 2026-09-20

Read as a whole block and `JSON.parse`d, never line by line.

```
block 1: parses = true
  escaped-slash occurrences : 13
  bare-slash occurrences    : 0
  unicode-escape occurrences: 45
  raw cyrillic present      : false
```

Both behaviours the file relies on are **unchanged** by the 5.2 → 8.5 upgrade. No code change was needed and none was made. The conclusion that matters is in Decisions below.

### CSS delta (comment-stripped and gzipped — what actually goes on the wire)

| File | Before | After | Delta |
|---|---:|---:|---:|
| `src/css/components.css` | 3,222 B | 3,274 B | +52 B |
| `src/css/base.css` | 1,696 B | 1,704 B | +8 B |
| **Total** | | | **+60 B** |

Against the phase budget of **≤ 1.5 KB** gzipped for the whole site-wide CSS delta.

### Static gates

| Check | Result |
|---|---|
| Non-comment uses of the built-in ini parser in `settings.php` | 0 |
| Short-array constructs in `settings.php` / `banner.php` / `jsonld.php` | 0 / 0 / 0 |
| `date_default_timezone_set` in `settings.php` | 1 |
| `settings.txt.example` exists / `settings.txt` absent | yes / yes |
| Cyrillic lines in `settings.txt.example` | 46 |
| `function torin_read_settings(` / `function torin_setting_is_valid(` | 1 / 1 |
| `function torin_render_banner(` | 1 |
| `localStorage` / `addEventListener` / `<script>` in `banner.php` non-comment lines | 0 |
| `torin_render_banner` occurrences in `header.php` | 1 |
| `border:` within the `.holiday-banner__inner` block | 0 |
| Active `[ASSUMED]` markers in `site-config.php` | **3 → 2** (hours removed; `base_url` and `notice` intact) |
| `'08:00'` in `jsonld.php` non-comment lines | 0 |
| Cyrillic lines in `docs/naruchnik-nastroyki.md` | 101 |
| `Google Business Profile` in the guide | 1 |

**Read the clock-literal check carefully.** `jsonld.php` still contains four `'00:00'` literals. Those are Google's documented *closed-all-day* marker on the two weekend entries and the two ends of the closure period — they are not the shop's opening or closing time, and a naive grep for "any clock literal" will flag them. The criterion is satisfied; the literal the plan removes is gone.

### Behaviour harnesses run under Node

No PHP runtime exists on this machine, so the PHP self-test could not be executed. Instead two harnesses read the **shipped source text**, extracted the actual regular expressions, numeric bounds, day tokens and date comparison, and ran the plan's behaviour tables against them.

```
settings validator (patterns/bounds/tokens extracted from settings.php) : 12/12 passed
banner date gate  (comparison extracted from banner.php)                : 10/10 passed
```

This is evidence about **the patterns and the comparison that ship**. It is **not** evidence that the PHP parses or runs, and the two must not be conflated. Both harnesses were throwaway and are not committed; `scripts/settings-selftest.php` is the committed, runnable form.

### Before values captured from live staging (2026-09-20, pre-deploy)

Recorded so the one-edit-three-locations proof has a baseline the moment a deploy is possible:

| Location | Served value |
|---|---|
| Footer hours line | `Понеделник – Петък, 8:00 – 16:00` |
| Footer notice band | `Работно време: понеделник – петък, 8:00 – 16:00 ч.` |
| Contact page hours line | reads `$site['hours']` — same string |
| `openingHoursSpecification` | **1 entry**, `Mo–Fr 08:00–16:00` |

**After values were not captured. The deploy is blocked** — see Known Gaps.

## Decisions Made

**1. `hours_days` is a controlled token, not free text.** The plan's action text bounds it at 20 characters. A length bound alone lets the owner type «Понеделник – Събота» into the page while the structured data keeps publishing Monday-to-Friday — which is precisely the drift OWNER-01 exists to remove, and it would be invisible to anyone reading either one alone. The value is now validated against a three-row map (`Mo-Fr`, `Mo-Sa`, `Mo-Su`), and each row carries the Bulgarian sentence-case form, the Bulgarian lower-case form, the open-day enums and the closed-day enums together. The forms are columns of one row rather than readings of one string, so they cannot disagree. The 20-character bound is still enforced.

**2. The message cap is 120 code points, not 300 characters.** The plan's action text says 300; the UI contract says 120 code points and says explicitly that the research's byte cap is wrong for Cyrillic. 120 is binding for two independent reasons: a byte cap halves the allowance for Bulgarian and doubles it for Latin, and 300 code points renders as ten lines / 313px, which makes this plan's own 180px backstop unmeetable. The two forms are stored twice rather than case-folded at runtime, because folding Cyrillic needs `mbstring` and a fallback returning the string unchanged would ship a capitalised word mid-sentence on twenty pages without failing anything.

**3. The banner shows only during the closure; the structured data publishes it as soon as it is scheduled.** The plan says "today is within the inclusive range"; the UI contract's visibility rule (`date <= vacation_to`) would also render it in advance. Taking the plan's reading: a strip reading «Затворено» that appears in July for an August closure is a true sentence a scanning visitor reads as a false one — the word lands before the dates do. The structured data has no such problem, because the entry declares its own validity window, so Google is told early either way. **This is an owner question, not a settled fact** — if he wants a visible advance notice it is one comparison in `banner.php`. Flagged for 04-10.

**4. The slash-escaping guarantee is weaker than it was, and that is the finding.** The measurement says the output is unchanged. But under 5.2 the escaping was *structural* — the flag that disables it did not exist. Under 8.5 it is a *default*, and the flag exists. The T-02-11 protection now depends on nobody passing a second argument to the encoder. So no code changed, and instead the file now carries a standing prohibition: no encoding flag may ever be passed there. The plan-level grep asserting no 5.4+ JSON constant appears in that file has been promoted from a stylistic rule about a runtime that could not use them into the actual control.

**5. The fallback sentinel is emitted into the served page.** The plan requires dropped keys to be *detectable*, not merely survived — the `?v=0` discipline. A fallback renders identically to a success by design, so nothing short of a page-visible marker lets a remote check assert on it. `footer.php` now emits `<!-- torin-settings: fallback=... -->`, carrying key **names** only and never values. With no `settings.txt` on the server every key is listed, and that is the correct reading of the shipped state; the interesting case is a *short* list, meaning the file exists and one line in it is wrong.

**6. `src/settings.txt` is deliberately NOT added to `.gitignore`.** It looks like the obvious guard and it is the wrong one: `deploy-new.sh` builds its file list with a filesystem walk and ignores git entirely, so gitignoring the file would not stop a deploy from clobbering the owner's edits — it would only stop anyone from *seeing* that a local copy had been created. Left untracked and visible in `git status`, which is where the warning belongs.

**7. `settings.txt` lives inside the document root.** The whole point is that the owner reaches it in File Manager without being told a server path. It is denied over HTTP instead, and the path is computed from `includes/`'s own location so the root cutover needs no edit — one fewer thing for the cutover checklist to miss.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 — Bug] The reference parser in 04-RESEARCH C-5 is a fatal parse error on this runtime**
- **Found during:** Task 1
- **Issue:** The research code example tests the comment character with `$line{0}` — curly-brace string offset syntax. That syntax was **removed in PHP 8**, and 04-01 moved this tree to 8.5. Transcribed as written it is a compile-time parse error in a file required by `site-config.php`, which every page includes: all twenty pages blank, before any output, which is the exact white-screen class D4-23 exists to prevent — arriving through the file written to prevent it.
- **Fix:** `substr($torin_line, 0, 1) === '#'`, with the reason recorded at the line.
- **Files modified:** `src/includes/settings.php`
- **Verification:** the node harness extracts and exercises the shipped comment-skip behaviour (B4 passes); the syntax itself is confirmed by inspection against the PHP 8 removal, not by execution — no PHP binary here.
- **Committed in:** `8814ac3`

**2. [Rule 2 — Missing critical functionality] `hours_days` allowlist**
- **Found during:** Task 1
- **Issue:** Validating only `strlen($val) <= 20` makes the plan's own headline truth unreachable — the rendered page and the structured data could state different days.
- **Fix:** allowlist lookup against `torin_settings_day_map()`, in addition to the length bound.
- **Files modified:** `src/includes/settings.php`, `src/includes/site-config.php`, `src/includes/jsonld.php`
- **Verification:** node harness X1 — `Mo-Fr`/`mo-su` accepted, `Понеделник – Петък`/`Mo-We` rejected.
- **Committed in:** `8814ac3`

**3. [Rule 2 — Missing critical functionality] UTF-8 validity check on every value**
- **Found during:** Task 1
- **Issue:** cPanel File Manager will happily save a file in a legacy Cyrillic encoding. Those bytes reach `htmlspecialchars()` with a UTF-8 charset, which answers an invalid sequence with an **empty string** — so a mis-encoded message renders as nothing at all while every check reports the key present and valid.
- **Fix:** `preg_match('//u', $val) !== 1` rejects the value before any key-specific test.
- **Files modified:** `src/includes/settings.php`
- **Verification:** by construction and inspection; the empty-pattern-with-`u`-modifier idiom is the standard 5.2-safe UTF-8 test.
- **Committed in:** `8814ac3`

**4. [Rule 2 — Missing critical functionality] `checkdate()` on the closure dates**
- **Found during:** Task 1
- **Issue:** the ISO pattern accepts `2026-02-30` and `2026-13-01`. Either makes the banner's range comparison quietly meaningless.
- **Fix:** the pattern now captures, and `checkdate()` rejects dates that do not exist.
- **Files modified:** `src/includes/settings.php`
- **Verification:** node harness B8 — both impossible dates rejected.
- **Committed in:** `8814ac3`

**5. [Rule 2 — Missing critical functionality] `.holiday-banner` added to the dark-surface focus group in `base.css`**
- **Found during:** Task 2
- **Issue:** `base.css` was not in the plan's `files_modified`, but the UI contract requires the fourth dark surface to be named in that group. The Phase-2 CR-02 defect was caused by exactly this omission for `.callbar`: the surface was introduced, the group was not updated, and the consequence was invisible until someone pressed Tab.
- **Fix:** one selector added to the `(0,2,0)` group, with the reasoning and the 11.09:1 figure recorded beside it. The `(0,3,0)` button group is deliberately **not** touched — the strip has no `.btn--primary` and the comment says what to do if it ever gains one.
- **Files modified:** `src/css/base.css`
- **Verification:** measured — ring `#ffd84d`, 3px, offset 2px, 11.09:1 on the banner's own fill.
- **Committed in:** `cd98cef`

**6. [Rule 2 — Missing critical functionality] `scripts/probes/focus-rings.js` taught about the fourth dark surface**
- **Found during:** Task 2
- **Issue:** the probe hard-codes `.hero, .site-footer, .callbar` as the dark-surface set. A probe that does not know about a surface does not merely miss it — it measures a ring on that surface against the **white page** token, so a too-light ring would come back `PASS`. That is the same shape of silent wrong answer as CR-02 itself.
- **Fix:** `.holiday-banner` added to the `closest()` selector, in the same commit as the CSS rule it verifies.
- **Files modified:** `scripts/probes/focus-rings.js`
- **Verification:** the run reports `surface: "dark"` and compares against the dark tokens.
- **Committed in:** `cd98cef`

### Other deviations (not auto-fixes)

**7. Two files created that are not in the plan's `artifacts_this_phase_produces`.**
`scripts/settings-selftest.php` (the `tdd="true"` behaviour block in executable form, extended to cover Task 2's date gate and Task 3's entry counts) and `scripts/probes/holiday-banner.js` (without it the 180px backstop is not measurable at all, and it will be needed again at cutover). Both live under `scripts/`, so `deploy-new.sh` cannot reach either.

**8. `src/kontakti.html` is listed in `files_modified` but was NOT modified.**
It already read `$site['hours']`. The "repointing" the plan asks for happened by making that key derived, so the contact page now reads the composed value with no edit. Leaving the file untouched is also what keeps this plan clear of 04-05, which is parked at a checkpoint.

**9. `vacation_message` cap set to 120 code points rather than the plan action's 300 characters.** Reasoned in Decisions #2. Recorded here because the plan's Task 1 action text and its Task 2 action text disagree with each other, and this resolves in favour of Task 2 and the UI contract.

**10. The banner's visibility window follows the plan's wording rather than the UI contract's.** Reasoned in Decisions #3, and raised as an open owner question rather than recorded as settled.

**Total deviations:** 6 auto-fixed (1 × Rule 1, 5 × Rule 2) + 4 recorded judgement calls.

## Threat Flags

None. No new network endpoint, auth path or trust boundary was introduced. The one new file-read surface (`settings.txt`) is inside the plan's own threat register as T-04-29/30/34 and each mitigation is implemented: plain text never PHP (T-04-29), an HTTP deny block (T-04-30), and example-ships-real-file-never (T-04-34). T-04-31's mitigation — one parsed source read by every consumer — is implemented; **its proof is unrun** (see below). T-04-32 (injected markup via the banner message) is mitigated by escaping at output plus the code-point bound.

## Known Gaps

Recorded here for the orchestrator to file into `.planning/WINDOWS.md`. **This plan does not write that file.**

**G1 — `unrun-verify` · `src/includes/settings.php` · EVERY live gate in this plan is unrun.** Same single cause as 04-04's ledger entry 23, and closed by the same single action. `scripts/deploy-new.sh` is denied to subagents by the permission classifier (confirmed this session, with the `TORIN_CRED_FILE` worktree override in place), and no `php` binary exists on the build machine. Nothing in this plan reached the server. The unrun gates are:
1. A deliberately corrupted `settings.txt` on staging leaving all 20 pages at HTTP 200 with zero warnings, the corrupted key on its default and **every other key still applied**, with both served bodies recorded.
2. `settings.txt`, `.user.ini` and `php.fcgi` each returning 403 or 404 over HTTP. *The deny block is written and module-guarded but has never been exercised against a live Apache — and an `.htaccess` mistake in this tree has historically been a whole-subtree 500.*
3. Zero PHP warnings across the 20 served pages after the `site-config.php` / `jsonld.php` / `footer.php` changes.
4. Banner absent by default on the served homepage; present exactly once on two pages with a current closure; gone with an expired one; present on its end date.
5. The one-edit-three-locations proof, with after values to pair with the before values recorded above.
6. Three / four / three `openingHoursSpecification` entries on the served page.

**G2 — `unrun-verify` · `scripts/settings-selftest.php` · never executed, in either direction.** 22 assertions covering the behaviour block, the date gate and the structured-data entry counts. Authored against a machine with no PHP and no running Docker daemon, exactly as `scripts/upload-selftest.php` was in 04-03 (ledger entry 19). **A test that has never run is a specification, not a gate.** Run `php scripts/settings-selftest.php` the moment a PHP runtime exists; it is the fastest way to close most of G1's logic half without a deploy.

**G3 — `unrun-verify` · `src/includes/settings.php` · no PHP file in this plan has been syntax-checked.** `php -l` was not available. Four files gained or changed PHP (`settings.php`, `banner.php`, `site-config.php`, `jsonld.php`, `footer.php`) and a parse error in any of them takes down all twenty pages at once — the precise failure mode this plan exists to prevent, arriving from the other direction. **Run `php -l` on all five before or immediately after the first deploy, and deploy them together rather than one at a time.**

**G4 — `deviation` · `src/includes/banner.php` · the advance-notice question is open.** The strip does not appear until the closure starts (Decisions #3). The UI contract's phrasing would have shown it from the moment the dates were set. One comparison either way; it should be the owner's call and he has not been asked. Raise at the 04-10 owner checkpoint.

**G5 — `stub` · `src/includes/site-config.php` · the `notice` `[ASSUMED]` marker is still open.** WINDOWS entry 3 covers `hours`, `viber` and `notice`. **`hours` is now closed** by D4-25 and this plan; `viber` was removed in 04-04. `notice` remains: OWNER-QUESTIONS #8 asks whether the footer band should exist at all and has no answer. Its *content* is no longer unconfirmed — it is composed from the confirmed hours — but the existence question is untouched and the marker was deliberately left in place rather than promoted because it looked settled. Entry 3 should be narrowed, not closed.

**G6 — `unrun-verify` · `src/settings.txt.example` · the File Manager round-trip has never been performed.** The guide tells the owner to copy the example, rename it, and edit it in cPanel with UTF-8 encoding. Nobody has done this once. The realistic failure is the panel's editor writing a BOM or a non-UTF-8 encoding — the parser now rejects non-UTF-8 values per-key, so the failure mode is safe, but it would be silent and the owner would see his edit ignored with no explanation. One human doing the procedure once, with the guide open, closes both this and D5's human-judgment item.

## Requirements

`requirements-completed` is deliberately **empty**. OWNER-01 and OWNER-02 are implemented, but every live gate that would demonstrate them is unrun (G1). Marking them complete on the strength of static greps and a local render harness is the exact false green this project has shipped before. They close when G1 and G2 close.

## Self-Check: PASSED

Files claimed created, verified present:

```
FOUND: src/includes/settings.php
FOUND: src/includes/banner.php
FOUND: src/settings.txt.example
FOUND: docs/naruchnik-nastroyki.md
FOUND: scripts/settings-selftest.php
FOUND: scripts/probes/holiday-banner.js
CONFIRMED ABSENT: src/settings.txt
```

Commits claimed, verified in `git log`:

```
FOUND: 8814ac3  feat(04-06): owner-editable settings file with per-key fail-safe fallback
FOUND: cd98cef  feat(04-06): self-expiring closure strip, no script and no dismiss
FOUND: 688d8a0  feat(04-06): one hours value, four consumers, and a Bulgarian owner guide
```

No file deletions in any of the three commits (`git diff --diff-filter=D HEAD~3 HEAD` is empty).
