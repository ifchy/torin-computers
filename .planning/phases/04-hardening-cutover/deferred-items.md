## Deny public access to the panel-generated PHP files (from 04-01 Task 3)

**Found:** 2026-09-17, plan 04-01 Task 3.
**Severity:** low-medium — information disclosure, no secrets.

`https://torin.bg/new/php.fcgi` and `https://torin.bg/new/php85-fcgi.ini` both return
200 to anonymous requests. `php.fcgi` (214 B) discloses the account home path
(`/home/torin/`), the panel's ini-scan directory (`/home/torin/.sh.phpmanager/php85.d`)
and the exact PHP build path (`/opt/cpanel/ea-php85/root/usr/bin/php-cgi`).
`php85-fcgi.ini` is a 44 KB dump of the full PHP configuration. cPanel created both
without an access rule and regenerates them on every version change.

**Fix:** a `<FilesMatch "^(php\.fcgi|php[0-9]*-fcgi\.ini|\.user\.ini)$">` denial in
`src/.htaccess`, pattern-based rather than named after today's filenames.

**Why it is not in 04-01:** both authorization syntaxes need an `AllowOverride` grant
this host has not been observed to give (`AuthConfig` for `Require`, `Limit` for
`Order`/`Deny`). The file's existing directives only prove `FileInfo`. An `<IfModule>`
guard does not help — the module is present, the override permission is what would be
missing — so a wrong guess is a 500 for the entire subtree. Bundling that into the one
commit that decides whether 19 pages execute would make any failure ambiguous between
two causes.

**When:** after the handler cutover is verified, as its own deploy. Also applies at the
root cutover (04-10), where the same two files will be generated in `public_html/`.

## LIVE VULNERABILITY — email header injection in the production `mailer.php`

**Found:** 2026-09-19, plan 04-01, while reading `site-current/mailer.php` to assess whether
it survives a PHP 8.5 account-default switch.
**Severity:** high. **Live on the production site right now**, independent of PHP version.
**Not introduced by this phase, and not fixable from this phase** — `deploy-new.sh` writes
only to `public_html/new/` and refuses everything else, by design.

`site-current/mailer.php:83-84`:

```php
$headers =  "From: <$email>\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
mail("office@torin.bg", "...", $m2, $headers);
```

`$email` is `htmlentities($_POST['mail'], ENT_QUOTES, 'UTF-8')`. **`htmlentities()` does not
strip CR or LF** — it encodes `< > & " '` and nothing else. A submitted address containing
`\r\nBcc: ...` is therefore concatenated straight into the additional-headers argument of
`mail()`, which PHP does not sanitise (its injection guards cover `to` and `subject`, not
`additional_headers`). The shop's contact form is usable as a mail relay, and any resulting
spam is sent from the shop's own domain and IP.

**Why it is recorded here rather than fixed:** the live root is outside every deploy path
this project has, and changing it is a deliberate act with its own rollback story. It is
recorded because (a) it is a genuine live finding, (b) it is an argument for not letting the
cutover slip indefinitely, and (c) 04-05 replaces this endpoint entirely with a hardened one
— the fix already exists in the plan; what matters is that the exposure window is a known
quantity rather than a surprise.

**Whoever ships the cutover should treat retiring this file as security work, not cleanup.**

## `wc -l | grep -qx N` is not portable — it reports a false FAILURE on macOS

**Found:** 2026-09-20, plan 04-02 Task 3, while running Task 2's verify block.
**Severity:** low as a defect, high as a trap — it fails in the direction that wastes time,
and it will recur in five more plans in this phase.

04-02's V7 check is written as:

```sh
bash -c 'grep -L "kontakti.html" src/includes/header.php' | wc -l | grep -qx 0
```

It reported failure. The condition it tests is **true** — `src/includes/header.php:264` does
contain `kontakti.html`, and `grep -L` correctly printed nothing. The check is what is wrong.

BSD `wc` (macOS, the build machine) **right-pads its count to a fixed width**: the byte stream
is seven spaces, then `0`, then a newline — confirmed with `od -c`. `grep -qx 0` anchors both
ends of the line, so it never matches the padded form. GNU `wc` (Linux, CI) emits `0\n`
unpadded and the identical check passes. The same command therefore passes on one machine and
fails on the other while the thing being measured is unchanged.

**Fix, and the form later plans should use:**

```sh
N=$(grep -L "kontakti.html" src/includes/header.php | wc -l | tr -d ' '); [ "$N" = "0" ]
```

`tr -d ' '` costs nothing on GNU and makes the check mean the same thing on both. (`grep -c`
is the better tool where the input is a file rather than a pipe, and needs no counting at all.)

**Where it still bites:** seven occurrences of `wc -l | grep -qx N` across five plans —
`04-01`, `04-02`, `04-04`, `04-05`, `04-07`. Four of those five are unexecuted. Anyone running
them on macOS should expect a false failure and check the padding before believing it.
Recorded in `.planning/WINDOWS.md` as entry 16.

## What Task 3's live pass did *not* cover

**Found:** 2026-09-20, plan 04-02 Task 3.

Task 3 passed — see `04-02-SUMMARY.md` for exactly what the human reported and exactly what
is inferred rather than observed. Two items from the plan's `<how-to-verify>` list remain
genuinely unproven and are carried forward rather than quietly marked done:

1. **Honeypot versus browser autofill** (`WINDOWS.md` entry 14). The plan names this as the
   number-one honeypot false-positive source and the one failure mode nobody would ever see —
   a real enquiry discarded silently, with the visitor shown a success page. The human's manual
   submission arrived, but they did not state whether autofill was active, so the trap has not
   been proven safe against it. Re-test with autofill explicitly on, ideally with a password
   manager that fills aggressively.
2. **The notification failure path** (`WINDOWS.md` entry 15). Every check so far drove the
   success branch. Nothing has made `api.telegram.org` unreachable, so the visitor-facing
   "every channel failed" page and the `error_log` correlation-id branch are unexercised at
   runtime. 04-05 adds a second driver and is the natural place to exercise both.

Items 5 and 6 of the checklist (phone numbers still tappable; the 360px weighting of form
versus phone list) were not separately reported either. They are lower-stakes — a regression in
either is visible to anyone who opens the page — but they were not confirmed, and 04-04 touches
this page's CTAs, so they are worth a look then.

