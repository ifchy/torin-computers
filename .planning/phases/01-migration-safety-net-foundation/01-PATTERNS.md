# Phase 1: Migration Safety Net & Foundation - Pattern Map

**Mapped:** 2026-08-04
**Files analyzed:** 8 (new files/artifacts this phase creates or reads-from)
**Analogs found:** 3 exact/role-match / 8 total (this phase is mostly greenfield scaffolding — most "analogs" are the live-site facts the new files must not diverge from, not code to copy line-for-line)

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `public_html/new/.htaccess` | config | request-response (server config) | `site-current/.htaccess` | exact (same file purpose, must extend not replace) |
| `public_html/new/phptest.html` (spike probe, delete after use) | utility/test | request-response | `site-current/mailer.php` (only executable PHP-on-host reference) | role-match (proves PHP execution on host) |
| `includes/site-config.php` | config | CRUD (static data — read-only at request time) | none in `site-current/` (no include pattern exists yet) | no analog — greenfield |
| `includes/header.php` | component (shared chrome) | request-response | `site-current/index.html` lines 1-48 (head + header markup, currently duplicated per-page) | role-match (extract this exact markup, not a code pattern) |
| `includes/footer.php` | component (shared chrome) | request-response | `site-current/index.html` lines ~1220-1280 (footer + script includes, currently duplicated per-page) | role-match |
| URL inventory artifact (e.g. `01-URL-INVENTORY.md`) | config/documentation | batch | `site-current/` root directory listing (16 `.html` files + must-carry non-page files) | exact — this *is* the source data |
| Backup/rollback script (curl-based FTP pull) | utility | file-I/O | none — no existing backup tooling in repo | no analog — greenfield, but must target the exact file list already verified in RESEARCH.md |
| `.gitignore` (verify/extend, not new) | config | — | `/Users/alabala/Documents/projects/torin/.gitignore` (existing, already excludes `filezilla-server-data.xml`, `.DS_Store`) | exact — extend only if a new secret-bearing file type is introduced (none expected this phase) |

## Pattern Assignments

### `public_html/new/.htaccess` (config, request-response)

**Analog:** `site-current/.htaccess` (verbatim, 5 lines)

**Current live content — must be preserved/extended, not replaced:**
```apache
# php -- BEGIN cPanel-generated handler, do not edit
# This domain inherits the "PHP" package.
# php -- END cPanel-generated handler, do not edit
```

**Rule:** The new `public_html/new/.htaccess` is additive on top of this cPanel stub pattern — do not remove the cPanel-generated comment block if/when this file is eventually merged toward the root at Phase 4 cutover; it signals `do not edit` boundaries maintained by the host's control panel. For the new `/new/` subfolder file (greenfield, no stub required there since it's a new directory the cPanel generator hasn't touched), use RESEARCH.md's Pattern 2/Pattern 3 combined content:

```apache
RewriteEngine On

<IfModule mod_headers.c>
  Header set X-Robots-Tag "noindex, nofollow"
</IfModule>

RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
RewriteCond %{HTTP_HOST} ^www\.torin\.bg$ [NC]
RewriteRule ^(.*)$ https://torin.bg/$1 [R=301,L]

AddType application/x-httpd-php .html .htm
```

