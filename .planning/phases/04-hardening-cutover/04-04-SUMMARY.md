---
phase: 04-hardening-cutover
plan: 04
subsystem: ui
tags: [cta, gdpr, privacy, analytics-disclosure, php-includes, content-truth]

requires:
  - phase: 04-02
    provides: kontakti.html — the contact page the five CTA slots now point at
  - phase: 04-03
    provides: the photo upload pipeline whose actual delete-after-send behaviour the new terms text describes
provides:
  - Five CTA slots (hero, repeated CTA, call bar, footer, category page) routing to kontakti.html under one label, «Пишете ни»
  - Removal of the chat-app affordance from the entire tree, gated on a summed per-file count reaching zero
  - Deletion of the 'viber' config key and ~100 lines of recorded failure history
  - Two new terms blocks (enquiry-form data handling, analytics disclosure) and one corrected retention block
  - Closure of the phantom cutover blocker as obsolete rather than deferred
affects: [cutover, analytics wiring, owner sign-off on published legal text]

actuals:
  tokens: 7120
  tasks: 2
  commits: 2

tech-stack:
  added: []
  patterns:
    - "Gate a removal on a summed per-file count, never on a hand-written file list"
    - "Couple a disclosure to the thing it discloses with a boxed in-file comment, so the two cannot ship apart"

key-files:
  created: []
  modified:
    - src/index.html
    - src/includes/footer.php
    - src/includes/category-page.php
    - src/includes/site-config.php
    - src/includes/rating-badge.php
    - src/css/components.css
    - src/uslovia.html
    - .planning/todos/completed/verify-viber-button-before-launch.md

key-decisions:
  - "The device-data block UI-SPEC §S11 sketched was deliberately NOT written — OWNER-QUESTIONS #27 is open and a plausible sentence would publish a commitment nobody agreed to"
  - "The analytics disclosure is coupled by a boxed comment to a tracker that is not yet in the tree; block and tracker ship together or neither ships"
  - "The inherited half of the retention sentence (what the shop does with its own records) was neither strengthened nor dropped — it is the owner's practice, not a property of this code"
  - "The callbar padding was left at --sp-sm: the shorter label would now fit with full --sp-lg padding, but changing it is a layout change and §C-7 specifies none"

requirements-completed: []

coverage:
  - id: D1
    description: "The chat-app affordance is gone from the entire tree"
    requirement: "CONTACT-06"
    verification:
      - kind: other
        ref: "grep -rc 'chat?number=' src/ | awk -F: '{s+=$2} END{...}' -> 0"
        status: pass
      - kind: other
        ref: "grep -rc \"site['viber']\" src/ summed -> 0"
        status: pass
    human_judgment: false
  - id: D2
    description: "Five CTA slots route to kontakti.html with five distinct data-slot values and the reused chat glyph"
    requirement: "CONTACT-06"
    verification:
      - kind: other
        ref: "grep -rho 'href=\"kontakti.html\"' across the 3 touched files | wc -l -> 5"
        status: pass
      - kind: other
        ref: "distinct data-slot values -> 5; torin_icon('chat') call sites -> 5; icons.php unchanged in diff"
        status: pass
    human_judgment: false
  - id: D3
    description: "«Пишете ни» fits on one line in the call bar at 360px and in the footer grid column"
    verification:
      - kind: automated_ui
        ref: "scripts/render-check.sh scripts/probes/svc-page.js https://torin.bg/new/index.html 360 640"
        status: unknown
    human_judgment: true
    rationale: "Requires a deploy to staging, which is denied to subagents by the permission classifier. Marked verification:backstop in the plan's own must_haves. Carried to the Task 3 checkpoint, which already requires the page to be live."
  - id: D4
    description: "The terms page states the actual handling of enquiry data and the actual analytics arrangement, one position per question"
    requirement: "CONTACT-06"
    verification:
      - kind: other
        ref: "grep -c 'изтрит' src/uslovia.html -> 1 before and after; 'от регистрите си' -> 0; Umami and ЕС/ЕИП each present"
        status: pass
      - kind: other
        ref: "curl https://torin.bg/new/uslovia.html — 200, zero PHP warnings, Cyrillic body"
        status: unknown
    human_judgment: true
    rationale: "Published legal text is a commitment the business is held to. No measurement can decide whether the shop is willing to stand behind a sentence; that is Task 3."

duration: ~3h50m wall clock across an interrupted session
completed: 2026-09-20
status: blocked
---

# Phase 04 Plan 04: CTA Retirement & Terms Truth Summary

