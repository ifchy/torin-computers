<?php
// includes/notify.php — PHP 5.2-safe (array() only, named functions only, no
// namespaces, no short echo tags, tabs). Emits nothing on include: function
// definitions and no top-level output, exactly like icons.php and
// asset-version.php.
//
// THE NOTIFICATION FAN-OUT (RESEARCH A-1, D4-05, D4-08). One entry point,
// torin_notify(), one driver per channel behind it, and a return value that
// says both «did anything get through» and «which channel did what».
//
// In this tracer exactly ONE driver is registered — Telegram. The mail leg
// slots in beside it in 04-05 by adding a second line to the driver loop and
// a second function; neither the interface nor the caller changes. That is the
// whole point of introducing the fan-out now rather than when there are two
// channels to fan out to: a second channel added to a single-channel handler
// means rewriting the handler's success test, and the success test is the one
// piece of this pipeline that must not be rewritten under time pressure.
//
// WHY THIS FILE NEVER READS THE SECRETS FILE. The token and chat id arrive as
// an ARGUMENT from contact-send.php, which is the only file permitted to
// resolve $site['secrets_path'] (T-04-08). notify.php touches no filesystem
// path at all. That is what makes it safe for this file to sit in includes/
// beside the chrome partials: even if a future edit accidentally required it
// from header.php or footer.php, no include chain from the page shell could
// reach a credential, because there is no credential in here to reach.
//
// WHY 5.2-SAFE, WHEN ITS ONE CALLER IS NOT. contact-send.php is the single
// quarantined modern-PHP file (RESEARCH A-3), and quarantine only works if the
// quarantined set stays at one. A 5.2 interpreter meeting namespaced code
// fails at COMPILE time, before any output — so if this file were written in
// the modern dialect, a runtime rollback would take down whatever else ever
// comes to include it, instead of the one POST endpoint.

// Compose the notification body from the submitted enquiry.
//
// PLAIN TEXT, with NO Telegram parse_mode. This is a security decision, not a
// formatting preference, and it is the reason there is no escaping helper in
// this file: with parse_mode set to HTML or MarkdownV2, every one of these
// values is attacker-controlled markup that would need escaping against
// Telegram's own rules, and an escaping bug there ranges from a mangled
// message to a message that silently truncates at the first stray angle
// bracket. Sending plain text removes the entire class. The owner loses bold
// labels; the owner keeps every character the customer actually typed.
function torin_notify_compose($payload) {
	$torin_lines = array(
		'Ново запитване от torin.bg',
		'',
		'Устройство: ' . $payload['device'],
		'Повреда: ' . $payload['fault'],
		'',
		'Име: ' . $payload['name'],
		'Телефон: ' . $payload['phone'],
		'Имейл: ' . $payload['email']
	);
	$torin_text = implode("\n", $torin_lines);

	// Telegram's sendMessage ceiling is 4096 characters. Over it the API
	// returns an error and the message is not «truncated», it is NOT SENT —
	// so clamping here is what stops a long fault description from losing the
	// whole enquiry rather than losing its tail.
	//
	// mb_substr counts CHARACTERS; substr counts BYTES, and every string above
	// is Cyrillic at two bytes per character. Falling back to substr would cut
	// a 4096-byte prefix mid-character and hand Telegram invalid UTF-8. The
	// byte fallback therefore uses a third of the limit, which is safe for any
	// UTF-8 this form can produce, and it only runs if mbstring disappears —
	// it is measured present on this host (04-HOST-CAPABILITIES, final probe).
	if (function_exists('mb_substr')) {
		if (mb_strlen($torin_text, 'UTF-8') > 4096) {
			$torin_text = mb_substr($torin_text, 0, 4090, 'UTF-8') . "\n…";
		}
	} else {
		if (strlen($torin_text) > 4096) {
			$torin_text = substr($torin_text, 0, 1360) . "\n…";
		}
	}
	return $torin_text;
}

