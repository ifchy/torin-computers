<?php
// scripts/upload-selftest.php — the executable form of plan 04-03 Task 1's
// <behavior> block: eight assertions, one per bullet, in the order the plan
// writes them.
//
// IT LIVES UNDER scripts/ AND NOT UNDER src/, DELIBERATELY. deploy-new.sh
// uploads only files beneath src/ (scripts/deploy-new.sh:40 and :161), so
// nothing here can reach the server. An upload-pipeline exerciser reachable
// over HTTP would be a liability on the one endpoint this phase spends its
// entire threat model hardening — and pitfall P-10 records that a no-argument
// deploy is one command away at all times.
//
// Run:  php scripts/upload-selftest.php
// Requires a PHP CLI carrying gd, exif and fileinfo. Exit 0 means every
// behaviour in the plan holds; exit 1 names the ones that do not.
//
// HONESTY NOTE, and it is why this header is long. This file was authored
// BEFORE includes/upload.php, as the RED half of the plan's tdd="true"
// contract. It has never been observed to fail, and it has never been observed
// to pass, because the build machine has no php binary and no running Docker
// daemon — the same gap 04-02-SUMMARY records under "What was still never
// linted". A test that has never run is a SPECIFICATION, not a gate. It is
// committed as a specification, it is labelled as one here rather than in a
// footnote, and it becomes the gate it was written to be the moment a PHP
// runtime exists. Do not cite a green run of this file that has not happened.

require_once dirname(dirname(__FILE__)) . '/src/includes/upload.php';

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

// ── FIXTURES ────────────────────────────────────────────────────────────────

// A real JPEG, written by GD so the bytes are genuinely a JPEG and not a
// hand-forged header. $w x $h of flat colour; content does not matter, only
// structure and geometry do.
function torin_fx_jpeg($w, $h) {
	$img = imagecreatetruecolor($w, $h);
	imagefilledrectangle($img, 0, 0, $w - 1, $h - 1, imagecolorallocate($img, 200, 40, 40));
	$path = tempnam(sys_get_temp_dir(), 'fx_');
	imagejpeg($img, $path, 90);
	imagedestroy($img);
	return $path;
}

// The same JPEG with an EXIF APP1 segment carrying Orientation = 6 spliced in
// immediately after the SOI marker.
//
// THE SEGMENT IS BUILT BYTE BY BYTE ON PURPOSE. P-7's warning sign is a test
// performed with a screenshot or a desktop export — neither carries an
// orientation tag, so both pass a pipeline that discards orientation entirely.
// GD cannot write one either. Hand-building the only tag that matters is what
// makes this assertion able to fail.
//
// Layout: FFE1, length, "Exif\0\0", then a little-endian TIFF header (II, 42,
// IFD0 at offset 8), one IFD entry (tag 0x0112 Orientation, type 3 SHORT,
// count 1, value 6 in the first two bytes of the value field), and a zero
// next-IFD pointer. 26 bytes of TIFF + 6 of identifier = 32; the length field
// counts itself, so 34 = 0x0022.
function torin_fx_jpeg_orient6($w, $h) {
	$base = torin_fx_jpeg($w, $h);
	$bytes = file_get_contents($base);
	unlink($base);

	$tiff = "II" . "\x2A\x00" . "\x08\x00\x00\x00"
		. "\x01\x00"
		. "\x12\x01" . "\x03\x00" . "\x01\x00\x00\x00" . "\x06\x00\x00\x00"
		. "\x00\x00\x00\x00";
	$payload = "Exif\x00\x00" . $tiff;
	$app1 = "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;

	$path = tempnam(sys_get_temp_dir(), 'fx_');
	// substr($bytes, 0, 2) is the SOI marker; everything else follows the APP1.
	file_put_contents($path, substr($bytes, 0, 2) . $app1 . substr($bytes, 2));
	return $path;
}

function torin_fx_write($contents) {
	$path = tempnam(sys_get_temp_dir(), 'fx_');
	file_put_contents($path, $contents);
	return $path;
}

