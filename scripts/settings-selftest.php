<?php
// scripts/settings-selftest.php — the executable form of plan 04-06 Task 1's
// <behavior> block: ten assertions, one per bullet, in the order the plan
// writes them, followed by the derived helpers the rest of the plan rests on
// and by Task 2's date gate.
//
// THE DATE GATE IS IN THIS FILE RATHER THAN A SECOND ONE because the banner's
// visibility is a settings question wearing a rendering costume: the gate reads
// two parsed values and a timezone that this file's subject sets. Splitting
// them would let one be run without the other, and the auto-expiry boundary is
// the single most likely thing in this plan to be off by one day.
//
// IT LIVES UNDER scripts/ AND NOT UNDER src/, DELIBERATELY. deploy-new.sh
// uploads only files beneath src/ (scripts/deploy-new.sh:40 and :161), so
// nothing here can reach the server. It also writes temporary fixture files;
// none of them is ever created inside the document root.
//
// Run:  php scripts/settings-selftest.php
// Exit 0 means every behaviour in the plan holds; exit 1 names the ones that do
// not. No extension is required beyond the PHP core — the code-point counter
// has a fallback for a host without the multibyte extension and this file
// exercises whichever branch the runtime provides.
//
// HONESTY NOTE, and it is why this header is long. This file has never been
// observed to fail and has never been observed to pass: the build machine that
// authored it has no php binary and no running Docker daemon, which is the same
// gap 04-02-SUMMARY and 04-03-SUMMARY both record. A test that has never run is
// a SPECIFICATION, not a gate. It is committed as a specification, it is
// labelled as one here rather than in a footnote, and it becomes the gate it
// was written to be the moment a PHP runtime exists. Do not cite a green run of
// this file that has not happened.
//
// What HAS been exercised, and is recorded in 04-06-SUMMARY.md rather than
// claimed here: the validator's regular expressions and numeric bounds were
// extracted from src/includes/settings.php by a harness and run against this
// same fixture table under Node. That is evidence about the patterns that ship;
// it is not evidence that this PHP parses, and the two must not be conflated.

require_once dirname(dirname(__FILE__)) . '/src/includes/settings.php';

$torin_fails = array();
$torin_count = 0;

function torin_t($name, $ok, $detail) {
	global $torin_fails, $torin_count;
	$torin_count++;
	if ($ok) {
		echo "ok   " . $name . "\n";
		return;
	}
	echo "FAIL " . $name . " — " . $detail . "\n";
	$torin_fails[] = $name;
}

// Writes a settings file into the system temp directory and returns its path.
function torin_fx_settings($body) {
	$path = tempnam(sys_get_temp_dir(), 'settings_');
	file_put_contents($path, $body);
	return $path;
}

// Runs the parser over a body and returns the resulting array. Warnings are
// promoted to a collected list rather than printed, because "no parse path
// emits a warning" is itself one of the behaviours under test — printing them
// would make the very defect invisible in the output.
$torin_warnings = array();
function torin_warning_collector($errno, $errstr) {
	global $torin_warnings;
	$torin_warnings[] = $errstr;
	return true;
}

function torin_parse($body) {
	$path = torin_fx_settings($body);
	$out = torin_read_settings($path);
	unlink($path);
	return $out;
}

set_error_handler('torin_warning_collector');

// ── 1. A well-formed file returns every declared key with its value ──────────

$full = torin_parse(
	"hours_open: 09:30\n" .
	"hours_close: 18:45\n" .
	"hours_days: Mo-Sa\n" .
	"vacation_from: 2026-08-10\n" .
	"vacation_to: 2026-08-20\n" .
	"vacation_message: Затворено за ремонт.\n"
);
torin_t(
	'well-formed file returns all six keys',
	count($full) === 6
		&& $full['hours_open'] === '09:30'
		&& $full['hours_close'] === '18:45'
		&& $full['hours_days'] === 'Mo-Sa'
		&& $full['vacation_from'] === '2026-08-10'
		&& $full['vacation_to'] === '2026-08-20'
		&& $full['vacation_message'] === 'Затворено за ремонт.',
	'got ' . var_export($full, true)
);

// ── 2. A line with no separator is skipped, no other key affected ────────────

