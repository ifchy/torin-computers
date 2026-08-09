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

	// [ASSUMED] OWNER-QUESTIONS #8 asks whether the legacy otpuska.js
	// holiday/hours banner should survive at all. It carried genuine content
	// rather than decoration, so the safe default preserves an equivalent as
	// static PHP-rendered content instead of dropping it. Set this to an empty
	// string and the band disappears with no other edit.
	'notice' => 'Работно време: понеделник – петък, 8:00 – 16:00 ч.',
);
?>
