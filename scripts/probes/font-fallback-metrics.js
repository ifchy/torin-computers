//
// font-fallback-metrics.js — calibration measurements for the metric-adjusted
// fallback face that closes G-02-4 (plan 02-09).
//
// G-02-4, measured by scripts/probes/font-swap.js at 360x640: the two hero CTA
// buttons move 27.1px upward when Sofia Sans arrives, against an 8px threshold.
// The hero h1 sets THREE lines in the fallback stack (110.4px) and TWO with
// Sofia Sans (73.6px); that 36.8px collapse is the whole displacement. The
// owner chose "reserve the space" over font-display: optional, and the
// mechanism is a `size-adjust`-ed local() fallback face tuned so the fallback
// sets the SAME number of lines as Sofia Sans.
//
// This probe produces every number plan 02-09 ships in src/css/base.css.
// Nothing here is estimated, copied from a blog post, or derived from published
// metrics of a font that is not installed on this machine.
//
//
// THE THIRD MEASUREMENT TRAP OF THIS PHASE, FOUND BY THIS PROBE'S FIRST RUN
// ------------------------------------------------------------------------
// The first version of this probe reported a flawless-looking result — every
// candidate matching the Sofia line count at every percentage from 100 down to
// 70 — while measuring nothing at all. Two independent bugs produced it:
//
//   1. In THIS Chromium (Brave 149 / Chromium 149, macOS), `local()` resolves
//      ONLY by FAMILY name. Every full-name and PostScript-name form fails:
//      local('Arial Bold'), local('Arial-BoldMT'), local('ArialMT'),
//      local('Helvetica Neue Bold'), local('HelveticaNeue-Bold'),
//      local('Helvetica-Bold'), local('Verdana Bold'), local('Verdana-Bold')
//      all reject with "A network error occurred", and document.fonts.check()
//      returns false — even though /System/Library/Fonts/Supplemental/Arial
//      Bold.ttf is present on disk. A @font-face whose src names only those
//      forms resolves to NOTHING.
//
//   2. An element whose font-family names only unresolvable faces falls to the
//      LAST-RESORT font, and on this machine the last-resort font is NARROWER
//      than Sofia Sans (advance 1831.06 vs 1892.52 for the hero string at
//      100px/700). It therefore sets the hero h1 in exactly TWO lines —
//      73.6px, bit-for-bit the target height. A completely unresolved face
//      reads as a perfect match.
//
// So the guard below is not defensive decoration. Every scan iteration checks
// document.fonts.check() AND asserts the measured advance tracks
// size-adjust * the face's own unadjusted advance; an iteration failing either
// is recorded with resolved:false and excluded from the match set. Without that
// guard this probe reports its most convincing result when it is most wrong.
//
//
// WHY ONE 400 FACE AND NOT TWO FACES, MEASURED NOT ASSUMED
// --------------------------------------------------------
// Because only family-name local() resolves, a fallback face can only ever be
// sourced from a family's REGULAR file. Measured by canvas ink coverage over
// the Cyrillic sample at 100px (dark-pixel count; advance width alone cannot
// see synthetic bold, because Skia's embolden widens strokes without changing
// advances):
//
//   Arial regular, drawn at 400                    ink 18634   advance 888.13
//   Arial real bold (direct family, 700)           ink 25772   advance 943.36
//   Sofia Sans 700 (the target)                    ink 23273   advance 916.00
//   face{font-weight:400; src:local(Arial)} at 700 ink 23075   advance 888.13  <- synthesised bold
//   face{font-weight:700; src:local(Arial)} at 700 ink 18634   advance 888.13  <- NOT bold
//
// Declaring the face at font-weight:700 makes the engine believe it already
// has a bold face, which SUPPRESSES synthetic bolding and renders the hero
// heading in a regular weight — the exact "measures as a pass while looking
// wrong" failure the plan names. Declaring a single font-weight:400 face lets
// the engine synthesise the bold, landing within 1% of Sofia Sans's own ink.
// A 400-plus-broken-700 pair is worse still: measured at ink 20301 / advance
// 852.78, identical to the last-resort font, because the failing 700 face
// still claims the weight.
//
// Synthetic bolding does not change advance widths, so ONE size-adjust value
// serves both shipped weights and there is no second percentage to calibrate.
//
//
// WHY THIS PROBE BLOCKS NOTHING, AND WHY font-swap.js DOES
// -------------------------------------------------------
// font-swap.js creates its fallback-only pass with
//   Network.setBlockedURLs({ urls: ['*.woff2', '*.woff', '*.ttf'] })   (line 39)
// A URL glob stops matching the moment a query string is appended. Plan 02-08,
// in this same wave, stamps `?v=<filemtime>` onto every stylesheet and script
// URL and DELIBERATELY leaves the Sofia Sans woff2 preload bare — that
// exclusion is the only reason the G-02-4 gate can still see anything. If the
// font URL is ever stamped, pass 1 loads the real font, both passes become
// identical, and font-swap.js reports `maxAbsDeltaPx: 0`: the cleanest-looking
// result a probe can produce while measuring nothing. A stamped woff2 does not
// FAIL that gate, it BLINDS it.
//
// This probe is immune to that failure by construction — it blocks no request,
// measures the fully loaded state, and derives the fallback behaviour by
// substituting font-family on the live element. The hazard is recorded here
// anyway, because this file is where a future reader will be standing when they
// ask why one font probe blocks and the other does not, and the answer includes
// a dependency on a URL owned by a different plan's file.
//
//
// text-wrap: balance IS AN INPUT
// ------------------------------
// src/css/base.css:136 sets `text-wrap: balance` on h1, h2, h3. The scan below
// therefore measures the widest adjustment at which a BALANCED two-line layout
// still fits — not the widest at which a greedy first line still fits. No
// advance-width ratio predicts that threshold, which is why the line-count scan
// is authoritative and the advance-width ratio is a companion reading reported
// alongside it, never a tolerance gate. The measurement itself is unaffected:
// this probe substitutes font-family on the live h1 and font-swap.js measures
// the live h1, so `balance` is in force identically in the scan and in the gate.
//
// Usage:
//   scripts/render-check.sh scripts/probes/font-fallback-metrics.js \
//     'https://torin.bg/new/index.html' 360 640
//

