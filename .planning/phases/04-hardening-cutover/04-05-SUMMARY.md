---
phase: 04-hardening-cutover
plan: 05
subsystem: contact
tags: [spam, honeypot, hmac, rate-limit, email, smtp, sendmail, phpmailer, security]
status: complete
requires:
  - "04-02 contact spine (contact-send.php, contact-form.php, torin_send_fail_page, the hidden `t` pass-through, the any-one-wins fan-out)"
  - "04-03 upload pipeline (the outside-the-document-root temp storage precedent, and the photo paths the mail leg attaches)"
  - "kontakti.html sending Cache-Control: no-store — the time trap is unsound without it (RESEARCH P-11)"
  - "/home/torin/torin-secrets.php — optional `smtp_password`; its ABSENCE is a supported configuration, not a failure"
provides:
  - "torin_verify_honeypot($post) — decoy test that never casts a non-string to string"
  - "torin_sign_timestamp($ts, $secret) — canonicalised unix time joined to a keyed SHA-256 HMAC"
  - "torin_verify_timestamp($field, $secret, $minAge, $maxAge) — hash_equals, then both age bounds; returns a reason string"
  - "torin_rate_limit_ok($ip, $secret, $limits) — read-only per-address throttle check"
  - "torin_rate_limit_record($ip, $secret, $limits) — writes a record for a DELIVERED enquiry only"
  - "torin_guard_secret() / torin_guard_dir() / torin_rate_limit_path() — private storage and the signing key"
  - "torin_notify_mail($payload, $photos, $secrets) — the second channel, behind the unchanged fan-out interface"
  - "torin_send_mail($spec, $secrets) — SMTP-with-fallback-to-sendmail; returns the transport name or false"
  - "torin_mail_attempt($spec, $password, $useSmtp) — one attempt, returns 'sent' | 'accepted' | 'pre'"
  - "torin_values() — the five text fields, read for repopulation rather than validation"
  - "torin_send_fail_page($summary, $errors, $status = 422, $values = []) — now re-renders the real form"
affects:
  - "04-07 (msg.html carries a $torin_robots assignment that nothing reads yet — that plan's emitter is what activates it)"
  - "04-08 sitemap (if 04-07 slips, msg.html ships without its noindex and must not be listed)"
  - "04-09/04-10 cutover (the signing key and throttle records live in the system temp directory; src/vendor/ is a NEW directory the deploy must create)"
  - "uslovia.html (the confirmation email is a new processing purpose the privacy copy should name)"
tech-stack:
  added:
    - "PHPMailer 7.1.1 — three files, hand-vendored, no package manager"
  patterns:
    - "RESEARCH 'Don't Hand-Roll' — hash_hmac for the token, hash_equals for the comparison, no polyfill"
    - "RESEARCH P-11 — the trap is coupled to the no-store header on the contact page"
    - "RESEARCH A-3 — the dialect quarantine: the mail library is reachable from exactly one file"
    - "RESEARCH C-2 — host relay on port 25, auth on, encryption off, SMTPAutoTLS explicitly false, UTF-8"
    - "upload.php:178-194 — private state lives in the system temp directory, never under the deployed tree"
    - "UI-SPEC C-6 — plausible decoy name, autocomplete off, ~3s lower bound, ~2h upper bound"
    - "UI-SPEC C-8 — one form partial, two callers; consent renders unchecked on every render"
key-files:
  created:
    - src/includes/spam-guard.php
    - src/vendor/phpmailer/PHPMailer.php
    - src/vendor/phpmailer/SMTP.php
    - src/vendor/phpmailer/Exception.php
    - src/vendor/phpmailer/.htaccess
    - scripts/notify-selftest.php
  modified:
    - src/contact-send.php
    - src/includes/notify.php
    - src/includes/contact-form.php
    - src/msg.html
decisions:
  - "PHPMailer pinned at 7.1.1 by the owner, over CLAUDE.md's 6.x line — an explicit, recorded deviation"
  - "sendmail is the PRIMARY transport by owner decision; authenticated SMTP is attempted first only when a credential exists — a recorded deviation from CONTACT-03's literal wording"
  - "The SMTP→sendmail fall-through fires only on PRE-ACCEPTANCE failure, and the boundary is placed by observing the relay's 354 reply rather than by parsing an exception message"
  - "Ambiguity at the boundary resolves to DO NOT RETRY: one lost enquiry the visitor is told about beats one duplicated enquiry nobody is told about"
  - "A per-request circuit breaker stops a dead relay stalling the visitor twice in one submission"
  - "The customer confirmation is sent from the SUCCESS branch, not from the mail driver — it must follow 'the enquiry reached the shop', not 'the mail channel worked'"
  - "Every failure branch now re-renders the real form with the visitor's text; only the oversized POST cannot, because the engine discarded the fields before PHP ran"
  - "No CAPTCHA and no visible challenge, recorded under the Claude's-Discretion grant so it is not revisited casually"
  - "The throttle records DELIVERED enquiries, not POSTs"
  - "The signing key is minted by spam-guard.php in its own private storage rather than read from the credentials file"
