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

