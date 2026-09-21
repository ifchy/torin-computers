<?php
// includes/header.php — PHP 5.2-safe shared document head + opening chrome.
// Rewritten in Phase 2 (plan 02-01) as the design-system shell. The legacy
// secondarybar contact block extracted from site-current/index.html lines 1-80
// is DELETED here: its Font Awesome markup rendered nothing (no icon font is
// loaded) and D-33 moves all contact chrome to the footer. Emitting no phone
// value from this file is deliberate — it is what lets plan 02-03 promote the
// site-config phone key from a scalar to a list without a coupled edit here.
// dirname(__FILE__) is the 5.2-safe idiom; the 5.3+ magic directory constant
// must never be introduced anywhere in this tree.
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
// The Услуги dropdown renders the same six records the homepage card grid
// renders, through the same torin_category_href() publish gate — so the nav
// and the cards can never disagree about a destination and publishing a page
// stays a single boolean flip with zero nav edit (D-23). Required here rather
// than relying on the page: fifteen of the sixteen pages include only this
// file. require_once makes index.html's own earlier require a no-op.
require_once(dirname(__FILE__) . '/categories.php');
// Version-stamps every static asset URL this head emits (gap G-02-1: a bare
// href cannot be invalidated, and the origin caches these files for days).
// Required HERE, before the dev-only theme switcher partial is included below,
// because that partial calls torin_asset_url() too and relies on this scope.
require_once(dirname(__FILE__) . '/asset-version.php');
// The holiday/closure strip (OWNER-02, D4-27). Required here and CALLED ONCE
// below, as the first child of the page wrapper — the shared-layout rule means
// every page gets it or no page does, and a second call site anywhere would
// render two strips on whichever page carried it. It emits nothing while no
// closure is scheduled, which is the shipped state.
require_once(dirname(__FILE__) . '/banner.php');

// Current-page detection for aria-current. SCRIPT_NAME is the ONLY acceptable
// source here: the other self-referencing server variable appends
// client-supplied PATH_INFO to the script path and is a classic reflected-XSS
// vector (T-02-10). A plan-level grep asserts that variable's name appears
// nowhere in this file, which is why it is not written out here. This value is
// used ONLY for comparison and is never echoed — do not "improve" it into
// output.
// RENAMED from $torin_page after a live defect, 2026-08-26. This file is
// required by every page, so any name it assigns is squatted in the GLOBAL
// scope of the page that included it. Four Phase 3 pages had already named
// their own $page data array $torin_page; this line silently overwrote each
// one with the filename string, and PHP 5.2 answers $string['intro'] by
// casting the key to 0 and returning character zero — so every lookup returned
// a single letter, every foreach over it warned, and the pages still returned
// HTTP 200 while rendering «s» where their prose belonged. Keep this name
// narrow and nav-specific so a page-level array can never collide with it.
$torin_nav_current = basename($_SERVER['SCRIPT_NAME']);

// ── DEV-ONLY THEME SWITCHER (D-03) — delete this block at the Phase 4 cutover.
// The guard is file existence and nothing else: never a request-path check
// (request values are client-influenced) and never a constant in
// site-config.php (that file does ship to production). "Is the switcher live?"
// must be answerable from an FTP directory listing.
$torin_html_attr = '';
$torin_extra_head = '';
$torin_dev_switcher = dirname(__FILE__) . '/dev-switcher.php';
if (file_exists($torin_dev_switcher)) { include($torin_dev_switcher); }
// ── END DEV-ONLY ─────────────────────────────────────────────────────────────

// Per-page metadata mechanism (02-RESEARCH N-5). A page assigns these before
// the include; anything left unset falls back to the site-level default, so a
// stub page that assigns nothing still renders a non-empty title.
if (!isset($torin_title)) {
	$torin_title = 'ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ';
}
// The fallback description below lost its trailing free-diagnostics sentence in
// plan 03.5-01. REMOVED, not reworded: D3.5-06 scopes that claim to categories
// 1-5, and the sentence sat in the same breath as «нестандартна техника» —
// category 6 — so the reworded form would have contradicted itself inside one
// description. Nothing replaces it. Every page on the site sets its own
// $torin_desc, so this fallback renders nowhere today and has no search value
// to preserve; it exists as the not-empty guarantee for a future stub page.
//
// KEEP THIS COMMENT OUTSIDE THE IF-BLOCK. scripts/seo-metadata-check.js reads
// both fallbacks straight out of this mechanism rather than copying them, and
// its matcher allows only whitespace between the brace and the assignment.
// A comment placed inside the block halts that gate with «the mechanism
// changed shape» — which is the gate doing its job, and which is how this
// paragraph came to be here.
if (!isset($torin_desc)) {
	$torin_desc = 'ТОРИН КОМПЮТЪРС — ремонт на лаптопи в София: счупвания, екран и клавиатура, оптимизация, заливане и дънни платки, прегряване, нестандартна техника.';
}

