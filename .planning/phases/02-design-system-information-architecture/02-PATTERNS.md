# Phase 2: Design System & Information Architecture — Pattern Map

**Mapped:** 2026-08-05
**Files analyzed:** 27 (10 new, 17 modified)
**Analogs found:** 22 / 27

> **Codebase reality check:** the Phase 1 foundation is *four* files —
> `src/includes/header.php` (62 lines), `src/includes/footer.php` (18 lines),
> `src/includes/site-config.php` (10 lines), `src/.htaccess` (20 lines) — plus 17 identical
> 8-line page stubs. That is the entire in-repo pattern surface. There is **no CSS file, no JS
> file, no font, and no data-array-beyond-`$site` anywhere in `src/`.** Every analog below is one
> of those four files. `site-current/` is the "before" state and is cited only where Phase 2 must
> *harvest a value from it* (`business.css` `:root`) or *not repeat a mistake* (legacy `<head>`).

---

## File Classification

| New/Modified File | New/Mod | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|---|
| `src/includes/header.php` | MOD | layout partial | request-response (server include) | *itself* (rewrite in place) | exact |
| `src/includes/footer.php` | MOD | layout partial | request-response | *itself* + `header.php` | exact |
| `src/includes/site-config.php` | MOD | config/data | read-only data source | *itself* (extend `$site` array) | exact |
| `src/includes/categories.php` | NEW | config/data | read-only data source | `src/includes/site-config.php` | role-match |
| `src/includes/icons.php` | NEW | utility (fn library) | transform (name → SVG string) | *none in `src/`* | **no analog** |
| `src/includes/dev-switcher.php` | NEW | config + partial, dev-only | request-response (cookie/query) | `src/includes/site-config.php` (array + guard shape only) | partial |
| `src/index.html` | MOD | page template (homepage) | request-response | `src/index.html` / `src/about.html` (stub shape) | exact |
| 13 existing scaffolded pages | MOD | page template | request-response | `src/about.html` | exact |
| `ekran-klaviatura-portove.html` | NEW | page template (category) | request-response | `src/mehanichni-problemi.html` | exact |
| `pregryavane-ohlazhdane.html` | NEW | page template (category) | request-response | `src/mehanichni-problemi.html` | exact |
| `nestandartna-technika.html` | NEW | page template (category) | request-response | `src/mehanichni-problemi.html` | exact |
| `src/css/base.css` | NEW | stylesheet (tokens/reset) | static asset | `site-current/.../themes/business.css` (**harvest only**) | partial |
| `src/css/layout.css` | NEW | stylesheet | static asset | *none* | **no analog** |
| `src/css/components.css` | NEW | stylesheet | static asset | *none* | **no analog** |
| `src/css/theme-a.css` | NEW | stylesheet, dev-only | static asset | *none* | **no analog** |
| `src/js/site.js` | NEW | client script | event-driven (DOM) | *none in `src/`* | **no analog** |
| `src/fonts/sofia-sans-{cyrillic,latin}.woff2` | NEW | binary asset | static asset | `site-current/fonts/*.woff` (placement convention only) | partial |
| `src/.htaccess` | MOD | server config | request-response | *itself* | exact |

---

## Pattern Assignments

### `src/includes/header.php` (layout partial, request-response) — MODIFY

**Analog:** itself. It is the only file in the repo that opens a document, and every UI-SPEC
"Mandatory Phase-1 scaffold repairs" row targets it. Rewrite in place; keep the four load-bearing
conventions below and change everything else.

**Convention 1 — include + comment header (lines 1-6).** Keep verbatim shape; `dirname(__FILE__)`
is the PHP 5.2-safe idiom (`__DIR__` is 5.3+ and **must not** be introduced):

```php
<?php
// includes/header.php — PHP 5.2-safe shared head + contact-info chrome.
// Extracted from site-current/index.html lines 1-80 ...
require_once(dirname(__FILE__) . '/site-config.php');
?>
```

**Convention 2 — `lang="bg"` already correct (line 8).** SEO-02 is satisfied today. Do not
regress it; the legacy analog `site-current/about.html:2` says `lang="en"` — that is the bug this
line already fixed.

