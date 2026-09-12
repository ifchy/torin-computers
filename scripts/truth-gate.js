#!/usr/bin/env node
'use strict';

// scripts/truth-gate.js — the Phase 3.5 forbidden-token gate.
//
// Joins the scripts/*-check.* convention deliberately: bare Node 20, zero
// dependencies, no package.json, argv-or-walk input, a per-file hit report, a
// summary, process.exit(1) on any failure. Same shape as
// scripts/seo-metadata-check.js, for the same reason — this repository has no
// build step and must not grow one for a gate.
//
// ─────────────────────────────────────────────────────────────────────────────
// WHY THIS FILE EXISTS AT ALL
//
// Phase 3.5 removes claims about three discontinued service lines. Six plans
// each remove a slice of them. If each plan re-derived its own regex they would
// disagree, and the phase whose entire purpose is that published claims are
// true would be policed by six different definitions of "true". Phase 3
// re-derived a broken PHP-5.2 short-array regex once and then inherited it
// through eight plans. THIS FILE IS THE ONE PLACE THE TOKEN LISTS LIVE. Every
// later plan calls it. No plan restates a token.
//
// It also means no blast-radius figure in this phase has to be typed by hand.
// Every run prints the token ids it applied, so a count published anywhere in
// the phase's artifacts can cite the token set that produced it — the standing
// requirement in 03.5-CONTEXT.md, recorded there after three defensible token
// sets over one unchanged tree returned 95/11, 82/9 and 67/10.
//
// ─────────────────────────────────────────────────────────────────────────────
// USAGE
//
//   node scripts/truth-gate.js
//       Walks src/ and scans every gate-eligible file (.html .htm .php .css
//       .js and .htaccess). Exits 1 if any Class-A token is present.
//
//   node scripts/truth-gate.js src/index.html src/about.html
//       Scans only the named files, so a content plan can gate the files it
//       owns without waiting for the rest of the tree. An explicitly named
//       file is scanned whatever its extension — naming it is the intent.
//
//   node scripts/truth-gate.js src/css
//       A directory argument is walked recursively with the same extension
//       filter as the no-argument case. The no-argument case is exactly this
//       walk seeded with src/.
//
// Exit codes: 0 clean · 1 Class-A token present · 2 usage error (a path that
// does not exist). The three are deliberately distinct: a caller asserting
// `rc -eq 1` must not be satisfied by a crash or by a silently-skipped path.
//
// ─────────────────────────────────────────────────────────────────────────────
// THE TWO CLASSES
//
// CLASS A — hard zero across src/. Any occurrence fails the run. These are the
// withdrawn service vocabulary, the withdrawn vendor names, the withdrawn
// success-rate claim, the two manufacturers the owner excluded from the brand
// row, the retired page filenames, and the pre-reword free-diagnostics claim.
//
// CLASS B — reported on every run, never fatal. Component nouns that
// legitimately survive in symptom, diagnostic and cause prose. The rule for
// judging a survivor, stated here so that whoever runs the gate learns what to
// do with a Class-B report without opening a plan file:
//
//   > A Class-B token may survive only in text that describes a SYMPTOM, a
//   > DIAGNOSTIC OBSERVATION, or a CAUSE. It may not survive in any text that
//   > describes work Torin performs, equipment Torin owns, or an outcome Torin
//   > achieves.
//
// Every Class-B survivor must be justified, one line each, in the SUMMARY of
// the plan that owns its file. The gate cannot make that judgement; it can only
// make sure nobody gets to skip making it.
//
// ─────────────────────────────────────────────────────────────────────────────
// TWO DELIBERATE NON-MEMBERS, recorded so that nobody re-adds them.
//
// 1. src/warrently.html IS DELIBERATELY UNTOUCHED BY THIS PHASE. It reproduces
//    the shop's own published warranty wording, and 03.5-CONTEXT D3.5-06 leaves
//    that wording unruled — the source comment forbidding harmonisation stays
//    in force. That page contains the bare figure 90% inside its own sentence
//    about repair success. The bare figure is therefore NOT a Class-A token.
//    The Class-A entries are the two full CLAIM phrases (the figure immediately
//    followed by the success-rate noun), which do not occur on that page.
//    `node scripts/truth-gate.js src/warrently.html` exits 0, and that is the
//    designed outcome, not an oversight. If it ever starts failing, the Class-A
//    phrase has been loosened — tighten it back; do not allowlist the page.
//
// 2. src/.htaccess is allowlisted for the retired page filenames. The 301 rules
//    must name them; a gate that forbade that would forbid the fix. The
//    positive assertion that the rules exist and are well-formed lives in plan
//    03.5-01 Task 3 instead.
//
// ─────────────────────────────────────────────────────────────────────────────
// MATCHING
//
// Case folding is JavaScript's own String.prototype.toLowerCase(), applied to
// both haystack and needle. It folds Cyrillic correctly and depends on no shell
// locale. This gate is NEVER to be reimplemented as `grep -i`: BSD grep's
// Cyrillic case folding is not dependable, and six other plans call this file.
//
// Comments are scanned, deliberately and not as an oversight. A stale comment
// quoting a withdrawn claim is a withdrawn claim still sitting in the
// repository, and Phase 3 shipped exactly that shape more than once.

