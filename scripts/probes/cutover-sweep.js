//
// cutover-sweep.js — the rendered half of the cutover go/no-go (D4-32, plan 04-09).
//
// WHAT THIS IS FOR. After the root swap, "the site is up" is not a finding. This
// project has already shipped a page that returned 200 while rendering a single
// letter where prose belonged, and passed every local check across five plans,
// because a variable collision emptied the body without touching the status. A
// status code settles nothing here. What settles it is: is there Bulgarian prose
// on the page, is the runtime silent, and is the retired third-party widget gone.
//
// THE THREE THINGS MEASURED, AND WHY EACH IS RENDERED RATHER THAN GREPPED:
//
//   1. CYRILLIC TOKEN COUNT. The real tell for the empty-body failure above. It
//      is counted from rendered text (document.body.innerText) rather than from
//      the HTML source, because source-grepping counts Cyrillic sitting inside
//      <script> strings, comments and meta tags that no visitor ever reads. A
//      page can carry plenty of Cyrillic bytes and still paint blank.
//
//   2. RUNTIME DIAGNOSTICS IN THE BODY. Every page on this host is PHP wearing a
//      .html extension. A notice or warning is emitted INTO the response, above
//      the markup, at status 200. Grepping the source finds these too — but this
//      probe also captures console errors, which only a real engine produces.
//
//   3. THE UNSTAFFED CHAT WIDGET. CONTACT-02's requirement is satisfied by the
//      promotion rather than by any work in this phase, which means nothing has
//      ever actively PROVEN it gone from the served pages. It is checked in the
//      DOM after scripts run, because a widget is injected by script: a source
//      grep for the vendor's markup finds nothing on a page that will inject it
//      a moment later.
//
// THE VIEWPORT IS SET BY cdp.open() THROUGH THE HARNESS, from opts — never by a
// browser window flag and never by this module reaching for the emulation domain
// itself. A window size does not constrain the layout viewport, which is the trap
// that makes a mobile probe silently report desktop numbers (see svc-page.js).
//
// THE PROCESS ENVIRONMENT DOES NOT EXIST INSIDE THE EVALUATED STRING. That string
// runs in the browser. Everything the page needs is read HERE and serialised in
// below — the same discipline svc-page.js and trust-signals.js already follow.
//

async function run(session, cdp, opts) {
	// Read here, in Node, ABOVE the evaluate call. Serialised into the page scope
	// as a literal below. Reading it inside the evaluated string would throw a
	// ReferenceError in the browser, and a probe that throws reports nothing.
	const minCyrillicTokens = Number(process.env.SWEEP_MIN_CYRILLIC || 40);

	const consoleErrors = [];
	if (typeof cdp.onConsoleError === 'function') {
		cdp.onConsoleError(session, (text) => consoleErrors.push(String(text)));
	}

	await cdp.open(session, opts.url, opts);

	const result = await cdp.evaluate(session, `(() => {
		const minCyrillicTokens = ${minCyrillicTokens};

		const text = (document.body && document.body.innerText) || '';

		// Rendered Cyrillic runs, not source bytes. A "token" is one unbroken run
		// of Cyrillic letters.
		const cyrillicTokens = (text.match(/[\\u0400-\\u04FF]+/g) || []).length;

		// PHP emits diagnostics INTO the response body at status 200. These are
		// matched against rendered text so a page that merely mentions the word
		// "Warning" inside a <script> string is not flagged.
		const diagnosticPatterns = [
			'Warning:', 'Notice:', 'Fatal error:', 'Parse error:',
			'Deprecated:', 'Uncaught Error', 'Call to undefined'
		];
		const diagnostics = diagnosticPatterns.filter(p => text.indexOf(p) !== -1);

		// The unstaffed chat widget (CONTACT-02). Checked in the live DOM after
		// scripts have run, because a widget is INJECTED — a source grep for it
		// finds nothing on a page that is about to create it.
		const widgetSelectors = [
			'#launcher', '.zopim', '#webWidget',
			'iframe[src*="zopim"]', 'iframe[src*="zendesk"]',
			'script[src*="zopim"]', 'script[src*="zendesk"]'
		];
		const widgetHits = widgetSelectors.filter(s => {
			try { return !!document.querySelector(s); } catch (e) { return false; }
		});
		// Also catch the loader by its global, which survives even if the iframe
		// has not been appended yet.
		const widgetGlobals = ['$zopim', 'zE', 'zEmbed'].filter(g => g in window);

		// A probe whose strongest assertion is over an absent surface reports a
		// pass it did not earn. Anything that asserted NOTHING is named, not
		// folded into PASS — the same discipline as svc-page.js.
		const inconclusive = [];
		if (!document.body) {
			inconclusive.push('no <body> in the rendered document — every check below asserted nothing');
		}
		if (text.trim().length === 0) {
			inconclusive.push('rendered text is empty — the Cyrillic count is vacuously 0, not measured');
		}

		const failures = [];
		if (cyrillicTokens < minCyrillicTokens) {
			failures.push('cyrillic token count ' + cyrillicTokens + ' is below the floor of ' + minCyrillicTokens + ' — the page returned a status but may not have rendered its prose');
		}
		if (diagnostics.length > 0) {
			failures.push('runtime diagnostics rendered into the body: ' + diagnostics.join(', '));
		}
		if (widgetHits.length > 0 || widgetGlobals.length > 0) {
			failures.push('unstaffed chat widget present: ' + widgetHits.concat(widgetGlobals).join(', '));
		}

		return {
			url: location.href,
			viewport: window.innerWidth + 'x' + window.innerHeight,
			title: document.title,
			cyrillicTokens: cyrillicTokens,
			minCyrillicTokens: minCyrillicTokens,
			renderedTextLength: text.trim().length,
			diagnostics: diagnostics,
			widgetHits: widgetHits,
			widgetGlobals: widgetGlobals,
			h1Count: document.querySelectorAll('h1').length,
			inconclusive: inconclusive,
			failures: failures,
			verdict: inconclusive.length > 0
				? 'INCONCLUSIVE'
				: (failures.length === 0 ? 'PASS' : 'FAIL')
		};
	})()`);

	// Console errors are collected in Node, so they are merged after the fact.
	// A page can render perfectly and still be throwing — site.js owns the
	// navigation, and a nav that throws is a nav that does not open.
	result.consoleErrors = consoleErrors;
	if (result.verdict === 'PASS' && consoleErrors.length > 0) {
		result.failures = (result.failures || []).concat(
			['console errors during load: ' + consoleErrors.join(' | ')]
		);
		result.verdict = 'FAIL';
	}

	return result;
}

module.exports = { run };