```php
<!DOCTYPE html>
<html lang="bg">
```

**Convention 3 — value interpolation from `$site` (lines 40, 51).** The only echo pattern in the
codebase. Every new contact value (address, hours, viber) uses this exact form, never a literal:

```php
<h3 class="font-size-14">Телефон: <?php echo $site['phone']; ?></h3>
...
<a href="mailto:<?php echo $site['email']; ?>"><?php echo $site['email']; ?></a>
```

**Convention 4 — tab-indented HTML, opening chrome left unclosed.** `header.php` opens
`<div id="wrap">` (line 22) and `<header>` (line 24) and closes `</header>` at line 61 but leaves
`#wrap` open for `footer.php:15`. This open/close split across two files is the templating
contract — preserve it whatever the new markup is.

**Defects to fix in this rewrite (each is a concrete line):**

| Line | Current | Required |
|---|---|---|
| 11 | favicon `<link>` precedes `<meta charset>` | `<meta charset="utf-8">` must be the **first** element in `<head>` |
| 14 | `<meta http-equiv="X-UA-Compatible" content="IE=edge">` | delete |
| 16 | `content="#3ed2a7"` | `content="#ffc70a"` (D-01) |
| 18 | `<title>` hardcoded for all 16 pages | `<?php echo $torin_title; ?>` with a default; add `<meta name="description">` |
| 20→22 | `</head>` then straight to `<div id="wrap">` — **no `<body>` at all** | emit `<body class="...">` |
| 37, 48 | `<i class="fa fa-phone">` / `fa-envelope-o`, no Font Awesome loaded → renders nothing | `torin_icon('phone')` / `torin_icon('mail')` |
| — | zero `<link rel="stylesheet">` | link `base.css`, `layout.css`, `components.css` in that order + font preload |

---

### `src/includes/footer.php` (layout partial, request-response) — MODIFY

**Analog:** itself + `header.php`. 18 lines today; D-33/D-34 make it the largest partial.

**Convention — closes what `header.php` opened (lines 15-18):**

```php
</div><!-- /#wrap -->

</body>
</html>
```

`</body>` here is currently orphaned (nothing opened it — see the `header.php` defect table). Once
`header.php` emits `<body>`, this file becomes correct with no change.

**Convention — dynamic year via `date()` (line 7).** The one existing dynamic-value pattern in the
footer; the UI-SPEC legal line reuses it exactly:

```php
<p class="my-0"><span style="font-size: 15px;">TORIN Company Ltd. &copy; <?php echo date("Y"); ?> г.</span></p>
```

Keep `date("Y")`; drop the inline `style` and the Bootstrap `col-md-3`/`my-0` classes — the
Bootstrap-4 utility layer is exactly what Phase 2 removes.

**New in this file:** three `tel:` links looped from `$site`, hours, Google-Maps address link, and
the `ComputerStore` JSON-LD block. JSON-LD renders here (not `header.php`) per RESEARCH's
responsibility map, and every value comes from `$site` — see Convention 3 above.

---

### `src/includes/site-config.php` (config/data) — MODIFY, and the analog for `categories.php`

**Analog:** itself. This is the **canonical data-file pattern** for the project and the thing
`categories.php` must imitate.

**Full file (10 lines) — the pattern in its entirety:**

```php
<?php
// includes/site-config.php — PHP 5.2-safe (no short array syntax, no closures, no namespaces)
// Single source of truth for site-wide contact values. Values sourced verbatim
// from site-current/index.html (phone, secondarybar block) and
// site-current/mailer.php (email, office@torin.bg).
$site = array(
	'phone' => '02 9549710, 088 9458404, 087 9128244',
	'email' => 'office@torin.bg',
);
?>
```

Four properties to copy: (1) a 5.2-safety note in the comment header, (2) **provenance comment
naming the source file** for every value, (3) a single top-level `array()` assigned to one
lowercase variable, (4) tab indentation, trailing comma, closing `?>`.