$nosep = torin_parse("hours_open: 09:30\nthis line has no separator\nhours_close: 18:45\n");
torin_t(
	'separatorless line is skipped and neighbours survive',
	count($nosep) === 2 && $nosep['hours_open'] === '09:30' && $nosep['hours_close'] === '18:45',
	'got ' . var_export($nosep, true)
);

// ── 3. A value failing its type check is dropped; that key alone falls back ──

$badtime = torin_parse("hours_open: 25:00\nhours_close: 18:45\nhours_days: Mo-Fr\n");
torin_t(
	'invalid value drops exactly one key',
	!isset($badtime['hours_open'])
		&& $badtime['hours_close'] === '18:45'
		&& $badtime['hours_days'] === 'Mo-Fr',
	'got ' . var_export($badtime, true)
);

// ── 4. A comment line and a blank line are both ignored ──────────────────────

$comments = torin_parse("# hours_open: 07:00\n\n   \nhours_open: 09:30\n   # trailing comment\n");
torin_t(
	'comment and blank lines are ignored',
	count($comments) === 1 && $comments['hours_open'] === '09:30',
	'got ' . var_export($comments, true)
);

// ── 5. An unknown key is ignored entirely rather than passed through ─────────

$unknown = torin_parse("hours_open: 09:30\nphone: 0888123456\nemail: a@b.bg\n");
torin_t(
	'unknown keys are dropped, not passed through',
	count($unknown) === 1 && $unknown['hours_open'] === '09:30',
	'got ' . var_export($unknown, true)
);

// ── 6. An absent or unreadable file returns an empty set, and is not an error ─

$absent = torin_read_settings(sys_get_temp_dir() . '/torin-no-such-settings-' . mt_rand() . '.txt');
torin_t(
	'absent file returns an empty set and is not an error',
	is_array($absent) && count($absent) === 0,
	'got ' . var_export($absent, true)
);

// ── 7. A time value outside a valid clock range is rejected ──────────────────

torin_t(
	'clock range is enforced at both ends',
	torin_setting_is_valid('hours_open', '00:00') === true
		&& torin_setting_is_valid('hours_close', '23:59') === true
		&& torin_setting_is_valid('hours_open', '24:00') === false
		&& torin_setting_is_valid('hours_open', '09:60') === false
		&& torin_setting_is_valid('hours_open', '9:30') === false
		&& torin_setting_is_valid('hours_open', '') === false,
	'boundary values misclassified'
);

// ── 8. Date format enforced; empty accepted because empty means banner off ───

torin_t(
	'dates: format enforced, empty accepted, impossible dates rejected',
	torin_setting_is_valid('vacation_from', '') === true
		&& torin_setting_is_valid('vacation_to', '') === true
		&& torin_setting_is_valid('vacation_from', '2026-08-10') === true
		&& torin_setting_is_valid('vacation_from', '10.08.2026') === false
		&& torin_setting_is_valid('vacation_from', '2026-8-1') === false
		&& torin_setting_is_valid('vacation_from', '2026-13-01') === false
		&& torin_setting_is_valid('vacation_from', '2026-02-30') === false,
	'date classification wrong'
);

// ── 9. A message longer than the cap is rejected rather than truncated ───────
//
// The cap is 120 CODE POINTS. The Cyrillic fixture below is 121 characters and
// 242 bytes; a byte-counting implementation would have rejected it at 121 bytes
// and would also wrongly reject the 120-character one, so this pair
// distinguishes a code-point cap from a byte cap rather than merely asserting
// that something long is refused.

$cp120 = str_repeat('я', 120);
$cp121 = str_repeat('я', 121);
$atcap = torin_parse("vacation_message: " . $cp120 . "\n");
$overcap = torin_parse("vacation_message: " . $cp121 . "\n");
torin_t(
	'message cap is 120 code points and over-long is rejected, never truncated',
	isset($atcap['vacation_message'])
		&& $atcap['vacation_message'] === $cp120
		&& !isset($overcap['vacation_message']),
	'at-cap len ' . strlen($cp120) . 'B, over-cap accepted=' . var_export(isset($overcap['vacation_message']), true)
);

// ── 10. No parse path emits a warning, and no parse path stops execution ─────
//
// Everything above has already run under the collector, including the absent
// file, the malformed lines and the over-long value. Reaching this line at all
// is the "does not stop execution" half; an empty collector is the other half.

