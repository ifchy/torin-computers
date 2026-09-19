# Phase 4 — Measured host capabilities (bell.host.bg, public_html/new/)

Every figure below was read out of a live response body. The command that
produced it and the date it was produced are recorded alongside it, following
the evidence discipline 03.5-TRUTH-AUDIT.md established.

**Anything not read out of a response body is an assumption and is labelled
one.** There are no unmarked inferences in this file.

The probe token is redacted from the recorded commands: it gated a file that no
longer exists, but writing a live credential into a committed artefact is a
habit worth not having.


## ⛔ CUTOVER BLOCKERS — read before promoting anything to the root

Two measured facts that must be acted on before `/new/` becomes the live root (04-09/04-10).
They are at the top of this file on purpose: both were found by measurement, both are easy
to carry into production unnoticed, and neither announces itself when it goes wrong.

### BLOCKER 1 — `display_errors` is On in `php85-fcgi.ini`

Measured on the final post-switch probe: `ini:display_errors : '1'` (with
`error_reporting : '22519'`, which includes `E_WARNING`). **This is the only known-bad value
in the entire measured set.**

Any PHP warning is rendered INTO the page, for a visitor, filesystem paths included. It is
deliberately left On for now because it is what lets `handler-sweep.sh` see a broken page
rather than a silently empty one — but that trade stops being worth it the moment real
customers are on the other end.

**Action at cutover:** set `display_errors = Off` in the root's `php*-fcgi.ini` (and in
`/new/`'s, if that directory survives). Verify by re-probing, not by reading the file.

Note the near miss this already produced: the live root's `mailer.php` reads four `$_POST`
keys with no `isset()`, then calls `header("Location: msg.html")`. On PHP 8 a missing key is
`E_WARNING`, so with `display_errors` On that output would precede the redirect and break it
with "headers already sent". The account-default switch to 8.5 did **not** trigger this —
root's ini is separate from `/new/`'s and kept errors out of the body (measured below) — but
the same file behind the `/new/` ini would behave differently. Do not let those two configs
converge without turning this off.

### BLOCKER 2 — there is no local MTA, on either PHP build

`smtp:localhost:25 : FAIL Connection refused` on 5.2.17 AND on 8.5.10. Unchanged by the
runtime upgrade because it was never a PHP problem.

**04-05's email leg cannot use a local relay.** It needs the `sendmail` binary
(`/usr/sbin/sendmail -t -i`, confirmed present) or authenticated remote SMTP. D4-11 should
be resolved in favour of authenticated SMTP against a real mailbox — which is also what
gives SPF/DKIM alignment, the thing CLAUDE.md flags as the reason raw `mail()` gets dropped.

### Also live, and not fixable from this phase

`site-current/mailer.php` carries a CRLF **email header injection** (`From: <$email>` with
only `htmlentities()` escaping, which does not strip CR/LF). The production contact form is
usable as a mail relay, sending from the shop's own domain and IP — a domain-reputation
risk, not just a spam nuisance. Full write-up in `deferred-items.md`. 04-05 retires this
endpoint; until then the exposure is live.

---

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

## Handler cutover — DEPLOYED AND VERIFIED (superseded status, kept for the record)

**RESOLVED 2026-09-19.** Step B is deployed and swept: `PASS — 19/19 pages: 200, no source
leak, no PHP errors, not on 5.2`, every page reporting `PHP/8.5.10`. Re-run independently by
the executor rather than accepted on report, since the sweep is Task 3's actual acceptance
criterion.

The original text of this section is kept below rather than deleted. It recorded a gap
between a committed edit and an unchanged server, and the discipline that mattered was
naming that gap instead of implying the commit had changed anything. Deleting the record of
a correctly-declared unknown teaches nothing; superseding it in place shows the gap opening
and closing.

---

*Original status, now superseded:*

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

## Probe run — 2026-09-19 — POST-SWITCH, STAGE A (PHP 8.5.10) — AUTHORITATIVE

