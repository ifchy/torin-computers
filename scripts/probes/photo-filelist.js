//
// photo-filelist.js — rendered proof of the browser half of CONTACT-05.
//
// js/photo-resize.js cannot be verified by grep or by curl. Everything it does
// happens in a layout engine, in response to a file selection no HTTP client
// can make: a file input's FileList is not settable from page script, by
// design. So the selection is made the only way it can be made from outside a
// human's hands — CDP's DOM.setFileInputFiles — and then the page is asked
// what it actually rendered.
//
// FOUR THINGS ONLY A BROWSER CAN ANSWER:
//
//   1. DOES THE SCRIPT RUN AT ALL. Everything else in this plan was verified
//      against the server. If createImageBitmap, DataTransfer or toBlob were
//      missing or the guard were wrong, the file would return silently, the
//      form would still work (the server enforces everything), and no other
//      check in this plan would notice.
//   2. DOES THE DOWNSCALE ACTUALLY HAPPEN. Asserted by BYTES, not by the
//      presence of a row: the input's files after the script has settled must
//      be materially smaller than the originals. A list that renders while the
//      replacement silently failed is the failure this catches.
//   3. NO HORIZONTAL OVERFLOW AT 360px WITH A LONG FILENAME. `min-width: 0` on
//      the flex child is the whole reason the name ellipsises instead of
//      pushing the row wider than the viewport; it is invisible in the CSS
//      until a long name meets a narrow screen.
//   4. THE PREVIEW IS DRAWN. An <img> whose naturalWidth is 0 is a broken
//      object URL, and it looks like an empty box rather than like an error.
//
// The viewport is set by cdp.open() through Emulation.setDeviceMetricsOverride,
// never by a window flag — a window size does not constrain the layout
// viewport, and that trap makes a mobile probe report desktop numbers.
//
// Usage:
//   PHOTO_FILES="/abs/a.jpg:/abs/b.jpg:/abs/c.png" \
//     scripts/render-check.sh scripts/probes/photo-filelist.js \
//     https://torin.bg/new/kontakti.html 360 640
//
async function run(session, cdp, opts) {
	await cdp.open(session, opts.url, opts);

	const files = (process.env.PHOTO_FILES || '').split(':').filter(Boolean);
	if (!files.length) return { error: 'PHOTO_FILES is empty' };

	// Sizes are read here, on the host, rather than in the page: the page can
	// only see the files AFTER the script has already replaced them, so the
	// "before" figure does not exist in there to compare against.
	const fs = require('fs');
	const originalBytes = files.reduce((n, f) => n + fs.statSync(f).size, 0);

	await session.cmd('DOM.enable');
	const doc = await session.cmd('DOM.getDocument', { depth: 1 });
	const rootId = doc.result.root.nodeId;
	const found = await session.cmd('DOM.querySelector', { nodeId: rootId, selector: '#photos' });
	const nodeId = found.result && found.result.nodeId;
	if (!nodeId) return { error: '#photos not found in the rendered document' };

	await session.cmd('DOM.setFileInputFiles', { nodeId: nodeId, files: files });

	// setFileInputFiles does not reliably dispatch `change` across Chromium
	// versions, and the script hangs its entire entry point off that event.
	// Dispatching it explicitly makes the probe test the SCRIPT rather than
	// the protocol's event behaviour.
	await cdp.evaluate(session, `document.getElementById('photos')
		.dispatchEvent(new Event('change', { bubbles: true })), true`);

	// The downscale is three async hops per file (decode, draw, encode). Poll
	// for the rows rather than sleeping a guessed interval, and cap the wait
	// well under the script's own ten-second submit ceiling.
	await cdp.evaluate(session, `new Promise(done => {
		const started = Date.now();
		(function poll() {
			const rows = document.querySelectorAll('.filelist__row').length;
			const drawn = [...document.querySelectorAll('.filelist__thumb')]
				.filter(i => i.naturalWidth > 0).length;
			if ((rows > 0 && drawn === rows) || Date.now() - started > 8000) return done(true);
			setTimeout(poll, 150);
		})();
	})`);

	const page = await cdp.evaluate(session, `(() => {
		const input = document.getElementById('photos');
		const rows = [...document.querySelectorAll('.filelist__row')];
		const names = [...document.querySelectorAll('.filelist__name')];
		const thumbs = [...document.querySelectorAll('.filelist__thumb')];
		const btn = document.querySelector('#contact-form button[type="submit"]');

		let selectedBytes = 0;
		for (const f of input.files) selectedBytes += f.size;

		// The longest rendered name, and whether its box actually clipped.
		const widest = names.reduce((a, b) =>
			(b.textContent.length > (a ? a.textContent.length : 0) ? b : a), null);

		// ::file-selector-button cannot be measured with getBoundingClientRect
		// — it is not an element — so getComputedStyle's pseudo form is the
		// only reading available, and it is enough to tell «the rule applied»
		// from «the rule was never matched» (UI-SPEC C-4's 44px floor and 2px
		// keyline).
		const fsb = getComputedStyle(input, '::file-selector-button');

		return {
			fileButtonMinHeight: fsb.minHeight,
			fileButtonBorder: fsb.borderTopWidth + ' ' + fsb.borderTopStyle,
			scrollWidth: document.documentElement.scrollWidth,
			innerWidth: window.innerWidth,
			rows: rows.length,
			selectedFiles: input.files.length,
			selectedBytes: selectedBytes,
			thumbsDrawn: thumbs.filter(i => i.naturalWidth > 0).length,
			removeButtons: document.querySelectorAll('.filelist__remove').length,
			removeLabelled: [...document.querySelectorAll('.filelist__remove')]
				.filter(b => (b.getAttribute('aria-label') || '').length > 11).length,
			removeGlyphs: document.querySelectorAll('.filelist__remove > svg').length,
			longestName: widest ? widest.textContent : '',
			// scrollWidth > clientWidth on the name box is the ellipsis doing
			// its job: the text is wider than its box and is being clipped.
			longestNameClipped: widest ? widest.scrollWidth > widest.clientWidth : false,
			rowWidth: rows.length ? Math.round(rows[0].getBoundingClientRect().width) : 0,
			submitLabel: btn ? btn.textContent.trim() : '',
			submitDisabled: btn ? btn.disabled : null,
			templateRendered: !!document.getElementById('photos-icon')
		};
	})()`);

	page.originalBytes = originalBytes;
	page.shrankTo = originalBytes ? Math.round((page.selectedBytes / originalBytes) * 100) + '%' : 'n/a';
	page.noOverflow = page.scrollWidth <= page.innerWidth;
	page.pass = page.noOverflow
		&& page.rows === files.length
		&& page.selectedFiles === files.length
		&& page.thumbsDrawn === files.length
		&& page.removeGlyphs === files.length
		&& page.removeLabelled === files.length
		&& page.selectedBytes < originalBytes / 2
		&& page.submitDisabled === false
		&& page.fileButtonMinHeight === '44px';
	return page;
}

module.exports = { run };
