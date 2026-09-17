---
phase: 04-hardening-cutover
plan: 01
subsystem: infra
tags: [php, apache, htaccess, fastcgi, shared-hosting, probe, capability-detection, superhosting]

requires:
  - phase: 03.5-truth-audit
    provides: "The 19-page live-sweep discipline and the evidence rule that no check is trusted until it has been run against an independently-known answer"
  - phase: 01-foundation
    provides: "src/.htaccess, the AddHandler line that makes 19 .html pages execute as PHP, and scripts/deploy-new.sh"
provides:
  - "scripts/host-probe/probe.php.tpl — a 5.2-safe, token-gated capability probe answering all six RESEARCH 'probe' rows in one request"
  - "scripts/host-probe/run-probe.sh — --prepare / --read / --verify-gone lifecycle driver that records into 04-HOST-CAPABILITIES.md and proves the probe is gone"
  - "A gitignore rule (/src/hc-*.php) preventing a materialised probe instance from ever being committed"
affects: [04-02, 04-03, 04-05, 04-08, 04-10]

actuals:
  tokens: 3900
  tasks: 1
  commits: 1

tech-stack:
  added: []
  patterns:
    - "Throwaway diagnostic kept as a template under scripts/, materialised into src/ only for the length of an explicit-path upload"
    - "Cleanup checks authenticate: a 404 assertion on a token-gated file must send the token, or it cannot distinguish deleted from present"

key-files:
  created:
    - scripts/host-probe/probe.php.tpl
    - scripts/host-probe/run-probe.sh
  modified:
    - .gitignore

key-decisions:
  - "The probe template lives under scripts/, never src/ — deploy-new.sh with no arguments uploads every file beneath src/ (deploy-new.sh:161), and this body reports ini values, the extension list and filesystem paths. Structural fix for P-10, not a second cleanup pass like 03-09's deletion of src/phptest.html."
  - "--verify-gone fetches WITH the valid token. The probe answers a bare 404 to any tokenless request, so the obvious cleanup check cannot tell 'deleted' from 'still live' and would report success either way."
  - "The probe emits outbound:curl443 unconditionally, including a FAIL line when ext-curl is absent. RESEARCH C-1 prints it only inside the function_exists branch, which would let a blocked notification channel reach 04-HOST-CAPABILITIES.md as a MISSING line — and a missing line reads as an unrun probe, not as a measured answer."
  - "run-probe.sh prints the deploy command instead of invoking it. deploy-new.sh is denied to subagents by the permission classifier; an agent that shells out to it gets denied mid-run and leaves a materialised probe sitting in src/."

patterns-established:
  - "Probe lifecycle: --prepare (generate token + unguessable name, materialise) -> developer runs deploy-new.sh by explicit path -> --read (fetch, record verbatim, delete local copy) -> manual server delete -> --verify-gone (authenticated 404 assertion)"
  - "Evidence discipline carried from 03.5-TRUTH-AUDIT.md: 04-HOST-CAPABILITIES.md records the verbatim response body, the command that produced it, and the date — with the live token redacted from the recorded command"

requirements-completed: []

coverage:
  - id: D1
    description: "Host-capability probe template implementing RESEARCH C-1 field for field, PHP 5.2-safe so it measures the runtime both before and after the version change"
    requirement: CONTACT-05
    verification:
      - kind: other
        ref: "grep -cE '(=>|=)[[:space:]]*\\[|return[[:space:]]+\\[' scripts/host-probe/probe.php.tpl -> 0 (no short-array syntax)"
        status: pass
      - kind: other
        ref: "grep -cF for php_sapi_name( / extension_loaded( / api.telegram.org / /usr/sbin/sendmail -> 1 each"
        status: pass
    human_judgment: false
  - id: D2
    description: "run-probe.sh lifecycle driver: token generation, materialisation into a gitignored src/ path, deploy-command hand-off, verbatim recording, local cleanup, authenticated 404 re-check"
    verification:
      - kind: other
        ref: "bash -n scripts/host-probe/run-probe.sh -> SYNTAX OK"
        status: pass
      - kind: integration
        ref: "scripts/host-probe/run-probe.sh --prepare (rehearsal run) -> materialised src/hc-<32hex>.php, token substituted (0 placeholders remaining), git status --untracked-files=all src/ -> empty (gitignored). Instance removed afterwards."
        status: pass
    human_judgment: false
  - id: D3
    description: "04-HOST-CAPABILITIES.md — the measured record of every host unknown the phase is gated on"
    verification: []
    human_judgment: true
    rationale: "NOT PRODUCED. Requires a live deploy of the probe, and scripts/deploy-new.sh is denied to subagents by the permission classifier (STATE.md, Phase 3). No local PHP interpreter exists in this project, so there is no substitute measurement. Blocked at the Task 1 checkpoint."
  - id: D4
    description: "src/.htaccess handler replacement and the 19-page served-output sweep (Task 3)"
    verification: []
    human_judgment: true
    rationale: "NOT STARTED. Depends on Task 2's cPanel PHP-version switch and on the measured SAPI name from D3."

