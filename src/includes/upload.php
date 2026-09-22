<?php
// includes/upload.php — PHP 5.2-safe (array() only, named functions only, no
// namespaces, no short echo tags, tabs). Emits nothing on include: function
// definitions and no top-level output, exactly like icons.php, notify.php and
// asset-version.php.
//
// THE STAKES HERE ARE NOT THE USUAL UPLOAD STAKES. This server maps .html to
// PHP (src/.htaccess, and 04-HOST-CAPABILITIES records the handler cutover
// that made it so). A file that survives intact anywhere the web server will
// serve it is therefore CODE EXECUTION, not a stray file. Nothing below ever
// writes outside the system temp directory — not a subdirectory of the served
// tree, not a temp folder inside it, and not one carrying a deny rule, because
// a deny file is one control-panel reset away from gone and the code that
// depended on it does not notice.
//
// THE DIRECTORY NAME OF THE SERVED TREE IS NOT WRITTEN ANYWHERE IN THIS FILE,
// including in this comment, and that is deliberate. A plan-level acceptance
// check greps this file for that literal and requires zero matches — it is how
// «just stage it under the site for a moment» is kept from being added later.
// Same discipline as brand-row.php:34-36 and contact-send.php's note about the
// legacy send call: a banned literal is discussed, never typed.
//
// WHAT ACTUALLY MAKES A FILE SAFE HERE, in one line, because it is easy to
// read the pipeline below as five redundant checks: it is the DECODE AND
// RE-ENCODE. getimagesize() and finfo only establish that the front of the
// file parses as an image — both are satisfied by a valid image with a PHP
// payload appended after the end-of-image marker, which is the entire
// polyglot technique. Only building a fresh image from the decoded pixels and
// writing new bytes discards everything that was not pixels. The checks before
// it exist to keep hostile input away from the decoder, not to bless it.
//
// CONTACT-05. Threats T-04-12 (upload executed as code), T-04-13 (resource
// exhaustion), T-04-14 (EXIF metadata forwarded to a third party), T-04-15
// ($_FILES metadata trusted), T-04-17 (a refused photo costing the enquiry).

