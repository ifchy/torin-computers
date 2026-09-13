//
// trust-signals.js — rendered proof of the trust surfaces on the homepage and
// on any page that carries the shared brand row: the brand wordmark row, the
// Google rating badge, section order and tint alternation, heading integrity,
// mobile overflow, and — where a page has one — an evidence strip.
//
// AMENDED 2026-09-12 by plan 03.5-02. The two differentiator sections this
// probe was originally written against were deleted from the homepage in that
// plan, along with the only two evidence strips the homepage carried. Both
// described service lines the shop discontinued (03.5-CONTEXT D3.5-01). The
// probe's other five measurements are unchanged and still unconditional.
//
// What is only correct if a real layout engine says so, and looks fine in the
// markup either way:
//
//   1. SECTION ORDER AND TINT ALTERNATION. The homepage now serves six bands —
//      hero, categories, brands, catch-all, self-diagnostic, contact — of which
//      three are tinted, in strict plain/tint alternation. One tint wrong
//      produces two adjacent tinted bands, a purely visual defect. NOTE that
//      the brand row is INJECTED by a PHP partial at render time and carries no
//      opening tag in index.html's source, so the rendered count is one higher
//      than any count grepped out of that file. The two numbers are both right.
//      Never reconcile them by editing one toward the other.
//   2. BRAND ROW WRAPPING. The row is a wrapping flex row of fixed-height
//      chips. Whether the brands plus a closer actually WRAP at 360px rather
//      than overflow depends on the resolved metrics of a webfont loaded over
//      the network — it is measured, not derived.
//   3. EVIDENCE BOX SIZING. The width/height ATTRIBUTES carry each file's true
//      intrinsic pixels while CSS sets the display size to 100x100. If that
//      contract breaks, the box takes the attribute size and the strip
//      silently renders 200px or 585px wide.
//   4. RATING BADGE. See the note below — its shipped state inverted.
//   5. MOBILE OVERFLOW. Long Bulgarian brand names in a wrapping row and a
//      three-column 100px grid are both plausible sources of horizontal scroll.
//
// The viewport is set by cdp.open() through Emulation.setDeviceMetricsOverride,
// NOT by a browser window flag — a window size does not constrain the layout
// viewport, which is the measurement trap that makes a mobile probe silently
// report desktop numbers.
//
// ABSENT SURFACES FORCE INCONCLUSIVE, NEVER PASS. [].every() is true, so a page
// served without a brand row would clear this probe's strongest assertions by
// having nothing to assert over. That is the exact false-pass shape plan 03-01
// hit and hardened against, so every measured surface that is missing is named
// in `inconclusive` and the verdict degrades rather than passing vacuously.
//
// TWO DELIBERATE NON-GATES, both reported unconditionally in the result object.
//
// 1. THE EVIDENCE STRIP IS OPT-IN, via TRUST_EXPECT_EVIDENCE=1. Until plan
//    03.5-02 the zero-strip case was pushed into `inconclusive`
//    unconditionally. The homepage now serves ZERO strips by design, so the
//    unconditional form would make every future homepage run INCONCLUSIVE
//    forever — and a verdict nobody reads is worse than no verdict. That is not
//    a hypothetical: it is the finding that produced SVC_EXPECT_URGENT in
//    scripts/probes/svc-page.js, after the unconditional urgent-block check
//    reported INCONCLUSIVE on every correctly built child page. Same shape,
//    same fix. Point the probe at a page CONTRACTED to carry a strip and set
//    the flag; `evidenceStrips` is reported either way, so an unexpected
//    disappearance stays visible in the output. The 100x100 box contract and
//    the honest-attributes check are untouched — they simply have nothing to
//    assert when there is no strip, which is what the flag makes explicit.
//
// 2. THE RATING BADGE IS REPORTED, NOT GATED — but no longer for the original
//    reason. This comment used to say the badge's ABSENCE was the specified,
//    shipped state (UI-SPEC §2a). That inverted on 2026-09-12: plan 03.5-01
//    answered OWNER-QUESTIONS #7, switched the badge on, and it was verified
//    rendering live on the homepage and on a service page at both viewports.
//    Absence on either of those pages is now a DEFECT, not a contract. It stays
//    reported rather than gated because this probe may legitimately be pointed
//    at pages that carry no badge, so gating it here would reintroduce exactly
//    the permanent-INCONCLUSIVE problem note 1 removes. A caller that needs the
//    badge proven must read `ratingBadgePresent` out of the JSON explicitly —
//    PASS alone does not prove it rendered.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	// Serialised into the page scope rather than read from process.env inside
	// it: the evaluated string runs in the browser, which has no process.env.
	// Same mechanism svc-page.js uses for expectUrgent.
	const expectEvidence = process.env.TRUST_EXPECT_EVIDENCE === '1';

	// Force the lazy evidence images to load BEFORE measuring.
	//
	// Check 3 above measures the rendered box of each evidence image against
	// the 100x100 CSS contract. Those images carry loading="lazy" and sit far
	// below the fold, so on a freshly opened page they are never fetched and
	// every one reports naturalWidth 0 / complete false. A box measured from an
	// unloaded image is not evidence about the CSS contract -- a broken image
	// can size differently from a decoded one -- so the probe correctly refuses
	// to score it and degrades to INCONCLUSIVE. That is the right verdict for
	// the wrong reason: it says nothing about the page, only about the probe
	// never having scrolled.
	//
	// So scroll each strip into view, wait for the fetches to settle, then
	// return to the top before measuring. Returning to the top matters because
	// the layout assertions below are read from the same document state.
	//
	// The wait is bounded: a 5 s cap, and decode failures resolve rather than
	// reject, so an image that genuinely 404s still reaches the measurement
	// step and is reported as not-loaded instead of hanging the probe.
	await cdp.evaluate(session, `(async () => {
		const strips = [...document.querySelectorAll('.evidence')];
		for (const el of strips) {
			el.scrollIntoView({ block: 'center' });
			await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
		}
		const imgs = [...document.querySelectorAll('.evidence img')];
		await Promise.race([
			Promise.all(imgs.map(img => img.complete
				? Promise.resolve()
				: new Promise(r => {
					img.addEventListener('load', r, { once: true });
					img.addEventListener('error', r, { once: true });
				}))),
			new Promise(r => setTimeout(r, 5000))
		]);
		window.scrollTo(0, 0);
		await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
		return true;
	})()`);

	return await cdp.evaluate(session, `(() => {
		const expectEvidence = ${expectEvidence};
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
		// OPT-IN (TRUST_EXPECT_EVIDENCE=1). The homepage serves zero strips by
		// design since plan 03.5-02, so gating this unconditionally would pin
		// every homepage run at INCONCLUSIVE. Set the flag for a page
		// contracted to carry a strip. evidenceStrips is returned either way.
		if (expectEvidence && evidenceStrips === 0) {
			inconclusive.push('no .evidence in the served HTML — the 100x100 box check asserted nothing (TRUST_EXPECT_EVIDENCE was set)');
		}
		// UNCONDITIONAL, and the flag does not soften it. A strip that IS in the
		// served response but holds no images is an absent surface where one was
		// promised — a different failure from having no strip at all, and always
		// a defect. Without this, making the zero-strip case opt-in would have
		// opened a second vacuous route: zero boxes inside a present strip would
		// excuse the box contract below with nothing reporting it.
		if (evidenceStrips > 0 && evidenceBoxes.length === 0) {
			inconclusive.push('an .evidence strip is present but contains no img — the 100x100 box check asserted nothing');
		}
		if (evidenceBoxes.length > 0 && evidenceBoxes.some(b => !b.complete || b.naturalW === 0)) {
			inconclusive.push('at least one evidence image did not load — its rendered box size is not a measurement of the CSS contract');
		}

		// Deliberately NON-VACUOUS: false when there is nothing to measure, so
		// the reported value never claims a contract was proven when it was not.
		// The verdict below excuses it only in the genuinely-no-strip case,
		// which TRUST_EXPECT_EVIDENCE is what governs.
		const evidenceBoxesOk = evidenceBoxes.length > 0 &&
			evidenceBoxes.every(b => Math.round(b.w) === 100 && Math.round(b.h) === 100);
		const evidenceContractApplies = evidenceBoxes.length > 0;
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
			// REPORTED, NOT GATED — and no longer because absence is specified.
			// The badge ships ENABLED since plan 03.5-01 and was verified
			// rendering live 2026-09-12, so its absence on the homepage or on a
			// service page is a defect. It stays out of the verdict only because
			// this probe may be pointed at pages that carry no badge. A caller
			// that needs it proven must read this field; PASS does not prove it.
			ratingBadgePresent: !!badge,
			ratingBadgeBackground: badge ? getComputedStyle(badge).backgroundColor : null,
			ratingBadgeHeight: badgeBox ? +badgeBox.height.toFixed(1) : null,
			// Reported UNCONDITIONALLY, flag or no flag. The number is evidence
			// even when it is not a gate: a strip that vanishes unexpectedly from
			// a page that should have one is visible here either way.
			evidenceStrips: evidenceStrips,
			evidenceExpected: expectEvidence,
			evidenceBoxes: evidenceBoxes,
			evidenceBoxesOk: evidenceBoxesOk,
			evidenceContractApplies: evidenceContractApplies,
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
				// The box contract is required whenever there is a box to
				// measure. It is excused ONLY when no evidence image was served
				// at all — and that case cannot pass silently on a page
				// contracted to carry one, because TRUST_EXPECT_EVIDENCE=1 sends
				// it to INCONCLUSIVE above before this expression is reached. A
				// present-but-empty strip is likewise caught above. Without this
				// guard the homepage would go from permanently INCONCLUSIVE to
				// permanently FAIL, which is not an improvement.
				(!evidenceContractApplies || evidenceBoxesOk) &&
				attrsHonest
			) ? 'PASS' : 'FAIL')
		};
	})()`);
}

module.exports = { run };
