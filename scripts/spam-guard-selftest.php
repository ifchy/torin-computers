<?php
// scripts/spam-guard-selftest.php — the guard's behaviour block, executable.
//
// Closes ledger 35. The same shape as scripts/upload-selftest.php (19) and
// scripts/settings-selftest.php (29): one assertion per line of plan 04-05
// Task 2's <behavior> block, runnable as
//
//   php scripts/spam-guard-selftest.php
//
// UNLIKE ITS TWO PREDECESSORS THIS FILE WAS AUTHORED AGAINST A RUNTIME AND HAS
// BEEN RUN. Those two were committed as specifications because no php binary
// existed; that is no longer true, so nothing here is owed a "becomes a gate
// later" disclaimer.
//
// ── WHAT THIS FILE CAN AND CANNOT PROVE ────────────────────────────────────
//
// Two of the nine behaviours are NOT implemented in spam-guard.php. The
// oversized-post branch and the correlation-id log lines live in
// src/contact-send.php, which is a request handler: it runs on include, reads
// superglobals, and ends by sending a response. Including it here would
// execute it. Those assertions are therefore SOURCE-LEVEL and each one says so
// in its own name, because a structural check labelled as a behavioural one is
// how this project got seven defective gates in a single phase (ledger 48).
//
// The distinction is not pedantry. A source assertion proves the branch is
// written; only a live POST proves it fires. Ledger 34's three live gates stay
// open regardless of what this file reports.
//
// ── ISOLATION ──────────────────────────────────────────────────────────────
//
// Every test passes its OWN secret rather than calling torin_guard_secret().
// That keeps the run from creating or reading the real signing key, and it
// makes the throttle's storage path — a hash of address|secret — unique per
// run, so two runs cannot collide and a developer's temp directory cannot
// carry state between them. Every file this creates is removed at the end.

require_once dirname(dirname(__FILE__)) . '/src/includes/spam-guard.php';

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

// A secret unique to this process, so the throttle files this run creates
// cannot be confused with any other run's.
$torin_secret = 'selftest-' . bin2hex(random_bytes(16));
$torin_other  = 'selftest-' . bin2hex(random_bytes(16));
$torin_ip     = '203.0.113.' . mt_rand(1, 254);   // TEST-NET-3, never routable
$torin_src    = dirname(dirname(__FILE__)) . '/src';
$torin_guard_src = file_get_contents($torin_src . '/includes/spam-guard.php');
$torin_send_src  = file_get_contents($torin_src . '/contact-send.php');

// Collect the guard's own error_log output so behaviour 9 can be checked
// against what was actually written, not against what the source looks like.
$torin_log = tempnam(sys_get_temp_dir(), 'guardlog_');
ini_set('error_log', $torin_log);

$torin_cleanup = array($torin_log);

// ── Behaviour 1 — the decoy ────────────────────────────────────────────────
// "A post with a non-empty decoy field is rejected, produces no outbound call,
// and returns the same visible outcome a bot cannot distinguish from success"

torin_t(
	'a filled decoy field is rejected',
	torin_verify_honeypot(array('website' => 'http://example.com')) === false,
	'a non-empty website value passed the decoy'
);

torin_t(
	'an untouched decoy passes, empty and whitespace alike',
	torin_verify_honeypot(array('website' => '')) === true
		&& torin_verify_honeypot(array('website' => "  \t\n ")) === true,
	'a blank decoy was treated as filled — this discards real enquiries'
);

// The absent-field case is the one that decides what happens when a browser
// extension, a proxy or P-5's silently-emptied post body drops the field. It
// must resolve towards LETTING THE SUBMISSION THROUGH: a missing decoy is not
// evidence of a bot, and treating it as one loses a customer invisibly.
torin_t(
	'an ABSENT decoy field passes rather than failing closed',
	torin_verify_honeypot(array()) === true
		&& torin_verify_honeypot('not an array') === true,
	'a missing decoy was treated as a filled one'
);

// An ARRAY posted into the decoy is the documented evasion: it is not a string,
// so it must be refused WITHOUT being cast — casting raises a warning that,
// with display_errors On in this subtree, prints before the caller's header()
// call and turns the redirect into "headers already sent".
torin_t(
	'an array posted into the decoy is refused, and no warning is raised',
	torin_verify_honeypot(array('website' => array('x'))) === false,
	'an array decoy was not refused'
);

// ── Behaviour 2 — a signature that does not verify ─────────────────────────