**⚠ Note the `phone` value is one comma-joined string, not a list.** UI-SPEC §7 requires three
separate `tel:` links and the UI-considerations table says "rendered by looping `site-config.php`".
Splitting `phone` into `array('02 9549710', '088 9458404', '087 9128244')` is a **breaking change
to `header.php:40`**, which echoes it as a scalar. Both files change together or the header prints
`Array`.

**New keys this phase adds** (each with the provenance comment convention, and `[ASSUMED]` inline
where RESEARCH N-3 / the Viber question is unresolved): `address`, `maps_url`, `hours`, `viber`,
`geo_lat`, `geo_lng`.

---

### `src/includes/categories.php` (config/data, read-only) — NEW

**Analog:** `src/includes/site-config.php` (role-match — same role, same flow, different shape).

Copy the five conventions above verbatim, then extend to a list-of-records plus one accessor:

```php
<?php
// includes/categories.php — PHP 5.2-safe. Single source of the six owner-priority
// categories (D-09/D-40). Consumed by the homepage card grid, the Услуги dropdown,
// the category page templates, and (Phase 4) sitemap.xml.
$torin_categories = array(
	array(
		'id'        => 'kat-1',
		'name'      => 'Счупвания и механични повреди',
		'symptoms'  => 'паднал лаптоп, счупен корпус, разхлабени панти', // [ASSUMED] OWNER-QUESTIONS #16
		'slug'      => 'mehanichni-problemi.html',                       // existing, SEO-04-locked
		'icon'      => 'cat-1',
		'published' => true,                                             // D-23 publish gate
	),
	// ... 5 more
);

function torin_category_href($cat) {
	if ($cat['published']) { return $cat['slug']; }
	return 'index.html#' . $cat['id'];
}
?>
```

**PHP 5.2 constraints that bite here:** `array()` only (no `[]`), no closures, no `??`, no array
short-dereference `f()[0]`. A plain named function is the accessor — this is the same style as
RESEARCH §2b's `torin_render_theme_switcher($current)`, the only other function specified this phase.

---

### `src/index.html` and all 16 page templates (page template, request-response) — MODIFY

**Analog:** `src/about.html` — 8 lines, and **13 of the 17 stubs are byte-identical to it**
(`about`, `mehanichni-problemi`, `msg`, `optimizatsiq`, … all carry the same `<h1>` and the same
placeholder paragraph). `src/index.html` is the same minus the `<h1>`.

**The complete per-page pattern — copy this wrapper for the three NEW category pages:**

```php
<?php require_once(dirname(__FILE__) . '/includes/header.php'); ?>

<main class="container py-5">
	<h1>ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ</h1>
	<p>Строим новия сайт ...</p>
</main>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
```

Three rules the pattern encodes:
1. **`require_once(dirname(__FILE__) . '/includes/...')`** — relative-to-file, not relative-to-cwd.
   Identical in all 17 stubs. Never `include`, never a bare relative path.
2. **The page owns only `<main>`.** No `<head>`, no `<body>`, no `<footer>` in a page file, ever.
3. **`.html` extension with PHP inside** — required, because `src/.htaccess:20`
   (`AddHandler application/x-httpd-php52 .html .htm`) is what makes these execute, and SEO-04
   locks the `.html` URLs. The three new category pages are `.html` too.

**What Phase 2 adds to the wrapper:** per-page metadata assignment *before* the include (the
mechanism RESEARCH N-5 says must land here), which is the one structural change to the pattern:

```php
<?php
$torin_title = 'Прегряване и охлаждане — ТОРИН КОМПЮТЪРС';
$torin_desc  = '...';
require_once(dirname(__FILE__) . '/includes/header.php');
?>
```

`container py-5` are Bootstrap-4 leftovers in the stub — replace with the design-system container
class; the `<main>` element itself stays.

---

### `src/includes/dev-switcher.php` (config + partial, dev-only) — NEW

**Analog:** `site-config.php` for file shape (comment header + `array()` + `?>`); **no analog for
the guard**, which is new to the project.

RESEARCH §2b supplies the full file verbatim (PHP 5.2-safe: `array()`, `in_array(..., true)`,
named function, no `<?=`). Two things the planner must carry into the plan text so an executor does
not "simplify" them:

