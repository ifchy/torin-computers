<?php
// includes/asset-version.php — PHP 5.2-safe. Emits nothing on include; it is
// one function and no data, per this tree's include-boundary rule (only
// header.php, footer.php and dev-switcher.php produce markup).
//
// WHY THIS FILE EXISTS (gap G-02-1). Every stylesheet link the shared head
// emitted carried a bare href, while the origin serves those files with a
// multi-day max-age. A visitor who loaded a page mid-iteration therefore held
// a stylesheet from an earlier state of the design system, and there was no
// remote way to flush it — the owner saw a completely broken desktop rendering
// that a hard reload fixed entirely. The CSS as deployed was correct; what was
// missing was a way to invalidate a cached copy. This function is that way:
// the returned URL carries the DEPLOYED FILE'S OWN MODIFICATION TIME, so
// changing a file changes its URL, and a different URL is a different browser
// cache entry.
//
// The token is deliberately NOT a request-time-varying value (a timestamp, a
// random number, a counter). That would bust the cache on every single request,
// which is cache DEFEAT, not cache invalidation, and would silently discard the
// compression/caching work plan 02-01 verified live while looking like a fix.
//
// The token is a modification time, not a content hash: a redeploy of
// byte-identical files does bump it and warm caches unnecessarily. That is the
// accepted cost of the mechanism — a content hash would mean reading and
// digesting every stylesheet on every request on a PHP 5.2 shared host with no
// reliable opcode or user cache. The owner's alternative (rename the file per
// version) also works and stays the fallback if query-string invalidation ever
// proves unreliable on this host; it is not chosen because it is manual, and
// every manual step is a step someone eventually skips.
//
// THE ?v=0 SENTINEL. If the stat fails — the path does not exist on the server,
// or filemtime() returns false — this function returns the path with a ZERO
// token rather than the bare path. That is deliberate: the emitted shape stays
// uniform, and a broken stamp becomes DETECTABLE (scripts/asset-version-check.sh
// asserts zero occurrences of it across all sixteen pages). Returning the bare
// href on failure would instead make a broken stamp byte-identical to the very
// defect this file exists to close, and no checker could tell them apart.
//
// DELIBERATE EXCLUSION: the Sofia Sans preload in header.php is NOT routed
// through this function, for two independent reasons recorded beside the
// preload element itself. In short: the preload href must byte-match the URL
// the @font-face src in base.css requests, and that stylesheet declares it with
// no query string and no way to stamp one in — a mismatch downloads the subset
// twice on every cold visit. Under D-06a a font change means a new subset,
// i.e. a new filename, which is its own invalidation.
//
// dirname(__FILE__) is the 5.2-safe idiom; the 5.3+ magic directory constant is
// banned everywhere in this tree.

// $rel is a site-root-relative path such as 'css/base.css'. It is always a
// developer-authored literal — no request value may ever reach this argument
// (T-02-26), and every call site escapes the return value for the HTML
// attribute it lands in regardless.
function torin_asset_url($rel) {
	// This file lives in includes/; every asset lives in a sibling directory of
	// includes/ (css/, js/, fonts/); every page that includes it sits at the
	// site root. So the parent of includes/ is BOTH the disk root and the base
	// the relative href resolves against — one directory serves both jobs.
	$torin_asset_root = dirname(dirname(__FILE__));
	$torin_asset_path = $torin_asset_root . '/' . $rel;

	$torin_asset_token = 0;
	if (file_exists($torin_asset_path)) {
		$torin_asset_mtime = filemtime($torin_asset_path);
		if ($torin_asset_mtime !== false) {
			$torin_asset_token = $torin_asset_mtime;
		}
	}

	return $rel . '?v=' . $torin_asset_token;
}
?>
