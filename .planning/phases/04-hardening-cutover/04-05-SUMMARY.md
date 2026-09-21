---
phase: 04-hardening-cutover
plan: 05
subsystem: contact
tags: [spam, honeypot, hmac, rate-limit, security, partial]
status: blocked
requires:
  - "04-02 contact spine (contact-send.php, contact-form.php, torin_send_fail_page, the hidden `t` pass-through)"
  - "04-03 upload pipeline (the outside-the-document-root temp storage precedent in includes/upload.php)"
  - "kontakti.html sending Cache-Control: no-store — the time trap is unsound without it (RESEARCH P-11)"
provides:
  - "torin_verify_honeypot($post) — decoy test that never casts a non-string to string"
  - "torin_sign_timestamp($ts, $secret) — canonicalised unix time joined to a keyed SHA-256 HMAC"
  - "torin_verify_timestamp($field, $secret, $minAge, $maxAge) — hash_equals, then both age bounds; returns a reason string"
  - "torin_rate_limit_ok($ip, $secret, $limits) — read-only per-address throttle check"
  - "torin_rate_limit_record($ip, $secret, $limits) — writes a record for a DELIVERED enquiry only"
  - "torin_guard_secret() / torin_guard_dir() / torin_rate_limit_path() — private storage and the signing key"
  - "torin_send_fail_page(..., $status = 422) — optional status, so the throttle can answer 429"
affects:
  - "04-05 Task 3 (the mail leg and the error re-render land on top of this guard order)"
  - "04-09/04-10 cutover (the signing key and the throttle records live in the system temp directory; nothing to move, but a temp-dir change rotates the key)"
tech-stack:
  added: []
  patterns:
    - "RESEARCH 'Don't Hand-Roll' — hash_hmac for the token, hash_equals for the comparison, no polyfill"
    - "RESEARCH P-11 — the trap is coupled to the no-store header on the contact page"
    - "upload.php:178-194 — private state lives in the system temp directory, never under the deployed tree"
    - "UI-SPEC C-6 — plausible decoy name, autocomplete off, ~3s lower bound, ~2h upper bound"
key-files:
  created:
    - src/includes/spam-guard.php
  modified:
    - src/contact-send.php
    - src/includes/contact-form.php
decisions:
  - "No CAPTCHA and no visible challenge, recorded under the Claude's-Discretion grant so it is not revisited casually"
  - "Oversized-POST detection moved AHEAD of the decoy: an emptied body has no `t` field and would otherwise be judged a forgery"
  - "The throttle records DELIVERED enquiries, not POSTs — counting every POST throttles the visitor who corrects a validation error ten seconds later"
  - "«too-fast» is absorbed silently; «forged»/«malformed»/«stale» get the honest retry page, because each has a mundane non-bot cause and a false 'sent' is the one thing the prohibitions forbid outright"
  - "The signing key is minted by spam-guard.php in its own private storage rather than read from the credentials file — contact-form.php is page chrome and site-config.php reserves that file for contact-send.php alone"
  - "torin_verify_timestamp returns 'ok' rather than '' for success, so that a future `if (!f())` misuse loses spam instead of losing every customer"
metrics:
  duration: "one session"
  completed: 2026-09-21
  tasks_completed: 1
  tasks_total: 3
actuals:
  tokens: 9200
  tasks: 1
  commits: 2
---

# Phase 04 Plan 05: Spam Defence and the Email Leg — Summary

**PARTIAL. 1 of 3 tasks complete.** Task 2 (the spam guard) is implemented and committed.
Task 1 and Task 3 are NOT done, and nothing in this plan should be read as if they were.

A honeypot, an HMAC-signed time trap and a per-address flat-file throttle now stand above
field validation and above every outbound call in `src/contact-send.php`, with every rejection
writing one correlation line that names its reason and carries no submitted content.

## Why this plan is blocked

| Task | State | Reason |
|---|---|---|
| 1 — package legitimacy gate + mailbox credential | **NOT RESOLVED** | A `gate="blocking"` human-verify checkpoint covering (a) independent confirmation of PHPMailer's repository, release tag and packaging against the vendor's own sources, and (b) whether `office@torin.bg` is a real mailbox and whether `smtp_password` was added to the secrets file. Package-legitimacy gates are **never auto-approvable** and the executor did not approve it. |
| 2 — spam defence + oversized-POST blind spot | **COMPLETE** | Committed as `e08de23`. It has no dependency on either half of the Task 1 gate — it vendors nothing, sends nothing and reads no credential. |
| 3 — the email leg, the confirmation, the failure states | **NOT STARTED** | It vendors three PHPMailer files and wires an SMTP transport, both of which sit directly behind the Task 1 gate. **No third-party code was vendored and `src/vendor/` was not created.** |

