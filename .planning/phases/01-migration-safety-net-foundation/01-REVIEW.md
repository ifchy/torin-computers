---
phase: 01-migration-safety-net-foundation
reviewed: 2026-08-05T00:00:00Z
depth: standard
files_reviewed: 22
files_reviewed_list:
  - .gitignore
  - scripts/backup-live-site.sh
  - src/.htaccess
  - src/about.html
  - src/covid.html
  - src/includes/footer.php
  - src/includes/header.php
  - src/includes/site-config.php
  - src/index.html
  - src/laptopi.html
  - src/mehanichni-problemi.html
  - src/msg.html
  - src/optimizatsiq.html
  - src/phptest.html
  - src/problem-stari.html
  - src/profilaktika-laptop.html
  - src/rezervni-chasti.html
  - src/test-laptop.html
  - src/tokov-udar.html
  - src/uslovia.html
  - src/warrently.html
  - src/za-bateriite.html
  - src/zalivane-technosti.html
findings:
  critical: 2
  warning: 6
  info: 3
  total: 11
status: issues_found
---

# Phase 01: Code Review Report

**Reviewed:** 2026-08-05T00:00:00Z
**Depth:** standard
**Files Reviewed:** 22
**Status:** issues_found

## Summary

Reviewed the PHP 5.2-safe include scaffolding (`header.php`/`footer.php`/`site-config.php`), the 16-page HTML skeleton that consumes it, the `/new/` staging `.htaccess`, and the FTP backup script that snapshots the live host before deploy.

The good news first, since it matters for the specific risks called out for this review: the `.htaccess` canonicalization rule hardcodes its redirect target (`https://torin.bg/new/$1`) and never reflects `%{HTTP_HOST}`, so it does not introduce the open-redirect/host-header-injection pattern it was explicitly written to avoid. `backup-live-site.sh` never echoes, logs, or places the FTP password in a shell variable or command line — it decodes the password entirely inside a short-lived Python subprocess and writes it straight to a chmod-600 temp `.netrc` file, which is the correct pattern. PHP 5.2 compatibility is consistently honored: every include uses `dirname(__FILE__)` (never `__DIR__`), plain `array()` literals, no closures/namespaces, no short-echo tags.

That said, two real security defects were found in the backup script's transport handling (credentials/content can end up transmitted in cleartext, and the "secure" path's TLS verification is more permissive than its own comment claims), a genuine HTML structural bug was found in the shared header/footer scaffolding that affects every one of the 17 pages built on it, and several robustness gaps were found in the backup script's completeness/error handling that could let it report success on a partial or truncated backup — directly undermining the "pre-deploy safety net" purpose of this phase.

## Post-Review Fixes (2026-08-05)

CR-01, CR-02, and WR-02 were addressed directly by the orchestrator after this review, since they are genuine credential/transport-security defects in a script handling live production FTP credentials:

- **CR-01** fixed: the FTPS-probe-fails path now exits non-zero by default instead of silently retrying over plaintext FTP; plaintext fallback requires an explicit `BACKUP_ALLOW_PLAINTEXT_FTP=1` opt-in.
- **CR-02** fixed: added `--pinnedpubkey` (SHA-256 public-key pin for bell.host.bg's actual certificate, verified live) alongside the existing `-k`. The chain itself is validly Sectigo-signed (not self-signed); `-k` was only needed for the wildcard/vanity-hostname mismatch. The pin now cryptographically rejects any peer not holding the exact matching private key, closing the MITM gap `-k` alone left open. Comment corrected to no longer mischaracterize `-k` as hostname-only.
- **WR-02** fixed: FTP `LIST` entry names containing `/` or `..` are now rejected before being used to build local backup paths.

Re-ran `scripts/backup-live-site.sh` live against bell.host.bg after the fix: completed successfully, 16/16 pages + 7/7 must-carry files + all 4 directories, ~12MB assets1/ (consistent with the pre-fix baseline) — confirms the fix does not regress the backup's core function.

WR-01, WR-03, WR-04, WR-05, WR-06, and the Info items remain open as documented below (robustness/quality issues, not credential-security defects) — candidates for `/gsd-code-review 01 --fix` or manual follow-up.

## Critical Issues

### CR-01: Backup script silently falls back to cleartext FTP, transmitting credentials unencrypted

**File:** `scripts/backup-live-site.sh:96-104`
**Issue:** `PROTO_FLAGS=(--ftp-ssl -k)` is used for the "secure" path, but if the FTPS `--list-only` probe fails for any reason, the script logs a warning to stderr and silently retries with `PROTO_FLAGS=()` — i.e., plain, unencrypted FTP:
```bash
PROTO_FLAGS=(--ftp-ssl -k)
if ! "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" --list-only "ftp://${FTP_HOST}/${REMOTE_ROOT}/" >/dev/null 2>&1; then
  echo "FTPS probe failed against ${FTP_HOST} -- falling back to plain FTP" >&2
  PROTO_FLAGS=()
  ...
```
Once this fallback triggers, `CURL_BASE` (which includes `--netrc-file "$NETRC_FILE"`, i.e., the FTP username/password) is reused for every subsequent listing and download over plain FTP. The username and password are sent in cleartext over the network for authentication, and the entire site content transfers unencrypted too. This is a real credential-exposure vector (network sniffing / passive MITM) for a script whose header comment explicitly promises the password "is never printed, never placed in a shell variable, and never appears on any command line" — that promise is only about local handling, but the script still hands the same credential to an unencrypted wire protocol without operator confirmation.
**Fix:** Fail closed instead of silently downgrading transport security for a credentialed connection:
```bash
PROTO_FLAGS=(--ftp-ssl -k)
if ! "${CURL_BASE[@]}" "${PROTO_FLAGS[@]}" --list-only "ftp://${FTP_HOST}/${REMOTE_ROOT}/" >/dev/null 2>&1; then
  echo "ERROR: FTPS probe failed against ${FTP_HOST}. Refusing to fall back to plaintext FTP with live credentials." >&2
  echo "Re-run with BACKUP_ALLOW_PLAINTEXT_FTP=1 to explicitly accept this risk." >&2
  exit 1
fi
```
If plaintext fallback is genuinely required for this host, gate it behind an explicit opt-in env var so the operator makes the call each time, rather than the script making it silently.

### CR-02: `-k` disables *all* TLS certificate validation, not just hostname verification as documented

**File:** `scripts/backup-live-site.sh:91-96`
**Issue:** The comment above `PROTO_FLAGS=(--ftp-ssl -k)` states: "the shared-hosting wildcard cert (`*.superhosting.bg`) doesn't match the vanity hostname, so `-k` skips hostname verification while keeping the session encrypted." This mischaracterizes curl's `-k`/`--insecure` flag. Per curl's own docs, `-k` disables the *entire* certificate verification chain — expiry, CA trust, and hostname — not hostname checking alone. The session remains TLS-*encrypted* (protecting against passive sniffing), but because the peer certificate is never validated, an active MITM presenting any self-signed or unrelated certificate would be accepted silently, defeating the purpose of using FTPS at all for the credentialed connection this script establishes.
**Fix:** At minimum, correct the comment so future maintainers don't underestimate the exposure. Better: pin the actual expected certificate instead of disabling verification wholesale, e.g. fetch and vendor the real leaf/CA cert once and use `--cacert path/to/bell-host-bg.pem`, or use `--pinnedpubkey` with the known public key hash. This closes the MITM gap while still tolerating the hostname/wildcard mismatch.

## Warnings

### WR-01: Shared header/footer include has a mismatched `<body>` tag — missing open, present close

**File:** `src/includes/header.php:7-20` (no `<body>` present), `src/includes/footer.php:15-18` (`</body>` present)
**Issue:** `header.php` emits `<!DOCTYPE html>`, `<html lang="bg">`, a full `<head>...</head>` block, and then goes straight into `<div id="wrap">` — there is no `<body>` opening tag anywhere in the file. `footer.php`, however, closes `</div><!-- /#wrap --></body></html>`. Every one of the 17 live pages includes both files, so every page currently ships invalid HTML with a `</body>` that was never opened. Browsers will auto-insert an implicit `<body>` (usually right after `</head>`), but this is fragile: it silently changes where CSS that targets `body` classes/attributes (page-transition classes, scroll-lock classes, etc. — patterns the original "Liquid" template relied on) will actually attach once real interactivity/CSS is layered on top of this scaffold in later phases.
**Fix:** Add the opening tag in `header.php` immediately after `</head>`:
```php
</head>
<body>

<div id="wrap">
```

### WR-02: Remote FTP filenames used to build local backup paths without sanitization (path traversal exposure)

**File:** `scripts/backup-live-site.sh:175-201` (`recurse_dir`), `scripts/backup-live-site.sh:115-124` (`download`)
**Issue:** `recurse_dir()` extracts `name` from the FTP `LIST` output and only special-cases the literal strings `.` and `..`. It does not reject names containing `/` or embedded `..` segments before building `remote_entry="${remote_dir}${name}"` and, in `download()`, `local_path="${BACKUP_ROOT}/${remote_entry}"` followed by `mkdir -p "$(dirname "$local_path")"`. A crafted or corrupted directory listing entry (e.g., from a compromised host, or — given CR-01 — an active MITM injecting a malicious `LIST` response over the plaintext-FTP fallback path) could write files outside `backups/<timestamp>/public_html/`, anywhere the invoking user has write access.
**Fix:** Reject suspicious entry names before using them:
```bash
case "$name" in
  */*|*..*)
    echo "ERROR: suspicious entry name '${name}' in ${remote_dir}, skipping" >&2
    continue
    ;;
esac
```

### WR-03: `error_log` and other optional must-carry files can abort an otherwise-successful backup

**File:** `scripts/backup-live-site.sh:145-169`
**Issue:** `MUST_CARRY_FILES` includes `error_log`, which on a fresh host, or one that has been rotated/cleared, may simply not exist. `download()` uses `curl --fail`, so a missing remote file returns non-zero, setting `DOWNLOAD_FAILED=1` in the same loop as the genuinely critical files (`.htaccess`, `mailer.php`, all 16 pages). The script then aborts the entire backup (`exit 1`) even when every page and every essential asset downloaded successfully — a false-negative failure for the very safety-net script this phase is building.
**Fix:** Split `MUST_CARRY_FILES` into a strictly-required set (pages, `.htaccess`, `mailer.php`, `favicon.ico`) and a best-effort set (`error_log`, `google*.html`) that logs a warning but doesn't flip `DOWNLOAD_FAILED` on failure.

### WR-04: Completeness check only verifies files are non-empty, not that they downloaded in full

**File:** `scripts/backup-live-site.sh:211-223`
**Issue:** `HTML_COUNT` verification uses `[ -s "${BACKUP_ROOT}/${p}" ]` — "file exists and has size > 0." A connection drop mid-transfer that still flushes a partial, truncated (but non-empty) file to disk would pass this check and be reported as `.html pages verified: 16/16`, giving false confidence in a partial backup — the exact failure mode a pre-deploy safety net is supposed to catch.
**Fix:** Compare downloaded byte count against the remote `SIZE` (curl `--head`/`SIZE` command over FTP, or parse the size column already available from `LIST`) before counting a file as verified, rather than relying on non-zero size alone.

### WR-05: `site-config.php` closes with `?>` followed by a trailing newline

**File:** `src/includes/site-config.php:10-11`
**Issue:** The file ends with `?>\n` (confirmed via hex dump — byte sequence `29 3b 0a 3f 3e 0a` = `);\n?>\n`). This is the classic PHP "accidental output" gotcha: the trailing newline after the closing tag is emitted as literal output every time this file is `require_once`'d, which is harmless today (it just adds whitespace before the `<!DOCTYPE html>` this file precedes) but will cause "headers already sent" warnings/failures the moment any future code path (a `header()` redirect in the hardened `mailer.php`, a `session_start()` call, etc.) needs to send HTTP headers after this shared config is included.
**Fix:** Omit the closing `?>` tag entirely in pure-PHP files (standard PSR recommendation):
```php
$site = array(
	'phone' => '02 9549710, 088 9458404, 087 9128244',
	'email' => 'office@torin.bg',
);
```

### WR-06: LIST-entry parser assumes a fixed 8-column format; breaks on symlink entries

**File:** `scripts/backup-live-site.sh:182-188`
**Issue:** `awk '{ $1=$2=$3=$4=$5=$6=$7=$8=""; ... }'` assumes every `LIST` line is standard Unix `ls -l` format with exactly 8 leading columns before the filename. Symlinks in Unix FTP `LIST` output commonly render as `lrwxrwxrwx 1 owner group 11 Jan 1 00:00 linkname -> target`, where the trailing `-> target` text would be captured as part of `name`, corrupting the constructed remote/local path for that entry (and, since `type_char` would be `l` not `d`/`-`, it falls into the `download()` branch, attempting to fetch a file literally named `"linkname -> target"`).
**Fix:** Explicitly detect and handle (or skip-with-warning) `type_char = "l"` entries, and strip any `" -> "` suffix before use:
```bash
if [ "$type_char" = "l" ]; then
  echo "WARN: skipping symlink entry '${name}' in ${remote_dir} (not mirrored)" >&2
  continue
fi
```

## Info

### IN-01: `<title>` is hardcoded once in shared `header.php` — all 17 pages currently render identical titles

**File:** `src/includes/header.php:18`
**Issue:** `<title>ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ</title>` is fixed inside the shared include with no per-page override mechanism. Combined with the fact that 15 of the 16 non-index pages are byte-for-byte identical placeholder content, every live URL currently serves the same `<title>`. Expected/acceptable for this phase's "prove the include foundation works" scope, but flagging as a forward-looking reminder before real per-page SEO content lands in a later phase.
**Fix:** When real content is added, thread a `$page_title` variable (set before `require_once('header.php')`) through the `<title>` tag, defaulting to the current value if unset.

### IN-02: `index.html` is missing the `<h1>` present on all 16 other pages

**File:** `src/index.html:3-5`
**Issue:** Every other placeholder page (`about.html`, `covid.html`, etc.) has `<h1>ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ</h1>` followed by the placeholder `<p>`; `index.html` has only the `<p>`. Minor inconsistency in otherwise-identical scaffold content — likely harmless for this phase but worth normalizing before it's forgotten.
**Fix:** Add the matching `<h1>` to `index.html` for consistency, or intentionally note why the homepage is meant to differ.

### IN-03: www-host canonicalization rule in `.htaccess` is currently unreachable

**File:** `src/.htaccess:10-12`
**Issue:** Because this file lives at `public_html/new/.htaccess` (per-directory scope), the `RewriteCond %{HTTP_HOST} ^www\.torin\.bg$` branch can only fire for requests that already resolve into the `/new/` subtree. A visitor hitting `www.torin.bg` without `/new/` in the path never reaches this rule at all, so the www-canonicalization is effectively dormant until this file (or its rules) is promoted to the site root at Phase 4 cutover, as the file's own header comment anticipates. Not a defect given the documented staging-only intent — flagging purely so it isn't mistaken for working www-redirect coverage during Phase 1-3 testing.
**Fix:** None needed now; confirm this rule is verified against a live `www.torin.bg/new/...` request (not just the bare domain) during Phase 1-3 manual QA, since that's the only path that currently exercises it.

---

_Reviewed: 2026-08-05T00:00:00Z_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
