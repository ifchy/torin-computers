<?php
// scripts/notify-selftest.php — the executable form of plan 04-05 Task 3's
// any-one-wins contract, and specifically of the branch WINDOWS entry 15 says
// has never been exercised: EVERY CHANNEL FAILING.
//
// IT LIVES UNDER scripts/ AND NOT UNDER src/, DELIBERATELY, for the reason
// scripts/upload-selftest.php:6-12 gives: deploy-new.sh uploads only files
// beneath src/, so nothing here can reach the server. A notification-fan-out
// exerciser reachable over HTTP would be a way to make the shop's phone buzz
// from a URL.
//
// Run:  php scripts/notify-selftest.php
// Exit 0 means every assertion holds; exit 1 names the ones that do not.
//
// ── WHY THIS FILE MAKES NO NETWORK CALL, AND WHY THAT IS THE POINT ──────────
// The all-channels-failed branch is the one branch of this pipeline that can
// be driven honestly with no network at all, because BOTH drivers refuse
// before they reach it when handed an empty secrets array:
//
//   · torin_notify_telegram() checks for the token and the chat id and returns
//     false with a log line before touching cURL;
//   · torin_notify_mail() checks that torin_send_mail() is defined — it is
//     defined in src/contact-send.php, which this file deliberately does NOT
//     include — and returns false before touching a mail library.
//
// So `torin_notify($payload, array(), array())` reaches the aggregation with
// two honest false verdicts and no packet leaves the machine. That aggregation
// is the risky logic: it is what decides whether a visitor is told the truth,
// it gained a second channel in this plan, and until now nothing had ever run
// it with every channel down.
//
// WHAT THIS FILE CANNOT DO, STATED HERE RATHER THAN IMPLIED BY ITS ABSENCE.
// It cannot prove the ONE-channel-down case, because that needs a channel that
// is genuinely up, which needs a credential and a network. It cannot prove the
// SMTP-to-sendmail cascade in src/contact-send.php at all, for the same reason
// plus a relay to fail against. Both remain live-host checks and both are
// recorded as unrun in 04-05-SUMMARY.md.
//
// HONESTY NOTE, same as upload-selftest.php's and for the same reason: THIS
// FILE HAS NEVER BEEN RUN. The build machine has no php binary and no running
// Docker daemon. A test that has never run is a SPECIFICATION, not a gate. Do
// not cite a green run of this file that has not happened.

require_once dirname(dirname(__FILE__)) . '/src/includes/notify.php';

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

$torin_payload = array(
	'device' => 'Lenovo ThinkPad T480',
	'fault'  => 'Не се включва след заливане с вода.',
	'name'   => 'Иван Петров',
	'phone'  => '0888123456',
	'email'  => 'ivan@example.com'
);

// ── 1. Both drivers are registered ──────────────────────────────────────────
// The fan-out's channel map is the only place the driver list is observable
// from outside. A driver silently dropped from the loop would otherwise look
// exactly like a driver that failed.
$torin_r = torin_notify($torin_payload, array(), array());
torin_t(
	'both channels are registered',
	is_array($torin_r) && isset($torin_r['channels'])
		&& array_key_exists('telegram', $torin_r['channels'])
		&& array_key_exists('mail', $torin_r['channels']),
	'channels map: ' . var_export(isset($torin_r['channels']) ? $torin_r['channels'] : null, true)
);

// ── 2. Every channel failing reports overall failure (D4-08, D4-10) ─────────
// THE ASSERTION THIS FILE EXISTS FOR. If this ever returns true, every visitor
// whose enquiry was delivered nowhere is redirected to a confirmation page and
// told it arrived — the exact defect of site-current/mailer.php, which this
// plan's prohibitions forbid outright.
torin_t(
	'all channels down reports ok=false',
	isset($torin_r['ok']) && $torin_r['ok'] === false,
	'ok was ' . var_export(isset($torin_r['ok']) ? $torin_r['ok'] : null, true)
);

// ── 3. Each channel individually reports false, not null ────────────────────
// A driver that threw before assigning would leave a null here, which is falsy
// and would pass assertion 2 for the wrong reason.
$torin_ch = isset($torin_r['channels']) && is_array($torin_r['channels'])
	? $torin_r['channels']
	: array();
torin_t(
	'telegram reports exactly false',
	array_key_exists('telegram', $torin_ch) && $torin_ch['telegram'] === false,
	'telegram was ' . var_export(isset($torin_ch['telegram']) ? $torin_ch['telegram'] : null, true)
);
torin_t(
	'mail reports exactly false',
	array_key_exists('mail', $torin_ch) && $torin_ch['mail'] === false,
	'mail was ' . var_export(isset($torin_ch['mail']) ? $torin_ch['mail'] : null, true)
);

// ── 4. The mail driver degrades rather than fatals outside the quarantine ───
// notify.php lives in includes/ beside the chrome partials; torin_send_mail()
// lives in contact-send.php and is not loaded here. Calling an undefined
// function is a fatal, so the function_exists guard in the driver is the only
// thing standing between «the mail channel is unavailable» and a white page.
torin_t(
	'torin_send_mail is genuinely absent in this process',
	!function_exists('torin_send_mail'),
	'it was defined — this test is no longer exercising the guard it claims to'
);
torin_t(
	'the mail driver returns false rather than fatalling without it',
	torin_notify_mail($torin_payload, array(), array()) === false,
	'the driver did not return false'
);

// ── 5. The quarantine holds in the direction a grep cannot see at runtime ───
// A grep asserts that the string 'vendor/phpmailer' does not appear under
// src/includes/. This asserts the consequence: including notify.php on its own
// declares no library class.
torin_t(
	'including notify.php declares no mail library class',
	!class_exists('\\PHPMailer\\PHPMailer\\PHPMailer', false),
	'PHPMailer was autoloaded or required through includes/ — the quarantine is breached'
);

// ── 6. The composer both channels share is intact ───────────────────────────
// The mail driver reuses torin_notify_compose(), so a change made for one
// channel is a change made for both. These two assertions are what would catch
// a field quietly dropped from the body for the sake of the other channel.
$torin_body = torin_notify_compose($torin_payload);
torin_t(
	'the composed body carries every submitted field',
	strpos($torin_body, $torin_payload['device']) !== false
		&& strpos($torin_body, $torin_payload['fault']) !== false
		&& strpos($torin_body, $torin_payload['name']) !== false
		&& strpos($torin_body, $torin_payload['phone']) !== false
		&& strpos($torin_body, $torin_payload['email']) !== false,
	'a field is missing from the composed body'
);
torin_t(
	'the composed body is plain text with no markup',
	strpos($torin_body, '<') === false && strpos($torin_body, '&') === false,
	'the body carries markup — parse_mode/HTML escaping questions are back'
);

echo "\n" . ($torin_count - count($torin_fails)) . '/' . $torin_count . " assertions held\n";
if (count($torin_fails) > 0) {
	echo "failed: " . implode(', ', $torin_fails) . "\n";
	exit(1);
}
exit(0);
?>
