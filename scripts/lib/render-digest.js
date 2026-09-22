//
// render-digest.js
//
// Renders the multi-page cutover-sweep probe result as a digest: one line per
// page, and the full record only for pages that did not pass.
//
// WHY THIS EXISTS. The probe now returns twenty page records. Printing them raw
// buries the verdict under ~250 lines of JSON, and a verdict nobody reads is a
// verdict nobody acts on — this script is the go/no-go instrument for a root
// cutover, so the failing page has to be the thing your eye lands on.
//
// It is a separate file rather than a `node -e` blob inside cutover-sweep.sh so
// that the shell stays readable and this stays testable on its own.
//
// CONTRACT WITH THE CALLER: exit 0 having printed a digest, or exit non-zero
// having printed nothing, in which case cutover-sweep.sh prints the probe's raw
// output instead. Unparseable output is usually a harness or browser error, and
// that text IS the diagnosis — it must never be swallowed.
//
// Usage: node scripts/lib/render-digest.js <probe-output.json>
//
const fs = require('fs');

const INDENT = '      ';

function pageName(page) {
	const raw = String(page.requestedUrl || page.url || '');
	const path = raw.split('?')[0].replace(/\/+$/, '');
	return path.slice(path.lastIndexOf('/') + 1) || raw || '(unknown)';
}

function main() {
	const file = process.argv[2];
	if (!file) {
		process.stderr.write('usage: render-digest.js <probe-output.json>\n');
		process.exit(2);
	}

	const result = JSON.parse(fs.readFileSync(file, 'utf8'));

	// A payload without the aggregate key is not this probe's output. Refusing
	// it hands the caller back to the raw print rather than inventing a summary.
	if (!result || typeof result.sweepVerdict !== 'string' || !Array.isArray(result.pages)) {
		process.exit(1);
	}

	const out = [];
	out.push(
		INDENT + result.sweepVerdict + ' — ' + result.pagesChecked + ' pages rendered' +
		' · pass ' + result.passed +
		' · fail ' + result.failed +
		' · inconclusive ' + result.inconclusive +
		' · viewport ' + result.viewport +
		' · min ' + result.minCyrillicTokens + ' Cyrillic tokens'
	);
	// Stated explicitly and always. "0 console errors" means nothing unless the
	// reader knows whether anything was listening — the whole reason this digest
	// carries the flag is that an unmeasured [] once read as a clean result.
	out.push(
		INDENT + 'console errors: ' +
		(result.consoleErrorsMeasured
			? 'measured (CDP Runtime events)'
			: 'NOT MEASURED — empty lists below mean nothing')
	);
	out.push('');

	const widest = result.pages.reduce((w, p) => Math.max(w, pageName(p).length), 0);

	for (const page of result.pages) {
		const name = pageName(page).padEnd(widest, ' ');
		const tokens = String(page.cyrillicTokens === undefined ? '?' : page.cyrillicTokens).padStart(4, ' ');
		const chars = String(page.renderedTextLength === undefined ? '?' : page.renderedTextLength).padStart(6, ' ');
		out.push(
			// 14, not 12: "INCONCLUSIVE" is exactly 12 characters and would butt
			// straight into the page name with no separator.
			INDENT + (page.pageVerdict || '?').padEnd(14, ' ') + name +
			'  ' + tokens + ' tok  ' + chars + ' chars  h1=' + (page.h1Count === undefined ? '?' : page.h1Count)
		);
	}

	const notPassing = result.pages.filter(p => p.pageVerdict !== 'PASS');
	if (notPassing.length > 0) {
		out.push('');
		out.push(INDENT + '── pages that did not pass ──');
		for (const page of notPassing) {
			out.push('');
			out.push(INDENT + page.pageVerdict + '  ' + (page.requestedUrl || page.url));
			for (const f of page.failures || []) out.push(INDENT + '  failure:      ' + f);
			for (const i of page.inconclusive || []) out.push(INDENT + '  inconclusive: ' + i);
			for (const c of page.consoleErrors || []) out.push(INDENT + '  console:      ' + c);
			for (const d of page.diagnostics || []) out.push(INDENT + '  diagnostic:   ' + d);
		}
	}

	process.stdout.write(out.join('\n') + '\n');
}

try {
	main();
} catch (e) {
	// Any failure here means the caller should print the raw output instead.
	process.exit(1);
}