// SERVER-RENDERED ANALYTICS EVENTS (UI-SPEC C-9). Two of the nine events in
// the contract cannot be observed from the client — «this enquiry completed»
// and «the server rejected this field» are facts only the server holds — so
// the page DECLARES them here and js/analytics.js fires them. A page assigns
// $torin_track before this include as a space-separated token string:
// «form-sent», or «form-error:device form-error:email».
//
// THE TOKEN IS NOT A PROPERTY BAG AND MUST NEVER BECOME ONE. Only the fixed
// event names and the seven fixed field names may appear in it; analytics.js
// drops anything outside those lists, so a submitted value written here would
// be discarded rather than sent — but it must not be written here in the first
// place. Nothing derived from what a visitor typed goes into this string.
//
// Unset on nineteen of twenty pages, where it emits no attribute at all and
// the <body> tag is byte-identical to what it has always been.
$torin_track_attr = '';
if (isset($torin_track) && $torin_track !== '') {
	$torin_track_attr = ' data-track="' . htmlspecialchars($torin_track, ENT_QUOTES, 'UTF-8') . '"';
}
?>
<!DOCTYPE html>
<html lang="bg"<?php echo $torin_html_attr; ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php // D-01: the brand colour is #ffc70a. The #3ed2a7 this replaced was an
      // unchanged leftover from the purchased "Liquid" template, used nowhere
      // in actual styling. ?>
<meta name="theme-color" content="#ffc70a">
<meta name="description" content="<?php echo htmlspecialchars($torin_desc, ENT_QUOTES, 'UTF-8'); ?>">

<title><?php echo htmlspecialchars($torin_title, ENT_QUOTES, 'UTF-8'); ?></title>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<?php // D-06a: Sofia Sans is self-hosted; the Cyrillic subset is what renders
      // the visible Bulgarian headline, so it alone is preloaded. crossorigin
      // is required even same-origin or the file downloads twice.
      //
      // THIS HREF IS DELIBERATELY NOT VERSION-STAMPED (plan 02-08), unlike
      // every other asset URL in this file. Two independent reasons, and a
      // future reader weighing "why not stamp this one too?" needs BOTH —
      // the second is invisible from this file.
      //
      // 1. The preload URL must byte-match the URL the @font-face src in
      //    css/base.css requests, or the preload misses and the browser
      //    downloads the subset TWICE on every cold visit. base.css declares
      //    that source as url('../fonts/sofia-sans-cyrillic.woff2') with no
      //    query string, and it is a static stylesheet with no way to stamp
      //    one in. Stamping only the preload is a performance regression
      //    introduced under cover of a cache fix, on the one asset D-06a was
      //    chosen for its payload size. Under D-06a a font change means a new
      //    subset, which means a new FILENAME — its own invalidation — so the
      //    one-year woff2 expiry stays correct with no stamp.
      //
      // 2. scripts/probes/font-swap.js line 39 builds its fallback-only pass
      //    with Network.setBlockedURLs on the glob ['*.woff2','*.woff','*.ttf'],
      //    and that glob STOPS MATCHING the moment a query string is appended
      //    to the URL. A stamped woff2 would sail straight through the block:
      //    pass 1 would render with the real font, both passes would be
      //    identical, and the G-02-4 font-swap-reflow gate (plan 02-09) would
      //    report maxAbsDeltaPx: 0 — reading as the cleanest possible pass
      //    while measuring nothing whatsoever. Stamping this href does not
      //    FAIL that gate, it BLINDS it, and a blinded gate is worse than a
      //    failing one. Plan 02-09 records the same coupling from its side. ?>
<link rel="preload" href="fonts/sofia-sans-cyrillic.woff2" as="font" type="font/woff2" crossorigin>

<?php // Cascade order IS link order — no @layer, no nesting. Three separate
      // elements on purpose: plan 02-06 built the no-script override's
      // correctness on source position, so a loop or an element-emitting
      // helper would hide the very ordering the argument rests on. The
      // ?v=<filemtime> query string (G-02-1) changes the URL, never the
      // source position — cascade order is unaffected by it. ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars(torin_asset_url('css/base.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="<?php echo htmlspecialchars(torin_asset_url('css/layout.css'), ENT_QUOTES, 'UTF-8'); ?>">
