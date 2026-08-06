/* site.js — the entire JavaScript surface of the redesign (plan 02-03). W3C APG
   Disclosure Navigation, deliberately NOT a menu widget: no role="menu", no
   arrow keys — six links in a nav are links. No library (N-7: Alpine.js is
   ~44 KB raw to replace this file). The only DOM writes are aria-expanded and
   focus(); components.css selects all visual state off that attribute, so the
   announced state and the rendered state cannot desynchronise. */
(function () {
	'use strict';
	var nav = document.querySelector('.nav');
	if (!nav) { return; }
	var disclosures = nav.querySelectorAll('[aria-expanded][aria-controls]');

	function setExpanded(btn, open) {
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	// Closes every disclosure except the one just used AND except any whose
	// panel CONTAINS it: below 56.25rem the Услуги button lives inside the
	// panel the hamburger controls, so closing ancestors would collapse the
	// panel out from under the button the visitor just pressed.
	function closeAll(except) {
		Array.prototype.forEach.call(disclosures, function (b) {
			if (b === except) { return; }
			var panel = document.getElementById(b.getAttribute('aria-controls'));
			if (except && panel && panel.contains(except)) { return; }
			setExpanded(b, false);
		});
	}

	Array.prototype.forEach.call(disclosures, function (btn) {
		btn.addEventListener('click', function () {
			var open = btn.getAttribute('aria-expanded') === 'true';
			closeAll(btn);
			setExpanded(btn, !open);
		});
	});

	// Escape closes the innermost open disclosure and returns focus to its
	// controlling button. WCAG 2.1 SC 1.4.13 requires this; it is not polish.
	// Last in document order == innermost, so Услуги closes before the panel.
	nav.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		var open = nav.querySelectorAll('[aria-expanded="true"][aria-controls]');
		if (!open.length) { return; }
		var btn = open[open.length - 1];
		setExpanded(btn, false);
		btn.focus();
	});

	// Focus leaving the nav region closes anything still open.
	document.addEventListener('focusin', function (e) {
		if (!nav.contains(e.target)) { closeAll(null); }
	});

	// A click outside closes. No hover handler is attached at any width:
	// hover-opened menus open when a cursor passes by, and fail on touch.
	document.addEventListener('click', function (e) {
		if (!nav.contains(e.target)) { closeAll(null); }
	});
})();
