# Phase 4 — Measured host capabilities (bell.host.bg, public_html/new/)

Every figure below was read out of a live response body. The command that
produced it and the date it was produced are recorded alongside it, following
the evidence discipline 03.5-TRUTH-AUDIT.md established.

**Anything not read out of a response body is an assumption and is labelled
one.** There are no unmarked inferences in this file.

The probe token is redacted from the recorded commands: it gated a file that no
longer exists, but writing a live credential into a committed artefact is a
habit worth not having.


## Probe run — 2026-09-17 — PRE-SWITCH (PHP 5.2.17)

Command:

```
curl -s 'https://torin.bg/new/hc-9f15ac99ad6383cfbcf50832aac339db.php?k=<32-hex-token>'
```

Response body, verbatim:

```
version              : 5.2.17
sapi                 : cgi-fcgi
ext:gd               : yes
ext:exif             : yes
ext:fileinfo         : yes
ext:curl             : yes
ext:openssl          : yes
ext:mbstring         : yes
ext:hash             : yes
ext:ctype            : yes
ext:filter           : yes
ini:upload_max_filesize: '2M'
ini:post_max_size    : '8M'
ini:max_file_uploads : '20'
ini:max_input_vars   : '1000'
ini:memory_limit     : '128M'
ini:max_execution_time: '30'
ini:allow_url_fopen  : '1'
ini:user_ini.filename: ''
ini:user_ini.cache_ttl: ''
ini:sendmail_path    : '/usr/sbin/sendmail -t -i'
ini:SMTP             : 'localhost'
ini:smtp_port        : '25'
outbound:curl443     : OK http=401
sendmail binary      : yes
smtp:localhost:25    : FAIL Connection refused
```

## Probe cleanup — 2026-09-17 — pre-switch probe

Fetched WITH the valid token, because a tokenless 404 is what a LIVE
probe returns too and would prove nothing:

```
curl -s -o /dev/null -w '%{http_code}' 'https://torin.bg/new/hc-9f15ac99ad6383cfbcf50832aac339db.php?k=<32-hex-token>'
404
```

---

## cPanel PHP version selection — 2026-09-17 (Task 2, human-performed)

The version switch has no CLI or API on this account (D4-01/D4-02). The developer
performed it in the control panel and reported back; everything below that could be
checked from outside was checked from outside rather than taken on report.

### Reported by the developer

| Fact | Value |
|---|---|
| PHP version selected | **8.5** |
| Scope | `public_html/new/` **only** — account-wide/root left untouched (D4-03 honoured) |
| Files the panel generated | `php.fcgi`, `php85-fcgi.ini`, both in `public_html/new/` |
| Absolute wrapper path | `/home/torin/public_html/new/php.fcgi` |
| Old handler line | `AddHandler application/x-httpd-php52 .html .htm` **still present** on the server |

### Independently verified, not taken on report

```
curl -sS -D - -o /dev/null https://torin.bg/new/index.html
  -> HTTP/2 200 · x-powered-by: PHP/5.2.17

curl -sS -D - -o /dev/null https://torin.bg/new/msg.html
  -> HTTP/2 200 · x-powered-by: PHP/5.2.17

curl -sS -D - -o /dev/null https://torin.bg/new/includes/site-config.php
  -> HTTP/2 200 · x-powered-by: PHP/5.2.17 · content-length: 0

curl -sS https://torin.bg/new/php.fcgi
  -> 200, 214 bytes, contents below

curl -sS -o /dev/null -w '%{http_code}' https://torin.bg/new/php85-fcgi.ini
  -> 200, 44217 bytes
```

The `.html` figure confirms report line 5 more strongly than reading the file would:
`x-powered-by: PHP/5.2.17` on a page that is 100% PHP proves the `php52` handler is
still live and still routing. The old line is present **and working**.

### FINDING 1 — the 8.5 selection currently governs NOTHING