<link rel="stylesheet" href="<?php echo htmlspecialchars(torin_asset_url('css/components.css'), ENT_QUOTES, 'UTF-8'); ?>">
<?php // The conditional override (plan 02-06), requested ONLY by a user agent
      // with scripting disabled — a scripting-capable one never parses this
      // element's contents as markup and never fetches the file, so there is no
      // state transition on load and no flash of an open nav collapsing.
      // Emitted HERE, after all three unconditional stylesheets, so cascade
      // order is still link order and every (0,1,0) rule in that file wins its
      // target by source position rather than by any escalation.
      //
      // CORRECTED RECORD (plan 02-08). This comment used to end "its contents
      // are static markup; no PHP reaches inside it." That is now false — the
      // href below is version-stamped by torin_asset_url() exactly like the
      // three links above it, because a reviewer with scripting disabled is
      // just as capable of holding a stale override as anyone else (G-02-1).
      // A wrong record gets corrected here, never dropped. The accurate
      // mechanism: PHP runs server-side regardless of which element it renders
      // into, so this override is stamped identically in both renderings.
      // The load-bearing half of the original claim is untouched — a
      // scripting-capable user agent never parses this element's contents as
      // markup and never fetches the file, so there is still no state
      // transition on load and no flash of an open nav collapsing. ?>
<noscript><link rel="stylesheet" href="<?php echo htmlspecialchars(torin_asset_url('css/no-js.css'), ENT_QUOTES, 'UTF-8'); ?>"></noscript>
<?php // The site's ONLY script (plan 02-03). defer, so it never blocks the
      // parser and runs after the nav markup exists.
      //
      // CORRECTED RECORD (plan 02-06). This comment used to say that with the
      // script blocked "the six category links are unreachable from the nav".
      // That understated the failure. Below 56.25rem components.css hides the
      // WHOLE list, and the only rules that reveal it are the two
      // adjacent-sibling matches on the disclosure state attribute that this
      // script alone writes — so with scripting blocked the entire five-item
      // navigation was hidden, not merely the six category links. Only the logo
      // and the footer links remained. A later phase reading the old wording
      // would have closed the wrong defect, which is how this one survived a
      // full code review; a record that is wrong gets corrected, never dropped.
      //
      // It is closed by the conditional override linked immediately above. That
      // link is load-bearing and must NOT be deleted as a redundant fourth copy
      // of the three links preceding it.
      //
      // The guarantee is unaffected: this script remains the sole writer of the
      // disclosure state attribute, and the override touches only display and
      // position. It declares no selector for that attribute and introduces no
      // second source of truth, so the announced state and the rendered state
      // still cannot disagree.
      //
      // One residual case, named rather than omitted: if scripting is ENABLED
      // but this file fails to load or throws, the nav stays hidden below
      // 56.25rem. Closing that would require a scripting-capability marker
      // written before first paint, which this project deliberately does not
      // have. ?>
