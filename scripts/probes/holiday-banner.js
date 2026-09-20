//
// holiday-banner.js — rendered measurement of the closure strip (OWNER-02,
// D4-27, UI-SPEC C-1).
//
// WHY THIS IS MEASURED AND NOT COMPUTED. The UI contract accepts that a closure
// banner pushes the homepage's first category card below the fold — a closure
// outranks the fold — but it bounds the strip at 180px tall at 360x640 and calls
// anything above that a defect. That number depends on the resolved line-height
// of a Cyrillic subset loaded over the network, on the icon column's em-relative
// width, and on how a 120-code-point Bulgarian sentence happens to wrap. All
// three are layout-engine facts. The contract's own table is arithmetic, and the
// plan says in as many words that the executor must re-measure rather than
// re-derive.
//
// It reports `absent: true` rather than failing when no strip is present. That
// is the SHIPPED state — no closure scheduled means banner.php emits nothing at
// all — so a run against an ordinary page is a valid result and says so, and the
// caller decides whether absence is what it expected.
//
// `cardBottom` is reported whenever the page has a category card grid, so the
// same run answers both the banner's own height and the D-30 above-the-fold
// consequence it causes. On a page with no cards the field is null.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	return await cdp.evaluate(session, `(async () => {
		// The strip's height is a text-wrapping outcome, so it is only correct
		// once the real font is in use. Measuring before the swap reports the
		// fallback's metrics, which on a Cyrillic page are not close.
		if (document.fonts && document.fonts.ready) { await document.fonts.ready; }

		const banner = document.querySelector('.holiday-banner');
		if (!banner) {
			return { absent: true, viewport: innerWidth + 'x' + innerHeight };
		}

		const inner = banner.querySelector('.holiday-banner__inner');
		const p = banner.querySelector('p');
		const cs = getComputedStyle(banner);
		const pcs = p ? getComputedStyle(p) : null;
		const rect = banner.getBoundingClientRect();

		// Lines are derived from the paragraph's own box against its own
		// resolved line-height, not counted from the source text: the source
		// has no line breaks in it and the wrap points are the engine's.
		const lineHeight = pcs ? parseFloat(pcs.lineHeight) : 0;
		const pHeight = p ? p.getBoundingClientRect().height : 0;

		// The first card of the homepage grid, when there is one. This is the
		// element D-30's arithmetic is about.
		const card = document.querySelector('.category-card, .card-grid > *');
		const cardBottom = card
			? Math.round(card.getBoundingClientRect().bottom + scrollY)
			: null;

		return {
			absent: false,
			viewport: innerWidth + 'x' + innerHeight,
			bannerHeight: Math.round(rect.height * 10) / 10,
			bannerTop: Math.round(rect.top + scrollY),
			// Proof the strip is the first thing inside the wrapper rather than
			// merely present somewhere on the page.
			isFirstChildOfWrap: (() => {
				const wrap = document.getElementById('wrap');
				return !!wrap && wrap.firstElementChild === banner;
			})(),
			messageCodePoints: p ? [...p.textContent].length : 0,
			lineHeight: Math.round(lineHeight * 100) / 100,
			paragraphHeight: Math.round(pHeight * 10) / 10,
			lines: lineHeight ? Math.round(pHeight / lineHeight) : null,
			fill: cs.backgroundColor,
			ink: cs.color,
			// The keyline must be an inset shadow. A non-empty border here is
			// the defect the contract names, so it is reported rather than
			// assumed away.
			boxShadow: cs.boxShadow,
			borderBlockEndWidth: cs.borderBlockEndWidth,
			gridTemplateColumns: inner ? getComputedStyle(inner).gridTemplateColumns : null,
			position: cs.position,
			// Non-dismissible means there is nothing to press and nothing to
			// remember. Both are asserted against the rendered tree, not the
			// template.
			interactiveDescendants: banner.querySelectorAll('button, a, input, [role]').length,
			scriptDescendants: banner.querySelectorAll('script').length,
			cardBottom: cardBottom,
			// Usable viewport for the fold comparison, stated so the reader of
			// a result does not have to reconstruct it.
			viewportHeight: innerHeight
		};
	})()`);
}

module.exports = { run };
