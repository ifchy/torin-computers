#!/usr/bin/env node
'use strict';

// scripts/trust-signals-probe-check.js — both-directions proof for the
// TRUST_EXPECT_EVIDENCE flag in scripts/probes/trust-signals.js.
//
// Joins the scripts/*-check.* convention: bare Node, zero dependencies, no
// package.json, a readable per-case report, exit 0 on success and 1 on any
// failure.
//
// ─────────────────────────────────────────────────────────────────────────────
// WHY THIS FILE EXISTS
//
// Plan 03.5-02 made the zero-evidence-strip case opt-in, because the homepage
// now serves zero strips by design and the unconditional form would have
// pinned every future homepage run at INCONCLUSIVE. A flag proven in only one
// direction is not proven — and the run that would prove it live needs a
// deploy, which the executor that wrote the flag cannot perform. Worse, the
// change touches a VERDICT expression, and the specific hazard this probe was
// hardened against is the vacuous pass: [].every() is true, so softening a
// gate over an empty list is invisible in review and invisible in a passing
// run.
//
// So this file proves the control flow instead, and it does so against the
// probe's OWN evaluated expression rather than a reimplementation of it:
// run() is called with a fake cdp that captures the string the probe would
// have sent to the browser, and that captured string is evaluated against a
// stubbed DOM. Edit the verdict and this exercises the edit. Delete the
// verdict and the capture fails.
//
// ─────────────────────────────────────────────────────────────────────────────
// WHAT THIS DOES NOT PROVE
//
// Every layout number below is fabricated. Wrap behaviour, adjacency gaps,
// rendered box sizes and overflow are exactly the things that need a real
// engine and a real webfont, and nothing here says anything about them. This
// file is NOT a substitute for:
//
//   scripts/render-check.sh scripts/probes/trust-signals.js <url> 360 640
//   TRUST_EXPECT_EVIDENCE=1 scripts/render-check.sh scripts/probes/trust-signals.js <url> 360 640
//
// It proves the part of the change that has no layout in it, which is the part
// the flag governs.

const vm = require('vm');
const path = require('path');

const PROBE = path.join(__dirname, 'probes', 'trust-signals.js');
const probe = require(PROBE);

// ── capture the probe's own evaluated expression ─────────────────────────────
async function capture() {
	const seen = [];
	const cdp = {
		open: async function () {},
		evaluate: async function (_s, expr) { seen.push(expr); return true; },
	};
	await probe.run(null, cdp, { url: 'https://example.invalid/index.html' });
	// The LAST evaluate call is the measurement expression; the first is the
	// image-settling preamble. Asserting on the count rather than indexing
	// blindly means a probe that stopped measuring fails here instead of
	// evaluating `undefined` and reporting something meaningless.
	if (seen.length < 2) throw new Error('probe issued no measurement evaluate call');
	return seen[seen.length - 1];
}

// ── stubbed DOM ──────────────────────────────────────────────────────────────
function el(opts) {
	const o = Object.assign({
		className: '', classes: [], heading: null, text: '',
		rect: { top: 0, left: 0, right: 0, width: 0, height: 0 },
		attrs: {}, naturalWidth: 0, naturalHeight: 0, complete: false,
	}, opts);
	return {
		className: o.className,
		classList: { contains: function (c) { return o.classes.indexOf(c) !== -1; } },
		querySelector: function (sel) {
			return sel === 'h1, h2' && o.heading !== null ? { textContent: o.heading } : null;
		},
		getBoundingClientRect: function () { return o.rect; },
		naturalWidth: o.naturalWidth,
		naturalHeight: o.naturalHeight,
		complete: o.complete,
		textContent: o.text,
		getAttribute: function (a) { return a in o.attrs ? o.attrs[a] : null; },
		scrollIntoView: function () {},
	};
}

