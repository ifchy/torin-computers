---
phase: 01-migration-safety-net-foundation
plan: 1
subsystem: infra
tags: [php, apache, htaccess, cloudlinux, ftp, seo, cpanel]

# Dependency graph
requires: []
provides:
  - "PHP 5.2-safe includes/{site-config,header,footer}.php pattern, live-verified end-to-end"
  - "Working .htaccess directive set for /new/ staging subtree: PHP-as-.html handler, canonicalization redirect, X-Robots-Tag noindex"
  - "Confirmed CloudLinux Alt-PHP handler naming convention (application/x-httpd-php52) for bell.host.bg"
affects: [01-03, 01-04, 01-05, phase-4-cutover]

actuals:
  tokens: 2026
  tasks: 2
  commits: 2

tech-stack:
  added: []
  patterns:
    - "PHP 5.2-safe include layout: array(), no closures, no namespaces, full <?php ?> tags"
    - "Single-source-of-truth $site config array referenced from header.php via require_once(dirname(__FILE__) . '/site-config.php')"
    - "Combined RewriteCond ... [OR] canonicalization rule (hardcoded literal target, never %{HTTP_HOST} reflection) - 1 hop for all non-canonical variants"
    - "X-Robots-Tag noindex via .htaccess Header directive instead of a per-subfolder robots.txt (which Google ignores outside true domain root)"

key-files:
  created:
    - src/includes/site-config.php
    - src/includes/header.php
    - src/includes/footer.php
    - src/.htaccess
    - src/index.html
    - src/phptest.html
  modified:
    - .planning/STATE.md

key-decisions:
  - "PHP-as-.html handler on bell.host.bg requires AddHandler application/x-httpd-php52 .html .htm (CloudLinux Alt-PHP version-specific handler name), not the generic AddType application/x-httpd-php or AddHandler application/x-httpd-php5/php-script variants the plan anticipated"
  - "FTPS certificate hostname mismatch (cert is *.superhosting.bg, not bell.host.bg) handled by using curl --ftp-ssl -k - keeps the transport encrypted while tolerating the shared-hosting wildcard cert not covering the vanity hostname"

patterns-established:
  - "PHP 5.2-safe include pattern: array() not [], no closures/namespaces, full <?php ?> tags - applies to every future .php file this project writes"
  - "Contact info as single source of truth in $site array, referenced (not duplicated) from header.php"

requirements-completed: [SEO-04]

coverage:
  - id: D1
    description: "PHP execution confirmed on bell.host.bg for .html files via a working AddHandler directive (CloudLinux Alt-PHP application/x-httpd-php52), proven by phptest.html returning the marker+version string instead of raw source"
    requirement: "SEO-04"
    verification:
      - kind: e2e
        ref: "curl -s https://torin.bg/new/phptest.html | grep -c PHP-IN-HTML-OK"
        status: pass
    human_judgment: true
    rationale: "User visually confirmed both live URLs (phptest.html and index.html) in browser before Task 2 proceeded, per the tracer feedback gate checkpoint"
  - id: D2
    description: "index.html renders through includes/header.php and includes/footer.php end-to-end, showing real site title, phone/email from site-config.php, and a dynamic (non-hardcoded) copyright year"
    requirement: "SEO-04"
    verification:
      - kind: e2e
        ref: "curl -s https://torin.bg/new/index.html | grep -c ТОРИН КОМПЮТЪРС"
        status: pass
    human_judgment: true
    rationale: "User visually confirmed the rendered page in browser before Task 2 proceeded, per the tracer feedback gate checkpoint"
  - id: D3
    description: "All 4 URL/protocol variants against /new/index.html canonicalize to https://torin.bg/new/index.html in exactly 1 hop (0 hops for the already-canonical https variant), and X-Robots-Tag noindex is present"
    requirement: "SEO-04"
    verification:
      - kind: e2e
        ref: "curl -o /dev/null -s -w '%{http_code} %{num_redirects}' -L against all 4 variants; curl -sI | grep -i x-robots-tag"
        status: pass
    human_judgment: false