Resume by answering Task 1's checkpoint (the version to pin, and either "smtp_password added"
or "use sendmail fallback"), then executing Task 3.

## What Was Built

### `src/includes/spam-guard.php` (new)

PHP 5.2-safe dialect, functions and no data, emits nothing on include. Eight functions — the
four the plan names, plus four supporting ones:

| Function | Role |
|---|---|
| `torin_verify_honeypot($post)` | True when the decoy is clean. A non-string decoy value is a rejection and is **never cast to string** — casting an array raises a warning which, with `display_errors` still On in this subtree, would print into the response before the caller's `header()` call. |
| `torin_sign_timestamp($ts, $secret)` | `(string)(int)$ts . '.' . hash_hmac('sha256', …)`. The canonicalisation is what lets the form pass an int and the handler re-sign a numeric string without disagreeing. |
| `torin_verify_timestamp($field, $secret, $min, $max)` | Splits, re-signs, compares with **`hash_equals`**, then checks both bounds. Returns `'ok' \| 'malformed' \| 'forged' \| 'too-fast' \| 'stale'`. |
| `torin_rate_limit_ok($ip, $secret, $limits)` | Read-only. Counts records inside the window. **Fails open**, with a log line. |
| `torin_rate_limit_record($ip, $secret, $limits)` | Writes one record under `LOCK_EX`; prunes expired entries; sweeps its own prefix one run in twenty. |
| `torin_guard_dir()` | The system temp directory, checked rather than assumed, or `''`. |
| `torin_guard_secret()` | Mints a 32-byte key once via `random_bytes`, exclusive-create, `chmod 600`, symlink refused; caches it per request. |
| `torin_rate_limit_path($ip, $secret)` | `sha256($ip . '|' . $secret)` — the address is hashed, never stored. |

**Storage is outside the document root**, in `sys_get_temp_dir()`, matching the placement
`includes/upload.php:178-194` chose for a re-encoded photograph and for the same reason: this
host maps `.html` to the PHP handler, so any intact file in a web-reachable folder is a
code-execution surface (D4-15). `grep -c 'public_html' src/includes/spam-guard.php` → **0**.

### `src/contact-send.php` — the guard order

The plan's order, and the one deviation from the file's previous shape:

```
66   require_once …/includes/spam-guard.php
99   $torin_len = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);   ← oversized-POST, FIRST
149  if (!torin_verify_honeypot($_POST)) {                   ← decoy
177  $torin_ts_verdict = torin_verify_timestamp(             ← signed time trap
219  if (!torin_rate_limit_ok($torin_ip, $torin_secret, $torin_rl)) {   ← throttle
249  if ($torin_in['device'] === '' || torin_chars(…) > 120) {          ← first field validation
376  $torin_result = torin_notify($torin_in, $torin_photos, $torin_secrets);
```

Every guard reference is on a **lower line number** than the first field-validation call (249)
and than `torin_notify(` (376), which is the plan's line-order acceptance criterion.

### `src/includes/contact-form.php`

`$torin_t = time();` became `$torin_t = torin_sign_timestamp(time(), torin_guard_secret());`.
The hidden field stops being the tracer's pass-through and carries a real value. The file's
one-function-no-output contract survives: `spam-guard.php` emits nothing on include and opens
no credential.

## Decisions and Judgement Calls

**1. No CAPTCHA, no visible challenge.** Taken under CONTEXT's Claude's-Discretion grant and
recorded in the file header so it is not revisited casually: a widget reintroduces the
third-party-cookie problem D4-18 was chosen to avoid, costs conversions on the one form this
business depends on, is third-party payload against DESIGN-02, and the actual threat is
commodity form spam.

**2. Oversized-POST detection moved ahead of the decoy.** 04-02 placed it second, which was
harmless while the decoy was the only check — an emptied body has an empty decoy and fell
through. It stops being harmless with a time trap below it: an emptied body also has no `t`
field, so a visitor whose five photographs exceeded the ceiling would be judged a forger. The
guard must never rule on a request whose fields the engine already discarded.