metrics:
  duration: "two sessions"
  completed: 2026-09-21
  tasks_completed: 3
  tasks_total: 3
actuals:
  tokens: 86500
  tasks: 3
  commits: 5
---

# Phase 04 Plan 05: Spam Defence and the Email Leg — Summary

**COMPLETE. 3 of 3 tasks.** A honeypot, an HMAC-signed time trap and a per-address throttle stand
above field validation and above every outbound call; a second delivery channel sends the enquiry
by authenticated SMTP or by the local sendmail binary, whichever is up; the customer gets a
confirmation; and every failure branch now hands the visitor back a working form with their own
text still in it.

This file supersedes the partial summary written after Task 2. Task 2's content is carried
forward below rather than restated in a link, because the plan is one unit and the guard order
is what the mail leg was wired on top of.

| Task | State | Commit |
|---|---|---|
| 1 — package legitimacy gate + mailbox credential | **RESOLVED BY THE OWNER, 2026-09-21** | n/a (a checkpoint, not code) |
| 2 — spam defence + oversized-POST blind spot | **COMPLETE** | `e08de23` |
| 3 — the email leg, the confirmation, the failure states | **COMPLETE** | `9581a8b`, `b31c872` |

---

## Task 1 — the gate, and what the owner actually decided

This was a `gate="blocking"` human-verify checkpoint and it was **not** auto-approved. The owner
answered it directly on 2026-09-21. Both answers are recorded here as *their* decisions, with the
evidence that was put in front of them, because both are deviations from written project
documents and neither should later look like an executor's drift.

### Part A — the version is pinned at 7.1.1, by the owner

Evidence presented and verified against both upstream sources on 2026-09-21:

| Check | Reading |
|---|---|
| `github.com/PHPMailer/PHPMailer` exists, not archived, actively developed | yes — ~22,000 stars |
| Dependent packages | 1,645 |
| `packagist.org/packages/phpmailer/phpmailer` abandoned? | **no** — auto-updated, last updated 2026-09-20 |
| Vendor / maintainer | "PHPMailer", primary maintainer Synchro |
| Licence | LGPL-2.1-only |
| `v7.1.1` a real tagged release | yes — current latest stable, 2026-05-18 |
| Packagist download count | **115,064,177** |

**The download count is recorded honestly rather than as a met criterion.** The gate's own wording
asked for "hundreds of millions". 115,064,177 is over one hundred million; it is *not* hundreds of
millions. The owner judged it overwhelmingly sufficient and pinned the version. It is written that
way here so that nobody later reads the criterion back as satisfied verbatim — it was not, and the
gate was passed on judgement rather than on the number clearing the bar as phrased.

Two further facts informed the choice and are recorded because they cut in opposite directions:
the package carries **14 historical security advisories**, and v6.12.0's own release notes state
it is *"exactly the same as 6.10.0, reverting everything released in 6.11.0 and 6.11.1"* — which
is what made the maintained 7.x line the less surprising of the two.

**CLAUDE.md deviation, explicit.** The project stack table names PHPMailer **6.x**. This ships
**7.1.1**, on the owner's instruction. CLAUDE.md's stated justification for 6.x — that the host
might be stuck on `php >= 5.5` — is **void**: `04-HOST-CAPABILITIES.md` measured the staging
subtree at **PHP 8.5.10**, and 7.1.1 declares the same `php >= 5.5.0` floor and the same three
extensions (`ctype`, `filter`, `hash`) as 6.x anyway, all three of which the host has.

### Part B — sendmail is the primary path, by owner decision

`office@torin.bg` is **confirmed a real mailbox**, not a forwarder. The owner nonetheless chose
the credential-free local binary as the primary path, after being shown that:

- deliverability does not distinguish the two options — both leave the same machine and are
  covered by the same published SPF (`+a`, `+ip4:217.174.156.170`) and the same
  `default._domainkey.torin.bg` DKIM key;
- a **sendmail bounce lands in the owner's own inbox, where a human sees it**, whereas an SMTP
  error code lands in an `error_log` nobody reads.

Also confirmed by the owner the same day: the three live test emails from quick task `260919-m0i`
**did arrive** at `office@torin.bg`. The live host demonstrably sends mail that reaches an inbox.

**CONTACT-03 deviation, explicit.** The requirement names *authenticated SMTP* literally. What
ships is **both**: the authenticated SMTP path is fully built and is attempted first whenever a
`smtp_password` is present, so the capability exists and is reachable — but it is not the default,
because no credential has been placed in the secrets file. The requirement is met in capability
and not as the default path, by owner decision.

---

## Task 2 — the spam guard (carried forward)

### `src/includes/spam-guard.php` (new)

PHP 5.2-safe dialect, functions and no data, emits nothing on include. Eight functions — the four
the plan names, plus four supporting ones:

