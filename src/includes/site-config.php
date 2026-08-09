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
	// ####################################################################
	// ## KNOWN DEAD LINK — G-02-5 is OPEN and BLOCKING. Do NOT ship as is. ##
	// ####################################################################
	//
	// All THREE of the shop's published numbers were tested on a real Android
	// handset against the deployed staging site, and all three fail identically
	// with Viber's "the requested page is unavailable, please update to the
	// latest version":
	//   +35929549710  (02 954 9710, landline) -> no Viber account
	//   +359879128244 (087 912 8244, mobile)  -> no Viber account
	//   +359889458404 (088 945 8404, mobile)  -> no Viber account   <- current
	//
	// The deep-link SCHEME is not the problem and must not be "fixed": a control
	// number known to have Viber was deployed briefly and opened a conversation
	// normally, so viber://chat?number= is correct. Changing it to viber://add
	// or anything else would be chasing an already-eliminated hypothesis.
	//
	// The conclusion is therefore about the business, not the code: the shop has
	// no Viber presence on any number it publishes. That turns OWNER-QUESTIONS
	// #21 from "which of the three?" into a D-16 design question — whether the
	// chat button should exist at all, and if so on what account. Trying further
	// numbers is not the answer; the owner is.
	//
	// The value below is retained only so the button renders while the question
	// is parked (owner: "we leave it for later", 2026-08-09). It is a dead end
	// for every visitor who presses it.
	'viber' => '+359889458404',

	// [ASSUMED] OWNER-QUESTIONS #8 asks whether the legacy otpuska.js
	// holiday/hours banner should survive at all. It carried genuine content
	// rather than decoration, so the safe default preserves an equivalent as
	// static PHP-rendered content instead of dropping it. Set this to an empty
	// string and the band disappears with no other edit.
	'notice' => 'Работно време: понеделник – петък, 8:00 – 16:00 ч.',
);
?>
