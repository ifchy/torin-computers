<?php
// includes/settings.php — PHP 5.2-safe. Emits nothing on include; it is
// functions and no data, per this tree's include-boundary rule (only
// header.php, footer.php and banner.php produce markup).
//
// WHY THIS FILE EXISTS (OWNER-01, D4-23). The shop's working hours lived as a
// literal inside site-config.php, which is PHP. The owner has a control-panel
// login and asked to change them himself. A stray apostrophe typed into a PHP
// file blanks all twenty pages at once, and nothing on the resulting blank page
// would tell him what he had done or how to undo it. This file lets him edit
// PLAIN TEXT instead: settings.txt can be wrong in any way at all, and the worst
// outcome is that ONE value reverts to the literal compiled into site-config.php.
//
// NOT THE BUILT-IN INI PARSER, and this is not a style preference. That function
// reserves several bare words as boolean-ish literals, is fussy about quoting,
// and emits a Warning on a malformed line — which on this host prints into the
// top of the HTML of every page. That is exactly the white-screen class D4-23
// forbids, arriving through the very function that was meant to prevent it. The
// loop below is thirty lines and cannot warn, cannot halt and cannot throw.
//
// PER-KEY FALLBACK, NEVER WHOLE-FILE. A typo in the closure end date must not
// also blank the hours. The parser therefore validates and accepts each line on
// its own; a rejected line removes exactly one key from the returned set and
// leaves every other key it had already accepted untouched. There is
// deliberately NO cross-key validation anywhere in this file — the moment one
// key's acceptance depends on another's, a single bad line can take down a
// second value, which is whole-file fallback wearing a per-key disguise.
//
// DROPPED KEYS ARE DETECTABLE, NOT MERELY SURVIVED. This mirrors the ?v=0
// sentinel argued for in asset-version.php:31-37: a failure that renders
// identically to success is a failure no checker can find. The caller
// (site-config.php) records which managed keys fell back, and footer.php emits
// that list as an HTML comment on every page, so a remote check can assert zero
// unexpected fallbacks the same way scripts/asset-version-check.sh asserts zero
// zero-tokens. Note that the SHIPPED DEFAULT — no settings.txt on the server at
// all — reports every key as fallen back, and that is the correct reading, not
// an alarm: the site is meant to work before the owner has ever touched the file.
//
// THE TIMEZONE IS SET HERE, at the loader, because the server's default is not
// something this project has measured and every date comparison in the holiday
// banner and in the structured data depends on it. Europe/Sofia is the shop's
// own (D4-27). Setting it is not "emitting" and does not breach the
// include-boundary rule above.
//
// dirname(__FILE__) is the 5.2-safe idiom; the 5.3+ magic directory constant is
// banned everywhere in this tree.
date_default_timezone_set('Europe/Sofia');

// Reads the owner-edited settings file and returns ONLY the keys that validated.
// An absent or unreadable file returns an empty array and is NOT an error: that
// is the shipped state, and every key simply falls back.
//
// $path is a developer-computed absolute path (site-config.php builds it from
// this file's own location). No request value may ever reach this argument.
function torin_read_settings($path) {
	$torin_out = array();
	if (!is_readable($path)) { return $torin_out; }

	// Suppressed because an unreadable-but-existing file (a permissions change
	// made in the panel, say) must not print a Warning into the top of twenty
	// pages. The false return is handled on the next line, so the suppression
	// hides nothing this function does not already answer for.
	$torin_lines = @file($path, FILE_IGNORE_NEW_LINES);
	if ($torin_lines === false) { return $torin_out; }

	foreach ($torin_lines as $torin_line) {
		// A file saved by a Windows editor arrives with a trailing carriage
		// return that FILE_IGNORE_NEW_LINES does not strip, and an invisible
		// character on the end of "08:00" fails the clock pattern for a reason
		// nobody can see. trim() removes it along with ordinary whitespace.
		$torin_line = trim($torin_line);
		if ($torin_line === '') { continue; }

		// substr(), never the curly-brace string offset the reference sketch
		// used: that syntax was REMOVED in PHP 8 and this host now runs 8.5, so
		// it would be a parse error — not a bad value, a blank site.
		if (substr($torin_line, 0, 1) === '#') { continue; }

		// Split on the FIRST separator only. Every time value contains a colon
		// of its own, so splitting on all of them would make "08:00" unparseable
		// while looking like it worked for the days key.
		$torin_pos = strpos($torin_line, ':');
		if ($torin_pos === false) { continue; }

		$torin_key = strtolower(trim(substr($torin_line, 0, $torin_pos)));
		$torin_val = trim(substr($torin_line, $torin_pos + 1));

		if (torin_setting_is_valid($torin_key, $torin_val)) {
			$torin_out[$torin_key] = $torin_val;
		}
	}

	return $torin_out;
}

