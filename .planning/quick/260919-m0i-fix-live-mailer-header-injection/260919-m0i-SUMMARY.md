---
phase: quick-260919-m0i
plan: 01
subsystem: contact-form / live-deploy
tags: [security, header-injection, php, ftps, live-production, verified]
status: complete
requires:
  - "human inspection of three test messages sent 2026-09-19 13:14:58Z / 13:15:26Z / 13:15:36Z — DONE 2026-09-23, all three pass"
provides:
  - "site-current/mailer.php with no path from POST data to a mail header — DEPLOYED"
  - "scripts/deploy-live.sh — allowlisted single-file live-root uploader"
  - "backups/20260919T131139Z/ — pre-patch snapshot, hash-verified rollback anchor"
affects:
  - "public_html/mailer.php on torin.bg — LIVE as of 2026-09-19 ~13:15Z"
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
  duration: ~55m
  completed: null
  tasks_completed: 2
  tasks_total: 3
actuals:
  tokens: 13000
  tasks: 2
  commits: 4
---

# Quick 260919-m0i: Fix Live Mailer Header Injection — Summary

## Status: the patch is LIVE and its runtime behaviour is UNVERIFIED

Read this before anything else in this file.

**What is true right now:** the injection sink is gone from the source that is
deployed to `public_html/mailer.php`, every source gate passes with a firing
negative control, and the live endpoint answers correctly. **Nobody has yet observed
the injected header's absence in a delivered message.**

Those are two different claims and only the first is established. The second is the
one the task exists to prove, and it cannot be proven from anything in this
repository — an injection POST returns `302` / `location: msg.html` / a zero-length
body **identically before and after the patch**, which the pre-patch control at
13:14:58Z and the post-patch attempt at 13:15:36Z demonstrate side by side in the
evidence file. Three matching green HTTP measurements are worth nothing as evidence
here.

**Blocked on:** a human opening office@torin.bg and reading the raw headers of three
test messages. No mailbox access until **Monday**. Full self-contained instructions,
including the per-message pass criteria and the UTC timestamps that identify them,
are in section 5 of
`.planning/quick/260919-m0i-fix-live-mailer-header-injection/260919-m0i-EVIDENCE.md`.

**Do not close this task on the gate battery.** A green section 1 is not a fix.

---

## Rollback — copy-pasteable

If the contact form turns out to be broken for real visitors:

```bash
cd /Users/alabala/Documents/projects/torin
git hash-object backups/20260919T131139Z/public_html/mailer.php
# must print fd4deb874f2a4cf2de134fd6db732d8db7f5d8de before you proceed
cp backups/20260919T131139Z/public_html/mailer.php site-current/mailer.php
TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php
```

Then re-run the normal-submission measurement (evidence §4a) and confirm `302` /
`location: msg.html` / zero-byte body.

**Rolling back re-opens the vulnerability.** Correct only if the form is genuinely
broken — §4a's clean result says it is not.

---

## What Shipped

**`site-current/mailer.php`** — live since ~13:15Z, 4943 bytes. No path remains from
posted data to a mail header. `From:` is a fixed literal on the shop's own domain
with zero interpolation. `Reply-To:` is emitted only after the submitted address has
had every CR, LF and NUL byte removed *and* then passed PHP's address validator —
stripping first, so the bytes that get approved are byte-for-byte the bytes written
into the header. All four POST reads carry `isset` + `is_string` guards, which is
not decoration: on PHP 8.5 a bracketed field name arrives as an array and makes
`htmlentities()` throw, and anything that error printed would be output sent before
`header()`, breaking the redirect. The guards are what preserved the measured
`content-length: 0` baseline under the hostile input at 13:15:36Z.

The HTML body, the Bulgarian subject and the `msg.html` redirect are byte-identical
— the diff has three hunks and none touches lines 13–70.

**`scripts/deploy-live.sh`** — uploads one allowlisted file to `public_html/`. Safety
is structural, not advisory: no source-tree enumeration code exists in the file, so
an accidental whole-directory upload is unexpressible rather than discouraged. The
allowlist holds bare filenames matched by exact string equality, so an argument
carrying a separator or a `..` cannot match. `TORIN_LIVE_DEPLOY_CONFIRM=1` is
required, and no flag is passed that would let curl create a remote directory.
Credential handling is carried over from `deploy-new.sh` unchanged — password decoded
inside a short-lived `python3` process straight into a chmod-600 netrc file, consumed
via `--netrc-file`, removed by an exit trap, never in a shell variable or on a
command line.

**`backups/20260919T131139Z/`** — pre-patch snapshot. 16/16 pages, 7/7 must-carry root
files, 4 directories mirrored. Its `mailer.php` hashes to
`fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`, an **exact** match for the reviewed
pre-patch bytes, so `site-current/` had not drifted from live and the patch was
applied to the same bytes production was running.

## Measured Results

