//
// trust-signals.js — rendered proof of the four surfaces plan 03-02 adds to the
// homepage: the brand wordmark row, the Google rating badge, and the two
// differentiator sections with their evidence strips.
//
// Five things this plan changed are only correct if a real layout engine says
// so, and every one of them looks fine in the markup:
//
//   1. SECTION ORDER AND TINT ALTERNATION. Inserting two sections into a
//      contracted eight-row order reassigns the tint on three sections that
//      already existed (catch-all, self-diagnostic, CTA). Getting one wrong
//      produces two adjacent tinted bands, which is a purely visual defect.
//   2. BRAND ROW WRAPPING. The row is a wrapping flex row of fixed-height
//      chips. Whether seven brands plus a closer actually WRAP at 360px rather
//      than overflow depends on the resolved metrics of a webfont loaded over
//      the network — it is measured, not derived.
//   3. EVIDENCE BOX SIZING. The width/height ATTRIBUTES carry each file's true
//      intrinsic pixels while CSS sets the display size to 100x100. If that
//      contract breaks, the box takes the attribute size and the strip
//      silently renders 200px or 585px wide.
//   4. RATING BADGE. Today it must be ABSENT (OWNER-QUESTIONS #7). Its
//      computed background and box height are reported when it is present, so
//      the same probe verifies the present state once the owner enables it.
//   5. MOBILE OVERFLOW. Long Bulgarian brand names in a wrapping row and a
//      three-column 100px grid are both plausible sources of horizontal scroll.
//
// The viewport is set by cdp.open() through Emulation.setDeviceMetricsOverride,
// NOT by a browser window flag — a window size does not constrain the layout
// viewport, which is the measurement trap that makes a mobile probe silently
// report desktop numbers.
//
// ABSENT SURFACES FORCE INCONCLUSIVE, NEVER PASS. [].every() is true, so a page
// served without a brand row or without an evidence strip would clear this
// probe's strongest assertions by having nothing to assert over. That is the
// exact false-pass shape plan 03-01 hit and hardened against, so every measured
// surface that is missing is named in `inconclusive` and the verdict degrades
// rather than passing vacuously.
//
// The rating badge is the ONE deliberate exception: its absence is the
// specified, shipped state (UI-SPEC §2a), so it is reported as
// `ratingBadgePresent: false` and does NOT make the run inconclusive.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	return await cdp.evaluate(session, `(() => {
		const main = document.querySelector('main');
		if (!main) return { error: 'no <main> found' };

		// Only direct-child sections of <main> form the alternating band
		// sequence; a section nested inside another is not part of the rhythm.
		const sections = [...main.querySelectorAll(':scope > section')];

		const sectionOrder = sections.map(el => {
			const cs = getComputedStyle(el);
			const h = el.querySelector('h1, h2');
			return {
				cls: String(el.className),
				tinted: el.classList.contains('section--tint'),
				heading: h ? h.textContent.trim() : null,
				// The class is the CONTRACT but the paint is the OUTCOME: a
				// tinted band whose fill did not apply would pass a class-only
				// assertion and still look wrong on the screen.
				background: cs.backgroundColor
			};
		});

		let adjacentTintedPairs = 0;
		for (let i = 1; i < sectionOrder.length; i++) {
			if (sectionOrder[i].tinted && sectionOrder[i - 1].tinted) adjacentTintedPairs++;
		}

		// Rendered ROWS, not item count: distinct offsetTop values are how many
		// lines the row actually occupies once the font has resolved.
		const brandItems = [...document.querySelectorAll('.brand-row__item')];
		const brandTops = [...new Set(brandItems.map(el => Math.round(el.getBoundingClientRect().top)))];
		const brandRowRows = brandTops.length;

		// Adjacency: no two chips on the same rendered line may touch. Measured
		// as the horizontal gap between consecutive items sharing a row.
		let minAdjacentGap = null;
		for (let i = 1; i < brandItems.length; i++) {
			const a = brandItems[i - 1].getBoundingClientRect();
			const b = brandItems[i].getBoundingClientRect();
			if (Math.round(a.top) !== Math.round(b.top)) continue;
			const gap = b.left - a.right;
			if (minAdjacentGap === null || gap < minAdjacentGap) minAdjacentGap = +gap.toFixed(1);
		}

		const brandTexts = brandItems.map(el => el.textContent.trim());
		const brandDuplicates = brandTexts.filter((t, i) => brandTexts.indexOf(t) !== i);
		const closerIsLast = brandItems.length > 0 &&
			brandItems[brandItems.length - 1].classList.contains('brand-row__item--more');

		const badge = document.querySelector('.rating-badge');
		const badgeBox = badge ? badge.getBoundingClientRect() : null;

		const evidenceStrips = document.querySelectorAll('.evidence').length;
		const evidenceBoxes = [...document.querySelectorAll('.evidence img')].map(img => {
			const r = img.getBoundingClientRect();
			return {
				src: img.getAttribute('src'),
				w: +r.width.toFixed(1),
				h: +r.height.toFixed(1),
				// The ATTRIBUTES must be the file's intrinsic pixels, and the
				// browser's own naturalWidth is the only way to tell whether
				// they are honest. A mismatch means the box reserved before
				// load was the wrong shape — a CLS defect no static check sees.
				attrW: img.getAttribute('width'),
				attrH: img.getAttribute('height'),
				naturalW: img.naturalWidth,
				naturalH: img.naturalHeight,
				loading: img.getAttribute('loading'),
				decoding: img.getAttribute('decoding'),
				complete: img.complete
			};
		});

		const headings = [...document.querySelectorAll('h1, h2, h3, h4, h5, h6')];
		const emptyHeadings = headings.filter(h => h.textContent.trim() === '').length;

		// Each entry names a surface this probe exists to measure that is NOT
		// in the served response. Its checks therefore assert nothing, and
		// saying so is the entire point.
		const inconclusive = [];
		if (brandItems.length === 0) {
			inconclusive.push('no .brand-row__item in the served HTML — the wrap, adjacency and ordering checks asserted nothing');
		}
		if (!document.querySelector('.brand-row__note')) {
			inconclusive.push('no .brand-row__note in the served HTML — the mandatory trademark disclaimer is absent');
		}
		if (evidenceStrips === 0) {
			inconclusive.push('no .evidence in the served HTML — the 100x100 box check asserted nothing');
		}
		if (evidenceBoxes.length > 0 && evidenceBoxes.some(b => !b.complete || b.naturalW === 0)) {
			inconclusive.push('at least one evidence image did not load — its rendered box size is not a measurement of the CSS contract');
		}

		const evidenceBoxesOk = evidenceBoxes.length > 0 &&
			evidenceBoxes.every(b => Math.round(b.w) === 100 && Math.round(b.h) === 100);
		const attrsHonest = evidenceBoxes.every(
			b => !b.complete || b.naturalW === 0 ||
				(String(b.naturalW) === String(b.attrW) && String(b.naturalH) === String(b.attrH))
		);

		return {
			url: location.href,
			viewport: window.innerWidth + 'x' + window.innerHeight,
			sectionOrder: sectionOrder,
			sectionCount: sectionOrder.length,
			adjacentTintedPairs: adjacentTintedPairs,
			scrollWidth: document.documentElement.scrollWidth,
			innerWidth: window.innerWidth,
			horizontalScroll: document.documentElement.scrollWidth > window.innerWidth,
			brandItemCount: brandItems.length,
			brandRowRows: brandRowRows,
			brandTexts: brandTexts,
			brandDuplicates: brandDuplicates,
			brandCloserIsLast: closerIsLast,
			minAdjacentGap: minAdjacentGap,
			// The absent badge is the SPECIFIED state today (UI-SPEC §2a), so
			// this is reported and deliberately does NOT force INCONCLUSIVE.
			ratingBadgePresent: !!badge,
			ratingBadgeBackground: badge ? getComputedStyle(badge).backgroundColor : null,
			ratingBadgeHeight: badgeBox ? +badgeBox.height.toFixed(1) : null,
			evidenceStrips: evidenceStrips,
			evidenceBoxes: evidenceBoxes,
			evidenceBoxesOk: evidenceBoxesOk,
			evidenceAttrsHonest: attrsHonest,
			h1Count: document.querySelectorAll('h1').length,
			headingCount: headings.length,
			emptyHeadings: emptyHeadings,
			inconclusive: inconclusive,
			verdict: inconclusive.length > 0 ? 'INCONCLUSIVE' : ((
				adjacentTintedPairs === 0 &&
				document.documentElement.scrollWidth <= window.innerWidth &&
				document.querySelectorAll('h1').length === 1 &&
				emptyHeadings === 0 &&
				brandDuplicates.length === 0 &&
				closerIsLast &&
				(minAdjacentGap === null || minAdjacentGap > 0) &&
				evidenceBoxesOk &&
				attrsHonest
			) ? 'PASS' : 'FAIL')
		};
	})()`);
}

module.exports = { run };