// The Telegram driver. Returns true only when the API itself reported success;
// anything else — no cURL, a timeout, a non-200, an `ok:false` body — is false.
//
// A 200 IS NOT ENOUGH ON ITS OWN. Telegram answers a rejected sendMessage with
// a JSON body carrying "ok":false, and on some error classes it does so under
// a 200. Treating the HTTP status as the answer is the same defect as
// site-current/mailer.php discarding mail()'s return value, one layer up.
function torin_notify_telegram($payload, $photos, $secrets) {
	// $photos is accepted and deliberately UNUSED in this tracer. 04-03 sends
	// the photographs through sendPhoto/sendMediaGroup from inside this same
	// function, with the composed text as the caption of the first one. The
	// parameter ships now so that adding uploads is a change to this function's
	// BODY and to nothing else — not a change to the interface, the fan-out,
	// or the handler. A tracer stub that would force a signature change later
	// is not a tracer stub, it is a placeholder.
	if (!function_exists('curl_init')) {
		error_log('torin notify: ext-curl missing');
		return false;
	}
	if (!isset($secrets['telegram_bot_token']) || !isset($secrets['telegram_chat_id'])) {
		error_log('torin notify: secrets incomplete');
		return false;
	}

	$torin_body = array(
		'chat_id' => $secrets['telegram_chat_id'],
		'text'    => torin_notify_compose($payload),
		'disable_web_page_preview' => true
	);

	// json_encode, never string concatenation. A hand-built body would need
	// the same escaping argument the parse_mode note above rejects, and a
	// newline in a fault description is enough to break a concatenated form.
	$torin_json = json_encode($torin_body);
	if ($torin_json === false) {
		error_log('torin notify: payload not encodable');
		return false;
	}

	// The token goes into the URL because that is the shape of Telegram's API.
	// It is never logged and never echoed: every error_log call in this file
	// carries a fixed literal and no interpolated value, so the token cannot
	// reach the log through a diagnostic (T-04-08, P-14).
	$torin_url = 'https://api.telegram.org/bot' . $secrets['telegram_bot_token'] . '/sendMessage';

	$torin_ch = curl_init($torin_url);
	curl_setopt($torin_ch, CURLOPT_POST, true);
	curl_setopt($torin_ch, CURLOPT_POSTFIELDS, $torin_json);
	curl_setopt($torin_ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($torin_ch, CURLOPT_RETURNTRANSFER, true);
	// BOTH timeouts, and both bounded. CURLOPT_TIMEOUT alone caps the whole
	// transfer but lets a black-holed connection sit in connect() for the
	// system default first. A visitor waiting on a form submission is the one
	// paying for an unbounded wait here (T-04-11).
	curl_setopt($torin_ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($torin_ch, CURLOPT_TIMEOUT, 10);
	// Certificate verification stays ON. It is the default, and it is written
	// out anyway so that nobody «fixes» a future TLS error by turning it off:
	// this request carries a credential to a fixed third-party host, and an
	// unverified peer is exactly the case where that credential leaves.
	curl_setopt($torin_ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($torin_ch, CURLOPT_SSL_VERIFYHOST, 2);

	$torin_resp = curl_exec($torin_ch);
	$torin_code = curl_getinfo($torin_ch, CURLINFO_HTTP_CODE);
	curl_close($torin_ch);

	if ($torin_resp === false || $torin_code !== 200) {
		error_log('torin notify: telegram transport failed');
		return false;
	}

	$torin_decoded = json_decode($torin_resp, true);
	if (!is_array($torin_decoded) || !isset($torin_decoded['ok']) || $torin_decoded['ok'] !== true) {
		error_log('torin notify: telegram rejected the message');
		return false;
	}
	return true;
}

// The fan-out. Returns array('ok' => bool, 'channels' => array(name => bool)).
//
// $secrets is a third parameter that RESEARCH A-1's sketch does not show. It
// is required, not incidental: it is what keeps this file off the filesystem
// (see the header note), and the alternative — reading the secrets path in
// here — would put a credential inside includes/, one careless require away
// from the page shell.
function torin_notify($payload, $photos, $secrets) {
	$torin_channels = array();

	// One entry per registered driver. 04-05 adds 'mail' here and nothing else
	// in this function changes.
	$torin_drivers = array('telegram');

	foreach ($torin_drivers as $torin_driver) {
		$torin_channels[$torin_driver] = false;
		// EVERY driver call is wrapped, and the wrapper is the point. A
		// Telegram outage, a DNS failure or a malformed API response must not
		// reach the visitor as a 500 on a form they just spent two minutes
		// filling in (T-04-11). A driver that fails is a channel that returned
		// false — never an exception that escapes this loop.
		try {
			if ($torin_driver === 'telegram') {
				$torin_channels[$torin_driver] = torin_notify_telegram($payload, $photos, $secrets);
			}
		} catch (Exception $torin_err) {
			error_log('torin notify: driver threw (' . $torin_driver . ')');
		} catch (Throwable $torin_err) {
			// PHP 7+ engine errors (a TypeError from a changed signature, for
			// instance) do NOT extend Exception, so the block above would miss
			// them and a form submission would 500 on a code defect rather
			// than degrade. The class name does not exist on PHP 5.2, but a
			// catch block naming an undefined class still PARSES there and
			// simply never matches — so this stays inside the 5.2-safe dialect
			// while closing the PHP 8 case that the host actually runs.
			error_log('torin notify: driver errored (' . $torin_driver . ')');
		}
	}

	// ANY ONE CHANNEL SUCCEEDING IS SUCCESS (D4-08). The caller branches on
	// this flag and never on an individual channel — which is what makes
	// D4-05's choice of Telegram reversible, and what stops the email leg
	// landing in 04-05 from turning a delivered enquiry into a reported
	// failure.
	$torin_ok = false;
	foreach ($torin_channels as $torin_result) {
		if ($torin_result === true) {
			$torin_ok = true;
		}
	}

	return array('ok' => $torin_ok, 'channels' => $torin_channels);
}
?>
