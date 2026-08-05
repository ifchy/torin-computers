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
$torin_extra_head = '<link rel="stylesheet" href="css/theme-a.css">';

function torin_render_theme_switcher($current) {
	echo '<div class="dev-switcher" role="group" aria-label="Тема (само за разработка)">';
	echo '<a href="?theme=b"' . ($current === 'b' ? ' aria-current="true"' : '') . '>Тема B</a>';
	echo '<a href="?theme=a"' . ($current === 'a' ? ' aria-current="true"' : '') . '>Тема A</a>';
	echo '</div>';
}
?>
