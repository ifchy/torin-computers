<?php
// includes/rating-badge.php — PHP 5.2-safe (array() only, named functions only,
// no namespaces, no short echo tags, tabs). Emits nothing on include: one
// function definition and no top-level output.
//
// TRUST-02 / D3-07 — the Google rating badge.
//
// ############################################################################
// ## THE ABSENT STATE IS SPECIFIED FIRST, IN THIS COMMENT AND IN THE CODE   ##
// ## BELOW, BECAUSE IT IS THE STATE THAT SHIPS TODAY.                       ##
// ############################################################################
//
// When the badge is switched off, OR the profile URL, the rating or the review
// count is empty, this function emits NOTHING AT ALL. Not a skeleton. Not a
// placeholder. Not a greyed-out pill. Not «Очаквайте». There is no empty-state
// copy for this component and none may be invented: THE EMPTY STATE IS THE
// ABSENCE. A reviewer seeing no badge on the page is seeing the contract being
// honoured, not an unfinished row.
//
// The reason is not fastidiousness. Nobody has read the shop's live Google
// Business Profile (OWNER-QUESTIONS #7). Shipping «4,8 от 128 отзива в Google»
// as a stand-in would put a fabricated trust claim on sixteen pages, which is
// exactly the class of thing a customer disproves in one click — and it would
// do it on the element whose entire job is to be believable. Absence costs a
// trust signal; a wrong number costs the trust itself.
//
// Two independent gates, both required, deliberately not collapsed into one:
//   * $site['gbp_badge_enabled'] — the switch. It is what the owner flips.
//   * the three values being non-empty — the safety net, so flipping the
//     switch with the figures still blank renders nothing rather than
//     «от отзива в Google».
// Either alone would eventually ship the wrong thing.
//
// NO RATING OR REVIEW STRUCTURED DATA accompanies this badge under any schema
// type, and no consumer of these keys may add one. A business marking up
// reviews of ITSELF under LocalBusiness/Organization is categorically
// ineligible (RESEARCH P-1) — this is a hard prohibition, not a preference.
// The single permitted profile signal is jsonld.php's sameAs.
//
// THE ANCHOR OPENS IN THE SAME TAB. No new-browsing-context attribute appears
// on it, and none may be added — a plan-level gate asserts that literal is
// absent from this file, which is why this paragraph describes it rather than
// spelling it. The choice is a security decision as much as a courtesy one:
// with no new browsing context there is no opener to reverse-tabnab, so the
// whole category of attack is REMOVED rather than mitigated. The noopener
// relationship is kept on the link anyway as belt-and-braces, because some
// engines still permit an opener to be navigated by script even without one.
// The href is a developer-authored config literal and is never assembled from
// a request value (T-03-07).
//
// The rating is rendered EXACTLY as stored, comma decimal separator and all.
// It is not reformatted here: number_format() on PHP 5.2 for one display
// string, in a locale this build does not carry, is how a «4,8» becomes a
// «4.80».
//
// The mutual require_once with category-page.php (for torin_has_content() and
// torin_esc()) is safe for the reason written out in brand-row.php.
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/icons.php');
require_once(dirname(__FILE__) . '/category-page.php');

function torin_render_rating_badge() {
	global $site;

	// --- THE ABSENT STATE -------------------------------------------------
	if (!isset($site['gbp_badge_enabled']) || $site['gbp_badge_enabled'] !== true) {
		return;
	}
	if (!isset($site['gbp_url']) || !torin_has_content($site['gbp_url'])) {
		return;
	}
	if (!isset($site['gbp_rating']) || !torin_has_content($site['gbp_rating'])) {
		return;
	}
	if (!isset($site['gbp_reviews']) || !torin_has_content($site['gbp_reviews'])) {
		return;
	}
	// --- THE PRESENT STATE ------------------------------------------------
	// The accessible name is the CONCATENATION «4,8 от 128 отзива в Google»,
	// which names both the value and the destination (WCAG 2.4.4). The score
	// must never be the link's only text: a link announced as "4,8" tells a
	// screen-reader user nothing about where it goes. That is why the label
	// span is not optional and why the star is aria-hidden.
	//
	// The badge owns its own top margin in components.css and no parent rule
	// names it positionally, so this whole node can be deleted — or, as today,
	// never emitted — and the CTA block's vertical rhythm is unchanged. Same
	// structural discipline as .cta-block__form.
?>
				<a class="rating-badge" href="<?php echo torin_esc($site['gbp_url']); ?>" rel="noopener"><span class="rating-badge__star"><?php echo torin_icon('star'); ?></span><span class="rating-badge__score"><?php echo torin_esc($site['gbp_rating']); ?></span><span class="rating-badge__label">от <?php echo torin_esc($site['gbp_reviews']); ?> отзива в Google</span></a>
<?php
}
?>
