<?php
// includes/contact-form.php — PHP 5.2-safe (array() only, named functions
// only, no namespaces, no short echo tags, tabs). Emits nothing on include:
// it is ONE function definition and no top-level output, exactly like
// brand-row.php, category-page.php, categories.php and site-config.php.
//
// That include-boundary property is not cosmetic here, it is what makes the
// file usable at all. UI-SPEC C-3 requires ONE partial with TWO callers —
// kontakti.html renders the empty state and contact-send.php re-renders the
// populated error state from the same markup. A second copy of a seven-field
// form is how the two renderings start to disagree about their own field
// names, and a field name that disagrees with the handler is a silently
// dropped enquiry. If this file ever grows top-level output, contact-send.php
// can no longer include it before deciding what to send, and the second caller
// has to fork the markup. Keep it one function and no output.
//
// CONTACT-03 / CONTACT-05 / CONTACT-06. UI-SPEC C-3 (form element, field
// order, field anatomy), C-5 (consent row), C-6 (honeypot and time trap).
//
// torin_esc() and torin_render_breadcrumbs() live in category-page.php, which
// is required below for the former. The mutual-require note in brand-row.php
// :39-46 applies unchanged: function BODIES resolve their callees at CALL
// time, so the require order cannot strand a definition. Duplicating
// torin_esc() here instead would be the same two-writers defect this project
// refuses everywhere else.
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
require_once(dirname(__FILE__) . '/category-page.php');
// asset-version.php is required EXPLICITLY, not relied on from header.php.
// This file's one guarantee is that it works for both its callers, and a
// helper that happens to be loaded because some other include pulled it in is
// a dependency that holds right up until the include order changes.
require_once(dirname(__FILE__) . '/asset-version.php');
// spam-guard.php is required for torin_sign_timestamp() and torin_guard_secret()
// alone. It is functions and no data, it opens no credential — the key it uses
// is its own, created in private storage for exactly this reason
// (spam-guard.php:109-131) — and it emits nothing on include, so this file's
// own one-function-no-output contract survives the addition.
require_once(dirname(__FILE__) . '/spam-guard.php');

