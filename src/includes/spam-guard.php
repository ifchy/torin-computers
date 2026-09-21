<?php
// includes/spam-guard.php — PHP 5.2-safe. Emits nothing on include; it is
// functions and no data, per this tree's include-boundary rule (only
// header.php, footer.php and banner.php produce markup). Two callers:
// contact-form.php signs the render timestamp, contact-send.php verifies it
// and runs the rest of the guard.
//
// WHAT THIS IS. The three layers that stand between a commodity spam bot and
// the notification fan-out: a decoy field, a signed time trap, and a per-
// address throttle. CONTACT-03. Threats T-04-23 (relay-shaped endpoint /
// spam amplification), T-04-25 (a genuine enquiry discarded by a false
// positive), T-04-28 (forged replay of a signed timestamp).
//
// NO CAPTCHA AND NO VISIBLE CHALLENGE OF ANY KIND. This is a decision taken
// under CONTEXT's «Claude's Discretion» grant and recorded here so it is not
// revisited casually (UI-SPEC C-6):
//   1. reCAPTCHA and its peers reintroduce exactly the third-party-cookie
//      problem D4-18 was chosen to avoid — a consent banner in front of a
//      site whose entire premise is getting a worried customer to their
//      problem fast;
//   2. it is a measurable conversion cost on the one form this business
//      depends on;
//   3. it is third-party JavaScript against DESIGN-02's budget;
//   4. the actual threat here is commodity form spam, not a targeted
//      adversary, and three cheap server-side layers handle it.
//
// THE ASYMMETRY THAT SHAPES EVERY DECISION BELOW. A bot that gets through
// costs the owner one ignorable message. A false positive silently discards a
// real customer's enquiry, and — because D4-07 keeps no server-side copy —
// nobody on either end ever learns it happened. The two are not
// comparable, so every ambiguous case in this file resolves towards letting
// the submission through, and every rejection writes a correlation line so a
// lost enquiry is at least recoverable as a FACT even though the enquiry
// itself is not.
//
// NO PHP SESSION IS STARTED HERE OR ANYWHERE ELSE ON THIS SITE, and that is
// the whole reason the time trap is an HMAC rather than a server-side
// counter: a session means a cookie, a cookie re-opens the consent question
// that D4-18 was chosen to close, and a consent banner is the first thing a
// visitor would then meet. The signature is what buys statelessness.
//
// WHY THERE IS NO function_exists() DANCE AROUND THE TWO HASH CALLS, when
// contact-send.php carefully guards mb_strlen(). ext-mbstring is an optional
// build that measurably came and went under this account during 04-01;
// ext-hash stopped being removable in PHP 7.4 and is compiled in
// unconditionally, so hash_hmac() and hash_equals() cannot go missing the
// way mb_strlen() can. A fallback for them would be hand-rolled crypto —
// RESEARCH's «Don't Hand-Roll» table names both calls explicitly — and a
// hand-rolled comparison that leaks timing is worse than no guard at all.
// random_bytes() IS guarded, because it arrived in PHP 7 and this file is
// reachable from the page shell.
//
// COUPLED TO kontakti.html's Cache-Control: no-store (RESEARCH P-11). The
// time trap reads a timestamp minted when the page was rendered. If that
// header is ever dropped, an intermediary hands two visitors the same
// timestamp and this file starts rejecting real people. The header is not
// decoration and not a cache-tuning preference; it is half of this mechanism.

// The system temp directory, checked rather than assumed, or '' when it is
// unusable. EVERY path this file writes is built from it.
//
// This is the same placement upload.php:178-194 chose for a re-encoded
// photograph, and for the same reason: it is outside the document root on
// this host, so nothing written here is fetchable by URL. That matters more
// on this server than on a normal one — .htaccess maps .html to the PHP
// handler, so any file that survives intact somewhere web-reachable is a
// code-execution surface (D4-15), and a throttle record whose name an
// attacker can guess would be one.
//
// Returning '' rather than falling back to a directory under the deployed
// tree is deliberate: a guard that cannot find private storage degrades (and
// says so in the log), it does not quietly relocate its state somewhere the
// web server will serve.
function torin_guard_dir() {
	$torin_dir = rtrim(sys_get_temp_dir(), '/');
	if ($torin_dir === '' || !is_dir($torin_dir) || !is_writable($torin_dir)) {
		return '';
	}
	return $torin_dir;
}

