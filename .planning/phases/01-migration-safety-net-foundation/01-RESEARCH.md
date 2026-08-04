# Phase 1: Migration Safety Net & Foundation - Research

**Researched:** 2026-08-04
**Domain:** Pre-migration safety net (URL/ranking continuity, backup/rollback discipline) + PHP-include technical foundation on a legacy shared-hosting cPanel/Apache stack
**Confidence:** MEDIUM-HIGH (HIGH for everything verified directly against the live host or the local dev machine this session; MEDIUM for general cPanel/PHP-ecosystem web research)

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **D-01:** All Phase 1-4 build work happens in a `public_html/new/` subfolder on the same live host (`bell.host.bg`), reachable at `torin.bg/new` for live preview throughout the project. The real site at `torin.bg` (root of `public_html/`) is untouched until the Phase 4 cutover swap.
- **D-02:** Claude handles all FTP uploads to `torin.bg/new` as work progresses — the user does not need to use FileZilla or any FTP client themselves. Just visit the URL to check progress.
- **D-03:** The `.htaccess` `.html`-as-PHP spike-verification (needed to confirm pages can keep their `.html` filenames while being parsed as PHP) happens inside `public_html/new/` — no cPanel/hosting-panel access needed, no risk to the live site. Reversibility: reversible — isolated subfolder, no production impact.
- **D-04:** Git remains the local source of truth (already initialized). In addition, push the repo to a **private GitHub repository** as an off-site backup, protecting against local machine loss.
- **D-05:** GitHub CLI (`gh`) is not installed/authenticated in this environment. The user will create an empty private repo on github.com themselves and share the remote URL — Claude cannot create it directly. Open item, blocks nothing else — proceed and wire up the remote once the URL is provided.
- **D-06:** Live-site check found all four URL variants (`http://torin.bg`, `https://torin.bg`, `http://www.torin.bg`, `https://www.torin.bg`) currently serve identical content with `200 OK` and no redirect between them — a real duplicate-content exposure, not hypothetical. Reversibility: reversible — a redirect rule, easy to adjust.
- **D-07:** Phase should canonicalize to **`https://torin.bg`** (non-www, HTTPS) — matches what the site's existing branding/internal links already use. Redirect `www→non-www` and `http→https`. Treated as part of delivering SEO-04 and the Phase 1 foundation.
- **D-08:** The site already has a Google Search Console ownership-verification file (`google1718743335455f1c.html`) at its root — some Google account previously verified GSC access, but neither the user nor Claude currently has that access.
- **D-09:** GSC access will not be available within the next 12-14+ hours minimum. Decision: proceed with Phase 1 planning and execution without GSC access now. Fall back to inferring the URL list from `site-current/` plus public `site:torin.bg` search checks. Re-incorporate real GSC data retroactively once/if access is obtained — a follow-up, not a phase blocker.

### Claude's Discretion

- Backup format/mechanism beyond "git + GitHub" (e.g., whether to also keep a raw zip snapshot) — Claude's call during planning.
- Exact `.htaccess` spike-test method (what harmless test file/route to use in `public_html/new/` to confirm PHP-as-`.html` behavior) — Claude's call.

### Deferred Ideas (OUT OF SCOPE)

- Full GSC-backed URL inventory cross-check (MIGR-01 as originally scoped) — deferred until the user gets Search Console access details from the customer. Proceed now with a site-mirror + public-search-based inventory instead; retrofit GSC data later.
- GitHub remote wiring — deferred until the user creates the private repo and shares the URL.

</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| MIGR-01 | A complete URL inventory of all 16 live pages is captured and cross-checked against Search Console before rebuild work starts | GSC unavailable (D-08/D-09) — see "URL Inventory Without GSC" below for the substitute method built from `site-current/` (verified 16-file listing) + public `site:torin.bg` search. Design the inventory artifact so a GSC column can be added later without rework. |
| MIGR-03 | A pre-deploy full backup of the live site is taken before every FTP upload, with git as local source of truth for rollback | See "Backup & Rollback Mechanism" — git is already the source of truth for `site-current/`; recommend adding a lightweight, repeatable live-mirror-pull step (curl-based, since `lftp`/`wget` are not installed locally) exercised at least once this phase. |
| SEO-04 | All existing page URLs are preserved unchanged through the redesign (no slug/filename changes) | Directly delivered by the `.htaccess` `.html`-as-PHP approach (see "PHP-Include Foundation" and ".htaccess Spike Test") — filenames/extensions never change; combined with the D-06/D-07 canonicalization redirects. |

</phase_requirements>

## Summary

