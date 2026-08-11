# Phase 3: Content & Trust-Signal Build-Out - Pattern Map

**Mapped:** 2026-08-11
**Files analyzed:** 31 new/modified files (8 new pages, 15 modified pages, 5 modified includes, 2 new includes, 1 CSS, 1 .htaccess, 1 asset dir)
**Analogs found:** 30 / 31 — this phase creates almost nothing without a shipped Phase 2 precedent

> **Framing for the planner.** Phase 3 has an unusually high analog hit rate because Phase 2 shipped
> the machine and Phase 3 pours content through it. Every "new" file in this phase has a *live,
> working* sibling in `src/` written under the same PHP 5.2 constraints. There is no reason for any
> executor to invent structure. The only genuinely un-analogued surface is the structured `blocks`
> slot renderer (§Pattern Assignments, `category-page.php`), and even that has a near-analog in the
> same file's existing `switch`-free guarded sections and in `icons.php`'s `switch` dispatch.

---

## File Classification

### New files

| New file | Role | Data Flow | Closest Analog | Match Quality |
|---|---|---|---|---|
| `src/includes/services.php` | model (data) | static read | `src/includes/categories.php` | **exact** |
| `src/includes/brand-row.php` *(or inline partial)* | component | static read | `src/index.html:51-68` (card-grid section) | exact |
| `src/includes/breadcrumbs.php` *(or fn in `category-page.php`)* | component | static read | `src/includes/category-page.php:85-91` + `index.html:105-109` | role-match |
| `src/ekran-klaviatura-portove.html` (cat-2 hub) | route/page | request-response | `src/zalivane-technosti.html` | **exact** |
| `src/pregryavane-ohlazhdane.html` (cat-5) | route/page | request-response | `src/zalivane-technosti.html` | **exact** |
| `src/smyana-na-matrica.html` (child) | route/page | request-response | `src/zalivane-technosti.html` | exact |
| `src/smyana-na-klaviatura.html` (child) | route/page | request-response | `src/zalivane-technosti.html` | exact |
| `src/smyana-na-panti.html` (child) | route/page | request-response | `src/zalivane-technosti.html` | exact |
| `src/remont-na-portove.html` (child) | route/page | request-response | `src/zalivane-technosti.html` | exact |
| `src/smyana-na-buksa.html` (child) | route/page | request-response | `src/zalivane-technosti.html` | exact |
| `src/img/repairs/*.jpg` (~40 ported) | asset | file-I/O | `src/img/torin-logo.png` + `.cat-card__media > img` rule | partial |

> Child filenames above are **illustrative** — CONTEXT lists them as Claude's discretion under the
> D-42 transliterated-Latin `.html` convention. The *pattern* is what is locked, not the slug.

### Modified files

| Modified file | Role | Data Flow | Closest Analog (for the ADDED code) | Match Quality |
|---|---|---|---|---|
| `src/includes/categories.php` | model (data) | static read | itself (kat-6 `page` value edit, D3-05) | exact |
| `src/includes/site-config.php` | config | static read | itself — `phones` / `viber` / `notice` keys | **exact** |
| `src/includes/category-page.php` | template/renderer | static read | itself — the eight existing guarded slots | **exact** |
| `src/includes/jsonld.php` | service (structured data) | transform | itself — `$torin_ld` + `json_encode()` | exact |
| `src/includes/icons.php` | utility | static read | itself — 15 existing `case` arms | exact |
| `src/css/components.css` | style | — | `.svc__*` group (`components.css:710-770`) | exact |
| `src/.htaccess` | config | — | its own `ExpiresByType image/png` line | exact |
| `src/index.html` | route/page | request-response | itself — the 4 existing `<section class="section">` blocks | exact |
| 3 thin category pages (`mehanichni-problemi`, `optimizatsiq`, `zalivane-technosti`) | route/page | request-response | `src/zalivane-technosti.html` (deepen in place) | exact |
| 12 stub pages (`about`, `test-laptop`, `za-bateriite`, `profilaktika-laptop`, `tokov-udar`, `problem-stari`, `uslovia`, `warrently`, `msg`, `laptopi`, `rezervni-chasti`, `covid`) | route/page | request-response | `src/test-laptop.html` (stub shape) for the frame; body copy from `site-current/` | exact (frame) / n-a (copy) |