// The server-side signing key, created once and read thereafter.
//
// WHY THIS IS NOT IN site-config.php AND NOT IN THE SECRETS FILE. Both
// callers need it, and one of them — contact-form.php — is included by
// kontakti.html, which is ordinary page chrome. site-config.php:283-310 is
// explicit that contact-send.php is the ONLY file in this tree that may read
// the credentials file, precisely so no include chain from the page shell
// reaches a credential (T-04-26). Handing the form partial that path to keep
// one key in one place would breach the rule this project spent a plan
// establishing. A key that this file creates for itself, in storage this file
// already uses, keeps the credential quarantine intact.
//
// FAILS TO '' RATHER THAN TO A GUESSABLE CONSTANT. An empty key still
// produces a stable, symmetric HMAC — both sides compute it the same way, so
// the time WINDOW still works and still catches the sub-three-second bot,
// which is the layer that actually earns its keep. What is lost is
// unforgeability. Falling back to a hard-coded string would look safer while
// being strictly worse: it would be in this repository, and «forgeable by
// anyone who read the source» is not a better outcome than «forgeable by
// anyone who read the source, logged».
//
// RESIDUAL, RECORDED RATHER THAN HIDDEN: on a shared host whose temp
// directory is genuinely shared between accounts, a neighbouring tenant could
// read this key or pre-create the file. The exclusive-create mode and the
// symlink refusal below close the cheap version of that; the expensive
// version is closed by the host giving each account its own temp directory,
// which is what upload.php already stakes a visitor's photographs on.
function torin_guard_secret() {
	static $torin_cached = null;
	if ($torin_cached !== null) {
		return $torin_cached;
	}
	$torin_cached = '';

	$torin_dir = torin_guard_dir();
	if ($torin_dir === '') {
		error_log('torin guard: no private storage for the signing key — time trap degraded');
		return $torin_cached;
	}
	$torin_path = $torin_dir . '/torin-guard-key';

	// A symlink here means somebody else put it there. Refuse rather than
	// follow it: following would either read a foreign key or write ours
	// wherever the link points.
	if (is_link($torin_path)) {
		error_log('torin guard: signing key path is a symlink — refused');
		return $torin_cached;
	}

	if (is_file($torin_path)) {
		$torin_raw = @file_get_contents($torin_path);
		if (is_string($torin_raw) && strlen(trim($torin_raw)) >= 32) {
			$torin_cached = trim($torin_raw);
			return $torin_cached;
		}
	}

	// MINT ONE. random_bytes() is guarded because it is PHP 7+ and this
	// function is reachable from the page shell; it is also the only
	// acceptable source here, so its absence degrades the trap rather than
	// summoning mt_rand() to stand in for a CSPRNG.
	if (!function_exists('random_bytes')) {
		error_log('torin guard: no CSPRNG for the signing key — time trap degraded');
		return $torin_cached;
	}
	$torin_new = '';
	try {
		$torin_new = bin2hex(random_bytes(32));
	} catch (Throwable $torin_e) {
		// Catching Throwable rather than Exception: random_bytes() signals
		// exhaustion as an Error on PHP 7/8.1 and as a Random\RandomException
		// on 8.2+. The class name parses on every version this tree targets
		// and simply never matches on the older ones.
		$torin_new = '';
	}
	if ($torin_new === '') {
		error_log('torin guard: entropy unavailable for the signing key — time trap degraded');
		return $torin_cached;
	}

	// 'xb' — exclusive create. Fails rather than truncates if the file
	// appeared between the is_file() test above and this line, which is both
	// the ordinary race between two visitors and the interesting one.
	$torin_fh = @fopen($torin_path, 'xb');
	if ($torin_fh === false) {
		$torin_raw = @file_get_contents($torin_path);
		if (is_string($torin_raw) && strlen(trim($torin_raw)) >= 32) {
			$torin_cached = trim($torin_raw);
		} else {
			error_log('torin guard: signing key could not be created — time trap degraded');
		}
		return $torin_cached;
	}
	fwrite($torin_fh, $torin_new);
	fclose($torin_fh);
	@chmod($torin_path, 0600);

	$torin_cached = $torin_new;
	return $torin_cached;
}

// THE DECOY. Returns true when the submission is clean.
//
// The field name lives in exactly two places — the markup in
// contact-form.php and the literal below — and UI-SPEC C-6 fixes it as a
// PLAUSIBLE one («website»), never «honeypot», «hp» or «leaveblank», because
// the name is in the served HTML for anyone to read. If one side of that pair
// is ever renamed, the trap stops firing silently and nothing fails visibly.
//
// A NON-STRING VALUE IS A REJECTION, and it is the one place in this project
// where that is the right answer. contact-send.php's torin_post() treats a
// non-string as absent, because posting `name[]=x` is a shape no rendering of
// our form can produce and the ordinary required-field message is the honest
// response. Here the same reasoning points the other way: nothing legitimate
// posts an ARRAY into a hidden decoy nobody can see. Note that the value is
// never cast to string on that branch — casting an array raises a warning
// which, with display_errors still On in this subtree
// (04-HOST-CAPABILITIES BLOCKER 1), would print into the response before the
// caller's header() call and turn a redirect into «headers already sent».
function torin_verify_honeypot($post) {
	if (!is_array($post) || !isset($post['website'])) {
		return true;
	}
	if (!is_string($post['website'])) {
		return false;
	}
	return trim($post['website']) === '';
}

