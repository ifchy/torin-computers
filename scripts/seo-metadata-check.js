#!/usr/bin/env node
'use strict';

// scripts/seo-metadata-check.js — the site-wide SEO-01 gate.
//
// Joins the scripts/*-check.* convention: exit 0 when every rule passes on
// every served page, exit 1 with a readable per-page per-rule report otherwise.
// Dependency-free, runs on bare Node (no package manifest in this repo).
//
// Two modes:
//   (default)  source mode — reads the repository.
//   --live     additionally fetches each page as served, asserts the served
//              value equals its source literal after HTML decoding, and runs
//              the whole rule set again over the served values.
//
// Every URL this script touches is public. It reads no credential file and
// passes no authentication flag to curl; a Task 1 gate greps this file to
// prove it.
//
// ─────────────────────────────────────────────────────────────────────────────
// THE ENCODING CONTRACT — the decision the whole script rests on.
//
// Length here means UNICODE CODE POINTS ON AN NFC-NORMALISED STRING.
//
// Not bytes. UTF-8 encodes Cyrillic at two units per character, so an
// octet-based count reads a correct 129-character Bulgarian description as 230
// and rejects it — wrong by roughly 1.8x, in the direction that throws out good
// copy and waves through copy that is genuinely too long. Every string this site
// serves is Cyrillic, so this is not a rounding error, it is the whole
// measurement. An octet-length property, an octet-buffer constructor, an
// octet-mode character count and a non-multibyte string-length function are each
// forbidden anywhere in this file, and two separate gates grep for them.
//
// Not UTF-16 units either — though for this content the two numbers coincide.
// The script ASSERTS they coincide (rule `bmp-only`) rather than assuming it,
// because the assumption is only true while no astral character appears.
//
// Bulgarian uses no combining marks, so code points and grapheme clusters are
// also the same number here. The script asserts that too (rule `nfc`) rather
// than trusting it.
//
// The permitted way to take a length is to spread into an array first. The one
// deliberate exception is inside `bmp-only`, which must read the raw UTF-16 unit
// count precisely in order to compare it against the code-point count — that
// comparison is the proof the two definitions agree, so it cannot be written
// without both halves.
// ─────────────────────────────────────────────────────────────────────────────

const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const REPO = path.resolve(__dirname, '..');
const SRC = path.join(REPO, 'src');
const HEADER = path.join(SRC, 'includes', 'header.php');
const SITE_CONFIG = path.join(SRC, 'includes', 'site-config.php');

const LIVE = process.argv.includes('--live');

// ── Exemption lists ──────────────────────────────────────────────────────────
// Each is a named constant carrying its rationale inline, so widening one is a
// visible diff in a reviewed file rather than a silent pass. Every run prints
// all of them back, which is what lets a later gate assert they were not
// widened by reading them out of a run instead of pattern-matching source that
// may legitimately mention a filename for other reasons.

// Pages exempt from the 120-140 code-point description FLOOR. The ceiling still
// applies to them — this list buys a shorter description, never a longer one.
const EXEMPTIONS = {
	// The post-submission confirmation page. Plan 03-06 gave it a short plain
	// description deliberately: it is reached only after a form POST, it has no
	// search intent, and padding it to 120 characters would be writing for this
	// gate rather than for a reader. Floor lowered to 60, ceiling unchanged.
	'msg.html': 'post-POST confirmation page, no search intent (03-06)',
};
const EXEMPT_FLOOR = 60;

// Pages permitted the longer two-word brand suffix rather than the short one.
const LONG_SUFFIX_OK = {
	// Formal warranty terms — the full registered form of the company name is
	// the appropriate register for a document a customer may rely on.
	'warrently.html': 'formal warranty document, full company name is the right register',
	// Statutory EU operational-programme funding disclosure, same reasoning.
	'covid.html': 'statutory funding disclosure, full company name is the right register',
};

// The one page whose SUBJECT is the company, and therefore the only page whose
// title may carry the brand outside the trailing suffix segment.
const BRAND_IS_SUBJECT = {
	'about.html': 'the page is about the company itself',
};