// Fallback candidates worth testing: every family named in --font-sans plus the
// platform faces a visitor is most likely to land on. A candidate that does not
// resolve on THIS machine is reported as such and measured no further.
const CANDIDATES = [
	'Arial',
	'Helvetica Neue',
	'Helvetica',
	'Segoe UI',
	'Roboto',
	'Verdana',
	'Tahoma',
	'Liberation Sans'
];

// Bold local() name forms, tested per candidate purely to RECORD whether they
// resolve. They are not used for calibration: on this engine none of them do,
// which is why the shipped face is a single font-weight:400 block.
const BOLD_NAME_FORMS = {
	'Arial': ['Arial Bold', 'Arial-BoldMT'],
	'Helvetica Neue': ['Helvetica Neue Bold', 'HelveticaNeue-Bold'],
	'Helvetica': ['Helvetica Bold', 'Helvetica-Bold'],
	'Segoe UI': ['Segoe UI Bold'],
	'Roboto': ['Roboto Bold'],
	'Verdana': ['Verdana Bold', 'Verdana-Bold'],
	'Tahoma': ['Tahoma Bold', 'Tahoma-Bold'],
	'Liberation Sans': ['Liberation Sans Bold']
};

// The scan runs ABOVE 100% as well as below, so the two-line cliff is measured
// rather than merely bounded by the scan ceiling. The margin claim ("the
// shipped value sits below the widest value that still sets two lines") is only
// meaningful if that widest value was actually found.
const SCAN_MAX_PCT = 130;
const SCAN_MIN_PCT = 70;
const MATCH_TOLERANCE_PX = 0.5;
// The margin goes on the NARROW side, deliberately and asymmetrically: one
// percent too wide costs a whole 36.8px line the moment the balanced two-line
// layout no longer fits, while too narrow costs nothing until the string would
// collapse from two lines to one — which at 32px/700 across ~36 Cyrillic
// characters would need roughly a HALVING of the advance width, far outside any
// plausible scan result.
const SAFETY_MARGIN_PCT = 3;
const RECOMMEND_FLOOR_PCT = 75;
// Never recommend above 100%. Widening a fallback past its own natural advance
// buys nothing and moves it TOWARD the three-line cliff; 100% is both the
// natural width and the safe ceiling.
const RECOMMEND_CEILING_PCT = 100;

