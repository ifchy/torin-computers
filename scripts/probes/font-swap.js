//
// font-swap.js — FOUT / web-font-swap reflow check (WINDOWS entry 7, 02-05 D8).
//
// The concern: Sofia Sans loads as a web font. Until it arrives the fallback
// stack is used, and if the two stacks have different metrics the hero content
// reflows when the swap happens -- visibly displacing the two hero CTA buttons
// under the user's cursor/thumb on a slow connection.
//
// Measured by loading the page twice against the same viewport: once with the
// woff2 requests blocked (the fallback-only state a throttled first paint
// shows) and once normally (the swapped state). The vertical delta of the CTA
// buttons between those two states IS the reflow the check is about.
//
// A pure font-family swap with `font-display: swap` cannot be eliminated
// entirely; what matters is whether the CTAs move enough to be noticeable.
// The threshold below (8px, half a default line) is the plan's "visibly
// displace" phrasing made concrete -- adjust deliberately, not to make a
// number pass.
//
const MEASURE = `(() => {
	const out = { fonts: [] };
	const hero = document.querySelector('.hero');
	const btns = [...document.querySelectorAll('.hero .btn, .hero__inner .btn')];
	out.heroHeight = hero ? +hero.getBoundingClientRect().height.toFixed(1) : null;
	out.ctaTops = btns.map(b => +b.getBoundingClientRect().top.toFixed(1));
	out.ctaCount = btns.length;
	const h1 = document.querySelector('h1');
	out.h1Height = h1 ? +h1.getBoundingClientRect().height.toFixed(1) : null;
	out.resolvedFamily = h1 ? getComputedStyle(h1).fontFamily : null;
	try { out.fontsLoaded = document.fonts ? document.fonts.status : 'n/a'; } catch (e) { out.fontsLoaded = 'n/a'; }
	return out;
})()`;

async function run(session, cdp, opts) {
	const THRESHOLD_PX = 8;

	// --- Pass 1: fonts blocked (fallback stack only)
	await session.cmd('Network.enable');
	await session.cmd('Network.setBlockedURLs', { urls: ['*.woff2', '*.woff', '*.ttf'] });
	await cdp.open(session, opts.url, opts);
	const fallback = await cdp.evaluate(session, MEASURE);

	// --- Pass 2: fonts allowed (swapped stack)
	await session.cmd('Network.setBlockedURLs', { urls: [] });
	// Cache-bust so pass 2 is a genuine reload rather than a repaint of pass 1.
	const sep = opts.url.indexOf('?') === -1 ? '?' : '&';
	await cdp.open(session, opts.url + sep + 'fontpass=2', opts);
	const swapped = await cdp.evaluate(session, MEASURE);

	const deltas = [];
	const n = Math.min(fallback.ctaTops.length, swapped.ctaTops.length);
	for (let i = 0; i < n; i++) deltas.push(+(swapped.ctaTops[i] - fallback.ctaTops[i]).toFixed(1));
	const maxAbs = deltas.length ? Math.max.apply(null, deltas.map(Math.abs)) : null;

	return {
		viewport: opts.width + 'x' + opts.height,
		thresholdPx: THRESHOLD_PX,
		fallbackOnly: {
			family: fallback.resolvedFamily,
			heroHeight: fallback.heroHeight,
			h1Height: fallback.h1Height,
			ctaTops: fallback.ctaTops
		},
		webFontSwapped: {
			family: swapped.resolvedFamily,
			heroHeight: swapped.heroHeight,
			h1Height: swapped.h1Height,
			ctaTops: swapped.ctaTops,
			fontsStatus: swapped.fontsLoaded
		},
		ctaVerticalDeltaPx: deltas,
		maxAbsDeltaPx: maxAbs,
		heroHeightDeltaPx: (fallback.heroHeight !== null && swapped.heroHeight !== null)
			? +(swapped.heroHeight - fallback.heroHeight).toFixed(1) : null,
		verdict: maxAbs === null
			? 'INCONCLUSIVE — no hero CTA buttons found'
			: (maxAbs <= THRESHOLD_PX
				? 'PASS — hero CTAs move at most ' + maxAbs + 'px across the font swap (threshold ' + THRESHOLD_PX + 'px)'
				: 'FAIL — hero CTAs move ' + maxAbs + 'px across the font swap (threshold ' + THRESHOLD_PX + 'px)')
	};
}

module.exports = { run };