torin_t(
	'no parse path emitted a warning',
	count($torin_warnings) === 0,
	'warnings: ' . implode(' | ', $torin_warnings)
);

// ── Beyond the behaviour block: the two pieces the rest of the plan rests on ─

restore_error_handler();

torin_t(
	'hours_days is a controlled token, not free text',
	torin_setting_is_valid('hours_days', 'Mo-Fr') === true
		&& torin_setting_is_valid('hours_days', 'mo-su') === true
		&& torin_setting_is_valid('hours_days', 'Понеделник – Петък') === false
		&& torin_setting_is_valid('hours_days', 'Mo-We') === false,
	'day token classification wrong'
);

$mofr = torin_settings_days('Mo-Fr');
torin_t(
	'the day token names all seven days across open and closed',
	count($mofr['open']) === 5 && count($mofr['closed']) === 2
		&& $mofr['bg'] === 'Понеделник – Петък',
	'got ' . var_export($mofr, true)
);

torin_t(
	'hours display drops the leading zero without mangling midnight',
	torin_hours_display('08:00') === '8:00'
		&& torin_hours_display('16:00') === '16:00'
		&& torin_hours_display('00:00') === '0:00',
	'display conversion wrong'
);

torin_t(
	'date range collapses a shared month and spells out a split one',
	torin_settings_date_range('2026-08-10', '2026-08-20') === '10 – 20 август 2026'
		&& torin_settings_date_range('2026-12-28', '2027-01-05') === '28 декември 2026 – 5 януари 2027',
	'got ' . torin_settings_date_range('2026-08-10', '2026-08-20')
		. ' / ' . torin_settings_date_range('2026-12-28', '2027-01-05')
);

// ── The banner's date gate (Task 2, OWNER-02) ───────────────────────────────
//
// THE AUTO-EXPIRY PROOF. The plan says this must be run rather than reasoned
// about, and the reason is that a date boundary is exactly the kind of thing
// that reads correct and behaves off by one. The four cases below are the
// boundary in full: off, inside, the last day, and the day after. They are
// written against RELATIVE dates so the file does not start passing or failing
// because of the calendar.

require_once dirname(dirname(__FILE__)) . '/src/includes/banner.php';

function torin_banner_html($from, $to, $message) {
	$site = array(
		'vacation_from'    => $from,
		'vacation_to'      => $to,
		'vacation_message' => $message
	);
	ob_start();
	torin_render_banner($site);
	return ob_get_clean();
}

$torin_yesterday = date('Y-m-d', strtotime('-1 day'));
$torin_today     = date('Y-m-d');
$torin_tomorrow  = date('Y-m-d', strtotime('+1 day'));
$torin_lastweek  = date('Y-m-d', strtotime('-7 days'));

torin_t(
	'banner off by default emits NOTHING — not an empty element',
	torin_banner_html('', '', 'Затворено.') === '',
	'emitted ' . var_export(torin_banner_html('', '', 'Затворено.'), true)
);

$torin_current = torin_banner_html($torin_yesterday, $torin_tomorrow, 'Затворено за отпуск.');
torin_t(
	'a current closure renders the strip exactly once, with its message',
	substr_count($torin_current, 'holiday-banner') === 2   // block + __inner
		&& substr_count($torin_current, 'Затворено за отпуск.') === 1,
	'emitted ' . $torin_current
);

torin_t(
	'INCLUSIVE end: a closure ending TODAY still renders today',
	strpos(torin_banner_html($torin_lastweek, $torin_today, 'Затворено.'), 'holiday-banner') !== false,
	'nothing emitted on the end date itself'
);

torin_t(
	'AUTO-EXPIRY: a closure that ended YESTERDAY emits nothing, with no second edit',
	torin_banner_html($torin_lastweek, $torin_yesterday, 'Затворено.') === '',
	'strip survived its end date'
);

torin_t(
	'a closure that has not started yet emits nothing',
	torin_banner_html($torin_tomorrow, $torin_tomorrow, 'Затворено.') === '',
	'strip rendered ahead of its start date'
);

torin_t(
	'the strip carries no script, no button and no storage',
	strpos($torin_current, '<script') === false
		&& strpos($torin_current, '<button') === false
		&& strpos($torin_current, 'localStorage') === false,
	'interactive or scripted content found in the strip'
);