function makeContext(scene) {
	const sections = scene.sections.map(function (s, i) {
		return el({
			className: s.tinted ? 'section section--tint' : 'section',
			classes: s.tinted ? ['section', 'section--tint'] : ['section'],
			heading: 'H' + i,
		});
	});
	const brands = scene.brands.map(function (b, i) {
		return el({
			className: 'brand-row__item' + (b.more ? ' brand-row__item--more' : ''),
			classes: b.more ? ['brand-row__item', 'brand-row__item--more'] : ['brand-row__item'],
			text: b.text,
			rect: { top: 0, left: i * 60, right: i * 60 + 50, width: 50, height: 28 },
		});
	});
	const imgs = scene.evidenceImgs.map(function (m) {
		return el({
			rect: { top: 0, left: 0, right: m.w, width: m.w, height: m.h },
			attrs: { src: m.src, width: String(m.attrW), height: String(m.attrH), loading: 'lazy', decoding: 'async' },
			naturalWidth: m.naturalW, naturalHeight: m.naturalH, complete: m.complete,
		});
	});
	const strips = [];
	for (let i = 0; i < scene.evidenceStrips; i++) {
		strips.push(el({ className: 'evidence', classes: ['evidence'] }));
	}
	const headings = sections.map(function (_, i) { return el({ text: 'H' + i }); });
	const h1n = scene.h1Count === undefined ? 1 : scene.h1Count;
	const h1s = [];
	for (let i = 0; i < h1n; i++) h1s.push(el({ text: 'H1' }));

	function qsa(sel) {
		if (sel === ':scope > section') return sections;
		if (sel === '.brand-row__item') return brands;
		if (sel === '.evidence') return strips;
		if (sel === '.evidence img') return imgs;
		if (sel === 'h1, h2, h3, h4, h5, h6') return headings;
		if (sel === 'h1') return h1s;
		return [];
	}

	const main = { querySelectorAll: qsa };
	const document = {
		querySelector: function (sel) {
			if (sel === 'main') return scene.hasMain === false ? null : main;
			if (sel === '.rating-badge') {
				return scene.badge ? el({ rect: { top: 0, left: 0, right: 120, width: 120, height: 44 } }) : null;
			}
			if (sel === '.brand-row__note') return scene.brandNote ? el({}) : null;
			return null;
		},
		querySelectorAll: qsa,
		documentElement: { scrollWidth: scene.scrollWidth },
	};

	return vm.createContext({
		document: document,
		window: { innerWidth: scene.innerWidth, innerHeight: 640, scrollTo: function () {} },
		location: { href: 'https://example.invalid/index.html' },
		getComputedStyle: function () { return { backgroundColor: 'rgb(255, 255, 255)' }; },
		Math: Math, String: String, Set: Set, Array: Array, Number: Number, JSON: JSON,
	});
}

// The homepage exactly as plan 03.5-02 leaves it: six bands, three tinted, in
// strict alternation, brand row and its trademark note present, badge present,
// ZERO evidence strips.
function homepage() {
	return {
		sections: [
			{ tinted: false }, { tinted: true }, { tinted: false },
			{ tinted: true }, { tinted: false }, { tinted: true },
		],
		brands: [
			{ text: 'Lenovo' }, { text: 'HP' }, { text: 'Dell' }, { text: 'Asus' },
			{ text: 'Acer' }, { text: 'MSI' }, { text: 'и др.', more: true },
		],
		brandNote: true,
		badge: true,
		evidenceStrips: 0,
		evidenceImgs: [],
		scrollWidth: 360,
		innerWidth: 360,
	};
}

function goodImg(src) {
	return { src: src, w: 100, h: 100, attrW: 200, attrH: 200, naturalW: 200, naturalH: 200, complete: true };
}

// ── assertions ───────────────────────────────────────────────────────────────
let failures = 0;
function check(name, actual, expected) {
	const ok = JSON.stringify(actual) === JSON.stringify(expected);
	if (!ok) failures++;
	console.log('  ' + (ok ? 'ok  ' : 'FAIL') + '  ' + name +
		'  got=' + JSON.stringify(actual) + '  expect=' + JSON.stringify(expected));
}