**3. The throttle counts DELIVERED enquiries, not POSTs.** This is the one place the plan's
literal shape was extended: `torin_rate_limit_ok()` is read-only at the guard position, and a
second function `torin_rate_limit_record()` runs inside the success branch, after a channel has
accepted. Recording every POST would throttle (a) the visitor who mistypes their email, reads
«проверете отбелязаните полета» and corrects it ten seconds later, and (b) the visitor
re-sending after an all-channels-failed page that literally tells them to try again. Both are
T-04-25 — a real enquiry refused by our own defence — in a new costume. The plan's behaviour
("a second post from the same address inside the throttle window is rejected") still holds
literally for completed submissions, which is what its acceptance test exercises. Window 900s,
max 1.

**4. Only «too-fast» is absorbed silently.** The decoy keeps 04-02's byte-identical 303, and
so does a sub-three-second submission — nothing a human does produces that verdict. But
«forged», «malformed» and «stale» each have a mundane non-bot cause (a cached contact page
serving a stale token, our own key rotating between render and submit, a proxy mangling a
hidden field, a tab left open over lunch), so they get the honest retry page instead. Telling
that visitor "sent" when nothing was sent is the prohibition this plan states outright, and
with no server-side copy it would be invisible on both ends. The single sentence names the
likeliest cause without asserting it and without naming the verdict, which would tell a bot
which knob to turn.

**5. The signing key is minted locally, not read from the credentials file.** Both callers need
it and one of them, `contact-form.php`, is ordinary page chrome. `site-config.php:283-310` is
explicit that `contact-send.php` is the only file that may read the credentials file, precisely
so no include chain from the page shell reaches a credential (T-04-26). A key `spam-guard.php`
creates for itself keeps that quarantine intact.

**6. `torin_verify_timestamp` returns `'ok'`, not `''`.** Both encode success, but they fail
differently under a future `if (!torin_verify_timestamp(...))`: with `'ok'` that misuse accepts
everything, with `''` it rejects everything. One loses spam; the other loses every customer.

**7. `torin_send_fail_page()` gained an optional `$status = 422`.** The throttle branch is not
"what you sent is unprocessable" but "you have sent one already", and answering 422 would be a
lie told to every machine reading the response. The three existing call sites are untouched.

**8. No `function_exists` guard around `hash_hmac`/`hash_equals`.** `ext-hash` stopped being
removable in PHP 7.4; `ext-mbstring` — which `contact-send.php` does guard — measurably came and
went under this account during 04-01. A fallback for either would be hand-rolled crypto, which
RESEARCH's "Don't Hand-Roll" table names explicitly. `random_bytes` **is** guarded, because it
arrived in PHP 7 and is reachable from the page shell.

## Verification Performed

Precondition checked first, read-only, and **met**: `src/kontakti.html:41` sends
`Cache-Control: no-store`.

| Gate | Result |
|---|---|
| `grep -c 'hash_hmac' src/includes/spam-guard.php` ≠ 0 | **PASS** (2) |
| `grep -c 'hash_equals' src/includes/spam-guard.php` ≠ 0 | **PASS** (3) |
| non-comment `session_start` occurrences = 0 | **PASS** (0) |
| `grep -c 'CONTENT_LENGTH' src/contact-send.php` ≠ 0 | **PASS** (1) |
| `grep -cE '(=>\|=)[[:space:]]*\[\|return[[:space:]]+\[' src/includes/spam-guard.php` = 0 | **PASS** (0) |
| `grep -c 'public_html' src/includes/spam-guard.php` = 0 | **PASS** (0) |
| The four named functions are declared | **PASS** (lines 201, 217, 242, 309) |
| Guard line order vs validation and `torin_notify(` | **PASS** (see table above) |
| Brace/paren/bracket balance across all three files | **PASS** (0/0/0 each) |
| Live POST with a forged timestamp returns no 5xx | **NOT RUN** — see Known Gaps |
| Live POST at a valid interval, then a throttled repeat | **NOT RUN** — see Known Gaps |
| Rejection lines present in the host error log, carrying no field values | **NOT RUN** — see Known Gaps |

## Known Gaps

These are stated as gaps, not as passes. The orchestrator should file them in `WINDOWS.md`;
this executor did not write that shared ledger.