**The chat button is gone from all five slots and every CTA now has a real destination; the terms page describes the enquiry pipeline that actually exists — but it is not signed off, and the live gates never ran.**

## Performance

- **Duration:** ~3h50m wall clock (15:02 → 18:55 +03:00), spanning a provider session interruption; actual working time was substantially less
- **Tasks:** 2 of 3 completed; Task 3 is a blocking human checkpoint, not started
- **Files modified:** 8

## Accomplishments

- **Five slots, not four.** The plan's own warning proved correct: the source artefacts said four CTA slots and there were five. All five now carry `href="kontakti.html"`, a distinct `data-slot`, the **reused** `torin_icon('chat')` glyph and the label «Пишете ни». No icon was added; `src/includes/icons.php` is untouched in the diff.
- **The count reached zero.** Gated on a summed per-file count rather than a file list, exactly as the plan required — a naive whole-tree occurrence count read 8 today because the config comments quoted the URL scheme three times, and could never have reached zero. The `'viber'` key went with ~100 lines of failure history.
- **The empty reserved form container is gone**, along with the two comments that still named it — leaving those would have left the literal in the tree and the count above zero.
- **The call-bar sizing comment is now a measurement, not a fossil.** Correcting it surfaced a live defect the old comment concealed (see Deviations).
- **The terms page states one position per question.** The retention sentence was corrected *in place* rather than answered a second time lower down, and the meta description — which carried the same contradiction — was corrected with it.
- **The phantom cutover blocker is closed as obsolete.** There is no button left to open on a handset.

## Task Commits

1. **Task 1: Five slots, one label, counted not listed** — `9c24538` (feat)
2. **Task 2: The terms page says what actually happens** — `501fd36` (docs)
3. **Task 3: Owner confirms every published data-handling sentence** — NOT RUN (blocking checkpoint)

## Files Created/Modified

- `src/index.html` — three CTA slots swapped (hero, repeated CTA, call bar); empty `.cta-block__form` container removed
- `src/includes/footer.php` — footer CTA slot swapped; this is the slot whose 233.6px grid column decided the label
- `src/includes/category-page.php` — category CTA slot swapped, on all 15 service pages
- `src/includes/site-config.php` — `'viber'` key and its failure-history block deleted; replaced by a short note saying the key is gone, not dormant
- `src/includes/rating-badge.php` — comment updated; it referenced the deleted container by name
- `src/css/components.css` — call-bar sizing comment re-measured for the shipping label; CTA-block comment rewritten
- `src/uslovia.html` — retention block corrected, two blocks added, meta description corrected, Phase-4 note discharged
- `.planning/todos/completed/verify-viber-button-before-launch.md` — moved from `pending/`, `status: obsolete`, reason stated in one line

## Decisions Made

**The device-data block was deliberately not written.** UI-SPEC §S11 sketched a block covering what happens to customer data on a device left for repair. `OWNER-QUESTIONS #27` is open and explicitly not a developer decision. Writing a plausible sentence would have published a commitment nobody at the shop agreed to — precisely the prohibition this plan carries and threat T-04-18. It is raised at the Task 3 checkpoint instead, where the owner is already reading the page.

**The analytics disclosure is coupled to a tracker that does not exist yet.** Recorded in-file with a boxed comment (see Deviations).

**The callbar padding was left alone.** «Пишете ни» would now fit at 360px even with the full `--sp-lg` padding restored (88.7 + 48 + 29.3 = 166.0px against 180px). Restoring it is tempting and was not done: §C-7 specifies *no layout change*, and widening a button inside a sticky bar on the strength of arithmetic — when the arithmetic in that very comment was wrong for months — is the mistake this plan exists to stop repeating.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] The call-bar comment concealed a live overflow, and correcting it required correcting the arithmetic, not just the string**

- **Found during:** Task 1
- **Issue:** The plan asked for the sizing comment to be updated to name the shipping label. Reading it against §Conflicts C-2 showed the comment was not merely stale but *wrong*: it measured the label alone (141px) and omitted `.btn svg { width: 1.25em }` (21.3px) and `.btn { gap: --sp-sm }` (8px). The retired button's true width was 186.7px against the 180px each half gets at 360px — it had been overflowing at 360px and below the whole time, saved from visible breakage only by `min-width: 0` letting the label wrap to two lines inside a 56px bar.
- **Fix:** Rewrote the comment as a full budget — label + padding + icon + gap = 134.0px against 180px at 360px, and 134px against 160px at 320px — and recorded the retired figure and why it misled, so the next editor measures a replacement label the same way.
- **Files modified:** `src/css/components.css`
- **Verification:** Arithmetic reproduces §Conflicts C-1's independently computed 134.4px figure to within 0.4px. **The rendered measurement did not run** — see Unrun Verifications.
- **Committed in:** `9c24538`

