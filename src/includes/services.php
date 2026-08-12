<?php
// includes/services.php — PHP 5.2-safe (no short array syntax, no closures,
// no namespaces). Emits nothing on include; it is data plus two accessors.
//
// Single source of truth for the five D3-03 CHILD pages under category 2
// («Екран, клавиатура и портове»). Панти, матрица, клавиатура, USB/HDMI портове
// and захранваща букса are five distinct searches with their own price intent,
// and one page competing for all five loses all five — that is the whole
// reasoning behind D3-03. The parent stays as a deliberately short routing hub
// that passes authority down rather than competing for a keyword of its own.
//
// FOUR consumers read this file, which is why no href is ever hand-typed:
//   1. the cat-2 routing hub          (plan 03-03)
//   2. the five child pages themselves (plan 03-03)
//   3. breadcrumbs                     (torin_render_breadcrumbs)
//   4. sitemap.xml                     (Phase 4)
//
// This is a SIBLING of categories.php and deliberately NOT part of it. Two
// reasons. The obvious one: a child page is not a category, and merging them
// would put eleven records behind an accessor that six consumers read as six.
// The load-bearing one: the record-integrity greps in plan 02-02 count
// single-quoted key literals in categories.php to assert there are exactly six
// category records, and five more records in that file would break that
// assertion silently.
//
// Page filenames follow the D-42 transliterated-Latin .html convention, the
// same one categories.php uses. They are settled here, before the pages exist,
// because changing a slug after publication needs a 301 and forfeits ranking
// signal (D3-01/SEO-04).
//
// The publish flag on each record is the D-23 gate. All five ship FALSE here:
// the pages do not exist yet, and the gate is exactly what stops a card or a
// breadcrumb pointing at a file that would 404. Plan 03-03 creates the pages
// and flips the booleans in the same change — create-a-file plus flip-a-boolean,
// with zero edits in any consumer.
require_once(dirname(__FILE__) . '/categories.php');

$torin_services = array(
	array(
		'id'        => 'svc-matrica',
		'name'      => 'Смяна на матрица на лаптоп',
		// [ASSUMED] Placeholder standing in for the real customer phrasing the
		// owner hears daily (OWNER-QUESTIONS #16). Plan 03-03 replaces it. Not
		// confirmed shop language — do not quote it back as such.
		'symptoms'  => 'счупен екран, пукнат дисплей, петна и линии по картината',
		'page'      => 'smyana-na-matrica.html',
		'parent'    => 'kat-2',
		'published' => false,
	),
	array(
		'id'        => 'svc-klaviatura',
		'name'      => 'Смяна на клавиатура на лаптоп',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'залепнали клавиши, липсващи бутони, не пише правилно',
		'page'      => 'smyana-na-klaviatura.html',
		'parent'    => 'kat-2',
		'published' => false,
	),
	array(
		'id'        => 'svc-panti',
		'name'      => 'Смяна на панти на лаптоп',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'разхлабен капак, пукнат корпус около екрана, не стои отворен',
		'page'      => 'smyana-na-panti.html',
		'parent'    => 'kat-2',
		'published' => false,
	),
	array(
		'id'        => 'svc-portove',
		'name'      => 'Ремонт на USB и HDMI портове',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'не разчита флашка, разклатен порт, няма образ на телевизора',
		'page'      => 'remont-na-portove.html',
		'parent'    => 'kat-2',
		'published' => false,
	),
	array(
		'id'        => 'svc-buksa',
		'name'      => 'Смяна на захранваща букса',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'не се зарежда, изпада зарядното, клати се при включване',
		'page'      => 'smyana-na-buksa.html',
		'parent'    => 'kat-2',
		'published' => false,
	),
);

// Record lookup by id, the same shape torin_category_by_id() uses. Returns null
// rather than a fabricated record, so a typo renders nothing instead of a page
// of blanks.
//
// Keys are double-quoted in both accessors purely so that the record-integrity
// greps counting single-quoted key literals are not inflated by the lookups
// themselves. Inside the records, single quotes are the project convention —
// keep it that way.
function torin_service_by_id($id) {
	global $torin_services;
	foreach ($torin_services as $torin_svc_rec) {
		if ($torin_svc_rec["id"] === $id) {
			return $torin_svc_rec;
		}
	}
	return null;
}

// The D-23 publish gate for child pages. An unpublished child routes to its
// PARENT hub rather than to a homepage anchor, because unlike a category a
// child has a real parent page that covers its subject — sending a visitor to
// «Екран, клавиатура и портове» is a useful answer, while sending them to an
// anchor is a dead end.
//
// The parent href is resolved through torin_category_href() rather than read
// off the record, so the parent’s own publish state is honoured too: if the hub
// is not live either, the chain falls back to the homepage anchor exactly once,
// in one place. No consumer ever hand-types an href.
function torin_service_href($svc) {
	if ($svc["published"]) {
		return $svc["page"];
	}
	$torin_parent = torin_category_by_id($svc["parent"]);
	if ($torin_parent === null) {
		return 'index.html';
	}
	return torin_category_href($torin_parent);
}
?>