// Normalise ONE uploaded file. Returns a fresh temp path, or false.
//
// $tmpPath is a path PHP itself produced for an upload; $maxEdge is the cap
// for the longest side, in pixels. Nothing about the client's filename or its
// declared content type reaches this function at all — they are not arguments,
// so there is no way for a later edit to start trusting them here.
function torin_normalise_upload($tmpPath, $maxEdge) {
	if (!is_string($tmpPath) || $tmpPath === '' || !is_file($tmpPath)) {
		return false;
	}

	// 1. STRUCTURAL PARSE FIRST. getimagesize() reads and parses the header;
	// it fails on a file that merely carries an image extension or a spoofed
	// content type. The mime it reports is derived from the BYTES.
	$torin_info = @getimagesize($tmpPath);
	if ($torin_info === false || !isset($torin_info['mime'])) {
		return false;
	}
	$torin_mime = $torin_info['mime'];
	// Only the three types the form advertises. An allow-list, never a
	// deny-list: a deny-list is a promise to have thought of every format, and
	// the formats that matter are the ones nobody listed.
	if ($torin_mime !== 'image/jpeg' && $torin_mime !== 'image/png' && $torin_mime !== 'image/webp') {
		return false;
	}

	// 2. finfo ALONGSIDE the parse, not instead of it. The two read the file
	// by different rules, and a file that satisfies one but not the other is
	// exactly the shape this pipeline exists to refuse.
	//
	// FAILS CLOSED if fileinfo is absent. It is measured present on this host
	// (04-HOST-CAPABILITIES, final probe) — but over the course of 04-01 six
	// extensions on this account were present, then absent, then present
	// again. Degrading to «then skip the sniff» would silently turn the
	// weakest possible control on a public upload endpoint back on.
	if (!class_exists('finfo')) {
		error_log('torin upload: ext-fileinfo missing, refusing all uploads');
		return false;
	}
	$torin_finfo = new finfo(FILEINFO_MIME_TYPE);
	$torin_sniff = $torin_finfo->file($tmpPath);
	if ($torin_sniff !== $torin_mime) {
		return false;
	}

	// 3. ORIENTATION IS READ BEFORE ANY TRANSFORM, and this is the only moment
	// it exists. The re-encode in step 6 drops every metadata block — which is
	// how the GPS coordinates, the device model and the capture timestamp in a
	// phone photograph are kept out of a third-party API (T-04-14) — and takes
	// the orientation tag with them. Read it here or deliver every portrait
	// photograph rotated a quarter turn (P-7).
	$torin_orient = 1;
	if ($torin_mime === 'image/jpeg' && function_exists('exif_read_data')) {
		$torin_exif = @exif_read_data($tmpPath);
		if (is_array($torin_exif) && isset($torin_exif['Orientation'])) {
			$torin_orient = (int) $torin_exif['Orientation'];
		}
	}

	// 4. FULL DECODE. Hostile bytes reach a decoder here and nowhere earlier,
	// which is why the ceilings in torin_collect_uploads() are enforced before
	// this function is ever called.
	$torin_img = false;
	if ($torin_mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
		$torin_img = @imagecreatefromjpeg($tmpPath);
	} elseif ($torin_mime === 'image/png' && function_exists('imagecreatefrompng')) {
		$torin_img = @imagecreatefrompng($tmpPath);
	} elseif ($torin_mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
		// GD is built with WebP on this host, but the decoder is a separate
		// build option from the extension itself. If it is ever missing, the
		// file is refused with a reason rather than silently accepted and
		// then mangled — the form advertises WebP, so a refusal here is a
		// copy problem to fix, not a bug to hide.
		$torin_img = @imagecreatefromwebp($tmpPath);
	}
	if ($torin_img === false || $torin_img === null) {
		return false;
	}

	// 5. APPLY THE ORIENTATION. All eight EXIF values, not just the four a
	// phone usually writes — the mirrored ones cost three lines and their
	// absence is invisible until someone's photograph arrives back-to-front.
	//
	// imagerotate() measures COUNTER-clockwise, and every EXIF value is
	// defined by where the stored image's first row and column belong. Value 6
	// means the image must be turned a quarter turn clockwise to read
	// correctly, which is -90 here. Getting this sign wrong produces a photo
	// rotated the WRONG way, which looks exactly as broken as no fix at all.
	$torin_img = torin_upload_orient($torin_img, $torin_orient);
	if ($torin_img === false) {
		return false;
	}

	// 6. GEOMETRY. Scale so the longest edge meets the cap; leave a smaller
	// image alone in dimension.
	$torin_w = imagesx($torin_img);
	$torin_h = imagesy($torin_img);
	if ($torin_w < 1 || $torin_h < 1) {
		imagedestroy($torin_img);
		return false;
	}

	// Aspect ratio is checked and not merely assumed. The notification service
	// refuses a single photo whose long side is more than twenty times its
	// short side, and unlike the byte ceiling and the pixel ceiling, a uniform
	// downscale does NOT bring a ratio back inside a limit — it is invariant
	// under scaling. Leaving it unchecked would make «a photograph that passes
	// validation is within the service's limits» false for one input shape.
	$torin_long = max($torin_w, $torin_h);
	$torin_short = min($torin_w, $torin_h);
	if ($torin_long > $torin_short * 20) {
		imagedestroy($torin_img);
		return false;
	}

	$torin_scale = 1.0;
	if ($torin_long > $maxEdge) {
		$torin_scale = $maxEdge / $torin_long;
	}
	$torin_nw = max(1, (int) round($torin_w * $torin_scale));
	$torin_nh = max(1, (int) round($torin_h * $torin_scale));

	// THE RESAMPLE RUNS EVEN AT 1:1, onto an opaque white canvas, and that is
	// not a wasted copy. A PNG or a WebP with an alpha channel re-encoded
	// straight to JPEG composites its transparency against BLACK, so a photo
	// of a screen shot through a transparent overlay arrives with black
	// blotches. One code path, one flattening rule, no branch that is only
	// exercised by the file type nobody tested with.
	$torin_dst = imagecreatetruecolor($torin_nw, $torin_nh);
	if ($torin_dst === false) {
		imagedestroy($torin_img);
		return false;
	}
	imagefilledrectangle($torin_dst, 0, 0, $torin_nw - 1, $torin_nh - 1, imagecolorallocate($torin_dst, 255, 255, 255));
	imagecopyresampled($torin_dst, $torin_img, 0, 0, 0, 0, $torin_nw, $torin_nh, $torin_w, $torin_h);
	imagedestroy($torin_img);

	// 7. RE-ENCODE UNDER A RANDOM NAME, IN THE SYSTEM TEMP DIRECTORY ONLY.
	// tempnam() supplies the random name; the client's filename is never
	// reused, never sanitised, never involved. Quality 82 is the same figure
	// the browser-side downscale uses, so a photo that took the scripted path
	// and one that did not arrive looking alike.
	$torin_out = tempnam(sys_get_temp_dir(), 'torin_');
	if ($torin_out === false || $torin_out === '') {
		imagedestroy($torin_dst);
		return false;
	}

	// The containment assertion, run rather than assumed. tempnam() falls back
	// to the system temp directory when its first argument is unusable, which
	// is the behaviour being relied on — but «relied on» and «checked» are
	// different claims, and this one guards the single worst outcome in the
	// whole phase. A path that lands anywhere else is removed and refused.
	//
	// COMPARE RESOLVED PATHS ON BOTH SIDES. tempnam() returns an already-
	// resolved path on some platforms while sys_get_temp_dir() returns the
	// symlinked form. On macOS /var is a symlink to /private/var, so the two
	// spellings disagree and the plain string prefix test refused EVERY upload
	// it was handed — a false refusal dressed as containment, and invisible to
	// the visitor, who is simply told their photo was not recognised.
	// Discovered 2026-09-22 the first time scripts/upload-selftest.php was
	// executed, which is the whole argument for owning a runtime.
	//
	// realpath() on both sides is also strictly STRONGER than the comparison it
	// replaces: it collapses `..` and resolves symlinks BEFORE the prefix test,
	// so a path that merely looks contained can no longer pass one.
	$torin_root = realpath(sys_get_temp_dir());
	$torin_real = realpath($torin_out);
	if ($torin_root === false || $torin_real === false
	    || strpos($torin_real, rtrim($torin_root, '/') . '/') !== 0) {
		@unlink($torin_out);
		imagedestroy($torin_dst);
		error_log('torin upload: temp path escaped the system temp directory');
		return false;
	}

	$torin_ok = @imagejpeg($torin_dst, $torin_out, 82);
	imagedestroy($torin_dst);
	if (!$torin_ok) {
		@unlink($torin_out);
		return false;
	}
	return $torin_out;
}

