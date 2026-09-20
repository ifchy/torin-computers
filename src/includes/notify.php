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
	return torin_notify_clamp($torin_text, 4096);
}

// Clamp to a CHARACTER budget, leaving a visible mark where the cut happened.
//
// THE MARKER IS THE POINT, not decoration. A silent cut at the API boundary
// reads to the owner as a customer who stopped mid-sentence; an ellipsis on
// its own line reads as text that was too long, which is a different thing to
// ring back about. The clamp is deliberate truncation, never a rejection —
// losing the tail of a fault description is survivable, losing the enquiry is
// not.
//
// mb_substr counts CHARACTERS; substr counts BYTES, and every string this form
// produces is Cyrillic at two bytes per character. Falling back to substr
// would cut a prefix mid-character and hand Telegram invalid UTF-8. The byte
// fallback therefore takes a third of the budget, which is safe for any UTF-8
// this form can produce, and it only runs if mbstring disappears — it is
// measured present on this host (04-HOST-CAPABILITIES, final probe), and it
// has disappeared from this account once already.
function torin_notify_clamp($text, $limit) {
	if (function_exists('mb_substr')) {
		if (mb_strlen($text, 'UTF-8') <= $limit) {
			return $text;
		}
		return mb_substr($text, 0, $limit - 2, 'UTF-8') . "\n…";
	}
	if (strlen($text) <= $limit) {
		return $text;
	}
	return substr($text, 0, (int) floor($limit / 3)) . "\n…";
}

// ONE OUTBOUND CALL, whatever the method. Returns true only when the API
// itself reported success; anything else — no cURL, a timeout, a non-200, an
// `ok:false` body — is false.
//
// A 200 IS NOT ENOUGH ON ITS OWN. Telegram answers a rejected call with a JSON
// body carrying "ok":false, and on some error classes it does so under a 200.
// Treating the HTTP status as the answer is the same defect as
// site-current/mailer.php discarding mail()'s return value, one layer up.
//
// $useJson picks the encoding, and the two are NOT interchangeable. A call
// carrying files has to be multipart, and setting a JSON content type on a
// multipart body produces a request Telegram cannot parse and a failure that
// looks like a network fault.
function torin_notify_tg_call($secrets, $method, $fields, $useJson) {
	if (!function_exists('curl_init')) {
		error_log('torin notify: ext-curl missing');
		return false;
	}

	// The token goes into the URL because that is the shape of Telegram's API.
	// It is never logged and never echoed: every error_log call in this file
	// carries a fixed literal plus the method name, which is a
	// developer-authored constant, so the token cannot reach the log through a
	// diagnostic (T-04-08, P-14).
	$torin_url = 'https://api.telegram.org/bot' . $secrets['telegram_bot_token'] . '/' . $method;

	$torin_ch = curl_init($torin_url);
	curl_setopt($torin_ch, CURLOPT_POST, true);

	if ($useJson) {
		// json_encode, never string concatenation — the house rule
		// jsonld.php:14-27 argues for structured data. A hand-built body
		// would need the same escaping argument the parse_mode note above
		// rejects, and a newline in a fault description is enough to break a
		// concatenated form.
		$torin_json = json_encode($fields);
		if ($torin_json === false) {
			error_log('torin notify: payload not encodable (' . $method . ')');
			curl_close($torin_ch);
			return false;
		}
		curl_setopt($torin_ch, CURLOPT_POSTFIELDS, $torin_json);
		curl_setopt($torin_ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($torin_ch, CURLOPT_TIMEOUT, 10);
	} else {
		// An array containing a CURLFile makes cURL build the multipart body
		// itself, headers and boundaries included. No Content-Type is set by
		// hand here: overriding it discards the boundary cURL generated.
		curl_setopt($torin_ch, CURLOPT_POSTFIELDS, $fields);
		// Wider than the JSON ceiling because this one carries megabytes, and
		// still bounded, because a visitor is holding a submitted form open
		// for the duration (T-04-11). Every photograph on this path has been
		// through the re-encode in includes/upload.php, which caps the long
		// edge at 1600px — so five of them is single-digit megabytes, not the
		// fifty the raw limits would allow.
		curl_setopt($torin_ch, CURLOPT_TIMEOUT, 30);
	}

	curl_setopt($torin_ch, CURLOPT_RETURNTRANSFER, true);
	// BOTH timeouts, and both bounded. CURLOPT_TIMEOUT alone caps the whole
	// transfer but lets a black-holed connection sit in connect() for the
	// system default first.
	curl_setopt($torin_ch, CURLOPT_CONNECTTIMEOUT, 5);
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
		error_log('torin notify: telegram transport failed (' . $method . ')');
		return false;
	}

	$torin_decoded = json_decode($torin_resp, true);
	if (!is_array($torin_decoded) || !isset($torin_decoded['ok']) || $torin_decoded['ok'] !== true) {
		error_log('torin notify: telegram rejected the call (' . $method . ')');
		return false;
	}
	return true;
}

// The plain text message. Factored out because four different places in the
// driver below need exactly it, and four copies of one array literal is four
// chances for one of them to lose a field.
function torin_notify_tg_text($secrets, $text) {
	return torin_notify_tg_call($secrets, 'sendMessage', array(
		'chat_id' => $secrets['telegram_chat_id'],
		'text'    => $text,
		'disable_web_page_preview' => true
	), true);
}

