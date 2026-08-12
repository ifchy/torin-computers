//
// svc-page.js — rendered proof of the Phase 3 service-page spine.
//
// Four things this phase changed are only correct if a real layout engine says
// so, and all four are the kind that look fine in the markup and wrong on a
// screen:
//
//   1. TINT ALTERNATION. category-page.php used to hardcode the tint class onto
//      three specific slots while every slot is optional, so a page that filled
//      some and not others rendered two adjacent tinted bands (UI-SPEC C3-5).
//      That is now a running toggle flipped once per EMITTED section. The
//      toggle is correct by construction; the defect it replaces was visual,
//      so it is confirmed by looking. This is the backstop truth in the plan.
//   2. BREADCRUMB TOUCH TARGETS. The 44px floor is reached as a 28px line box
//      plus 2 x --sp-sm of block padding. That arithmetic depends on the
//      resolved line-height of a font that is loaded over the network, so it is
//      measured, not derived.
//   3. MOBILE OVERFLOW. Long Bulgarian names in a wrapping breadcrumb row, and
//      an urgent callout with a keyline and padding, are both plausible sources
//      of horizontal scroll at 360px.
//   4. HEADING INTEGRITY. Exactly one h1 (every blocks variant emits h2), and
//      no heading left empty by a guard that fired half-way.
//
// The viewport is set by cdp.open() through Emulation.setDeviceMetricsOverride,
// NOT by a browser window flag — a window size does not constrain the layout
// viewport, which is the measurement trap that makes a mobile probe silently
// report desktop numbers.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	return await cdp.evaluate(session, `(() => {
		const main = document.querySelector('main');
		if (!main) return { error: 'no <main> found' };

		// Only direct-child sections of <main> form the alternating band
		// sequence; a section nested inside another is not part of the rhythm.
		const sections = [...main.querySelectorAll(':scope > section')];

		const rendered = sections.map(el => {
			const cs = getComputedStyle(el);
			return {
				cls: String(el.className),
				tinted: el.classList.contains('section--tint'),
				// Recorded because the class is the CONTRACT but the paint is
				// the outcome: a tinted band whose fill did not apply would
				// pass a class-only assertion and still look wrong.
				background: cs.backgroundColor
			};
		});

		let adjacentTintedPairs = 0;
		for (let i = 1; i < rendered.length; i++) {
			if (rendered[i].tinted && rendered[i - 1].tinted) adjacentTintedPairs++;
		}

		const breadcrumbLinks = [...document.querySelectorAll('.breadcrumbs a')];
		const breadcrumbLinkHeights = breadcrumbLinks.map(
			a => +a.getBoundingClientRect().height.toFixed(1)
		);

		const headings = [...document.querySelectorAll('h1, h2, h3, h4, h5, h6')];
		const emptyHeadings = headings.filter(h => h.textContent.trim() === '').length;

		// Each entry here is a surface this probe was written to measure that
		// is not present in the served response. Its checks therefore assert
		// nothing, and saying so is the whole point.
		const inconclusive = [];
		if (breadcrumbLinks.length === 0) {
			inconclusive.push('no .breadcrumbs a in the served HTML — the 44px touch-target check asserted nothing');
		}
		if (!document.querySelector('.svc__block--urgent')) {
			inconclusive.push('no .svc__block--urgent in the served HTML — the urgent-tone surface is absent');
		}
		if (!document.querySelector('.svc__warranty__term')) {
			inconclusive.push('no .svc__warranty__term in the served HTML — the TRUST-03 term line is absent');
		}

		return {
			url: location.href,
			viewport: window.innerWidth + 'x' + window.innerHeight,
			sections: rendered,
			sectionCount: rendered.length,
			adjacentTintedPairs: adjacentTintedPairs,
			scrollWidth: document.documentElement.scrollWidth,
			innerWidth: window.innerWidth,
			horizontalScroll: document.documentElement.scrollWidth > window.innerWidth,
			breadcrumbLinkCount: breadcrumbLinks.length,
			breadcrumbLinkHeights: breadcrumbLinkHeights,
			h1Count: document.querySelectorAll('h1').length,
			headingCount: headings.length,
			emptyHeadings: emptyHeadings,
			hasUrgentBlock: !!document.querySelector('.svc__block--urgent'),
			hasWarrantyTerm: !!document.querySelector('.svc__warranty__term'),
			// A probe whose strongest assertion is over an EMPTY list reports a
			// pass it did not earn: [].every() is true, so a page served
			// WITHOUT breadcrumbs would clear the 44px target check by having
			// no targets. That is the precise shape of the unrun-check-recorded-
			// as-passing failure this probe exists to stop repeating, so the
			// vacuous case is named INCONCLUSIVE rather than folded into PASS.
			inconclusive: inconclusive,
			verdict: inconclusive.length > 0 ? 'INCONCLUSIVE' : ((
				adjacentTintedPairs === 0 &&
				document.documentElement.scrollWidth <= window.innerWidth &&
				document.querySelectorAll('h1').length === 1 &&
				emptyHeadings === 0 &&
				breadcrumbLinkHeights.every(h => h >= 44)
			) ? 'PASS' : 'FAIL')
		};
	})()`);
}

module.exports = { run };
