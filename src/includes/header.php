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

// Per-page metadata mechanism (02-RESEARCH N-5). A page assigns these before
// the include; anything left unset falls back to the site-level default, so a
// stub page that assigns nothing still renders a non-empty title.
if (!isset($torin_title)) {
	$torin_title = 'ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ';
}
if (!isset($torin_desc)) {
	$torin_desc = 'ТОРИН КОМПЮТЪРС — ремонт на лаптопи в София: счупвания, екран и клавиатура, оптимизация, заливане и дънни платки, прегряване, нестандартна техника. Безплатна диагностика.';
}
?>
<!DOCTYPE html>
<html lang="bg">
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
      // is required even same-origin or the file downloads twice. ?>
<link rel="preload" href="fonts/sofia-sans-cyrillic.woff2" as="font" type="font/woff2" crossorigin>

<?php // Cascade order IS link order — no @layer, no nesting. ?>
<link rel="stylesheet" href="css/base.css">
<link rel="stylesheet" href="css/layout.css">
<link rel="stylesheet" href="css/components.css">
</head>

<body class="site-body">

<div id="wrap">

	<header class="site-header">
		<div class="container site-header__inner">
			<a class="site-header__brand" href="index.html">
				<img class="site-header__logo" src="img/torin-logo.png" width="150" height="80" alt="ТОРИН КОМПЮТЪРС">
			</a>
			<!-- NAV PLACEHOLDER — plan 02-03 inserts the Услуги disclosure nav here (IA-02, D-18/D-19). -->
		</div>
	</header>
