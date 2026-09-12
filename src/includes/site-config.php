<?php
// includes/site-config.php — PHP 5.2-safe (no short array syntax, no closures, no namespaces)
// Single source of truth for site-wide contact values. Every entry carries a
// provenance comment naming where the value came from.
//
// Entries marked [ASSUMED] are NOT confirmed by the shop owner. Each names the
// open question that closes it. They render on all 16 pages, and the hours also
// feed the structured data Google acts on — a wrong value there sends real
// customers to a closed shop, sixteen pages and one search engine at once. Do
// not quote them back as confirmed fact, and do not drop a marker until its
// OWNER-QUESTIONS item is answered.
$site = array(
	// Three separate numbers, never one joined string: each renders its own
	// tel: link, so the footer works identically for one number or five. No
	// consumer may join this list back into a display string. Sourced from the
	// secondarybar block of site-current/index.html. The scalar key this
	// replaced was REMOVED rather than kept alongside — two representations of
	// one fact silently disagree the day a number changes, and single-sourcing
	// is the entire reason this file exists.
	'phones' => array('02 9549710', '088 9458404', '087 9128244'),

	// The E.164 form of the FIRST entry above — the shop's main line — and the
	// single source for every primary call CTA on the site (the homepage hero,
	// the repeated CTA block, the sticky call bar, the footer, and the three
	// category pages) as well as the structured-data telephone property. Change
	// the shop's main number and this one line changes all of them; that is the
	// whole reason the key exists, and it is what stops one page dialling a
	// string another page no longer serves.
	//
	// It is a LITERAL and is deliberately NOT derived at runtime from the list
	// above: mapping a local zero-prefixed Bulgarian number to its
	// international form is a dialling rule, not a string operation, and
	// jsonld.php already refused that same substitution for that same reason.
	// The value is the one decoded from the legacy site, and it is identical to
	// the literal jsonld.php previously carried independently.
	//
	// The two mobile entries have NO counterpart here on purpose: no source
	// artifact supplies their international forms, and inventing them would
	// publish two possibly-undialable numbers on sixteen pages while looking
	// like a tidy-up.
	'phone_e164' => '+35929549710',

	'email' => 'office@torin.bg',   // site-current/mailer.php

	'address' => 'ул. Свети Иван Рилски 46, София 1606',   // site-current/index.html hero block

	// Deep link that REPLACES the legacy Google Maps embed (D-34): an embed
	// pulls several hundred KB of third-party JavaScript per page view, on 16
	// pages, working directly against DESIGN-02. Built on the coordinates below.
	'maps_url' => 'https://www.google.com/maps/search/?api=1&query=42.68856%2C23.30806',

	// Decoded from the legacy Maps embed URL at site-current/index.html:940
	'geo_lat' => '42.68856',
	'geo_lng' => '23.30806',

	// [ASSUMED] OWNER-QUESTIONS #20. The live site states two different sets of
	// hours across three sources (02-RESEARCH N-3): index.html and about.html
	// say 8:00-16:00, profilaktika-laptop.html says 9:00-17:00, and the banner
	// labelled «НОВО» says 8:00-16:00. The two-of-three majority is the interim
	// value. It is also hard-coded into jsonld.php's opening hours — change
	// BOTH when the owner answers.
	'hours' => 'Понеделник – Петък, 8:00 – 16:00',

	// The Viber deep-link target. UAT gap G-02-5 (test 28) is the whole story
	// behind this value, and it is worth keeping because the same trap is easy
	// to walk back into.
	//
	// This was +35929549710 — the main line — as an [ASSUMED] placeholder
	// against OWNER-QUESTIONS #21. It was a dead end: 02 954 9710 is a Sofia
	// LANDLINE and Viber accounts are provisioned against mobile numbers, so on
	// Android the button returned Viber's "the requested page is unavailable,
	// please update to the latest version" on all 16 deployed pages. The client
	// was current; the deep link simply did not resolve.
	//
	// That failure had TWO candidate causes which could not be told apart while
	// both were in play:
	//   (a) the number has no Viber account
	//   (b) viber://chat is the wrong deep-link path
	// They were separated by deploying a number known to HAVE Viber: the button
	// opened a conversation, so (b) is ELIMINATED — the viber://chat?number=
	// scheme is correct and the fix is a value change, not a rewrite.
	//
	// ###########################################################
	// ## CUTOVER GATE — must be re-tested before launch.       ##
	// ## .planning/todos/pending/verify-viber-button-before-   ##
	// ## launch.md (resolves_phase: 4)                         ##
	// ###########################################################
	//
	// This number is DELIBERATE and settled — do not change it while chasing a
	// bug report. Decision 2026-08-09: the button stays on 088 945 8404 and the
	// OWNER will provision a Viber account on that number. The value is already
	// the intended target; what is missing is the account on the far end.
	//
	// History, kept because the failure is easy to misdiagnose. At Phase 2 UAT
	// this button was a dead link on all 16 pages. All three published shop
	// numbers were tested individually on a real Android handset and all three
	// failed identically with Viber's "the requested page is unavailable, please
	// update to the latest version":
	//   +35929549710  (02 954 9710, landline) -> no Viber account
	//   +359879128244 (087 912 8244, mobile)  -> no Viber account
	//   +359889458404 (088 945 8404, mobile)  -> no Viber account  <- current
	//
	// The deep-link SCHEME is NOT the problem and must not be "fixed". A control
	// number known to have Viber was deployed briefly and opened a conversation
	// normally through this identical viber://chat?number= href, which eliminates
	// that hypothesis experimentally. Switching to viber://add or anything else
	// would chase an already-falsified cause and hide the real one.
	//
	// No automated check can verify this. Every Phase 2 probe confirmed the href
	// is present, well-formed and single-sourced — all true, and still true.
	// Whether the number behind it has a Viber account is not a property of the
	// markup and is invisible from the origin; it needs a real handset with Viber
	// installed. Hence the human cutover gate rather than a script.
	'viber' => '+359889458404',

	// TRUST-03 (D3-10). ONE warranty summary, written here once and read by
	// every service page through $page['warranty_key'] — never retyped on a
	// page. A page selects a KEY; it never authors a literal.
	//
	// ANSWERED 2026-09-11 (OWNER-QUESTIONS #23, recorded as CONTEXT D3.5-06):
	// one month on every repair EXCEPT category 6 — medical and industrial
	// equipment — where the standard terms do not apply. Voided by liquid, by
	// impact, or by another shop opening or working on the device, with the
	// owner's own caveat that third-party opening cannot currently be reliably
	// detected. The [ASSUMED] marker that stood here is gone because the
	// question behind it was answered, not because a marker was tidied away.
	//
	// The set carried a second entry until plan 03.5-01: a longer product term
	// on a service line the shop has since discontinued (D3.5-01), selected by
	// a page that is retired in the same plan. Both are gone. The set stays
	// KEYED rather than collapsing to a scalar because category 6 needs its own
	// term, and because category-page.php:377-379 falls back to 'default' for
	// an unknown key — so a stale selector left anywhere degrades to the
	// standard terms rather than to a page with no warranty at all.
	//
	// Source for 'default': site-current/warrently.html:113-129.
	//
	// D3-10 reframing, recorded because the omission would look like sloppiness
	// and the reproduction would look like a trap: the live warranty page
	// additionally REQUIRES the customer to run the laptop 5-6 hours a day to
	// accumulate 150-200 hours of test time. Read as the shop means it, that is
	// a statement of confidence that the repair holds under real use; read as a
	// customer would, it is a way to void a claim. The detail below carries the
	// first reading and deliberately states no hour threshold. The term is NOT
	// silently dropped — it is a condition the shop operates under and it is
	// still stated in full on warrently.html, which every entry links to.
	// OWNER-QUESTIONS #23 did NOT rule on that clause in either direction, so it
	// stays exactly as it is, and harmonising it remains forbidden.
	'warranty' => array(
		'default' => array(
			'term'   => '1 месец гаранция на всеки ремонт',
			'detail' => 'Гаранционното обслужване е безплатно, в сервиза. Съветваме ви да ползвате лаптопа активно през този месец — така и вие, и ние сме сигурни, че ремонтът държи при реална употреба. Гаранцията не важи за нестандартна техника — медицинска и индустриална апаратура. Отпада при заливане с течност, удар или при отваряне и намеса от друг сервиз.',
			'href'   => 'warrently.html',
		),
		// Category 6. The owner ruled what does NOT apply and did not say what
		// does, so this entry has NO 'detail' key and none may be invented
		// (D3.5-08) — a term written into that gap would be a promise the shop
		// has not made. category-page.php:454 guards the key, so the omission
		// renders as a term with no elaboration rather than as an empty
		// paragraph. The entry exists so that a category-6 page states the
		// exclusion instead of silently inheriting the standard month.
		'nonstandard' => array(
			'term'   => 'Стандартният едномесечен гаранционен срок не важи за нестандартна техника',
			'href'   => 'warrently.html',
		),
	),

	// D3.5-06 (OWNER-QUESTIONS #24). The free-diagnostics claim, written here
	// once and read by every consumer, so the reword has ONE writer instead of
	// fifteen. TWO keys rather than one joined string, for the same reason the
	// phone list is never joined: the claim and its exclusion are two separate
	// facts, and a consumer with room for only the first must drop the second
	// visibly, at its own call site, rather than silently.
	//
	// The middle word is doing real work and is not a hedge: the owner scoped
	// the promise to a quick initial assessment rather than an unlimited free
	// investigation. Dropping it re-publishes a promise the shop did not make.
	//
	// The exclusion is category 6 — medical and industrial equipment. The owner
	// ruled the claim applies to categories 1-5 ONLY, so a category-6 page must
	// not render it at all; the second key is for the SHARED surfaces that
	// render on every service page and therefore cannot know their category.
	'free_diagnostics'           => 'Безплатна първоначална диагностика',
	'free_diagnostics_exception' => '(освен за нестандартна техника)',

	// TRUST-01 (D3-09). The brand wordmark row, rendered by
	// includes/brand-row.php on the homepage and every service page. A flat
	// list of NAMES — never logo images: zero of the eight competitors
	// surveyed use logo files, and text wordmarks make the trademark position
	// below defensible without introducing a single figurative mark.
	//
	// ANSWERED 2026-09-11 (OWNER-QUESTIONS #22, recorded as CONTEXT D3.5-05).
	// The owner named two manufacturers that must NOT be advertised here. Both
	// may still be accepted if a customer asks; neither may be listed. One of
	// them shipped in this array until plan 03.5-01 and was a live factual
	// error on staging; the other has never been in it.
	//
	// Their names are deliberately NOT written in this comment.
	// scripts/truth-gate.js scans comments as well as markup, and a comment
	// naming them would fail the very gate that guards them — which is the
	// protection working, not a false positive. The gate's Class-A list is the
	// record; read it there.
	//
	// Six names now. The list still came from requirements drafting rather than
	// from the owner, and #22 narrowed it rather than confirming it, so it is
	// not owner-authored — do not quote it back as confirmed shop language.
	//
	// The «и др.» closer is NOT an entry here. It is emitted by the partial,
	// because it is a UI affordance meaning "this list is not exhaustive", not
	// a brand the shop services — the same discipline that forbids any
	// consumer joining the phone list back into one display string. Order is
	// the STORED order: the partial does not sort, so this line is the single
	// place the row's sequence is decided.
	'brands' => array('Lenovo', 'HP', 'Dell', 'Asus', 'Acer', 'MSI'),

	// TRUST-02 (D3-07). The Google rating badge — a styled STATIC anchor, no
	// embed, no iframe, no third-party script, no Places API call and no key.
	//
	// ###########################################################
	// ## The badge is ON, as of plan 03.5-01 (CONTEXT D3.5-07).##
	// ###########################################################
	//
	// ANSWERED (OWNER-QUESTIONS #7). All four values below were READ OFF THE
	// LIVE Google Business Profile on 2026-09-11 — not from an aggregator, not
	// from memory. The instructions that stood here told a future reader to
	// leave the badge off and said nobody had read the live figures. Both
	// statements are now false, and a false instruction in a config file is
	// worse than no instruction, so they are replaced rather than annotated.
	//
	//   * 'gbp_rating' is a DISPLAY STRING with a comma decimal separator
	//     (Bulgarian convention: never a period, never a float). It is never
	//     computed with, so formatting a float at render time on PHP 5.2, in a
	//     locale this build does not carry, would be the wrong trade — that is
	//     exactly how a «4,7» becomes a «4.70».
	//
	//   * 'gbp_reviews' is DELIBERATELY ROUNDED DOWN and carries a «+». The
	//     live count read on 2026-09-11 was higher than the figure stored
	//     below. This is the owner's explicit request (D3.5-07): a rounded
	//     floor stays true as the real count climbs, so the badge does not
	//     need a redeploy every time someone leaves a review. DO NOT
	//     "correct" it to the exact live number — that is a regression, not a
	//     fix, and it is the single most likely well-meant edit to this file.
	//
	//   * 'gbp_url' was verified to resolve on 2026-09-11. It is a
	//     developer-authored config literal and is NEVER assembled from a
	//     request value (T-03-07). It reaches the page through torin_esc() at
	//     the anchor and through the JSON encoder at the structured-data site.
	//
	// The flag and the three values are BOTH checked by the partial. The flag
	// is the deliberate switch; the emptiness checks are the safety net, so
	// blanking a figure renders nothing rather than «от отзива в Google».
	// Neither alone would be enough, and neither may be collapsed into the
	// other.
	//
	// THE ONE PARAGRAPH HERE THAT IS STILL LOAD-BEARING, AND MUST NOT BE CUT:
	// no rating or review STRUCTURED DATA accompanies this badge under any
	// schema type, ever — a business marking up reviews of itself is
	// categorically ineligible (RESEARCH P-1). The single permitted profile
	// signal is jsonld.php's sameAs, which reads 'gbp_url' below and omitted
	// the property entirely while it was empty. Filling the key is therefore
	// what puts a sameAs on every page of the site for the first time.
	'gbp_badge_enabled' => true,

	'gbp_rating'  => '4,7',
	'gbp_reviews' => '150+',
	'gbp_url'     => 'https://maps.google.com/?cid=7041654319750291392',

	// [ASSUMED] The absolute base every BreadcrumbList item URL is built from
	// (jsonld.php), because schema.org item URLs must be absolute while every
	// href in the markup stays relative.
	//
	// ###########################################################
	// ## CUTOVER GATE — this is the ONLY place the /new/       ##
	// ## staging path segment appears anywhere in src/.        ##
	// ## At Phase 4 cutover it becomes https://torin.bg/ and   ##
	// ## that one edit is the whole change.                    ##
	// ###########################################################
	//
	// The staging path MUST NEVER be hardcoded into a page file. Written into
	// 23 pages it becomes 23 edits at cutover, of which one will be missed and
	// will publish a structured-data URL pointing at a staging tree that no
	// longer exists. This is also why rel=canonical was NOT taken this phase
	// (RESEARCH OQ-5): it is a Phase 4 decision for the same reason.
	'base_url' => 'https://torin.bg/new/',

	// [ASSUMED] OWNER-QUESTIONS #8 asks whether the legacy otpuska.js
	// holiday/hours banner should survive at all. It carried genuine content
	// rather than decoration, so the safe default preserves an equivalent as
	// static PHP-rendered content instead of dropping it. Set this to an empty
	// string and the band disappears with no other edit.
	'notice' => 'Работно време: понеделник – петък, 8:00 – 16:00 ч.',
);
?>
