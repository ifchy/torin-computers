# Phase 4: Hardening & Cutover - Pattern Map

**Mapped:** 2026-09-17
**Files analyzed:** 27 (17 new, 10 modified)
**Analogs found:** 23 / 27

> Grounded in what is on disk: this is a **PHP-include static site** (`src/` + `src/includes/`
> + `site-config.php`), not Astro. Every analog below was read this session.

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|-------------------|------|-----------|----------------|---------------|
| `src/kontakti.html` (NEW) | page | request-response | `src/msg.html` | exact |
| `src/includes/contact-form.php` (NEW) | component/partial | request-response | `src/includes/brand-row.php` | exact |
| `src/includes/banner.php` (NEW) | component/partial | transform (config→markup) | `src/includes/footer.php` `.notice--info` block | exact |
| `src/includes/settings.php` (NEW) | service/config-loader | file-I/O | `src/includes/asset-version.php` | role-match |
| `src/settings.txt` (NEW) | config data | file-I/O | *(none)* | — |
| `src/contact-send.php` (NEW) | controller | request-response + file-I/O | `site-current/mailer.php` (**anti-pattern reference only**) | anti-analog |
| `src/includes/notify.php` (NEW) | service | event-driven fan-out | `src/includes/icons.php` (pure-function include shape) | partial |
| `src/includes/upload.php` (NEW) | service | file-I/O | *(none)* | — |
| `src/includes/spam-guard.php` (NEW) | middleware | request-response | *(none)* | — |
| `src/vendor/phpmailer/*` (NEW) | vendored lib | — | *(none — first vendored dep)* | — |
| `src/js/analytics.js` (NEW) | client script | event-driven | `src/js/site.js` | exact |
| `src/js/photo-resize.js` (NEW) | client script | transform | `src/js/site.js` | role-match |
| `src/robots.txt`, `src/sitemap.xml` (NEW) | static config | — | *(none on disk; both 404 live)* | — |
| `src/includes/site-config.php` (MOD) | config | CRUD | itself — extend in place | exact |
| `src/includes/jsonld.php` (MOD) | component | transform | itself | exact |
| `src/includes/header.php` (MOD) | layout | request-response | itself | exact |
| `src/includes/footer.php` (MOD) | layout | request-response | itself | exact |
| `src/includes/category-page.php` (MOD) | template renderer | transform | itself | exact |
| `src/index.html` (MOD) | page | request-response | itself | exact |
| `src/msg.html` (MOD) | page | request-response | itself | exact |
| `src/uslovia.html` (MOD) | page (prose) | — | itself | exact |
| `src/includes/icons.php` (MOD — `alert` glyph) | utility | — | `case 'check'` in same file | exact |
| `src/css/components.css` (MOD) | styles | — | `.notice` group, `.btn` group | exact |
| `src/.htaccess` (MOD) | server config | request-response | itself (5 concerns) | exact |
| `scripts/gen-sitemap.sh`, `scripts/sitemap-check.sh` (NEW) | dev tooling | batch | `scripts/asset-version-check.sh` / `deploy-new.sh` | role-match |
| `scripts/probes/cutover-sweep.js` (NEW) | test/probe | batch | `scripts/probes/svc-page.js` | exact |
| **Deletions:** `src/css/theme-a.css`, `src/includes/dev-switcher.php` | — | — | — | — |

---

## Pattern Assignments

### `src/kontakti.html` (page, request-response)

**Analog:** `src/msg.html` (45 lines — the smallest complete page in the tree; read it whole).

**Page-file shape** (`src/msg.html:21-25`, `:45`):
```php
$torin_title = 'Съобщението е изпратено · Торин';
$torin_desc  = 'Съобщението ви до Торин Компютърс е изпратено. ...';
require_once(dirname(__FILE__) . '/includes/header.php');
?>
<main> ... </main>
<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
```
Two page-scope variables assigned **before** the header require; `dirname(__FILE__)` is the
mandated 5.2-safe idiom (`header.php:9-10` bans the 5.3 magic constant tree-wide).

**Section rhythm** (`src/msg.html:27-41`): `<section class="section">` → `<section class="section
--tint">`, each wrapping a `<div class="container">`. Alternate by hand for two sections;
`torin_next_tint()` (`category-page.php:133-139`) is for the variable-length service spine only.

