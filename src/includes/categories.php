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
// ── D3.5-04: THE CATEGORY 1/2 BOUNDARY IS FAILURE TYPE, NOT COMPONENT ────────
//
// The owner's own distinction is physical or mechanical damage versus
// malfunction or electronic fault. Stated that way it is a TECHNICIAN'S rule,
// and a customer staring at a dark screen cannot apply it. The agreed
// CUSTOMER-FACING form of the SAME boundary is the observable proxy, and it is
// what the two symptom lines below encode:
//
//   category 1 — «има видима повреда»
//   category 2 — «изглежда здрав, но не работи»
//
// That rule is written down here so the next reader does not re-derive the
// technician's version — which is what produced the two inconsistencies plan
// 03.5-02 resolves: category 1's line claimed a loose-hinge symptom while the
// hinge child page hangs off category 2, and category 2's line opened with
// damage that is plainly visible and therefore belongs to category 1
// (ROADMAP SC-5).
//
// ⚠ THE FIVE CHILD RECORDS IN services.php ARE NOT RE-PARENTED, AND THAT IS
// DELIBERATE. Reading category 1's new line and nothing else makes the hinge
// child's parent key look like an oversight. It is not. Those are COMPONENT
// pages: a component fails both ways, and a screen that is physically damaged
// and a screen that stays dark are the SAME repair reached from either
// category. torin_service_href() routes by record, so this is a data change
// with no template change and both categories already reach the child pages —
// mehanichni-problemi.html resolves four of the five through that accessor
// today. Re-parenting would additionally rewrite each page's breadcrumb and
// its already-indexed BreadcrumbList, for no gain.
//
// THE [ASSUMED] MARKER STAYS ON EVERY SYMPTOM LINE IN THIS FILE, including the
// two rewritten here. OWNER-QUESTIONS #16 — the phrasing customers actually
// use — was deferred by the owner to developer research and is NOT answered by
// this phase. For kat-1 and kat-2 the lines are now OWNER-DERIVED rather than
// drafting placeholders; they are still not confirmed literal customer speech,
// so they must not be quoted back as such.
$torin_categories = array(
	array(
		'id'        => 'kat-1',
		'name'      => 'Счупвания и механични повреди',
		// [ASSUMED] Owner-derived per D3.5-04, still unconfirmed as literal
		// customer speech. Two changes from the line this replaces: the hinge
		// symptom moves from loose to broken, and a physically damaged screen is
		// ADDED — it is visible damage, and visible damage is this category.
		'symptoms'  => 'паднал лаптоп, счупен корпус, пукнат екран, счупени панти',
		'page'      => 'mehanichni-problemi.html',
		'icon'      => 'cat-1',
		// Intrinsic size of img/icons/cat-1.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 120,
		'published' => true,
	),
	array(
		'id'        => 'kat-2',
		'name'      => 'Екран, клавиатура и портове',
		// [ASSUMED] Owner-derived per D3.5-04, still unconfirmed as literal
		// customer speech. The physically damaged screen that used to LEAD this
		// line is gone — that was the misplacement ROADMAP SC-5 names, and it now
		// sits in category 1. What remains is the looks-intact-but-does-not-work
		// half of the boundary: three things a customer can observe without
		// knowing which part failed.
		'symptoms'  => 'екранът не светва, клавиши не реагират, портът не зарежда',
		'page'      => 'ekran-klaviatura-portove.html',
		'icon'      => 'cat-2',
		// Intrinsic size of img/icons/cat-2.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 98,
		'published' => true, // published by plan 03-03 as a D3-03 routing hub
	),
	array(
		'id'        => 'kat-3',
		'name'      => 'Оптимизация',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'бавен е, забива, дълго стартира, пълна памет',
		'page'      => 'optimizatsiq.html',
		'icon'      => 'cat-3',
		// Intrinsic size of img/icons/cat-3.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 120,
		'published' => true,
	),
	array(
		'id'        => 'kat-4',
		'name'      => 'Заливане и ремонт на дънни платки',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'залят с течност, не дава признаци на живот, не зарежда',
		'page'      => 'zalivane-technosti.html',
		'icon'      => 'cat-4',
		// Intrinsic size of img/icons/cat-4.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 98,
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
		// Intrinsic size of img/icons/cat-5.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 98,
		// Published by plan 03-08, in the SAME change that lands the page file.
		'published' => true,
	),
	array(
		// Scope is still an open owner question (OWNER-QUESTIONS #3); the broad
		// name deliberately keeps the options open.
		'id'        => 'kat-6',
		'name'      => 'Нестандартна техника',
		// [ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16.
		'symptoms'  => 'нестандартна или стара техника, която другаде не приемат',
		// D3-05: category 6 lands on problem-stari.html, an EXISTING indexed URL
		// whose slug reads as «стари» and whose semantics match this symptom
		// line, rather than on a new slug. It inherits whatever authority that
		// URL holds and needs no new file.
		//
		// This value previously named a file that D3-05 guarantees will never
		// exist. Worth fixing now rather than when the page is written, because
		// the wrong value is INVISIBLE until the moment it matters: the category
		// is unpublished, so torin_category_href() routes every card and nav
		// entry to index.html#kat-6 and never reads this key. It would have
		// started 404-ing on exactly the day someone flipped the boolean — the
		// one day nobody would be looking for a routing bug.
		'page'      => 'problem-stari.html',
		'icon'      => 'cat-6',
		// Intrinsic size of img/icons/cat-6.svg, scaled from its own viewBox to a
		// 120px width. These are for the NO-STYLESHEET case: an <img> with no
		// dimensions falls back to the SVG's own canvas, which is 2048px wide on
		// four of the six. With components.css loaded the box is already fixed by
		// .cat-card__media, so these never affect the styled render.
		'art_w'     => 120,
		'art_h'     => 98,
		// Published 2026-09-16 on the owner's instruction. The page renders 845
		// Cyrillic tokens - longer than about.html, ekran-klaviatura-portove.html
		// and index.html, all of which were already published - and every claim on
		// it traces to an owner answer (category 6 is medical and industrial
		// equipment, #3; warranty and diagnostics carve-outs, #23 and #24).
		// Publishing also retires the orphan: while this was false,
		// torin_category_href() routed every card and nav entry to index.html#kat-6
		// and nothing pointed at the page, so it would have become indexable and
		// still unreachable the moment cutover stripped the noindex header.
		// Reversible: this flag is the only thing that changes, and the page stays
		// live at its URL either way.
		// STILL OPEN, and now VISIBLE on the homepage card: the symptoms line below
		// is developer-written, not owner-confirmed - OWNER-QUESTIONS #3f and #16.
		// Gaps #3c (what is refused), #32 and #33 (what warranty and diagnostics DO
		// apply) remain unanswered and must not be filled with invented copy.
		'published' => true,
	),
);

// Record lookup by id. A plain named function, never a closure — closures do
// not exist on PHP 5.2. Returns null rather than a fabricated record, so a
// typo'd id renders nothing instead of a page of blanks.
//
// This lives HERE, beside the data and beside the publish gate, rather than in
// category-page.php where it was originally written. It is a record accessor,
// not a rendering concern, and services.php needs it to resolve a child page
// parent — routing a child through its parent hub must go through the same gate
// every other consumer uses. Leaving it in the template would have forced a
// data file to depend on the renderer to do a lookup.
function torin_category_by_id($id) {
	global $torin_categories;
	foreach ($torin_categories as $torin_cat_rec) {
		if ($torin_cat_rec["id"] === $id) {
			return $torin_cat_rec;
		}
	}
	return null;
}

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
