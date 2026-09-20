<?php
// includes/banner.php — PHP 5.2-safe holiday/closure strip (OWNER-02, D4-27,
// UI-SPEC §C-1).
//
// EMITS NOTHING ON INCLUDE. It is a partial CALLED FROM header.php, not an
// emitting include: header.php and footer.php are the only two files in this
// tree that produce markup at include time, and this one is a function that
// header.php invokes at the exact point in the document where the strip belongs.
// Including it from anywhere else is harmless; calling it twice is not, which is
// why there is exactly one call site.
//
// IT IS THE FOOTER NOTICE IDIOM WITH A DATE GATE. footer.php:26 renders its band
// when a config value is non-empty and removes it when the value is emptied,
// with no other edit. This does the same thing with a DATE RANGE in place of the
// non-empty test, and that substitution is the whole of the auto-expiry the
// owner asked for: the strip is not switched off, it stops being rendered. He
// never has to come back to it.
//
// WHEN IT IS OFF IT EMITS NOTHING AT ALL — not an empty element, not a collapsed
// container, not a comment. That is the shipped default and it is what keeps
// every page unchanged while no closure is scheduled, so "the banner is off" and
// "the banner is broken" cannot look the same in a served page.
//
// NO SCRIPT, NO STORAGE, NO CLOSE BUTTON. D4-27 makes it non-dismissible: a
// closure is precisely the thing a visitor must not miss, so there is nothing to
// dismiss and therefore nothing to remember having dismissed. It is also NOT
// sticky — a non-dismissible strip that is also fixed is a permanent tax on the
// mobile viewport and would fight the above-the-fold arithmetic on every page
// forever. It scrolls away with the page. The whole cost of this component is
// markup and CSS.
//
// ADVANCE NOTICE IS DELIBERATELY NOT GIVEN, and this is the one behavioural
// choice here worth arguing rather than assuming. The strip renders only while
// the closure is CURRENT. A strip reading «Затворено» that appears in July for
// an August closure is a true sentence a scanning visitor reads as a false one —
// the word lands before the dates do. The structured data does not have that
// problem, because there the period declares its own validity window, so
// jsonld.php publishes the closure as soon as it is scheduled and Google knows
// early either way. If the shop later wants a visible advance notice, it is one
// comparison in this file, and the choice should be the owner's rather than a
// default nobody made.
require_once(dirname(__FILE__) . '/settings.php');
require_once(dirname(__FILE__) . '/icons.php');

// $site is passed as an ARGUMENT rather than reached for through the global
// scope. Same discipline notify.php follows with the credentials array: a
// partial that reaches outside itself for its data is a partial that renders
// differently depending on who called it.
function torin_render_banner($site) {
	if (!isset($site['vacation_from']) || !isset($site['vacation_to'])) { return; }

	$torin_from = $site['vacation_from'];
	$torin_to   = $site['vacation_to'];

	// Either date empty means OFF. An empty start date is the documented off
	// switch and is what ships; an empty end date is refused too, because a
	// closure with no end is a banner nobody would ever remove.
	if ($torin_from === '' || $torin_to === '') { return; }

	// INCLUSIVE AT BOTH ENDS, in the shop's own timezone — settings.php sets it
	// at the loader, and this comparison is the reason it does. A banner whose
	// end date is today is still shown today and is gone tomorrow.
	//
	// String comparison is correct here and is not a shortcut: both operands are
	// zero-padded ISO dates that have already passed the validator, and ISO
	// dates sort lexicographically in calendar order. Parsing them into
	// timestamps to compare them would add a timezone conversion, a DST edge and
	// a 2038 question to a problem that has none of those.
	$torin_today = date('Y-m-d');
	if ($torin_today < $torin_from || $torin_today > $torin_to) { return; }

	// The dates are the most load-bearing part of the strip, so they are
	// rendered rather than left to the owner to retype into his message. An
	// unformattable range removes the banner rather than rendering a closure
	// with no dates on it — a bare «Затворено» is worse than nothing.
	$torin_range = torin_settings_date_range($torin_from, $torin_to);
	if ($torin_range === '') { return; }

	$torin_message = isset($site['vacation_message']) ? $site['vacation_message'] : '';

	// A SECOND code-point clamp, after the validator's. The validator refuses an
	// over-long value from settings.txt, so the only way an over-long string can
	// reach here is the compiled-in default in site-config.php — which is to say,
	// a developer. Counted in CODE POINTS, never bytes: Cyrillic is two bytes per
	// character in UTF-8, so a byte cut lands mid-character and produces an
	// invalid sequence that htmlspecialchars answers with an empty string, and
	// the message vanishes entirely rather than being shortened.
	if (torin_settings_cp_len($torin_message) > 120) {
		if (function_exists('mb_substr')) {
			$torin_message = mb_substr($torin_message, 0, 120, 'UTF-8');
		} else {
			$torin_m = array();
			preg_match('/^.{0,120}/us', $torin_message, $torin_m);
			$torin_message = $torin_m[0];
		}
	}

	// Markup per UI-SPEC §C-1. A plain div and a plain p: the live-region roles
	// are FORBIDDEN here. This is static content present at first paint, not an
	// update, and either role would make a screen reader interrupt itself on
	// every one of twenty pages. The glyph is decorative and already carries its
	// own aria-hidden from torin_icon().
	//
	// The message is ESCAPED, like every other config value that reaches a page.
	// The editor is the shop owner, so this is mistake containment rather than an
	// adversarial control — but it is also what keeps the settings value TEXT.
	// If a future phase wants a link in the strip, it must add one to this
	// template; it must never make the owner's value an HTML sink.
?>
	<div class="holiday-banner">
		<div class="container holiday-banner__inner">
			<?php echo torin_icon('clock'); ?>
			<p><strong>Затворено: <?php echo htmlspecialchars($torin_range, ENT_QUOTES, 'UTF-8'); ?>.</strong><?php
				if ($torin_message !== '') { echo ' ' . htmlspecialchars($torin_message, ENT_QUOTES, 'UTF-8'); }
			?></p>
		</div>
	</div>
<?php
}
?>