This phase has one job: make it structurally impossible for the redesign to damage what already works (existing indexed URLs, the site's contact-form-critical PHP execution, and the ability to undo a bad deploy) before any visual work starts. Three independent verification passes this session materially change what the planner needs to know beyond what SUMMARY.md/ARCHITECTURE.md/PITFALLS.md already established:

1. **The live host is running PHP 5.2.17** `[VERIFIED: curl -I https://torin.bg/mailer.php, repeated 3x, 2026-08-04]` — a version released in 2011 and unsupported for over a decade. This was an open question flagged in STATE.md and is now resolved with a concrete, load-bearing answer: PHP 5.2 has no namespaces, no closures, and no short array syntax (`[]`) — all `includes/*.php` code this phase scaffolds must use PHP 5.2-safe syntax (`array()`, no closures, no `??`). It also means **PHPMailer 6.x (Phase 4's planned dependency) cannot run as-is** — PHPMailer 6.x requires PHP ≥5.5 `[CITED: github.com/PHPMailer/PHPMailer discussion #3093]`. This phase should surface that risk to the user now (via a phpinfo probe, see below) rather than let it surface as a Phase 4 blocker.
2. **All four URL/protocol variants independently reconfirmed with 200 OK, no redirect** `[VERIFIED: curl -I against all 4 variants, 2026-08-04]` — D-06 stands, canonicalization work is real, not speculative.
3. **`robots.txt` and `sitemap.xml` are both genuinely absent (404)** `[VERIFIED: curl, 2026-08-04]`, and — new finding this session — **a `robots.txt` placed anywhere other than true domain root is silently ignored by crawlers** `[CITED: developers.google.com/crawling/docs/robots-txt/robots-txt-spec]`. This directly affects how `torin.bg/new` should be kept out of Google's index during the multi-phase build: PITFALLS.md's suggestion of "a separate disallow-all robots.txt on the staging location" does not work for a subfolder on the same domain. The correct mechanism is an `X-Robots-Tag: noindex` HTTP header scoped via `.htaccess` inside `public_html/new/` only — zero changes to the live root, fully compatible with D-01.

**Primary recommendation:** Scaffold `includes/header.php` / `footer.php` / `site-config.php` using PHP 5.2-compatible syntax, spike-verify `.htaccess` `.html`-as-PHP behavior with a single combined test/version-probe file inside `public_html/new/`, add an isolated `X-Robots-Tag: noindex` rule scoped to that subfolder (not a root `robots.txt`), and build the URL inventory from the verified 16-file listing in `site-current/` rather than waiting on GSC.

## Architectural Responsibility Map

This project has no client/SSR/API/CDN/DB tiers in the conventional sense — it is a flat PHP-on-Apache shared-hosting site with no framework, no database, and no client-side app. Tiers are remapped to what actually exists on this stack:

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| URL inventory & continuity (MIGR-01, SEO-04) | Content/File layer (`.html` filenames) | Web Server config (`.htaccess`) | URLs are literally filenames on this stack; preservation is a file-naming discipline, not a routing-layer decision |
| `.htaccess` `.html`-as-PHP parsing | Web Server / Hosting Config (Apache) | — | Apache handler config decides whether `.html` requests are parsed by the PHP interpreter or served as static files; this is host-level, not application-level |
| URL canonicalization (http→https, www→non-www) | Web Server / Hosting Config (Apache `.htaccess` RewriteRule) | — | Redirects are a server-config concern; no application code is involved |
| PHP-include layout (`header.php`/`footer.php`/`site-config.php`) | Server-side templating (PHP, request-time) | — | Closest analog to a "backend" tier in this stack — PHP executes per-request before the response is sent, but there is no API/JSON boundary, just server-rendered HTML |
| Backup & rollback (git, GitHub, live-mirror pull) | Local Dev Tooling / Ops | — | Not a runtime tier at all — this is entirely local-machine and CI-adjacent tooling that never executes on the host |
| Local preview (`php -S` or live `/new` URL) | Local Dev Tooling | Web Server (Apache, when previewing via `/new`) | D-01/D-02 make the live `/new` subfolder the primary preview path; a local PHP server is a secondary, optional convenience |
| Staging-subfolder de-indexing (`X-Robots-Tag`) | Web Server / Hosting Config (Apache `.htaccess`) | — | HTTP response headers are the only mechanism that works for a subfolder on the same domain (see Common Pitfalls) |

## Standard Stack

### Core

No new runtime dependencies are introduced by this phase — it deliberately uses only what the host already proves it runs (per SUMMARY.md's PHP-include decision).

| Tool | Version | Purpose | Why Standard |
|------|---------|---------|--------------|
| PHP (server-side, on `bell.host.bg`) | **5.2.17** `[VERIFIED: X-Powered-By header, curl, 2026-08-04]` | Executes `includes/*.php` and page templates parsed via `.htaccess` | Already running on the host (proves `mailer.php` works today); this phase must write code compatible with this specific, very old version, not assume a modern PHP feature set |
| Apache (server-side) | Unknown minor version, `Server: Apache` header only `[VERIFIED: curl -I, 2026-08-04]` | Serves all files, executes `.htaccess` directives | Already the host's web server; `.htaccess` is the only configuration surface available without a control-panel login |
| Git | Local, already initialized (`git log` shows 5 existing commits) `[VERIFIED: git log, 2026-08-04]` | Local source of truth / rollback mechanism | Already adopted per PROJECT.md's Key Decisions |
| curl | 8.7.1, local machine `[VERIFIED: curl --version, 2026-08-04]` | FTP/FTPS upload+download, HTTP header verification | Already installed locally; supports `ftp`/`ftps` protocols directly (`Protocols: ... ftp ftps ...`), so it can substitute for `lftp`/`wget` for both the FTP upload workflow (D-02) and a scriptable live-mirror backup pull, neither of which are installed |

### Supporting

| Tool | Version | Purpose | When to Use |
|------|---------|---------|-------------|
| Homebrew | 4.6.0, local machine `[VERIFIED: brew --version, 2026-08-04]` | Package manager for optional local tooling | Available if the team wants to install `php` (for local `php -S` preview) or `lftp` (nicer mirroring UX than raw curl loops) — neither is required given D-01/D-02's live-preview-first workflow |
| GitHub CLI (`gh`) | Not installed/authenticated `[VERIFIED: D-05, already confirmed in CONTEXT.md]` | Would let Claude create the private repo directly | Not available this session — proceed per D-05, wire the remote once the user shares the URL |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| curl-based FTP loop for backup/upload | `lftp mirror` | `lftp` gives cleaner recursive mirror semantics (`--reverse`, `--only-newer`) but is not installed locally and would need a Homebrew install first; curl already works and needs zero new installs |
| Manual `<?php phpinfo(); ?>` probe file | cPanel "PHP Version" info panel | No confirmed cPanel/control-panel login exists for this project (only FTP credentials are documented in PROJECT.md) — the file-probe method works with FTP-only access |

**Installation:** None required — this phase introduces no new local or server-side dependencies. (Optional, if local preview is desired: `brew install php`.)

**Version verification:** PHP server version was verified directly via the live `X-Powered-By` HTTP response header, not assumed from training data or hosting-provider marketing pages — this is the single most important number in this phase's research, since it invalidates any assumption of PHP ≥7 conventions.

## Package Legitimacy Audit

Not applicable — this phase installs no packages (no `npm`/`pip`/`composer` dependencies are introduced; PHPMailer, the one external library the project will eventually add, is explicitly scoped to Phase 4, not this phase). The PHP-version risk that PHPMailer 6.x will encounter is flagged above under Summary and again under Common Pitfalls so the planner can decide whether to front-load a PHP-version-bump task into this phase instead of discovering it as a Phase 4 blocker.

## Architecture Patterns

### System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│  Local dev machine (no PHP/lftp/wget installed; git, curl, Node OK)  │
│                                                                       │
│   1. Edit includes/header.php, footer.php, site-config.php           │
│      (PHP 5.2-safe syntax: array(), no closures, no namespaces)      │
│   2. git commit  →  git push origin main (once GitHub remote exists) │
│   3. curl -T <file> ftp(s)://bell.host.bg/public_html/new/<path>     │
└───────────────────────────────┬───────────────────────────────────────┘
                                 │ FTP/FTPS upload (curl, scripted)
                                 ▼
┌─────────────────────────────────────────────────────────────────────┐
│  bell.host.bg — cPanel/Apache/PHP 5.2.17, shared hosting              │
│                                                                       │
│   public_html/                      (LIVE — untouched until Phase 4) │
│   ├── .htaccess                     (cPanel PHP stub only, no rules) │
│   ├── index.html, laptopi.html, ... (16 pages, unchanged)            │
│   └── mailer.php                    (unchanged this phase)           │
│                                                                       │
│   public_html/new/                  (THIS PHASE'S WORKING ROOT)      │
│   ├── .htaccess                     (NEW: AddType .html-as-PHP +     │
│   │                                  canonicalization RewriteRules + │
│   │                                  X-Robots-Tag: noindex header)   │
│   ├── phptest.html                  (spike file — delete after use)  │
│   ├── includes/                                                      │
│   │   ├── header.php  footer.php  site-config.php                   │
│   └── (page skeletons, using the exact same 16 filenames as live)    │
└─────────────────────────────────────────────────────────────────────┘
                                 ▲
                                 │ GET https://torin.bg/new/...
                                 │ (D-02: visit URL to check progress,
                                 │  no FTP client needed by the user)
┌───────────────────────────────┴───────────────────────────────────────┐
│                          Browser (Claude / user, verifying)           │
└─────────────────────────────────────────────────────────────────────┘
```

### Recommended Project Structure

```
torin/
├── site-current/                 # existing — live FTP mirror, reference baseline (16 pages verified)
├── src/ (or a name of the planner's choosing)  # new source tree mirroring public_html/new/
│   ├── includes/
│   │   ├── header.php            # PHP 5.2-safe: no closures, no namespaces
│   │   ├── footer.php
│   │   └── site-config.php       # array(...) not [...]  — PHP 5.2 has no short array syntax
│   ├── .htaccess                 # AddType .html-as-PHP + canonicalization + X-Robots-Tag noindex
│   └── (16 page skeletons, same filenames as site-current/)
├── .planning/
└── filezilla-server-data.xml     # gitignored — unchanged, reused for FTP creds
```

### Pattern 1: PHP 5.2-safe include layout

**What:** Same PHP-include pattern ARCHITECTURE.md already recommends, adjusted for the confirmed PHP 5.2.17 runtime.
**When to use:** All shared-chrome files this phase scaffolds.
**Key adjustment vs. ARCHITECTURE.md's example code:** ARCHITECTURE.md's `site-config.php` example uses `[ ... ]` short-array syntax and `<?= ... ?>` short-echo tags — `[]` requires PHP 5.4+ `[CITED: mediawiki.org/wiki/PHP_5.4, wayson.github.io PHP 5.2-5.6 comparison]` and is a hard parse error on 5.2. Use `array()` instead. `<?= ?>` (short echo) has existed since PHP 5.4-and-earlier via the `short_open_tag` ini setting in some configs, but is not guaranteed on `[VERIFIED: unconfirmed for this host]` — use full `<?php echo ...; ?>` to avoid a second unverified assumption stacking on top of the first.

**Example (adjusted for PHP 5.2):**
```php
<?php
// includes/site-config.php — PHP 5.2-safe (no short array syntax, no namespaces)
$site = array(
  'phone'   => '+359 ...',
  'email'   => 'office@torin.bg',
  'hours'   => 'Пон-Пет 09:00-18:00',
  'nav'     => array(
    array('label' => 'Лаптопи', 'href' => 'laptopi.html'),
    array('label' => 'Заляти течности', 'href' => 'zalivane-technosti.html'),
  ),
);
```

### Pattern 2: `.htaccess` `.html`-as-PHP spike test (combined with PHP-version probe)

**What:** A single small file that proves two things at once — (a) `.html` files inside `public_html/new/` are parsed by the PHP interpreter, and (b) exactly which PHP version is doing the parsing.
**When to use:** Once, early in this phase, before any real page templates are built on top of the assumption that it works.
**Example:**
```
<!-- public_html/new/.htaccess -->
AddType application/x-httpd-php .html .htm
```
```php
<!-- public_html/new/phptest.html -->
<?php echo 'PHP-IN-HTML-OK ' . phpversion(); ?>
```
Then `curl https://torin.bg/new/phptest.html`. Two outcomes:
- Output is literally `PHP-IN-HTML-OK 5.2.17` (or similar) → confirmed working, safe to proceed.
- Output is the raw literal text `<?php echo 'PHP-IN-HTML-OK ' . phpversion(); ?>` → the directive did not take effect; the host's PHP handler is not registered under `application/x-httpd-php` for this account (common on suPHP/PHP-FPM/lsapi setups where the handler is registered under a different name, e.g. `x-httpd-php7` or a `php-script` handler alias). Fall back to trying `AddHandler application/x-httpd-php5 .html .htm` or `AddHandler php-script .html .htm` `[CITED: multiple hosting-provider KB articles, see Sources]` — the exact working directive is host-specific and cannot be determined without this live test.

**Cleanup:** Delete `phptest.html` (or restrict it) once confirmed — `phpversion()` output is low-sensitivity but there is no reason to leave a debug probe reachable indefinitely.

### Pattern 3: Canonicalization + staging-subfolder de-index, both via one `.htaccess`

**What:** The `public_html/new/.htaccess` also carries the canonicalization redirect rules being developed/tested for the eventual root `.htaccess` (D-06/D-07), plus an isolated `X-Robots-Tag: noindex` for the whole `/new/` subtree — all without touching the live root.
**Why this belongs together:** Both are Apache-config-level concerns solved in the same file, and testing the canonicalization RewriteRules against `/new/` first (before they ever touch the live root in Phase 4) is a safe rehearsal of exactly the kind of change PITFALLS.md's Pitfall 3 warns needs a dry run.
**Example:**
```apache
# public_html/new/.htaccess
RewriteEngine On

# De-index the staging subfolder — robots.txt cannot do this for a subfolder
# on the same domain (see Common Pitfalls); X-Robots-Tag is the correct tool.
<IfModule mod_headers.c>
  Header set X-Robots-Tag "noindex, nofollow"
</IfModule>

# Rehearsal only — canonicalization rules that WILL move to the root .htaccess
# at Phase 4 cutover, tested here first against /new/ paths.
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
RewriteCond %{HTTP_HOST} ^www\.torin\.bg$ [NC]
RewriteRule ^(.*)$ https://torin.bg/$1 [R=301,L]

AddType application/x-httpd-php .html .htm
```
**Caveat:** `mod_headers` availability should be confirmed the same way as the PHP handler — if `Header set` silently has no effect, the host may not have `mod_headers` enabled (uncommon on cPanel but not universal); verify with a `curl -I` check for the `X-Robots-Tag` response header after deploying this file, the same way the PHP spike is verified.

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Recursive backup of the live `public_html` | A custom recursive-FTP-listing-and-download script from scratch | `curl` with an explicit, hand-written file list (from the already-known 16-page + assets inventory), OR `brew install lftp` once and use `lftp mirror` | The file list for this site is small and already fully known (`site-current/` mirror + `find` listing below) — a generic recursive-FTP-crawl tool is unnecessary engineering for ~24 root-level items and one `assets1/` folder |
| Keeping staging subfolder out of Google's index | A `robots.txt` placed at `/new/robots.txt` | `X-Robots-Tag: noindex` HTTP header via `.htaccess` scoped to `/new/` | Google explicitly ignores `robots.txt` files outside the true domain root `[CITED: developers.google.com/crawling/docs/robots-txt/robots-txt-spec]` — this is not a style preference, the subfolder-robots.txt approach silently does nothing |
| Detecting server PHP version | Guessing from hosting-provider marketing copy or ARCHITECTURE.md's assumption | A one-line `<?php echo phpversion(); ?>` probe (already partially done this session via the `X-Powered-By` header, but that header can be spoofed/disabled server-side, so a direct probe during the spike test is the authoritative confirmation) | Assuming a modern PHP version when the real one is 5.2.17 would produce code that fatally errors on the live host the first time it's uploaded |

**Key insight:** Every "don't hand-roll" item here traces back to the same principle — this host is old and under-documented from the outside, so anything that can be *directly tested* against it (PHP version, handler behavior, header support) should be, rather than assumed from generic cPanel/shared-hosting documentation.

## Runtime State Inventory

This phase is a migration-safety phase, not a code rename — but MIGR-01/MIGR-03/SEO-04 are explicitly about preserving external state through a structural change, so this inventory is answered explicitly per the trigger's intent.

| Category | Items Found | Action Required |
|----------|-------------|------------------|
| Stored data | None — no database, no CMS. The only "stored data" analog is Google's own search index of the 16 current URLs, which is external and not under this project's control. | No migration; URL preservation (SEO-04) is the mitigation, not a data migration |
| Live service config | Google Search Console site-verification file `google1718743335455f1c.html` at root `[VERIFIED: curl 200 OK + `cat` contents, 2026-08-04 — content is `google-site-verification: google1718743335455f1c.html`]`. No DNS records need to change (no hosting migration). No CDN detected — `Server: Apache` header only, no CDN-vendor headers observed. | Must-carry file for Phase 4 cutover; not touched by this phase since work is isolated to `/new/`. Re-verify GSC ownership via a second method (DNS TXT or Analytics) once GSC access is regained (D-08/D-09), independent of the file staying in place |
| OS-registered state | None — shared hosting with FTP-only access; no Task Scheduler/pm2/systemd/cron jobs are referenced anywhere in `site-current/` or PROJECT.md | Nothing to re-register |
| Secrets/env vars | FTP credentials live in `filezilla-server-data.xml` (gitignored, confirmed via `.gitignore` contents) `[VERIFIED: Read .gitignore, 2026-08-04]`. No SOPS/env-var files exist in the repo. | No change — code/rename-only concern doesn't apply; reuse credentials as-is for the curl-based upload/backup scripts this phase may introduce |
| Build artifacts | None — confirmed no `package.json`, no build tooling, no compiled output anywhere in `site-current/` or the repo root (only `.gitignore`, `filezilla-server-data.xml`, `.DS_Store` at repo root besides `.planning/` and `site-current/`) `[VERIFIED: directory listing, 2026-08-04]` | Nothing to reinstall or invalidate |

**Nothing found in category:** Stored data, OS-registered state, and build artifacts are all explicitly empty — verified by direct inspection this session, not assumed.

## Common Pitfalls

### Pitfall A: Assuming a modern PHP version when writing the include layout

**What goes wrong:** Code using `[]` array literals, closures, or namespaced classes is written for `includes/*.php`, uploaded, and fatally errors on the live host (parse error, white screen) the first time any page is requested.
**Why it happens:** ARCHITECTURE.md's own example code uses `[ ... ]` short-array syntax, and most PHP tutorials/AI training data default to PHP 7+ idioms. Nothing in the codebase visibly signals "this host runs PHP 5.2" until you check the response header.
**How to avoid:** Every file written in this phase uses `array()`, no closures, no namespaces, full `<?php ?>` tags (not `<?= ?>` unless separately confirmed). Confirm with the spike-test probe (Pattern 2 above) before writing the real `site-config.php`.
**Warning signs:** A blank white page or a "Parse error: syntax error, unexpected '['" in the site's error log after any upload.

### Pitfall B: PHPMailer silently can't be installed later because of this same PHP version

**What goes wrong:** Phase 4 planning assumes PHPMailer 6.x can just be vendored in, discovers at that point that PHP 5.2.17 doesn't meet PHPMailer 6.x's PHP ≥5.5 requirement `[CITED: github.com/PHPMailer/PHPMailer discussion #3093]`, and the hardened-mailer work stalls mid-phase.
**Why it happens:** STATE.md already flagged "PHP version and Composer availability... needed to confirm PHPMailer install path" as a Phase 4 dependency but scoped the *detection* to this phase's foundation work — if that detection doesn't happen now, the risk surfaces at the worst possible time (mid-Phase-4, after other Phase 4 work is already built on top of the assumption).
**How to avoid:** During this phase's spike test, also check whether the host offers a way to select a newer PHP version per-domain (cPanel's "MultiPHP Manager" is the standard tool for this `[CITED: multiple hosting-provider KB articles, see Sources]`) — but note this project has no confirmed cPanel login (only FTP creds are documented). Flag this explicitly to the user as an open question: does a cPanel/control-panel login exist for `bell.host.bg`, separate from the FTP-only credentials in `filezilla-server-data.xml`? If not, PHPMailer 6.x may need to be swapped for an older PHPMailer major version (5.x line, which supports older PHP) or a different low-level mail library compatible with 5.2 — a decision to make in Phase 4, but the underlying fact must be surfaced now.
**Warning signs:** `composer require phpmailer/phpmailer` (or manually vendoring the latest release) failing PHP-version checks, or PHPMailer classes fatally erroring with syntax-related messages if actually run under 5.2.

### Pitfall C: Using `robots.txt` to keep `torin.bg/new` out of Google's index

**What goes wrong:** A `robots.txt` file is placed inside `public_html/new/` expecting it to disallow crawling of that subtree, per PITFALLS.md's Pitfall 8 suggestion ("a separate disallow-all robots.txt on any staging location"). It does nothing — Google only reads `robots.txt` from the true domain root `[CITED: developers.google.com/crawling/docs/robots-txt/robots-txt-spec]`, and a root-level `robots.txt` change is out of scope for this phase per D-01 (root untouched until Phase 4).
**Why it happens:** "Put a robots.txt in the staging folder" is intuitive but factually wrong for a same-domain subfolder (it's correct only for a separate subdomain or a genuinely separate host).
**How to avoid:** Use `X-Robots-Tag: noindex, nofollow` as an HTTP response header, set via `Header set X-Robots-Tag "noindex, nofollow"` inside `public_html/new/.htaccess` only (Pattern 3 above) — this is scoped per-directory and requires no root changes.
**Warning signs:** `torin.bg/new/...` pages start appearing in `site:torin.bg` search results during the build.

### Pitfall D: Treating "16 pages" as a fixed, already-final URL list without noting the borderline ones

**What goes wrong:** `covid.html`, `test-laptop.html`, and `problem-stari.html` get silently dropped or silently kept without an explicit keep/retire decision, because the inventory step treats "file exists in `site-current/`" as equivalent to "should exist in the new site."
**Why it happens:** PITFALLS.md's own Pitfall 9 already flags `covid.html` as likely-stale; `problem-stari.html` ("old problem[s]") and `test-laptop.html` (the self-diagnostic tool, which DIFF-01 explicitly wants surfaced more prominently, not retired) are two more pages whose fate isn't a foregone conclusion.
**How to avoid:** The MIGR-01 inventory artifact this phase produces should have an explicit per-URL disposition column (keep-as-is / retire-with-redirect / content-owner-decision-pending), not just a list of filenames. `covid.html` is a strong retire candidate (redirect to `about.html` or homepage); `test-laptop.html` and `problem-stari.html` need an explicit decision recorded, not a default.
**Warning signs:** The inventory artifact is just a bare list of 16 filenames with no "what happens to each one" column.

## Code Examples

### Full 16-page live inventory (verified by direct directory listing this session)

```
[VERIFIED: `find site-current -maxdepth 1 -type f` + `ls -la`, 2026-08-04]
index.html
about.html
covid.html
laptopi.html
mehanichni-problemi.html
msg.html
optimizatsiq.html
problem-stari.html
profilaktika-laptop.html
rezervni-chasti.html
test-laptop.html
tokov-udar.html
uslovia.html
warrently.html
za-bateriite.html
zalivane-technosti.html
```
16 `.html` files total — matches REQUIREMENTS.md/PITFALLS.md's stated "16 live pages" figure exactly. Also present at root (not pages, must-carry non-content files): `.htaccess`, `favicon.ico`, `google1718743335455f1c.html`, `header.js`, `otpuska.js`, `mailer.php`, `error_log`, plus the `.well-known/`, `cgi-bin/`, `covid-19/`, and `assets1/` directories.

### Current root `.htaccess` (verbatim, confirms nothing needs preserving beyond the stub)

```
[VERIFIED: Read/cat site-current/.htaccess, 2026-08-04]

# php -- BEGIN cPanel-generated handler, do not edit
# This domain inherits the "PHP" package.
# php -- END cPanel-generated handler, do not edit
```
No active `AddType`/`AddHandler`/`RewriteRule` today — confirms ARCHITECTURE.md's note that "nothing existing needs preserving beyond the file's presence" and that this phase's new directives are additive, not a rewrite of existing rules.

### `.well-known/.htaccess` (verbatim — must-carry, unrelated to the PHP-parsing work)

```
[VERIFIED: cat site-current/.well-known/.htaccess, 2026-08-04]
Require all granted
RewriteEngine Off

<FilesMatch "\.(txt)$">
	Require all granted
</FilesMatch>

<FilesMatch "\.(txt)$">
	Allow from all
</FilesMatch>
```
This exists to allow public access to `.txt` files under `.well-known/` (commonly used for ACME/SSL domain-validation challenges) — carry forward unchanged at Phase 4 cutover; irrelevant to this phase's `/new/` work.

### Live host verification results (this session)

```
[VERIFIED: curl -I against all 4 variants, 2026-08-04]
http://torin.bg    → HTTP 200, no redirect
https://torin.bg   → HTTP 200, no redirect
http://www.torin.bg  → HTTP 200, no redirect
https://www.torin.bg → HTTP 200, no redirect

[VERIFIED: curl -I https://torin.bg, 2026-08-04]
server: Apache
(no x-powered-by on static .html — expected, since .html is not currently PHP-parsed)

[VERIFIED: curl -I https://torin.bg/mailer.php, repeated 3x, 2026-08-04]
HTTP/2 302
x-powered-by: PHP/5.2.17
location: msg.html

[VERIFIED: curl -o /dev/null -w "%{http_code}" checks, 2026-08-04]
robots.txt              → 404 (absent, confirms PITFALLS.md Pitfall 8)
sitemap.xml             → 404 (absent, confirms PITFALLS.md Pitfall 8)
google1718743335455f1c.html → 200 (present, confirms D-08's must-carry file is currently live)
```

### Local dev machine tooling audit (this session)

```
[VERIFIED: which/--version checks, 2026-08-04]
git      → available (5 existing commits, no remote configured yet — confirms D-05's "repo doesn't exist yet")
curl     → 8.7.1, supports ftp/ftps protocols directly
node     → v20.18.0
brew     → 4.6.0 (Homebrew available for optional installs)
php      → NOT installed ("command not found")
composer → NOT installed
lftp     → NOT installed
wget     → NOT installed
ftp (CLI) → NOT installed
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|---------------|--------|
| suPHP as the standard cPanel PHP handler | PHP-FPM (or LiteSpeed's `mod_lsapi`) as the modern default | suPHP is now widely deprecated/unmaintained across cPanel hosts `[CITED: quickhost.uk suPHP-deprecated blog, 2025]` | Which `.htaccess` directive actually works (`AddType` vs `AddHandler php-script` vs a numbered handler name) depends on which of these this specific host uses — must be spike-tested, not assumed from generic docs |
| `robots.txt` per-folder to block crawling of a subsection | `X-Robots-Tag` HTTP header (via `.htaccess` or server config) for anything other than the whole domain root | Long-standing Google behavior, not a recent change, but easy to get wrong when following generic "just add a robots.txt" advice | Directly changes how this phase should keep `/new/` out of the index (see Pitfall C) |

**Deprecated/outdated:** suPHP is deprecated industry-wide; if `bell.host.bg` is confirmed to use it (unclear without a cPanel login), that's independently worth flagging to the user as a hosting-provider-side concern, separate from this project's scope.

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | The `X-Powered-By: PHP/5.2.17` header accurately reflects the PHP version actually used to interpret uploaded `.php`/`.html` files (rather than, e.g., a stale/misconfigured header while the real interpreter differs) | Summary, Standard Stack, Pitfall A/B | Low-medium — the spike test (Pattern 2) in this same phase directly confirms the real interpreter version via `phpversion()`, so this assumption is self-correcting within the phase; flagged here only because the header alone, without the spike test, would be an assumption |
| A2 | No cPanel/hosting-control-panel login exists for this project beyond the FTP-only credentials in `filezilla-server-data.xml` | Pitfall B | Medium — if a cPanel login does exist and simply wasn't documented, MultiPHP Manager could resolve the PHP-version risk (Pitfall B) trivially; if it doesn't exist, that risk needs a different mitigation (older PHPMailer, or a hosting-provider support ticket) — this should be an explicit question to the user before Phase 4 planning |
| A3 | `mod_headers` is enabled on this host (required for the `X-Robots-Tag` / canonicalization approach in Pattern 3) | Architecture Patterns (Pattern 3) | Low-medium — common on cPanel by default, but not universal; the same spike-test discipline (curl and check for the response header) resolves this without extra research |
| A4 | The Google-search-based URL inventory substitute (site:torin.bg) will surface the same URL set as a real GSC "Pages" export would | Phase Requirements (MIGR-01) | Medium — a public site: search can under- or over-report vs. GSC's authoritative index (e.g., recently de-indexed or newly-crawled-but-not-yet-ranked pages); this is exactly what D-09 already accepts as a known, temporary gap to retrofit later |

## Open Questions

1. **Does a cPanel (or other hosting control-panel) login exist for `bell.host.bg`, separate from the FTP-only credentials already documented?**
   - What we know: PROJECT.md and `filezilla-server-data.xml` document FTP access only. The live `.htaccess` shows cPanel-generated content, meaning cPanel is the hosting platform — but "the hosting platform is cPanel" doesn't mean this project has a login to it.
   - What's unclear: Whether MultiPHP Manager (or any PHP-version-selection tool) is reachable at all for this project.
   - Recommendation: Ask the user directly during this phase, before Phase 4 planning locks in a PHPMailer version — the answer changes whether Pitfall B's mitigation is "bump PHP version" (fast) or "use an older mail library" (more code changes).

2. **Is the `robots.txt`/`sitemap.xml` addition itself (Pitfall 8, not this phase's `/new/`-scoping concern) meant to happen in this phase or a later one?**
   - What we know: ROADMAP.md scopes `robots.txt`/`sitemap.xml` explicitly to Phase 4 (SEO-03), not Phase 1.
   - What's unclear: Nothing structurally — this is just a reminder that this phase's `X-Robots-Tag` work is solely about hiding `/new/`, and is unrelated to (and should not be conflated with) the eventual root `robots.txt`/`sitemap.xml` deliverable in Phase 4.
   - Recommendation: Keep the two concerns explicitly separate in the plan so a future reader doesn't assume Phase 1 already delivered SEO-03.

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP (local) | Optional local preview via `php -S localhost:8000` (ARCHITECTURE.md Pattern 3) | ✗ | — | Not required given D-01/D-02: primary preview path is the live `torin.bg/new` URL. Install via `brew install php` only if local preview is explicitly wanted |
| Composer (local) | Vendoring PHPMailer later (Phase 4, not this phase) | ✗ | — | PHPMailer ships as plain PHP files and can be vendored manually without Composer |
| `lftp` (local) | Nicer recursive mirror UX for backups | ✗ | — | `curl` (already available, supports `ftp`/`ftps`) with an explicit file list, since the site's file inventory is small and fully known |
| `wget` / `ftp` CLI (local) | Alternative mirror/backup tools | ✗ | — | Same fallback as `lftp` — use `curl` |
| Git | Local source of truth (MIGR-03) | ✓ | (repo already has 5 commits) | — |
| GitHub CLI (`gh`) | Automated private-repo creation (D-04) | ✗ | — | Per D-05: user creates the repo manually via github.com and shares the URL; Claude wires the remote afterward |
| curl | HTTP verification + FTP upload/download | ✓ | 8.7.1 | — |
| Homebrew | Optional local tool installs | ✓ | 4.6.0 | — |
| Node.js | Not required by this phase's PHP-include foundation (no build step in this architecture) | ✓ | v20.18.0 | N/A — present but unused by this phase's approach |

**Missing dependencies with no fallback:**
- None — every missing local tool has a documented, workable fallback above.

**Missing dependencies with fallback:**
- PHP (local): fallback is to skip local preview entirely and rely on the live `/new` URL (already the primary D-01/D-02 workflow), or `brew install php` if local preview is still wanted.
- `lftp`/`wget`/`ftp` CLI: fallback is `curl`, already installed and already ftp/ftps-capable.
- GitHub CLI: fallback is manual repo creation by the user (already the agreed D-05 plan).

## Security Domain

ASVS Level 1, per project config. This phase makes no visitor-facing changes and introduces no authentication, session, or user-input-handling code (that's Phase 4's `mailer.php` hardening, CONTACT-03) — most ASVS categories are not applicable to this phase's actual surface area. What is applicable:

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-------------------|
| V2 Authentication | No | No auth surface introduced this phase |
| V3 Session Management | No | No sessions introduced this phase |
| V4 Access Control | Yes (narrow) | The `.htaccess` spike-test file (`phptest.html`/`phpinfo.php`) must not be left permanently reachable — delete after verification, since `phpinfo()`-style output discloses server configuration details to any visitor who finds the URL |
| V5 Input Validation | No | No form/input handling introduced this phase (contact form hardening is Phase 4/CONTACT-03) |
| V6 Cryptography | Yes (narrow) | The http→https canonicalization redirect (D-06/D-07) is itself the relevant control — ensures no visitor traffic is ever served unencrypted on the canonical URL going forward |
| V12 Files and Resources (config/handler safety) | Yes | The `.htaccess` `AddType`/`AddHandler` directive must be scoped precisely (`.html .htm` only) — an overly broad handler directive risks parsing unintended file types as PHP, or (misapplied) causing PHP source to be served as plain text instead of executed, both of which are configuration-safety concerns specific to this phase's spike work |

### Known Threat Patterns for this stack

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|----------------------|
| Debug/spike probe file left publicly reachable after use | Information Disclosure | Delete `phptest.html`/any `phpinfo()` probe immediately after confirming the result; never leave a `phpinfo()` call reachable on a production or production-adjacent (`/new`) path long-term |
| FTP credentials committed to git | Information Disclosure | Already mitigated — `filezilla-server-data.xml` is confirmed present in `.gitignore` `[VERIFIED: Read .gitignore, 2026-08-04]`; re-verify this stays true any time the gitignore file is touched |
| Private GitHub repo accidentally created/left public | Information Disclosure | When the user creates the repo per D-05, confirm visibility is set to Private before Claude pushes any history to it |
| Staging subfolder (`/new`) indexed and surfaced in search before launch | Information Disclosure (minor — pre-release content) | `X-Robots-Tag: noindex` scoped via `.htaccess` (Pattern 3) — not a `robots.txt`, per Pitfall C |
| Misconfigured `AddType`/`AddHandler` causing `.php` source to be served as plain text instead of executed | Information Disclosure (source code) | Verify with the spike test (Pattern 2) that PHP is actually *executing*, not just being served — a misconfigured directive can silently degrade to serving raw source, which is far worse than the failure mode of "not parsed at all" |

## Sources

### Primary (HIGH confidence — direct verification this session)
- `curl -I` against `https://torin.bg`, `http://torin.bg`, `https://www.torin.bg`, `http://www.torin.bg` (2026-08-04) — confirms D-06 (no redirects, all 200 OK)
- `curl -I https://torin.bg/mailer.php`, repeated 3x (2026-08-04) — confirms live PHP version is 5.2.17 via `X-Powered-By` header
- `curl -o /dev/null -w "%{http_code}"` against `/robots.txt`, `/sitemap.xml`, `/google1718743335455f1c.html` (2026-08-04) — confirms absence/presence
- Direct `Read`/`cat` of `site-current/.htaccess` and `site-current/.well-known/.htaccess` (2026-08-04)
- Direct directory listing (`ls -la`, `find`) of `site-current/` (2026-08-04) — confirms 16-page inventory
- Local tooling audit (`which`/`--version` for php, composer, lftp, wget, ftp, git, curl, node, brew) on the dev machine (2026-08-04)
- `git log`, `git remote -v`, `git status` on the project repo (2026-08-04) — confirms no remote configured yet, matches D-05

### Secondary (MEDIUM confidence — cross-checked web sources)
- [PHPMailer/PHPMailer discussion #3093 — Raising PHPMailer's minimum PHP version](https://github.com/PHPMailer/PHPMailer/discussions/3093) — PHPMailer 6.x requires PHP ≥5.5
- [PHPMailer/PHPMailer GitHub repo](https://github.com/PHPMailer/PHPMailer) — current stable line and compatibility range
- [PHP 5.2, 5.3, 5.4, 5.5, 5.6 Comparison — Wayson](https://wayson.github.io/2017/03/30/php-5.2-to-5.6-comparison/) — confirms namespaces/closures added in 5.3, short array syntax added in 5.4
- [PHP 5.4 — MediaWiki](https://www.mediawiki.org/wiki/PHP_5.4) — corroborates short array syntax introduction
- [How Google Interprets the robots.txt Specification — Google for Developers](https://developers.google.com/crawling/docs/robots-txt/robots-txt-spec) — robots.txt only valid at true domain root, ignored elsewhere
- [Robots Meta Tags Specifications — Google Search Central](https://developers.google.com/search/docs/crawling-indexing/robots-meta-tag) — X-Robots-Tag HTTP header behavior
- [suPHP and cPanel: What it is, why it's deprecated — QuickHost](https://www.quickhost.uk/blog/2025/04/30/suphp-and-cpanel-what-it-is-why-its-deprecated-and-what-to-use-instead/) — suPHP deprecation context
- [PHP handlers — catalyst2 knowledgebase](https://www.catalyst2.com/knowledgebase/server-management/php-handlers/) — suPHP/mod_php/PHP-FPM handler naming differences
- [How can I parse html as php? — multiple hosting KB articles (Canadian Web Hosting, AccuWebHosting, Encodable)](https://help.canadianwebhosting.com/php/how-do-i-parse-html-files-as-php) — standard `AddType`/`AddHandler` directive variants
- [Using .htaccess and X-Robots-Tag to noindex directories — WebmasterWorld](https://www.webmasterworld.com/apache/4598050.htm) — per-directory `Header set X-Robots-Tag` pattern
- [How to Use MultiPHP Manager: Set PHP Version per Domain — Bluehost](https://www.bluehost.com/help/article/multiphp-manager) — cPanel PHP-version-selection tool, requires a cPanel login (open question A2)
- [How to check PHP version and configuration — Namecheap](https://www.namecheap.com/support/knowledgebase/article.aspx/9397/2219/how-to-check-php-version-and-configuration/) — phpinfo() probe method for hosts without control-panel access
- [Git vs FTP for Website Deployment — 1Byte](https://blog.1byte.com/git-vs-ftp-for-website-deployment/) — git-as-source-of-truth + FTP-as-publish-step pattern

### Tertiary (already carried from prior project research, not re-verified this session)
- `.planning/research/PITFALLS.md`, `ARCHITECTURE.md`, `SUMMARY.md` — foundational research this phase builds directly on top of; corrections/refinements to specific claims are called out explicitly above (Pitfall C corrects Pitfall 8's robots.txt suggestion; Pitfall A/B refine the PHP-version unknown flagged in STATE.md)

## Metadata

**Confidence breakdown:**
- Live-host facts (PHP version, redirect behavior, file presence/absence): HIGH — directly verified via curl this session, repeated for the PHP-version finding
- `.htaccess` handler directive that will actually work: MEDIUM — standard, well-documented options exist, but the specific working directive for `bell.host.bg` is not yet spike-tested (that's this phase's own job, not something research can pre-determine)
- Cyrillic/URL-preservation architecture guidance: MEDIUM-HIGH — inherited from SUMMARY.md/ARCHITECTURE.md/PITFALLS.md, already MEDIUM-HIGH confidence there
- PHPMailer/PHP-version compatibility risk: MEDIUM — well-documented on PHPMailer's own GitHub, but whether it actually blocks Phase 4 depends on the still-open cPanel-access question (A2)

**Research date:** 2026-08-04
**Valid until:** Re-verify the live-host facts (PHP version, redirect behavior) if more than ~14 days pass before this phase's plan executes — shared-hosting providers occasionally push PHP version bumps or handler changes outside the account holder's control; everything else (PHP 5.x language-version facts, robots.txt spec behavior) is stable/unlikely to change.