Written by `run-probe.sh --read` directly from the response, not transcribed. Where any
other section of this file quotes this run, THIS block wins.

Command:

```
curl -s 'https://torin.bg/new/hc-33183d7b433062f082cf479d5f83a75f.php?k=<32-hex-token>'
```

Response body, verbatim:

```
version              : 8.5.10
sapi                 : cgi-fcgi
ext:gd               : NO
ext:exif             : NO
ext:fileinfo         : NO
ext:curl             : NO
ext:openssl          : yes
ext:mbstring         : NO
ext:hash             : yes
ext:ctype            : NO
ext:filter           : yes
ini:upload_max_filesize: '250M'
ini:post_max_size    : '200M'
ini:max_file_uploads : '20'
ini:max_input_vars   : '5000'
ini:memory_limit     : '256M'
ini:max_execution_time: '600'
ini:allow_url_fopen  : '1'
ini:user_ini.filename: '.user.ini'
ini:user_ini.cache_ttl: '300'
ini:sendmail_path    : '/usr/sbin/sendmail -t -i'
ini:SMTP             : 'localhost'
ini:smtp_port        : '25'
outbound:curl443     : FAIL ext-curl not loaded
sendmail binary      : yes
smtp:localhost:25    : FAIL Connection refused
selfdelete           : OK

```

## Probe cleanup — 2026-09-19 — post-switch probe, self-deleted

Fetched WITH the valid token, because a tokenless 404 is what a LIVE
probe returns too and would prove nothing:

```
curl -s -o /dev/null -w '%{http_code}' 'https://torin.bg/new/hc-33183d7b433062f082cf479d5f83a75f.php?k=<32-hex-token>'
404
```

---

## Stage A — analysis of the 2026-09-19 post-switch run

Commentary on the authoritative block above. It deliberately holds NO second copy of the
response body: the first draft of this section did, transcribed by hand, and the two
copies had already drifted within a day (see the provenance note below). One machine-written
record, one analysis, no transcription.

Taken during the Stage-A rehearsal, in which `.php` runs on the 8.5 fcgid wrapper while
all 19 `.html` pages remain on the known-good 5.2 handler. That is the only window in
which the 8.5 runtime can be measured without having already bet 19 pages on it, and it is
the reason the deploy was staged.

Mechanism confirmed first:

```
curl -sS -D - -o /dev/null https://torin.bg/new/includes/site-config.php
  -> HTTP/2 200 · x-powered-by: PHP/8.5.10 · content-type: text/html; charset=UTF-8

bash scripts/host-probe/handler-sweep.sh
  -> FAIL — 19 of 19 ... STILL-ON-5.2   (expected and correct during Stage A)
```

`mod_fcgid` is present, `FcgidWrapper` is permitted in `.htaccess` here, and the absolute
wrapper path is right. **The handler mechanism is proved.** No 500s, no raw source.

The body itself is in the authoritative block above and is deliberately not repeated here.

### The extension set collapsed: 5.2 vs 8.5, measured

| Extension | 5.2.17 | 8.5.10 | What its absence blocks |
|---|---|---|---|
| `gd` | yes | **NO** | 04-03 photo resizing (D4-13) |
| `exif` | yes | **NO** | 04-03 EXIF orientation (EA-07) |
| `fileinfo` | yes | **NO** | 04-03 / 04-05 upload MIME validation — a security control, not a nicety |
| `curl` | yes | **NO** | 04-02 tracer, 04-05 Telegram (D4-05), and D4-06's own measurement |
| `mbstring` | yes | **NO** | multibyte-safe string handling |
| `ctype` | yes | **NO** | validation helpers (04-05) |
| `openssl` | yes | yes | — |
| `hash` | yes | yes | — |
| `filter` | yes | yes | — |

Six of nine lost. **Inference, not measured:** the three survivors are the ones compiled
into cPanel's base `ea-php85` build, while the six absentees ship as separate
`ea-php85-php-*` packages that have to be enabled per-version in the panel. Selecting a PHP
version and selecting its extensions are two different panel operations, and doing only the
first is what produced this. Recorded as the likely explanation, not as a reading.