| Function | Role |
|---|---|
| `torin_verify_honeypot($post)` | True when the decoy is clean. A non-string decoy value is a rejection and is **never cast to string** — casting an array raises a warning which, with `display_errors` still On in this subtree, would print into the response before the caller's `header()` call. |
| `torin_sign_timestamp($ts, $secret)` | `(string)(int)$ts . '.' . hash_hmac('sha256', …)`. The canonicalisation is what lets the form pass an int and the handler re-sign a numeric string without disagreeing. |
| `torin_verify_timestamp($field, $secret, $min, $max)` | Splits, re-signs, compares with **`hash_equals`**, then checks both bounds. Returns `'ok' \| 'malformed' \| 'forged' \| 'too-fast' \| 'stale'`. |
| `torin_rate_limit_ok($ip, $secret, $limits)` | Read-only. Counts records inside the window. **Fails open**, with a log line. |
| `torin_rate_limit_record($ip, $secret, $limits)` | Writes one record under `LOCK_EX`; prunes expired entries; sweeps its own prefix one run in twenty. |
| `torin_guard_dir()` | The system temp directory, checked rather than assumed, or `''`. |
| `torin_guard_secret()` | Mints a 32-byte key once via `random_bytes`, exclusive-create, `chmod 600`, symlink refused; caches it per request. |
| `torin_rate_limit_path($ip, $secret)` | `sha256($ip . '\|' . $secret)` — the address is hashed, never stored. |

**Storage is outside the document root**, in `sys_get_temp_dir()`, matching the placement
`includes/upload.php:178-194` chose for a re-encoded photograph and for the same reason: this host
maps `.html` to the PHP handler, so any intact file in a web-reachable folder is a code-execution
surface (D4-15). `grep -c 'public_html' src/includes/spam-guard.php` → **0**.

### The guard order in `src/contact-send.php`

Line numbers below are the file **as it stands after Task 3**, re-measured rather than carried
over from the partial summary:

```
82   require_once …/includes/spam-guard.php
115  $torin_len = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);   ← oversized-POST, FIRST
165  if (!torin_verify_honeypot($_POST)) {                   ← decoy
193  $torin_ts_verdict = torin_verify_timestamp(             ← signed time trap
245  if (!torin_rate_limit_ok($torin_ip, $torin_secret, $torin_rl)) {   ← throttle
280  $torin_errors['device'] = 'Моля, попълнете полето.';    ← first field validation
418  $torin_result = torin_notify($torin_in, $torin_photos, $torin_secrets);
```

Every guard reference is on a **lower line number** than the first field validation (280) and than
`torin_notify(` (418), which is the plan's line-order acceptance criterion. Task 3 added ~370
lines below the guard and did not move any of it.

### `src/includes/contact-form.php`

`$torin_t = time();` became `$torin_t = torin_sign_timestamp(time(), torin_guard_secret());`. The
hidden field stops being the tracer's pass-through and carries a real value.

### Task 2's own judgement calls (unchanged)

1. **No CAPTCHA, no visible challenge** — a widget reintroduces the third-party-cookie problem
   D4-18 was chosen to avoid, costs conversions on the one form this business depends on, is
   third-party payload against DESIGN-02, and the actual threat is commodity form spam.
2. **Oversized-POST detection moved ahead of the decoy** — an emptied body also has no `t` field,
   so a visitor whose photographs exceeded the ceiling would otherwise be judged a forger.
3. **The throttle counts DELIVERED enquiries, not POSTs** — window 900 s, max 1. Counting every
   POST would throttle the visitor correcting a typo ten seconds later.
4. **Only «too-fast» is absorbed silently** — «forged», «malformed» and «stale» each have a
   mundane non-bot cause and get the honest page.
5. **The signing key is minted locally**, not read from the credentials file, because
   `contact-form.php` is page chrome and `site-config.php` reserves that file for
   `contact-send.php` alone (T-04-26).
6. **`torin_verify_timestamp` returns `'ok'`, not `''`** — under a future `if (!f())` misuse,
   `'ok'` loses spam and `''` would lose every customer.
7. **`torin_send_fail_page()` gained `$status = 422`** so the throttle can answer 429.
8. **No `function_exists` guard around `hash_hmac`/`hash_equals`** — `ext-hash` stopped being
   removable in PHP 7.4. `random_bytes` **is** guarded.

---

## Task 3 — the email leg

### The vendored library, and its checksums

Downloaded from the **tagged release URL**, not a search result and not a fork:

```
https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v7.1.1.tar.gz
tarball sha256  6c8210a20f2bdca42f398ea688042a17c572a52369449e80f5bf6ec7fbfce3e5
```

`PHPMailer::VERSION` in the copied file reads `'7.1.1'` — checked in the vendored copy, not in the
release notes. `composer.json` in the same tarball declares `php >= 5.5.0` and `ext-ctype`,
`ext-filter`, `ext-hash`, all three measured present on the host.

**The three digests. There is no lockfile in this project, so this record IS the lockfile
(T-04-24).** Re-assert them before trusting any later copy:

```
22ab858ae438d98f58f41f38ad2191d1b0d59570aebea0463a7948cfae1021b7  src/vendor/phpmailer/Exception.php
45599a196ae7944ee2dcd4f3d3da0ac4243513d346b2d77bbd15dbd0c37f7064  src/vendor/phpmailer/PHPMailer.php
522bcf0d07be7e7e00114711db5c9ce2b4d59ac041c5ec36eaadd979d5fa7046  src/vendor/phpmailer/SMTP.php
```

