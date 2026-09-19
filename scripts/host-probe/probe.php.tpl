<?php
// ---------------------------------------------------------------------------
// probe.php.tpl — TEMPLATE for the Phase 4 host-capability probe (D4-06).
//
// THIS FILE IS A TEMPLATE AND LIVES UNDER scripts/, NOT UNDER src/.
// That placement is the whole of pitfall P-10's mitigation. `deploy-new.sh`
// with no arguments builds its file list with `find . -type f` over src/
// (scripts/deploy-new.sh:161) — every file under src/ is one no-argument
// deploy away from being published. This file reports the PHP version, the
// loaded extension list, twelve ini values, the sendmail path and the local
// MTA state: that is an environment disclosure, not a diagnostic convenience.
// It is therefore kept where no deploy run can reach it, materialised into
// src/ only for the seconds it takes to upload it by explicit path, and
// removed again by scripts/host-probe/run-probe.sh --read.
//
// Phase 3 already paid for this lesson once: src/phptest.html was deleted in
// plan 03-09 for exactly this reason (STATE.md, Phase 3). This template is the
// structural fix rather than a second cleanup.
//
// LIFECYCLE — three steps, and the disclosure closes itself:
//   1. scripts/host-probe/run-probe.sh --prepare
//   2. scripts/deploy-new.sh <generated-filename>
//   3. scripts/host-probe/run-probe.sh --read <file> <token>
//        -> this single request is also the LAST one the probe ever answers:
//           its final act is to unlink itself (see the bottom of this file),
//           and --read then asserts the 404 automatically.
//
// SELF-DELETION, AND WHY IT IS THE MECHANISM RATHER THAN A CONVENIENCE.
// The first version of this lifecycle ended with "delete it from the server by
// hand, then run --verify-gone". Nothing in this repo can delete a remote file:
// deploy-new.sh only uploads, and reimplementing the FTPS credential handling
// here to issue a DELE would duplicate the one piece of secret handling this
// project has deliberately centralised. So the cleanup step depended on a human
// remembering — for the single file in this phase whose continued existence is
// a live environment disclosure (T-04-01). A disclosure whose closure depends on
// someone remembering is not mitigated, it is scheduled. The probe therefore
// removes itself after answering exactly once, which bounds the exposure window
// to one request no matter what anyone forgets. The unlink result is REPORTED in
// the body rather than assumed, so a failure is legible instead of silent, and
// --read still asserts the 404 independently afterwards.
//
// DIALECT: PHP 5.2-safe, deliberately. This probe has to run BOTH before and
// after the runtime change of plan 04-01 — measuring the old runtime is half
// its job — so array() not [], no closures, no namespaces, no short echo tags.
// Matches the tree-wide rule recorded in src/includes/site-config.php:2.
//
// Body implements 04-RESEARCH.md §"Code Examples" C-1 field for field.
// ---------------------------------------------------------------------------

// Token gate. An unguessable filename alone is not access control — a directory
// listing, a referrer leak or a log line defeats it. A wrong or absent token is
// answered with a bare 404 and nothing else, so the file is indistinguishable
// from an absent one. (This is also why --verify-gone must pass the REAL token:
// a 404 on a tokenless fetch proves nothing, because that is what a live probe
// returns too.)
if (!isset($_GET['k']) || $_GET['k'] !== 'REPLACE_WITH_RANDOM_32_CHARS') {
	header('HTTP/1.0 404 Not Found');
	exit;
}

header('Content-Type: text/plain; charset=utf-8');

echo "version              : " . phpversion()    . "\n";
// sapi decides the ini mechanism, and getting it wrong is a 500 for the whole
// subtree: php_value in .htaccess works ONLY under mod_php, .user.ini works
// ONLY under CGI/FastCGI (04-RESEARCH.md P-5).
echo "sapi                 : " . php_sapi_name() . "\n";

$torin_exts = array('gd', 'exif', 'fileinfo', 'curl', 'openssl', 'mbstring', 'hash', 'ctype', 'filter');
foreach ($torin_exts as $torin_ext) {
	echo str_pad("ext:" . $torin_ext, 21) . ": " . (extension_loaded($torin_ext) ? 'yes' : 'NO') . "\n";
}

// user_ini.filename and user_ini.cache_ttl are read because they are the
// mechanism AND the measurement trap: a limit changed and re-tested inside the
// TTL window measures the old value (P-5).
$torin_inis = array(
	'upload_max_filesize', 'post_max_size', 'max_file_uploads',
	'max_input_vars', 'memory_limit', 'max_execution_time',
	'allow_url_fopen', 'user_ini.filename', 'user_ini.cache_ttl',
	'sendmail_path', 'SMTP', 'smtp_port'
);
foreach ($torin_inis as $torin_ini) {
	echo str_pad("ini:" . $torin_ini, 21) . ": " . var_export(ini_get($torin_ini), true) . "\n";
}

// THE question D4-06 exists to answer: is outbound TCP/443 from PHP allowed at
// all? Telegram's own no-op endpoint — bot id 0, token 0 — needs no credential
// and returns 401 when the network path works. A 401 here is a PASS: it proves
// the request reached Telegram. Only a transport failure is a FAIL.
if (function_exists('curl_init')) {
	$torin_ch = curl_init('https://api.telegram.org/bot0:0/getMe');
	curl_setopt($torin_ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($torin_ch, CURLOPT_TIMEOUT, 10);
	$torin_body = curl_exec($torin_ch);
	echo "outbound:curl443     : " . ($torin_body === false
		? 'FAIL ' . curl_error($torin_ch)
		: 'OK http=' . curl_getinfo($torin_ch, CURLINFO_HTTP_CODE)) . "\n";
	curl_close($torin_ch);
} else {
	// Emitted unconditionally. C-1 prints this line only inside the branch, which
	// would let "cURL absent" reach 04-HOST-CAPABILITIES.md as a MISSING line
	// rather than a recorded NO — and a missing line reads as an unrun probe, not
	// as a measured answer. A blocked notification channel must be legible.
	echo "outbound:curl443     : FAIL ext-curl not loaded\n";
}

echo "sendmail binary      : " . (is_executable('/usr/sbin/sendmail') ? 'yes' : 'NO') . "\n";

// Local MTA reachability for the PHPMailer SMTP leg (D4-11).
$torin_errno = 0;
$torin_errstr = '';
$torin_fp = @fsockopen('localhost', 25, $torin_errno, $torin_errstr, 5);
echo "smtp:localhost:25    : " . ($torin_fp ? 'OPEN' : 'FAIL ' . $torin_errstr) . "\n";
if ($torin_fp) {
	fclose($torin_fp);
}

// Last act: delete this file. See the SELF-DELETION note in the header — this is
// what bounds the disclosure window to a single request rather than to however
// long it takes someone to open FileZilla.
//
// The result is echoed, not swallowed. If the unlink fails (ownership, a
// read-only mount) the body says so in the same breath as the measurement, so
// the failure arrives with the data instead of being discovered later by
// --read's 404 assertion with no explanation attached.
$torin_unlinked = @unlink(__FILE__);
echo "selfdelete           : " . ($torin_unlinked
	? 'OK'
	: 'FAILED — delete this file from public_html/new/ BY HAND, now') . "\n";