// The orientation transforms, split out so torin_normalise_upload() reads as
// the pipeline it is. Returns a GD image (possibly the same one) or false.
//
// imagerotate() and imageflip() RETURN A NEW IMAGE and leave the old one
// allocated; forgetting to destroy the old one is a memory leak that only
// shows up under the load this endpoint will never see and is therefore never
// noticed. Each branch destroys what it replaces.
function torin_upload_orient($img, $orient) {
	if ($orient === 2 || $orient === 5 || $orient === 7) {
		if (!function_exists('imageflip')) {
			// Mirrored orientations are vanishingly rare and a mirrored photo
			// is still readable, so an ancient GD without imageflip() loses
			// the mirror rather than losing the photograph.
			$orient = ($orient === 2) ? 1 : (($orient === 5) ? 8 : 6);
		} else {
			imageflip($img, IMG_FLIP_HORIZONTAL);
			$orient = ($orient === 2) ? 1 : (($orient === 5) ? 8 : 6);
		}
	} elseif ($orient === 4) {
		if (function_exists('imageflip')) {
			imageflip($img, IMG_FLIP_VERTICAL);
		}
		$orient = 1;
	}

	$torin_angle = 0;
	if ($orient === 3) {
		$torin_angle = 180;
	} elseif ($orient === 6) {
		$torin_angle = -90;
	} elseif ($orient === 8) {
		$torin_angle = 90;
	}
	if ($torin_angle === 0) {
		return $img;
	}

	$torin_rotated = @imagerotate($img, $torin_angle, 0);
	if ($torin_rotated === false || $torin_rotated === null) {
		// The rotation failed but the pixels are fine. Returning the
		// unrotated image delivers a sideways photograph, which is a bad
		// outcome; refusing it delivers nothing, which is a worse one.
		return $img;
	}
	imagedestroy($img);
	return $torin_rotated;
}