const fs = require('fs');
const path = require('path');

const REPO = path.resolve(__dirname, '..');
const SRC = path.join(REPO, 'src');

const EXTS = new Set(['.html', '.htm', '.php', '.css', '.js']);
const BASENAMES = new Set(['.htaccess']);

// A Unicode letter, used for the Class-B word-start test.
const LETTER = /\p{L}/u;

// ── The free-diagnostics matcher ─────────────────────────────────────────────
// Matched by MEANING rather than as a fixed phrase, because the tree carries
// eight distinct phrasings of one claim and a fixed-phrase list misses half of
// them. Measured 2026-09-12, all eight: «безплатна диагностика», «безплатна
// оценка», «диагностиката е безплатна», «диагностиката остава безплатна»,
// «диагностиката, която е безплатна», «диагностиката при нас е безплатна»,
// «диагностиката наистина ли е безплатна», «оценката е безплатна».
//
// The rule: a free-diagnostics CLAIM is any «безплатн…» with «диагностик…» or
// «оценк…» inside the same window. It is Class A unless «първоначал…» is also
// inside that window — that reword is D3.5-06, and it is the only permitted
// form of the claim.
//
// This is why the gate does not fire on src/warrently.html, whose «безплатно»
// is about warranty servicing: that page contains no «диагностик» and no
// «оценк» anywhere at all (measured 2026-09-12, zero occurrences of either).
const FREE_DIAG_WINDOW = 60;
const RE_FREE_ADJ = /безплатн[а-я]*/g;
const RE_FREE_SUBJ = /(диагностик|оценк)[а-я]*/;
const RE_FREE_REWORD = /първоначал/;

function scanFreeDiagnostics(line) {
	const out = [];
	RE_FREE_ADJ.lastIndex = 0;
	let m;
	while ((m = RE_FREE_ADJ.exec(line)) !== null) {
		const from = Math.max(0, m.index - FREE_DIAG_WINDOW);
		const to = Math.min(line.length, m.index + m[0].length + FREE_DIAG_WINDOW);
		const win = line.slice(from, to);
		if (!RE_FREE_SUBJ.test(win)) continue;
		if (RE_FREE_REWORD.test(win)) continue;
		out.push({ index: m.index, text: m[0] });
	}
	return out;
}

