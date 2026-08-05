<?php
// includes/categories.php — PHP 5.2-safe (no short array syntax, no closures,
// no namespaces). Emits nothing on include; it is data plus one accessor.
//
// Single source of truth for the six owner-priority categories (D-09/D-40).
// FOUR consumers read it, which is why no href is ever hand-typed anywhere:
//   1. the homepage card grid           (plan 02-02)
//   2. the Услуги dropdown              (plan 02-03)
//   3. the category page templates      (Phase 3)
//   4. sitemap.xml                      (Phase 4)
//
// Names are the D-40 working set, sourced verbatim from the category table in
// 02-UI-SPEC §Copywriting Contract. They are DISPLAY strings, never URL slugs
// (D-42) — the on-screen name and the filename are independent by design.
//
// Page filenames: three of the six pages already exist live and their names are
// locked by SEO-04, so they are reproduced here byte-for-byte; the other three
// are new and follow the same transliterated-Latin convention (D-42, тех → tech).
// Changing a new slug after Phase 3 publishes it needs a 301 and forfeits
// accumulated ranking signal — treat all six as settled.
//
// The publish flag on each record is the D-23 gate: a category page does not go
// live until it has genuine content, and until then torin_category_href() sends
// the card to that category's own homepage anchor instead. Publishing later is
// one boolean flip with zero edits in any consumer.
$torin_categories = array(
	array(
		'id'        => 'kat-1',
		'name'      => 'Счупвания и механични повреди',
		// [ASSUMED] Placeholder standing in for the real customer phrasing the
		// owner hears daily (OWNER-QUESTIONS #16). Phase 3 replaces it. Not
		// confirmed shop language — do not quote it back as such.
		'symptoms'  => 'паднал лаптоп, счупен корпус, разхлабени панти',
		'page'      => 'mehanichni-problemi.html',
		'icon'      => 'cat-1',
		'published' => true,
	),
	array(
		'id'        => 'kat-2',
		'name'      => 'Екран, клавиатура и портове',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'пукнат екран, не свети, липсващи клавиши, не се зарежда',
		'page'      => 'ekran-klaviatura-portove.html',
		'icon'      => 'cat-2',
		'published' => false,
	),
	array(
		'id'        => 'kat-3',
		'name'      => 'Оптимизация',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'бавен е, забива, дълго стартира, пълна памет',
		'page'      => 'optimizatsiq.html',
		'icon'      => 'cat-3',
		'published' => true,
	),
	array(
		'id'        => 'kat-4',
		'name'      => 'Заливане и ремонт на дънни платки',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'залят с течност, не дава признаци на живот, не зарежда',
		'page'      => 'zalivane-technosti.html',
		'icon'      => 'cat-4',
		'published' => true,
	),
	array(
		// D-40 renames this category away from the owner's original
		// «Смяна на вентилатори», which named the SOLUTION. A customer whose
		// laptop is overheating would not recognise a fan swap as their problem
		// (D-29). The rename also lets профилактика cross-list here (D-28),
		// which is what resolves the category's thinness. Do not revert the
		// wording — that reopens both halves of D-29.
		'id'        => 'kat-5',
		'name'      => 'Прегряване и охлаждане',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'прегрява, шуми силно, изключва се сам',
		'page'      => 'pregryavane-ohlazhdane.html',
		'icon'      => 'cat-5',
		'published' => false,
	),
	array(
		// Scope is still an open owner question (OWNER-QUESTIONS #3); the broad
		// name deliberately keeps the options open.
		'id'        => 'kat-6',
		'name'      => 'Нестандартна техника',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'нестандартна или стара техника, която другаде не приемат',
		'page'      => 'nestandartna-technika.html',
		'icon'      => 'cat-6',
		'published' => false,
	),
);

// The D-23 publish gate, in one place. A plain named function, never a closure —
// closures do not exist on PHP 5.2. Every consumer calls this instead of reading
// the record itself, so flipping the publish flag is the whole publishing action.
//
// Keys are double-quoted here purely so that the record-integrity greps in
// 02-02-PLAN, which count single-quoted key literals to assert there are exactly
// six records, are not inflated by this accessor's own lookups. Inside the
// records, single quotes are the project convention — keep it that way.
function torin_category_href($cat) {
	if ($cat["published"]) {
		return $cat["page"];
	}
	return 'index.html#' . $cat["id"];
}
?>
