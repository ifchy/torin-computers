//
// no-script-nav.js — no-script navigation + overflow check (WINDOWS entries 8, 9).
//
// Loads the page with script execution disabled (what a user agent with
// scripting blocked sees) and asserts:
//   - all five top-level nav items and all six category links are visible,
//   - neither disclosure control is visible or Tab-reachable (with no script
//     there is no state for them to toggle, so they must not be offered),
//   - scrollWidth <= innerWidth, i.e. the no-script layout introduces no
//     horizontal overflow at this viewport.
//
// Note: Emulation.setScriptExecutionDisabled blocks the PAGE's scripts; the
// debugger's own Runtime.evaluate still works, which is what makes this
// measurable at all.
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, Object.assign({}, opts, { noScript: true }));

	return await cdp.evaluate(session, `(() => {
		const visible = el => {
			if (!el) return false;
			const cs = getComputedStyle(el);
			if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) return false;
			const r = el.getBoundingClientRect();
			return r.width > 0 && r.height > 0;
		};

		const topItems = [...document.querySelectorAll('.nav__list > li')];
		const navLinks = [...document.querySelectorAll('.nav__link')];
		const subLinks = [...document.querySelectorAll('.nav__sublist a, .nav__sub a')];
		const toggle = document.getElementById('navToggle');
		const disclosure = document.querySelector('.nav__disclosure');

		// Tab-reachability without running page script: an element is reachable
		// if it is focusable and not display:none / hidden.
		const tabReachable = el => {
			if (!visible(el)) return false;
			if (el.hasAttribute('disabled')) return false;
			const ti = el.getAttribute('tabindex');
			if (ti !== null && Number(ti) < 0) return false;
			return true;
		};

		const visibleTop = topItems.filter(visible);
		const visibleNavLinks = navLinks.filter(visible);
		const visibleSubLinks = subLinks.filter(visible);

		const overflow = document.documentElement.scrollWidth - window.innerWidth;

		const problems = [];
		if (visibleTop.length !== 5) problems.push('expected 5 visible top-level items, got ' + visibleTop.length);
		if (visibleNavLinks.length < 4) problems.push('expected >=4 visible top-level links, got ' + visibleNavLinks.length);
		if (visibleSubLinks.length !== 6) problems.push('expected 6 visible category links, got ' + visibleSubLinks.length);
		if (tabReachable(toggle)) problems.push('hamburger toggle is Tab-reachable with scripting disabled');
		if (tabReachable(disclosure)) problems.push('services disclosure is Tab-reachable with scripting disabled');
		if (overflow > 0) problems.push('horizontal overflow of ' + overflow + 'px');

		return {
			viewport: window.innerWidth + 'x' + window.innerHeight,
			scriptingDisabled: true,
			noJsStylesheetApplied: [...document.styleSheets].some(s => (s.href || '').indexOf('no-js.css') !== -1),
			visibleTopLevelItems: visibleTop.length,
			visibleTopLevelLinks: visibleNavLinks.map(a => a.textContent.trim()),
			visibleCategoryLinks: visibleSubLinks.map(a => a.textContent.trim()),
			toggleVisible: visible(toggle),
			disclosureVisible: visible(disclosure),
			toggleTabReachable: tabReachable(toggle),
			disclosureTabReachable: tabReachable(disclosure),
			scrollWidth: document.documentElement.scrollWidth,
			innerWidth: window.innerWidth,
			overflowPx: overflow,
			problems: problems,
			verdict: problems.length === 0 ? 'PASS' : 'FAIL'
		};
	})()`);
}

module.exports = { run };