// ── The structured data (Task 3, D4-26, D4-27, research P-13) ───────────────
//
// The block is parsed AS A WHOLE BLOCK and never line by line. A previous audit
// on this project reported this JSON malformed because it read it with a
// line-oriented tool; the block is multiline and was valid the whole time.

require_once dirname(dirname(__FILE__)) . '/src/includes/site-config.php';

// Renders jsonld.php with the vacation keys set to the given pair and returns
// the decoded LocalBusiness object. $site is reached through `global` so that
// jsonld.php, which is written to run at file scope, sees the same array this
// function edited.
function torin_ld($from, $to) {
	global $site;
	$site['vacation_from'] = $from;
	$site['vacation_to']   = $to;
	ob_start();
	include dirname(dirname(__FILE__)) . '/src/includes/jsonld.php';
	$html = ob_get_clean();
	$m = array();
	if (!preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m)) {
		return null;
	}
	return json_decode($m[1], true);
}

$torin_ld_off = torin_ld('', '');
torin_t(
	'with no closure scheduled the block parses and carries exactly THREE entries',
	is_array($torin_ld_off)
		&& isset($torin_ld_off['openingHoursSpecification'])
		&& count($torin_ld_off['openingHoursSpecification']) === 3,
	'got ' . var_export($torin_ld_off, true)
);

torin_t(
	'the weekend is STATED, not inferred: Saturday and Sunday at midnight/midnight',
	is_array($torin_ld_off)
		&& $torin_ld_off['openingHoursSpecification'][1]['dayOfWeek'] === 'Saturday'
		&& $torin_ld_off['openingHoursSpecification'][1]['opens'] === '00:00'
		&& $torin_ld_off['openingHoursSpecification'][1]['closes'] === '00:00'
		&& $torin_ld_off['openingHoursSpecification'][2]['dayOfWeek'] === 'Sunday',
	'weekend entries wrong'
);

$torin_ld_current = torin_ld($torin_lastweek, $torin_tomorrow);
torin_t(
	'with a current closure the block carries FOUR entries, the fourth seasonal',
	is_array($torin_ld_current)
		&& count($torin_ld_current['openingHoursSpecification']) === 4
		&& $torin_ld_current['openingHoursSpecification'][3]['validFrom'] === $torin_lastweek
		&& $torin_ld_current['openingHoursSpecification'][3]['validThrough'] === $torin_tomorrow
		&& !isset($torin_ld_current['openingHoursSpecification'][3]['dayOfWeek']),
	'got ' . var_export(isset($torin_ld_current['openingHoursSpecification']) ? $torin_ld_current['openingHoursSpecification'] : null, true)
);

$torin_ld_past = torin_ld($torin_lastweek, $torin_yesterday);
torin_t(
	'with a closure that has passed the block is back to THREE entries',
	is_array($torin_ld_past) && count($torin_ld_past['openingHoursSpecification']) === 3,
	'got ' . count($torin_ld_past['openingHoursSpecification']) . ' entries'
);

// The encoder's slash escaping is the T-02-11 control and is now a DEFAULT
// rather than a structural property of the runtime (see jsonld.php's header).
// A default is worth asserting; a structural property was not.
$site['vacation_from'] = '';
$site['vacation_to']   = '';
ob_start();
include dirname(dirname(__FILE__)) . '/src/includes/jsonld.php';
$torin_ld_raw = ob_get_clean();
torin_t(
	'forward slashes are escaped in the emitted JSON (T-02-11 still closed)',
	strpos($torin_ld_raw, '"https:\\/\\/schema.org"') !== false,
	'bare slashes found — an encoding flag has been introduced somewhere'
);

torin_t(
	'the three rendered hours consumers all read one composed value',
	strpos($site['hours'], $site['hours_open_display']) !== false
		&& strpos($site['notice'], $site['hours_open_display']) !== false
		&& $torin_ld_off['openingHoursSpecification'][0]['opens'] === $site['hours_open'],
	'hours: ' . $site['hours'] . ' / notice: ' . $site['notice']
);

// ── Result ──────────────────────────────────────────────────────────────────

echo "\n" . ($torin_count - count($torin_fails)) . "/" . $torin_count . " passed\n";
if (count($torin_fails) > 0) {
	echo "failed: " . implode(', ', $torin_fails) . "\n";
	exit(1);
}
exit(0);
?>