`.php` also answers `x-powered-by: PHP/5.2.17`. The account default is still 5.2.17 and
the panel's per-directory selection has not taken effect on any extension.

This is the direct consequence of FINDING 2 below: the panel delegated the activating
edit to the human, the human (correctly) did not apply it, so the wrapper exists on disk
and nothing routes to it. Stated plainly so no later reader mistakes "nothing broke" for
"8.5 is running and is compatible":

> **Nothing is running on 8.5 yet. Task 3's handler block is what activates it — for
> every extension the block names, simultaneously. There is no intermediate state in
> which 8.5 has been proven on `.php` before `.html` is bet on it, unless Task 3
> deliberately creates one.**

### FINDING 2 — D4-04 did not fire as predicted; the variant matters at cutover

D4-04 predicted: *"cPanel writes its own handler block into the root `.htaccess` when the
PHP version changes."* It did not. The PHP manager instead **instructed the human to add
one by hand**:

```apache
<IfModule mod_fcgid.c>
AddHandler fcgid-script .php
FcgidWrapper /home/torin/public_html/new/php.fcgi .php
</IfModule>
```

**The panel delegates the edit rather than making it.** That is materially different from
the predicted behaviour, and better for us in one way and worse in another:

- *Better:* there is no auto-generated block racing our hand-written one, so the
  coexistence conflict D4-04 anticipated does not exist in this directory.
- *Worse:* the suggested block covers **`.php` only**. This site's 19 pages are `.html`.
  A developer who pastes the panel's snippet verbatim and then follows the host's other
  instruction — remove the old `AddHandler` — strands every page on no handler at all and
  serves 19 pages of raw PHP source. The panel hands you the shape of the failure and
  trusts you to notice the extension list.

**Carry to cutover (04-10):** the root directory will present the same instruction with
the same `.php`-only extension list, and the root wrapper path will be
`/home/torin/public_html/php.fcgi`, not this one. The wrapper path is absolute and is
therefore invalidated by any directory move — including the `/new/` → root promotion.

### FINDING 3 — `php.fcgi` and `php85-fcgi.ini` are publicly readable

Both panel-generated files return 200 to an anonymous request. `php.fcgi` is served as a
download rather than executed, verbatim:

```
#!/bin/bash

PHP_INI_SCAN_DIR=/home/torin/.sh.phpmanager/php85.d
export PHP_INI_SCAN_DIR

DEFAULTPHPINI=/home/torin/public_html/new/php85-fcgi.ini
exec /opt/cpanel/ea-php85/root/usr/bin/php-cgi -c ${DEFAULTPHPINI}
```

That discloses the account home path, the panel's ini-scan directory and the exact PHP
build path. `php85-fcgi.ini` is a full 44 KB configuration dump. Neither is catastrophic
alone, but both are gratuitous and neither is anything the panel asked for — it created
them without an access rule, and the panel regenerates them on every version change.

**DEFERRED, deliberately — not fixed in Task 3.** The obvious fix is a `<FilesMatch>`
denial in `src/.htaccess`, and it was written and then pulled back out. Both available
authorization syntaxes (`Require all denied` on 2.4, `Order`/`Deny` on 2.2) need an
`AllowOverride` grant this host has not been observed to give: the file's existing
directives only prove `FileInfo`, not `AuthConfig` or `Limit`. An `<IfModule>` guard does
NOT protect against that — the module is present; it is the override permission that would
be missing — so a wrong guess is a 500 for the whole subtree, which is the exact failure
class (T-02-02) the handler change itself has to be verified against. Bundling an untested
authz directive into the one commit that decides whether 19 pages execute would make a
failure ambiguous between two causes. One concern per change: the denial ships separately,
after the handler cutover is proven. Logged in `deferred-items.md`.

### FINDING 4 — the 8.5 ini is already far more generous than 5.2's, and D4-13's ceiling is gone