// ── Class A ──────────────────────────────────────────────────────────────────
// Two entry shapes: { id, literal, why } for a plain case-insensitive
// substring, and { id, scan, why } for the one claim that has to be matched by
// meaning rather than by a fixed phrase. Kept as ONE top-level array so a
// future reader can amend the list without reading the matcher below it.
const CLASS_A = [
	// -- the chip-level service vocabulary (D3.5-01, D3.5-02) ----------------
	{ id: 'bga', literal: 'bga', why: 'the chip package acronym — the whole service line is withdrawn' },
	{ id: 'reballing-1', literal: 'реболинг', why: 'ball-reattachment, spelling 1 of 2 present in the tree' },
	{ id: 'reballing-2', literal: 'ребоулинг', why: 'ball-reattachment, spelling 2 of 2 present in the tree' },
	{ id: 'resolder', literal: 'дозапояване', why: 'the re-soldering noun, chip-level only' },
	{ id: 'solder-balls', literal: 'топченца', why: 'the solder-ball noun' },
	{ id: 'flux-vendor', literal: 'amtech', why: 'the flux vendor named in the withdrawn equipment claim' },
	{ id: 'infrared', literal: 'инфрачервен', why: 'the heat-source adjective of the withdrawn station' },
	{ id: 'northbridge', literal: 'северен мост', why: 'bridge-chip name — its replacement is withdrawn' },
	{ id: 'southbridge', literal: 'южен мост', why: 'bridge-chip name — its replacement is withdrawn' },
	// «ниво чип» is a VOCABULARY CORRECTION, not an excision. On some pages it
	// labels work D3.5-02 explicitly KEEPS (power circuits, individual failed
	// components); there the fix is «ниво компонент», not deletion. Read the
	// surrounding claim before removing the token — see 03.5-CONTEXT.
	{ id: 'chip-level', literal: 'ниво чип', why: 'the phrase meaning "at chip level"' },

	// -- battery regeneration (D3.5-01) --------------------------------------
	{ id: 'regen-stem-1', literal: 'регенерац', why: 'regeneration stem (регенерация, регенерацията)' },
	{ id: 'regen-stem-2', literal: 'регенерир', why: 'regeneration stem (регенерираме, регенерирана, регенериране)' },
	{ id: 'cell-vendor', literal: 'panasonic', why: 'the cell vendor named in the withdrawn regeneration claim' },
	{ id: 'tape-vendor', literal: 'hilumin', why: 'the welding-tape vendor named in the same claim' },

	// -- the success-rate claim ----------------------------------------------
	// The FULL CLAIM in both forms the tree carries, never the bare figure.
	// See non-member 1 in the header for why that distinction is load-bearing.
	{ id: 'success-claim-pct', literal: '90% успеваемост', why: 'success-rate claim, percent-sign form' },
	{ id: 'success-claim-word', literal: '90 процента успеваемост', why: 'success-rate claim, spelled-out form' },

	// -- the two excluded manufacturers (D3.5-05, OWNER-QUESTIONS #22) -------
	// Both may still be accepted if a customer asks; neither may be
	// ADVERTISED. One shipped in the brand list; the other never did, and this
	// entry is what stops it being helpfully added later.
	{ id: 'excluded-brand-1', literal: 'apple', why: 'excluded from the brand row by the owner 2026-09-11' },
	{ id: 'excluded-brand-2', literal: 'chromebook', why: 'excluded from the brand row by the owner 2026-09-11' },

	// -- the retired page filenames (D3.5-03, D3.5-09, SEO-05) ---------------
	// FOUR, not three: covid.html retires too (D3.5-09). Allowlisted for
	// src/.htaccess only, where the 301 rules must name them.
	{ id: 'retired-za-bateriite', literal: 'za-bateriite.html', why: 'retired URL — 301 to zalivane-technosti.html' },
	{ id: 'retired-laptopi', literal: 'laptopi.html', why: 'retired URL — 301 to index.html' },
	{ id: 'retired-rezervni-chasti', literal: 'rezervni-chasti.html', why: 'retired URL — 301 to ekran-klaviatura-portove.html' },
	{ id: 'retired-covid', literal: 'covid.html', why: 'retired URL — 301 to about.html, which carries the relocated disclosure' },

	// -- the free-diagnostics claim, pre-reword (D3.5-06) --------------------
	{ id: 'free-diagnostics', scan: scanFreeDiagnostics, why: 'free-diagnostics claim not carrying the «първоначална» reword' },
];

// ── Class B ──────────────────────────────────────────────────────────────────
// Reported, never fatal. Matched at a word start only, longest token winning at
// any given position — so «видеочип» reports once as the GPU noun rather than
// twice, and «чипсет» reports as the chipset noun rather than as the bare one.
const CLASS_B = [
	{ id: 'chipset-noun', literal: 'чипсет', why: 'legitimate in cause prose: a chipset that came unseated is a CAUSE' },
	{ id: 'gpu-noun', literal: 'видеочип', why: 'legitimate in symptom/diagnostic prose: a failed GPU is an observation' },
	{ id: 'chip-noun', literal: 'чип', why: 'legitimate in symptom/diagnostic prose' },
	{ id: 'battery-noun', literal: 'батери', why: 'legitimate as a symptom («не държи батерия») and as a component' },
];

// ── Allowlist ────────────────────────────────────────────────────────────────
// Per-token, per-file, with the reason inline. Exactly one entry. Widening it
// is a visible diff in a reviewed file; every run prints it back, so a later
// gate can assert it was not widened by reading a run rather than by
// pattern-matching source.
const ALLOWLIST = [
	{
		file: 'src/.htaccess',
		tokens: ['retired-za-bateriite', 'retired-laptopi', 'retired-rezervni-chasti', 'retired-covid'],
		why: 'the 301 rules must name the retired filenames — a gate forbidding that would forbid the fix (SEO-05)',
	},
];

// ── File discovery ───────────────────────────────────────────────────────────
function eligible(p) {
	const base = path.basename(p);
	if (BASENAMES.has(base)) return true;
	return EXTS.has(path.extname(base).toLowerCase());
}