// Per-key type check. Returns true only for a key this site knows AND a value
// of the right shape for it. An unknown key returns false and is ignored
// entirely rather than passed through — a settings file is not an open bag, and
// a typo'd key name must not become a live config entry.
function torin_setting_is_valid($key, $val) {
	// Rejected before any key-specific test: a byte sequence that is not valid
	// UTF-8. The control panel's file manager will happily save a file in a
	// legacy Cyrillic encoding, and those bytes would reach htmlspecialchars()
	// with a UTF-8 charset, which answers an invalid sequence with an EMPTY
	// STRING — so a mis-encoded message would silently render as nothing at all
	// while every check reported the key as present. The empty pattern with the
	// unicode modifier returns false on invalid input and matches everything
	// else, which is the cheapest correct test available here.
	if (preg_match('//u', $val) !== 1) { return false; }

	if ($key === 'hours_open' || $key === 'hours_close') {
		return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $val) === 1;
	}

	if ($key === 'vacation_from' || $key === 'vacation_to') {
		// Empty is VALID and means the banner is off (D4-27's shipped default).
		// This is the one place an empty value is a decision rather than a gap.
		if ($val === '') { return true; }
		if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $val, $torin_m) !== 1) { return false; }
		// Shape is not existence: 2026-02-30 and 2026-13-01 both match the
		// pattern above. A date that never happens would make the banner's
		// range comparison quietly meaningless, so it is rejected here instead.
		return checkdate(intval($torin_m[2]), intval($torin_m[3]), intval($torin_m[1]));
	}

	if ($key === 'vacation_message') {
		// 120 CODE POINTS, not 300 bytes. Counting bytes is wrong twice over on
		// this site: Cyrillic is two bytes per character in UTF-8, so a byte cap
		// silently halves the allowance for Bulgarian and doubles it for anyone
		// who types Latin. The figure itself is the UI contract's, and it is a
		// measured limit rather than a round number — a 120-code-point message
		// renders as five lines at 360px and pushes the homepage's first
		// category card below the fold, which is accepted for a closure; 300
		// would render ten lines and stop being a banner.
		$torin_len = torin_settings_cp_len($val);
		return ($torin_len !== false && $torin_len <= 120);
	}

	if ($key === 'hours_days') {
		// Bounded at 20 characters AND drawn from a fixed set. The length bound
		// alone would let the owner type «Понеделник – Събота» here while the
		// structured data kept publishing Monday-to-Friday, which is the exact
		// drift OWNER-01 exists to remove — the page and the search engine would
		// disagree and nobody reading either one alone could tell. The set below
		// is what lets ONE edited value drive both the Bulgarian line the
		// visitor reads and the English day enums the search engine reads.
		if (strlen($val) > 20) { return false; }
		$torin_map = torin_settings_day_map();
		return isset($torin_map[strtolower($val)]);
	}

	return false;
}

// Length in CODE POINTS. Returns false if the value cannot be counted, and every
// caller treats false as "too long" rather than as zero — a value we cannot
// measure is not a value we may publish.
//
// The multibyte extension is present on this host (measured 2026-09-17,
// 04-HOST-CAPABILITIES), but it was absent for part of this phase and the
// account's extension set has moved once already, so the fallback stays. It is
// a code-point count either way; neither branch ever counts bytes.
function torin_settings_cp_len($val) {
	if (function_exists('mb_strlen')) { return mb_strlen($val, 'UTF-8'); }
	$torin_m = array();
	return preg_match_all('/./us', $val, $torin_m);
}