**2. [Rule 2 - Missing critical functionality] Two comments kept the deleted container's literal alive in the tree**

- **Found during:** Task 1
- **Issue:** The acceptance criterion requires `cta-block__form` to appear **zero** times anywhere under `src/`. The plan named only the node in `index.html`, but `src/includes/rating-badge.php:88` and `src/css/components.css:354` both referenced it by name. Removing only the node would have left the gate at 2 and left a reader believing the container still existed.
- **Fix:** Rewrote both comments to describe the structural discipline without naming the dead class. `rating-badge.php` now records that it is the last node relying on that discipline.
- **Files modified:** `src/includes/rating-badge.php`, `src/css/components.css`
- **Verification:** summed per-file count for `cta-block__form` across `src/` = **0**
- **Committed in:** `9c24538`

**3. [Rule 1 - Bug] The meta description contradicted the corrected retention block**

- **Found during:** Task 2
- **Issue:** The plan scoped the correction to the retention sentence in the blocks array. `$torin_desc` independently told the reader *when* the shop erases their data from its registers — asserting a register exists. A page whose meta description disagrees with its own body still carries two positions, which is the exact failure the "one position" criterion targets.
- **Fix:** Corrected the description to state that data is not stored on the server. Re-measured at **135 characters**, inside the 87–139 band Phase 3 established for this site.
- **Files modified:** `src/uslovia.html`
- **Verification:** `grep -c 'от регистрите си' src/uslovia.html` → 0
- **Committed in:** `501fd36`

**4. [Rule 1 - Bug] My own correction comment tripped the project's own grep convention**

- **Found during:** Task 2, caught by re-running the gate after editing
- **Issue:** The comment explaining the meta-description fix quoted the retired phrasing verbatim, so `grep -c 'от регистрите си'` returned **1** after the fix instead of 0 — the comment satisfied nothing and broke the gate. This is the convention 03-07 recorded: *a source comment must never quote a gated string verbatim.*
- **Fix:** Reworded the comment to describe the retired phrasing rather than reproduce it, and cited 03-07 so the next editor knows why.
- **Files modified:** `src/uslovia.html`
- **Verification:** count returned to 0
- **Committed in:** `501fd36`

---

**Total deviations:** 4 auto-fixed (2 × Rule 1 bug, 1 × Rule 1 self-inflicted and caught by re-running the gate, 1 × Rule 2 missing functionality)

## Unrun Verifications

**Every live gate in this plan is unrun.** `scripts/deploy-new.sh` is denied to subagents by the permission classifier (recorded in STATE.md as a Phase 3 finding and re-confirmed this session), and `php` is denied as well, so neither a staging deploy nor a local render of these PHP pages was possible from this agent. This is an environment boundary, not a defect in the work, and it is **not** a reason to treat these as passing.

| Gate | Command | Status |
|---|---|---|
| Homepage carries the new label 3× with zero PHP warnings | `B=$(curl -s https://torin.bg/new/index.html); printf '%s' "$B" \| grep -c 'Пишете ни'` → expect 3; warnings → expect 0 | **unrun** |
| No horizontal overflow at 360×640, and the measured call-bar button width | `scripts/render-check.sh scripts/probes/svc-page.js https://torin.bg/new/index.html 360 640` | **unrun** |
| Terms page returns 200 with zero PHP warnings | `curl -s -o /dev/null -w '%{http_code}' https://torin.bg/new/uslovia.html` | **unrun** |
| Terms page body is Cyrillic | `curl -s .../uslovia.html \| LC_ALL=C grep -c '[^ -~]'` → expect non-zero | **unrun** |
| Terms page names the processor | `curl -s .../uslovia.html \| grep -c 'Umami'` → expect non-zero | **unrun** |
| No new SEO metadata failure for this page | `node scripts/seo-metadata-check.js --live 2>&1 \| grep -qv 'uslovia.*FAIL'` | **unrun** |

**The acceptance criterion asking for the measured rendered call-bar button width alongside the available half-bar width cannot be satisfied from this agent.** The computed figures are 134.0px of button against 180px available at 360px; the *measured* figure is owed and the plan's own `must_haves` marks it `verification: backstop`. It must be taken at the Task 3 checkpoint, when the page is live.

**Deploy command for whoever runs the checkpoint** (must be run by the user; the worktree has no credentials file, hence the override):