// One attachment. The name Telegram is given is GENERATED — the visitor's own
// filename never travels, here or anywhere (it is not even an argument to the
// upload pipeline).
//
// CURLFile, never the legacy @-prefixed path string. That syntax is gone from
// modern PHP, and its failure mode is not an error: the path is posted as an
// ordinary text field and the call «succeeds» having sent no photograph.
function torin_notify_tg_file($path, $index) {
	return new CURLFile($path, 'image/jpeg', 'torin-' . $index . '.jpg');
}

// The Telegram driver — THREE SHAPES, KEYED ON THE PHOTO COUNT.
//
// The obvious single call does not exist. The media-group method REFUSES a
// media array with fewer than two items, and «one photo of the cracked
// screen» is the single most likely submission this form will ever see — so a
// driver that always reached for it would fail on exactly the common case and
// work in every test written with two. That refusal rule is the whole reason
// the one-photo branch exists, and it is the thing a later editor must not
// «simplify» away.
//
// Zero  -> the text message, carrying every field.
// One   -> the single-photo method, enquiry as the caption.
// Two+  -> the text message FIRST, then the group. In that order, so that a
//          group that fails still leaves the enquiry delivered.
//
// THE THREE LIMITS THIS FUNCTION DOES NOT RE-CHECK, and where they are
// actually guaranteed: the 10 MB per-photo multipart ceiling, the 10000-pixel
// combined-dimension rule, and the aspect ratio of 20. All three are
// established by the re-encode in includes/upload.php — the long edge is
// capped at 1600px, the ratio is rejected outright, and the re-encode is what
// makes the byte count a consequence of the geometry rather than of whatever
// arrived. Re-checking them here would be a second writer of the same rule.
function torin_notify_telegram($payload, $photos, $secrets) {
	if (!function_exists('curl_init')) {
		error_log('torin notify: ext-curl missing');
		return false;
	}
	if (!isset($secrets['telegram_bot_token']) || !isset($secrets['telegram_chat_id'])) {
		error_log('torin notify: secrets incomplete');
		return false;
	}

	$torin_photos = is_array($photos) ? array_values($photos) : array();
	$torin_count = count($torin_photos);
	$torin_text = torin_notify_compose($payload);

	// ── ZERO ────────────────────────────────────────────────────────────────
	// A complete, valid submission (D4-12). Plenty of faults have nothing to
	// photograph, and «it will not power on» is the commonest of them.
	if ($torin_count === 0) {
		return torin_notify_tg_text($secrets, $torin_text);
	}

	// Multipart needs CURLFile. If it is ever absent the enquiry still goes —
	// without its photographs, and saying so in the log. A lead delivered
	// incompletely beats a lead not delivered.
	if (!class_exists('CURLFile')) {
		error_log('torin notify: CURLFile missing, sending the enquiry without photographs');
		return torin_notify_tg_text($secrets, $torin_text);
	}

	// ── ONE ─────────────────────────────────────────────────────────────────
	// The caption budget is 1024 characters and the composed enquiry can reach
	// roughly 1600, because contact-send.php allows a 1024-character fault
	// description on its own. So the caption is clamped unconditionally, and
	// when the clamp ACTUALLY BIT the full text follows as its own message —
	// the owner needs the whole fault description to quote a price, and a
	// description silently losing its last third is the failure that would
	// never be noticed on either end.
	if ($torin_count === 1) {
		$torin_caption = torin_notify_clamp($torin_text, 1024);
		$torin_ok = torin_notify_tg_call($secrets, 'sendPhoto', array(
			'chat_id' => $secrets['telegram_chat_id'],
			'caption' => $torin_caption,
			'photo'   => torin_notify_tg_file($torin_photos[0], 0)
		), false);

		if ($torin_ok) {
			if ($torin_caption !== $torin_text) {
				torin_notify_tg_text($secrets, $torin_text);
			}
			return true;
		}

		// The photo call failed. The enquiry is not lost with it: the text
		// goes on its own. This is the ONLY path on which the one-photo branch
		// makes a second call without the caption having overflowed.
		error_log('torin notify: sendPhoto failed, falling back to text only');
		return torin_notify_tg_text($secrets, $torin_text);
	}

	// ── TWO TO FIVE ─────────────────────────────────────────────────────────
	// Text first. If the group fails, the shop still has the enquiry and the
	// customer's number, which is the part that pays for the repair.
	$torin_msg_ok = torin_notify_tg_text($secrets, $torin_text);

	// The media array names each attachment by the multipart field it will
	// arrive in. Five is the ceiling torin_collect_uploads() enforces, which is
	// inside the method's own two-to-ten range; this function does not re-cap
	// it, because two writers of one limit is how the two start to disagree.
	$torin_media = array();
	$torin_fields = array('chat_id' => $secrets['telegram_chat_id']);
	$torin_i = 0;
	foreach ($torin_photos as $torin_path) {
		$torin_key = 'photo' . $torin_i;
		$torin_media[] = array('type' => 'photo', 'media' => 'attach://' . $torin_key);
		$torin_fields[$torin_key] = torin_notify_tg_file($torin_path, $torin_i);
		$torin_i++;
	}

	$torin_media_json = json_encode($torin_media);
	if ($torin_media_json === false) {
		error_log('torin notify: media descriptor not encodable');
		return $torin_msg_ok;
	}
	$torin_fields['media'] = $torin_media_json;

	$torin_group_ok = torin_notify_tg_call($secrets, 'sendMediaGroup', $torin_fields, false);

	// Either call landing is a delivered enquiry, on the same any-one-wins
	// reading the fan-out below applies to channels.
	return ($torin_msg_ok || $torin_group_ok);
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