| Step | Result |
|------|--------|
| Source gates (11) | all pass |
| Deployer structural gates (10) + behavioural refusals (6) | all pass |
| Negative controls (5) | all fired against known-positive files |
| Backup hash vs pre-patch blob | exact match, no drift |
| Pre-patch control POST, 13:14:58Z | `302`, `location: msg.html`, `content-length: 0`, 0-byte body |
| Deploy | 4943 bytes → `public_html/mailer.php`, exit 0 |
| Post-patch normal, 13:15:26Z | `302`, `location: msg.html`, `content-length: 0`, 0-byte body |
| Post-patch injection, 13:15:36Z | `302`, `location: msg.html`, `content-length: 0`, 0-byte body |
| Live static pages after deploy | `/`, `/index.html`, `/uslovia.html`, `/msg.html` all `200` |
| **Mailbox raw-header inspection** | ✅ **DONE 2026-09-23 — all three messages pass** (see the closing section at the end of this file) |

## The Weakest Link: unparsed PHP went live

`php -l` was **never run** — no `php` binary on the executor's machine or the
developer's, Docker daemon down on both. The patch introduces no syntax newer than
PHP 5.2 and gate G9 confirms it, but that is a source-level argument, not an
execution.

So: **unparsed PHP was uploaded to the live production root, and the first thing to
execute it was a real HTTP request.** The 13:15:26Z clean `302` with a zero-byte body
is genuine parse evidence — a parse error returns `500` with a body — but it was
obtained *after* the file was already serving public traffic. That is verification
after exposure, not before. Had it failed, the contact form would have been broken
for real visitors between upload and measurement.

The window was small and rollback is one command, but the ordering was wrong. A parse
check is cheap and belongs before an upload. Worth fixing before the next live PHP
change.

## Deviations from Plan

**1. [Rule 3 — Blocking] All live-host steps performed by the developer, not the executor.**
- **Found during:** Task 1, before any command ran.
- **Issue:** `backup-live-site.sh` and `deploy-live.sh` both read the gitignored
  credentials and write to / send from the live host — the coordinator's boundary,
  and denied to subagents by the permission classifier.
- **Action:** Did not probe the boundary. Completed all local work, wrote exact
  commands with pass/fail criteria into the evidence file, halted at a checkpoint.
  The developer ran them and returned output, which is recorded verbatim. No live
  result was predicted or assumed at any point.

**2. [Rule 3 — Blocking] Mirror-drift check re-expressed as a content hash.**
- **Issue:** The plan diffs the snapshot against `site-current/mailer.php`. Because
  the patch landed before the backup (forced by deviation 1), that comparison's
  answer depends on whether the worktree had been merged — false drift after a
  merge, correct only by luck before one.
- **Fix:** Now `git hash-object` against the pinned pre-patch blob
  `fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`. Same question, same strictness,
  independent of merge state and of which checkout runs it. It returned an exact
  match.

**3. [Rule 1 — Bug] The deployer's own gate was counting its comments.**
- **Found during:** Task 2 Part C, first gate run.
- **Issue:** The script explained in two comments that it deliberately omits the
  remote-dir-creation flag — and named the flag. The absence-gate counted those
  comments and reported the flag **present** (measured 2, required 0). Fourth
  instance in this project of a check counting prose.
- **Fix:** Comments reworded to describe the flag without naming it; the gate also
  strips comment lines before counting. Both comment-inclusive and comment-stripped
  forms now measure 0.

**4. [Rule 1 — Bug] Two gates in my own battery were measuring nothing.**
- **Issue:** G7 and G8 reported 0 against required 1 and 4 — not a regression in the
  file, a quoting bug that put literal backslashes into the `grep -F` patterns so
  they matched text that cannot exist. Written as absence-gates they would have
  passed while matching nothing.
- **Fix:** Patterns moved into correctly-quoted variables; both now measure the
  expected 1 and 4.

**5. [Rule 2 — Missing critical functionality] Negative controls added for every absence-gate.**
- **Issue:** After deviations 3 and 4, two checks had been observed only in states
  that proved nothing. The plan mandated a negative control for G1 alone.
- **Fix:** Five now run, each against a file known to contain what the gate looks
  for — G1 and the sink literal against pre-patch `mailer.php`; the directory-walk,
  remote-dir-creation and staging-root gates against `deploy-new.sh`, which contains
  all three. All fired. Also added D13, a near-miss allowlist argument
  (`mailer.php.bak`) that prefix or glob matching would wrongly accept.

## Known Gaps

**1. Runtime absence of the injected header — UNPROVEN.** The blocking item. Monday.
Evidence §5.

**2. Live PHP was deployed unparsed.** See "The Weakest Link" above.

**3. One of the plan's evidence gates is weak by construction.**
`grep -qF 'POST-PATCH-260919'` is satisfied by the marker appearing in a documented
curl command, so it would have passed before any send. It happens to be backed by a
real measurement now, but it was never a real gate. Its companions are honest:
`grep -c 'HTTP/... 302'` measures 3 and `grep -c '^content-length: 0'` measures 3,
and both were 0 until real output was pasted in.