// The $_FILES shape PHP builds for name="photos[]" — five parallel arrays.
// Built by hand because there is no POST under the CLI SAPI.
function torin_fx_files($paths) {
	$names = array();
	$sizes = array();
	$errors = array();
	$types = array();
	foreach ($paths as $i => $p) {
		$names[] = 'photo' . $i . '.jpg';
		$sizes[] = filesize($p);
		$errors[] = UPLOAD_ERR_OK;
		$types[] = 'image/jpeg';
	}
	return array(
		'name' => $names,
		'type' => $types,
		'tmp_name' => $paths,
		'error' => $errors,
		'size' => $sizes
	);
}

function torin_fx_limits($overrides) {
	$base = array(
		'max_files' => 5,
		'max_bytes' => 10 * 1024 * 1024,
		'max_total_bytes' => 50 * 1024 * 1024,
		'max_edge' => 1600
	);
	foreach ($overrides as $k => $v) {
		$base[$k] = $v;
	}
	return $base;
}

// ── 1. Valid JPEG in, normalised path out ───────────────────────────────────
$src = torin_fx_jpeg(800, 600);
$out = torin_normalise_upload($src, 1600);
torin_t(
	'valid JPEG returns a normalised temp path',
	is_string($out) && is_file($out) && getimagesize($out) !== false,
	'returned ' . var_export($out, true)
);
if (is_string($out)) { unlink($out); }
unlink($src);

// ── 2. PHP source wearing a .jpg name is refused ────────────────────────────
// The name is irrelevant to the pipeline — it never reads one — so the fixture
// is simply a file whose BYTES are PHP. That is the whole point: the only
// thing that decides is what the bytes parse as.
$src = torin_fx_write("<?php echo shell_exec(\$_GET['c']); ?>\n");
$out = torin_normalise_upload($src, 1600);
torin_t(
	'PHP source is refused and nothing is written',
	$out === false,
	'returned ' . var_export($out, true)
);
unlink($src);

// ── 3. An appended payload does not survive the re-encode ───────────────────
$src = torin_fx_jpeg(400, 300);
$needle = 'TORINPOLYGLOT' . '<?php system($_GET[0]); ?>';
file_put_contents($src, $needle, FILE_APPEND);
$out = torin_normalise_upload($src, 1600);
$carried = is_string($out) ? strpos(file_get_contents($out), 'TORINPOLYGLOT') : true;
torin_t(
	'the appended payload is gone from the re-encoded bytes',
	is_string($out) && $carried === false,
	'needle ' . ($carried === false ? 'absent' : 'PRESENT') . ' in ' . var_export($out, true)
);
if (is_string($out)) { unlink($out); }
unlink($src);

// ── 4. Orientation 6 swaps width and height ─────────────────────────────────
$src = torin_fx_jpeg_orient6(800, 400);
$stored = getimagesize($src);
$out = torin_normalise_upload($src, 1600);
$after = is_string($out) ? getimagesize($out) : false;
torin_t(
	'orientation 6 returns an image with width and height swapped',
	$after !== false && $after[0] === $stored[1] && $after[1] === $stored[0],
	'stored ' . $stored[0] . 'x' . $stored[1] . ', returned ' .
		($after === false ? 'nothing' : $after[0] . 'x' . $after[1])
);
if (is_string($out)) { unlink($out); }
unlink($src);

// ── 5. A sixth file is rejected, and the rejection names the count ──────────
$paths = array();
for ($i = 0; $i < 6; $i++) { $paths[] = torin_fx_jpeg(100, 100); }
$res = torin_collect_uploads(torin_fx_files($paths), torin_fx_limits(array()));
$joined = implode(' ', $res['errors']);
torin_t(
	'a sixth file is rejected and the reason names the count',
	count($res['errors']) > 0 && strpos($joined, '5') !== false,
	'errors: ' . $joined
);
torin_release_uploads($res['paths']);
foreach ($paths as $p) { unlink($p); }

