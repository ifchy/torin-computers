---
phase: quick-260919-m0i
plan: 01
subsystem: contact-form / live-deploy
tags: [security, header-injection, php, ftps, live-production]
status: blocked-on-checkpoint
requires:
  - live FTP credentials (primary checkout only, gitignored)
  - developer-run backup, deploy, and mailbox inspection
provides:
  - site-current/mailer.php with no path from POST data to a mail header
  - scripts/deploy-live.sh — allowlisted single-file live-root uploader
affects:
  - public_html/mailer.php on torin.bg (NOT YET — deploy is pending)
tech-stack:
  added: []
  patterns:
    - "strip CR/LF/NUL first, then validate — the approved bytes are the emitted bytes"
    - "structural safety over advisory safety: a capability absent from the source cannot be re-enabled by argument or environment"
    - "every absence-gate run against a known-positive file before being trusted"
key-files:
  created:
    - scripts/deploy-live.sh
    - .planning/quick/260919-m0i-fix-live-mailer-header-injection/260919-m0i-EVIDENCE.md
  modified:
    - site-current/mailer.php
decisions:
  - "From: is a fixed ASCII literal on torin.bg; no interpolation of any kind reaches it"
  - "Reply-To: omitted entirely when the address fails either barrier, rather than repaired by guessing"
  - "PHP 5.2-compatible dialect retained even though the live root now runs 8.5.10"
  - "new deployer rather than parameterising deploy-new.sh, whose no-argument branch uploads a whole tree"
  - "mirror-drift check re-expressed as a content hash (fd4deb87...) so it holds regardless of merge order"
metrics:
  duration: ~40m
  completed: 2026-09-19
  tasks_completed: 1.5
  tasks_total: 3
actuals:
  tokens: 11000
  tasks: 1
  commits: 3
---

# Quick 260919-m0i: Fix Live Mailer Header Injection — Summary

The injection sink is gone from the source and the live-root deploy tool exists and
is gated; the patch is **not yet on the live host** and the fix is **not yet
proven** — both remaining steps require credentials and a mailbox, which are the
developer's.

## What Is Done

**`site-current/mailer.php`** no longer has any path from posted data to a mail
header. `From:` became a fixed literal on the shop's own domain with zero
interpolation. `Reply-To:` is emitted only after the submitted address has had every
CR, LF and NUL byte removed *and* then passed PHP's address validator — strip first,
so the bytes that get approved are byte-for-byte the bytes written into the header.
All four POST reads carry `isset` + `is_string` guards, which is not decoration: on
PHP 8.5 a bracketed field name arrives as an array and makes `htmlentities()` throw,
and anything that error printed would be output sent before `header()`, breaking the
redirect. The guards are what preserve the measured `content-length: 0` baseline
under hostile input.

The HTML body, the Bulgarian subject and the `msg.html` redirect are byte-identical
— the diff has three hunks and none of them touches lines 13–70.

**`scripts/deploy-live.sh`** uploads one allowlisted file to `public_html/`. Its
safety is structural, not advisory: there is no source-tree enumeration code in the
file at all, so an accidental whole-directory upload is unexpressible rather than
discouraged. The allowlist holds bare filenames matched by exact string equality, so
an argument carrying a separator or a `..` cannot match any entry.
`TORIN_LIVE_DEPLOY_CONFIRM=1` is required, and no flag is passed that would let curl
create a remote directory. Credential handling is carried over from `deploy-new.sh`
unchanged — password decoded inside a short-lived `python3` process straight into a
chmod-600 netrc file, consumed via `--netrc-file`, removed by an exit trap, never in
a shell variable or on a command line.

All source gates, the deployer's structural gates and its six behavioural refusals
pass, with the measured numbers recorded in `260919-m0i-EVIDENCE.md` section 1.

## What Is NOT Done — and what that means

| Step | Status | Why |
|------|--------|-----|
| Fresh live backup | not run | `backup-live-site.sh` needs the gitignored credentials; live-host operations are the developer's |
| Mirror-drift check | not run | depends on the backup |
| Pre-patch control POST | not run | live send |
| Deploy to `public_html/` | not run | live-root write |
| Post-patch measurements | not run | live sends |
| Mailbox raw-header inspection | not run | human, and the only place the fix is actually proven |

**The honest position on proof.** Every check that passed today is a source-level
check. It proves the sink is gone from the code. It does not prove the injected
header is absent from a delivered message, and no HTTP-level check can: an injection
POST returns `302` / `location: msg.html` / a zero-length body identically before
and after the patch. That is precisely why the plan front-loads a pre-patch control
send. **This fix is unverified until a human reads the raw headers of the three
messages.** Section 5 of the evidence file is that checklist.

## Deviations from Plan

**1. [Rule 3 — Blocking] All live-host steps deferred to the developer.**
- **Found during:** Task 1, before any command ran.
- **Issue:** `backup-live-site.sh` and `deploy-live.sh` both read the gitignored
  credentials file and write to / send from the live host. The orchestrator holds
  that boundary and the permission classifier denies these to subagents.