function walk(dir, out) {
	const entries = fs.readdirSync(dir, { withFileTypes: true });
	entries.sort(function (a, b) { return a.name < b.name ? -1 : a.name > b.name ? 1 : 0; });
	for (const e of entries) {
		const full = path.join(dir, e.name);
		if (e.isDirectory()) walk(full, out);
		else if (e.isFile() && eligible(full)) out.push(full);
	}
}

// An argument that is a DIRECTORY is expanded recursively with the same filter
// as the no-argument case. Handing an argv entry straight to a file read throws
// EISDIR on the directory form; worse, swallowing it would scan nothing and
// exit 0, which is indistinguishable from success. Both failure modes are
// specified against in 03.5-01 Task 2.
function collect(args) {
	const out = [];
	if (args.length === 0) {
		walk(SRC, out);
		return out;
	}
	for (const a of args) {
		const abs = path.resolve(REPO, a);
		let st;
		try {
			st = fs.statSync(abs);
		} catch (err) {
			console.error('truth-gate: no such path: ' + a);
			process.exit(2);
		}
		if (st.isDirectory()) walk(abs, out);
		else out.push(abs); // an explicitly named file is scanned whatever its extension
	}
	return out;
}

// ── Scanning ─────────────────────────────────────────────────────────────────
function allowedTokensFor(rel) {
	const s = new Set();
	for (const a of ALLOWLIST) {
		if (a.file === rel) for (const t of a.tokens) s.add(t);
	}
	return s;
}

function scanFile(abs) {
	const rel = path.relative(REPO, abs).split(path.sep).join('/');
	const hay = fs.readFileSync(abs, 'utf8').toLowerCase();
	const lines = hay.split('\n');
	const allow = allowedTokensFor(rel);
	const hits = [];

	for (let i = 0; i < lines.length; i++) {
		const line = lines[i];
		const no = i + 1;

		for (const t of CLASS_A) {
			if (allow.has(t.id)) continue;
			if (t.literal) {
				let from = 0;
				let at;
				while ((at = line.indexOf(t.literal, from)) !== -1) {
					hits.push({ cls: 'A', rel: rel, line: no, col: at + 1, id: t.id, text: t.literal });
					from = at + 1;
				}
			} else {
				const found = t.scan(line);
				for (const m of found) {
					hits.push({ cls: 'A', rel: rel, line: no, col: m.index + 1, id: t.id, text: m.text });
				}
			}
		}

		for (let p = 0; p < line.length; p++) {
			if (p > 0 && LETTER.test(line[p - 1])) continue; // word start only
			let best = null;
			for (const t of CLASS_B) {
				if (allow.has(t.id)) continue;
				if (line.startsWith(t.literal, p) && (best === null || t.literal.length > best.literal.length)) best = t;
			}
			if (best !== null) {
				hits.push({ cls: 'B', rel: rel, line: no, col: p + 1, id: best.id, text: best.literal });
			}
		}
	}
	return hits;
}

// ── Main ─────────────────────────────────────────────────────────────────────
function main() {
	const files = collect(process.argv.slice(2));

	const all = [];
	for (const f of files) {
		const hits = scanFile(f);
		for (const h of hits) all.push(h);
	}

	for (const h of all) {
		console.log(h.rel + ':' + h.line + ':' + h.id + '  [class ' + h.cls + ' col ' + h.col + '] ' + h.text);
	}

	const aHits = all.filter(function (h) { return h.cls === 'A'; });
	const bHits = all.filter(function (h) { return h.cls === 'B'; });
	const aFiles = new Set(aHits.map(function (h) { return h.rel; }));
	const bFiles = new Set(bHits.map(function (h) { return h.rel; }));

	console.log('');
	console.log('class-A tokens applied: ' + CLASS_A.map(function (t) { return t.id; }).join(' '));
	console.log('class-B tokens applied: ' + CLASS_B.map(function (t) { return t.id; }).join(' '));
	for (const a of ALLOWLIST) {
		console.log('allowlisted: ' + a.file + ' -> ' + a.tokens.join(',') + '  (' + a.why + ')');
	}
	console.log('files scanned: ' + files.length);
	console.log('class-A hits: ' + aHits.length + ' in ' + aFiles.size + ' files');
	console.log('class-B survivors: ' + bHits.length + ' in ' + bFiles.size + ' files  [REPORTED, NEVER FATAL — justify each in the owning plan SUMMARY]');
	console.log(aHits.length === 0
		? 'VERDICT: PASS — no Class-A token present'
		: 'VERDICT: FAIL — Class-A tokens present; a retired-URL hit means a reference to a page that no longer exists, so remove or repoint it');

	process.exit(aHits.length === 0 ? 0 : 1);
}

main();