The other four files in the library's `src/` tree (`DSNConfigurator.php`, `OAuth.php`,
`OAuthTokenProvider.php`, `POP3.php`) are **not** needed for sending and were not copied.
`scripts/deploy-new.sh` strips comments from `.css` only, so the bytes on the server are the bytes
in this repository and these digests are checkable there too.

### The quarantine holds

| Direction | Command | Result |
|---|---|---|
| Nothing in `includes/` reaches the library | `grep -rn 'vendor/phpmailer' src/includes/` | **no output** |
| Exactly three crossings in the one permitted file | `grep -n 'vendor/phpmailer' src/contact-send.php` | **3 lines — 840, 841, 842, all `require_once`** |

Both took an edit to achieve, and the edit is worth naming: the first drafts of the explanatory
comments in `notify.php` and `contact-send.php` *spelled* the banned path while explaining that it
must not appear — which tripped the very gate they described. They now describe it without writing
it, on exactly the discipline `brand-row.php:34-36` and `contact-send.php:34-41` already apply to
other banned literals. A comment that fails its own gate teaches the next person to weaken the
gate.

The three `require_once` calls are **inside `torin_send_mail()`**, not at the top of the file: a
bot stopped by the spam guard never parses 250 KB of library, and the quarantine's blast radius
shrinks from "any POST to this endpoint" to "a POST that had something to deliver".

### The transport cascade, and the pre/post-acceptance boundary

The owner's requirement, in their words: **email must send even when authentication is impossible
or fails.** A missing or wrong `smtp_password` must never cost the business an enquiry.

```
credential present?  ──no──▶ sendmail directly (no connection, no timeout paid)
        │yes
        ▼
  SMTP torin.bg:25, auth on, encryption off, SMTPAutoTLS false, UTF-8, 15 s
        │
        ├─ sent                      ──▶ 'smtp'
        ├─ failed BEFORE acceptance  ──▶ fall through to sendmail
        └─ failed AFTER  acceptance  ──▶ FALSE. never retried.
```

**Where the boundary is drawn, and how it is detected.** PHPMailer raises one exception class for
every failure and localises its text, so a string match would silently stop matching the day the
library or the language changes. Instead the SMTP conversation is watched for the relay's **`354`
"start mail input"** reply — the exact instant it begins taking the message:

```php
$m->SMTPDebug   = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
$m->Debugoutput = function ($str, $level) use (&$accepted) {
    if (strpos($str, 'SERVER -> CLIENT: 354') === 0) { $accepted = true; }
};
```

The prefix is anchored at position 0 and includes the literal PHPMailer writes before every server
reply (`SMTP.php:380`, `:1148`), so a `354` inside a message id or a banner cannot trip it.

| Treated as **pre-acceptance** (fall through to sendmail is safe) | Treated as **post-acceptance** (never retried) |
|---|---|
| No credential at all — no connection attempted | Anything at all, once `SERVER -> CLIENT: 354` has been observed |
| `smtp_connect_failed` — connection refused, DNS failure, TLS failure | A failure inside `SMTP::data()` after the 354, including `data_not_accepted` |
| Auth rejected (`SMTP Error: Could not authenticate`) | The terminating dot written and the connection dying before the reply arrives — **genuinely ambiguous, and resolved conservatively** |
| `MAIL FROM` refused | Any `Throwable` raised after the 354 |
| `RCPT TO` refused / `recipients_failed` | |
| Connect or command timeout before `DATA` | |
| Any `Throwable` raised before the 354 (including a pre-send validation error) | |

**The default, when there is no evidence either way, is "do not retry" for everything from the 354
onwards.** Not retrying risks losing one enquiry — and the visitor is *told* about that on the
failure page and can act on it. Retrying risks silently duplicating it, which nobody is told about
at all, and which the owner cannot distinguish from a customer who wrote twice.

**Watching the conversation is safe.** `SMTP::client_send()` replaces the AUTH payload with the
literal `[credentials hidden]` at every debug level below `DEBUG_LOWLEVEL` (`SMTP.php:1240-1251`);
this runs at `DEBUG_SERVER`, two levels below it, so the mailbox password cannot reach the
callback. The callback also stores nothing, logs nothing and returns nothing — it sets one boolean.

**What is logged, on every send, with the existing correlation id:**

```
torin contact <rid>: mail delivered via=smtp
torin contact <rid>: mail delivered via=sendmail
torin contact <rid>: mail no smtp credential, using sendmail
torin contact <rid>: mail smtp failed before acceptance, falling through to sendmail
torin contact <rid>: mail smtp failed AFTER the relay accepted — not retried, message may be queued
torin contact <rid>: mail smtp already failed this request, going straight to sendmail
torin contact <rid>: mail sendmail failed
torin contact <rid>: confirmation to the customer failed
torin contact <rid>: all notification channels failed (telegram=fail mail=fail)
```

Without the `via=` line nobody can tell which path is live. None of these carries a submitted
value; channel and transport names are developer-authored constants (P-14).