// $values repopulates the controls on the error re-render; $errors maps a
// field name to the Bulgarian message the SERVER produced for it. Both default
// to empty so the first render needs no arguments it does not have.
//
// THE ERROR RE-RENDER PATH DOES NOT SHIP UNTIL 04-05 — contact-send.php calls
// this with empty arrays today. The repopulation and escaping are written NOW
// anyway, because writing them later means writing them under the pressure of
// a half-finished second caller, and UI-SPEC C-8 names this exact branch as
// the one where the escaping convention historically breaks. Every echoed
// value below goes through torin_esc() on BOTH branches; there is no branch
// where a submitted string reaches an attribute unescaped (T-04-07).
function torin_render_contact_form($values = array(), $errors = array()) {
	global $site;

	// Read helpers. Written as plain lookups rather than a second function
	// because this file is contracted to define exactly one.
	$torin_v_device = isset($values['device']) ? $values['device'] : '';
	$torin_v_fault  = isset($values['fault'])  ? $values['fault']  : '';
	$torin_v_name   = isset($values['name'])   ? $values['name']   : '';
	$torin_v_phone  = isset($values['phone'])  ? $values['phone']  : '';
	$torin_v_email  = isset($values['email'])  ? $values['email']  : '';

	$torin_e_device  = isset($errors['device'])  ? $errors['device']  : '';
	$torin_e_fault   = isset($errors['fault'])   ? $errors['fault']   : '';
	$torin_e_photos  = isset($errors['photos'])  ? $errors['photos']  : '';
	$torin_e_name    = isset($errors['name'])    ? $errors['name']    : '';
	$torin_e_phone   = isset($errors['phone'])   ? $errors['phone']   : '';
	$torin_e_email   = isset($errors['email'])   ? $errors['email']   : '';
	$torin_e_consent = isset($errors['consent']) ? $errors['consent'] : '';

	// The render timestamp for the time trap (UI-SPEC C-6), SIGNED as of
	// 04-05: the field stopped being the tracer's pass-through and now carries
	// «<unix time>.<keyed SHA-256 HMAC of it>». contact-send.php re-signs the
	// claimed time and compares in constant time, so the value cannot be
	// back-dated to walk past the lower bound or forward-dated to survive the
	// upper one. The signature is what lets the window be stateless — no
	// server-side record of this render exists, and none is wanted, because
	// keeping one means a cookie and a cookie re-opens the consent question
	// D4-18 was chosen to close.
	//
	// Deliberately re-read from server time on every render, including the
	// error re-render, rather than carried forward out of $values. A
	// carried-forward value would hand the submitter control of their own
	// clock — the signature makes forgery hard, but re-emitting it costs
	// nothing and keeps the server the one writer of this field. The visitor
	// who fails validation therefore restarts their own clock, which is the
	// harmless direction to be wrong in: the lower bound exists to catch bots,
	// and a human who just filled a form in is past it either way.
	//
	// COUPLED TO kontakti.html's Cache-Control: no-store (RESEARCH P-11). If
	// that header is ever dropped, an intermediary hands two visitors the same
	// timestamp — and every one of them past the first would be judged stale
	// and told to open the page again. The header is half of this mechanism,
	// not a cache-tuning preference.
	$torin_t = torin_sign_timestamp(time(), torin_guard_secret());
?>
			<form class="form" id="contact-form" action="contact-send.php" method="post" enctype="multipart/form-data" novalidate>

				<?php // 1 — Модел на устройството. The form opens on what the
				      // visitor came to say, not on who they are: asking for a
				      // phone number first reads as a gate (UI-SPEC C-3).
				      //
				      // required, type and autocomplete are all KEPT even though
				      // novalidate suppresses the native bubbles. They still drive
				      // the mobile keyboard, autofill and the accessible required
				      // state. Only the BUBBLES are suppressed, and only because
				      // they render in the browser's locale — English error
				      // bubbles on a site whose hard constraint is Bulgarian only.
				      //
				      // aria-describedby names the error node unconditionally.
				      // Toggling the id list is a standard source of stale
				      // references; the `hidden` attribute on the node is what
				      // changes instead — and as of 2026-09-22 that attribute is
				      // finally binding (base.css `[hidden]`), which it had never
				      // been while `.field__error { display: flex }` outranked the
				      // UA rule.
				      //
				      // THE HINT IS A PLACEHOLDER, NOT A LINE OF ITS OWN (owner,
				      // 2026-09-22: «too much information … hard to say what is
				      // going on»). The visible <label> STAYS. A placeholder that
				      // replaces its label is the well-known accessibility defect
				      // — the field loses its name as soon as it has a value, for
				      // sighted and assistive users alike. Here the label names
				      // the field and the placeholder only shows the shape of an
				      // answer, which is the one division of labour that survives
				      // the visitor starting to type.
				      //
				      // Shortened from the old help line: «Ако не знаете модела,
				      // опишете с думи» was reassurance rather than an example,
				      // and a placeholder long enough to clip mid-word at 360px
				      // teaches nothing. ?>
				<div class="field<?php echo ($torin_e_device !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="device">Модел на устройството <span class="field__req" aria-hidden="true">*</span></label>
					<input class="field__control" id="device" name="device" type="text" maxlength="120" autocomplete="off" required placeholder="Например Lenovo ThinkPad T480" aria-describedby="device-err"<?php echo ($torin_e_device !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, попълнете полето." value="<?php echo torin_esc($torin_v_device); ?>">
					<p class="field__error" id="device-err"<?php echo ($torin_e_device !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_device !== '' ? $torin_e_device : 'Моля, попълнете полето.'); ?></span></p>
				</div>

				<?php // 2 — Какво прави устройството. maxlength is clamped to
				      // 1024 because that is Telegram's caption ceiling, and
				      // 04-03 sends this text as the caption of the first photo
				      // when photos are attached. Clamping in the markup is a
				      // courtesy, never the enforcement point — contact-send.php
				      // bounds the length again server-side and unconditionally. ?>
				<div class="field<?php echo ($torin_e_fault !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="fault">Какво прави устройството <span class="field__req" aria-hidden="true">*</span></label>
					<textarea class="field__control" id="fault" name="fault" rows="5" maxlength="1024" required placeholder="Кога започна, какво сте опитали, какво чувате или виждате" aria-describedby="fault-err"<?php echo ($torin_e_fault !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, опишете повредата."><?php echo torin_esc($torin_v_fault); ?></textarea>
					<p class="field__error" id="fault-err"<?php echo ($torin_e_fault !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_fault !== '' ? $torin_e_fault : 'Моля, опишете повредата.'); ?></span></p>
				</div>

				<?php // 3 — Снимки. OPTIONAL (D4-12), and placed HERE rather than
				      // at the end on purpose: this is the moment «покажете ни»
				      // makes sense, and a clearly optional field placed mid-form
				      // is abandoned far less often than one at the end, where it
				      // reads as a final obstacle.
				      //
				      // The control ships NATIVE and complete in this tracer. The
				      // SERVER side lands in 04-03 behind the same handler call,
				      // and the JS downscale in 04-04 — neither changes this
				      // markup, which is the test of a legitimate tracer stub.
				      //
				      // accept= is a hint to the picker and NEVER the enforcement
				      // point; so is the extension, and so is $_FILES[…]['type'].
				      // 04-03 verifies content, not claims.
				      //
				      // No .field__req marker and no `required`: the label says
				      // «(по избор)» and the two must not contradict each other. ?>
				<div class="field<?php echo ($torin_e_photos !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="photos">Снимки на повредата (по избор)</label>
					<?php // NO .field__control class on this one control, and that is
					      // deliberate rather than an omission. UI-SPEC C-4 is
					      // explicit that the file input must sit on the PAGE
					      // background and not on the --c-surface-3 input fill —
					      // it is a button, not a text field, and a field fill
					      // makes it read as an empty input. It stays NATIVE and
					      // is styled through ::file-selector-button in
					      // components.css (04-03); the visually-hidden-input
					      // plus styled-label pattern is forbidden here because
					      // it routinely loses the focus ring, which base.css
					      // says is never suppressed. The alternative — a --file
					      // modifier that spends CSS budget undoing the base rule
					      // it just inherited — is how a component ends up with a
					      // variant whose whole job is cancelling another. ?>
					<input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" aria-describedby="photos-help photos-err"<?php echo ($torin_e_photos !== '' ? ' aria-invalid="true"' : ''); ?>>
					<?php // THE ONE HELP LINE THAT STAYS, because a file input cannot
					      // carry a placeholder — there is no text field to put one
					      // in. Trimmed to the two facts a visitor acts on: «Не е
					      // задължително» repeated the label's «(по избор)», and
					      // the auto-downscale promise was reassurance about
					      // something that happens either way. ?>
					<p class="field__help" id="photos-help">До 5 снимки, всяка до 10 MB.</p>
					<?php // The remove glyph for the JS-rendered .filelist rows,
					      // parked in an inert <template> so icons.php stays the
					      // ONE writer of every glyph in this project. The
					      // alternative is pasting the SVG path into
					      // photo-resize.js, where it becomes the eighteenth icon
					      // that nobody knows exists and the first one that does
					      // not change when the set does.
					      //
					      // <template> renders nothing, so with scripting off it
					      // costs a visitor the bytes and nothing else — and
					      // .filelist never renders either, which is the whole
					      // no-JS contract for this control. ?>
					<template id="photos-icon"><?php echo torin_icon('close'); ?></template>
					<p class="field__error" id="photos-err"<?php echo ($torin_e_photos !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_photos !== '' ? $torin_e_photos : 'Може да прикачите най-много 5 снимки.'); ?></span></p>
				</div>

				<?php // 4 — Вашето име. By this point the visitor has already
				      // described the fault, which is the whole mechanism behind
				      // the field order: the effort is already invested. ?>
				<div class="field<?php echo ($torin_e_name !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="name">Вашето име <span class="field__req" aria-hidden="true">*</span></label>
					<input class="field__control" id="name" name="name" type="text" maxlength="120" autocomplete="name" required aria-describedby="name-err"<?php echo ($torin_e_name !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, въведете името си." value="<?php echo torin_esc($torin_v_name); ?>">
					<p class="field__error" id="name-err"<?php echo ($torin_e_name !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_name !== '' ? $torin_e_name : 'Моля, въведете името си.'); ?></span></p>
				</div>

				<?php // 5 — Телефон. inputmode is what puts a phone keypad under
				      // the field on a handset; type="tel" alone does not
				      // guarantee it. No format is imposed — a visitor with a
				      // broken laptop should not be arguing with a phone mask. ?>
				<div class="field<?php echo ($torin_e_phone !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="phone">Телефон <span class="field__req" aria-hidden="true">*</span></label>
					<?php // NO PLACEHOLDER AND NO HELP LINE. «За да ви върнем
					      // обаждане» was a reassurance, not an entry hint, and the
					      // owner chose to drop it rather than have it rewritten
					      // into a fake example (2026-09-22). An example number
					      // would also be the one thing capable of implying a
					      // format on a field that deliberately imposes none. ?>
					<input class="field__control" id="phone" name="phone" type="tel" inputmode="tel" maxlength="40" autocomplete="tel" required aria-describedby="phone-err"<?php echo ($torin_e_phone !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, въведете телефон за връзка." value="<?php echo torin_esc($torin_v_phone); ?>">
					<p class="field__error" id="phone-err"<?php echo ($torin_e_phone !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_phone !== '' ? $torin_e_phone : 'Моля, въведете телефон за връзка.'); ?></span></p>
				</div>

				<?php // 6 — Имейл. Two distinct server messages exist for this
				      // field (empty, and malformed); data-err carries the
				      // REQUIRED one, because that is the only check a
				      // client-side validator can make without re-implementing
				      // FILTER_VALIDATE_EMAIL and drifting from it. The server
				      // remains the one writer of both strings. ?>
				<div class="field<?php echo ($torin_e_email !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="email">Имейл <span class="field__req" aria-hidden="true">*</span></label>
					<?php // The «Ще получите потвърждение…» line is dropped here by
					      // the same owner decision. The promise itself is NOT
					      // lost — contact-send.php actually sends that
					      // confirmation, and the success page is where a visitor
					      // reads about it at the moment it becomes true, rather
					      // than as a fourteenth line of small print beside an
					      // empty field. ?>
					<input class="field__control" id="email" name="email" type="email" inputmode="email" maxlength="254" autocomplete="email" required aria-describedby="email-err"<?php echo ($torin_e_email !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, въведете имейл." value="<?php echo torin_esc($torin_v_email); ?>">
					<p class="field__error" id="email-err"<?php echo ($torin_e_email !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_email !== '' ? $torin_e_email : 'Моля, въведете имейл.'); ?></span></p>
				</div>

				<?php // 7 — Съгласие (CONTACT-06, UI-SPEC C-5). Last, immediately
				      // above the submit, where it reads as part of submitting
				      // rather than as a checkpoint.
				      //
				      // RENDERS UNCHECKED UNCONDITIONALLY — $values is NOT read
				      // here, and that omission is the feature. A re-render that
				      // restores a previously-ticked box is pre-ticked consent by
				      // another name; consent has to be a deliberate act every
				      // time the form is submitted. Do not "fix" this by adding
				      // the checked ternary every other field has.
				      //
				      // The link INSIDE the label is correct and deliberate: the
				      // HTML specification excludes interactive content
				      // descendants from a label's activation behaviour, so
				      // clicking «условията…» navigates and does NOT toggle the
				      // box. Moving the link out of the label to "fix" this would
				      // break the sentence for no gain. ?>
				<div class="field consent<?php echo ($torin_e_consent !== '' ? ' field--invalid' : ''); ?>">
					<input class="consent__box" id="consent" name="consent" type="checkbox" value="1" required aria-describedby="consent-err"<?php echo ($torin_e_consent !== '' ? ' aria-invalid="true"' : ''); ?> data-err="За да изпратите запитването, трябва да приемете условията.">
					<label class="consent__label" for="consent">Прочетох и приемам <a href="uslovia.html">условията за обработка на личните ми данни</a>.</label>
					<p class="field__error" id="consent-err"<?php echo ($torin_e_consent !== '' ? '' : ' hidden'); ?>><?php echo torin_icon('alert'); ?><span><?php echo torin_esc($torin_e_consent !== '' ? $torin_e_consent : 'За да изпратите запитването, трябва да приемете условията.'); ?></span></p>
				</div>

				<?php // The honeypot (UI-SPEC C-6). Everything about this block is
				      // load-bearing and none of it is hygiene:
				      //
				      // · the name is PLAUSIBLE («website»), never «honeypot» or
				      //   «leaveblank» — the name is in the served HTML;
				      // · the hiding is the .hp rule in components.css and NEVER
				      //   an inline style, because style="display:none" on a form
				      //   field is the single most recognisable trap signature a
				      //   scraper can match on;
				      // · aria-hidden on the WRAPPER removes label and input from
				      //   the accessibility tree together, so a screen-reader user
				      //   is never asked for a website;
				      // · tabindex="-1" keeps a keyboard user from landing in it;
				      // · autocomplete="off" is the one that actually matters. A
				      //   password manager or browser autofill filling a hidden
				      //   field is the NUMBER-ONE source of honeypot false
				      //   positives, and a false positive here silently discards a
				      //   real customer's enquiry — the worst failure this form
				      //   can produce, because nobody ever sees it happen. ?>
				<div class="hp" aria-hidden="true">
					<label for="website">Уебсайт</label>
					<input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
				</div>
				<input type="hidden" name="t" value="<?php echo torin_esc($torin_t); ?>">

				<button class="btn btn--primary" type="submit">Изпратете запитване</button>

				<?php // The required-fields legend sits UNDER the submit (UI-SPEC
				      // C-3), and reuses .field__help rather than introducing a
				      // .form__legend of its own. The two want identical
				      // treatment — body size, --c-ink-muted — because this
				      // design system deliberately has no fifth type size and
				      // differentiates secondary text by colour alone
				      // (base.css:201-203). A second class here would be one more
				      // name to keep in sync with .field__help for zero visual
				      // difference, against a 1.5 KB gzipped budget for the whole
				      // phase. ?>
				<p class="field__help">Полетата със * са задължителни.</p>
			</form>

			<?php // THE PAGE-SCOPED SCRIPT, EMITTED FROM THE PARTIAL THAT OWNS
			      // THE MARKUP IT DRIVES — not from header.php, which is shared
			      // by twenty pages, and where one page's enhancement becomes
			      // nineteen pages' dead download. Emitting it here means the
			      // script and the DOM it needs cannot be deployed apart.
			      //
			      // In the body rather than the head, because the shared head
			      // offers a page no hook for arbitrary markup. It carried one
			      // until 04-07 — a dev-only slot the theme switcher owned,
			      // reset on every include so a page could not use it — and
			      // that slot is now deleted outright rather than promoted into
			      // a page hook. A deferred script with a src is deferred
			      // wherever it sits, so this placement costs nothing.
			      //
			      // torin_asset_url() stamps it with the deployed file's mtime,
			      // the same invalidation every other asset here gets. ?>
			<?php // ORDER IS LOAD-BEARING: form-validate BEFORE photo-resize.
			      // Deferred scripts execute in document order, so listener
			      // registration follows markup order, and the validator must get
			      // the submit event FIRST.
			      //
			      // photo-resize.js:130 registers its own submit listener which
			      // disables the button and relabels it «Изпраща се…» /
			      // «Подготвяме снимките…». If the validator rejected a submit
			      // without also stopping that listener, the button would lock
			      // disabled on a form that is not being sent — a dead page, worse
			      // than the clutter this change set out to fix. The validator
			      // therefore calls stopImmediatePropagation() as well as
			      // preventDefault() on rejection; the two are one mechanism here,
			      // not belt and braces.
			      //
			      // photo-resize.js:114 then calls form.submit(), which does NOT
			      // fire the submit event. That is correct and must stay correct:
			      // validation already ran on the user-initiated submit that
			      // started the resize, and a second check there would be
			      // unreachable. ?>
			<script src="<?php echo torin_esc(torin_asset_url('js/form-validate.js')); ?>" defer></script>
			<script src="<?php echo torin_esc(torin_asset_url('js/photo-resize.js')); ?>" defer></script>
<?php
}
?>