---

## Pattern Assignments

### `src/includes/services.php` (model, static read) — the five D3-03 child records

**Analog:** `src/includes/categories.php` — copy its *entire* shape: header comment naming consumers,
a `$torin_*` array of `array()` records, single-quoted keys inside records, a `published` boolean,
and one accessor function that is the publish gate.

**File-header comment pattern** (`categories.php:1-25`) — reproduce this convention, listing the new
file's own consumers and its own decision refs (D3-03, D-23, D-42):

```php
<?php
// includes/categories.php — PHP 5.2-safe (no short array syntax, no closures,
// no namespaces). Emits nothing on include; it is data plus one accessor.
//
// Single source of truth for the six owner-priority categories (D-09/D-40).
// FOUR consumers read it, which is why no href is ever hand-typed anywhere:
```

**Record pattern** (`categories.php:27-37`) — note the `[ASSUMED]` comment sits *immediately above*
the value it qualifies, and names the OWNER-QUESTIONS number:

```php
	array(
		'id'        => 'kat-1',
		'name'      => 'Счупвания и механични повреди',
		// [ASSUMED] Placeholder standing in for the real customer phrasing the
		// owner hears daily (OWNER-QUESTIONS #16). Phase 3 replaces it. Not
		// confirmed shop language — do not quote it back as such.
		'symptoms'  => 'паднал лаптоп, счупен корпус, разхлабени панти',
		'page'      => 'mehanichni-problemi.html',
		'icon'      => 'cat-1',
		'published' => true,
	),
```

**Publish-gate accessor pattern** (`categories.php:93-106`) — copy verbatim in shape, including the
double-quoted-keys trick and the comment explaining *why* they are double-quoted (a plan-level grep
counts single-quoted key literals to assert record counts). A `torin_service_href()` must exist so
no child-page href is ever hand-typed:

```php
function torin_category_href($cat) {
	if ($cat["published"]) {
		return $cat["page"];
	}
	return 'index.html#' . $cat["id"];
}
```

**Do not** add the five child records to `categories.php` — plan 02-02's integrity greps assert
exactly six records there.

---

### `src/includes/site-config.php` (config, static read) — MOD: `brands`, `rating`, `review_count`, `gbp_url`, `warranty`

**Analog:** the file itself. Every existing key already models what the new keys need.

**List-valued key + "no consumer may re-join it" rule** (`site-config.php:14-20`):

```php
	// Three separate numbers, never one joined string: each renders its own
	// tel: link, so the footer works identically for one number or five. No
	// consumer may join this list back into a display string. Sourced from the
	// secondarybar block of site-current/index.html. The scalar key this
	// replaced was REMOVED rather than kept alongside — two representations of
	// one fact silently disagree the day a number changes, and single-sourcing
	// is the entire reason this file exists.
	'phones' => array('02 9549710', '088 9458404', '087 9128244'),
```

