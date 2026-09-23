---
quick_id: 260923-ebg
slug: record-the-live-end-to-end-contact-form-proof
date: 2026-09-23
description: The owner submitted the real form and all three channels delivered. Record it with log evidence, close ledger 42, narrow ledger 38, and state plainly what it does NOT close.
---

# The delivery cascade has run for real

## What happened

The owner submitted the contact form on staging and reports: Telegram message arrived, the email
to the shop arrived, and the customer confirmation email arrived.

This is the one class of evidence no automated check in this project can produce. Every prior
check either drove a selftest with no network, or asserted on an API's answer rather than on what
was delivered. `--submit` exists but pages a human, so it is owner-triggered only.

## The log turns the report into evidence

Fetched read-only from `public_html/new/error_log` with `scripts/fetch-remote.sh`. Correlation id
`f4e59592`, 22-Sep-2026 19:33:47–19:33:50 Europe/Sofia:

```
19:33:47  f4e59592: mail no smtp credential, using sendmail
19:33:48  f4e59592: mail delivered via=sendmail
19:33:48  f4e59592: photos=0 leftover=0
19:33:48  f4e59592: mail no smtp credential, using sendmail
19:33:50  f4e59592: mail delivered via=sendmail
```

Two mail legs — owner notification and customer confirmation — each taking the sendmail path and
each delivered **exactly once**. No `telegram transport failed` line. Whole cascade ≈3 seconds.

## What this closes

**Ledger 38, case (a) only.** Entry 38 calls the SMTP-to-sendmail fall-through "the cascade's
most important branch" and prescribes a three-case recipe. Case (a) — NO CREDENTIAL, the shipped
state — is now proven, with the exact log lines it predicted, and its hard condition holds: *"IN
ALL THREE THE ENQUIRY MUST ARRIVE EXACTLY ONCE. Two copies means the pre/post-acceptance boundary
is wrong."* Two legs, one delivery each.

Cases (b) wrong credential and (c) correct credential remain unrun, and they are the ones that
actually exercise a *failure* falling through. Entry 38 stays **open**, narrowed.

**Ledger 42 — closed.** Its assertion was that the vendored-library deny block is unverified over
HTTP. Now verified on staging:

| URL | Status |
|---|---|
| `vendor/phpmailer/PHPMailer.php` | 403 |
| `vendor/phpmailer/SMTP.php` | 403 |
| `vendor/phpmailer/Exception.php` | 403 |

Its second half — an operational note that two messages leave per submission and the worst-case
latency was "worth one look at live timings once the form is in real use" — is answered by the
same log: ~3 seconds, with no SMTP timeout in the path because there is no credential to try.

## What this does NOT close, and must not be read as closing

A success-path run says nothing about a failure path. Recorded explicitly because a green
end-to-end test is exactly the thing that makes people assume the rest is fine:

- **15 / 40 — the all-channels-failed page.** Never exercised. This drove the success branch.
- **17 — portrait-orientation photos.** `photos=0`. No photograph was attached, so neither side of
  the EXIF path ran. Still needs one portrait photo from a real handset.
- **18 — nobody has seen a photo render in Telegram.** Same reason. The owner saw *a* message; the
  open questions are whether a single photo carries the enquiry as its caption and whether three
  arrive as one media group.
- **14 — honeypot autofill false-positive.** Unknown whether autofill was active during this
  submission. A false positive here silently discards a real enquiry and is invisible to both
  parties, so "it worked once" is not evidence.
- **Quick task 260919-m0i — the live root `mailer.php` CRLF patch.** A DIFFERENT code path: the
  old site's form at the domain root, not `contact-send.php` under `/new/`. Untouched by this.

## Tasks

1. `.planning/WINDOWS.md` — update entry 38's description (keep `open`), mark entry 42 `fixed`
   with `resolved_at`. Both the markdown table row AND the matching JSON object for each.
   Frontmatter: `open_count` 30→29, `fixed_count` 21→22.
2. `.planning/STATE.md` — record the proof and the four non-closures under Phase 4 notes.

## Verify

- `open_count` + `waived_count` + `fixed_count` == `total_count` (29+3+22 == 54).
- Table row and JSON object agree for both 38 and 42.