// The supported values of hours_days, and everything each one implies.
//
// ONE ROW PER VALUE, carrying all four forms together, is what makes the page
// and the structured data incapable of disagreeing: they are not two readings of
// one string, they are two columns of one row. The Bulgarian forms are stored
// twice, sentence-case and lower-case, rather than converted at runtime —
// case-folding Cyrillic needs the multibyte extension, and a fallback that
// silently returned the string unchanged would ship «Работно време:
// Понеделник...» on twenty pages without failing anything.
//
// 'open' and 'closed' together must always name all seven days. The closed list
// is emitted EXPLICITLY into the structured data rather than left to inference
// (research P-13): schema.org supports reading an absent day as closed, but the
// search engine's own documented example lists the closed day with midnight
// opening and closing times and nowhere states that omission means anything.
// This is the one value on the site where being wrong sends a customer to a
// locked door, so it is stated rather than implied.
function torin_settings_day_map() {
	return array(
		'mo-fr' => array(
			'bg'       => 'Понеделник – Петък',
			'bg_lower' => 'понеделник – петък',
			'open'     => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
			'closed'   => array('Saturday', 'Sunday')
		),
		'mo-sa' => array(
			'bg'       => 'Понеделник – Събота',
			'bg_lower' => 'понеделник – събота',
			'open'     => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
			'closed'   => array('Sunday')
		),
		'mo-su' => array(
			'bg'       => 'Понеделник – Неделя',
			'bg_lower' => 'понеделник – неделя',
			'open'     => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
			'closed'   => array()
		)
	);
}

// Resolves an hours_days value to its row. An unrecognised value cannot reach
// here through the validator, but the guard is kept anyway: this function is
// called while building $site, and a notice raised there prints above the
// doctype of every page on the site.
function torin_settings_days($token) {
	$torin_map = torin_settings_day_map();
	$torin_token = strtolower($token);
	if (isset($torin_map[$torin_token])) { return $torin_map[$torin_token]; }
	return $torin_map['mo-fr'];
}

// 24-hour clock value to the display form this site has always used: «8:00»,
// not «08:00». The stored form stays zero-padded because the structured data
// requires it; only the rendered form loses the pad.
//
// Written as an explicit first-character test rather than a left-trim of zeroes:
// trimming would turn «00:00» into «:00».
function torin_hours_display($hhmm) {
	if (substr($hhmm, 0, 1) === '0' && strlen($hhmm) > 1) { return substr($hhmm, 1); }
	return $hhmm;
}

// Bulgarian month names in the nominative, indexed 1-12.
//
// Hand-written rather than formatted by the runtime because this host has no
// Bulgarian locale to rely on and a locale-dependent date string is a value that
// changes when the hosting account is migrated — silently, and only for the one
// page element nobody re-reads.
function torin_settings_months() {
	return array(
		1  => 'януари',
		2  => 'февруари',
		3  => 'март',
		4  => 'април',
		5  => 'май',
		6  => 'юни',
		7  => 'юли',
		8  => 'август',
		9  => 'септември',
		10 => 'октомври',
		11 => 'ноември',
		12 => 'декември'
	);
}

// Formats a validated ISO date pair as the banner's Bulgarian range:
// «10 – 20 август 2026» when both dates share a month and a year, and
// «28 декември 2026 – 5 януари 2027» when they do not. The en dash with spaces
// matches the hours line the footer has always rendered.
//
// Both arguments have already passed torin_setting_is_valid(), so the substr
// arithmetic below cannot read past the end of either string. Returns an empty
// string for anything else, and every caller checks for it.
function torin_settings_date_range($from, $to) {
	if (strlen($from) !== 10 || strlen($to) !== 10) { return ''; }

	$torin_months = torin_settings_months();

	$torin_fy = substr($from, 0, 4);
	$torin_fm = intval(substr($from, 5, 2));
	$torin_fd = intval(substr($from, 8, 2));
	$torin_ty = substr($to, 0, 4);
	$torin_tm = intval(substr($to, 5, 2));
	$torin_td = intval(substr($to, 8, 2));

	if (!isset($torin_months[$torin_fm]) || !isset($torin_months[$torin_tm])) { return ''; }

	if ($torin_fy === $torin_ty && $torin_fm === $torin_tm) {
		return $torin_fd . ' – ' . $torin_td . ' ' . $torin_months[$torin_tm] . ' ' . $torin_ty;
	}

	return $torin_fd . ' ' . $torin_months[$torin_fm] . ' ' . $torin_fy
		. ' – ' . $torin_td . ' ' . $torin_months[$torin_tm] . ' ' . $torin_ty;
}
?>