**Also carry forward unchanged (must-carry, unrelated to this phase's new directives):** `site-current/.well-known/.htaccess` — verbatim:
```apache
Require all granted
RewriteEngine Off

<FilesMatch "\.(txt)$">
	Require all granted
</FilesMatch>

<FilesMatch "\.(txt)$">
	Allow from all
</FilesMatch>
```
This file has no bearing on the `/new/` spike work but must be copied unchanged whenever the must-carry-file checklist gets built (Phase 4 cutover), so record it now in the URL/must-carry inventory this phase produces.

---

### `public_html/new/phptest.html` (utility/test, request-response)

**Analog:** `site-current/mailer.php` — the only proof-of-PHP-execution reference on this exact host (confirmed live via `curl -I https://torin.bg/mailer.php` → `x-powered-by: PHP/5.2.17`).

**Pattern to copy:** Nothing structural to copy from `mailer.php` itself (it's a form handler, not a template), but its existence is the evidence that plain `<?php ... ?>` tags execute on this host today — the spike probe should use the same bare-tag style, no short-echo:
```php
<?php echo 'PHP-IN-HTML-OK ' . phpversion(); ?>
```
**Security note (ASVS V4):** delete this file immediately after the probe returns a result — do not leave a version-disclosure endpoint reachable, mirroring the same "don't leave debug output live" discipline implied by `mailer.php`'s commented-out debug lines (see below).

---

### `includes/header.php` (component/shared chrome, request-response)

**Analog:** `site-current/index.html`, lines 1-48 (verified read this session)

**Imports/head pattern to copy verbatim (structure, not content — content moves to `site-config.php`):**
```html
<!DOCTYPE html>
<html lang="en">
<head>

	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="theme-color" content="#3ed2a7">

	<title>ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ</title>

	<link href="https://fonts.googleapis.com/css?family=Barlow:600,700" rel="stylesheet">
	<link rel="stylesheet" href="assets1/vendors/liquid-icon/liquid-icon.min.css" />
	<link rel="stylesheet" href="assets1/vendors/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="assets1/css/theme-vendors.min.css" />
	<link rel="stylesheet" href="assets1/css/theme.min.css" />
	<link rel="stylesheet" href="assets1/css/themes/business.css" />
	<link rel="stylesheet" href="assets1/css/animation.css" />
	<link rel="stylesheet" href="assets1/css/preloader.css">

	<script async src="assets1/vendors/modernizr.min.js"></script>
	<script src="otpuska.js"></script>

</head>
```

**Contact-info block pattern (secondarybar), lines 48-80 — this is exactly the kind of duplicated-across-17-pages content `includes/header.php` exists to eliminate:**
```html
<div class="header-module">
	<div class="iconbox iconbox-inline iconbox-xs">
		<div class="iconbox-icon-wrap">
			<span class="iconbox-icon-container font-size-16"><i class="fa fa-phone"></i></span>
		</div>
		<h3 class="font-size-14">Телефон: 02 9549710, 088 9458404, 087 9128244</h3>
	</div>
</div>
```

**Guidance for this phase's scaffold:** Phase 1 does not need to fully port the visual template — it only needs `header.php`/`footer.php`/`site-config.php` to exist and prove the include mechanism works end-to-end on PHP 5.2 (per RESEARCH.md Pattern 1). Use a minimal representative slice of this markup (title, meta, phone/email) rather than porting all 1,280 lines of `index.html` — full visual porting is later-phase work. The point of this phase's analog reference is: whatever slice is chosen must come from real current markup, not invented placeholder HTML, so nothing silently diverges from what's live today.

---

### `includes/footer.php` (component/shared chrome, request-response)

**Analog:** `site-current/index.html`, tail (~last 45 lines, verified read this session)

**Footer + script-include pattern to copy:**
```html
			<div class="lqd-column col-md-3 text-md-right">
				<p class="my-0"><span style="font-size: 15px;">TORIN Company Ltd. © 2019 г.</span></p>
			</div>

		</div>
	</div>
</section>
</footer>

</div><!-- /#wrap -->

<script src="./assets1/vendors/jquery.min.js"></script>
<script src="./assets1/js/theme-vendors.js"></script>
<script src="./assets1/js/theme.min.js"></script>

</body>
</html>
```
**Note:** live footer year is hardcoded `© 2019` — flag as stale content to fix via `site-config.php`, not silently carried forward verbatim (the phase's own `site-config.php` pattern is designed to hold exactly this kind of value in one place).

---

### `includes/site-config.php` (config, CRUD/read-only)

**No direct analog exists in `site-current/`** — the live site has no include/config layer at all (17-page duplication is the exact problem this file exists to fix). Use RESEARCH.md's PHP 5.2-safe pattern directly (already vetted against the confirmed PHP 5.2.17 runtime):
```php
<?php
// includes/site-config.php — PHP 5.2-safe (no short array syntax, no namespaces)
$site = array(
  'phone'   => '02 9549710, 088 9458404, 087 9128244', // sourced from site-current/index.html header block
  'email'   => 'office@torin.bg',
  'hours'   => 'Пон-Пет 09:00-18:00',
);
```
**Critical constraint carried from RESEARCH.md:** no `[]` array literals, no closures, no `<?= ?>` short-echo — this host is PHP 5.2.17, confirmed live via `X-Powered-By` header on `mailer.php`.

---

### URL inventory artifact (documentation, batch)

**Analog/source data:** `site-current/` root listing — 16 verified `.html` pages plus must-carry non-page files, already enumerated in RESEARCH.md:
```
index.html, about.html, covid.html, laptopi.html, mehanichni-problemi.html,
msg.html, optimizatsiq.html, problem-stari.html, profilaktika-laptop.html,
rezervni-chasti.html, test-laptop.html, tokov-udar.html, uslovia.html,
warrently.html, za-bateriite.html, zalivane-technosti.html
```
Must-carry non-page files: `.htaccess`, `favicon.ico`, `google1718743335455f1c.html`, `header.js`, `otpuska.js`, `mailer.php`, plus `.well-known/`, `cgi-bin/`, `covid-19/`, `assets1/` directories.

**Structure to use (per RESEARCH.md Pitfall D):** a table with an explicit per-URL disposition column (keep-as-is / retire-with-redirect / decision-pending), not a bare filename list — `covid.html`, `test-laptop.html`, `problem-stari.html` need explicit dispositions recorded, not defaults.

---

### Backup/rollback script (utility, file-I/O)

**No existing analog** — `site-current/` has no backup tooling of its own; this is genuinely new local-dev-tooling scope. Base it on the already-verified live file list above and RESEARCH.md's "Don't Hand-Roll" guidance: use `curl` with an explicit file list (ftp/ftps-capable, already installed, version 8.7.1) rather than writing a generic recursive-FTP crawler — the 16-page + assets1 inventory is small and fully known, so a hand-written file list is correct here, not a scope gap.

---

## Shared Patterns

### PHP 5.2 compatibility constraint (applies to ALL new `.php` files this phase)
**Source:** Live-verified via `curl -I https://torin.bg/mailer.php` → `x-powered-by: PHP/5.2.17` (RESEARCH.md, repeated 3x same session)
**Apply to:** `includes/header.php`, `includes/footer.php`, `includes/site-config.php`, `phptest.html`
- `array()` not `[...]` (short array syntax needs PHP ≥5.4)
- No closures, no namespaces (need PHP ≥5.3)
- Full `<?php ... ?>` tags, never `<?= ... ?>` (short-echo availability unconfirmed on this host)
- Reference: `site-current/mailer.php` already demonstrates bare `<?php ... ?>` tag usage executing successfully on this exact host today — the one confirmed working PHP idiom baseline.

### Contact info as single source of truth
**Source:** Currently duplicated per-page in `site-current/index.html` (phone/email in the secondarybar block, lines 55-77) — the exact 17-page duplication problem this phase's `includes/` pattern exists to fix.
**Apply to:** `includes/header.php` + `includes/site-config.php` together — phone/email/hours values live once in `site-config.php`, header markup references them.

### Staging de-index via HTTP header, not robots.txt
**Source:** RESEARCH.md Pitfall C, confirmed no existing `robots.txt`/`sitemap.xml` on live host (`curl` → 404 for both)
**Apply to:** `public_html/new/.htaccess` only — `Header set X-Robots-Tag "noindex, nofollow"`, scoped to the `/new/` subtree, zero changes to the live root `.htaccess`.

### Secrets/credentials handling
**Source:** `/Users/alabala/Documents/projects/torin/.gitignore` (verified) — already excludes `filezilla-server-data.xml` and `.DS_Store`
**Apply to:** Any new backup/upload script this phase introduces — reuse existing FTP credentials from `filezilla-server-data.xml`, never inline credentials in a committed script or in `site-config.php`.

## No Analog Found

| File | Role | Data Flow | Reason |
|---|---|---|---|
| `includes/site-config.php` | config | CRUD (read-only) | No include/config layer exists anywhere in `site-current/` — this phase introduces the pattern from scratch, per RESEARCH.md Pattern 1 |
| Backup/rollback curl script | utility | file-I/O | No existing backup tooling in the repo; build directly from RESEARCH.md's "Don't Hand-Roll" guidance and the verified 16-file inventory, not from a codebase analog |
| `public_html/new/.htaccess` canonicalization + noindex rules | config | request-response | The live `.htaccess` (`site-current/.htaccess`) is a no-op cPanel stub with zero active rules — there is no existing RewriteRule/Header pattern anywhere in this codebase to copy; rules must be authored fresh per RESEARCH.md Pattern 3, then rehearsed against `/new/` before ever touching the live root |

## Metadata

**Analog search scope:** `/Users/alabala/Documents/projects/torin/site-current/` (full 215-file FTP mirror — `.htaccess`, `.well-known/.htaccess`, `mailer.php`, `index.html`, `about.html` directly read this session); `/Users/alabala/Documents/projects/torin/.gitignore`
**Files scanned:** 2 full-page HTML files (`index.html`, `about.html` — line counts checked, `index.html` head/footer read in full), `mailer.php` (full, 99 lines), both `.htaccess` files (full, small)
**Pattern extraction date:** 2026-08-04