function measureExpression(cfg) {
	return `(async () => {
		const CFG = ${JSON.stringify(cfg)};
		const out = { candidates: {} };

		// --- Part 0: the target. The Sofia Sans line count we are matching.
		const h1 = document.querySelector('h1');
		if (!h1) return { error: 'no h1 found on the page' };
		const cs = getComputedStyle(h1);
		const r0 = h1.getBoundingClientRect();
		const sample = (h1.textContent || '').replace(/\\s+/g, ' ').trim();
		out.h1 = {
			text: sample,
			width: +r0.width.toFixed(1),
			height: +r0.height.toFixed(1),
			fontSize: cs.fontSize,
			lineHeight: cs.lineHeight,
			fontWeight: cs.fontWeight,
			fontFamily: cs.fontFamily,
			textWrap: cs.textWrap || '(not reported)'
		};
		const sofiaHeight = out.h1.height;
		const fontSizePx = parseFloat(cs.fontSize);
		const h1Weight = cs.fontWeight;
		const fontSans = getComputedStyle(document.documentElement)
			.getPropertyValue('--font-sans').trim();
		out.fontSans = fontSans;

		// --- Off-screen ruler for advance-width work.
		const ruler = document.createElement('span');
		ruler.style.cssText = 'position:absolute;left:-99999px;top:0;white-space:nowrap;' +
			'font-size:100px;line-height:1;visibility:hidden;';
		document.body.appendChild(ruler);
		const widthIn = (family, weight, text) => {
			ruler.style.fontFamily = family;
			ruler.style.fontWeight = String(weight);
			ruler.textContent = text;
			return +ruler.getBoundingClientRect().width.toFixed(2);
		};

		// --- Canvas ink coverage. Advance width cannot see synthetic bold
		// --- (Skia widens strokes without touching advances), so weight is
		// --- measured by counting dark pixels instead.
		const cv = document.createElement('canvas');
		cv.width = 1600; cv.height = 200;
		const ctx = cv.getContext('2d', { willReadFrequently: true });
		const inkOf = (family, weight) => {
			ctx.clearRect(0, 0, cv.width, cv.height);
			ctx.fillStyle = '#000';
			ctx.textBaseline = 'alphabetic';
			ctx.font = weight + ' 100px ' + family;
			ctx.fillText(sample, 5, 150);
			const d = ctx.getImageData(0, 0, cv.width, cv.height).data;
			let dark = 0;
			for (let p = 3; p < d.length; p += 4) if (d[p] > 128) dark++;
			return dark;
		};

		// --- The last-resort font. THE trap: an element naming only
		// --- unresolvable faces renders in this, and here it is NARROWER than
		// --- Sofia Sans, so it sets two lines and reads as a perfect match.
		const LAST_RESORT = 'NoSuchFallbackFontXYZ';
		h1.style.fontFamily = '"' + LAST_RESORT + '"';
		void h1.offsetHeight;
		out.lastResort = {
			family: LAST_RESORT,
			h1Height: +h1.getBoundingClientRect().height.toFixed(1),
			advance400: widthIn('"' + LAST_RESORT + '"', 400, sample),
			advance700: widthIn('"' + LAST_RESORT + '"', 700, sample),
			ink700: inkOf('"' + LAST_RESORT + '"', 700),
			note: 'An unresolved face renders here. If its h1Height equals the Sofia height, ' +
				'a broken measurement is indistinguishable from a perfect one without the guard below.'
		};
		h1.style.fontFamily = '';
		void h1.offsetHeight;

		// --- What the page renders TODAY before Sofia Sans arrives: --font-sans
		// --- with 'Sofia Sans' removed. This, not an unadjusted Arial, is the
		// --- 110.4px three-line rendering G-02-4 is about.
		const fallbackToday = fontSans.replace(/^\\s*['"]?Sofia Sans['"]?\\s*,\\s*/, '');
		h1.style.fontFamily = fallbackToday;
		void h1.offsetHeight;
		out.currentFallback = {
			familyList: fallbackToday,
			resolvedFamily: getComputedStyle(h1).fontFamily,
			h1Height: +h1.getBoundingClientRect().height.toFixed(1),
			advance700: widthIn(fallbackToday, 700, sample),
			ink700: inkOf(fallbackToday, 700)
		};
		h1.style.fontFamily = '';
		void h1.offsetHeight;

		// --- Sofia Sans reference advances, measured against the family
		// --- DIRECTLY. (Measuring them through the whole --font-sans list is
		// --- fine too and gives the same number, but naming the family alone
		// --- removes any doubt about which face answered.)
		out.sofia = {
			advance400: widthIn("'Sofia Sans'", 400, sample),
			advance700: widthIn("'Sofia Sans'", 700, sample),
			advanceViaToken700: widthIn(fontSans, 700, sample),
			ink400: inkOf("'Sofia Sans'", 400),
			ink700: inkOf("'Sofia Sans'", 700),
			h1Height: sofiaHeight
		};

		// --- Part 1: which candidates actually resolve on this machine.
		const DETECT = 'mmmmmmmmmmlliWWWWWWWWWW';
		const monoW = widthIn('monospace', 400, DETECT);
		for (const name of CFG.candidates) {
			const w = widthIn('"' + name + '", monospace', 400, DETECT);
			out.candidates[name] = {
				installed: Math.abs(w - monoW) > 0.01,
				detectWidth: w,
				monospaceWidth: monoW
			};
		}

		const styleEl = document.createElement('style');
		document.head.appendChild(styleEl);
		const installed = CFG.candidates.filter(n => out.candidates[n].installed);
		let uid = 0;

		// Declare one face and return the family name, or null if it did not resolve.
		const declare = async (srcList, weight, pct) => {
			const fam = 'FBM-' + (++uid);
			styleEl.textContent =
				'@font-face{font-family:"' + fam + '";font-style:normal;font-weight:' + weight +
				';src:' + srcList + (pct === null ? '' : ';size-adjust:' + pct + '%') + ';}';
			let loadErr = null;
			try { await document.fonts.load(weight + ' ' + fontSizePx + 'px "' + fam + '"', sample); }
			catch (e) { loadErr = String((e && e.message) || e); }
			return { fam: fam, loadErr: loadErr, check: document.fonts.check(weight + ' ' + fontSizePx + 'px "' + fam + '"', sample) };
		};

		for (const name of installed) {
			const c = out.candidates[name];

			// --- Record which local() name forms resolve. This is WHY the
			// --- shipped face is a single 400 block: if no bold form resolves,
			// --- a 700-declared face can only be sourced from a regular file,
			// --- which suppresses synthetic bolding.
			c.localNameForms = [];
			for (const nm of ['' + name].concat(CFG.boldNameForms[name] || [])) {
				const d = await declare("local('" + nm + "')", nm === name ? 400 : 700, null);
				const adv = widthIn('"' + d.fam + '"', nm === name ? 400 : 700, sample);
				c.localNameForms.push({
					name: nm,
					resolved: d.check && Math.abs(adv - (nm === name ? out.lastResort.advance400 : out.lastResort.advance700)) > 0.01,
					fontsCheck: d.check,
					loadError: d.loadErr,
					advance: adv
				});
			}

			// --- The shipped composition, measured: ONE font-weight:400 face
			// --- sourced from the family name, rendered at the h1's own 700.
			const shipped = await declare("local('" + name + "')", 400, null);
			c.faceResolves = shipped.check;
			c.advance400 = widthIn('"' + shipped.fam + '"', 400, sample);
			c.advance700 = widthIn('"' + shipped.fam + '"', 700, sample);
			c.ink400 = inkOf('"' + shipped.fam + '"', 400);
			c.ink700 = inkOf('"' + shipped.fam + '"', 700);
			c.realBoldAdvance = widthIn('"' + name + '"', 700, sample);
			c.realBoldInk = inkOf('"' + name + '"', 700);
			// Synthetic bold present iff the 700 rendering inks materially more
			// than the 400 rendering while the advance is unchanged.
			c.syntheticBold = c.ink700 > c.ink400 * 1.05 && Math.abs(c.advance700 - c.advance400) < 0.01;

			// --- Part 2: the size-adjust scan, at the h1's own weight, with the
			// --- shipped single-400-face composition.
			const scan = [];
			for (let pct = CFG.scanMax; pct >= CFG.scanMin; pct--) {
				// A FRESH family name per iteration is mandatory: reusing one
				// name lets the engine serve the cached face and silently report
				// the PREVIOUS percentage's result.
				const d = await declare("local('" + name + "')", 400, pct);
				// GUARD. Two independent checks, because the last-resort font
				// sets the SAME height as the target and would otherwise be
				// recorded as a match.
				const adv = widthIn('"' + d.fam + '"', 400, sample);
				const expected = c.advance400 * pct / 100;
				const resolved = d.check && Math.abs(adv - expected) <= Math.max(0.5, expected * 0.01);

				// The substituted family and NOTHING else — no comma, no
				// fallback — so a face that failed to resolve cannot quietly
				// borrow another family.
				h1.style.fontFamily = '"' + d.fam + '"';
				void h1.offsetHeight;
				scan.push({
					pct: pct,
					height: +h1.getBoundingClientRect().height.toFixed(1),
					advance: adv,
					expectedAdvance: +expected.toFixed(2),
					resolved: resolved
				});
			}
			h1.style.fontFamily = '';
			void h1.offsetHeight;

			// Only RESOLVED iterations may count as matches.
			const matches = scan.filter(s => s.resolved && Math.abs(s.height - sofiaHeight) <= CFG.tolerance)
				.map(s => s.pct);
			const at100 = scan.find(s => s.pct === 100);
			c.heightAt100 = at100 ? at100.height : null;
			c.unresolvedIterations = scan.filter(s => !s.resolved).length;
			c.scan = scan;
			c.matchPcts = matches;
			c.minPct = matches.length ? Math.min.apply(null, matches) : null;
			c.maxPct = matches.length ? Math.max.apply(null, matches) : null;
			// The safe upper bound is the LESSER of the measured two-line cliff
			// and 100%: widening a fallback past its own natural advance buys
			// nothing and only moves it toward the cliff. The safety margin is
			// then taken BELOW that bound, because one percent too wide costs a
			// whole 36.8px line while too narrow costs nothing measurable.
			c.safeUpperBoundPct = matches.length ? Math.min(CFG.ceiling, c.maxPct) : null;
			c.recommendedPct = matches.length
				? Math.max(CFG.floor, Math.min(c.maxPct, c.safeUpperBoundPct - CFG.margin))
				: null;
			c.marginToCliffPct = (matches.length && c.recommendedPct !== null)
				? +(c.maxPct - c.recommendedPct).toFixed(1) : null;

			// --- Part 3: advance-width ratios at both shipped weights, measured
			// --- on the live Bulgarian h1 string, against what the fallback
			// --- ACTUALLY renders (regular advances at both weights, because
			// --- synthetic bolding does not widen advances).
			c.sofiaWidth400 = out.sofia.advance400;
			c.sofiaWidth700 = out.sofia.advance700;
			c.candWidth400 = c.advance400;
			c.candWidth700 = c.advance700;
			c.ratio400 = c.advance400 > 0 ? +((100 * out.sofia.advance400) / c.advance400).toFixed(1) : null;
			c.ratio700 = c.advance700 > 0 ? +((100 * out.sofia.advance700) / c.advance700).toFixed(1) : null;
		}

		styleEl.remove();
		ruler.remove();
		out.sofiaHeight = sofiaHeight;
		out.h1Weight = h1Weight;
		out.installed = installed;
		out.notInstalled = CFG.candidates.filter(n => !out.candidates[n].installed);
		return out;
	})()`;
}

