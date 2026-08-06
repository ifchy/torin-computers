//
// nav-enhanced.js — scripting-ENABLED nav state check (02-06 N9).
//
// The concern the plan rejected an alternative design over: shipping the nav
// open in markup and collapsing it with script produces a visible flash of an
// open nav on every page load. The chosen <noscript> design should have no such
// flash BY CONSTRUCTION -- the mobile nav is display:none in the base
// stylesheet and is only revealed by an attribute site.js writes on click, so
// there is no state to flash.
//
// This asserts that construction rather than trying to catch a transient
// visually: at load, with scripting enabled and at a mobile viewport, the nav
// list must already be collapsed, the toggle must report aria-expanded=false,
// and the <noscript> override must NOT be applied.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	return await cdp.evaluate(session, `(() => {
		const list = document.querySelector('.nav__list');
		const toggle = document.getElementById('navToggle');
		const disclosure = document.querySelector('.nav__disclosure');
		const isMobile = window.innerWidth < 900;

		const listDisplay = list ? getComputedStyle(list).display : 'absent';
		const noJsApplied = [...document.styleSheets].some(s => (s.href || '').indexOf('no-js.css') !== -1);

		const problems = [];
		// The override is requested from <noscript>; with scripting on it must not load.
		if (noJsApplied) problems.push('no-js.css is applied even though scripting is enabled');
		if (isMobile && listDisplay !== 'none') problems.push('mobile nav list is ' + listDisplay + ' at load, expected none (flash of open nav)');
		if (toggle && toggle.getAttribute('aria-expanded') !== 'false') problems.push('toggle aria-expanded is ' + toggle.getAttribute('aria-expanded') + ' at load, expected false');
		if (disclosure && disclosure.getAttribute('aria-expanded') !== 'false') problems.push('disclosure aria-expanded is ' + disclosure.getAttribute('aria-expanded') + ' at load, expected false');
		if (!isMobile && listDisplay === 'none') problems.push('desktop nav list is none, expected visible');

		return {
			viewport: window.innerWidth + 'x' + window.innerHeight,
			mode: isMobile ? 'mobile' : 'desktop',
			scriptElements: [...document.querySelectorAll('script[src]')].map(s => s.getAttribute('src')),
			noJsStylesheetApplied: noJsApplied,
			navListDisplayAtLoad: listDisplay,
			toggleAriaExpanded: toggle ? toggle.getAttribute('aria-expanded') : 'absent',
			disclosureAriaExpanded: disclosure ? disclosure.getAttribute('aria-expanded') : 'absent',
			problems: problems,
			verdict: problems.length === 0
				? 'PASS — nav is collapsed at load with no open-state to flash'
				: 'FAIL'
		};
	})()`);
}

module.exports = { run };
