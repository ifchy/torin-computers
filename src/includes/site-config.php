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
//
// SOME VALUES BELOW ARE NOW DEFAULTS RATHER THAN THE LAST WORD (OWNER-01,
// D4-23). The six settings keys near the bottom of the array are overridden, one
// key at a time, by the plain-text file the owner edits in the control panel.
// The literals here are what the site renders when that file is absent, which is
// the shipped state — so this file is still the thing that decides what a page
// says, it is simply no longer the only thing that can. Read includes/settings.php
// before changing any of them; the merge that applies them runs below the array.
require_once(dirname(__FILE__) . '/settings.php');

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

	// ANSWERED 2026-09-17 (OWNER-QUESTIONS #20, recorded as CONTEXT D4-25):
	// Monday to Friday, 8:00 to 16:00, no lunch break, closed Saturday and
	// Sunday. The [ASSUMED] marker that stood here is gone because the question
	// behind it was answered, not because a marker was tidied away — the live
	// site had stated two different sets of hours across three pages and the
	// interim value here was a two-of-three majority.
	//
	// The «change BOTH» instruction that stood beside it is gone too, and that
	// is the substantive half of this edit. There is no second copy to change:
	// jsonld.php read a hard-coded clock literal and the footer's notice band
	// read a third hand-typed string, so the site could tell a search engine one
	// thing, its own footer band another, and its footer hours line a third.
	// Both derived values are now COMPOSED below from the three keys here, and
	// the notice band itself was removed entirely on 2026-09-22 — see the
	// DERIVED block below for why it never should have been made permanent.
	//
	// These three are the first of the six keys the owner's settings.txt can
	// override. They are stored in machine form — zero-padded 24-hour clock and
	// a fixed day token — because the structured data needs exactly that; every
	// human-readable form is derived below and none of them is typed anywhere.
	'hours_open'  => '08:00',
	'hours_close' => '16:00',
	'hours_days'  => 'Mo-Fr',

	// The holiday-closure band (OWNER-02, D4-27). OFF by default and off as
	// shipped: an empty start date emits nothing at all — not an empty element,
	// not a collapsed container — so every page is unchanged until the owner
	// schedules a closure. It then expires by itself the day after the end date,
	// which was his explicit requirement rather than merely an off switch.
	//
	// The message is the compiled-in fallback and is deliberately generic: it is
	// what renders if the owner sets dates but mistypes the message line. It is
	// NOT a claim that the shop is closed — the dates alone decide that.
	'vacation_from'    => '',
	'vacation_to'      => '',
	'vacation_message' => 'Сервизът е в годишен отпуск. Приемаме заявки по телефона.',

	// A chat-app deep-link key lived here until 04-04 (D4-17), together with
	// roughly a hundred lines recording why it never worked. It is GONE, not
	// dormant, and it must not come back: the five CTA slots it fed now link to
	// kontakti.html, a page in this tree. The recorded history went with it on
	// purpose — a dead value surrounded by an essay reads as a feature waiting
	// to be switched on. The full account survives in this file's git log and in
	// the Phase 2 and Phase 4 SUMMARYs, which is where a reader should look.

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

	// ANALYTICS-01 (D4-18, D4-19, D4-21). The Umami Cloud website id — the only
	// value the tracker tag needs, and NOT a secret: it ships in the HTML of
	// every page by construction, so hiding it would be theatre. What is
	// genuinely secret — the account behind it — is not in this repository at
	// all and never may be, which is the same handling 'secrets_path' below
	// describes for the Telegram credentials.
	//
	// PROVENANCE: read from Umami Cloud → Settings → Websites and supplied by
	// the shop owner on 2026-09-21, together with his sign-off on the departure
	// from the analytics product he originally asked for by name. D4-18 made
	// that sign-off a precondition rather than a courtesy; plan 04-07 Task 1 is
	// where it was given.
	//
	// FREE-TIER LIMITS, re-verified on 2026-09-21 as D4-19 requires: 100,000
	// events per month, THREE websites, six months of retention. 04-07-PLAN.md
	// says «one website» — that figure is out of date and this line is the
	// correction, recorded rather than silently carried forward. A completed
	// enquiry costs at most six events, so a local repair shop is nowhere near
	// the ceiling.
	//
	// COMMERCIAL USE: confirmed permitted BY THE OWNER on 2026-09-21, and not
	// by any public document. The pricing and terms pages render through
	// JavaScript and return nothing to a fetcher; the Cloud FAQ says only that
	// the tier suits «personal projects and low traffic websites», which is
	// marketing copy and not a restriction clause. There is no public
	// prohibition AND no public permission — the permission on record is his.
	// Do not read this line as a citation of vendor terms. 04-07-SUMMARY.md
	// carries the full provenance and the open request for the artefact behind
	// his confirmation.
	//
	// BLANK THIS STRING to remove analytics from every page with no other edit:
	// header.php emits no tracker tag at all when it is empty, and js/analytics.js
	// then finds no tracker and silently does nothing.
	'umami_website_id' => '51b8d23c-9410-4a41-8e56-3a63a3835bb2',

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

	// The ABSOLUTE server path of the credentials file, provisioned by hand in
	// cPanel File Manager on 2026-09-19 (plan 04-02 Task 1) and confirmed by the
	// developer as chmod 600. It sits ONE LEVEL ABOVE public_html, so it is
	// outside the document root, outside src/, and therefore outside every
	// deploy path this project has — a no-argument scripts/deploy-new.sh run
	// uploads everything under src/ (RESEARCH P-10) and still cannot reach it.
	//
	// THE PATH IS NOT THE SECRET AND IS DELIBERATELY RECORDED HERE. The file it
	// names returns array('telegram_bot_token' => …, 'telegram_chat_id' => …);
	// neither value is in this repository, in any shell history or on any
	// command line, and neither may ever be written into a file under src/.
	// That handling is the one scripts/deploy-new.sh:10-17 established for the
	// FTPS password, applied to a second credential (T-04-08).
	//
	// Corroboration for "outside the document root", so this is not taken on
	// trust: the account home is /home/torin/ and the document root is
	// /home/torin/public_html/ — both disclosed by the panel-generated
	// php.fcgi (04-01 Task 3, recorded in deferred-items.md).
	//
	// ONLY contact-send.php reads this key, and it is the only file in the tree
	// that may. notify.php takes the secrets array as an ARGUMENT and never
	// touches the filesystem, which is what stops any include chain from
	// header.php or footer.php reaching a credential.
	//
	// ###########################################################
	// ## CUTOVER GATE — this path is tied to the cPanel account, ##
	// ## not to the document root, so the 04-10 root cutover    ##
	// ## does NOT change it. Re-confirm the file still exists   ##
	// ## and still reads 600 after the swap; do not move it     ##
	// ## into public_html to "keep things together".            ##
	// ###########################################################
	'secrets_path' => '/home/torin/torin-secrets.php',

);

