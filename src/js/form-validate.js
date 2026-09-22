/* form-validate.js — kontakti.html only. Submit-time validation.

   AN ENHANCEMENT, NEVER THE ENFORCEMENT POINT. contact-send.php validates
   every field again, unconditionally, and is the one writer of the messages a
   visitor is finally judged by. With scripting off this file simply does not
   run and the form posts as it always did.

   WHY SUBMIT AND NOT AS-YOU-TYPE (owner, 2026-09-22): validating on blur or on
   input tells people they are wrong while they are still answering. Nothing is
   flagged until the visitor says they are done by pressing the button. After
   that first rejection a flagged field re-checks live, so its message clears
   the moment it is satisfied rather than surviving until the next press — but
   a field that was never invalid is never flagged.

   THE MESSAGES ARE NOT WRITTEN HERE. Each control carries data-err with the
   exact Bulgarian string the SERVER would produce for the same failure, so the
   two cannot drift. Adding a message in this file means the client and the
   server disagree about what is wrong, and the visitor sees the wording change
   when the page reloads.

   EMAIL IS CHECKED FOR EMPTINESS ONLY, deliberately. The server distinguishes
   empty from malformed via FILTER_VALIDATE_EMAIL; re-implementing that here
   guarantees drift, and a client-side regex that rejects a deliverable address
   costs a real enquiry. Malformed-but-present goes to the server and comes
   back with the server's own wording.

   ORDER MATTERS AGAINST photo-resize.js — see the comment in contact-form.php
   where both scripts are emitted. Sparse comments below: this ships to every
   visitor of the page. */
(function () {
	'use strict';

	var form = document.getElementById('contact-form');
	if (!form) { return; }

	// The required set, in DOM order — focus lands on the first failure and
	// "first" has to mean first on the page, not first in an arbitrary list.
	var NAMES = ['device', 'fault', 'name', 'phone', 'email', 'consent'];

	var fields = [];
	for (var i = 0; i < NAMES.length; i++) {
		var control = document.getElementById(NAMES[i]);
		var error = document.getElementById(NAMES[i] + '-err');
		// A missing node is not an excuse to guess. Skip it and let the server
		// hold the line for that field.
		if (control && error) {
			// closest() is guarded the way analytics.js:40 guards it. Every
			// consumer of .wrap is null-checked, so the absence degrades to "no
			// wrapper hook" rather than to a TypeError that would take the whole
			// validator down and leave the form with no client-side checks at all.
			fields.push({
				control: control,
				error: error,
				wrap: control.closest ? control.closest('.field') : null
			});
		}
	}
	if (!fields.length) { return; }

	// Start "already submitted" when the server re-rendered with errors: those
	// fields are flagged in the markup, so they must clear live too. Read from
	// the error nodes rather than a query string — the re-render is a POST.
	var submitted = fields.some(function (f) { return !f.error.hidden; });

	function ok(f) {
		if (f.control.type === 'checkbox') { return f.control.checked; }
		return f.control.value.trim() !== '';
	}

	function mark(f, valid) {
		// Toggle the `hidden` attribute and aria-invalid — never the
		// aria-describedby id list, which stays pointing at both nodes for the
		// life of the page. A toggled id list is how references go stale.
		f.error.hidden = valid;
		if (valid) {
			f.control.removeAttribute('aria-invalid');
			if (f.wrap) { f.wrap.classList.remove('field--invalid'); }
		} else {
			f.control.setAttribute('aria-invalid', 'true');
			if (f.wrap) { f.wrap.classList.add('field--invalid'); }
			// Restore the generic message if the server left a specific one:
			// once the visitor edits the field, the server's verdict describes a
			// value that no longer exists.
			var generic = f.control.getAttribute('data-err');
			var slot = f.error.querySelector('span');
			if (generic && slot) { slot.textContent = generic; }
		}
	}

	function recheck(f) {
		return function () {
			if (!submitted) { return; }
			mark(f, ok(f));
		};
	}

	for (var j = 0; j < fields.length; j++) {
		fields[j].control.addEventListener('input', recheck(fields[j]));
		fields[j].control.addEventListener('change', recheck(fields[j]));
	}

	form.addEventListener('submit', function (e) {
		submitted = true;

		var first = null;
		for (var k = 0; k < fields.length; k++) {
			var valid = ok(fields[k]);
			mark(fields[k], valid);
			if (!valid && !first) { first = fields[k].control; }
		}
		if (!first) { return; }

		e.preventDefault();
		// stopImmediatePropagation, not just preventDefault: photo-resize.js has
		// its own submit listener on this form that disables the button and
		// relabels it. Without this the button locks as «Изпраща се…» on a
		// submission that is not happening.
		e.stopImmediatePropagation();

		first.focus();
	});
})();