- **Action:** Did not probe the boundary. Completed everything local, wrote the
  exact commands and their pass/fail criteria into the evidence file, and halted at
  a checkpoint. No live result is claimed or predicted anywhere.

**2. [Rule 3 — Blocking] Mirror-drift check re-expressed as a content hash.**
- **Found during:** Task 1 planning, once step ordering was forced by deviation 1.
- **Issue:** The plan diffs the snapshot against `site-current/mailer.php`. Because
  the patch now lands before the backup, that comparison's answer depends on whether
  this worktree has been merged into the primary checkout yet — it would report
  false drift after a merge, and the correct answer only by luck before one.
- **Fix:** The check is now `git hash-object` against the pinned pre-patch blob
  `fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`. Same question, same strictness, and
  the answer no longer depends on merge state or which checkout it runs from.

**3. [Rule 1 — Bug] The deployer's own gate was counting its comments.**
- **Found during:** Task 2 Part C, first gate run.
- **Issue:** The script explained in two comments that it deliberately omits the
  remote-dir-creation flag — and named the flag. The absence-gate counted those
  comments and reported the flag **present** (measured 2, required 0). This is the
  fourth time this project has shipped a check that counted prose.
- **Fix:** Comments reworded to describe the flag without naming it, and the gate
  additionally strips comment lines before counting. Both the comment-inclusive and
  comment-stripped forms now measure 0.

**4. [Rule 1 — Bug] Two gates in my own battery were measuring nothing.**
- **Found during:** Task 2 Part C, first gate run.
- **Issue:** G7 and G8 reported 0 against required 1 and 4. Not a regression in the
  file — a quoting bug in my gate script put literal backslashes into the `grep -F`
  patterns, so they matched text that cannot exist. Had those gates been written to
  assert "absent" instead of "present", they would have passed while measuring
  nothing at all.
- **Fix:** Patterns moved into variables with correct quoting; both now measure the
  expected 1 and 4. Prompted deviation 5.

**5. [Rule 2 — Missing critical functionality] Negative controls added for every
absence-gate.**
- **Issue:** After deviations 3 and 4, two of my checks had been observed only in a
  state that proved nothing. The plan mandated a negative control for G1 only.
- **Fix:** Five now run, each against a file known to contain what the gate looks
  for — G1 and the sink literal against the pre-patch `mailer.php`, and the
  directory-walk, remote-dir-creation and staging-root gates against
  `deploy-new.sh`, which genuinely contains all three. All five fired (NC1–NC5).
  Also added D13, a near-miss allowlist argument (`mailer.php.bak`) that a prefix or
  glob match would wrongly accept; exact equality rejects it.

## Known Gaps

**1. The patched PHP has not been parsed.** No `php` binary on this machine and the
Docker daemon is not running, so `php -l` could not run. The patch uses no syntax
newer than PHP 5.2 and gate G9 confirms no short arrays or null-coalescing were
introduced, but "should parse" is not "parses". Mitigations, in order: run `php -l`
before deploying if any PHP is reachable; otherwise the normal-submission
measurement is a runtime parse proof, since a parse error returns `500` with a body
rather than `302` with none. Rollback is one command and the pre-patch bytes are
content-addressed.

**2. Two of the plan's evidence gates are weak and currently pass for the wrong
reason.** `grep -qF 'POST-PATCH-260919'` is satisfied by the marker appearing in the
documented curl command, so it passes now, before any send. Flagging it rather than
leaning on it. The companion gates are honest: `grep -c 'HTTP/... 302'` and
`grep -c '^content-length: 0'` both measure **0** against the evidence file right
now and will stay 0 until real measurements are pasted in. Those two are the ones
worth watching.

**3. `mailer.php` remains unauthenticated and unthrottled.** Accepted in the plan
(T-m0i-05) and owned by 04-05 along with the honeypot, PHPMailer/SMTP and the
unaligned-SPF deliverability work. This patch neither creates nor worsens it.

## Scope

`src/` and `.planning/phases/04-hardening-cutover/` were not touched — `git status`
showed only `site-current/mailer.php` and `scripts/deploy-live.sh` throughout.

## Commits

| Commit | What |
|--------|------|
| `60fd501` | `fix(quick-260919-m0i)`: stop visitor input reaching mail headers |
| `a30c0c2` | `feat(quick-260919-m0i)`: add single-file live-root deployer |
| _(this)_ | `docs(quick-260919-m0i)`: evidence and summary |

## Self-Check: PASSED

- `site-current/mailer.php` — FOUND, all 11 source gates pass, 5 negative controls fired
- `scripts/deploy-live.sh` — FOUND, executable, 10 structural + 6 behavioural gates pass
- `260919-m0i-EVIDENCE.md` — FOUND
- `60fd501`, `a30c0c2` — both FOUND in `git log`

No claim of a live deploy, a delivered message, or a runtime measurement appears
anywhere in this summary or in the evidence file. Every such slot is explicitly
marked PENDING.
