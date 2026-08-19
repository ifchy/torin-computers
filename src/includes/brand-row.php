<?php
// includes/brand-row.php — PHP 5.2-safe (array() only, named functions only,
// no namespaces, no short echo tags, tabs). Emits nothing on include: it is
// one function definition and no top-level output, exactly like
// category-page.php, categories.php and site-config.php.
//
// TRUST-01 / D3-09 — the brand wordmark row.
//
// WHY TEXT AND NOT LOGOS. The brief for this phase asked for a grayscale logo
// strip. D3-09 rejects logo IMAGES outright on evidence gathered live across
// eight competitors: zero of eight use them. Text wordmarks answer every
// concern the brief raised in a cheaper idiom — optical normalisation becomes
// the fixed-height chip in components.css, CLS becomes structurally zero
// because there are no images at all, and lazy-loading becomes a non-question.
// The .brand-row__item > img rule in components.css keeps the logo swap
// available later at zero layout change if the owner ever supplies files.
//
// THE DISCLAIMER PARAGRAPH IS NOT DECORATION AND IS NOT OPTIONAL. Naming
// «Lenovo» and «Apple» on a commercial page is lawful referential use only
// while the page also says, in the visitor's own language, that the shop is an
// independent out-of-warranty service and not an authorised representative
// (Art. 14(1)(c) EUTMR; RESEARCH P-7). Strip the <p class="brand-row__note">
// and what is left is an implied-authorisation claim. It ships with the row or
// the row does not ship.
//
// WHERE THIS MUST NOT APPEAR. The legal and utility pages — uslovia,
// warrently, msg, covid, laptopi, rezervni-chasti — get no brand row: a
// trademark disclaimer on a privacy policy is noise, and a row of
// manufacturer names beside the warranty terms invites exactly the misreading
// the disclaimer exists to prevent. Today that falls out for free, because
// those pages call neither this partial nor torin_render_service_page(). It is
// written down here so a later phase does not "tidy up" by including this file
// from footer.php, which would put the row on all sixteen pages at once.
//
// torin_has_content() and torin_esc() live in category-page.php, which is
// required below. The mutual require_once between the two files is safe and
// deliberate: whichever is included first marks itself included before
// requiring the other, so the second require is a no-op, and both function
// BODIES resolve their callees at CALL time — by which point every definition
// exists. Duplicating the two helpers here instead would be the same two-
// writers defect this project keeps refusing everywhere else.
require_once(dirname(__FILE__) . '/site-config.php');
require_once(dirname(__FILE__) . '/category-page.php');

// $tint_class is the running-tint string produced by torin_next_tint() on a
// service page, and '' on the homepage where the section is contracted plain
// (UI-SPEC §Section order). It exists so the row can be dropped into the
// service-page spine WITHOUT breaking the no-two-adjacent-tinted-bands rule:
// a partial that hardcoded its own surface would reintroduce the exact C3-5
// defect the running toggle was written to kill. It is a class string SELECTED
// by torin_next_tint(), never interpolated from page data.
function torin_render_brand_row($tint_class = '') {
	global $site;

	// An empty (or absent) brand list emits NOTHING AT ALL — not the heading,
	// not the disclaimer, not an empty <ul>. A trademark disclaimer standing
	// under a heading with no brands beneath it is not a degraded state, it is
	// a broken page. Same guard as every template slot: present-but-empty
	// behaves exactly like absent.
	if (!isset($site['brands']) || !torin_has_content($site['brands'])) {
		return;
	}
?>
	<section class="section<?php echo $tint_class; ?> brands" aria-labelledby="brands-h">
		<div class="container">
			<span class="eyebrow" aria-hidden="true"></span>
			<h2 id="brands-h">Обслужваме всички марки</h2>
			<?php // A real <ul> rather than a div of spans: it announces its own
			      // item count to a screen reader, which is most of what makes a
			      // list of names scannable non-visually. Items are not links, not
			      // buttons and not hoverable, so the 44px touch-target floor does
			      // not apply to them — there is nothing to touch. ?>
			<ul class="brand-row">
<?php	// STORED ORDER, deliberately unsorted. site-config.php is the single
		// place the sequence is decided; sorting here would mean the row a
		// visitor sees and the list a maintainer edits disagree about their own
		// order, and «и др.» would stop being reliably last.
		foreach ($site['brands'] as $torin_brand) { ?>
				<li class="brand-row__item"><?php echo torin_esc($torin_brand); ?></li>
<?php	} ?>
				<?php // Emitted by the TEMPLATE, never stored as an entry: it means
				      // "this list is not exhaustive", which is a UI affordance and
				      // not a manufacturer. Always last, by construction. ?>
				<li class="brand-row__item brand-row__item--more">и др.</li>
			</ul>
			<p class="brand-row__note">Независим извънгаранционен сервиз. Не сме оторизиран представител на нито един производител — имената на марките указват само каква техника приемаме.</p>
		</div>
	</section>
<?php
}
?>
