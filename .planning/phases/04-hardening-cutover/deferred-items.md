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