Read directly out of the public `php85-fcgi.ini`. **These are CONFIGURED values, not yet
measured in effect** — nothing routes to this ini until Task 3's handler block lands, so
every row below is labelled accordingly and must be re-measured afterwards.

| Setting | 5.2 (measured, in effect) | 8.5 (configured, not yet in effect) |
|---|---|---|
| `upload_max_filesize` | `2M` | `250M` |
| `post_max_size` | `8M` | `200M` |
| `memory_limit` | `128M` | `256M` |
| `max_input_vars` | `1000` (measured — see note below) | `5000` |
| `max_execution_time` | `30` | `600` |
| `max_file_uploads` | `20` | not set → PHP default `20` |
| `allow_url_fopen` | `1` | `On` |
| `expose_php` | On (inferred: `x-powered-by` present) | `On` (line 286) |
| `disable_functions` | not measured | empty — nothing disabled |
| `error_reporting` | not measured | `E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT` |
| `display_errors` | not measured | **`On`** |
| `sendmail_path` | `/usr/sbin/sendmail -t -i` | `/usr/sbin/sendmail -t -i` |
| `SMTP` / `smtp_port` | `localhost` / `25` | `localhost` / `25` |

Consequences, for the plans that are gated on these:

- **04-03 (uploads, D4-13: 5 photos x 10 MB = 50 MB).** The 2M ceiling that made this plan
  hard **disappears on its own**. 250M/200M clears 50 MB with room to spare, so 04-03 needs
  **no** `.user.ini` work and no panel round-trip for limits. If a limit ever does need
  changing, the lever on this host is **editing `php85-fcgi.ini` directly** — the wrapper
  passes it with `-c`, so it is authoritative and it is in a directory we deploy to. That is
  a better lever than `.user.ini`: no `user_ini.cache_ttl` window to wait out.
- **`user_ini.filename`** measured as an empty string on 5.2 (`ini:user_ini.filename: ''`).
  The 8.5 ini does not set it either, so it falls to the PHP default (`.user.ini`,
  `cache_ttl` 300). `.user.ini` therefore *becomes* available at cutover, but per the row
  above 04-03 should not need it. **Re-measure rather than assume** — this row is inference
  from a default, not a reading.
- **`display_errors = On` is a production defect waiting to happen.** Any warning or fatal
  renders into the page for visitors, complete with filesystem paths. It must be turned
  **Off** in `php85-fcgi.ini` before the root cutover (04-10) — flagged there, not changed
  here. It is deliberately left **On for now**, because it is what makes Task 3's 19-page
  sweep able to see a broken page instead of a silently-empty one.
- **Two 5.2 readings do not match the documented feature history, and are recorded as read
  rather than reconciled.** `max_input_vars` was introduced in PHP 5.3.9 and `user_ini.*` in
  5.3, yet the 5.2.17 probe returned `'1000'` and `''` respectively rather than the `false`
  an unknown directive yields. Most likely this host's 5.2 build is a vendor backport whose
  ini file carries the directives. Either way, the figures above are what the response body
  said; the explanation is not measured and is not treated as one. The consequence is small
  — both are superseded by the 8.5 values — but "the number disagrees with the manual" is
  exactly the kind of thing that gets quietly rounded off, so it is written down instead.

- **`error_reporting` excludes `E_DEPRECATED` and `E_NOTICE`**, so 8.x deprecation noise
  will not render. Good for the sweep; it also means the sweep cannot be used as evidence
  that the tree is deprecation-clean.

### PHP 8.5 source compatibility — checked, not assumed

D4-01 asserted the tree is 8.x-clean. Re-run here against the constructs PHP 7 and 8
removed, since it is the gate for 19 public pages:

```
grep -rnE 'create_function|\beach\s*\(|\bereg[i]?\s*\(|\bsplit\s*\(|mysql_[a-z]+\s*\(|HTTP_(GET|POST|SERVER|COOKIE|SESSION)_VARS|money_format|get_magic_quotes|\$[a-zA-Z_]+\{[0-9]' src/ --include='*.php' --include='*.html'
  -> no matches
```