// Pages whose description is legitimately non-commercial, and which are
// therefore reported as `cta-position: n/a` rather than failing it.
//
// DEVIATION from 03-09-PLAN, recorded in the summary. The plan defined this set
// by reference — "the ones in EXEMPTIONS, plus the two documents in
// LONG_SUFFIX_OK" — which silently conflates two unrelated properties: being a
// non-commercial document, and being allowed the long brand suffix. uslovia.html
// is the first and not the second, so under the plan's wording it would have had
// to grow a call to action inside a privacy declaration. That is precisely the
// "writing for a gate rather than for a reader" the plan forbids elsewhere, and
// a privacy policy that solicits phone calls also mis-serves the person who
// searched for it. Made an explicit fourth list rather than widening
// LONG_SUFFIX_OK — that would have changed uslovia's rendered suffix to satisfy
// an unrelated rule.
const NO_CTA_EXPECTED = {
	'msg.html': 'confirmation page — the visitor has already made contact',
	'warrently.html': 'warranty terms — a legal document, not a sales surface',
	'covid.html': 'funding disclosure — a statutory notice, not a sales surface',
	'uslovia.html': 'privacy declaration — a legal document, not a sales surface',
};

// ── Budgets ──────────────────────────────────────────────────────────────────
const TITLE_MAX = 55;
const DESC_MIN = 120;
const DESC_MAX = 140;
const CTA_MAX_POS = 95; // mobile description truncation point

// Call-to-action verb forms, matched as WHOLE letter-runs. Matching a bare
// substring here would be a silent false pass: "пишете" occurs inside
// "опишете" ("describe"), which is not a call to action, and accepting it would
// report a call arriving ~23 code points earlier than it really does.
const CTA_VERBS = [
	'обадете', 'заповядайте', 'донесете', 'пишете', 'изпратете',
	'потърсете', 'свържете', 'елате', 'попитайте',
];

const SHORT_BRAND = 'Торин';
const LONG_BRAND = 'Торин Компютърс';
const SEP = ' · ';

// ── Helpers ──────────────────────────────────────────────────────────────────

// The permitted length expression: spread to code points, then count.
const cp = (s) => [...s].length;

// Comparison normalisation. Two strings differing only in normalisation form
// are identical to a searcher and distinct to a naive equality test, so
// uniqueness is decided after NFC, lowercasing and whitespace collapsing.
const norm = (s) => s.normalize('NFC').toLowerCase().replace(/\s+/g, ' ').trim();

const COMBINING = /[\u0300-\u036f]/;
const LETTER_RUN = /\p{L}+/gu;
const CYRILLIC = /\p{Script=Cyrillic}/u;
const LATIN = /\p{Script=Latin}/u;

function readFallbacks() {
	// The two fallback defaults are READ OUT of the metadata mechanism at
	// runtime rather than copied here. Copying them would let the gate and the
	// thing it guards drift apart, and the whole point of `not-fallback` is that
	// it knows what the mechanism would actually emit.
	const src = fs.readFileSync(HEADER, 'utf8');
	const t = src.match(/!isset\(\$torin_title\)\)\s*\{\s*\$torin_title\s*=\s*'([^']*)'/);
	const d = src.match(/!isset\(\$torin_desc\)\)\s*\{\s*\$torin_desc\s*=\s*'([^']*)'/);
	if (!t || !d) {
		console.error('FATAL: could not read both metadata fallback defaults out of header.php');
		console.error('The mechanism changed shape; this gate must be updated with it, not around it.');
		process.exit(1);
	}
	return { title: t[1], desc: d[1] };
}

function readBaseUrl() {
	const src = fs.readFileSync(SITE_CONFIG, 'utf8');
	const m = src.match(/'base_url'\s*=>\s*'([^']*)'/);
	if (!m) {
		console.error('FATAL: could not read base_url out of site-config.php');
		process.exit(1);
	}
	return m[1];
}

