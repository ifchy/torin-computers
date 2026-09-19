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
  - "scripts/host-probe/handler-sweep.sh — the 4-assertion, body-reading acceptance gate for the handler cutover, re-runnable at rollback and at the root cutover via --base"
  - "src/.htaccess — the PHP 8.5 fcgid handler block covering .php/.html/.htm, with a !mod_fcgid fail-safe that makes raw-source disclosure structurally impossible (COMMITTED, NOT YET DEPLOYED)"
  - "04-HOST-CAPABILITIES.md — now also carries the cPanel 8.5 record, the 8.5 ini values, and the D4-04 variant finding"
affects: [04-02, 04-03, 04-05, 04-08, 04-10]

actuals:
  tokens: 37000
  tasks: 3
  commits: 5

tech-stack:
  added: []
  patterns:
    - "Throwaway diagnostic kept as a template under scripts/, materialised into src/ only for the length of an explicit-path upload"
    - "Cleanup checks authenticate: a 404 assertion on a token-gated file must send the token, or it cannot distinguish deleted from present"

key-files:
  created:
    - scripts/host-probe/probe.php.tpl
    - scripts/host-probe/run-probe.sh
    - scripts/host-probe/handler-sweep.sh
    - .planning/phases/04-hardening-cutover/deferred-items.md
  modified:
    - .gitignore
    - src/.htaccess
    - .planning/phases/04-hardening-cutover/04-HOST-CAPABILITIES.md

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
    requirement: CONTACT-05
    verification:
      - kind: integration
        ref: "Probe deployed, read and verified gone by the orchestrator (commit 332960f). All six RESEARCH 'probe' rows answered from a live response body on PHP 5.2.17."
        status: pass
    human_judgment: false
  - id: D5
    description: "Task 2 — cPanel PHP 8.5 selection, recorded and independently verified rather than re-performed"
    verification:
      - kind: integration
        ref: "curl -sS -D - https://torin.bg/new/index.html -> 200, x-powered-by: PHP/5.2.17 — proves the old php52 handler is still present AND routing, which is report line 5 verified more strongly than reading the file would"
        status: pass
      - kind: integration
        ref: "curl -sS -D - https://torin.bg/new/includes/site-config.php -> 200, x-powered-by: PHP/5.2.17 — FINDING 1: the 8.5 selection governs nothing yet"
        status: pass
      - kind: other
        ref: "php.fcgi + php85-fcgi.ini fetched directly (both 200) — confirms the panel-generated filenames and the absolute wrapper path without taking them on report"
        status: pass
    human_judgment: false
  - id: D4
    description: "src/.htaccess handler replacement (Task 3) — repo-side edit and its acceptance gate"
    verification:
      - kind: other
        ref: "grep -v '^[[:space:]]*#' src/.htaccess | grep -c php_value -> 0"
        status: pass
      - kind: other
        ref: "line 86's AddHandler application/x-httpd-php52 replaced and the fcgid block added in ONE commit (0c74370) — no revision exists in which .html matches no handler"
        status: pass
      - kind: integration
        ref: "handler-sweep.sh run against the live subtree in its unfixed state -> FAIL 19/19 on STILL-ON-5.2, with checks 1-3 passing on all 19. The gate is proven able to detect the failure it exists to detect."
        status: pass
    human_judgment: false
  - id: D6
    description: "Task 3 DEPLOYED and verified — 19 pages executing on PHP 8.5 with no source leakage"
    verification: []
    human_judgment: true
    rationale: "NOT DONE. scripts/deploy-new.sh is refused by the permission classifier on any real upload, so the committed .htaccess is not on the server and the subtree is still on 5.2.17. Recorded as an unmeasured gap in 04-HOST-CAPABILITIES.md rather than claimed. Returned as a checkpoint with the exact staged deploy, assertions and rollback."
  - id: D7
    description: "Post-switch re-measurement of extensions and ini values on the 8.5 runtime"
    verification: []
    human_judgment: true
    rationale: "NOT DONE, and blocked behind D6 — a probe deployed before the handler cutover would itself be measuring 5.2. Tooling is ready and now self-cleaning: the probe unlinks itself after one request (feac63b), so the re-measure no longer ends on a human remembering to delete a live file. The 8.5 ini values were read out of the publicly-readable php85-fcgi.ini in the meantime and are labelled CONFIGURED, not in effect."