duration: 25min
completed: 2026-09-17
status: in-progress
---

# Phase 04 Plan 01: Host Capability Probe & PHP Runtime Upgrade — Summary

**Token-gated, 5.2-safe capability probe plus its full lifecycle driver, deliberately kept outside `src/` so no no-argument deploy can publish it — authored and committed; the measurement itself, the cPanel PHP switch and the `.htaccess` handler replacement are blocked at a human gate.**

## Status: INCOMPLETE — stopped at a blocking human action

1 of 3 tasks reached a committable state. The plan is `autonomous: false` and the
blocker is structural, not a failure:

- **Task 1 — partially complete.** The tooling is authored, verified and committed.
  `04-HOST-CAPABILITIES.md` does **not** exist, because producing it requires deploying
  the probe, and `scripts/deploy-new.sh` is denied to subagents by the permission
  classifier (STATE.md, Phase 3 note, plan 03-01). This project has **no local PHP
  interpreter** — a structural property — so the deploy is the only PHP check that
  exists and there is no substitute measurement.
- **Task 2 — not started.** The cPanel PHP-version switch is a control-panel operation
  with no CLI or API path on this account.
- **Task 3 — not started.** Its `<precondition>` (Task 2 applied, panel-generated
  filenames known) is unmet, and its edit depends on the SAPI name Task 1 would have
  measured.

**No fabricated record was written.** A skeleton `04-HOST-CAPABILITIES.md` with
`sapi : PENDING` placeholders would satisfy this plan's own grep gates while being
false, which is precisely the failure class 03.5 spent a phase eliminating. The file is
absent rather than wrong.

## Performance

- **Duration:** ~25 min
- **Completed:** 2026-09-17T19:25:29Z
- **Tasks:** 1 of 3 (partial)
- **Files modified:** 3

## Accomplishments

- **One request now answers every host unknown the phase is gated on.**
  `scripts/host-probe/probe.php.tpl` implements RESEARCH C-1 field for field: PHP
  version, SAPI name, nine extensions (gd, exif, fileinfo, curl, openssl, mbstring,
  hash, ctype, filter), twelve ini values including `user_ini.filename` and
  `user_ini.cache_ttl`, an outbound cURL GET to `api.telegram.org` with a 10s timeout,
  `is_executable('/usr/sbin/sendmail')`, and an `fsockopen` to localhost:25.
- **The probe is PHP 5.2-safe on purpose.** It has to run both before and after the
  runtime change — measuring the old runtime is half its job — so `array()` not `[]`,
  no closures, no short echo tags, matching the tree-wide rule at
  `src/includes/site-config.php:2`.
- **The disclosure risk is closed structurally, not procedurally.** The template lives
  under `scripts/`, so a no-argument `deploy-new.sh` run (which uploads every file under
  `src/` — `deploy-new.sh:161`) cannot reach it. `03-09` deleted `src/phptest.html` for
  exactly this reason; this is the fix that stops the next one happening.
- **`/src/hc-*.php` is gitignored**, so the materialised instance — which carries a live
  access token — cannot be committed even by accident. Verified against a real rehearsal
  run: `git status --untracked-files=all src/` returned empty.

## Task Commits

1. **Task 1 (partial): probe template, lifecycle driver, gitignore rule** — `b876790` (feat)

**Plan metadata:** committed with this SUMMARY.

## Files Created/Modified