**On `04-HOST-CAPABILITIES` BLOCKER 2.** Its `smtp:localhost:25 FAIL Connection refused` reading
rules out a **local MTA listening on a socket**. It says nothing about the sendmail *binary*, which
is piped to rather than connected to and is confirmed present at `/usr/sbin/sendmail` with
`sendmail_path = '/usr/sbin/sendmail -t -i'` (`04-HOST-CAPABILITIES.md:94, :98`) — and nothing
about the relay, which is a different host. Outbound 25/26/465 to *external* servers are blocked
by SuperHosting, so that relay is the only SMTP option there is.

### The sender, and the anti-analog

```php
$torin_m->setFrom('office@torin.bg', 'ТОРИН КОМПЮТЪРС');
```

A quoted literal, not a variable derived from the post array (T-04-22). Written as a literal rather
than read from `$site['email']` on purpose: the same address is the mailbox the SMTP leg
authenticates as, and deriving it from an editable config value would let a future edit to the
displayed contact address silently break authentication, in a place nobody would look. The
visitor's address — already through `FILTER_VALIDATE_EMAIL` — reaches `addReplyTo()` and no other
header. The visitor's *name* is deliberately not passed as the reply-to display name: it is not
validated the way the address is, and it is already one line below in the body.

`grep -nE '(^|[^A-Za-z0-9_$>])mail[[:space:]]*\(' src/contact-send.php` → **no output**. There is
no bare `mail()` call anywhere in the file.

### The customer confirmation (D4-09) — and where it lives

Sent from the **success branch of `contact-send.php`**, not from inside the mail driver. That
placement is the substantive decision:

- inside the driver it would fire whenever the *email channel* worked, which is a different
  question — and it would **not** fire when Telegram alone carried the enquiry, so a visitor whose
  enquiry arrived perfectly well would get no confirmation because of which internal channel
  happened to be up;
- in the success branch it fires exactly when the enquiry **reached the shop by any route**, which
  is what "получихме запитването ви" actually claims. Sending it on a failed submission would be
  this plan's stated prohibition in its most convincing possible form — a false "we got it" landing
  in the visitor's inbox.

Its own failure is logged and dropped. The enquiry has already been delivered; refusing to redirect
because a courtesy message bounced would turn a success into a visible failure. No attachment: the
visitor already has the photographs, and returning them costs them mobile data and risks the spam
folder. Hours, phone and address in the body are composed from `$site`, never typed.

### The error re-render — WINDOWS 22, closed

`torin_send_fail_page()` gained a fourth parameter, `array $values = []`, and its body now renders
the **real form** through `torin_render_contact_form($values, $errors)`. One partial, two callers —
the contract `contact-form.php:6-15` has stated since 04-02, now actually exercised.

| Branch | Values repopulated? |
|---|---|
| Oversized POST | **No, and cannot be** — PHP discarded `$_POST` before this file ran |
| Timestamp forged / malformed / stale | Yes, via `torin_values()` |
| Rate limited (429) | Yes, via `torin_values()` |
| Field validation failed | Yes, via `$torin_in` — the array validation actually ruled on |
| **Photographs refused** | **Yes — this is WINDOWS 22** |
| Secrets file missing or malformed | Yes |
| All channels failed | Yes |

Two consequences worth naming:

- **The in-page links became bare fragments** (`#device`, not `kontakti.html#device`). That is not
  tidying: the old href navigated *away* from the populated re-render to an empty form, throwing
  away the very values this change exists to preserve.
- **The stale-token copy changed with the page.** It used to say «отворете страницата наново»
  because the page it led to had no form on it. It now says the text is preserved and to try again
  — a stale token is the likeliest non-bot cause of that branch and a re-render with a freshly
  minted `t` repairs it in one click.

The consent box still renders unchecked on every render — `contact-form.php` does not read
`$values` for it at all, and that omission is the feature. Photographs are not carried back and
cannot be: a file input's value is not settable from markup in any browser, and a server-side
upload cache is exactly what D4-07 promises this site does not keep.

### `src/msg.html`

Adds the confirmation-email sentence and a photographs sentence, both phrased so the page asserts
nothing about a reader who arrived at the URL directly — it is a plain URL anyone can open, and it
is also where two deliberately-indistinguishable spam rejections land. `$torin_robots =
'noindex, follow'` is set; **nothing reads it yet** (see Known Gaps).

### `scripts/notify-selftest.php` — WINDOWS 15, addressed but not discharged

Nine assertions that drive the **all-channels-failed** branch with **no network call at all**: both
drivers refuse before reaching the wire when handed an empty secrets array — Telegram on the
missing token, mail on the `function_exists('torin_send_mail')` guard — so the aggregation is
reached with two honest `false` verdicts. It also asserts that including `notify.php` alone
declares no library class, which is the runtime consequence of the quarantine that a grep cannot
see.

**It has never been run.** See Known Gaps 1. The one-channel-down case and the SMTP→sendmail
cascade are not in it and cannot be — both need a channel that is genuinely up.

---

## Verification Performed

Precondition for Task 2 checked read-only and **met**: `src/kontakti.html:41` sends
`Cache-Control: no-store`.