**Breadcrumbs:** call the existing renderer, do not re-author — `category-page.php:146-163`:
```php
function torin_render_breadcrumbs($crumbs) { ... }
// last crumb: <li><span aria-current="page">…</span></li>  — never a link
```
Also assign `$torin_crumbs` before including `footer.php` so `jsonld.php:104-130` emits the
matching `BreadcrumbList` from the **same array** (that is the stated no-drift contract).

**Do NOT type a contact literal.** `footer.php:34-55` is the model: every phone, the email, the
address and the map URL are read from `$site` and escaped at output.

---

### `src/includes/contact-form.php` (component/partial, request-response)

**Analog:** `src/includes/brand-row.php` — the canonical "one function, zero output on include,
callable partial" file.

**Include-boundary rule** (`brand-row.php:1-5`):
```php
// Emits nothing on include: it is one function definition and no top-level output,
// exactly like category-page.php, categories.php and site-config.php.
```
Only `header.php`, `footer.php` and `dev-switcher.php` may emit on include. `contact-form.php`
must therefore define `torin_render_contact_form($values, $errors)` and emit nothing at load —
this is what lets both `kontakti.html` and `contact-send.php` include it (UI-SPEC C-3's "one
partial, two callers").

**Function + global + guard shape** (`brand-row.php:56-62`):
```php
function torin_render_brand_row($tint_class = '') {
	global $site;
	// An empty (or absent) list emits NOTHING AT ALL — not the heading, not an empty <ul>.
```

**Escaping on every output** — `category-page.php:100-106`:
```php
// PHP 5.2 defaults htmlspecialchars() to ISO-8859-1 and every string on these pages is
// Cyrillic, so the charset argument is always passed.
function torin_esc($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```
Use `torin_esc()` (require `category-page.php`, which also owns `torin_has_content()`); the
error re-render path is exactly where this convention historically breaks (UI-SPEC C-8).

**Inline mixed-mode template style** (`footer.php:44-50`) — closing `?>` before markup, tabs,
no short echo tags:
```php
<?php foreach ($site['phones'] as $torin_phone) { ?>
	<a class="footer-phone" href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $torin_phone), ENT_QUOTES, 'UTF-8'); ?>">…</a>
<?php } ?>
```

---

### `src/includes/banner.php` (component/partial, config→markup)

**Analog:** the `.notice--info` band in `src/includes/footer.php:20-29` — the existing
"config value renders a strip, empty value removes it with no other edit" pattern.

```php
// Static, PHP-rendered replacement for the legacy otpuska.js banner. ...
// Emptying the config value removes the band with no other edit.
if ($site['notice'] !== '') { ?>
<p class="notice notice--info"><?php echo torin_icon('clock'); ?><span><?php echo htmlspecialchars($site['notice'], ENT_QUOTES, 'UTF-8'); ?></span></p>
<?php } ?>
```
The holiday banner is the same idiom with a **date gate** instead of a non-empty gate, and it
renders in `header.php` (first child of `#wrap`, `header.php:195`) rather than the footer.

**CSS to copy, not re-author** — `src/css/components.css:682-697`:
```css
.notice { display: grid; grid-template-columns: 1.25em 1fr; gap: var(--sp-sm);
          align-items: start; max-width: none; margin-block-end: var(--sp-xl);
          padding: var(--sp-md); border-radius: var(--r-md); }
.notice > svg { width: 1.25em; height: 1.25em; margin-block-start: .2em; }
```
UI-SPEC C-1 requires `.holiday-banner__inner` to use the identical three declarations
(`grid` / `1.25em 1fr` / `gap` / `align-items:start`) so the icon column cannot drift.
Keyline is `box-shadow: inset 0 -3px 0 var(--c-brand)` — **never** a `border` (amber in a
border is forbidden system-wide).

---

### `src/includes/settings.php` (service, file-I/O)

**Analog:** `src/includes/asset-version.php` — the tree's only other "read the filesystem at
request time, never throw, degrade to a detectable sentinel" module.

**Header-comment contract** (`asset-version.php:1-4`):
```php
// includes/asset-version.php — PHP 5.2-safe. Emits nothing on include; it is
// one function and no data, per this tree's include-boundary rule (only
// header.php, footer.php and dev-switcher.php produce markup).
```

**Fail-detectably, never fail-silently** (`asset-version.php:32-38`) — the exact mental model
for D4-23's last-known-good parse:
```php
// THE ?v=0 SENTINEL. If the stat fails — the path does not exist on the server,
// or filemtime() returns false — this function returns the path with a ZERO
// token rather than the bare path. ... a broken stamp becomes DETECTABLE
// (scripts/asset-version-check.sh asserts zero occurrences of it across all pages).
```
Mirror this: a dropped key falls back to the `site-config.php` default **per key** (never
whole-file), and the fallback must be observable by a check script.

**Path idiom** (`asset-version.php:57`): `$root = dirname(dirname(__FILE__));` — `settings.txt`
lives beside the page files, resolved the same way. Never `__DIR__`.

**Research constraint:** do **not** use `parse_ini_file()` (RESEARCH A-2) — hand-rolled
`fgets` + first-colon split, no warnings reaching output.

---

### `src/includes/site-config.php` (MODIFIED — config, CRUD)

**Analog:** itself. Two conventions are load-bearing.

**Provenance comment per value** (`site-config.php:13-20`):
```php
// Three separate numbers, never one joined string: each renders its own
// tel: link ... Sourced from the secondarybar block of site-current/index.html.
'phones' => array('02 9549710', '088 9458404', '087 9128244'),
```

**`[ASSUMED]` marker + the question that closes it** (`site-config.php:56-62`):
```php
// [ASSUMED] OWNER-QUESTIONS #20. ... It is also hard-coded into jsonld.php's
// opening hours — change BOTH when the owner answers.
'hours' => 'Понеделник – Петък, 8:00 – 16:00',
```
D4-25 closes this marker; D4-26 removes the "change BOTH" duplication.

**The cutover gate is already marked** (`site-config.php`, `base_url` entry):
```php
// ## CUTOVER GATE — this is the ONLY place the /new/ staging path segment  ##
// ## appears anywhere in src/. At Phase 4 cutover it becomes               ##
// ## https://torin.bg/ and that one edit is the whole change.              ##
'base_url' => 'https://torin.bg/new/',
```

**Deletion pattern:** the `'viber'` key (`site-config.php:64-114`, ~50 lines of failure history)
is removed wholesale — UI-SPEC C-7. Gate on `grep -rc "viber://chat" src/` summing to **0**;
a naïve `grep -rho | wc -l` returns 8 today because comments quote the scheme.

---

### `src/includes/jsonld.php` (MODIFIED — component, transform)

**Analog:** itself.

**Single-source-a-value-and-say-so** (`jsonld.php:38-46`):
```php
// Read from the single-sourced E.164 key. It used to be an independent literal
// here, which meant the number a search engine publishes and the number the
// page's own call buttons dial could drift apart silently ...
'telephone' => $site['phone_e164'],
```
Apply verbatim to the hours: `openingHoursSpecification` (`jsonld.php:63-71`) currently carries
the `'08:00'`/`'16:00'` literals and the `[ASSUMED]` restatement — both come from `settings.php`
after D4-26.

**Conditional-property idiom** (`jsonld.php`, `sameAs`):
```php
if (isset($site['gbp_url']) && trim($site['gbp_url']) !== '') {
	$torin_ld['sameAs'] = array($site['gbp_url']);
}
```
The holiday closure block (D4-27) is added the same way — appended to
`openingHoursSpecification` only when the dates parse and are current.

**JSON is encoded, never hand-written** (`jsonld.php:14-25`) — and note the comment's own
warning that a **PHP upgrade removes the automatic `/` escaping this file relies on**:
> *"A future PHP upgrade REMOVES that protection — whoever performs one must re-check this file."*
D4-01 triggers exactly that re-check. This is a cross-file consequence the planner must own.

---

### `src/includes/header.php` (MODIFIED — layout)

**Nav `aria-current` ternary to copy** (`header.php:217`):
```php
<li><a class="nav__link" href="index.html"<?php echo ($torin_nav_current === 'index.html' ? ' aria-current="page"' : ''); ?>>Начало</a></li>
```
`header.php:250` is the target: `<li><a class="nav__link" href="index.html#contact-us">Контакти</a></li>`
→ `href="kontakti.html"` plus the same ternary. `$torin_nav_current` is already in scope
(`header.php:42`, `basename($_SERVER['SCRIPT_NAME'])` — SCRIPT_NAME only, `header.php:26-29`
explains why the alternative is an XSS vector).

**Script tag + asset stamp** (`header.php:~184`):
```php
<script src="<?php echo htmlspecialchars(torin_asset_url('js/site.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
```
`js/analytics.js` follows this line exactly. The Umami third-party tag does **not** go through
`torin_asset_url()` and must not get `preconnect` (UI-SPEC C-9).

**Deletions to perform here** (`header.php:44-53`, `:185`, `:190-192`): the three `DEV-ONLY`
blocks and `<?php echo $torin_extra_head; ?>`. The new `$torin_robots` mechanism is a **separate**
production feature emitted next to `<meta name="description">` (`header.php:88`), using the same
escaping form.

---

### `src/includes/footer.php` / `category-page.php` / `index.html` (MODIFIED — CTA slots)

**The exact line to replace, 5 occurrences** (`footer.php:74`, `index.html:63`, `:303`, `:343`,
`category-page.php:554`):
```php
<a class="btn btn--primary" href="viber://chat?number=<?php echo rawurlencode($site['viber']); ?>"><?php echo torin_icon('chat'); ?>Пишете във Viber</a>
```
becomes (UI-SPEC C-7 — label «Пишете ни», `chat` icon reused, `.btn--primary` unchanged):
```php
<a class="btn btn--primary" href="kontakti.html" data-slot="footer"><?php echo torin_icon('chat'); ?>Пишете ни</a>
```
**Paired call button, unchanged, shows the escaping convention** (`footer.php:73`):
```php
<a class="btn btn--primary" href="tel:<?php echo htmlspecialchars($site['phone_e164'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo torin_icon('phone'); ?>Обадете се</a>
```
`data-slot` is added to these 7 rendered `tel:` anchors too (C-9), including the
`foreach ($site['phones'] …)` loop at `footer.php:50`.

---

### `src/includes/icons.php` (MODIFIED — one `alert` glyph)

**Analog:** `case 'check'` in the same file:
```php
case 'check':
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m4.5 12.5 5 5 10-11"/></svg>';
```
House style is stroke-only; `star` is the single documented `fill` exception and must stay so.
Update the "16" counts in the file header comment (`icons.php:5`, `:11`) when adding the 17th.

---

### `src/contact-send.php` (controller, request-response + file-I/O)

**No positive analog exists — there is no `<form>` and no handler anywhere in `src/`.**

**Anti-analog, read for what NOT to do:** `site-current/mailer.php:84-99`:
```php
$headers =  "From: <$email>\r\n";                      // From: built from USER INPUT
$headers .= "Content-type: text/html; charset=utf-8\r\n";
mail("office@torin.bg", "Въпрос към ТОРИН КОМПЮТЪРС…", $m2, $headers);  // return value DISCARDED
header("Location: msg.html");                          // redirect regardless of outcome
```
Three defects D4-10 / CONTACT-03 exist to not repeat: user-controlled `From:`, ignored return
value, unconditional success redirect. No honeypot, no escaping.

**Isolation rule (RESEARCH A-3):** this is the **only** file in the tree not written in the
5.2-safe dialect, and the only one that may `require` `vendor/phpmailer/`. No include chain from
`header.php` or `footer.php` may reach it. Its own header comment should say so, in the same
voice as `asset-version.php:1-4`.

---

### `src/js/analytics.js` and `src/js/photo-resize.js` (client scripts)

**Analog:** `src/js/site.js` (2,476 B, the entire JS surface today).

**IIFE + strict + early-return guard** (`site.js:7-11`):
```js
(function () {
	'use strict';
	var nav = document.querySelector('.nav');
	if (!nav) { return; }
```
UI-SPEC C-9 requires the same guard shape: `if (document.getElementById('contact-form'))`.

**No library, `var`, ES5-safe idioms** (`site.js:23`): `Array.prototype.forEach.call(...)` —
the file deliberately avoids modern array methods on NodeLists. Match it.

**Passive listeners, delegated at the document** (`site.js:50-58`):
```js
document.addEventListener('focusin', function (e) { if (!nav.contains(e.target)) { closeAll(null); } });
document.addEventListener('click',   function (e) { if (!nav.contains(e.target)) { closeAll(null); } });
```
This is precisely the shape C-9 mandates for `call-click`: delegated, **no `preventDefault`**,
nothing awaited in the activation path.

**Comment convention:** every non-obvious block states the WCAG clause or the defect it prevents
(`site.js:38-40`). Carry that into the `tel:`-tracking comment.

---

### `scripts/probes/cutover-sweep.js` (test/probe, batch)

**Analog:** `scripts/probes/svc-page.js`.

**Module contract** (`render-check.sh:32-33`):
> *A probe is a Node module exporting `async function run(session, cdp, opts)` and returning a
> JSON-serialisable result.*

```js
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);
	const expectUrgent = process.env.SVC_EXPECT_URGENT === '1';   // serialised IN, not read in page scope
	return await cdp.evaluate(session, `(() => { ... })()`);
}
```
**Two traps the analog documents and the sweep inherits** (`svc-page.js:22-33`): the viewport must
be set via `Emulation.setDeviceMetricsOverride` through `cdp.open()`, never a window flag; and
`process.env` does not exist inside the evaluated string.

**Measure the paint, not the class** (`svc-page.js:48-56`): the probe records
`getComputedStyle(el).backgroundColor` beside the class because *"a tinted band whose fill did not
apply would pass a class-only assertion and still look wrong."* Same discipline applies to D4-30:
**follow the redirect and assert final status + final URL**; grepping `^HTTP/` and `^location:`
passes the defect this project actually shipped.

**Invocation** (`render-check.sh:26-29`):
```
scripts/render-check.sh scripts/probes/cutover-sweep.js https://torin.bg/index.html 360 640
```

---

### `scripts/gen-sitemap.sh` / `sitemap-check.sh` (dev tooling, batch)

**Analog:** `scripts/asset-version-check.sh` (an assertion script over the built tree) and
`scripts/deploy-new.sh` for the bash house style.

**Bash preamble** (`deploy-new.sh:25-28`):
```bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
```
Long block comment at the top stating why the script exists and its exact usage lines
(`deploy-new.sh:1-22`) is the convention, not decoration.

---

### `src/.htaccess` (MODIFIED — server config, 5 concerns)

**Analog:** itself. Every rule already carries its own rationale block.

**The cutover header is already written** (`.htaccess:1-6`):
```apache
# At Phase 4 cutover, this file (or its rules) is promoted to the root .htaccess. TWO edits are
# required there, not one:
#   1. the canonicalisation target changes from https://torin.bg/new/$1 to https://torin.bg/$1
#   2. the RewriteBase directly below changes from /new/ to /
# Missing either one breaks a redirect silently, at 301, with no build failure.
```

**Hardcoded-literal redirect target, never `%{HTTP_HOST}`** (`.htaccess:20-25`):
```apache
RewriteCond %{HTTPS} off [OR]
RewriteCond %{HTTP_HOST} ^www\.torin\.bg$ [NC]
RewriteRule ^(.*)$ https://torin.bg/new/$1 [R=301,L]
```

**Module-guard every directive** (`.htaccess:106-109`, `:118-159`):
```apache
# an unguarded directive for an absent module returns 500 for the entire /new/ subtree (T-02-02)
<IfModule mod_expires.c>
	ExpiresByType text/html "access plus 0 seconds"
	ExpiresByType text/css  "access plus 5 minutes"     # ← D4-34 raises to 1 year
</IfModule>
```
The new `<Files>` denials for `settings.txt` / `.user.ini` and any handler change follow the same
one-concern-per-commented-block rule. **`php_value` is forbidden** (FastCGI → 500 subtree).

**To remove at promotion** (`.htaccess:80-84`): the `<IfModule mod_headers.c>` block carrying
`X-Robots-Tag "noindex, nofollow"`.

---

## Shared Patterns

### Escaping — applies to every new file that emits markup
**Source:** `src/includes/category-page.php:100-106`
```php
function torin_esc($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
```
The `ENT_QUOTES, 'UTF-8'` arguments are non-optional: PHP 5.2 defaults to ISO-8859-1 and every
string is Cyrillic. `header.php`/`footer.php`/`index.html` spell `htmlspecialchars(...)` inline;
files with many call sites use `torin_esc()`. Both forms are acceptable — pick one per file.

### 5.2-safe dialect — applies to every new file EXCEPT `contact-send.php`
**Source:** `src/includes/site-config.php:2`, `jsonld.php:1-3`, `brand-row.php:1-3`
```php
// PHP 5.2-safe (no short array syntax, no closures, no namespaces, no short echo tags, tabs)
```
Plus `dirname(__FILE__)` never `__DIR__` (`header.php:9-10`), and `array()` never `[]`.

### Include boundary — applies to every new `includes/*.php`
**Source:** `src/includes/brand-row.php:1-5`, `asset-version.php:1-4`
Only `header.php` and `footer.php` emit markup on include. Everything else defines functions and
emits nothing. `banner.php` is a partial **called from** `header.php`, not an emitting include.

### Requires at the top, `require_once` always
**Source:** `src/includes/header.php:11-24`
```php
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
require_once(dirname(__FILE__) . '/categories.php');
require_once(dirname(__FILE__) . '/asset-version.php');
```
Mutual `require_once` between two function-only files is documented-safe (`brand-row.php:39-46`).

### Single-source-a-value and record why
**Source:** `src/includes/jsonld.php:38-46`, `site-config.php` (`phone_e164` entry)
Any value that would otherwise exist twice is read from `$site` with a comment naming the drift
it prevents. Directly governs D4-26 (hours) and the settings→config→jsonld chain.

### Secrets outside git
**Source:** `scripts/deploy-new.sh:10-17`
> *"The password is base64-decoded entirely inside a short-lived Python process and written
> straight into a chmod-600 .netrc-style temp file … never printed, never placed in a shell
> variable, and never appears on any command line (so it cannot show up in `ps aux`)."*
The Telegram bot token and the SMTP password follow this exact handling. Also note `P-10`: a
no-argument `deploy-new.sh` uploads **everything** under `src/` — secrets must live outside `src/`.

### Commenting convention — load-bearing in this codebase
**Source:** every file read. Comments state the *defect prevented*, the *measurement taken*, and
the *thing a later editor must not "fix"* (e.g. `icons.php` `star` exception; `uslovia.html:25-29`
retention note addressed to Phase 4; `msg.html:11-20`). New Phase-4 files are expected to carry
the same density. Note `scripts/truth-gate.js` scans comments — a comment quoting a banned literal
trips its own gate (`brand-row.php:34-36`, `.htaccess:56-62`).

---

## No Analog Found

| File | Role | Data Flow | Reason |
|------|------|-----------|--------|
| `src/includes/upload.php` | service | file-I/O | No file upload exists anywhere in the tree. Use RESEARCH C-3 (`finfo` → `getimagesize()` → exif rotate → GD re-encode → random name). |
| `src/includes/spam-guard.php` | middleware | request-response | No form, no honeypot, no rate limiter, no HMAC anywhere. Use RESEARCH C-6 and UI-SPEC C-6. |
| `src/includes/notify.php` | service | event-driven | No outbound HTTP anywhere in `src/`. Only shape guidance available: pure-function include (`icons.php`), any-one-wins interface from RESEARCH A-1. |
| `src/vendor/phpmailer/*` | vendored lib | — | First vendored dependency; no `vendor/` dir, no Composer, no lockfile. RESEARCH prescribes 3 files + recorded SHA256. |
| `src/settings.txt` | config data | file-I/O | No plain-text config exists; all config is PHP. |
| `src/robots.txt`, `src/sitemap.xml` | static config | — | Neither exists in `src/` and both 404 on the live site. Creation, not migration. |
| `src/contact-send.php` | controller | request-response | Only anti-analog available (`site-current/mailer.php`). |

---

## Metadata

**Analog search scope:** `src/`, `src/includes/`, `src/css/`, `src/js/`, `scripts/`,
`scripts/probes/`, `site-current/`
**Files read this session:** `asset-version.php`, `icons.php`, `site-config.php`, `jsonld.php`,
`header.php` (targeted), `footer.php`, `brand-row.php` (head), `category-page.php` (targeted),
`msg.html`, `uslovia.html` (head), `index.html` (CTA slots), `components.css` (`.notice`),
`site.js`, `.htaccess` (directives + comments), `deploy-new.sh` (head), `render-check.sh` (head),
`probes/svc-page.js` (head), `site-current/mailer.php` (tail)
**Pattern extraction date:** 2026-09-17