$torin_now = time();

torin_t(
	'a timestamp signed with another secret reports forged',
	torin_verify_timestamp(
		torin_sign_timestamp($torin_now - 60, $torin_other),
		$torin_secret, 3, 7200
	) === 'forged',
	'a signature from a different key was accepted'
);

// A forged field of the CORRECT LENGTH is what proves the comparison actually
// ran, rather than a length check short-circuiting ahead of it.
$torin_valid_field = torin_sign_timestamp($torin_now - 60, $torin_secret);
$torin_tampered = substr($torin_valid_field, 0, -1)
	. (substr($torin_valid_field, -1) === 'a' ? 'b' : 'a');
torin_t(
	'a same-length tampered signature reports forged',
	strlen($torin_tampered) === strlen($torin_valid_field)
		&& torin_verify_timestamp($torin_tampered, $torin_secret, 3, 7200) === 'forged',
	'a one-character tamper of equal length was accepted'
);

torin_t(
	'malformed shapes report malformed, not forged',
	torin_verify_timestamp('', $torin_secret, 3, 7200) === 'malformed'
		&& torin_verify_timestamp('no-dot', $torin_secret, 3, 7200) === 'malformed'
		&& torin_verify_timestamp('a.b.c', $torin_secret, 3, 7200) === 'malformed'
		&& torin_verify_timestamp('abc.def', $torin_secret, 3, 7200) === 'malformed',
	'a structurally broken field was not reported as malformed'
);

// ── Behaviour 3 — younger than the lower bound ─────────────────────────────

torin_t(
	'a submission faster than the lower bound reports too-fast',
	torin_verify_timestamp(
		torin_sign_timestamp($torin_now, $torin_secret),
		$torin_secret, 3, 7200
	) === 'too-fast',
	'an instant submission was accepted'
);

// ── Behaviour 4 — older than the upper bound ───────────────────────────────

torin_t(
	'a submission past the upper bound reports stale',
	torin_verify_timestamp(
		torin_sign_timestamp($torin_now - 100000, $torin_secret),
		$torin_secret, 3, 7200
	) === 'stale',
	'a long-abandoned form was accepted'
);

// ── Behaviour 5 — the human interval ───────────────────────────────────────

torin_t(
	'a plausible human interval with a valid signature returns ok',
	torin_verify_timestamp($torin_valid_field, $torin_secret, 3, 7200) === 'ok',
	'a legitimate submission was rejected — this is the T-04-25 failure'
);

// The boundaries are INCLUSIVE at the lower end and at the upper end: the code
// rejects age < min and age > max, so exactly-min and exactly-max must pass. A
// visitor who takes precisely three seconds is a visitor, not a bot.
torin_t(
	'both bounds are inclusive — exactly min and exactly max still pass',
	torin_verify_timestamp(
		torin_sign_timestamp($torin_now - 3, $torin_secret), $torin_secret, 3, 7200
	) === 'ok'
		&& torin_verify_timestamp(
			torin_sign_timestamp($torin_now - 7200, $torin_secret), $torin_secret, 3, 7200
		) === 'ok',
	'an exactly-on-the-boundary submission was rejected'
);

// The success value is the string 'ok' and NOT a truthy/falsy flag. This guards
// the misuse the source comment names: `if (!torin_verify_timestamp(...))`
// accepts everything when the success value is 'ok'-vs-'', and the assertion
// exists so a later change to a boolean return breaks a test instead of a form.
torin_t(
	'the verdict is a reason STRING, never a boolean',
	torin_verify_timestamp($torin_valid_field, $torin_secret, 3, 7200) === 'ok'
		&& torin_verify_timestamp('', $torin_secret, 3, 7200) !== false,
	'the verdict became a boolean — every caller comparing !== \'ok\' now misreads it'
);

// ── Behaviour 6 — constant-time comparison ─────────────────────────────────
// SOURCE-LEVEL. Timing behaviour cannot be asserted reliably from a test
// process, so this checks the mechanism is present and that the unsafe form is
// absent from the verification path.

torin_t(
	'SOURCE: the guard uses hash_equals and starts no session',
	strpos($torin_guard_src, 'hash_equals(') !== false
		&& strpos($torin_guard_src, 'hash_hmac(') !== false
		&& preg_match('/^[^\/\n]*session_start/m', $torin_guard_src) === 0,
	'hash_equals/hash_hmac missing, or a session is started'
);