### `outbound:curl443` is now UNANSWERED, not answered "no"

The 5.2 run recorded `OK http=401`. That was a fact about the 5.2 build and does not carry
over. The 8.5 run says `FAIL ext-curl not loaded`, which is **not a measurement of the
network** — it is the absence of one. D4-06's question ("can this host reach the internet
on 443 at all?") is therefore reopened, and D4-05 (Telegram as primary channel) cannot be
committed to until it is answered again.

**Probe defect, fixed (deviation Rule 2).** Tying the phase's notification-channel decision
to a single extension means a missing extension silently converts a measured answer back
into an unknown. The probe now carries a second, independent outbound test —
`outbound:fopen443` — using the `https://` stream wrapper, which needs only
`allow_url_fopen` (`1`) and `ext-openssl` (present on **both** builds). It answers D4-06
even if cURL never comes back. It is absent from the body above because it did not exist
when that probe was deployed.

### SECOND MEASURED GATE WEAKNESS — and it is a scope failure, not a blind spot

The `x-powered-by` finding was a gate that could not *see* a bad state. This one is
different and worth stating precisely rather than dramatically, because the distinction
changes what it teaches.

**Measured, this session:** `grep -rloE '\bmb_|\bctype_|\biconv|\bexif_|\bfinfo|\bcurl_' src/`
returns **zero files**. No page, include or helper in this tree calls a single function from
any of the six missing extensions. The tree uses `htmlspecialchars()` (21 occurrences) and
core string functions only.

Two consequences, and they pull in opposite directions:

1. **`handler-sweep.sh` is not blind here.** If the 19 pages *did* call a missing function,
   PHP 8 raises `Error: Call to undefined function ...`, and with `display_errors = On` that
   renders into the body where the sweep's check 3 catches it. The gate would have fired.
2. **But the sweep's SCOPE is wrong, and that is the real defect.** It only ever asks
   "do the pages that exist today still render?" Six extensions that no current page touches
   — but that 04-02, 04-03 and 04-05 are entirely built on — can vanish and the sweep
   reports a clean `PASS 19/19`. Step B would have been declared a success on a runtime that
   cannot resize an image, read EXIF, sniff a MIME type, or open an HTTPS connection. The
   loss would have surfaced inside 04-02's tracer, which is **exactly** the failure
   RESEARCH §Summary says this plan exists to prevent.

The correction is a separate gate with a separate scope, not more assertions bolted onto
the sweep: `scripts/host-probe/assert-capabilities.sh` checks capability, `handler-sweep.sh`
checks rendering. Neither can answer the other's question.

**The checker itself shipped with a defect that only a real body exposed.** Its first
implementation extracted values by stripping up to the first colon — but probe keys contain
colons (`ext:openssl`), so every present extension parsed as `openssl : yes`, compared
unequal to `yes`, and was reported as a BLOCKER. Run against the body above it announced
that `openssl`, `hash` and `filter` were missing while the body plainly said otherwise. A
checker that invents failures is no more useful than one that misses them, and it was caught
only because the script was run against real data before being trusted rather than after.

### Provenance note — a claim in this file was wrong, and how it got here

**CORRECTED 2026-09-19.** An earlier revision of this section stated that the probe which
produced the 8.5 run "predates the self-deleting build (`feac63b`)" and therefore ran from
a checkout without the plan's tooling. **That was false**, and it is corrected here rather
than quietly overwritten, because a file whose entire discipline is evidence provenance
cannot carry a wrong claim about which build produced a measurement.

What actually happened: the probe body reached the executor through a relay, and a single
trailing line — `selfdelete : OK` — was dropped in the retransmission. The executor
observed a real absence in the text it was given, reasoned correctly from it, and reached
a conclusion that was wrong because the input was incomplete. The `--prepare`, deploy and
`--read` were all run from the plan's worktree; `feac63b`'s self-deleting single-fetch
probe is exactly what executed, it deleted itself, and `--read`'s authenticated 404
assertion passed:

```
Probe reported: selfdelete OK
Asserting the probe is gone ...
OK: ... returns 404 with a valid token — the probe is gone.
```

**The structural lesson, which is worth more than the correction.** The truncation was
survivable only because `run-probe.sh --read` writes the response body into this file
directly, at the moment of measurement, with no human or agent in the copying path. That
machine-written block is above and is marked AUTHORITATIVE. The corrupted copy was the
*hand-transcribed* one — and the two disagreed within a day of being written.

So this section no longer holds a second copy of the body at all. A transcription is a new
observation with its own failure modes, not a reproduction of the original, and the way to
be safe from transcription error is to not transcribe. That is now the rule in this file:
**one machine-written record per run, analysis referring to it, never a second copy.**

`assert-capabilities.sh` treats an absent `selfdelete` line as a WARNING rather than a
failure, and that call happens to survive the correction — but its justification changes.
It was originally reasoned from a premise that turned out to be false (an older probe
build). The real justification is the one this incident demonstrated: an absent line means
either an older build **or a truncated body**, the second of which is now a measured
failure mode rather than a hypothetical, and neither is something a capability checker
should convert into a hard blocker. The warning text names both causes.

### Confirmed on 8.5 (the good news)

- `upload_max_filesize` `250M`, `post_max_size` `200M` — **measured in effect now**, not
  inferred from the ini file. D4-13's 2M ceiling is genuinely gone; 04-03 needs no
  `.user.ini` work and no panel round-trip for limits.
- `user_ini.filename` is now `.user.ini` with `cache_ttl` `300` — populated, as predicted.
  Available if ever needed, though per the row above it should not be.
- `sendmail` binary present; `smtp:localhost:25` still refused, unchanged from 5.2. The
  mail leg (D4-11) must use the sendmail binary or an external authenticated relay, not a
  local MTA on port 25.