- The `[data-theme]` attribute value is a **literal chosen by the code** after whitelist validation
  — never `$_GET['theme']` echoed. Changing it to `echo ' data-theme="' . $_GET['theme'] . '"'` is
  reflected XSS.
- The gate in `header.php` is `file_exists(dirname(__FILE__) . '/dev-switcher.php')`, **not** a
  `REQUEST_URI` check and **not** a `define()` in `site-config.php` (which ships to production).
  Verification is "is the file on the server" — answerable by an FTP listing.

---

### `src/css/base.css` (stylesheet, static asset) — NEW

**Analog:** `site-current/assets1/css/themes/business.css` — **partial match, harvest-only.** It is
the only hand-sized stylesheet in the repo (1,787 B; the rest of the legacy CSS is 604 KB of
minified template output with no readable pattern).

**Harvest exactly this (`business.css` lines 8-13) — it is D-01's source of truth:**

```css
:root {
  --color-primary: #ffc70a;
  --color-secondary: #0e305d;
  --color-gradient-start: #ffc70a;
  --color-gradient-stop: #ffcd2b;
}
```

Maps to `--c-brand` / `--c-ink-deep` per UI-SPEC §Dual-Theme Contract. `--color-gradient-stop`
`#ffcd2b` is **deliberately dropped** (UI-SPEC drops `--c-brand-hi`) — do not carry it forward.

**One more corroborating datum, `business.css` line 19:** `line-height: 1.7em` on `body`. The
previous designer independently landed on the same generous leading UI-SPEC specifies (1.65) —
cited in UI-SPEC §Typography as supporting evidence.

**Everything else in `business.css` is an anti-pattern to invert:**

| `business.css` | Phase 2 |
|---|---|
| `@font-face` for Basier Square (zero Cyrillic glyphs) | Sofia Sans, two woff2 subsets, `unicode-range` |
| two families (Basier Square body + Barlow headings) | one family (D-06a) |
| `font-size: 15px` body, `#808996` text (~3.9:1) | 17px body, `--c-ink` `#1f2a3c` (14.44:1) |
| six fixed `h1`–`h6` px sizes, `h1: 55px` | four `clamp()` sizes, no fifth |
| weights 400/500/600/700 | exactly two: 400 / 700 |

The `@font-face` block itself is the one structural shape worth copying — RESEARCH §1d gives the
Sofia Sans replacement verbatim, including the `unicode-range` values copied from the Google Fonts
`css2` response.

---

### `src/.htaccess` (server config, request-response) — MODIFY (spike only, RESEARCH N-6)

**Analog:** itself. Two conventions to preserve when appending `mod_deflate`/`mod_expires`:

**Convention 1 — every rule carries a why-comment naming the threat or the requirement** (lines 7-9):

```apache
# Canonicalize all 4 protocol/host variants to https://torin.bg/new/... in exactly 1 hop.
# Target is a hardcoded literal, never %{HTTP_HOST} reflection — reflecting the client-supplied
# Host header into a redirect Location is a host-header-injection/open-redirect vector (T-01-03).
```

**Convention 2 — every optional module is wrapped in `<IfModule>`** (lines 16-18). `mod_headers` is
proven working; `mod_deflate`/`mod_expires` are **unverified**, so they must follow this shape or a
missing module 500s the whole subtree:

```apache
<IfModule mod_headers.c>
	Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
```

**Do not touch line 20** — `AddHandler application/x-httpd-php52 .html .htm` is version-specific
(CloudLinux Alt-PHP) and is the single line making the whole `.html`-runs-PHP architecture work.

---

## Shared Patterns

### PHP 5.2 safety (applies to every `.php` and every `.html` page file)

**Source:** `src/includes/site-config.php:2` — the project states the rule in the file itself.

```php
// includes/site-config.php — PHP 5.2-safe (no short array syntax, no closures, no namespaces)
```