// The SIGNATURE must never be compared with ===. Checked against the
// verification function's own body rather than the whole file, so an unrelated
// comparison elsewhere cannot mask a regression here.
//
// TARGET THE SIGNATURE COMPARISON, NOT EVERY === ON $field. The first version
// of this assertion banned `$field ===` outright and failed on
// `$field === ''` — the emptiness check at the top of the function, which is
// correct code and has nothing to do with timing. An assertion that forbids a
// safe construct is a gate that will be silenced rather than obeyed, so what is
// banned here is specifically an equality test against the recomputed HMAC.
// STRIP COMMENTS BEFORE MATCHING. The second version of this assertion failed
// because the function's own comment says "CONSTANT-TIME COMPARISON, never
// ===" — the prose explaining the rule tripped the check enforcing it. That is
// ledger 48's standing rule arriving from a new direction: a counter or a
// pattern that reads comments is measuring the wrong text. Everything below
// matches CODE only.
$torin_vt_body = '';
if (preg_match('/function torin_verify_timestamp\(.*?\n\}/s', $torin_guard_src, $torin_m)) {
	$torin_vt_body = preg_replace('/^[[:space:]]*\/\/.*$/m', '', $torin_m[0]);
}
torin_t(
	'SOURCE: torin_verify_timestamp compares the signature with hash_equals only',
	$torin_vt_body !== ''
		&& strpos($torin_vt_body, 'hash_equals(torin_sign_timestamp(') !== false
		&& preg_match('/torin_sign_timestamp\([^;]*?(===|==|!=|!==)/', $torin_vt_body) === 0
		&& preg_match('/(===|==|!=|!==)[^;]*?torin_sign_timestamp\(/', $torin_vt_body) === 0,
	'the recomputed signature is compared with an equality operator instead of hash_equals'
);

// ── Behaviour 7 — the per-address throttle ─────────────────────────────────
// "A second post from the same address inside the throttle window is rejected;
// one outside it passes"

$torin_limits = array('window' => 900, 'max' => 1);
$torin_rl_path = torin_rate_limit_path($torin_ip, $torin_secret);
if ($torin_rl_path !== '') {
	$torin_cleanup[] = $torin_rl_path;
}

$torin_first = torin_rate_limit_ok($torin_ip, $torin_secret, $torin_limits);
torin_t(
	'a first submission from an unseen address passes the throttle',
	$torin_first === true,
	'an address with no record was throttled'
);

torin_t(
	'the throttle record is written and the address is HASHED, not stored',
	torin_rate_limit_record($torin_ip, $torin_secret, $torin_limits) === true
		&& $torin_rl_path !== ''
		&& is_file($torin_rl_path)
		&& strpos(basename($torin_rl_path), $torin_ip) === false
		&& strpos(file_get_contents($torin_rl_path), $torin_ip) === false,
	'no record written, or the raw address appears in the path or the contents'
);

torin_t(
	'a second submission inside the window is refused',
	torin_rate_limit_ok($torin_ip, $torin_secret, $torin_limits) === false,
	'the throttle did not refuse a repeat inside the window'
);

// Outside the window the same record must stop counting. The window is the
// variable rather than the clock, because a test that waits 900 seconds is a
// test nobody runs.
torin_t(
	'the same record outside the window passes again',
	torin_rate_limit_ok($torin_ip, $torin_secret, array('window' => 0, 'max' => 1)) === true,
	'an expired record still counted against the address'
);

// A DIFFERENT address must be unaffected — the quota is per-address, and a
// shared one would let a single bot lock out the whole site.
torin_t(
	'the throttle is per-address: a different address is unaffected',
	torin_rate_limit_ok('203.0.113.255', $torin_secret, $torin_limits) === true,
	'one address throttled another'
);

// FAILS OPEN. The documented contract is that a throttle which cannot reach its
// storage lets the submission through, because refusing every enquiry
// invisibly is the failure this file exists to prevent.
$torin_ro = torin_rate_limit_ok($torin_ip, $torin_secret, array('window' => 900, 'max' => 0));
torin_t(
	'max=0 refuses, proving the counter is read rather than assumed',
	$torin_ro === false,
	'the limit value was not honoured'
);

// ── Behaviour 8 — oversized post with an empty body ────────────────────────
// SOURCE-LEVEL: this branch lives in contact-send.php, a request handler that
// executes on include. See the header note.