duration: 25min + 45min (continuation)
completed: 2026-09-19
status: in-progress
---

# Phase 04 Plan 01: Host Capability Probe & PHP Runtime Upgrade — Summary

**All three tasks are authored, verified as far as they can be without a deploy, and committed: the host is measured on 5.2, the cPanel 8.5 switch is recorded and independently verified, and `src/.htaccess` now carries the fcgid handler block with a fail-safe that makes raw-source disclosure structurally impossible — but the edit is NOT on the server, so the subtree is still running PHP 5.2.17.**

## Status: INCOMPLETE — repo-side complete, blocked on a deploy

**Continuation run, 2026-09-19.** Tasks 2 and 3 were executed in this session on top of
the prior executor's Task 1. Task 1's original narrative is preserved below unchanged.

- **Task 1 — complete.** Tooling committed by the prior executor (`b876790`); the
  measurement itself was completed by the orchestrator (`332960f`), which deployed the
  probe, read it, deleted it and verified the 404 with a valid token.
- **Task 2 — complete.** The human performed the cPanel action; this session recorded it
  and verified every externally-observable part of it rather than taking it on report.
  Committed `d17c851`.
- **Task 3 — repo-side complete, deploy blocked.** `src/.htaccess` is committed with line
  86 replaced and the fcgid block added in one commit (`0c74370`), and its acceptance gate
  is committed and proven able to fail (`5d2b7e6`). `scripts/deploy-new.sh` is refused by
  the permission classifier on any real upload, so none of it is live.

**The single most important fact in this summary:** the site is still on PHP 5.2.17. Every
`.html` page renders perfectly, returns 200, leaks no source and throws no warnings — and
is running an interpreter unpatched since 2011. Nothing here should be read as "the
upgrade happened".

### The original Task 1 record (prior session, unchanged)

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

# Continuation session — 2026-09-19 (Tasks 2 and 3)

## Task 2 — the cPanel switch, recorded and verified rather than re-performed

The human performed the control-panel action. This session's job was to confirm it, and
everything checkable from outside was checked from outside.

**Reported:** PHP **8.5**, scoped to `public_html/new/` only (root untouched, D4-03 held);
panel generated `php.fcgi` and `php85-fcgi.ini`; wrapper at
`/home/torin/public_html/new/php.fcgi`; the old `AddHandler` line still present.

**Verified independently:** `x-powered-by: PHP/5.2.17` on `index.html` and `msg.html`
proves the php52 handler is not merely present but still routing — a stronger check than
reading the file would have been. Both panel-generated files were fetched directly,
confirming their names and the absolute wrapper path without relying on the report.

### FINDING 1 — the 8.5 selection currently governs nothing

`.php` also answers `x-powered-by: PHP/5.2.17`. The account default is unchanged and the
per-directory selection has taken effect on no extension at all. The wrapper exists on
disk; nothing routes to it.

This removes an intermediate safety state the phase implicitly assumed. There is no window
in which 8.5 has been proven on `.php` before 19 `.html` pages are bet on it — unless the
deploy is deliberately staged to create one, which is why the returned checkpoint is
staged.

### FINDING 2 — D4-04 did not fire as predicted, and the variant is worse at cutover

D4-04 predicted cPanel would write its own handler block and conflict with ours. It did
not. The PHP manager **instructed the human to add one by hand** instead. The panel
delegates the edit rather than making it.

Better in one way: no auto-generated block races ours. Worse in the way that matters at
04-10: **the snippet it hands you covers `.php` only.** Paste it verbatim, then follow the
host's other instruction to remove the existing `AddHandler` line, and every `.html` page
is left matching no handler and is served as source. The panel hands you the shape of the
failure and trusts you to notice the extension list. The root directory will present the
same instruction with a different absolute wrapper path.

### FINDING 3 — the panel-generated files are publicly readable

`php.fcgi` (200, 214 B) discloses the account home path, the panel's ini-scan directory and
the exact PHP build path. `php85-fcgi.ini` (200, 44 KB) is the full configuration dump.
Deferred deliberately rather than fixed here — see Deviations #3.

### FINDING 4 — D4-13's upload ceiling has already disappeared

Read out of the public `php85-fcgi.ini`: `upload_max_filesize=250M`, `post_max_size=200M`,
`memory_limit=256M`, `max_input_vars=5000`, `max_execution_time=600` — against the 5.2
values in effect today (`2M`/`8M`/`128M`/`1000`/`30`).