// ── The owner's settings file, merged one key at a time (OWNER-01, D4-23) ────
//
// settings.txt sits beside index.html rather than outside the document root,
// because the whole point is that the owner reaches it in the control panel's
// file manager without being told a server path. It is DENIED over HTTP in
// src/.htaccess; that deny block and this path are a pair, and moving either
// without the other publishes the file.
//
// THE PATH IS COMPUTED, NEVER HARD-CODED. includes/ is a direct child of the
// site root on staging and will be again at the root cutover, so this resolves
// correctly in both without an edit — which is one fewer thing for the cutover
// checklist to miss.
//
// THE EXAMPLE FILE SHIPS; THE REAL FILE NEVER DOES. A no-argument deploy uploads
// everything beneath src/, so a settings.txt committed here would overwrite
// whatever the owner had typed on the server, on every deploy, silently
// (T-04-34). src/settings.txt must not exist in this repository. The parser
// treats an absent file as a non-error precisely so that shipping only the
// example is safe.
$site['settings_path'] = dirname(dirname(__FILE__)) . '/settings.txt';

// The six keys the owner may override, and the ONLY six. A key absent from this
// list cannot be set from settings.txt even if the parser accepted it.
$torin_settings_managed = array(
	'hours_open', 'hours_close', 'hours_days',
	'vacation_from', 'vacation_to', 'vacation_message'
);

$torin_settings_read = torin_read_settings($site['settings_path']);

// PER-KEY, in a loop, with the fallen-back keys recorded rather than merely
// tolerated. A value the owner's file did not supply — because the file is
// absent, or because that one line was malformed — leaves the literal above in
// place and its name in this list. footer.php emits the list as an HTML comment
// so a remote check can assert zero UNEXPECTED fallbacks; see the sentinel
// argument in includes/settings.php's header. With no settings.txt on the
// server every key is listed here, and that is the correct reading of the
// shipped state rather than an alarm.
$site['settings_fallbacks'] = array();
foreach ($torin_settings_managed as $torin_settings_key) {
	if (isset($torin_settings_read[$torin_settings_key])) {
		$site[$torin_settings_key] = $torin_settings_read[$torin_settings_key];
	} else {
		$site['settings_fallbacks'][] = $torin_settings_key;
	}
}

// ── Everything below is DERIVED. Nothing here may be hand-edited into a literal.
//
// This block is the whole of D4-26. The working hours are written once, in
// machine form, and every human-readable and machine-readable consumer is
// composed from that one value: the footer's hours line, the contact page's
// hours line and the structured data Google reads. The owner cannot change one
// and leave another behind, because there is no other to leave behind.
//
// A FOURTH consumer stood here until 2026-09-22 — the footer's «notice» band —
// and it is gone by owner decision, not by refactor. It descended from the
// hours half of the legacy otpuska.js, which announced «НОВО Работно време» as
// a TEMPORARY notice that the hours had changed. Carried across permanently and
// stripped of its «НОВО», it just restated the hours line thirty-four lines
// above it in the same footer. The closure half of otpuska.js is alive and well
// as banner.php, which is what OWNER-QUESTIONS #8 actually approved.
$torin_days = torin_settings_days($site['hours_days']);

$site['hours_days_bg']       = $torin_days['bg'];
$site['hours_days_open']     = $torin_days['open'];
$site['hours_days_closed']   = $torin_days['closed'];
$site['hours_open_display']  = torin_hours_display($site['hours_open']);
$site['hours_close_display'] = torin_hours_display($site['hours_close']);

// «Понеделник – Петък, 8:00 – 16:00» — the exact shape this site has rendered
// since Phase 2, now assembled rather than typed. The en dash with spaces is the
// site's existing convention and is used in both positions.
$site['hours'] = $torin_days['bg'] . ', '
	. $site['hours_open_display'] . ' – ' . $site['hours_close_display'];


// Every page on the site includes this file, so each of the four names above is
// squatted in that page's GLOBAL scope. That is not a theoretical tidiness
// point here: header.php:33-41 records a live defect where exactly this
// happened, four Phase 3 pages lost their own $page array to an include's
// assignment, and every affected page still returned HTTP 200 while rendering a
// single letter where its prose belonged. Nothing outside this block needs these
// four, so they do not survive it.
unset($torin_settings_managed, $torin_settings_read, $torin_settings_key, $torin_days);
?>