- `Content-Type` now carries `charset=UTF-8` (PHP 8's `default_charset`), where 5.2 sent a
  bare `text/html`. A small improvement for Bulgarian content, and a real behavioural
  difference between the two runtimes worth having on record.

### What is blocked while each extension is missing

| Extension | Blocks | Severity | Notes |
|---|---|---|---|
| `curl` | **04-02** (phase tracer), **04-05** (notification layer) | **hard block** | D4-05 makes Telegram the primary channel and it is one HTTPS POST. Also blocks D4-06's own measurement. A `file_get_contents` fallback over the `https://` wrapper exists (`allow_url_fopen=1`, `openssl` present) and is worth knowing about, but it is a fallback, not the plan. |
| `gd` | **04-03** (photo pipeline) | **hard block** | D4-13's server-side resize has no other implementation. |
| `exif` | **04-03** | **hard block** | EA-07: orientation is discarded by both transforms without it. |
| `fileinfo` | **04-03**, **04-05** | **hard block, security-relevant** | Upload MIME validation. Without it, type checking falls back to the client-supplied extension, which is the weakest possible control on a public upload endpoint. |
| `mbstring` | *nothing currently* | **not a blocker today** | See the correction below. |
| `ctype` | *nothing currently* | **low** | 04-05 validation helpers can be written with `filter` / `preg_*`, both present. Worth restoring; not worth blocking on. |

**Correction to the working assumption that `mbstring` makes Step B unsafe on its own.**
Measured, not argued: **no file in `src/` calls any `mb_*` function.** The site is Bulgarian,
but Bulgarian content in UTF-8 HTML does not require mbstring — the bytes pass through PHP
untouched. The one escaping function the tree actually uses, `htmlspecialchars()`, lives in
`ext-standard`, not mbstring, and handles UTF-8 natively via `default_charset`.

So the 19 pages would almost certainly render correctly on 8.5 today with `mbstring`
absent. **The hold is still right — but `curl`, `gd`, `exif` and `fileinfo` are the reason,
not `mbstring`.** Those four are hard blocks on 04-02, 04-03 and 04-05, the human is going
into the panel regardless, and enabling all six in one visit is strictly cheaper than
cutting over now and returning later. Getting the reason right matters: if `mbstring` alone
were the only gap, holding 19 pages on an unpatched-since-2011 interpreter to wait for it
would be the wrong trade.

*(Related and NOT a blocker, but worth knowing before cutover: PHP 8.1 changed
`htmlspecialchars()`'s default flags to include `ENT_SUBSTITUTE`, so invalid UTF-8 is
replaced rather than returning an empty string as it did on 5.2. For a Cyrillic site that
is a strict improvement. Documented behaviour, not measured here — the 19-page sweep is
what would actually catch any regression.)*

### Step B precondition — runnable, not assumed

Step B (widening the handler to `.html`/`.htm` and removing the 5.2 fallback path) proceeds
only when a **fresh** probe body passes:

```
scripts/host-probe/assert-capabilities.sh <file-containing-the-probe-body>
```

Exit `0` is the gate. It asserts: `version` is 8.x; `sapi` is `cgi-fcgi`; all nine of
`gd exif fileinfo curl openssl mbstring hash ctype filter` report `yes`; outbound 443
answers `OK` on **either** `curl443` or `fopen443`; and the probe reported a successful
self-delete (absent line warns rather than fails — see the provenance note above).

Run against the 2026-09-19 body it reports:

```
FAIL — 7 blocker(s). Step B must NOT proceed.
```

Six missing extensions plus the unanswered outbound check. That is the current state, and
it is the number that has to become zero.

---

## Account-default PHP switch — verification of the proposed ordering (2026-09-19)

The host constraint: cPanel exposes extension management only for the **account-default**
PHP version, currently 5.2, so the apparent route to the six missing extensions is to make
8.5 the account default. The concern raised was that doing so could stop
`AddHandler application/x-httpd-php52 .html .htm` resolving and serve 19 pages of source.
The proposal was to invert: land Step B first, so `/new/` no longer depends on that handler.

### Claim 1 — is `FcgidWrapper` independent of the account-default version? **YES. Verified.**

```
curl -sS https://torin.bg/new/php.fcgi
```
```
#!/bin/bash

PHP_INI_SCAN_DIR=/home/torin/.sh.phpmanager/php85.d
export PHP_INI_SCAN_DIR

DEFAULTPHPINI=/home/torin/public_html/new/php85-fcgi.ini
exec /opt/cpanel/ea-php85/root/usr/bin/php-cgi -c ${DEFAULTPHPINI}
```

The wrapper **hardcodes an absolute path to the 8.5 binary**
(`/opt/cpanel/ea-php85/root/usr/bin/php-cgi`) and an absolute path to its ini. It performs
no lookup of "the account's PHP", reads no version variable, and consults nothing the panel
could repoint. After Step B, `/new/` executes 8.5 because that path is baked into a file on
disk. The account-default setting cannot reach it.

This is the single fact the whole inversion rests on, and it is read out of the wrapper
itself rather than inferred from cPanel's documented behaviour.

### Claim 2 — is the `x-httpd-php52` line inert once `mod_fcgid` is present? **YES.**

The one non-comment occurrence sits inside `<IfModule !mod_fcgid.c>`. Stage A proved
`mod_fcgid` **is** present on this host (`.php` answered `PHP/8.5.10` through the wrapper),
so the guard evaluates false and Apache never reads the directive.

What happens to it when the account default changes: **nothing.** `<IfModule>` tests whether
an Apache *module* is loaded, which is a function of the server's Apache build, not of which
PHP version cPanel has nominated for the account. Changing the default cannot load or unload
`mod_fcgid`, so the guard's result does not move. The line stays dead.

It is worth being exact about what it protects against after Step B: if `mod_fcgid` ever
disappeared, `.html` would fall back to the php52 handler, and if *that* had also stopped
resolving (because the default moved to 8.5) the result is a **500 or an unhandled type —
not source disclosure**, because a handler that is configured-but-unresolvable does not
degrade to "serve the file as text". The dangerous state remains the one the fallback
exists to prevent: no handler matching `.html` at all.

### Claim 3 — does Step B need any of the six missing extensions? **NO. Verified exhaustively.**

Rather than re-grep for the six, every function call in `src/` was enumerated:

```
grep -rhoE '\b[a-z_][a-z0-9_]*\s*\(' src/ --include='*.php' --include='*.html' | sort -u
```

After discarding prose words from comment blocks, the complete set of PHP functions this
site calls is:

`array` · `array_merge` · `array_slice` · `basename` · `count` · `date` · `dirname` ·
`echo` · `empty` · `file_exists` · `filemtime` · `htmlspecialchars` · `in_array` ·
`is_array` · `isset` · `json_encode` · `number_format` · `rawurlencode` · `require_once` ·
`setcookie` · `str_replace` · `trim`

Every one is core/`ext-standard`. **Step B is extension-independent**, so the handler
cutover and the extension problem are genuinely separable.

**One thing this enumeration caught that the targeted grep did not:** `json_encode()`, in
`includes/jsonld.php`, called on **every page** to emit the `LocalBusiness` and
`BreadcrumbList` structured data. `json` is the only extension the *current* site depends on
— and the probe never checked it, because the extension list was built from what 04-02,
04-03 and 04-05 will need. Nobody asked what the site already uses. `json` is compiled into
PHP 8 and cannot be disabled, so this was never going to fail; the point is that a gate
which only checks the things someone remembered to worry about is precisely how six
extensions went missing without anything objecting. `json` is now in the probe and in
`assert-capabilities.sh`, along with `display_errors` and `error_reporting`.

### Scope of `php85-fcgi.ini` — `/new/` ONLY. Verified.

The wrapper passes `-c /home/torin/public_html/new/php85-fcgi.ini`, and that wrapper is
invoked only by the `FcgidWrapper` directive in `/new/.htaccess`. Nothing else on the
account routes through it. Confirmed by absence at the root:

```
curl -o /dev/null -w '%{http_code}' https://torin.bg/php.fcgi        -> 404
curl -o /dev/null -w '%{http_code}' https://torin.bg/php85-fcgi.ini  -> 404
```

So the `display_errors = On` recorded in FINDING 4 is a `/new/` fact and does **not**
currently touch the live root.

**What root would use after an account-default switch is NOT KNOWN and must not be guessed.**
It would fall to whatever ini cPanel's default handler uses for ea-php85 — plausibly the
system `/opt/cpanel/ea-php85/root/etc/php.ini`, where `display_errors` is usually `Off`,
but "usually" is not a measurement and this file does not record those.

### Why that unknown matters: `site-current/mailer.php` on 8.5

Read in full. On the **normal path it is 8.5-safe**: no `ereg*`, `split`, `mysql_*`,
`create_function`, `each`, `$HTTP_*_VARS`, `money_format` or curly-brace string offsets. It
calls `htmlentities`, `date`, `mail`, `header` — all present in 8.5.

The conditional risk is real but narrower than feared. `mailer.php:3-6` reads
`$_POST['name']`, `['mail']`, `['mobile']`, `['message']` with no `isset()`. On PHP 8 a
missing key is `E_WARNING` (it was `E_NOTICE` on 5.2, suppressed by default). If
`display_errors` is On at the root, that warning is printed at line 3 — **before**
`header("Location: msg.html")` at line 95 — producing "headers already sent" and a broken
redirect.

But a real browser submission of that form sends all four fields, as empty strings if blank,
so the keys exist and no warning fires. The failure mode is confined to requests that omit
fields: bots, scanners, direct GETs. Those already trigger a junk `mail()` call today on 5.2;
on 8.5 with `display_errors` On they would additionally render a warning instead of
redirecting. **That is a degradation of an already-degenerate path, not a loss of the lead
channel.** It should still be measured rather than tolerated on faith.

**Separately, and more seriously: reading this file surfaced a live email-header-injection
vulnerability in it** — `$headers = "From: <$email>\r\n"` where `$email` is only
`htmlentities()`-escaped, which does not strip CR/LF. That is live on production now,
independent of any PHP version, and is logged in `deferred-items.md`. It is not in scope to
fix from this phase (no deploy path reaches the live root) but it is an argument against
letting the cutover drift.

### Verdict

**The inversion is mechanically sound and is adopted: Step B goes first.** All three of its
load-bearing claims verified. Step B is extension-independent, removes `/new/`'s dependence
on `x-httpd-php52` entirely, and is worth doing on its own merits regardless of what happens
to the account default.

**The account-default switch is NOT endorsed as the following step.** It is the one action
in this sequence whose blast radius is the live site's only lead channel, and it would be
taken to fix a problem confined to a staging subtree — inverting D4-02's founding principle
that the risky change is proven on `/new/` first. The unexplored option should be tried
first: **ask SuperHosting to enable `curl`, `gd`, `exif`, `fileinfo`, `mbstring` and `ctype`
for `ea-php85` without changing the account default.** These are EasyApache packages the
provider installs server-side; the account-level selector only toggles what is already
installed, which is consistent with the observation that `curl`, `exif` and `ctype` do not
appear in the 5.2 list at all while the 5.2 probe reports all three loaded — the selector
lists optional modules, not compiled-in ones. A support ticket has zero blast radius and
costs a day.

## Probe run — 2026-09-19 — FINAL, POST-ACCOUNT-DEFAULT-SWITCH (PHP 8.5.10) — AUTHORITATIVE

The closing measurement for plan 04-01: account default moved to 8.5 and all six missing
extensions enabled. Written by `run-probe.sh --read` straight from the response — not
transcribed. **This is the record of record for the Phase 4 runtime.** Where anything else
in this file or in any summary quotes the post-switch host, this block wins.

Command:

```
curl -s 'https://torin.bg/new/hc-bd11d80ac76c46e4506b28d7291100d6.php?k=<32-hex-token>'
```

Response body, verbatim:

```
version              : 8.5.10
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
ext:json             : yes
ini:upload_max_filesize: '250M'
ini:post_max_size    : '200M'
ini:max_file_uploads : '20'
ini:max_input_vars   : '5000'
ini:memory_limit     : '256M'
ini:max_execution_time: '600'
ini:allow_url_fopen  : '1'
ini:user_ini.filename: '.user.ini'
ini:user_ini.cache_ttl: '300'
ini:sendmail_path    : '/usr/sbin/sendmail -t -i'
ini:SMTP             : 'localhost'
ini:smtp_port        : '25'
ini:display_errors   : '1'
ini:error_reporting  : '22519'
outbound:curl443     : OK http=401
outbound:fopen443    : OK HTTP/1.1 401 Unauthorized
sendmail binary      : yes
smtp:localhost:25    : FAIL Connection refused
selfdelete           : OK

```

## Probe cleanup — 2026-09-19 — final probe, self-deleted

Fetched WITH the valid token, because a tokenless 404 is what a LIVE
probe returns too and would prove nothing:

```
curl -s -o /dev/null -w '%{http_code}' 'https://torin.bg/new/hc-bd11d80ac76c46e4506b28d7291100d6.php?k=<32-hex-token>'
404
```

---

## Account-default switch to PHP 8.5 — executed 2026-09-19

The executor verified the Step-B-first inversion and then recommended **against** the
account-default switch, proposing a host support ticket instead (zero blast radius). The
developer was given both options with the unknown-root-ini risk stated, judged the switch
reversible, and chose it. Recorded as their call, made with the facts in hand — the
recommendation is preserved above rather than rewritten to match the outcome, because a
recommendation edited after the fact to agree with what happened is worth nothing next time.

All six missing extensions were enabled in the same visit.

### Order of operations, and why it was safe

Step B landed **first**, while 5.2 was still the account default. After it, `/new/` routes
`.html`/`.htm` through `FcgidWrapper /home/torin/public_html/new/php.fcgi`, whose script
`exec`s a hardcoded `/opt/cpanel/ea-php85/root/usr/bin/php-cgi`. The staging subtree
therefore stopped depending on `application/x-httpd-php52` entirely **before** anything
touched the account default, so the switch could not strand it. That ordering was the whole
point of the inversion and it held.

### Live-site blast radius — baselined before, re-measured after

Relayed by the orchestrator, who ran the two `mailer.php` POSTs. Recorded as **relayed, not
executor-measured**: the executor deliberately did not exercise `mailer.php`, because
`mail()` is called unconditionally at top level in that file and a bare request sends a real
email to the shop.

| Check | Before (5.2) | After (8.5) |
|---|---|---|
| `/`, `/index.html`, `/uslovia.html`, `/msg.html` | 200 | 200 |
| `mailer.php` status | 302 | 302 |
| `location` | `msg.html` | `msg.html` |
| `content-length` | 0 | **0** |
| `x-powered-by` | PHP/5.2.17 | PHP/8.5.10 |

**`content-length: 0` holding across the switch is the answer to the question this file
previously refused to guess at.** It is direct evidence that the root is NOT running with
`display_errors` leaking into output: had it been, the undefined-array-key warnings from
`mailer.php:3-6` would have appeared in the body and broken the redirect. Instead the
redirect survives with an empty body on both runtimes. **Root's ini is separate from
`/new/`'s, and it keeps errors out of the response.** That closes the unknown; it does not
retire BLOCKER 1, which is about the `/new/` ini that would follow the site to the root if
the two configs are ever merged.

Independently re-confirmed by the executor after the switch (read-only, no side effects):

```
curl -sS -o /dev/null -w '%{http_code}' https://torin.bg/             -> 200
curl -sS -o /dev/null -w '%{http_code}' https://torin.bg/uslovia.html -> 200
curl -sS -o /dev/null -w '%{http_code}' https://torin.bg/msg.html     -> 200

bash scripts/host-probe/handler-sweep.sh
  -> PASS — 19/19 pages: 200, no source leak, no PHP errors, not on 5.2.
```

### `outbound:fopen443` earned itself on its first run

The final probe answers D4-06 through **two independent mechanisms**:

```
outbound:curl443     : OK http=401
outbound:fopen443    : OK HTTP/1.1 401 Unauthorized
```

The second test was added precisely because the previous run had reported
`FAIL ext-curl not loaded` — an extension fact masquerading as a network result. D4-06 is
now answered in a way that survives any single extension going missing again, which on this
host is not a hypothetical. **D4-05 (Telegram as the primary notification channel) is
cleared to proceed.**

### Final measured state — every Phase 4 gate answered

| Question | Answer | Gates |
|---|---|---|
| PHP version / SAPI | `8.5.10` / `cgi-fcgi` | D4-01, D4-02 |
| `gd`, `exif`, `fileinfo` | all `yes` | 04-03 photo pipeline |
| `curl`, `openssl` | both `yes` | 04-02, 04-05, D4-11 |
| `mbstring`, `ctype`, `hash`, `filter`, `json` | all `yes` | 04-05 validation, current pages |
| Outbound 443 | `OK` via **both** curl and fopen | D4-05, D4-06 |
| Upload ceiling | `250M` / `200M`, 20 files | D4-13 — ceiling gone, no `.user.ini` needed |
| `user_ini.filename` | `.user.ini`, ttl `300` | available, not required |
| Mail transport | `sendmail` binary yes; localhost:25 **refused** | D4-11 → authenticated SMTP, **BLOCKER 2** |
| `display_errors` | `1` in `/new/` — **BLOCKER 1** | 04-09 / 04-10 |

Every row RESEARCH marked "probe" now has a measured value, and the staging tree runs a
supported PHP with no page serving source.