- `scripts/host-probe/probe.php.tpl` — the token-gated capability probe, 5.2-safe, RESEARCH C-1 field for field
- `scripts/host-probe/run-probe.sh` — `--prepare` / `--read` / `--verify-gone`; generates the token and an unguessable filename, materialises into `src/`, prints the deploy command for the developer, records the verbatim body into `04-HOST-CAPABILITIES.md`, deletes the local copy, and asserts an authenticated 404
- `.gitignore` — added `/src/hc-*.php` with the rationale inline

## Decisions Made

1. **Filename prefix is `hc-`, not `probe-`.** The literal string "probe" is absent from
   the deployed name so a directory guess against the obvious word finds nothing. The
   32 hex characters are what make it unguessable; the prefix only has to be gitignorable.
2. **`--verify-gone` requires the token.** See Deviations #2 — this was the single most
   consequential design call in the plan.
3. **`run-probe.sh` prints the deploy command rather than running it.** Not politeness:
   an agent that shells out to `deploy-new.sh` gets denied mid-run and leaves a
   materialised probe sitting in `src/`, which is the exact disclosure the design
   prevents.
4. **The token is redacted from the command recorded in `04-HOST-CAPABILITIES.md`.** It
   gates a file that will no longer exist, but writing a live credential into a
   committed artefact is a habit worth not having.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] The plan's second Task 1 verify command fabricates a failure on a clean tree**