// Walk PHP's multi-file upload shape, enforce every ceiling, normalise what
// survives. Returns array('paths' => array(...), 'errors' => array(...)).
//
// BOTH HALVES OF THE RETURN MATTER. The paths are what gets delivered; the
// errors are what the visitor is told. A pipeline that returned only the paths
// would refuse a photograph in silence — and the visitor, who can see their
// own photo attached in the form, would have no way to learn it never arrived
// (T-04-17).
//
// $limits carries 'max_files', 'max_bytes', 'max_total_bytes' and 'max_edge'.
// They are arguments rather than constants so the caller owns the policy and
// the pipeline owns the mechanism — and so the self-test can lower a ceiling
// without building a ten-megabyte fixture.
function torin_collect_uploads($filesEntry, $limits) {
	$torin_paths = array();
	$torin_errors = array();

	// No control rendered, or no file chosen. Zero photographs is a COMPLETE,
	// VALID submission (D4-12) — plenty of faults have nothing to photograph,
	// and «my laptop will not power on» is the commonest of them.
	if (!is_array($filesEntry) || !isset($filesEntry['tmp_name']) || !is_array($filesEntry['tmp_name'])) {
		return array('paths' => $torin_paths, 'errors' => $torin_errors);
	}

	$torin_max_files = (int) $limits['max_files'];
	$torin_max_bytes = (int) $limits['max_bytes'];
	$torin_max_total = (int) $limits['max_total_bytes'];
	$torin_max_edge = (int) $limits['max_edge'];

	// An empty file control still posts one slot carrying UPLOAD_ERR_NO_FILE.
	// Counting slots rather than real files would tell a visitor who attached
	// nothing that they attached too many.
	$torin_slots = array();
	foreach ($filesEntry['tmp_name'] as $torin_i => $torin_tmp) {
		$torin_code = isset($filesEntry['error'][$torin_i]) ? (int) $filesEntry['error'][$torin_i] : UPLOAD_ERR_NO_FILE;
		if ($torin_code === UPLOAD_ERR_NO_FILE) {
			continue;
		}
		$torin_slots[] = array('tmp' => $torin_tmp, 'code' => $torin_code);
	}

	$torin_total_files = count($torin_slots);
	// The system-scoped max_file_uploads (20 on this host, and NOT settable
	// from a per-directory ini — P-5) silently discards anything past the
	// twentieth before PHP builds this array at all. Five is far below it, so
	// the count the visitor is told about is the count they actually sent.
	if ($torin_total_files > $torin_max_files) {
		$torin_errors[] = 'Може да прикачите най-много ' . $torin_max_files .
			' снимки, а са приложени ' . $torin_total_files . '.';
		$torin_slots = array_slice($torin_slots, 0, $torin_max_files);
	}

	$torin_running = 0;
	$torin_n = 0;
	foreach ($torin_slots as $torin_slot) {
		$torin_n++;
		$torin_label = 'Снимка ' . $torin_n . ': ';

		// The visitor's own filename is NEVER echoed back, here or anywhere.
		// It is attacker-controlled and it would land in a page (T-04-07); the
		// position in the list identifies the photo just as well and carries
		// nothing from the request.
		if ($torin_slot['code'] !== UPLOAD_ERR_OK) {
			$torin_errors[] = $torin_label . torin_upload_code_copy($torin_slot['code']);
			continue;
		}

		$torin_tmp = $torin_slot['tmp'];
		if (!is_string($torin_tmp) || $torin_tmp === '' || !is_file($torin_tmp)) {
			$torin_errors[] = $torin_label . 'файлът не стигна до сървъра.';
			continue;
		}

		// is_uploaded_file() affirms that this path really came from a POST
		// and is not an arbitrary server file handed to this function by a
		// later, careless caller. It is SKIPPED under the CLI SAPI, where
		// there is no POST for it to affirm and it can only ever return false
		// — which is also what lets scripts/upload-selftest.php exercise this
		// walk. The web SAPI, the only one a visitor can reach, always checks.
		if (PHP_SAPI !== 'cli' && !is_uploaded_file($torin_tmp)) {
			$torin_errors[] = $torin_label . 'файлът не стигна до сървъра.';
			continue;
		}

		// THE BYTE COUNT IS READ OFF THE DISK, not taken from the upload
		// metadata. filesize() is what is actually there; everything in the
		// request is a claim.
		$torin_bytes = @filesize($torin_tmp);
		if ($torin_bytes === false) {
			$torin_errors[] = $torin_label . 'файлът не стигна до сървъра.';
			continue;
		}
		if ($torin_bytes > $torin_max_bytes) {
			$torin_errors[] = $torin_label . 'файлът е по-голям от ' .
				(int) round($torin_max_bytes / 1048576) . ' MB.';
			continue;
		}
		if ($torin_running + $torin_bytes > $torin_max_total) {
			$torin_errors[] = $torin_label . 'общият размер на снимките е твърде голям.';
			continue;
		}

		// Only now does anything reach a decoder.
		$torin_out = torin_normalise_upload($torin_tmp, $torin_max_edge);
		if ($torin_out === false) {
			$torin_errors[] = $torin_label . 'файлът не е разпознат като снимка (JPEG, PNG или WebP).';
			continue;
		}
		$torin_running += $torin_bytes;
		$torin_paths[] = $torin_out;
	}

	return array('paths' => $torin_paths, 'errors' => $torin_errors);
}