Precondition for Task 3 — «the secrets file carries `smtp_password`, **or** Task 1 recorded the
decision to use the local sendmail binary instead» — **met by the second clause**, by owner
decision.

| Gate | Result |
|---|---|
| `src/vendor/phpmailer/` contains exactly three files (`ls \| wc -l` = 3) | **PASS** |
| `shasum -a 256 src/vendor/phpmailer/*.php` matches the tarball byte-for-byte | **PASS** (three digests above) |
| `PHPMailer::VERSION` in the vendored copy = `'7.1.1'` | **PASS** |
| `grep -rn 'vendor/phpmailer' src/includes/` | **PASS** (no output) |
| `grep -n 'vendor/phpmailer' src/contact-send.php` = 3 require lines | **PASS** (840-842) |
| `grep -c 'setFrom' src/contact-send.php` ≠ 0 | **PASS** (1, a quoted literal) |
| non-comment `addReplyTo` occurrences ≠ 0 | **PASS** (1) |
| zero bare `mail(` calls | **PASS** (no output) |
| `grep -c 'hash_hmac' src/includes/spam-guard.php` ≠ 0 | **PASS** (2) |
| `grep -c 'hash_equals' src/includes/spam-guard.php` ≠ 0 | **PASS** (3) |
| non-comment `session_start` occurrences = 0 | **PASS** |
| `grep -c 'CONTENT_LENGTH' src/contact-send.php` ≠ 0 | **PASS** |
| `grep -c 'public_html' src/includes/spam-guard.php` = 0 | **PASS** |
| Guard line order vs first validation (280) and `torin_notify(` (418) | **PASS** |
| Brace / paren / bracket balance, comments and strings stripped, all six PHP files | **PASS** (0/0/0 each) |
| `deploy-new.sh` will create `vendor/phpmailer/` and upload its dotfile | **PASS** by inspection — `find . -type f` includes dotfiles, `CURL_BASE` carries `--ftp-create-dirs`, and only `.css` is comment-stripped |
| Live POST with a forged timestamp returns no 5xx | **NOT RUN** — Known Gap 1 |
| Live POST at a valid interval, then a throttled repeat | **NOT RUN** — Known Gap 1 |
| Live submission with both channels up → notification **and** email, 303 | **NOT RUN** — Known Gap 1 |
| Live submission with a deliberately broken mail credential → still 303 | **NOT RUN** — Known Gap 1 |
| Live submission with both channels broken → 200, values preserved, phone present | **NOT RUN** — Known Gap 1 |
| An SMTP auth failure actually observed falling through to sendmail | **NOT RUN** — Known Gap 2 |
| The customer confirmation arriving at the submitted address | **NOT RUN** — Known Gap 1 |
| `curl https://torin.bg/new/msg.html` free of PHP warnings | **NOT RUN** — Known Gap 1 |
| `php scripts/notify-selftest.php` | **NOT RUN** — Known Gap 1 |
| `php scripts/upload-selftest.php` (WINDOWS 19) | **NOT RUN** — unchanged |

---

## Known Gaps

Stated as gaps, **not as passes**. The orchestrator should file them in `WINDOWS.md`; this executor
did not write that shared ledger.

1. **`unrun-verify` — nothing in this plan has been executed by a PHP interpreter, in either
   direction.** There is no `php` binary on the build machine and the Docker daemon is not running
   (`Cannot connect to the Docker daemon at unix:///Users/alabala/.docker/run/docker.sock`);
   `scripts/deploy-new.sh` is denied to subagents by the permission classifier. Greps and the
   brace-balance smoke test are **structural — they do not prove the files parse.** Every live gate
   in the table above is therefore unrun and must be run the moment this reaches `/new/`. **This is
   an environment boundary, not evidence of correctness.**

2. **`unrun-verify` — the cascade's most important branch has never been observed.** The
   SMTP→sendmail fall-through is the functional heart of Task 3 and **no real authentication
   failure has been watched falling through to sendmail.** It cannot be simulated here: it needs a
   live host, a relay to fail against, and a deliberately wrong credential. The recipe, for whoever
   has the host:
   - **(a) no credential** — the shipped state. Submit the form. Expect
     `mail no smtp credential, using sendmail` then `mail delivered via=sendmail`.
   - **(b) wrong credential** — add `'smtp_password' => 'definitely-wrong'` to
     `/home/torin/torin-secrets.php`. Submit. Expect
     `mail smtp failed before acceptance, falling through to sendmail` then
     `mail delivered via=sendmail`, a 303, and the email to arrive. **Exactly one copy.**
   - **(c) correct credential** — expect `mail delivered via=smtp` and no fall-through line.
   - In **all three** the enquiry must arrive **once**. Two copies means the boundary is wrong and
     is the one outcome this design is built to prevent.

3. **`unrun-verify` — the all-channels-failed page is still unproven at runtime (WINDOWS 15,
   carried forward, not closed).** `scripts/notify-selftest.php` now encodes that branch and can
   run without a network, but it has never executed. To prove it live: point
   `telegram_bot_token` at a bad value **and** make the mail leg fail, submit, and check the page
   returns **200** (not 5xx), shows the error band, **preserves the typed values**, renders consent
   **unchecked**, contains `02 9549710`, and produces exactly one
   `all notification channels failed (telegram=fail mail=fail)` line carrying no submitted value.

