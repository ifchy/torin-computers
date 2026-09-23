//
// focus-rings.js — WCAG 2.1 SC 1.4.11 focus-indicator check (WINDOWS entry 6).
//
// Tabs through the page with REAL key events (programmatic .focus() does not
// reliably match :focus-visible) and measures each focus ring against the
// surface it is actually drawn on.
//
// `outline-offset: 2px` means the ring is painted on the surface BEHIND the
// control, not on the control's own fill -- which is the whole substance of
// defect CR-02. So a ring on a hero/footer/callbar button is measured against
// the dark surface token, and a ring on a page-surface control against the page
// token. Both comparisons are reported so the result cannot be read the wrong way.
//
const { HELPERS } = require('./contrast.js');

async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	const results = [];
	const maxTabs = 30;
	for (let i = 0; i < maxTabs; i++) {
		await cdp.pressTab(session, 1);
		const entry = await cdp.evaluate(session, `(() => {
			${HELPERS}
			const el = document.activeElement;
			if (!el || el === document.body) return null;
			const cs = getComputedStyle(el);
			const ring = __parse(cs.outlineColor);
			if (!ring || cs.outlineStyle === 'none' || parseFloat(cs.outlineWidth) === 0) {
				return { text: (el.textContent || '').trim().slice(0, 30), cls: String(el.className).slice(0, 40), ring: null, noRing: true };
			}
			// Which surface is this control sitting on?
			//
			// .holiday-banner joined this list in Phase 4 (OWNER-02). It is the
			// fourth dark surface, and the reason it is added here at the same
			// time as the base.css rule is that a probe which does not know
			// about a surface does not merely miss it — it reports a ring on
			// that surface as though it were on the white page, and a
			// too-light-for-dark ring would come back PASS. That is the same
			// shape of silent wrong answer as CR-02 itself.
			const onDark = !!el.closest('.hero, .site-footer, .callbar, .holiday-banner');
			const darkTokens = {
				'--c-ink-deep': __tokenRgb('--c-ink-deep'),
				'--c-ink-deep-2': __tokenRgb('--c-ink-deep-2'),
				'--c-ink-deepest': __tokenRgb('--c-ink-deepest')
			};
			const against = {};
			if (onDark) {
				for (const k in darkTokens) against[k] = __ratio(ring, darkTokens[k]);
			} else {
				against['--c-page'] = __ratio(ring, __tokenRgb('--c-page'));
				against['--c-surface-2'] = __ratio(ring, __tokenRgb('--c-surface-2'));
			}
			const ratios = Object.values(against).filter(v => typeof v === 'number');
			return {
				text: (el.textContent || '').trim().slice(0, 30),
				cls: String(el.className).slice(0, 40),
				surface: onDark ? 'dark' : 'page',
				focusVisible: (() => { try { return el.matches(':focus-visible'); } catch (e) { return 'n/a'; } })(),
				outlineColor: cs.outlineColor,
				outlineWidth: cs.outlineWidth,
				outlineOffset: cs.outlineOffset,
				against: against,
				worstRatio: ratios.length ? Math.min.apply(null, ratios) : null
			};
		})()`);
		if (entry) results.push(entry);
	}

	// The dev theme switcher is a staging-only control, not production UI.
	const production = results.filter(r => !/Тема/.test(r.text || ''));
	const withRing = production.filter(r => !r.noRing && r.worstRatio !== null);
	const failing = withRing.filter(r => r.worstRatio < 3);

	// MEASURED, not inferred from the URL. This used to read
	// `opts.url.indexOf('theme=a') !== -1 ? 'A' : 'B'`, which was correct only
	// while the dev switcher existed and `?theme=a` selected the override. That
	// switcher was deleted in 04-07, and the owner's 2026-09-23 reversal baked
	// Theme A into :root — so the URL test would have labelled every future run
	// "B" no matter what was actually on screen, quietly mislabelling the
	// evidence in exactly the artefact that exists to be trusted. The brand
	// token is the ground truth; the label is derived from it and the raw value
	// is reported alongside so an unrecognised palette cannot masquerade as
	// either one.
	const brand = await cdp.evaluate(session,
		"getComputedStyle(document.documentElement).getPropertyValue('--c-brand').trim()");
	const themeLabel = brand === '#fbad03' ? 'A' : (brand === '#ffc70a' ? 'B' : 'unrecognised');

	return {
		theme: themeLabel,
		brandToken: brand,
		viewport: opts.width + 'x' + opts.height,
		tabbed: results.length,
		productionControls: production.length,
		missingRing: production.filter(r => r.noRing).map(r => r.text),
		darkSurfaceControls: withRing.filter(r => r.surface === 'dark').length,
		minRatioOverall: withRing.length ? Math.min.apply(null, withRing.map(r => r.worstRatio)) : null,
		failingSC1411: failing,
		verdict: failing.length === 0 && production.filter(r => r.noRing).length === 0
			? 'PASS — every production control shows a ring at >=3:1 on its own surface'
			: 'FAIL',
		detail: withRing
	};
}

module.exports = { run };