async function main() {
	console.log('serialisation of the flag into the page scope');
	delete process.env.TRUST_EXPECT_EVIDENCE;
	const unset = await capture();
	check('flag unset serialises as false', /expectEvidence = false/.test(unset), true);
	process.env.TRUST_EXPECT_EVIDENCE = '1';
	const set = await capture();
	check('flag set serialises as true', /expectEvidence = true/.test(set), true);
	delete process.env.TRUST_EXPECT_EVIDENCE;

	console.log('');
	console.log('direction 1 — homepage, zero strips, flag UNSET: PASS, nothing inconclusive');
	let r = vm.runInContext(unset, makeContext(homepage()));
	check('verdict', r.verdict, 'PASS');
	check('inconclusive', r.inconclusive, []);
	check('evidenceStrips still reported', r.evidenceStrips, 0);
	check('evidenceExpected', r.evidenceExpected, false);
	check('sectionCount', r.sectionCount, 6);
	check('adjacentTintedPairs', r.adjacentTintedPairs, 0);
	check('horizontalScroll', r.horizontalScroll, false);
	check('ratingBadgePresent', r.ratingBadgePresent, true);

	console.log('');
	console.log('direction 2 — the SAME page with the flag SET: INCONCLUSIVE, naming the flag');
	r = vm.runInContext(set, makeContext(homepage()));
	check('verdict', r.verdict, 'INCONCLUSIVE');
	check('one entry', r.inconclusive.length, 1);
	check('entry names the flag', /TRUST_EXPECT_EVIDENCE was set/.test(r.inconclusive[0]), true);
	check('evidenceExpected', r.evidenceExpected, true);

	console.log('');
	console.log('the box contract still bites wherever there IS a box to measure');
	let s = homepage();
	s.evidenceStrips = 1;
	s.evidenceImgs = [{ src: 'a.jpg', w: 200, h: 200, attrW: 200, attrH: 200, naturalW: 200, naturalH: 200, complete: true }];
	check('wrong box size, flag unset -> FAIL', vm.runInContext(unset, makeContext(s)).verdict, 'FAIL');
	check('wrong box size, flag set   -> FAIL', vm.runInContext(set, makeContext(s)).verdict, 'FAIL');

	s = homepage();
	s.evidenceStrips = 1;
	s.evidenceImgs = [goodImg('a.jpg'), goodImg('b.jpg')];
	r = vm.runInContext(set, makeContext(s));
	check('correct strip -> PASS', r.verdict, 'PASS');
	check('contract applied', r.evidenceContractApplies, true);
	check('boxes ok', r.evidenceBoxesOk, true);

	s = homepage();
	s.evidenceStrips = 1;
	s.evidenceImgs = [{ src: 'a.jpg', w: 100, h: 100, attrW: 100, attrH: 100, naturalW: 200, naturalH: 200, complete: true }];
	check('dishonest width/height attrs -> FAIL', vm.runInContext(unset, makeContext(s)).verdict, 'FAIL');

	console.log('');
	console.log('the second vacuous route the opt-in change could have opened');
	s = homepage();
	s.evidenceStrips = 1;
	s.evidenceImgs = [];
	r = vm.runInContext(unset, makeContext(s));
	check('strip present but empty -> INCONCLUSIVE even with flag unset', r.verdict, 'INCONCLUSIVE');
	check('and it says so', /present but contains no img/.test(r.inconclusive.join(' ')), true);

	console.log('');
	console.log('no other gate was softened');
	s = homepage(); s.brands = [];
	r = vm.runInContext(unset, makeContext(s));
	check('brand row gone -> INCONCLUSIVE', r.verdict, 'INCONCLUSIVE');
	check('and it says so', /no \.brand-row__item/.test(r.inconclusive.join(' ')), true);

	s = homepage(); s.brandNote = false;
	check('trademark note gone -> INCONCLUSIVE', vm.runInContext(unset, makeContext(s)).verdict, 'INCONCLUSIVE');

	s = homepage();
	s.sections = [{ tinted: false }, { tinted: true }, { tinted: true }, { tinted: false }, { tinted: true }];
	r = vm.runInContext(unset, makeContext(s));
	check('two adjacent tinted bands counted', r.adjacentTintedPairs, 1);
	check('two adjacent tinted bands -> FAIL', r.verdict, 'FAIL');

	s = homepage(); s.scrollWidth = 420;
	check('horizontal scroll at 360 -> FAIL', vm.runInContext(unset, makeContext(s)).verdict, 'FAIL');

	s = homepage(); s.h1Count = 2;
	check('two h1 elements -> FAIL', vm.runInContext(unset, makeContext(s)).verdict, 'FAIL');

	s = homepage();
	s.brands = s.brands.slice(0, 6).concat([{ text: 'HP' }, { text: 'и др.', more: true }]);
	check('duplicate brand -> FAIL', vm.runInContext(unset, makeContext(s)).verdict, 'FAIL');

	console.log('');
	if (failures > 0) {
		console.log('VERDICT: FAIL — ' + failures + ' assertion(s) failed');
		process.exit(1);
	}
	console.log('VERDICT: PASS — the flag is proven in both directions and no gate was softened');
	console.log('NOTE: layout numbers here are fabricated. This does NOT replace the live run.');
	process.exit(0);
}

main().catch(function (err) {
	console.error('trust-signals-probe-check: ' + (err && err.message ? err.message : err));
	process.exit(2);
});