**4. `assets1/` snapshot is 12228KB against a ~14000KB baseline.** Above the
10000KB truncation-abort threshold, so the backup was accepted. Some delta is
expected after a year of edits. Worth a glance if a future snapshot drops further;
not worth acting on now.

**5. `mailer.php` remains unauthenticated and unthrottled.** Accepted in the plan
(T-m0i-05), owned by 04-05 with the honeypot, PHPMailer/SMTP and the unaligned-SPF
work. This patch neither creates nor worsens it.

## Scope

`src/` and `.planning/phases/04-hardening-cutover/` untouched — `git status` showed
only `site-current/mailer.php`, `scripts/deploy-live.sh` and the two quick-task docs
throughout.

## Commits

| Commit | What |
|--------|------|
| `60fd501` | `fix(quick-260919-m0i)`: stop visitor input reaching mail headers |
| `a30c0c2` | `feat(quick-260919-m0i)`: add single-file live-root deployer |
| `b6c64a2` | `docs(quick-260919-m0i)`: gate measurements, halt at deploy checkpoint |
| _(this)_ | `docs(quick-260919-m0i)`: record live results, leave mailbox proof open |

## Self-Check: PASSED

- `site-current/mailer.php` — FOUND, 11 source gates pass, 5 negative controls fired
- `scripts/deploy-live.sh` — FOUND, executable, 10 structural + 6 behavioural gates pass
- `260919-m0i-EVIDENCE.md` — FOUND, sections 1–4 measured, section 5 open
- `60fd501`, `a30c0c2`, `b6c64a2` — all FOUND in `git log`

Every live figure in this summary was supplied by the developer from a real run and
is reproduced without rounding. No claim is made anywhere that the injection has been
observed to be absent from a delivered message, because it has not.

---

## ✅ CLOSED 2026-09-23 — the header assertion was read, all three messages pass

This task shipped a security patch to a live production file on 2026-09-19 and then sat
`incomplete` for four days, because **the only observable that could prove it works lives in
delivered email headers, not in anything this repo can reach.**

The owner inspected the raw headers of all three test messages at `office@torin.bg` and reports
all three matched their criteria:

| # | Sent (UTC) | Criterion | Result |
|---|---|---|---|
| 1 | 13:14:58 | `X-Torin-Injection-Test: PRE-PATCH-260919` **present** | ✅ present |
| 2 | 13:15:26 | correct `From:`, `Reply-To:` holds the visitor address, no `X-Torin-` | ✅ |
| 3 | 13:15:36 | **zero** `X-Torin` hits, correct `From:`, **no** `Reply-To:` | ✅ |

**Message 1 is the load-bearing result and it is easy to misread as the boring one.** Its
criterion is a PRESENCE while message 3's is an ABSENCE. Had message 1 come back clean, message
3's clean headers would have proven nothing — an injection that never worked is absent from every
message, patched or not, and this task would have shipped a fix for a vulnerability it had not
demonstrated. Message 1 carrying the marker establishes the hole was real and reachable on the
live host; only against that does message 3's zero hits mean the patch closed it.

**Nothing cheaper could have substituted.** §3 and §4 recorded byte-identical HTTP responses
before and after the patch — `302`, `location: msg.html`, `content-length: 0`, zero-byte body. No
status code, no source gate, no re-reading of the deployed bytes distinguishes a patched host from
an unpatched one here. The gates that looked green on 2026-09-19 were all necessary and none of
them was sufficient, which is exactly why this task refused to mark itself complete on their
strength.

Message 2 also confirms the shop's real workflow survived: pressing Reply still addresses the
customer, not `office@torin.bg`. That was the single thing the change was most likely to break.

### The process lesson stands, and is not retired by this

**Unparsed PHP reached the live root.** There was no `php` binary on the build machine and Docker
was down, so the patch was never syntax-checked before upload — the first thing to execute it was
a real public HTTP request. It parsed clean, but that is verification *after* exposure, and a
parse error would have taken the live contact form down for every visitor.

That gap is now closed by circumstance rather than by discipline: **PHP 8.5.10 is installed** (see
quick task `260922-i38`, which ran `php -l` clean across all 45 files and found a real containment
bug on a selftest's first-ever execution). The rule survives the fix: lint before a live PHP
deploy, every time.

### Owner housekeeping still outstanding

The three test messages should be **deleted from `office@torin.bg`**. They are labelled
`AUTOMATED SECURITY TEST 260919-m0i`, but a security test left in an enquiry mailbox is eventually
read as a real customer. Nothing in this repo can do it.

### Scope note

This closes the patch on the **old site's root `mailer.php`** only. It is a different code path
from `contact-send.php` under `/new/`, whose own end-to-end delivery was proven separately on
2026-09-22 (quick task `260923-ebg`). Phase 4 plan 04-05 retires this endpoint entirely at
cutover, at which point the patched file stops being reachable at all.