1. **`unrun-verify` — `src/includes/spam-guard.php`: nothing in this plan has been executed by a
   PHP interpreter, in either direction.** There is no `php` binary and no running Docker daemon
   on the build machine, and `scripts/deploy-new.sh` is denied to subagents by the permission
   classifier, so neither a local render nor a staging deploy was possible. Balance checks and
   greps are structural; **they do not prove the file parses**. Three live gates are therefore
   unrun: (a) a POST with a forged timestamp returning no 5xx and producing no notification;
   (b) a valid submission succeeding and an identical repeat inside the window being rejected,
   both with timestamps; (c) each rejection producing exactly one correlation line in the host
   error log with no submitted field value in it. Run all three the moment the code is deployed
   to `/new/`. **This is an environment boundary, not evidence of correctness.**

2. **`unrun-verify` — the `tdd="true"` RED/GREEN cycle was not performed.** With no PHP runtime
   there is no way to observe a failing test, and the executor's scope for this run was limited
   to the three named source files, so no `scripts/spam-guard-selftest.php` was authored either.
   The same shape as `scripts/upload-selftest.php` (WINDOWS entry 19) would close it: nine
   assertions matching the plan's `<behavior>` list, runnable as `php scripts/…` once a runtime
   exists.

3. **`deviation` — the signing key and the throttle records live in `sys_get_temp_dir()`.** On a
   host whose temp directory is genuinely shared between accounts, a neighbouring tenant could
   read the key or pre-create the file; the exclusive-create mode and the symlink refusal close
   the cheap version of that, not the expensive one. This is the same trust `upload.php` already
   places in that directory for a visitor's photographs. Worth one line of confirmation from the
   panel that the account has a private temp directory. Note also that a temp-dir change or a
   system reaper **rotates the key**, which makes in-flight forms verify as `forged` — they get
   the honest retry page, so the failure is visible and recoverable, not silent.

4. **`deviation` — the throttle is keyed on `REMOTE_ADDR`.** A whole office or a mobile carrier
   behind one NAT address shares a quota of one delivered enquiry per fifteen minutes. The
   message names the shop's telephone number in the same sentence, so the visitor is not left
   without a route, but the shape is worth knowing before the window is ever tightened.

5. **Carried, not closed: WINDOWS entry 14** (honeypot autofill false positive, unconfirmed).
   This plan does not close it — it moves the decoy test into a function and adds a log line, so
   a false positive now leaves a trace (`reason=honeypot`), which makes the retest cheaper to
   evaluate but does not perform it. The retest still needs one genuine handset submission with
   browser autofill explicitly on.

6. **Carried, not closed: WINDOWS entry 22** (a refused photograph costs the visitor their typed
   description). The plan assigns the error re-render to Task 3, which is blocked.

## Deviations from Plan

**1. [Rule 2 — missing critical functionality] `torin_rate_limit_record()` added as a fifth
public function.** The plan describes four. Splitting check from record is what prevents the
guard throttling a visitor who is correcting a validation error or retrying after a delivery
failure — the T-04-25 class of defect this plan is most concerned about. Found while wiring the
guard order; documented in decision 3 above. Files: `src/includes/spam-guard.php`,
`src/contact-send.php`. Commit: `e08de23`.

**2. [Rule 2] `torin_send_fail_page()` gained an optional `$status` parameter.** Needed so the
throttle branch can answer 429 rather than 422. Backwards compatible; the three existing call
sites are unchanged. Commit: `e08de23`.

**3. [Rule 3] Oversized-POST detection reordered ahead of the decoy.** Required for the time
trap to be correct at all; see decision 2. Commit: `e08de23`.

**4. Three supporting functions beyond the plan's four** (`torin_guard_dir`,
`torin_guard_secret`, `torin_rate_limit_path`). The signing key has to come from somewhere both
callers can reach without opening the credentials file, and the storage path has to have exactly
one writer. All three are small and single-purpose.

No CLAUDE.md-driven adjustments were needed: nothing in this task touches the stack table (no
package was added, no build step introduced), and the PHP-side conventions this file follows are
the ones already established in the tree.

## Threat Flags

None. This task introduces no new network endpoint, no new auth path and no new schema. It adds
one piece of server-side state — the throttle records and the signing key — which the plan's
threat register already names as a boundary ("rate-limiter flat files → filesystem outside the
web root"), and which is mitigated exactly as registered.

## Commits

- `e08de23` — `feat(04-05): spam guard — decoy, signed time trap, per-address throttle`
- (this summary)

## Self-Check: PASSED

- `src/includes/spam-guard.php` — FOUND
- `src/contact-send.php` — FOUND (modified)
- `src/includes/contact-form.php` — FOUND (modified)
- commit `e08de23` — FOUND in `git log`
- `src/vendor/` — **ABSENT, as required.** No third-party code was vendored.