async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	// Wait for Sofia Sans to actually arrive: Part 0 measures the LOADED state,
	// and measuring it mid-swap would set the whole calibration against the
	// wrong target height.
	await cdp.evaluate(session, '(async () => { await document.fonts.ready; return true; })()');

	const report = await cdp.evaluate(session, measureExpression({
		candidates: CANDIDATES,
		boldNameForms: BOLD_NAME_FORMS,
		scanMax: SCAN_MAX_PCT,
		scanMin: SCAN_MIN_PCT,
		tolerance: MATCH_TOLERANCE_PX,
		margin: SAFETY_MARGIN_PCT,
		floor: RECOMMEND_FLOOR_PCT,
		ceiling: RECOMMEND_CEILING_PCT
	}));

	if (!report || report.error) {
		return {
			viewport: opts.width + 'x' + opts.height,
			url: opts.url,
			error: (report && report.error) || 'probe returned nothing',
			verdict: 'INCONCLUSIVE — ' + ((report && report.error) || 'probe returned nothing')
		};
	}

	report.viewport = opts.width + 'x' + opts.height;
	report.url = opts.url;
	report.scanRange = SCAN_MAX_PCT + '..' + SCAN_MIN_PCT;
	report.matchTolerancePx = MATCH_TOLERANCE_PX;
	report.safetyMarginPct = SAFETY_MARGIN_PCT;
	report.recommendCeilingPct = RECOMMEND_CEILING_PCT;

	// The calibration target: Arial. It is named in --font-sans, it is present
	// on macOS and Windows, and it is the family this scan can measure. Segoe UI
	// (Windows) and Roboto (Android) are NOT installed here and their ratios
	// were not measured; both are narrower than Arial, so an Arial-calibrated
	// adjustment applied to them errs on the safe side — the same line count or
	// fewer, never more. That is a reasoned expectation, not a measurement, and
	// it must not be written up as one.
	const target = report.candidates && report.candidates['Arial'];

	if (!report.installed || !report.installed.length) {
		report.verdict = 'INCONCLUSIVE — no fallback candidate resolved on this machine; nothing can be calibrated';
	} else if (!target || !target.installed) {
		report.verdict = 'INCONCLUSIVE — Arial, the calibration target, did not resolve';
	} else if (target.recommendedPct === null) {
		report.verdict = 'INCONCLUSIVE — no size-adjust percentage in ' + report.scanRange +
			' made Arial set the Sofia Sans line count (' + report.sofiaHeight + 'px)';
	} else {
		// Reported, NOT gated. The scan measures a laid-out, balanced, two-line
		// fit; the ratio measures an unwrapped nowrap span. They are different
		// quantities and the scan sits systematically above the ratio, because
		// Sofia's own longest line does not fill the content box exactly and
		// every pixel of that slack is headroom the scan finds and the ratio
		// cannot see. A fixed tolerance between them has no measurement behind
		// it and would halt a legitimate calibration.
		report.crossCheck = {
			scanRecommendedPct: target.recommendedPct,
			scanTwoLineCliffPct: target.maxPct,
			marginToCliffPct: target.marginToCliffPct,
			pureWidthRatio700: target.ratio700,
			// The LIKE-FOR-LIKE comparison: the measured two-line cliff against
			// the pure advance-width ratio. Expected sign is positive, and it is
			// — the scan sees slack the ratio cannot, because Sofia's own longest
			// balanced line does not fill the 328px box exactly.
			cliffMinusRatio: (typeof target.maxPct === 'number' && typeof target.ratio700 === 'number')
				? +(target.maxPct - target.ratio700).toFixed(1) : null,
			// The recommendation against the ratio. This one is NEGATIVE by
			// construction and is not a finding: the recommendation deliberately
			// carries safety margin below the safe upper bound, so it must sit
			// below the cliff and may well sit below the ratio too.
			scanMinusRatio: (typeof target.recommendedPct === 'number' && typeof target.ratio700 === 'number')
				? +(target.recommendedPct - target.ratio700).toFixed(1) : null,
			note: 'Two readings of two DIFFERENT quantities. The scan is authoritative — it ' +
				'measures the target property (how many lines the hero h1 actually sets) directly, ' +
				'on the live element, under the page\'s own wrapping rules including text-wrap: balance. ' +
				'The ratio is a companion sanity reading, never a gate. Compare cliffMinusRatio, not ' +
				'scanMinusRatio, when checking the expected sign.'
		};
		report.shipped = {
			faces: 1,
			fontWeight: 400,
			sizeAdjustPct: target.recommendedPct,
			why: 'ONE font-weight:400 face. No bold local() name form resolves on this engine, so a ' +
				'font-weight:700 face could only be sourced from a regular file — which suppresses ' +
				'synthetic bolding and renders the heading at the wrong weight (measured: ink ' +
				target.ink400 + ' vs ' + target.ink700 + ' synthesised, against Sofia 700 ink ' +
				report.sofia.ink700 + '). Synthetic bolding leaves advances unchanged, so this one ' +
				'percentage serves both shipped weights.',
			syntheticBoldConfirmed: target.syntheticBold
		};
		report.verdict = 'PASS — size-adjust ' + target.recommendedPct + '% on a single font-weight:400 face, ' +
			'calibrated against Arial, inside the measured two-line range ' +
			target.minPct + '–' + target.maxPct + '% (Sofia h1 ' + report.sofiaHeight +
			'px; today\'s fallback ' + report.currentFallback.h1Height + 'px; Arial at 100% ' +
			target.heightAt100 + 'px)';
	}

	return report;
}

module.exports = { run };
