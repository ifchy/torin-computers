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

	// The render timestamp for the time trap (UI-SPEC C-6). The tracer only
	// PASSES IT THROUGH — 04-05 signs it with HMAC and verifies the window.
	//
	// Deliberately re-read from server time on every render, including the
	// error re-render, rather than carried forward out of $values. Until 04-05
	// signs it, a carried-forward value is an attacker-controlled string that
	// nothing verifies; re-emitting server time keeps the one writer of this
	// field on the server for as long as it is unverified. The cost is that a
	// visitor who fails validation restarts their own clock, which is the
	// harmless direction to be wrong in — the lower bound exists to catch bots,
	// and a human who just filled a form in is past it either way.
	//
	// COUPLED TO kontakti.html's Cache-Control: no-store (RESEARCH P-11). If
	// that header is ever dropped, an intermediary hands two visitors the same
	// timestamp and the 04-05 window starts rejecting real people.
	$torin_t = time();
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
				      // aria-describedby names BOTH the help node and the error
				      // node unconditionally. Toggling the id list is a standard
				      // source of stale references; the `hidden` attribute on the
				      // node is what changes instead. ?>
				<div class="field<?php echo ($torin_e_device !== '' ? ' field--invalid' : ''); ?>">
					<label class="field__label" for="device">Модел на устройството <span class="field__req" aria-hidden="true">*</span></label>
					<input class="field__control" id="device" name="device" type="text" maxlength="120" autocomplete="off" required aria-describedby="device-help device-err"<?php echo ($torin_e_device !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, попълнете полето." value="<?php echo torin_esc($torin_v_device); ?>">
					<p class="field__help" id="device-help">Например Lenovo ThinkPad T480. Ако не знаете модела, опишете с думи.</p>
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
					<textarea class="field__control" id="fault" name="fault" rows="5" maxlength="1024" required aria-describedby="fault-help fault-err"<?php echo ($torin_e_fault !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, опишете повредата."><?php echo torin_esc($torin_v_fault); ?></textarea>
					<p class="field__help" id="fault-help">Кога започна, какво сте опитали, какво чувате или виждате.</p>
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
					      // makes it read as an empty input. It therefore stays
					      // NATIVE and unstyled in this tracer; the
					      // ::file-selector-button styling and the .filelist
					      // preview row are 04-03/04-04's, and neither needs this
					      // markup to change. The alternative — a --file modifier
					      // that spends CSS budget undoing the base rule it just
					      // inherited — is how a component ends up with a variant
					      // whose whole job is cancelling another variant. ?>
					<input id="photos" name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" aria-describedby="photos-help photos-err"<?php echo ($torin_e_photos !== '' ? ' aria-invalid="true"' : ''); ?>>
					<p class="field__help" id="photos-help">Не е задължително. До 5 снимки, всяка до 10 MB. Смаляваме ги автоматично преди изпращане.</p>
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
					<input class="field__control" id="phone" name="phone" type="tel" inputmode="tel" maxlength="40" autocomplete="tel" required aria-describedby="phone-help phone-err"<?php echo ($torin_e_phone !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, въведете телефон за връзка." value="<?php echo torin_esc($torin_v_phone); ?>">
					<p class="field__help" id="phone-help">За да ви върнем обаждане.</p>
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
					<input class="field__control" id="email" name="email" type="email" inputmode="email" maxlength="254" autocomplete="email" required aria-describedby="email-help email-err"<?php echo ($torin_e_email !== '' ? ' aria-invalid="true"' : ''); ?> data-err="Моля, въведете имейл." value="<?php echo torin_esc($torin_v_email); ?>">
					<p class="field__help" id="email-help">Ще получите потвърждение, че запитването е стигнало до нас.</p>
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
<?php
}
?>