duration: 18min
completed: 2026-08-05
status: complete
---

# Phase 1 Plan 1: PHP-Include Foundation Tracer Summary

**Live-verified PHP-as-.html execution on bell.host.bg via CloudLinux's version-specific `application/x-httpd-php52` handler, plus a working canonicalization + noindex `.htaccess` rehearsal in `public_html/new/`**

## Performance

- **Duration:** 18 min
- **Started:** 2026-08-05T09:11:00+03:00
- **Completed:** 2026-08-05T09:29:59+03:00
- **Tasks:** 2
- **Files modified:** 7 (6 created, 1 modified)

## Accomplishments

- Proved end-to-end, on the real live host, that `.html` files can be parsed as PHP without changing their filenames — the core mechanism SEO-04 (URL preservation) depends on for the rest of the rebuild.
- Discovered (via reading the host's PHP startup log over FTP, not guessing) that `bell.host.bg` runs CloudLinux "Alt-PHP", which requires the fully version-specific handler name `application/x-httpd-php52` — none of the plan's three anticipated directives (`AddType application/x-httpd-php`, `AddHandler application/x-httpd-php5`, `AddHandler php-script`) worked.
- Built the reusable PHP 5.2-safe include scaffold (`site-config.php`, `header.php`, `footer.php`) that every later page (Plan 01-05) and phase (2-4) will extend.
- Rehearsed the http/https × www/non-www canonicalization redirect and the `X-Robots-Tag: noindex` staging de-index against `/new/` before either rule ever touches the live root at Phase 4 cutover.
- Refreshed STATE.md's PHP-version blocker with a live-reconfirmed result (`phpversion()` output, not just the `X-Powered-By` header) and surfaced the CloudLinux Alt-PHP finding as new context for the still-open cPanel-login question that Phase 4's PHPMailer version decision depends on.

## Task Commits

Each task was committed atomically:

1. **Task 1 (tracer): PHP-include foundation wired end-to-end on the live host** - `87b61bd` (feat)
2. **Task 2: Verify canonicalization + noindex header across all 4 URL variants, then remove the spike probe** - `794d208` (chore)

_Note: Task 1 is a `type="tracer"` task; per the tracer feedback gate, execution paused after committing it for a human-verify checkpoint (both live URLs visually confirmed by the user) before Task 2 (expansion) proceeded._

## Files Created/Modified

- `src/includes/site-config.php` - PHP 5.2-safe `$site` array (phone, email), single source of truth for contact info
- `src/includes/header.php` - Shared head + contact-info chrome, requires `site-config.php`, references `$site['phone']`/`$site['email']` via full `<?php echo ...; ?>`
- `src/includes/footer.php` - Shared footer + closing markup with a dynamic `<?php echo date("Y"); ?>` copyright year (replaces the live site's stale hardcoded `© 2019`)
- `src/.htaccess` - Combined `RewriteCond ... [OR]` canonicalization rule (hardcoded literal target), `X-Robots-Tag: noindex, nofollow` via `mod_headers`, and the working `AddHandler application/x-httpd-php52 .html .htm` directive
- `src/index.html` - First live-verified page skeleton using the include pattern end-to-end
- `src/phptest.html` - Spike probe; kept locally only (not deployed) with a comment documenting its non-deployed status, per ASVS V4 (no lingering version-disclosure endpoint)
- `.planning/STATE.md` - PHP-version blocker updated with live-reconfirmed 5.2.17 result and the CloudLinux Alt-PHP finding; cPanel-login open question appended (pre-existing blocker rows untouched)

## Decisions Made

- **Handler directive:** `AddHandler application/x-httpd-php52 .html .htm` is the one directive that actually works on `bell.host.bg`, discovered by reading `~/error_log` over FTP (revealed `/opt/alt/php52/...` paths, the CloudLinux Alt-PHP signature) rather than by further blind guessing after the plan's three suggested variants all failed (two served raw unexecuted source, one 500'd).
- **FTPS transport:** Used `curl --ftp-ssl -k` (explicit TLS with hostname verification skipped) rather than falling back to plain FTP. The TLS handshake itself succeeded; only the certificate's hostname (`*.superhosting.bg`, a shared-hosting wildcard cert) didn't match `bell.host.bg`. This keeps the FTP session's credential exchange and file transfers encrypted, closer to T-01-05's "prefer encrypted transport" intent than dropping to unencrypted plain FTP would have been.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] Discovered the correct `.htaccess` PHP handler directive beyond the plan's three suggested options**
- **Found during:** Task 1, step 10 (PHP-as-.html spike test)
- **Issue:** The plan's step 10 anticipated at most two fallback directives (`AddHandler application/x-httpd-php5`, then `AddHandler php-script`) after the primary `AddType application/x-httpd-php`. All three failed on this host: the `AddType` and `php-script` variants served the raw, unexecuted PHP source (200 OK, but literal `<?php ... ?>` text in the response body); the `application/x-httpd-php5` variant (tried both as `AddType` and `AddHandler`) returned a 500 Internal Server Error.
- **Fix:** Downloaded the tail of the host's `~/error_log` via an FTP range request (read-only, no side effects) and found `/opt/alt/php52/usr/lib64/php/modules/...` paths in the PHP startup warnings — the signature of CloudLinux's "Alt-PHP" (PHP Selector) feature, which registers handler names with the exact PHP minor version baked in. Tried `AddHandler application/x-httpd-php52 .html .htm`, which worked immediately (`phptest.html` returned `PHP-IN-HTML-OK 5.2.17`).
- **Files modified:** `src/.htaccess`
- **Verification:** `curl -s https://torin.bg/new/phptest.html` returns the marker+version string; `curl -s https://torin.bg/new/index.html` renders through the includes.
- **Committed in:** `87b61bd` (Task 1 commit)