**04-03 no longer needs `.user.ini` work or a panel round-trip for limits.** D4-13's 5
photos x 10 MB fits inside 250M/200M with room to spare. If a limit ever does need
changing on this host, the lever is editing `php85-fcgi.ini` directly — the wrapper passes
it with `-c`, so it is authoritative, it lives in a directory we already deploy to, and it
has no `user_ini.cache_ttl` window to wait out. A strictly better lever than the plan
anticipated.

Every one of those values is labelled **CONFIGURED, not in effect** in
`04-HOST-CAPABILITIES.md`, because nothing routes to that ini until the deploy happens.

Also flagged there: **`display_errors = On`** in the 8.5 ini. A production defect — any
warning renders into the page for visitors, filesystem paths included — to be turned off
before the root cutover (04-10). Deliberately left on for now: it is what lets the sweep
see a broken page instead of a silently empty one.

## Task 3 — the handler cutover, committed but not deployed

`src/.htaccess:86` (`AddHandler application/x-httpd-php52 .html .htm`) is replaced, and the
fcgid block added, in **one commit** — `0c74370`. No revision of this file exists in which
`.html` matches no handler.

The block maps `.php`, `.html` and `.htm` to `fcgid-script` with three `FcgidWrapper`
directives (the directive takes one suffix each; there is no list form), wrapped in
`<IfModule mod_fcgid.c>` per the file's module-guard rule. Its comment records what the
surrounding blocks record: which panel action generated the wrapper path, that the host's
documentation instructs removal of the old line and why following that literally serves raw
source, and that the path is absolute and therefore silently invalidated by the `/new/` ->
root move.

### Verification is the task, and the plan's own gate was not sufficient

The plan's sweep asserted status 200, zero literal PHP open tags, and zero warning/fatal
strings. `scripts/host-probe/handler-sweep.sh` implements those and **adds a fourth**:
`x-powered-by` must not report `PHP/5.2`.

That addition is not defensive padding — it was measured. Run against the live subtree in
its current, definitionally-failed state (edit committed, not deployed, everything on
5.2.17):

```
FAIL — 19 of 19 page(s) failed at least one assertion.   EXIT=1
```

**and every one of those 19 pages passed checks 1, 2 and 3.** 200, no source, no warnings —
in a state where the upgrade had not happened at all. Shipped with only the plan's three
assertions, this gate would have reported a clean 19/19 PASS against a completely unchanged
server. It was run against a known-bad state before being trusted, because a gate that has
only ever been run against the state it is meant to bless has not been tested.

## Task Commits

| # | Task | Commit | Type |
|---|---|---|---|
| 1 | Task 1: probe template, lifecycle driver, gitignore (prior session) | `b876790` | feat |
| 2 | Task 1 measurement: probe deployed, read, deleted, 404 verified (orchestrator) | `332960f` | feat |
| 3 | Task 2: cPanel 8.5 selection recorded and independently verified | `d17c851` | docs |
| 4 | Probe made self-deleting; `--read` reduced to a single fetch | `feac63b` | fix |
| 5 | Task 3: `.html`/`.htm` moved onto the 8.5 fcgid handler | `0c74370` | feat |
| 6 | Handler sweep added, and proven to fail on the unfixed state | `5d2b7e6` | test |

## Deviations from Plan — continuation session

### 1. [Rule 2 - Missing Critical] The committed `.htaccess` keeps a php52 fallback, against an explicit acceptance criterion

- **Criterion deviated from:** "`src/.htaccess` contains zero non-comment occurrences of
  `x-httpd-php52`". The file contains exactly one, inside `<IfModule !mod_fcgid.c>`.
- **Why:** an `<IfModule>` guard protects against a 500 from an absent module, but here it
  does the opposite of protecting — if `mod_fcgid` is absent the guarded block is skipped,
  `.html` matches **no handler at all**, and 19 pages are served as readable PHP source.
  That is T-04-03, the highest-severity outcome in this plan's own threat register, and the
  criterion as written makes it reachable. The fallback fires only in that broken-config
  state and turns the worst case into "still on 5.2" — where the site already was, so it
  forfeits nothing.
- **Risk it introduces, and how it is closed:** a fallback that quietly keeps 5.2 alive is
  exactly the kind of green check this project has already shipped once. That is why the
  sweep's fourth assertion exists, and why it was tested against a bad state before being
  trusted. The fallback cannot pass as success.
