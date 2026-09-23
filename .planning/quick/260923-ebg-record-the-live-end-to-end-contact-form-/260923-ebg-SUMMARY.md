---
quick_id: 260923-ebg
slug: record-the-live-end-to-end-contact-form-proof
date: 2026-09-23
status: complete
commits:
  - c784347 docs — record the live delivery proof, close ledger 42, narrow 38
---

# The delivery cascade has run for real

The owner submitted the contact form and all three channels delivered: Telegram message, shop
email, customer confirmation email.

**This is the one class of evidence nothing in this project can generate on its own.** Every
prior check either ran a selftest with no network, or asserted on an API's *answer* rather than
on what a human actually received. `--submit` exists but pages a person, so it is owner-triggered
by design.

## The report, turned into evidence

An owner's "it worked" is a real signal but not a record. Fetched read-only from
`public_html/new/error_log` via `scripts/fetch-remote.sh` — correlation id **`f4e59592`**,
22-Sep-2026 19:33:47–19:33:50 Europe/Sofia:

```
19:33:47  f4e59592: mail no smtp credential, using sendmail
19:33:48  f4e59592: mail delivered via=sendmail
19:33:48  f4e59592: photos=0 leftover=0
19:33:48  f4e59592: mail no smtp credential, using sendmail
19:33:50  f4e59592: mail delivered via=sendmail
```

Two mail legs, each taking the shipped no-credential path, each delivered **exactly once**. No
telegram failure line. Whole cascade ~3 seconds.

## Ledger 38 — case (a) proven, entry stays open

Entry 38 calls the SMTP-to-sendmail fall-through "the cascade's most important branch" and sets a
three-case recipe. The log matches case (a)'s predicted lines exactly, and its hard condition
holds — *"IN ALL THREE THE ENQUIRY MUST ARRIVE EXACTLY ONCE. Two copies means the
pre/post-acceptance boundary is wrong, which is the single outcome this design exists to
prevent."* Two legs, one delivery each.

**Not closed.** Cases (b) wrong credential and (c) correct credential are the ones that exercise
an actual *failure* falling through, and neither has run. Description updated to carry the case
(a) evidence and narrow the remaining scope; status stays `open`.

## Ledger 42 — closed

Its assertion was that the vendored-library deny block was unverified over HTTP:

| URL | Status |
|---|---|
| `vendor/phpmailer/PHPMailer.php` | **403** |
| `vendor/phpmailer/SMTP.php` | **403** |
| `vendor/phpmailer/Exception.php` | **403** |

Its second half — an operational note that two messages leave per submission and worst-case
latency was "worth one look at live timings once the form is in real use" — is answered by the
same run: ~3s, with no SMTP timeout in the path because the shipped state has no credential to
try. Re-check if a credential is ever added.

`open_count` 30 → 29, `fixed_count` 21 → 22. Table row and JSON object updated together for both
entries; reconciled 29 + 3 + 22 = 54.

## What this does NOT close — recorded deliberately

A green end-to-end test is precisely the thing that makes people assume the rest is fine.

| Ledger | Why the live run does not touch it |
|---|---|
| **15 / 40** | The all-channels-failed page has still never been exercised. This drove the success branch. |
| **17** | `photos=0` — no photograph attached, so neither side of the portrait-EXIF path ran. Still needs one portrait photo from a real handset. |
| **18** | Nobody has watched a photo *render* in Telegram. The owner saw a message; the open questions are the single-photo caption and whether three arrive as one media group. |
| **14** | Unknown whether browser autofill was active. A honeypot false positive silently discards a real enquiry and is invisible to both parties — "it worked once" is not evidence. |
| **Quick 260919-m0i** | A **different code path**: the old site's root `mailer.php`, not `contact-send.php` under `/new/`. Still live and runtime-unverified, and its mailbox check was due Monday 2026-09-21. |

## Note on method

The first attempt to edit the ledger parsed the JSON block by locating `[\n  {` and slicing to
end of file. It threw — the block is inside a ```` ````json ```` fence with content after it. The
script failed before writing, so the file was never corrupted, and the fix reads the fence
boundaries explicitly. The rewrite also refuses any description containing a literal `|`, which
would silently break the markdown table (the defect already recorded against entries 16 and 48).