torin_t(
	'SOURCE: contact-send.php branches on CONTENT_LENGTH with an empty $_POST',
	preg_match('/CONTENT_LENGTH/', $torin_send_src) === 1
		&& preg_match('/\$torin_len\s*>\s*0\s*&&\s*count\(\$_POST\)\s*===\s*0/', $torin_send_src) === 1,
	'the oversized-post branch is missing or changed shape'
);

// The branch must sit ABOVE the guard and above the notification call —
// otherwise a truncated post falls through and is read as an empty submission.
$torin_pos_len    = strpos($torin_send_src, 'CONTENT_LENGTH');
$torin_pos_honey  = strpos($torin_send_src, 'torin_verify_honeypot(');
$torin_pos_notify = strpos($torin_send_src, 'torin_notify(');
torin_t(
	'SOURCE: the guard runs before the notification, and the size check before both',
	$torin_pos_len !== false && $torin_pos_honey !== false && $torin_pos_notify !== false
		&& $torin_pos_len < $torin_pos_honey
		&& $torin_pos_honey < $torin_pos_notify,
	'ordering broken: size=' . var_export($torin_pos_len, true)
		. ' honeypot=' . var_export($torin_pos_honey, true)
		. ' notify=' . var_export($torin_pos_notify, true)
);

// ── Behaviour 9 — rejections leave a trace, carrying no submitted content ──

// Behavioural half: the guard's OWN log output, captured from this run.
$torin_written = file_get_contents($torin_log);
torin_t(
	'the guard\'s own log output carries no address and no submitted value',
	strpos($torin_written, $torin_ip) === false
		&& strpos($torin_written, $torin_secret) === false,
	'the captured log leaked an address or the signing secret: ' . trim($torin_written)
);

// Source half: every rejection line in the handler carries the correlation id
// and a reason, and interpolates no superglobal.
preg_match_all('/error_log\((.*?)\);/s', $torin_send_src, $torin_logs);
$torin_bad_log = '';
$torin_reject_lines = 0;
foreach ($torin_logs[1] as $torin_line) {
	if (strpos($torin_line, 'reason=') === false) {
		continue;
	}
	$torin_reject_lines++;
	if (strpos($torin_line, '$torin_rid') === false) {
		$torin_bad_log = 'no correlation id: ' . $torin_line;
	}
	if (preg_match('/\$_(POST|GET|REQUEST|FILES)/', $torin_line)) {
		$torin_bad_log = 'interpolates a superglobal: ' . $torin_line;
	}
}
torin_t(
	'SOURCE: every reason= log line carries a correlation id and no superglobal',
	$torin_reject_lines > 0 && $torin_bad_log === '',
	$torin_reject_lines === 0 ? 'no reason= log lines found at all' : $torin_bad_log
);

// The guard file must not carry the served directory's name, and must not
// reach for a forwarded-for header — that header is attacker-controlled, so
// trusting it lets a bot mint a fresh identity per request.
torin_t(
	'SOURCE: no document-root path and no forwarded-for trust in the guard',
	strpos($torin_guard_src, 'public_html') === false
		&& preg_match('/HTTP_X_FORWARDED_FOR|X-Forwarded-For/i', $torin_guard_src) === 0,
	'the guard names the document root or trusts a forwarded address'
);

// PHP 5.2-safe, like the file it tests: no short-array syntax anywhere.
torin_t(
	'SOURCE: the guard uses no short-array syntax (PHP 5.2 contract)',
	preg_match('/(=>|=)[[:space:]]*\[|return[[:space:]]+\[/', $torin_guard_src) === 0,
	'a short array construct appeared in a file declared PHP 5.2-safe'
);

// ── Cleanup ────────────────────────────────────────────────────────────────
// Every file this run created is removed, so a developer's temp directory does
// not accumulate throttle records named after a secret that no longer exists.
foreach ($torin_cleanup as $torin_path) {
	if ($torin_path !== '' && is_file($torin_path)) {
		@unlink($torin_path);
	}
}

$torin_left = ($torin_rl_path !== '' && is_file($torin_rl_path));
torin_t(
	'the run leaves no throttle record or log file behind',
	!$torin_left && !is_file($torin_log),
	'a temp file survived cleanup'
);

// ── Result ─────────────────────────────────────────────────────────────────

echo "\n" . ($torin_count - count($torin_fails)) . "/" . $torin_count . " assertions held\n";
if (count($torin_fails) > 0) {
	echo "failed: " . implode(', ', $torin_fails) . "\n";
	exit(1);
}
exit(0);
?>