4. **`unrun-verify` — the `tdd="true"` RED/GREEN cycle was not performed for either task.** With no
   PHP runtime there is no way to observe a failing test. Both self-tests in this project
   (`upload-selftest.php`, `notify-selftest.php`) are **specifications, not gates**, and are
   labelled as such in their own headers. No `spam-guard-selftest.php` was authored at all.

5. **`deviation` — `$torin_robots` on `msg.html` is inert.** The emitter ships in **04-07**;
   `header.php` does not read the variable yet. The assignment is in place so 04-07's emitter finds
   it. **If 04-07 slips, `msg.html` ships without its noindex and must not be listed in the
   sitemap** — 04-08 owns that.

6. **`deviation` — the error band is focusable but nothing focuses it.** UI-SPEC C-8 asks for
   `tabindex="-1"` **plus** a programmatic `.focus()` on render. The attribute and the `id`
   (`#form-error`) ship here; the `.focus()` call belongs to `js/analytics.js`, which this plan does
   not touch. A keyboard or screen-reader user currently lands at the top of the document rather
   than on the explanation.

7. **`deviation` — the UI-SPEC C-9 `form-error` analytics event is not emitted** by the re-render.
   It is a server-rendered event with a `field` property; 04-04 owns `js/analytics.js` and this plan
   changed no analytics.

8. **`deviation` — `uslovia.html` does not yet mention the confirmation email.** A new automated
   message to the visitor's address is a processing purpose the privacy copy should name. Out of
   this plan's file list.

9. **`deviation` — the signing key and the throttle records live in `sys_get_temp_dir()`.** On a
   host whose temp directory is genuinely shared between accounts, a neighbouring tenant could read
   the key or pre-create the file; the exclusive-create mode and the symlink refusal close the cheap
   version of that, not the expensive one. A temp-dir change or a system reaper **rotates the key**,
   which makes in-flight forms verify as `forged` — they get the honest retry page, so the failure
   is visible rather than silent.

10. **`deviation` — the throttle is keyed on `REMOTE_ADDR`.** A whole office or a mobile carrier
    behind one NAT address shares a quota of one delivered enquiry per fifteen minutes. The message
    names the shop's phone in the same sentence.

11. **Carried, not closed: WINDOWS 14** (honeypot autofill false positive, unconfirmed). This plan
    does not close it. The retest still needs one genuine handset submission with browser autofill
    explicitly on.

12. **`unrun-verify` — `src/vendor/phpmailer/.htaccess` is unverified over HTTP.** After deploy,
    `curl -s -o /dev/null -w '%{http_code}' https://torin.bg/new/vendor/phpmailer/PHPMailer.php`
    should return **403**. If it returns 200 the deny block did not apply and the library is
    fetchable — harmless today (the files only declare classes) but an unnecessary disclosure.

13. **Operational, not a defect: two messages leave per submission** (owner notification +
    customer confirmation), each on its own connection. A per-request circuit breaker stops a dead
    relay stalling the visitor twice, but total request latency on a bad day is still the Telegram
    timeout plus one SMTP timeout plus two sendmail invocations. Worth one look at the live timings
    once the form is in use.

---

## Deviations from Plan

**1. [Owner decision] PHPMailer 7.1.1 instead of CLAUDE.md's 6.x.** Full evidence and reasoning in
Task 1 above, including the honest reading of the 115,064,177 download count. Files:
`src/vendor/phpmailer/*`. Commit `9581a8b`.

**2. [Owner decision] sendmail is the primary transport, SMTP the attempted-first-when-possible
path.** A recorded deviation from CONTACT-03's literal "authenticated SMTP" wording. The capability
is built and reachable; it is not the default because no credential is in the secrets file. Commit
`b31c872`.

**3. [Rule 2 — missing critical functionality] The transport cascade itself.** The plan describes a
single SMTP transport. The owner required that a missing or wrong password must never cost an
enquiry, so `torin_send_mail()` falls through to sendmail — with the pre/post-acceptance boundary
that stops the fall-through duplicating a message the relay already took. Commit `b31c872`.

**4. [Rule 2] A per-request SMTP circuit breaker.** Not in the plan. Without it, a submission that
sends two messages pays the 15-second connect timeout **twice** against a relay already proven down
this request, with the visitor sitting on a submitted form. In-memory, per request, not persisted —
a persisted breaker would need its own expiry, its own storage outside the web root and its own
failure mode. Commit `b31c872`.

**5. [Rule 2] `src/vendor/phpmailer/.htaccess`.** Not in the plan. The library files are fetchable
by URL once deployed; they declare classes and produce an empty 200, which still confirms the
library and its path to a scanner, and a future lost handler mapping would serve the version
constant as plain text. **It does not break the plan's three-file check** — that check uses `ls`,
which does not list dotfiles. Commit `9581a8b`.