// PHP's upload error codes, in the visitor's language. The two size codes are
// told apart from the rest because they are the only ones a visitor can act
// on, and INI_SIZE in particular is the one the per-directory .user.ini
// decides — see src/.user.ini for why that ceiling matches the form's copy.
function torin_upload_code_copy($code) {
	if ($code === UPLOAD_ERR_INI_SIZE || $code === UPLOAD_ERR_FORM_SIZE) {
		return 'файлът е твърде голям.';
	}
	if ($code === UPLOAD_ERR_PARTIAL) {
		return 'качването прекъсна. Опитайте отново.';
	}
	// NO_TMP_DIR, CANT_WRITE and EXTENSION are server faults, not visitor
	// faults, so they are logged for the developer and described neutrally.
	error_log('torin upload: server-side upload error code ' . (int) $code);
	return 'файлът не можа да бъде приет.';
}

// Remove normalised temp files. Idempotent, and safe to call twice — which it
// is, because contact-send.php registers it as a shutdown handler as well as
// calling it on the ordinary path.
//
// D4-07 BUYS A SHORT PRIVACY NOTE AND NO RETENTION OBLIGATION PRECISELY
// BECAUSE NOTHING IS KEPT. A temp file that survives one request quietly
// converts that into a false statement on the terms page, and nobody would
// ever notice, because the file is somewhere nobody looks.
function torin_release_uploads($paths) {
	if (!is_array($paths)) {
		return;
	}
	foreach ($paths as $torin_path) {
		if (is_string($torin_path) && $torin_path !== '' && file_exists($torin_path)) {
			@unlink($torin_path);
		}
	}
}
?>