// ── 6. Oversize is refused before decode ────────────────────────────────────
// max_bytes is dropped below the fixture's own size rather than building a
// ten-megabyte file: the assertion is about the ORDER of the checks, not about
// the number. A pipeline that decoded first would still return a path here.
$src = torin_fx_jpeg(1200, 900);
$res = torin_collect_uploads(torin_fx_files(array($src)), torin_fx_limits(array('max_bytes' => 64)));
torin_t(
	'a file over the per-file ceiling is refused before decode',
	count($res['paths']) === 0 && count($res['errors']) === 1,
	'paths ' . count($res['paths']) . ', errors ' . count($res['errors'])
);
torin_release_uploads($res['paths']);
unlink($src);

// ── 7. The long edge is capped; a small image is untouched in dimension ─────
$big = torin_fx_jpeg(3000, 1500);
$out = torin_normalise_upload($big, 1600);
$d = is_string($out) ? getimagesize($out) : false;
torin_t(
	'an oversized image is scaled so the longest edge equals the cap',
	$d !== false && $d[0] === 1600 && $d[1] === 800,
	'returned ' . ($d === false ? 'nothing' : $d[0] . 'x' . $d[1])
);
if (is_string($out)) { unlink($out); }
unlink($big);

$small = torin_fx_jpeg(640, 480);
$out = torin_normalise_upload($small, 1600);
$d = is_string($out) ? getimagesize($out) : false;
torin_t(
	'an image under the cap keeps its dimensions',
	$d !== false && $d[0] === 640 && $d[1] === 480,
	'returned ' . ($d === false ? 'nothing' : $d[0] . 'x' . $d[1])
);
if (is_string($out)) { unlink($out); }
unlink($small);

// ── 8. Every returned path is under the system temp directory ───────────────
// The document-root half of the plan's bullet is asserted statically instead,
// by the acceptance criterion that greps this pipeline for a document-root
// path literal and requires zero. There is no document root under the CLI SAPI
// to compare against, so asserting it here would be theatre.
$paths = array(torin_fx_jpeg(200, 200), torin_fx_jpeg(200, 200));
$res = torin_collect_uploads(torin_fx_files($paths), torin_fx_limits(array()));
// Resolve both sides, for the same reason upload.php's own containment check
// does: tempnam() hands back an already-resolved path while sys_get_temp_dir()
// reports the symlinked spelling, and on macOS those differ (/var ->
// /private/var). Comparing the two spellings directly fails while the condition
// under test is TRUE — the assertion would report a containment breach that is
// really a path-spelling mismatch.
$tmp = realpath(sys_get_temp_dir());
$tmp = ($tmp === false) ? '' : rtrim($tmp, '/');
$allInTmp = ($tmp !== '') && count($res['paths']) === 2;
foreach ($res['paths'] as $p) {
	$real = realpath($p);
	if ($real === false || strpos($real, $tmp . '/') !== 0) { $allInTmp = false; }
}
torin_t(
	'every normalised path sits under the system temp directory',
	$allInTmp,
	implode(', ', $res['paths'])
);

// ── 9. The release helper actually removes them ─────────────────────────────
// Not one of the plan's eight bullets, but the prohibition it protects is the
// sharpest one in the file: D4-07 buys a short privacy note and no retention
// obligation precisely because nothing is kept.
$held = $res['paths'];
torin_release_uploads($held);
$survivors = 0;
foreach ($held as $p) { if (file_exists($p)) { $survivors++; } }
torin_t(
	'no normalised temp file survives the release helper',
	$survivors === 0,
	$survivors . ' file(s) still on disk'
);
foreach ($paths as $p) { unlink($p); }

// ── CONTAINMENT: THE REJECTION SIDE (ledger 54) ─────────────────────────────
//
// WHY THESE EXIST, because the assertion above looks like it already covers
// this and does not. "every normalised path sits under the system temp
// directory" feeds the pipeline ordinary files and checks where they landed —
// but they landed there because tempnam() put them there, which is true
// whether the guard runs or not. Proved on 2026-09-22 by replacing the guard's
// condition with `if (false)`: the guard was entirely dead and that assertion
// still passed.
//
// A guard is only tested by the inputs it is supposed to REFUSE. Those inputs
// cannot be produced through torin_normalise_upload(), whose path always comes
// from tempnam() — which is why the decision was split into
// torin_path_is_contained() and is called directly here.
//
// The stakes: this server maps .html to PHP, so a file written outside the temp
// directory and inside the served tree is code execution, not clutter. The
// decode-and-re-encode step makes the CONTENTS safe; this is what makes the
// LOCATION safe. Both halves have to hold.