**Banned, verified against the live host's PHP 5.2.17:** `[]` short arrays · `__DIR__` (5.3+) ·
namespaces · closures / `function() use()` · `??` and `?:` shorthand · `::class` · `<?=` (unverified
`short_open_tag`) · `f()[0]` array dereference · `goto`. **Every existing file complies** —
`dirname(__FILE__)` in all 17 page stubs and both includes is the tell.

### Include boundary

**Source:** `src/about.html:1,8` + `src/includes/footer.php:15-18`
**Apply to:** all 17 page files, all three new category pages

`header.php` opens `<div id="wrap">`; `footer.php` closes it. A page file contains `<main>` and
nothing outside it. Any new partial (`icons.php`, `categories.php`) is a **function/data library
that emits nothing on include** — only `header.php`/`footer.php`/`dev-switcher.php` produce markup.

### Value provenance comments

**Source:** `src/includes/site-config.php:3-5`, `src/includes/header.php:3-4`, `src/.htaccess:1-3`
**Apply to:** `site-config.php`, `categories.php`, `.htaccess`

Every file in `src/` states where its values came from and, where relevant, which phase changes
them. New values whose source is an open owner question carry `[ASSUMED]` inline (working hours per
RESEARCH N-3; the Viber number per UI-SPEC C-9).

### Tabs, not spaces

**Source:** all five hand-authored files in `src/` (`header.php`, `footer.php`, `site-config.php`,
the page stubs, `.htaccess`). Consistent across the board. No `.editorconfig` exists to enforce it.

---

## No Analog Found

The planner should follow `02-UI-SPEC.md` (prescriptive) and `02-RESEARCH.md` (verbatim code) for
these — there is nothing in the repo to copy from.

| File | Role | Data Flow | Reason | Follow instead |
|---|---|---|---|---|
| `src/css/layout.css` | stylesheet | static | No hand-authored layout CSS exists; the legacy layer is 604 KB of minified Bootstrap-4-era template output | UI-SPEC §Layout & Breakpoints |
| `src/css/components.css` | stylesheet | static | Same | UI-SPEC §Component Contracts |
| `src/css/theme-a.css` | stylesheet, dev | static | No theming pattern exists; `business.css` has `:root` vars but no override scope | RESEARCH §2a (file given verbatim) |
| `src/js/site.js` | client script | event-driven | Zero JS in `src/`. The legacy JS (`theme-vendors.js` 530 KB, `otpuska.js`) is jQuery-era and is being deleted — an anti-pattern, not an analog | RESEARCH §3a (~35 lines given verbatim) |
| `src/includes/icons.php` | utility | transform | No function library exists in `src/`; the only precedent for a PHP function this phase is RESEARCH's own `torin_render_theme_switcher()`. Icons today are Font Awesome `<i>` classes with no font loaded | UI-SPEC §1 icon set; use a 5.2-safe `switch` in `torin_icon($name)` |
| `src/fonts/*.woff2` | binary asset | static | Binaries, not code. `site-current/fonts/` gives the placement convention only — and all three fonts there have zero Cyrillic glyphs | RESEARCH §1d (curl commands + `@font-face` verbatim) |

---

## Cross-Cutting Risks the Planner Should Sequence Around

1. **`site-config.php['phone']` shape change is a two-file edit.** Splitting the comma-joined string
   into an array breaks `header.php:40`'s scalar `echo`. Same plan, same commit.
2. **`header.php` is touched by at least six independent concerns** — theme-color, `<body>`, CSS
   links, font preload, icons, per-page title, dev-switcher gate. It is the phase's highest-contention
   file; one plan should own it outright rather than several plans editing it.
3. **Every one of the 17 page stubs is byte-identical.** Whatever wrapper change lands (the
   `$torin_title` assignment) applies uniformly — a single mechanical pass, not 17 decisions.
4. **`.htaccess` line 20 is load-bearing.** Any edit that drops or reorders
   `AddHandler application/x-httpd-php52 .html .htm` takes the entire staging subtree down to raw
   PHP source in the browser.

---

## Metadata

**Analog search scope:** `src/` (all 21 files), `site-current/` (`assets1/css/themes/business.css`,
`about.html` head block, `index.html`)
**Files scanned:** 27
**Pattern extraction date:** 2026-08-05