- **Lifetime:** flagged in the file itself for deletion at 04-10 once 8.5 is proven in
  production. A transition guard, not a feature.

### 2. [Rule 2 - Missing Critical] The probe now deletes itself

- **Issue:** nothing in this repo can delete a remote file, so the probe's cleanup depended
  on a human remembering — for the one file in this phase whose continued existence is a
  live environment disclosure (T-04-01). That is not a mitigated disclosure, it is a
  scheduled one, and it is what blocked the previous session.
- **Fix:** `@unlink(__FILE__)` as the probe's final act, with the result **echoed** rather
  than assumed so a failed unlink arrives with the data. `--read` consequently had to drop
  from two fetches to one — against a self-deleting probe the second fetch 404s and the
  measurement is lost. (Two fetches were always two observations reported as one anyway.)
- **Commit:** `feac63b`

### 3. [Rule 4 - Deferred, not applied] The `<FilesMatch>` denial for the panel files

Written, then deliberately pulled back out. Both authorization syntaxes (`Require all
denied` on 2.4, `Order`/`Deny` on 2.2) need an `AllowOverride` grant this host has not been
observed to give — the file's existing directives only prove `FileInfo`. An `<IfModule>`
guard cannot cover that: the module is present, the override permission is what would be
missing, so a wrong guess is a 500 for the whole subtree. Bundling an untested authz
directive into the one commit that decides whether 19 pages execute would make a failure
ambiguous between two causes. Logged in `deferred-items.md` with the exact fix.

### 4. [Rule 1 - Corrected] Two inference-shaped claims caught in my own writing

While recording Task 2 I wrote "`max_input_vars` `1000` (absent — 5.3+ feature)" and
"`user_ini.filename` measured empty, correctly — a 5.3+ feature". Both were explanations
dressed as measurements: the 5.2.17 probe actually **returned** `'1000'` and `''`, not the
`false` an unknown directive yields. Corrected to record what the response body said, with
the discrepancy against the documented feature history written down rather than quietly
reconciled. The file's own rule is that anything not read out of a response body is an
assumption and must be labelled one — that rule has to bind the person writing the file.

### 5. [Correction] The "deploy-new.sh is denied to subagents" note was imprecise

Measured this session: it runs as far as credential resolution and the upload loop, and is
denied **at the upload**. Both observations are now recorded in `run-probe.sh`'s header
rather than one overwriting the other. The hand-off is kept regardless, for the reason that
does not depend on the classifier: a mid-run denial leaves a materialised probe in `src/`.

## Known Stubs

None. No placeholder or fabricated value was written. The undeployed state is recorded as
an unmeasured gap with the assertions it must satisfy, not as a provisional result.

## What is NOT done, precisely

1. **`src/.htaccess` is not on the server.** The subtree runs PHP 5.2.17.
2. **The 19-page sweep has not passed.** It has only been run as a negative control.
3. **No post-switch re-measure exists.** `gd`, `exif`, `fileinfo`, `curl` and `openssl` are
   confirmed on **5.2 only**. They are load-bearing for 04-02/04-03/04-05 and must be
   re-confirmed on 8.5. The tooling is ready and self-cleaning; it cannot run usefully
   until the handler cutover lands, since a probe deployed before it measures 5.2 again.

## Next Phase Readiness

**Partially ready, and better than before.** 04-03's hardest open question — the 2M upload
ceiling — is answered and dissolved by FINDING 4, from values read out of a public file
rather than assumed. 04-02, 04-05 and 04-08 still depend on the 8.5 extension set, which
needs the deploy first.

---

# Stage A executed — mechanism proved, Step B disqualified (2026-09-19)

The orchestrator ran Stage A. **The handler mechanism works:** `mod_fcgid` is present,
`FcgidWrapper` is permitted in `.htaccess` on this host, the absolute wrapper path is
correct, `.php` answers `x-powered-by: PHP/8.5.10`, and all 19 `.html` pages stayed on the
known-good 5.2 handler with no 500s and no raw source. Staging the deploy did exactly what
it was designed to do.

**Then the probe disqualified Step B.** Six of nine extensions are absent on the 8.5 build
— `gd`, `exif`, `fileinfo`, `curl`, `mbstring`, `ctype`. Only `openssl`, `hash` and
`filter` survive. Selecting a PHP version and enabling its extensions are two separate
panel operations; only the first was done.