```
TORIN_CRED_FILE=/Users/alabala/Documents/projects/torin/filezilla-server-data.xml \
  scripts/deploy-new.sh index.html includes/footer.php includes/category-page.php \
  includes/site-config.php includes/rating-badge.php css/components.css uslovia.html
```

Named paths, not a bare invocation: a no-argument `deploy-new.sh` uploads everything under `src/` (Research P-10), which during a parallel wave would publish plan 04-05's in-flight work too.

## Known Gaps

**1. The analytics disclosure names a processor the site does not yet load.** There is no `js/analytics.js` and no Umami script tag anywhere in `src/` as of this plan — verified by `grep -rn "umami" src/`, which returns nothing. The disclosure is correct *at cutover*, when the tracker is wired, and inaccurate in the other direction until then. Staging carries `X-Robots-Tag: noindex`, so nothing is publicly committed yet. A boxed comment at the block states the coupling and says to **delete the block** if analytics is dropped. **This must not reach production ahead of the tracker.**

**2. The device-data commitment is absent by choice.** `OWNER-QUESTIONS #27` is open. Recorded in the page's header comment so the gap is visible to the next reader rather than silently missing.

**3. Carried-forward ROADMAP item 3 residue is untouched, as the plan directed.** The unconfirmed symptoms line on `remont-na-portove.html` and the three owner questions behind it are deliberately not closed here. Raise at the Task 3 checkpoint and record in `OWNER_ANSWERS.md`.

**4. The warranty-term rider is still open.** The battery warranty term on `za-bateriite.html` and the one-month general term on `warrently.html` disagree (`OWNER-QUESTIONS #23`). Not this plan's to fix; folded into the Task 3 checkpoint because the owner is reading the legal pages anyway.

> **Note for the orchestrator:** items 1–4 plus the six unrun gates above belong in `.planning/WINDOWS.md`. They were **not** appended from this worktree: `WINDOWS.md` is a shared append-only JSON array outside this plan's `files_modified` set, and a concurrent append from plan 04-05 in the same wave would conflict. Please append post-merge.

## Verification Results

All source-level gates pass:

| Assertion | Result |
|---|---|
| Summed per-file count, chat URL scheme across `src/` | **0** |
| Summed per-file count, removed config key across `src/` | **0** |
| Summed per-file count, `cta-block__form` across `src/` | **0** |
| `href="kontakti.html"` across the three touched files | **5** |
| Distinct `data-slot` values | **5** (hero, cta, callbar, footer, category) |
| `torin_icon('chat')` call sites | **5** |
| `src/includes/icons.php` changed | **no** — absent from the diff, so no icon was added |
| Todo under `pending/` | **absent**; present under `completed/` with `status: obsolete` |
| `grep -c 'изтрит' src/uslovia.html` | **1 before, 1 after** — one retention statement, corrected in place |
| `grep -c 'от регистрите си' src/uslovia.html` | **0** |
| `Umami` / `ЕС/ЕИП` present in `src/uslovia.html` | **yes / yes** |
| Meta description length | **135 chars** (band: 87–139) |
| PHP 5.2 safety on `uslovia.html` | no short arrays, no short tags; quotes balanced (68, even), parens 21/21 |

**On the retention count:** the criterion asks for a grep whose number is unchanged before and after. The token measured is `изтрит` — the exact stem of the retention sentence — and it is **1** both before and after, because the sentence was corrected in place rather than duplicated. Reported honestly rather than gamed: the new photo sentence does use the related form `изтриват се`, a *different* and non-contradicting fact (photographs deleted after delivery, as opposed to enquiry records). The page states one position on enquiry retention and one on photographs, not two positions on either.

## Self-Check: PASSED

- All 7 modified source files exist on disk
- `.planning/todos/completed/verify-viber-button-before-launch.md` exists; the `pending/` copy does not
- Both commits exist in `git log d1b6d68..HEAD`: `9c24538`, `501fd36`
- Working tree clean at time of writing
- No file deletions in either commit (`git diff --diff-filter=D` empty for both; the todo move registered as a rename)

## CHECKPOINT PENDING

Task 3 is a `checkpoint:human-verify` with `gate="blocking"`. Auto mode is off (`auto_advance: false`, `_auto_chain_active: false`), so it was not auto-approved — and it is the checkpoint threat T-04-18 is mitigated by, which must not be rubber-stamped regardless. **This plan is `status: blocked` until the owner confirms every published data-handling sentence.** Flip to `complete` only after that sign-off and after the live gates above have actually been run.
