<?php
// includes/dev-switcher.php — DEV ONLY (D-03). PHP 5.2-safe.
// Selects Theme A or B and renders the comparison control at torin.bg/new.
// Its own presence on the server IS the guard: header.php includes this file
// only when file_exists() says it is there, so "is the switcher live?" is
// answerable by an FTP directory listing rather than by reading code.
// Phase 4 cutover step 2: rm src/includes/dev-switcher.php.
//
// SECURITY (ASVS V5.1, T-02-01) — do NOT "simplify" any of the three points
// below. Both the query parameter and the cookie are client-supplied input:
//   1. Each is validated against the hardcoded whitelist BEFORE any use.
//   2. The value written into the attribute is a literal chosen by the code,
//      never the request value. Rewriting this as
//      echo ' data-theme="' . $_GET['theme'] . '"' is reflected XSS.
//   3. The cookie read is whitelisted exactly as strictly as the query string.
//      It is the branch that carries the selection across all sixteen pages,
//      so it is the one that actually runs in practice.

$torin_allowed_themes = array('a', 'b');
$torin_theme = 'b';                                    // D-02a: Theme B is default

if (isset($_COOKIE['torin_theme']) && in_array($_COOKIE['torin_theme'], $torin_allowed_themes, true)) {
	$torin_theme = $_COOKIE['torin_theme'];
}
if (isset($_GET['theme']) && in_array($_GET['theme'], $torin_allowed_themes, true)) {
	$torin_theme = $_GET['theme'];
	setcookie('torin_theme', $torin_theme, time() + 2592000, '/');
}

// Whitelist-selected literal, never a reflected request value → no XSS surface.
if ($torin_theme === 'a') {
	$torin_html_attr = ' data-theme="a"';
}
// Version-stamped by the same helper as every other stylesheet (G-02-1,
// plan 02-08). header.php requires includes/asset-version.php before it
// includes this file, so torin_asset_url() is already in scope. A reviewer
// comparing Theme A against Theme B must not be served a stale override
// either — that reviewer is precisely who G-02-1 bit.
//
// NOTE, deliberately not fixed here: this assignment sits OUTSIDE the
// `if ($torin_theme === 'a')` branch above, so the override is linked on
// every page load including the default Theme B. That is a real pre-existing
// wrong-condition bug, but it is out of scope for a cache-invalidation plan:
// the fix is a one-line move inside a file the Phase 4 cutover deletes
// outright, and its whole effect is one wasted request for a stylesheet whose
// single [data-theme="a"] block is inert on a Theme-B page.
$torin_extra_head = '<link rel="stylesheet" href="' . htmlspecialchars(torin_asset_url('css/theme-a.css'), ENT_QUOTES, 'UTF-8') . '">';

function torin_render_theme_switcher($current) {
	echo '<div class="dev-switcher" role="group" aria-label="Тема (само за разработка)">';
	echo '<a href="?theme=b"' . ($current === 'b' ? ' aria-current="true"' : '') . '>Тема B</a>';
	echo '<a href="?theme=a"' . ($current === 'a' ? ' aria-current="true"' : '') . '>Тема A</a>';
	echo '</div>';
}
?>