**Hard blocks:** `curl` -> 04-02 (tracer) and 04-05 (Telegram, D4-05); `gd` and `exif` ->
04-03's photo pipeline; `fileinfo` -> upload MIME validation, which is a security control
rather than a nicety. `ctype` is low (its uses are covered by `filter`, present).

**Correction issued to the working assumption about `mbstring`.** It was proposed as the
reason Step B was unsafe on its own. Measured instead of argued: **no file in `src/` calls
any `mb_*` function**, and the tree's one escaping function, `htmlspecialchars()`, lives in
`ext-standard`. The Bulgarian pages do not depend on mbstring. The hold is still correct —
but `curl`/`gd`/`exif`/`fileinfo` are the reason, and getting that right matters, because
holding 19 pages on an unpatched-since-2011 interpreter to wait for an extension nothing
uses would be the wrong trade.

## Second measured gate weakness — a scope failure, not a blind spot

The `x-powered-by` finding was a gate that could not *see* a bad state. This one is
different, and the distinction is the lesson.

`handler-sweep.sh` is **not** blind here: a page calling a missing function would fatal,
and with `display_errors = On` check 3 catches it. But its **scope** only ever asks "do
today's pages still render?" — and since no current page touches the six missing
extensions, it would have returned a clean `PASS 19/19` on a runtime that cannot resize an
image, read EXIF, sniff a MIME type or open an HTTPS connection. The loss would have
surfaced inside 04-02's tracer, which is precisely the failure RESEARCH §Summary says this
plan exists to prevent.

The correction is a second gate with a different scope, not more assertions bolted onto the
first: `assert-capabilities.sh` checks capability, `handler-sweep.sh` checks rendering.
Neither can answer the other's question.

**The new checker shipped with a defect of its own, caught only by running it against the
real body before trusting it.** Probe keys contain colons, so splitting on the first one
parsed `ext:openssl          : yes` as the value `openssl : yes` — and every *present*
extension was reported as a blocker. Its first run announced that `openssl`, `hash` and
`filter` were missing while the body plainly said otherwise. A checker that invents
failures is no more useful than one that misses them.

## Probe defect fixed: D4-06 was silently un-answered

`outbound:curl443 : FAIL ext-curl not loaded` is not a measurement of the network, it is
the absence of one. Tying D4-06 to a single extension let a missing extension convert the
phase's notification-channel decision from measured back to unknown. The probe now carries
an independent `outbound:fopen443` test over the `https://` stream wrapper, needing only
`allow_url_fopen` (`1`) and `openssl` (present on both builds), so D4-06 is answerable even
if cURL never returns.

## Confirmed good on 8.5

`upload_max_filesize` `250M` and `post_max_size` `200M` **measured in effect**, not inferred
— D4-13's 2M ceiling is genuinely gone and 04-03 needs no `.user.ini` work.
`user_ini.filename` is now populated (`.user.ini`, ttl 300). `smtp:localhost:25` still
refused, unchanged, so D4-11's mail leg needs the sendmail binary or an external relay.

## Current state

The server rests in Stage A: `.php` on 8.5, 19 `.html` pages on 5.2, stable and serving
correctly. `src/.htaccess` in the working tree matches that Stage-A state and is
deliberately left uncommitted; its **committed** state is the Step B payload, reached with
`git checkout -- src/.htaccess`.

Step B proceeds only when `scripts/host-probe/assert-capabilities.sh` exits `0` against a
fresh probe body. Against the 2026-09-19 body: `FAIL — 7 blocker(s)`.

---
*Phase: 04-hardening-cutover*
*Plan: 01 — INCOMPLETE. Handler mechanism proved; Step B gated on the 8.5 extension set.*
*Date: 2026-09-19*

## Self-Check: PASSED

All six claimed files exist on disk; all six claimed commits resolve in git.

```
scripts/host-probe/probe.php.tpl           FOUND
scripts/host-probe/run-probe.sh            FOUND
scripts/host-probe/handler-sweep.sh        FOUND
src/.htaccess                              FOUND
.../04-HOST-CAPABILITIES.md                FOUND
.../deferred-items.md                      FOUND

b876790 332960f d17c851 feac63b 0c74370 5d2b7e6   all resolve
```

Note what this self-check does NOT assert: that any of it is deployed. `src/.htaccess`
existing and being committed is not the same claim as the server running it, and this
plan's remaining work is entirely on the far side of that distinction.