Zero occurrences, including the 8.0-only removals D4-01's original grep predates
(`create_function`, `money_format`, curly-brace string offsets). `short_open_tag = On` in
the 8.5 ini, so even the short-tag rule the tree already follows is not load-bearing.

---

## Handler cutover — repo-side edit landed, NOT YET DEPLOYED, NOT YET VERIFIED

**Status as of 2026-09-19: `src/.htaccess` carries the new handler block. The server does
not. Nothing below has been measured.** This section exists so that the gap is legible;
it is replaced by measured output once the deploy runs.

Live state, re-confirmed immediately before the edit was committed:

```
curl -sS -D - -o /dev/null https://torin.bg/new/index.html
  -> HTTP/2 200 · x-powered-by: PHP/5.2.17
curl -sS -D - -o /dev/null https://torin.bg/new/includes/site-config.php
  -> HTTP/2 200 · x-powered-by: PHP/5.2.17
```

The executor cannot deploy: `scripts/deploy-new.sh` is refused by the permission
classifier on any real upload. (It is NOT refused outright — it runs as far as credential
resolution and the upload loop, and is denied at the upload itself. The Phase 3 note that
records it as flatly "denied to subagents" is imprecise; both observations are recorded in
`run-probe.sh`'s header rather than one being overwritten.)

### What must be asserted after the deploy, and why status codes are not enough

The gate is the **runtime**, not the rendering. Three separate failures all produce pages
that look fine to a status-code check:

| Failure | What a status check sees | What catches it |
|---|---|---|
| `mod_fcgid` absent → the `!mod_fcgid` fallback fires | 19x `200`, pages render perfectly | `x-powered-by` still reports `5.2.17` |
| handler maps but source is served | `200` with a full body | literal `<?php` present in the body |
| PHP fatals with `display_errors = On` | `200` with a body | `Fatal error` / `Warning` present in the body |

So the sweep asserts, per page: status `200`, **zero** literal PHP open tags in the body,
**zero** `Warning:`/`Fatal error:` strings, and `x-powered-by` **not** matching `PHP/5.2`.
The first three were in the plan; the fourth is added here because the fail-safe fallback
makes "renders correctly" and "was upgraded" genuinely different claims.

`https://torin.bg/new/kontakti.html` returning 404 is CORRECT and is not a regression —
`src/kontakti.html` does not exist yet; plan 04-02 creates it. It is excluded from the
sweep for that reason, not overlooked.

### The gate was run against a known-bad state BEFORE it was trusted — 2026-09-19

`scripts/host-probe/handler-sweep.sh` was executed against the live subtree while it was
still, definitionally, in the failed state (edit committed, not deployed, everything on
5.2.17). A gate that has only ever been run against the state it is supposed to bless has
not been tested; 03.5 spent a phase on exactly that lesson.

```
bash scripts/host-probe/handler-sweep.sh
  -> FAIL  about                     PHP/5.2.17   STILL-ON-5.2
     FAIL  ekran-klaviatura-portove  PHP/5.2.17   STILL-ON-5.2
     ... (all 19)
     FAIL — 19 of 19 page(s) failed at least one assertion.   EXIT=1
```

**The important detail is which assertion fired.** Every one of the 19 pages returned
`200`, with zero literal PHP open tags and zero warning/fatal strings in the body. Checks
1, 2 and 3 — the three the plan specified — **all passed on all 19 pages, in a state where
the upgrade had not happened at all.** Only check 4, the `x-powered-by` runtime assertion
added because of the `!mod_fcgid` fail-safe, distinguished it.

Had the sweep shipped with only the plan's three assertions, it would have reported a
clean 19/19 PASS against a completely unchanged server. That is not a hypothetical
weakness in the gate; it is a measured one, recorded here with the command that produced
it.