function decodeEntities(s) {
	// header.php escapes with the quote-inclusive flag and an explicit UTF-8
	// charset, so an apostrophe arrives as a numeric reference.
	return s
		.replace(/&#0?39;/g, "'")
		.replace(/&#x27;/gi, "'")
		.replace(/&quot;/g, '"')
		.replace(/&lt;/g, '<')
		.replace(/&gt;/g, '>')
		.replace(/&amp;/g, '&');
}

// A one-line, single-quoted assignment with no concatenation and no variable.
function literalAssignment(src, varName) {
	const re = new RegExp(
		'^[ \\t]*\\$' + varName + "[ \\t]*=[ \\t]*'([^'\\\\]*)'[ \\t]*;[ \\t]*(?://.*)?$",
		'm'
	);
	const m = src.match(re);
	if (!m) return null;
	const line = src.slice(0, m.index).split('\n').length;
	return { value: m[1], line };
}

function includeLine(src) {
	// Match the actual require/include STATEMENT, never any line that merely
	// mentions the file. Three pages carry a head comment naming header.php
	// while explaining the $torin_page global-scope defect, and a bare mention
	// match reads that comment as the include — placing the "include" above the
	// assignments and failing `assigned-before-include` on three correct pages.
	const m = src.match(/^[ \t]*(?:require|include)(?:_once)?[ \t]*\(?[^\n]*header\.php[^\n]*$/m);
	return m ? src.slice(0, m.index).split('\n').length : Infinity;
}

function resolveH1(src) {
	// Every service page carries an explicit 'h1' => literal in its page data
	// array; the seven standalone pages carry a literal <h1> element. Between
	// them that is all 23 pages. An unresolvable h1 FAILS rather than passes —
	// `title-matches-page` is the rule that catches a title describing a
	// different page than the one it labels, and it cannot do that silently.
	const key = src.match(/^[ \t]*'h1'[ \t]*=>[ \t]*'([^'\\]*)'[ \t]*,/m);
	if (key) return key[1];
	const el = src.match(/<h1[^>]*>([^<]+)<\/h1>/);
	if (el) return el[1].trim();
	return null;
}

function tokens(s) {
	return (s.normalize('NFC').toLowerCase().match(LETTER_RUN) || []);
}

function ctaPosition(desc) {
	LETTER_RUN.lastIndex = 0;
	let m;
	const re = new RegExp(LETTER_RUN.source, 'gu');
	while ((m = re.exec(desc)) !== null) {
		if (CTA_VERBS.indexOf(m[0].normalize('NFC').toLowerCase()) !== -1) {
			return cp(desc.slice(0, m.index));
		}
	}
	return -1;
}

// ── The rule set ─────────────────────────────────────────────────────────────

function checkOne(page, title, desc, h1, fb, fail) {
	// nonempty
	if (title.trim() === '') fail(page, 'nonempty', 'title is empty after trimming');
	if (desc.trim() === '') fail(page, 'nonempty', 'description is empty after trimming');

	// not-fallback — on this site the meaningful "empty" state is not a blank
	// string, it is silent inheritance of the site default, which reads
	// identically to every other page that inherited it.
	if (title === fb.title) fail(page, 'not-fallback', 'title equals the site-level fallback default');
	if (desc === fb.desc) fail(page, 'not-fallback', 'description equals the site-level fallback default');

	for (const [label, v] of [['title', title], ['desc', desc]]) {
		// nfc
		if (v !== v.normalize('NFC')) fail(page, 'nfc', label + ' is not NFC-normalised');
		if (COMBINING.test(v)) fail(page, 'nfc', label + ' contains a combining diacritical mark');
		// bmp-only — the one place a raw UTF-16 unit count is read, precisely so
		// it can be compared against the code-point count. See the contract above.
		if (cp(v) !== v.length) fail(page, 'bmp-only', label + ' contains an astral character');
		// no-mixed-script — a Latin character INSIDE a Cyrillic word is unique to
		// a machine and identical to a human, and is the one way two pages can
		// pass every uniqueness check while colliding in the index. Whole-Latin
		// tokens (USB, HDMI, the programme reference) are fine and expected.
		for (const run of v.match(LETTER_RUN) || []) {
			if (CYRILLIC.test(run) && LATIN.test(run)) {
				fail(page, 'no-mixed-script', label + ' token mixes Cyrillic and Latin: ' + run);
			}
		}
	}

	// title-len
	if (cp(title) > TITLE_MAX) fail(page, 'title-len', cp(title) + ' code points, max ' + TITLE_MAX);

	// desc-len
	const floor = Object.prototype.hasOwnProperty.call(EXEMPTIONS, page) ? EXEMPT_FLOOR : DESC_MIN;
	const dl = cp(desc);
	if (dl < floor) fail(page, 'desc-len', dl + ' code points, min ' + floor);
	if (dl > DESC_MAX) fail(page, 'desc-len', dl + ' code points, max ' + DESC_MAX);

	// suffix / no-legacy-suffix
	const segs = title.split(SEP);
	const suffix = segs.length > 1 ? segs[segs.length - 1] : null;
	const allowed = Object.prototype.hasOwnProperty.call(LONG_SUFFIX_OK, page)
		? [SHORT_BRAND, LONG_BRAND]
		: [SHORT_BRAND];
	if (suffix === null) {
		fail(page, 'suffix', 'title has no separator-delimited brand suffix');
	} else if (allowed.indexOf(suffix) === -1) {
		// Comparing case-insensitively FIRST separates the two failures: a suffix
		// that is the right words in the wrong case is the legacy all-caps form
		// RESEARCH measured at 228 px — 38% of the desktop title budget spent on
		// a string nobody searches for.
		const caseless = allowed.some((a) => a.toLowerCase() === suffix.toLowerCase());
		if (caseless) fail(page, 'no-legacy-suffix', 'title carries the all-caps brand suffix: ' + suffix);
		else fail(page, 'suffix', 'trailing segment is not an allowed brand form: ' + suffix);
	}

	// keyword-first — structurally: the brand may occupy only the trailing
	// segment, and may never open the title.
	const lead = segs[0];
	if (lead.toLowerCase().startsWith(SHORT_BRAND.toLowerCase())) {
		fail(page, 'keyword-first', 'title opens with the brand rather than a keyword');
	}
	if (!Object.prototype.hasOwnProperty.call(BRAND_IS_SUBJECT, page)) {
		for (let i = 0; i < segs.length - 1; i++) {
			if (segs[i].toLowerCase().includes(SHORT_BRAND.toLowerCase())) {
				fail(page, 'keyword-first', 'brand appears outside the suffix segment');
				break;
			}
		}
	}

	// title-matches-page — semantically: the leading segment must share a
	// substantial word with the page's own heading, so a title cannot describe a
	// different page than the one it labels.
	if (h1 === null) {
		fail(page, 'title-matches-page', 'unresolved — no h1 key and no literal <h1> element');
	} else {
		const hs = new Set(tokens(h1).filter((t) => cp(t) >= 5));
		const shared = tokens(lead).filter((t) => cp(t) >= 5 && hs.has(t));
		if (shared.length === 0) {
			fail(page, 'title-matches-page', 'leading segment shares no 5+ letter word with the h1: ' + h1);
		}
	}

	// cta-position — never skipped silently.
	if (Object.prototype.hasOwnProperty.call(NO_CTA_EXPECTED, page)) {
		return 'n/a';
	}
	const pos = ctaPosition(desc);
	if (pos === -1) fail(page, 'cta-position', 'description contains no call-to-action verb');
	else if (pos >= CTA_MAX_POS) fail(page, 'cta-position', 'call begins at code point ' + pos + ', must be under ' + CTA_MAX_POS);
	return pos;
}

function checkSet(entries, fail) {
	const byTitle = new Map();
	const byDesc = new Map();
	const byLead = new Map();
	const byHead = new Map();
	for (const e of entries) {
		if (e.title === null || e.desc === null) continue;
		const t = norm(e.title);
		const d = norm(e.desc);
		const l = norm(e.title.split(SEP)[0]);
		const h = [...norm(e.desc)].slice(0, 60).join('');
		for (const [map, key, rule, what] of [
			[byTitle, t, 'unique-title', 'title'],
			[byDesc, d, 'unique-desc', 'description'],
			[byLead, l, 'distinct-lead', 'title leading segment'],
			[byHead, h, 'distinct-desc-head', 'first 60 code points of the description'],
		]) {
			if (map.has(key)) fail(e.page, rule, what + ' collides with ' + map.get(key));
			else map.set(key, e.page);
		}
	}
}

// ── Collection ───────────────────────────────────────────────────────────────

function collectSource(fb) {
	const pages = fs.readdirSync(SRC).filter((f) => f.endsWith('.html')).sort();
	const out = [];
	for (const page of pages) {
		const src = fs.readFileSync(path.join(SRC, page), 'utf8');
		const t = literalAssignment(src, 'torin_title');
		const d = literalAssignment(src, 'torin_desc');
		out.push({
			page,
			title: t ? t.value : null,
			desc: d ? d.value : null,
			titleLine: t ? t.line : null,
			descLine: d ? d.line : null,
			incLine: includeLine(src),
			h1: resolveH1(src),
		});
	}
	return out;
}

function fetchLive(url) {
	return execFileSync('curl', ['-sS', '--fail', url], {
		encoding: 'utf8',
		maxBuffer: 32 * 1024 * 1024,
	});
}

// ── Main ─────────────────────────────────────────────────────────────────────

function main() {
	const fb = readFallbacks();
	const entries = collectSource(fb);

	const failures = new Map(); // page -> [ "rule detail", ... ]
	const fail = (page, rule, detail) => {
		if (!failures.has(page)) failures.set(page, []);
		failures.get(page).push(rule + ' ' + detail);
	};

	// The three lists, printed back sorted so a later gate can read them out of
	// a run rather than out of the source.
	console.log('exemptions: ' + Object.keys(EXEMPTIONS).sort().join(' '));
	console.log('long-suffix: ' + Object.keys(LONG_SUFFIX_OK).sort().join(' '));
	console.log('brand-is-subject: ' + Object.keys(BRAND_IS_SUBJECT).sort().join(' '));
	console.log('no-cta-expected: ' + Object.keys(NO_CTA_EXPECTED).sort().join(' '));
	console.log('mode: ' + (LIVE ? 'source+live' : 'source'));
	console.log('');

	const ctaPos = new Map();

	for (const e of entries) {
		// literal
		if (e.title === null) fail(e.page, 'literal', '$torin_title is not a one-line single-quoted string');
		if (e.desc === null) fail(e.page, 'literal', '$torin_desc is not a one-line single-quoted string');

		// assigned-before-include
		if (e.titleLine !== null && e.titleLine >= e.incLine) {
			fail(e.page, 'assigned-before-include', '$torin_title is assigned at or after the header include');
		}
		if (e.descLine !== null && e.descLine >= e.incLine) {
			fail(e.page, 'assigned-before-include', '$torin_desc is assigned at or after the header include');
		}

		if (e.title !== null && e.desc !== null) {
			const pos = checkOne(e.page, e.title, e.desc, e.h1, fb, fail);
			ctaPos.set(e.page, pos);
		}
	}

	checkSet(entries, fail);

	// Live half: the served value must equal its source literal after decoding.
	// A disagreement means the escaping or the before-the-include ordering broke,
	// which is the one failure mode a source-only check cannot see.
	if (LIVE) {
		const base = readBaseUrl();
		for (const e of entries) {
			let html;
			try {
				html = fetchLive(base + e.page);
			} catch (err) {
				fail(e.page, 'live-fetch', 'could not fetch ' + base + e.page);
				continue;
			}
			const tm = html.match(/<title>([\s\S]*?)<\/title>/);
			const dm = html.match(/<meta\s+name="description"\s+content="([^"]*)"/);
			if (!tm) { fail(e.page, 'live-title', 'served page has no title element'); continue; }
			if (!dm) { fail(e.page, 'live-desc', 'served page has no description meta'); continue; }
			const st = decodeEntities(tm[1]).trim();
			const sd = decodeEntities(dm[1]);
			if (e.title !== null && st !== e.title) {
				fail(e.page, 'served-matches-source', 'served title differs from the source literal');
			}
			if (e.desc !== null && sd !== e.desc) {
				fail(e.page, 'served-matches-source', 'served description differs from the source literal');
			}
			checkOne(e.page, st, sd, e.h1, fb, (p, r, d) => fail(p, 'live/' + r, d));
		}
	}

	// One status line per page, clean or not: a failure-only report cannot tell
	// "checked and clean" apart from "never checked", so three broken pages
	// emitting eight rows each would read as broad coverage.
	for (const e of entries) {
		const rows = failures.get(e.page);
		if (!rows || rows.length === 0) {
			const pos = ctaPos.get(e.page);
			const note = pos === 'n/a' ? '  [cta-position: n/a]' : '';
			console.log(e.page + ': ok' + note);
		} else {
			for (const r of rows) console.log(e.page + ': ' + r);
		}
	}

	console.log('');
	console.log('pages checked: ' + entries.length);
	const bad = entries.filter((e) => (failures.get(e.page) || []).length > 0).length;
	console.log('pages failing: ' + bad);
	process.exit(bad === 0 ? 0 : 1);
}

main();