→ `'brands' => array('Lenovo', 'HP', 'Dell', ...)` copies this exactly. The «и др.» closer is
rendered by the template and **must not** be stored as an array entry (same discipline as "no
consumer may join this list back into a display string").

**`[ASSUMED]` + empty-string-disables pattern** (`site-config.php:116-121`) — this is the exact
precedent for the TRUST-02 badge's absent state and for `brands`:

```php
	// [ASSUMED] OWNER-QUESTIONS #8 asks whether the legacy otpuska.js
	// holiday/hours banner should survive at all. It carried genuine content
	// rather than decoration, so the safe default preserves an equivalent as
	// static PHP-rendered content instead of dropping it. Set this to an empty
	// string and the band disappears with no other edit.
	'notice' => 'Работно време: понеделник – петък, 8:00 – 16:00 ч.',
```

**Nested-array key** — no analog in this file today; the closest is `jsonld.php`'s nested
`'address' => array('@type' => ...)`. Use that shape for the D3-10 keyed warranty set:

```php
	'address'   => array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => 'ул. Свети Иван Рилски 46',
		'addressLocality' => 'София',
		...
	),
```
[`jsonld.php:49-56`]

**Provenance-comment rule** (`site-config.php:2-11` header): *"Every entry carries a provenance
comment naming where the value came from."* Every new key must carry one — the warranty entries cite
`site-current/warrently.html:113-129` and `site-current/za-bateriite.html:129`.

---

### `src/includes/category-page.php` (template/renderer, static read) — MOD: h1 override, `blocks` slot, per-service warranty, breadcrumbs, tint toggle

**Analog:** itself. Every change is an additional instance of a pattern already in the file.

**1. The recognised-keys header block** (`category-page.php:32-40`) — this doc-comment IS the
template's contract and must be extended in the same edit as any new key:

```php
// Recognised $page keys:
//   cat_id    string  required — the record id in $torin_categories
//   intro     string  one paragraph (Phase 3)
//   fixes     list    entries carrying a 'text' key and an optional 'href' key
//   warranty  string  TRUST-03 summary (Phase 3)
//   process   list    ordered steps, each entry a string
//   faq       list    entries carrying a 'q' key and an 'a' key
//   related   list    entries carrying a 'text' key and an optional 'href' key
//   prices    list    entries, each a string
```

**2. The guard-declaration block** (`category-page.php:101-110`) — every new slot (`blocks`, `h1`)
adds one line here, in the same shape. Guards are decided once, at the top, never inline:

```php
	// Every optional block is decided here, once, and an unmet guard emits
	// NOTHING AT ALL — not an empty heading, not an empty section wrapper.
	$torin_has_intro    = isset($page['intro'])    && torin_has_content($page['intro']);
	$torin_has_fixes    = isset($page['fixes'])    && torin_has_content($page['fixes']);
	$torin_has_warranty = isset($page['warranty']) && torin_has_content($page['warranty']);
```

**3. The section-emission pattern** (`category-page.php:143-151`) — copy this exact shape for the
new `svc__blocks` section. Note: guard *outside* the markup, `<span class="eyebrow" aria-hidden>`
then `<h2>`, `.section` + optional `.section--tint`, `.container` inside:

```php
	if ($torin_has_warranty) { ?>
	<section class="section svc__warranty">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Гаранция</h2>
			<p><?php echo torin_esc($page['warranty']); ?></p>
		</div>
	</section>
<?php	}
```

**4. The h1 override (§UI-SPEC 6)** — the line to change, and the reason it is the way it is:

```php
			<h1><?php echo torin_esc($cat['name']); ?></h1>
```
[`category-page.php:114`; rationale at `category-page.php:17-19` — "read from `$torin_categories`
via the record id, never retyped on a page — so a D-40 rename cannot leave a stale name behind"]

The override must preserve that default. The `torin_has_content()` guard at `:65-70` is the exact
mechanism for "present-but-empty falls back":

```php
function torin_has_content($value) {
	if (is_array($value)) {
		return count($value) > 0;
	}
	return trim($value) !== '';
}
```

**5. The `blocks` variant dispatch** — the *only* structurally new code in this phase. There is no
`switch` in `category-page.php`, but `icons.php:9-45` is the house `switch` pattern, including the
"unknown name returns empty rather than emitting broken markup" rule that the `tone` whitelist and
the `kind` dispatch must both follow:

```php
function torin_icon($name) {
	switch ($name) {
	case 'cat-1':   // laptop with a fracture line across the lid corner
		return '<svg ...></svg>';
	...
	}
	return '';
}
```
[`icons.php:9-45` — also the analog for adding the 16th `star` icon; note the per-case trailing
comment naming the glyph's subject, and the `aria-hidden="true" focusable="false"` on every icon.]

**6. Escaping — the non-negotiable** (`category-page.php:72-78`). Every leaf of every new structured
block goes through this. There is currently **no unescaped output path anywhere in `src/`**; a
`'html'` passthrough slot would be the first:

```php
// PHP 5.2 defaults htmlspecialchars() to ISO-8859-1 and every string on these
// pages is Cyrillic, so the charset argument is always passed.
function torin_esc($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

**7. Link-or-text rendering** (`category-page.php:85-91`) — the analog for `blocks[].link` and for
every breadcrumb item that may or may not be a link:

```php
function torin_render_svc_item($item) {
	if (isset($item['href']) && trim($item['href']) !== '') {
		echo '<a href="' . torin_esc($item['href']) . '">' . torin_esc($item['text']) . '</a>';
		return;
	}
	echo torin_esc($item['text']);
}
```

**8. The tint toggle** — hardcoded `section--tint` literals currently live at `:125` (`svc__fixes`),
`:154` (`svc__process`), `:188` (`svc__related`). All three must be replaced by one running boolean
flipped per *emitted* section. PHP 5.2-safe: a plain local `$torin_tint = false;` with
`$torin_tint = !$torin_tint;` — no closures, no ternary chains.

---

### `src/includes/jsonld.php` (service/transform) — MOD: BreadcrumbList (+ `sameAs`)

**Analog:** the file itself. The added emitter copies the array-then-`json_encode()` shape verbatim.

**The encode pattern and the two PHP-5.2 facts that justify it** (`jsonld.php:14-27`) — reproduce
this reasoning verbatim for any new block; do not hand-write JSON:

```php
// THE JSON IS ENCODED, NEVER HAND-WRITTEN. Two facts about this host's PHP
// 5.2.17 build govern that choice:
//
//   * The 5.4-era flag that would leave Unicode unescaped does not exist here,
//     so Cyrillic is emitted as \uXXXX escapes. That is VALID JSON and Google
//     parses it correctly. Do NOT "fix" the escapes by writing the JSON by hand.
//   * The 5.4-era flag that would leave forward slashes unescaped does not
//     exist here either, and that absence is a SAFETY BENEFIT: this build always
//     escapes "/", so a literal closing script tag inside any string can never
//     terminate the block below early (T-02-11).
```

> ⚠ A plan-level grep asserts no 5.4+ JSON constant name appears in this file. The comment above
> deliberately does not spell the constants. **Any new plan must preserve that discretion.**

**Structure + emit** (`jsonld.php:33-36`, `74-76`):

```php
$torin_ld = array(
	'@context'  => 'https://schema.org',
	'@type'     => array('LocalBusiness', 'ComputerStore'),
	'name'      => 'ТОРИН КОМПЮТЪРС',   // decoded from the legacy Maps embed
...
?>
<script type="application/ld+json">
<?php echo json_encode($torin_ld); ?>
</script>
```

**Single-sourcing rule for schema values** (`jsonld.php:38-46`) — the `sameAs` → GBP URL addition
must read `$site['gbp_url']`, never a literal, for this stated reason:

```php
	// Read from the single-sourced E.164 key. It used to be an independent
	// literal here, which meant the number a search engine publishes and the
	// number the page's own call buttons dial could drift apart silently
	'telephone' => $site['phone_e164'],
```

**Include point** (`footer.php:114-119`) — the new emitter must be reachable from the same one place,
or take a parallel per-page include; do not add a second `<script type="application/ld+json">` writer
to sixteen pages:

```php
	// One LocalBusiness block per page, from one include, single-sourced off
	// $site (D-34). Included here rather than in header.php purely so that all
	// 16 pages get it from the file they already share.
	include(dirname(__FILE__) . '/jsonld.php');
```

---

### The 7 new pages + 3 category-page deepenings (route/page, request-response)

**Analog:** `src/zalivane-technosti.html` — the deepest of the three published category pages, and
the one whose header comment explicitly states it exists to prove the template renders both a shallow
and a deep shape.

**Full page skeleton** (`zalivane-technosti.html:1-39`) — copy structurally, whole:

```php
<?php
// Category 4 rendered through the D-24 template (includes/category-page.php).
// The display name and the symptom line are read from the category record,
// never retyped here.
require_once(dirname(__FILE__) . '/includes/category-page.php');

$torin_cat = torin_category_by_id('kat-4');

// Per-page metadata (02-RESEARCH N-5). The title is built from the shared
// record for the same reason the h1 is; SEO-01 in Phase 3 owns the final copy.
$torin_title = $torin_cat['name'] . ' · ТОРИН КОМПЮТЪРС';
$torin_desc  = 'Ремонт след заливане с течност и сервиз на дънни платки — BGA чипове, чипсет и захранващи вериги.';

$torin_cat_key  = $torin_cat['id'];
$torin_cat_page = array(
	'cat_id' => $torin_cat_key,
	'fixes'  => array(
		array('text' => 'Дозапояване на отпоени BGA чипове'),
		array('text' => 'Ребоулинг на BGA чипове'),
		...
	),
);

require_once(dirname(__FILE__) . '/includes/header.php');
?>

<main>
<?php torin_render_category_page($torin_cat_page); ?>
</main>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
```

**Load-bearing details in that skeleton:**
- `$torin_title` / `$torin_desc` are assigned **before** the `header.php` include. Non-negotiable
  ordering — `header.php:49-54` reads them at include time and falls back if unset.
- Line 18's `$torin_title = $torin_cat['name'] . ' · ТОРИН КОМПЮТЪРС';` is the derived-title form
  that RESEARCH says must become a **literal** on the three existing category pages, to decouple
  `<title>` from `<h1>` for keyword-first titles.
- A page file owns `<main>` and **nothing outside it**.
- `dirname(__FILE__)` everywhere; never `__DIR__`.

**Stub-page frame** (`test-laptop.html:1-21`) — the shape being *replaced* on the 12 stub pages.
Non-category pages keep this frame (`<main class="section"><div class="container">…`) rather than
calling `torin_render_category_page()`:

```php
<?php
// Per-page metadata (02-RESEARCH N-5). WORKING title and description carrying
// the mechanism, derived from this page's existing subject; SEO-01 in Phase 3
// owns the search-tuned copy, and CONTENT-01 owns the body below.
$torin_title = 'Тествай сам своя лаптоп · ТОРИН КОМПЮТЪРС';
$torin_desc  = 'Кратък тест, който помага да опишете проблема с лаптопа по-точно, преди да се обадите.';
require_once(dirname(__FILE__) . '/includes/header.php');
?>

<main class="section">
	<div class="container">
		<h1>Тествай сам своя лаптоп</h1>
		<p>Строим новия сайт на ТОРИН КОМПЮТЪРС тук. Тази страница е временен скелет…</p>
	</div>
</main>

<?php require_once(dirname(__FILE__) . '/includes/footer.php'); ?>
```

`«Тази страница е временен скелет»` is the exact grep string that identifies a remaining stub — a
free progress metric for the planner and a verification gate for the executor.

---

### `src/index.html` (route/page) — MOD: brand row, rating badge, DIFF-02/DIFF-03 sections

**Analog:** `src/index.html` itself — four existing `<section class="section">` blocks.

**Section shape to copy** (`index.html:154-161`) — the smallest complete example; the new DIFF-02/03
and brand-row sections are this plus an evidence strip:

```php
	<?php // The self-diagnostic tool gets its own homepage block rather than
	      // being reachable only from the navigation (D-12, DIFF-01). ?>
	<section class="section section--tint selftest">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2>Тествай сам своя лаптоп</h2>
			<p>Кратък тест, който помага да опишете проблема по-точно, преди да се обадите.</p>
			<a class="btn btn--secondary selftest__cta" href="test-laptop.html">Тествай сам лаптопа си</a>
		</div>
	</section>
```

**Data-driven list rendering on a page** (`index.html:56-66`) — the analog for the brand-row `<ul>`.
Note it escapes even hardcoded values, and the comment states why:

```php
			<div class="cat-grid">
<?php foreach ($torin_categories as $cat) { ?>
				<article class="cat-card" id="<?php echo htmlspecialchars($cat['id'], ENT_QUOTES, 'UTF-8'); ?>">
					<div class="cat-card__media"><?php echo torin_icon($cat['icon']); ?></div>
					...
				</article>
<?php } ?>
			</div>
```

> **Escaping-style note for the planner.** `index.html` calls `htmlspecialchars($v, ENT_QUOTES,
> 'UTF-8')` inline, because it does not include `category-page.php`. `category-page.php` uses
> `torin_esc()`. Both are correct in their own file. New homepage code should follow `index.html`'s
> inline form *unless* the section is factored into an include that already has `torin_esc()` in
> scope. Do not introduce a third escaping style.

**Optional-node discipline** (`index.html:183-188`) — the exact precedent the UI-SPEC cites for the
rating badge's absent state ("a subtraction, not a redesign"):

```php
				<?php // OPTIONAL, pending OWNER-QUESTIONS #2 (should the form exist at
				      // all?). Deliberately a SIBLING of the actions row, never a flex
				      // child of it, and no stylesheet rule references it from a
				      // parent's layout — so deleting this node is a subtraction, not a
				      // redesign (D-17). ?>
				<div class="cta-block__form"></div>
```

**CTA block** (`index.html:171-189` and `category-page.php:226-235`) — the rating badge lands as the
**last child** of `.cta-block`. Both copies of this block must receive it, or the two surfaces drift.

---

### `src/css/components.css` (style) — MOD: `.brands`, `.brand-row`, `.rating-badge`, `.evidence`, `.svc__blocks`, `.breadcrumbs`

**Analog:** the `.svc__*` group, `components.css:710-770`. Its own header comment states the reuse
rule this phase's ≤4 KB budget depends on:

```css
   Everything below reuses the eyebrow, heading, list, disclosure and CTA
   components already defined in this file: no new token, no fifth type size. */
```

**Amber-is-a-surface rule** (`components.css:740-753`) — the star glyph is the 7th accent element and
must obey the same reasoning:

```css
/* The bullet is a filled amber block, not a list marker and not a border:
   amber is a SURFACE token and may never be a text or border colour.        */
.svc__fixes li::before,
... {
	content: "";
	position: absolute;
	width: 8px; height: 8px;
	border-radius: var(--r-sm);
	background: var(--c-brand);
}
```

**Reusable-`ol` rule for `blocks.steps`** (`components.css:761-766`) — the UI-SPEC says `steps`
reuses this verbatim; do not write a second one:

```css
.svc__process ol {
	display: grid;
	gap: var(--sp-sm);
	margin-block-start: var(--sp-md);
	padding-inline-start: var(--sp-lg);   /* base.css zeroes list padding */
}
```

**Fixed-box image slot for the evidence strip** (`components.css:178-200`) — the exact mechanism
(`overflow: hidden` + `aspect-ratio` + `object-fit: cover` + `--c-surface-3` backing) the evidence
photo box copies, including the "ship width/height on any such `<img>`" instruction at `:176-177`:

```css
.cat-card__media {
	flex: none;
	display: grid;
	place-items: center;
	overflow: hidden;
	width: 5rem;
	aspect-ratio: 4/3;
	border-radius: var(--r-sm);
	background: var(--c-surface-3);
}

.cat-card__media > img {
	display: block;
	width: 100%;
	height: 100%;
	object-fit: cover;
}
```

**Explicit-columns rule** (`layout.css:37-49`) — the reason the evidence grid must declare
`repeat(2, 100px)` / `repeat(3, 100px)` rather than `auto-fit`:

```css
/* --- Six-category grid (IA-01, D-09) ------------------------------------
   The set is fixed at exactly six, so the column counts are declared
   explicitly at each breakpoint rather than left to content-driven track
   placement: with a known six, a minmax()-based track function produces 4+2
   and 5+1 orphan rows at intermediate widths… */
```

**Section rhythm + eyebrow tokens** (`layout.css:17-35`) — every new section composes from these two
rules and adds no layout of its own.

---

### `src/img/repairs/` (asset, file-I/O) — the ~40 ported JPEGs

**Analog:** partial only. `src/img/` holds one file (`torin-logo.png`); there is no existing image
*port* to copy. The binding patterns are therefore:

- **Markup:** `.cat-card__media > img` (`components.css:195-200`) — the slot shape.
- **Server:** `src/.htaccess`'s existing `ExpiresByType image/png` line is the analog for the
  required `ExpiresByType image/jpeg` addition, in the same edit that lands the files.
- **Existence guard:** no analog — `file_exists()` per figure (UI-SPEC §4 `error`) is new. Closest
  precedent is `header.php:42-43`'s dev-switcher guard, which is the house shape for "include only
  if present":

```php
$torin_dev_switcher = dirname(__FILE__) . '/dev-switcher.php';
if (file_exists($torin_dev_switcher)) { include($torin_dev_switcher); }
```

- **Filenames:** ASCII only. `site-current/covid-19/` holds Cyrillic + `+` filenames that must be
  renamed on port (RESEARCH).

---

## Shared Patterns

Applied to **every** file this phase touches. These are the project's operating rules, all verified
in source this session.

### PHP 5.2.17 dialect
**Source:** `category-page.php:1-5`, `categories.php:1-3`, `jsonld.php:2-3`, `site-config.php:1-2`
**Apply to:** every `.php` and `.html` file (`.html` runs PHP via `AddHandler`)

```php
// includes/category-page.php — PHP 5.2-safe (array() only, named functions
// only, no namespaces, no short echo tags, tabs). Emits nothing on include:
// it is function definitions and no top-level output, exactly like
// categories.php and site-config.php.
```

Forbidden throughout: `[]` array literals, closures, namespaces, `__DIR__`, `??`, `?:`, `<?=`,
`f()[0]`, and any PHP 5.4+ `json_encode` flag constant. **Tabs, not spaces.**

### Escaping — no raw-output path exists, and none may be created
**Source:** `category-page.php:72-78`
**Apply to:** every string that reaches HTML, including every leaf of every new structured block

```php
function torin_esc($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
```

The explicit `'UTF-8'` is mandatory — 5.2 defaults to ISO-8859-1 and every string here is Cyrillic.
Attribute interpolation is escaped even when the value is a hardcoded literal (`index.html:46-49`
gives the reason: the same loop becomes Phase 4's sitemap generator).

### Single-sourcing — one writer, many readers
**Source:** `site-config.php:22-41`, `jsonld.php:38-46`, `category-page.php:215-219`
**Apply to:** brands, rating, review count, GBP URL, warranty summary, every category and child name

```php
	// The E.164 key, not the first display entry of the phone list:
	// one telephone fact, one representation, every primary call CTA
	// on the site resolving the same string (see site-config.php).
```

Corollary rules, both already enforced in source: **no href is ever hand-typed** (route everything
through `torin_category_href()` / a new `torin_service_href()`), and **an explanation kept in two
places is the same defect as a value kept in two places** (`jsonld.php:41-45`) — so a rationale lives
beside its value, and other sites reference it rather than restating it.

### The empty guard — absent and present-but-empty behave identically
**Source:** `category-page.php:61-70`, `:101-102`
**Apply to:** every new slot, the brand row, the rating badge, the evidence strip

```php
// True only when a key carries something worth rendering. Both halves matter:
// a present-but-empty value must omit its block exactly like an absent key, or
// Phase 3 assigning an empty string would quietly reintroduce the empty
// heading these guards exist to prevent.
```

And the CSS side of the same rule (`components.css:710-714`): *"There is deliberately NO rule in this
group that hides an absent block. The renderer omits it from the markup entirely… a display rule
would leave the empty heading in the served HTML, where the crawler still reads it."*

### `[ASSUMED]` provenance marker
**Source:** `site-config.php:5-11`, `categories.php:30-33`, `jsonld.php:62-63`
**Apply to:** brand list (#22), rating + count (#7), warranty variance (#23), cat-6 scope (#3),
symptom lines (#16)

```php
// Entries marked [ASSUMED] are NOT confirmed by the shop owner. Each names the
// open question that closes it. They render on all 16 pages, and the hours also
// feed the structured data Google acts on — a wrong value there sends real
// customers to a closed shop, sixteen pages and one search engine at once. Do
// not quote them back as confirmed fact, and do not drop a marker until its
// OWNER-QUESTIONS item is answered.
```

The marker goes **in-source**, immediately above its value, and names its OWNER-QUESTIONS number.
When one value appears in two files (as `hours` does in `site-config.php` and `jsonld.php`), the
comment says so explicitly: *"It is also hard-coded into jsonld.php's opening hours — change BOTH."*

### Comment density — the load-bearing convention
**Source:** every file read this session. `site-config.php` is 123 lines for 11 keys; the `viber`
entry is ~60 lines of comment for one string, including a boxed cutover gate.

New code in this phase is expected to carry the *decision*, the *rejected alternative*, and the
*failure mode it prevents* — not a restatement of what the line does. This is why the executor should
not "clean up" comments while porting.

### Per-page metadata ordering
**Source:** `header.php:46-54`; instantiated on all 16 pages
**Apply to:** all 7 new pages and every SEO-01 tuning edit

Assign `$torin_title` / `$torin_desc` **before** `require_once('includes/header.php')`. Anything
unset falls back to the site default, so a page can never render an empty title.

### Section markup contract
**Source:** `category-page.php:143-151`, `index.html:51-68`, `layout.css:17-35`
**Apply to:** all seven new UI surfaces

```html
<section class="section [section--tint] [modifier]">
	<div class="container">
		<span class="eyebrow" aria-hidden="true"></span>
		<h2>…</h2>
		…
	</div>
</section>
```

Exactly one `<h1>` per page; every new block emits `<h2>`; no `<h3>` is introduced by this phase.

---

## No Analog Found

| File / surface | Role | Data Flow | Reason |
|---|---|---|---|
| `category-page.php` — the `blocks` `kind`/`tone` dispatch | template | transform | No structured-variant renderer exists in `src/`. Nearest patterns: `icons.php:9-45` (`switch` + empty-string default for unknown names) and `torin_render_svc_item()` (`category-page.php:85-91`, branch-on-optional-key). Compose from those two rather than inventing a third shape. |
| Evidence-strip `file_exists()` figure guard | template | file-I/O | No image-existence guard exists. Nearest: `header.php:42-43` dev-switcher include guard. |
| `rel=canonical` emission (if taken this phase) | config/template | — | No canonical tag exists anywhere in `src/`. RESEARCH recommends deriving it from a single `site-config.php` base-URL key or deferring to Phase 4 — do **not** hardcode `/new/` into sixteen pages. |

---

## Metadata

**Analog search scope:** `src/`, `src/includes/`, `src/css/`,
`.planning/phases/02-design-system-information-architecture/`
**Files read this session:** `category-page.php`, `categories.php`, `site-config.php`, `jsonld.php`,
`icons.php`, `header.php` (metadata block), `footer.php` (jsonld include), `index.html`,
`zalivane-technosti.html`, `test-laptop.html`, `problem-stari.html`, `components.css` (3 ranges),
`layout.css` (1 range)
**Pattern extraction date:** 2026-08-11