// The render timestamp, joined to a keyed SHA-256 HMAC of itself.
//
// Canonicalised through (string)(int) so that the form partial passing an
// int and the handler re-signing a numeric string produce byte-identical
// output. Without that, verification would compare a signature over '1758'
// with one over ' 1758' and reject every real visitor.
function torin_sign_timestamp($ts, $secret) {
	$torin_ts = (string) (int) $ts;
	return $torin_ts . '.' . hash_hmac('sha256', $torin_ts, (string) $secret);
}

// Verifies a signed timestamp and its age.
//
// RETURNS A REASON STRING, NOT A BOOLEAN: 'ok', 'malformed', 'forged',
// 'too-fast' or 'stale'. The caller needs the reason for two separate
// purposes — the log line that makes a rejection recoverable as a fact, and
// the branch that decides whether a rejection is a bot (silently absorbed) or
// a human who left a tab open (told the truth and handed back the form).
//
// 'ok' IS THE SUCCESS VALUE RATHER THAN '' ON PURPOSE. Both encode the same
// thing, but they fail differently when somebody later writes
// `if (!torin_verify_timestamp(...))`: with 'ok' that misuse accepts
// everything, with '' it rejects everything. One of those errors loses spam,
// the other loses every customer this form has. Compare with !== 'ok'.
//
// The bounds are the caller's (UI-SPEC C-6 fixes them at roughly three
// seconds and a couple of hours). The lower one is what catches bots — a
// human cannot read seven fields and type an answer in three seconds. The
// upper one only needs to stop replay, and a tight value there rejects the
// real person who opened the form, went to find the model number on the
// underside of the laptop, and came back.
function torin_verify_timestamp($field, $secret, $min_age, $max_age) {
	if (!is_string($field) || $field === '') {
		return 'malformed';
	}
	$torin_parts = explode('.', $field);
	if (count($torin_parts) !== 2) {
		return 'malformed';
	}
	$torin_ts = $torin_parts[0];
	if ($torin_ts === '' || strspn($torin_ts, '0123456789') !== strlen($torin_ts)) {
		return 'malformed';
	}

	// CONSTANT-TIME COMPARISON, never ===. Re-signing the claimed timestamp
	// and comparing the whole field in one call is the same test as comparing
	// the two MACs and is one call harder to get wrong. hash_equals() returns
	// false immediately on a length mismatch, which leaks only the length of
	// a value whose format is public anyway.
	if (!hash_equals(torin_sign_timestamp($torin_ts, $secret), $field)) {
		return 'forged';
	}

	$torin_age = time() - (int) $torin_ts;
	if ($torin_age < (int) $min_age) {
		return 'too-fast';
	}
	if ($torin_age > (int) $max_age) {
		return 'stale';
	}
	return 'ok';
}

// The throttle record for one address, or '' when there is no private
// storage.
//
// THE ADDRESS IS HASHED, NOT STORED. A directory listing of throttle records
// would otherwise be a list of everybody who has written to this shop, which
// is precisely the kind of server-side trace D4-07 was chosen to avoid
// keeping. Joining the server secret means the digest is not a plain
// sha256 of an IPv4 address, which is a space small enough to enumerate.
//
// The digest is the full 64 hex characters, and torin_rate_limit_record()
// relies on that width to recover this function's directory-and-prefix by
// trimming the tail — which is how the two agree on where the files live
// without either of them owning a second copy of the literal.
function torin_rate_limit_path($ip, $secret) {
	$torin_dir = torin_guard_dir();
	if ($torin_dir === '') {
		return '';
	}
	if (!is_string($ip) || $ip === '') {
		$ip = 'unknown';
	}
	return $torin_dir . '/torin-rl-' . hash('sha256', $ip . '|' . (string) $secret);
}