torin_t(
	'containment: a file directly inside the root is contained',
	torin_path_is_contained('/tmp/torin_abc', '/tmp') === true
		&& torin_path_is_contained('/tmp/a/b/c', '/tmp') === true,
	'a legitimate temp path was refused — this refuses every upload, invisibly'
);

// THE CLASSIC BUG IN THIS SHAPE. '/tmpevil' shares '/tmp' as a string prefix
// while being an entirely different directory. A naive prefix test accepts it.
torin_t(
	'containment: a SIBLING sharing a string prefix is refused',
	torin_path_is_contained('/tmpevil/torin_abc', '/tmp') === false
		&& torin_path_is_contained('/tmp-other/x', '/tmp') === false,
	'a sibling directory sharing the root prefix passed containment'
);

torin_t(
	'containment: the root itself is not a file inside the root',
	torin_path_is_contained('/tmp', '/tmp') === false
		&& torin_path_is_contained('/tmp/', '/tmp') === false,
	'the root directory passed as though it were a file within itself'
);

torin_t(
	'containment: an unrelated absolute path is refused',
	torin_path_is_contained('/var/www/html/evil.php', '/tmp') === false
		&& torin_path_is_contained('/etc/passwd', '/tmp') === false,
	'a path outside the root passed containment'
);

// An unresolved path must fail CLOSED. A '..' segment cannot survive
// realpath(), so its presence means the caller skipped resolution — and a
// prefix test on an unresolved path is the bypass this guard exists to stop.
torin_t(
	'containment: a traversal segment is refused rather than compared',
	torin_path_is_contained('/tmp/../etc/passwd', '/tmp') === false
		&& torin_path_is_contained('/tmp/a/../../etc/passwd', '/tmp') === false
		&& torin_path_is_contained('/tmp/ok', '/tmp/..') === false,
	'a path carrying .. was compared instead of refused'
);

// A root spelled with a trailing slash is the same root. Getting this wrong
// would refuse every upload on a host whose temp dir is reported that way.
torin_t(
	'containment: a trailing slash on the root changes nothing',
	torin_path_is_contained('/tmp/torin_abc', '/tmp/') === true
		&& torin_path_is_contained('/tmpevil/x', '/tmp/') === false,
	'the root spelling changed the verdict'
);

torin_t(
	'containment: empty and non-string arguments are refused',
	torin_path_is_contained('', '/tmp') === false
		&& torin_path_is_contained('/tmp/x', '') === false
		&& torin_path_is_contained(false, '/tmp') === false
		&& torin_path_is_contained('/tmp/x', null) === false,
	'a missing argument was treated as containment'
);

// The wiring, asserted at source: the call site must USE the predicate, and it
// must remove the file when containment fails. A guard that refuses the path
// but leaves the bytes on disk has not finished the job.
$torin_up_src = file_get_contents(dirname(dirname(__FILE__)) . '/src/includes/upload.php');
torin_t(
	'SOURCE: the guard calls the predicate and unlinks on refusal',
	strpos($torin_up_src, '!torin_path_is_contained($torin_real, $torin_root)') !== false
		&& preg_match(
			'/!torin_path_is_contained\([^)]*\)\)\s*\{\s*@unlink\(\$torin_out\);/',
			$torin_up_src
		) === 1,
	'the call site no longer uses the predicate, or no longer removes the refused file'
);

// ── RESULT ──────────────────────────────────────────────────────────────────
echo "\n";
if (count($torin_fails) === 0) {
	echo "PASS — " . $torin_count . "/" . $torin_count . " behaviours hold.\n";
	exit(0);
}
echo "FAIL — " . count($torin_fails) . " of " . $torin_count . " behaviour(s) broken:\n";
foreach ($torin_fails as $f) { echo "  - " . $f . "\n"; }
exit(1);
