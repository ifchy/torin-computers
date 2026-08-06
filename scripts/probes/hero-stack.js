//
// hero-stack.js — D-30 above-the-fold invariant (WINDOWS entry 5).
//
// The hero's INVARIANT, stated in components.css: the mobile hero content stack
// must stay strictly under the resolved clamp(16rem, 42svh, 22rem) min-height.
// If it does not, the hero is sized by its contents instead of by 42svh and it
// grows past the fold.
//
// Plan 02-05 changed the trust badge's top margin (16px -> 8px) as a side
// effect of the CR-01 specificity fix and could not re-measure the stack, so
// the file records 241.6px as DERIVED rather than measured. This measures it.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	return await cdp.evaluate(session, `(() => {
		const hero = document.querySelector('.hero');
		const inner = document.querySelector('.hero__inner') || hero;
		if (!hero) return { error: 'no .hero found' };

		const cs = getComputedStyle(hero);
		const heroRect = hero.getBoundingClientRect();

		// Sum the content stack: each child's border box plus its collapsed-in margins.
		const children = [...inner.children].filter(el => {
			const s = getComputedStyle(el);
			return s.display !== 'none' && s.position !== 'absolute' && s.position !== 'fixed';
		});
		let stack = 0;
		const parts = children.map(el => {
			const r = el.getBoundingClientRect();
			const s = getComputedStyle(el);
			const mt = parseFloat(s.marginBlockStart || s.marginTop) || 0;
			const mb = parseFloat(s.marginBlockEnd || s.marginBottom) || 0;
			stack += r.height + mt + mb;
			return {
				tag: el.tagName.toLowerCase(),
				cls: String(el.className).slice(0, 30),
				height: +r.height.toFixed(1),
				marginTop: mt,
				marginBottom: mb
			};
		});

		const minHeight = parseFloat(cs.minHeight);
		return {
			viewport: window.innerWidth + 'x' + window.innerHeight,
			resolvedMinHeight: +minHeight.toFixed(1),
			renderedHeroHeight: +heroRect.height.toFixed(1),
			contentStackHeight: +stack.toFixed(1),
			// The invariant: content stack strictly under the min-height, so the
			// hero is sized by 42svh rather than by its contents.
			sizedByMinHeightNotContent: stack < minHeight,
			headroomPx: +(minHeight - stack).toFixed(1),
			parts: parts,
			verdict: stack < minHeight
				? 'PASS — stack ' + stack.toFixed(1) + 'px is under min-height ' + minHeight.toFixed(1) + 'px'
				: 'FAIL — stack ' + stack.toFixed(1) + 'px meets or exceeds min-height ' + minHeight.toFixed(1) + 'px; hero is content-sized'
		};
	})()`);
}

module.exports = { run };