**6. [Rule 2] The owner notification carries the photographs as attachments.** The plan specifies
"no attachment" for the **customer confirmation** only, and is silent on the owner's copy. Under
D4-08 the email leg may be the only surviving channel, and an enquiry about a cracked screen that
arrives without the photograph of the cracked screen is a degraded lead — the same reading
`notify.php` already applies ("a lead delivered incompletely beats a lead not delivered"). Filenames
are generated (`torin-0.jpg`); the visitor's own filename never travels. Commit `b31c872`.

**7. [Rule 2] `torin_values()` added, and every failure branch repopulated — not only the two the
plan names.** The plan assigns the re-render to the overall-failure branch. A visitor rejected by
the time trap or the throttle has typed exactly as much as one rejected by validation, and losing
it costs them exactly the same. Commit `b31c872`.

**8. [Rule 2] The all-channels-failed log line now names the per-channel verdict.** With two
channels, "all notification channels failed" no longer says whether they failed for one shared
reason or two different ones. It is a diagnostic on the failure branch only; the success test still
reads the overall flag and nothing else (D4-08). Commit `b31c872`.

**9. [Rule 3] Two explanatory comments rewritten because they tripped their own gate.** The drafts
spelled the vendor path while explaining that it must not appear under `src/includes/` or more than
three times in `contact-send.php`. Both now describe the path without writing it. Commit `b31c872`.

**10. [Rule 2] `torin_rate_limit_record()` added as a fifth public function** (Task 2). The plan
describes four. Splitting check from record prevents throttling a visitor who is correcting a
validation error or retrying after a delivery failure — the T-04-25 class of defect this plan cares
most about. Commit `e08de23`.

**11. [Rule 2] `torin_send_fail_page()` gained `$status`** (Task 2), so the throttle can answer 429
rather than 422. Commit `e08de23`.

**12. [Rule 3] Oversized-POST detection reordered ahead of the decoy** (Task 2). Required for the
time trap to be correct at all. Commit `e08de23`.

**13. Three supporting guard functions beyond the plan's four** (Task 2) — `torin_guard_dir`,
`torin_guard_secret`, `torin_rate_limit_path`. Commit `e08de23`.

**CLAUDE.md-driven adjustments:** one, and it is deviation 1 — the stack table's PHPMailer 6.x row.
It is overridden by explicit owner instruction, recorded above with the evidence, and CLAUDE.md's
stated justification for 6.x (a possible PHP 5.5 floor) is void against a measured PHP 8.5.10. No
package manager was added; the project still has no `package.json`, no `composer.json` and no
lockfile, so CLAUDE.md's "no build step on the host" constraint is untouched. Everything else in
this plan follows the PHP conventions already established in the tree.

---

## Threat Flags

| Flag | File | Description |
|------|------|-------------|
| threat_flag: outbound-credential | `src/contact-send.php` | A second outbound network path with an authenticating credential, to `torin.bg:25` **unencrypted**. The plan's register names the boundary (`contact-send.php → host SMTP relay`) and the password is handled per T-04-26. What is new and not in the register: the plaintext-on-the-wire property. It is the host's documented configuration and a same-machine hop, but it is worth a line in the phase's security review rather than being left implicit. |
| threat_flag: local-command-execution | `src/contact-send.php` | The sendmail path **pipes to a local binary** via `popen`, which the register does not describe — it assumed SMTP only. The command comes from the host's own `sendmail_path` ini value via PHPMailer's `isSendmail()`, never from anything submitted, so no user input reaches a shell. Recorded because "the handler now executes a local process" is a category the register did not previously contain. |
| threat_flag: new-web-reachable-path | `src/vendor/phpmailer/` | A new directory of third-party code under the document root. Mitigated by the per-directory deny (`.htaccess`) and pinned by the three digests above — **but the deny is unverified over HTTP** (Known Gap 12). |

---

## Commits

- `e08de23` — `feat(04-05): spam guard — decoy, signed time trap, per-address throttle`
- `126b806` — `docs(04-05): partial summary — Task 2 done, plan blocked on Task 1 gate`
- `9581a8b` — `chore(04-05): vendor PHPMailer 7.1.1 by hand — three files, digests recorded`
- `b31c872` — `feat(04-05): the email leg, the customer confirmation, and every failure state`
- (this summary)

## Self-Check: PASSED

- `src/includes/spam-guard.php` — FOUND
- `src/vendor/phpmailer/PHPMailer.php` — FOUND, sha256 matches the record above
- `src/vendor/phpmailer/SMTP.php` — FOUND, sha256 matches
- `src/vendor/phpmailer/Exception.php` — FOUND, sha256 matches
- `src/vendor/phpmailer/.htaccess` — FOUND
- `scripts/notify-selftest.php` — FOUND
- `src/contact-send.php` — FOUND (modified)
- `src/includes/notify.php` — FOUND (modified)
- `src/includes/contact-form.php` — FOUND (modified in Task 2)
- `src/msg.html` — FOUND (modified)
- commits `e08de23`, `9581a8b`, `b31c872` — FOUND in `git log`
- `ls src/vendor/phpmailer | wc -l` = 3 — the four unneeded library files were **not** copied