// True when this address may submit. Reads; never writes.
//
// THE CALLER MUST ALSO USE THE ADDRESS PHP OBSERVED, never a forwarded-for
// header. That header is attacker-controlled, so trusting it would let a bot
// mint a fresh identity per request AND let it exhaust somebody else's quota
// by claiming their address.
//
// FAILS OPEN. A throttle that cannot read its own store lets the submission
// through and says so in the log, because the failure it would otherwise
// cause — every enquiry from every visitor refused, invisibly — is the one
// this file exists to prevent.
function torin_rate_limit_ok($ip, $secret, $limits) {
	$torin_window = isset($limits['window']) ? (int) $limits['window'] : 900;
	$torin_max = isset($limits['max']) ? (int) $limits['max'] : 1;

	$torin_path = torin_rate_limit_path($ip, $secret);
	if ($torin_path === '') {
		error_log('torin guard: no private storage for the throttle — rate limit degraded');
		return true;
	}
	if (!is_file($torin_path)) {
		return true;
	}
	$torin_raw = @file_get_contents($torin_path);
	if (!is_string($torin_raw)) {
		return true;
	}

	$torin_now = time();
	$torin_hits = 0;
	$torin_lines = explode("\n", $torin_raw);
	foreach ($torin_lines as $torin_line) {
		$torin_at = (int) trim($torin_line);
		if ($torin_at > 0 && ($torin_now - $torin_at) < $torin_window) {
			$torin_hits++;
		}
	}
	return $torin_hits < $torin_max;
}

// Records one DELIVERED enquiry against this address. Returns true when the
// record was written.
//
// WHY THIS IS A SECOND FUNCTION RATHER THAN A SIDE EFFECT OF THE CHECK, and
// why the caller invokes it only after a channel has actually accepted the
// enquiry. If every POST were recorded, the visitor who mistypes their email,
// reads «проверете отбелязаните полета» and corrects it ten seconds later
// would be throttled by their own first attempt — and so would the visitor
// re-sending after an all-channels-failed page that literally tells them to
// try again. Both are the T-04-25 failure in a new costume: a real enquiry
// refused by our own defence. Counting deliveries instead makes the window
// mean what it says — one enquiry got through, the next one can wait — and
// leaves the cheap rejection paths free to be retried immediately.
//
// It also bounds this store's growth to submissions that passed the decoy,
// the time trap AND validation, which is why a naive flood cannot turn a
// shared temp directory into an inode-exhaustion problem.
function torin_rate_limit_record($ip, $secret, $limits) {
	$torin_window = isset($limits['window']) ? (int) $limits['window'] : 900;

	$torin_path = torin_rate_limit_path($ip, $secret);
	if ($torin_path === '') {
		return false;
	}

	// 'c+b' — create if absent, do not truncate, and hold the existing bytes
	// for the read below. Truncating on open ('w') would drop the history
	// this function exists to extend.
	$torin_fh = @fopen($torin_path, 'c+b');
	if ($torin_fh === false) {
		error_log('torin guard: throttle record could not be opened');
		return false;
	}
	if (!@flock($torin_fh, LOCK_EX)) {
		fclose($torin_fh);
		error_log('torin guard: throttle record could not be locked');
		return false;
	}

	$torin_now = time();
	$torin_raw = stream_get_contents($torin_fh);
	$torin_keep = array();
	if (is_string($torin_raw)) {
		$torin_lines = explode("\n", $torin_raw);
		foreach ($torin_lines as $torin_line) {
			$torin_at = (int) trim($torin_line);
			if ($torin_at > 0 && ($torin_now - $torin_at) < $torin_window) {
				$torin_keep[] = $torin_at;
			}
		}
	}
	// Bounded on the way out as well as by the window: a clock that jumps
	// backwards would otherwise let this list grow without limit.
	if (count($torin_keep) > 32) {
		$torin_keep = array_slice($torin_keep, -32);
	}
	$torin_keep[] = $torin_now;

	rewind($torin_fh);
	ftruncate($torin_fh, 0);
	fwrite($torin_fh, implode("\n", $torin_keep) . "\n");
	fflush($torin_fh);
	flock($torin_fh, LOCK_UN);
	fclose($torin_fh);
	@chmod($torin_path, 0600);

	// OCCASIONAL SWEEP. These records are tiny and the system reaper clears
	// the directory eventually, but «eventually» on a shared host is a policy
	// nobody here controls, and an abandoned record is a fact about a visitor
	// that D4-07 promises not to keep. One pass in twenty, over this file's
	// own prefix only, never over the directory at large.
	//
	// The prefix is recovered from the path just used by trimming the 64-
	// character digest torin_rate_limit_path() appends, so the two functions
	// cannot disagree about which files are ours.
	if (mt_rand(1, 20) === 1) {
		$torin_prefix = substr($torin_path, 0, strlen($torin_path) - 64);
		$torin_old = @glob($torin_prefix . '*');
		if (is_array($torin_old)) {
			foreach ($torin_old as $torin_file) {
				if (is_file($torin_file) && !is_link($torin_file) &&
					(int) @filemtime($torin_file) > 0 &&
					($torin_now - (int) @filemtime($torin_file)) > ($torin_window * 4)) {
					@unlink($torin_file);
				}
			}
		}
	}

	return true;
}
?>