<script src="<?php echo htmlspecialchars(torin_asset_url('js/site.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php // ANALYTICS-01 (D4-18, UI-SPEC C-9). The tracker, emitted ONCE, here,
      // and deferred so it never blocks the parser.
      //
      // NOT version-stamped by torin_asset_url() like every other script URL
      // in this file: that helper reads a local filemtime and this is not a
      // first-party asset. No integrity pin is possible either — the vendor
      // updates the file in place — which is accepted and recorded as T-04-37
      // rather than papered over with a hash that would go stale silently.
      //
      // NO CONNECTION HINT OF ANY KIND MAY BE ADDED FOR THIS ORIGIN, EVER.
      // One would open a third-party connection in competition with the
      // Cyrillic font preload above, which is the largest-contentful-paint
      // resource on every page. Analytics never races the font. (The two
      // relevant link-rel keywords are deliberately not spelled out here: a
      // plan-level grep asserts this file contains neither, and writing them
      // in a comment would fail the gate that guards them.)
      //
      // The web-vitals collection attribute is OMITTED — it is opt-in, adds
      // payload, and would spend the event allowance on a question nobody
      // asked. Its name is likewise not written here, for the same reason.
      //
      // Emitted only when the id is set, so blanking that one config value
      // removes the third party from all 20 pages with no edit here.
      if ($site['umami_website_id'] !== '') { ?>
<script defer src="https://cloud.umami.is/script.js" data-website-id="<?php echo htmlspecialchars($site['umami_website_id'], ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php } ?>
<?php // The first-party listener that fires every event on this site. Site-wide
      // and deferred, because the phone-anchor listener is needed on every page
      // and the form handlers inside it guard on the form's own id. It is
      // version-stamped like every other first-party asset.
      //
      // IT IS LOADED AFTER THE TRACKER ABOVE AND THAT ORDER IS NOT LOAD-BEARING:
      // both are deferred, analytics.js re-reads window.umami at each call
      // rather than capturing it once, and a missing tracker is a silent no-op.
      // Do not "fix" the order into a dependency it does not have. ?>
<script src="<?php echo htmlspecialchars(torin_asset_url('js/analytics.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
<?php echo $torin_extra_head; ?>
</head>

<body class="site-body"<?php echo $torin_track_attr; ?>>
<?php
// ── DEV-ONLY (D-03) — delete these lines at the Phase 4 cutover ──────────────
if (file_exists($torin_dev_switcher)) { torin_render_theme_switcher($torin_theme); }
// ── END DEV-ONLY ─────────────────────────────────────────────────────────────
?>

<div id="wrap">
<?php torin_render_banner($site); ?>

	<header class="site-header">
		<div class="container site-header__inner">
			<a class="site-header__brand" href="index.html">
				<img class="site-header__logo" src="img/torin-logo.png" width="150" height="80" alt="ТОРИН КОМПЮТЪРС">
			</a>
<?php
			// The five-item navigation (IA-02, D-18) with ONE single-level
			// disclosure (D-19). W3C APG Disclosure Navigation. The ARIA menu
			// roles are deliberately absent, as is arrow-key handling: those
			// make a screen reader announce a desktop application menu and
			// hand navigation to the arrow keys, which is wrong for six links
			// and a common, damaging mistake. (The prohibited role names are
			// not spelled out here — a plan-level grep asserts this file
			// contains neither of them, and a comment would defeat it.)
			// The toggles are <button>s, never anchors with a fragment href.
			?>
			<nav class="nav" aria-label="Основна навигация">
				<button class="nav__toggle" id="navToggle" type="button" aria-expanded="false" aria-controls="navList"><span class="visually-hidden">Меню</span><?php echo torin_icon('menu'); ?></button>

				<ul class="nav__list" id="navList">
					<li><a class="nav__link" href="index.html"<?php echo ($torin_nav_current === 'index.html' ? ' aria-current="page"' : ''); ?>>Начало</a></li>

					<li class="nav__item--has-sub">
						<button class="nav__disclosure" id="uslugiBtn" type="button" aria-expanded="false" aria-controls="uslugiList">Услуги<span class="nav__chevron"><?php echo torin_icon('chevron-down'); ?></span></button>
						<?php
						// All six, regardless of publish state: one accessor,
						// one rule, both surfaces. Nav shape stays constant as
						// pages publish, all six categories stay discoverable
						// from the nav (IA-02), and publishing costs zero nav
						// edits. No category filename is typed here.
						//
						// The list carries its own accessible name because the
						// button above it is removed in the no-script rendering
						// (plan 02-06) — without it the six links would lose
						// their grouping name there. Correct in both renderings:
						// a named sub-list inside a named nav.
						?>
						<ul class="nav__sub" id="uslugiList" aria-label="Услуги">
<?php foreach ($torin_categories as $cat) { ?>
							<li><a class="nav__link" href="<?php echo htmlspecialchars(torin_category_href($cat), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?></a></li>
<?php } ?>
						</ul>
					</li>

					<?php // D-20's sales line stood here until plan 03.5-01. The shop
					      // discontinued sales (CONTEXT D3.5-01), so the item and its
					      // footer counterpart are gone and both URLs 301 from
					      // src/.htaccess. The nav is FOUR items now, and that is a
					      // subtraction inside IA-02's flat-nav decision, not a redesign:
					      // the four survivors keep their order, labels and weighting. ?>
					<li><a class="nav__link" href="test-laptop.html"<?php echo ($torin_nav_current === 'test-laptop.html' ? ' aria-current="page"' : ''); ?>>Тествай сам</a></li>
					<?php // D-21 folded Запитване into Контакти; through Phase 3 that
					      // item targeted the homepage CTA block rather than a page
					      // of its own. D4-16 gives it a real page, and this href is
					      // the whole nav half of that change.
					      //
					      // It gains the aria-current ternary the other three items
					      // carry. It had none before — not an oversight, but a
					      // consequence: SCRIPT_NAME never equals a fragment, so
					      // there was no state for the ternary to detect. Now there
					      // is a page to be on.
					      //
					      // index.html#contact-us IS NOT RETIRED. The id="contact-us"
					      // sections on the homepage and the category pages stay
					      // exactly where they are, so every existing bookmark and
					      // every external link to that fragment keeps resolving.
					      // Only this href moves. ?>
					<li><a class="nav__link" href="kontakti.html"<?php echo ($torin_nav_current === 'kontakti.html' ? ' aria-current="page"' : ''); ?>>Контакти</a></li>
				</ul>
			</nav>
		</div>
	</header>