---

**Total deviations:** 1 auto-fixed (1 blocking)
**Impact on plan:** Necessary to complete the plan's core objective at all — without this fix, the PHP-include foundation (and by extension SEO-04) would have been unprovable on this host with the plan's originally anticipated directives. No scope creep: the fix is a single-line change to the already-planned `.htaccess` file, informed by read-only diagnostic investigation rather than trial-and-error guessing against the live site.

## Issues Encountered

None beyond the auto-fixed handler-directive discovery above (which is documented as a deviation, not treated as a separate unresolved issue).

## User Setup Required

None - no external service configuration required. All work targeted the isolated `public_html/new/` staging subfolder on the already-configured `bell.host.bg` host; no new accounts, API keys, or dashboard configuration were introduced.

## Next Phase Readiness

- The PHP-include foundation, the correct `.htaccess` handler directive, and the canonicalization + de-index rules are all proven live on `bell.host.bg` — Plan 01-05 (and every later phase) can now build the remaining 15 page skeletons and real visual work on top of this confirmed mechanism without re-verifying it.
- **Carried forward, not blocking:** whether a cPanel/control-panel login exists separately from the FTP-only credentials remains an open question for Phase 4's PHPMailer version decision (STATE.md blocker, now enriched with the CloudLinux Alt-PHP finding — if cPanel access exists, MultiPHP Manager likely makes a PHP-version bump for PHPMailer 6.x straightforward).
- `public_html/new/.htaccess`'s canonicalization rule will need its redirect target changed from `https://torin.bg/new/$1` to `https://torin.bg/$1` when promoted to the root `.htaccess` at Phase 4 cutover — already noted inline in the file's own header comment.

---
*Phase: 01-migration-safety-net-foundation*
*Completed: 2026-08-05*

## Self-Check: PASSED

All 6 created files, the modified STATE.md, and both task commits (`87b61bd`, `794d208`) were verified present on disk / in git history. No credential material (FTP password, base64 value, or derived substring) appears in any commit diff or this SUMMARY — confirmed via `git diff` grep scan.