- **Found during:** Task 1, running the gate rather than reading it
- **Issue:** `! grep -rqi 'probe' src/ --include='*.html' --include='*.php'` is intended
  to assert that no probe *file* exists under `src/`. Run against the current, clean
  tree it **fails**, because three pre-existing source comments contain the word:
  `src/index.html:262` ("If a probe ever reports a non-zero adjacent-tinted-pair
  count"), `src/includes/header.php:113` ("scripts/probes/font-swap.js"), and
  `src/includes/site-config.php:109` ("Every Phase 2 probe confirmed the href"). The
  check is a content grep standing in for a filename assertion — the same
  substring-versus-element trap that made a brand-row count return 8 against a true 7
  (STATE.md, Phase 3.5).
- **Fix:** Use the filename form, which is also what the task's own acceptance criterion
  actually states ("No file matching `probe*` exists anywhere under `src/`"):
  `test -z "$(find src -name 'probe*' -o -name 'hc-*.php')"`
- **Files modified:** none (the defect is in the plan's gate, not in the tree)
- **Verification:** corrected command run — returns empty, passes. The original command
  run — returns three files, fails. Both run, neither read.
- **Committed in:** recorded here; no code change was required

**2. [Rule 2 - Missing Critical] The 404 cleanup check could not distinguish "deleted" from "still live"**

- **Found during:** Task 1, designing `--verify-gone`
- **Issue:** The plan's acceptance criterion is
  `curl -s -o /dev/null -w '%{http_code}' https://torin.bg/new/<probe-filename>` → `404`.
  But the probe answers a **bare 404 to every request without the correct token** —
  that is its access control. A tokenless fetch therefore returns 404 whether the file
  was deleted or is sitting there fully readable. The check passes in both states, which
  makes it worse than no check: it certifies cleanup that may not have happened, on the
  one file in this phase whose continued existence is a live environment disclosure
  (T-04-01).
- **Fix:** `--verify-gone <filename> <token>` fetches **with** the valid token. Only a
  genuinely absent file 404s then. The reasoning is written into the script header and
  into the recorded evidence block in `04-HOST-CAPABILITIES.md`, so a future reader
  cannot "simplify" the token away.
- **Files modified:** `scripts/host-probe/run-probe.sh`
- **Verification:** logic verified by construction; the live assertion runs after the
  developer deletes the file on the server
- **Committed in:** `b876790`

**3. [Rule 2 - Missing Critical] `outbound:curl443` was emitted only when cURL exists**

- **Found during:** Task 1, transcribing RESEARCH C-1
- **Issue:** C-1 wraps the outbound-443 measurement in `if (function_exists('curl_init'))`
  and prints the `outbound:curl443` line only inside it. If `ext-curl` is absent — a real
  possibility on budget shared hosting, and exactly what D4-06 exists to rule out — the
  line is simply missing from the response. A missing line in `04-HOST-CAPABILITIES.md`
  reads as an unrun probe, not as a measured "no", and this plan's own verify greps for
  `outbound:curl443` would fail without telling anyone why. The blocked notification
  channel that decides D4-05 would be the least legible fact in the file.
- **Fix:** added an `else` branch emitting `outbound:curl443     : FAIL ext-curl not loaded`.
  The row is now always present and always answered.
- **Files modified:** `scripts/host-probe/probe.php.tpl`
- **Verification:** both branches emit the same left-hand label; the grep gate now holds
  in either environment
- **Committed in:** `b876790`

**4. [Rule 3 - Blocking] `grep -q ... && die` aborts the success path under `set -e`**

- **Found during:** Task 1, `bash -n` plus a rehearsal `--prepare` run
- **Issue:** The token-substitution guard was first written as
  `grep -q PLACEHOLDER "$TARGET" && die "..."`. Under `set -euo pipefail` an AND-list
  whose left side fails is itself a failing command, so the **good** outcome (no
  placeholder left) would have aborted the script immediately after writing the probe —
  leaving a correctly-gated file in `src/` and no instructions printed.
- **Fix:** rewritten as an `if` block, which additionally `rm`s the target before dying
  so a failed substitution never leaves an ungated probe in `src/`.
- **Files modified:** `scripts/host-probe/run-probe.sh`
- **Verification:** `bash -n` clean; rehearsal `--prepare` run completed and printed both
  follow-up commands
- **Committed in:** `b876790`

---

**Total deviations:** 4 (1 defective gate corrected, 2 missing-critical additions, 1 blocking bug)
**Impact on plan:** No scope change. Two of the four (#2, #3) close holes that would have
let this plan report success while leaving an environment disclosure live or an unmeasured
dependency unrecorded — both are correctness requirements for a plan whose entire product
is trustworthy measurement.

## Issues Encountered

**The plan's own `<precondition>` on Task 1 is the blocker, and it is not resolvable by an
executor.** `scripts/deploy-new.sh` is denied to subagents; the developer must run it via
`!`. Because no local PHP interpreter exists, there is no way to produce a measured
`04-HOST-CAPABILITIES.md` without that deploy. The tooling is therefore built to hand the
one un-automatable step to a human with the exact command pre-filled, and to do everything
either side of it.

**Secondary, worth knowing before the next attempt:** `deploy-new.sh` resolves upload paths
relative to `src/` and refuses any argument that is absolute or contains `..`
(`deploy-new.sh:~170`). A probe living under `scripts/` therefore **cannot** be deployed
directly — hence the materialise-into-`src/`-then-delete design rather than a straight
upload from `scripts/host-probe/`. Also note the credentials file is gitignored and exists
only in the primary checkout, so the whole probe lifecycle must be run from there, not from
a worktree (`TORIN_CRED_FILE` exists as an override but points at a secret path).

## Known Stubs

None. No placeholder, mock or hardcoded value was written. `04-HOST-CAPABILITIES.md` is
absent rather than stubbed — deliberately, per the plan's prohibition that no unconfirmed
host value may be recorded as measured fact.

## Threat Flags

None beyond the plan's own register. T-04-01 (probe disclosure) is mitigated as designed
and additionally hardened by deviations #2 and #4.

## User Setup Required

**Yes — three sequential human steps, all recorded in the checkpoint returned with this
summary.** In order, from the primary checkout (not a worktree):

1. `scripts/host-probe/run-probe.sh --prepare`, then the `scripts/deploy-new.sh <file>`
   command it prints, then the `--read` command it prints.
2. Delete the probe from `public_html/new/` via cPanel File Manager or FileZilla, then
   `scripts/host-probe/run-probe.sh --verify-gone <file> <token>`.
3. cPanel → Select PHP Version / MultiPHP Manager → set the newest stable version **for
   `public_html/new/` only** (Task 2). **Do not delete the existing `AddHandler` line the
   panel tells you to delete** — it is why all 19 pages execute; Task 3 replaces it
   deliberately.

## Next Phase Readiness

**Not ready.** 04-02 is the phase tracer and RESEARCH §Summary is explicit that it cannot
be written before this plan's measurement exists: the notification channel (D4-05/D4-06),
the upload limits (D4-13) and the mail transport (D4-11) are all decided by facts only the
probe produces. 04-03, 04-05 and 04-08 inherit the same dependency.

Resume by re-running `/gsd-execute-phase` for 04-01 once the three steps above are done and
the probe output has been reported back.

---
*Phase: 04-hardening-cutover*
*Plan: 01 — INCOMPLETE, blocked at human action*
*Date: 2026-09-17*
