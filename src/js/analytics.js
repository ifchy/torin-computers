/* analytics.js — every analytics event on this site, and the ONLY mechanism
   permitted to fire one (UI-SPEC C-9). Style follows site.js: IIFE, strict,
   var, ES5-safe, delegated at the document.
   THIS FILE IS HELD TO A 1 KB GZIPPED WIRE BUDGET (UI-SPEC §Performance
   Budget) and does not meet it — 04-07-SUMMARY.md records the measurement and
   why. Every comment kept below exists to stop a specific future edit from
   reintroducing a defect; the rest of the reasoning is in UI-SPEC C-8/C-9. */
(function () {
	'use strict';

	// WHY THIS FILE EXISTS rather than the tracker's declarative click
	// attribute: for any anchor carrying that attribute the tracker cancels
	// the default, beacons, then re-navigates via location.href — putting the
	// dialer behind a third-party round trip in a task with no user gesture.
	// A desktop test PASSES that defect, because a phone link does nothing
	// there either way. So that attribute appears nowhere in this tree, and
	// nothing below cancels a default or waits on anything.

	// Properties are a CLOSED SET, enforced rather than promised: a value
	// outside these two lists is DROPPED, so no submitted text, free text or
	// identifier can reach the vendor even if later markup carries one.
	var SLOTS = ' hero cta callbar category kontakti footer footer-list footer-cta ';
	var FIELDS = ' device fault photos name phone email consent ';

	function ok(list, v) { return !!v && list.indexOf(' ' + v + ' ') > -1; }

	function track(name, key, value) {
		if (!window.umami || !window.umami.track) { return; }
		if (!key) { window.umami.track(name); return; }
		var props = {};
		props[key] = value;
		window.umami.track(name, props);
	}

	// passive, so the browser may never wait on this handler, and capturing,
	// so the click is seen before anything can stop it propagating. Neither
	// option may be dropped. The raw href ATTRIBUTE is read, never the
	// resolved property: the contract is written against what the markup says.
	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a') : null;
		if (!a) { return; }
		var href = a.getAttribute('href') || '';
		var slot = a.getAttribute('data-slot');
		if (!ok(SLOTS, slot)) { return; }
		if (href.indexOf('tel:') === 0) { track('call-click', 'slot', slot); }
		else if (href === 'kontakti.html') { track('write-click', 'slot', slot); }
	}, { capture: true, passive: true });

	// The two SERVER-rendered events: msg.html and the error re-render declare
	// them on <body>, so neither is inferred from the client.
	var declared = (document.body.getAttribute('data-track') || '').split(' ');
	for (var i = 0; i < declared.length; i++) {
		var part = declared[i].split(':');
		if (part[0] === 'form-sent') { track('form-sent'); }
		else if (part[0] === 'form-error' && ok(FIELDS, part[1])) { track('form-error', 'field', part[1]); }
	}

	// NOT ANALYTICS, and not stray — do not delete it as out of place. UI-SPEC
	// C-8's error band has carried tabindex="-1" since 04-05 with nothing to
	// focus it, so a keyboard or screen-reader user landed at the top of the
	// document rather than on the explanation of why their enquiry failed.
	var band = document.getElementById('form-error');
	if (band) { band.focus(); }

	var form = document.getElementById('contact-form');
	if (!form) { return; }

	// The funnel, each step at most ONCE per page and none carrying a
	// property. The step where the count falls is the step that loses people.
	var fired = {};
	function once(name) {
		if (fired[name]) { return; }
		fired[name] = 1;
		track(name);
	}

	// Capturing, because a focus event does not bubble.
	form.addEventListener('focus', function () { once('form-start'); }, true);

	form.addEventListener('change', function (e) {
		var t = e.target;
		if (t.id === 'photos' && t.files && t.files.length) { once('form-photos'); }
		if (t.id === 'consent' && t.checked) { once('form-consent'); }
	});

	form.addEventListener('submit', function () { once('form-submit'); });
})();
