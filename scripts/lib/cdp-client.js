//
// cdp-client.js
//
// A minimal Chrome DevTools Protocol client: enough of the protocol to drive a
// headless Chromium for rendered verification (viewport emulation, JS
// evaluation in the page, real key events, screenshots, and no-script mode).
//
// Why this exists rather than Playwright/Puppeteer: this project deliberately
// carries no Node toolchain -- there is no package.json and nothing is
// installed on the build machine (see .claude/CLAUDE.md, "no build step").
// Adding a dev dependency purely to measure contrast ratios would contradict
// that. Node 20 on this machine has no global WebSocket either, so the
// WebSocket framing is implemented here over `net` + `crypto`. It is ~80 lines
// and has no dependencies.
//
// Chromium binary: Brave is what is installed here. Brave IS Chromium, so it
// speaks CDP unchanged. Phase 2 recorded "no automatable browser on this
// machine" after searching only for Chrome/Chromium/Edge/Playwright by name --
// that conclusion was wrong and cost the phase every rendered check.
//
// Usage: see scripts/render-check.sh, which owns browser lifecycle. This module
// only speaks the protocol.
//
const net = require('net');
const crypto = require('crypto');
const http = require('http');

function httpJson(port, path) {
	return new Promise((resolve, reject) => {
		http.get({ host: '127.0.0.1', port: port, path: path }, res => {
			let body = '';
			res.on('data', d => body += d);
			res.on('end', () => {
				try { resolve(JSON.parse(body)); } catch (e) { reject(e); }
			});
		}).on('error', reject);
	});
}

// RFC 6455 client. Only text frames are used; CDP never sends binary or
// fragmented frames for these commands, but continuation is handled by
// buffering until a full frame is present.
class WebSocketClient {
	constructor(url) {
		const u = new URL(url);
		this.socket = net.connect(u.port, u.hostname);
		this.buffer = Buffer.alloc(0);
		this.handlers = [];
		this.ready = new Promise((resolve, reject) => {
			this.socket.on('connect', () => {
				const key = crypto.randomBytes(16).toString('base64');
				this.socket.write(
					'GET ' + u.pathname + u.search + ' HTTP/1.1\r\n' +
					'Host: ' + u.host + '\r\n' +
					'Upgrade: websocket\r\nConnection: Upgrade\r\n' +
					'Sec-WebSocket-Key: ' + key + '\r\n' +
					'Sec-WebSocket-Version: 13\r\n\r\n'
				);
			});
			this.socket.on('error', reject);

			let upgraded = false;
			this.socket.on('data', chunk => {
				if (!upgraded) {
					// The handshake response is ASCII; find the header terminator
					// and hand any trailing frame bytes straight to the parser.
					const text = chunk.toString('binary');
					const end = text.indexOf('\r\n\r\n');
					if (end === -1) return;
					upgraded = true;
					const rest = chunk.slice(Buffer.byteLength(text.slice(0, end + 4), 'binary'));
					resolve();
					if (rest.length) this._consume(rest);
					return;
				}
				this._consume(chunk);
			});
		});
	}

	_consume(chunk) {
		this.buffer = Buffer.concat([this.buffer, chunk]);
		for (;;) {
			const frame = this._readFrame(this.buffer);
			if (!frame) return;
			this.buffer = this.buffer.slice(frame.size);
			if (frame.opcode === 0x1) {
				const msg = JSON.parse(frame.payload.toString('utf8'));
				this.handlers.forEach(h => h(msg));
			}
		}
	}

	_readFrame(buf) {
		if (buf.length < 2) return null;
		const opcode = buf[0] & 0x0f;
		let len = buf[1] & 0x7f;
		let offset = 2;
		if (len === 126) {
			if (buf.length < 4) return null;
			len = buf.readUInt16BE(2); offset = 4;
		} else if (len === 127) {
			if (buf.length < 10) return null;
			len = Number(buf.readBigUInt64BE(2)); offset = 10;
		}
		if (buf.length < offset + len) return null;
		return { opcode: opcode, payload: buf.slice(offset, offset + len), size: offset + len };
	}

	send(obj) {
		const data = Buffer.from(JSON.stringify(obj), 'utf8');
		// Client-to-server frames MUST be masked (RFC 6455 §5.3).
		const mask = crypto.randomBytes(4);
		const masked = Buffer.alloc(data.length);
		for (let i = 0; i < data.length; i++) masked[i] = data[i] ^ mask[i % 4];

		let header;
		if (data.length < 126) {
			header = Buffer.from([0x81, 0x80 | data.length]);
		} else if (data.length < 65536) {
			header = Buffer.alloc(4);
			header[0] = 0x81; header[1] = 0xfe;
			header.writeUInt16BE(data.length, 2);
		} else {
			header = Buffer.alloc(10);
			header[0] = 0x81; header[1] = 0xff;
			header.writeBigUInt64BE(BigInt(data.length), 2);
		}
		this.socket.write(Buffer.concat([header, mask, masked]));
	}

	on(fn) { this.handlers.push(fn); }
	close() { try { this.socket.destroy(); } catch (e) { /* already gone */ } }
}

// Connect to the first page target and return a promise-based `cmd()`.
async function connect(port) {
	const targets = await httpJson(port, '/json/list');
	const page = targets.find(t => t.type === 'page');
	if (!page) throw new Error('no page target on port ' + port);

	const ws = new WebSocketClient(page.webSocketDebuggerUrl);
	await ws.ready;

	let nextId = 0;
	const pending = new Map();

	// CDP sends two kinds of message: replies (carry `id`) and EVENTS (carry
	// `method`, never an `id`). This handler used to dispatch replies and drop
	// events on the floor, which is why `onConsoleError` below could not exist:
	// console output and uncaught exceptions arrive only as events.
	const eventHandlers = new Map();
	ws.on(msg => {
		if (msg.id && pending.has(msg.id)) {
			pending.get(msg.id)(msg);
			pending.delete(msg.id);
			return;
		}
		if (!msg.method) return;
		const hs = eventHandlers.get(msg.method);
		if (!hs) return;
		// A throwing subscriber must not take down the message pump -- the
		// remaining frames in this buffer still need parsing.
		hs.forEach(h => { try { h(msg.params || {}); } catch (e) { /* subscriber's problem */ } });
	});

	const cmd = (method, params) => new Promise(resolve => {
		const id = ++nextId;
		pending.set(id, resolve);
		ws.send({ id: id, method: method, params: params || {} });
	});

	const onEvent = (method, fn) => {
		if (!eventHandlers.has(method)) eventHandlers.set(method, []);
		eventHandlers.get(method).push(fn);
	};

	return { ws: ws, cmd: cmd, onEvent: onEvent };
}

// Open a page at a given viewport and settle it.
//
// IMPORTANT: the viewport MUST be set with Emulation.setDeviceMetricsOverride.
// Chromium's `--window-size` does NOT constrain the layout viewport in headless
// mode -- the page lays out wide and the screenshot is merely cropped, which is
// indistinguishable from genuine horizontal overflow. That trap produced a
// false "mobile layout is broken" report during Phase 2.
async function open(session, url, opts) {
	const o = opts || {};
	const width = o.width || 390;
	const height = o.height || 844;
	await session.cmd('Page.enable');
	if (o.noScript) await session.cmd('Emulation.setScriptExecutionDisabled', { value: true });
	await session.cmd('Emulation.setDeviceMetricsOverride', {
		width: width, height: height, deviceScaleFactor: 1, mobile: width < 900
	});
	await session.cmd('Page.navigate', { url: url });
	await new Promise(r => setTimeout(r, o.settleMs || 3500));
}

// Evaluate an expression in the page and return its value.
async function evaluate(session, expression) {
	const res = await session.cmd('Runtime.evaluate', {
		expression: expression, returnByValue: true, awaitPromise: true
	});
	if (res.result && res.result.exceptionDetails) {
		throw new Error('page exception: ' + JSON.stringify(res.result.exceptionDetails.text || res.result.exceptionDetails));
	}
	return res.result && res.result.result ? res.result.result.value : null;
}

// Press Tab n times using real key events. Programmatic .focus() does not
// reliably match :focus-visible, so focus-ring checks must use this.
async function pressTab(session, times) {
	for (let i = 0; i < times; i++) {
		await session.cmd('Input.dispatchKeyEvent', { type: 'rawKeyDown', windowsVirtualKeyCode: 9, code: 'Tab', key: 'Tab' });
		await session.cmd('Input.dispatchKeyEvent', { type: 'keyUp', windowsVirtualKeyCode: 9, code: 'Tab', key: 'Tab' });
	}
}

async function screenshot(session, file) {
	const res = await session.cmd('Page.captureScreenshot', { format: 'png' });
	require('fs').writeFileSync(file, Buffer.from(res.result.data, 'base64'));
	return file;
}

// Subscribe to console errors and uncaught exceptions in the page.
//
// WHY THIS IS HERE. `cutover-sweep.js` has called `cdp.onConsoleError` since it
// was written, behind a `typeof … === 'function'` guard. This module never
// exported it, so the guard was never true, and every `"consoleErrors": []`
// that probe has ever reported was an empty array no code could have filled --
// a pass asserted over an absent surface, which is precisely the failure mode
// the probes are written to avoid. The guard stays useful (a probe should still
// run against an older client), but it now has something to find.
//
// MUST BE AWAITED before the first navigation: `Runtime.enable` is what starts
// the event flow, and errors thrown during a load that began before it landed
// are simply never delivered.
//
// Both sources are needed and they do not overlap: `consoleAPICalled` carries
// explicit `console.error(...)` calls, `exceptionThrown` carries uncaught
// throws -- and an uncaught throw is the one that stops site.js mid-file.
async function onConsoleError(session, cb) {
	if (typeof session.onEvent !== 'function') {
		throw new Error('cdp session has no onEvent -- connect() is too old to deliver CDP events');
	}

	const describe = (arg) => {
		if (!arg) return '';
		if (typeof arg.description === 'string') return arg.description;
		if ('value' in arg) return String(arg.value);
		return arg.type || '';
	};

	session.onEvent('Runtime.consoleAPICalled', params => {
		if (params.type !== 'error' && params.type !== 'assert') return;
		const text = (params.args || []).map(describe).join(' ').trim();
		cb(text || ('console.' + params.type + ' with no arguments'));
	});

	session.onEvent('Runtime.exceptionThrown', params => {
		const d = params.exceptionDetails || {};
		const text = describe(d.exception) || d.text || 'uncaught exception';
		// First line only: a stack trace in a verdict line buries the verdict.
		cb(String(text).split('\n')[0]);
	});

	await session.cmd('Runtime.enable');
}

// Subscribe to subresource failures: anything the page requests that comes back
// 4xx/5xx, or that never comes back at all.
//
// WHY THIS EXISTS. On 2026-09-23 index.html was deployed referencing six icon
// files that were still local. All six returned 404, the live homepage showed
// six broken images, and the cutover sweep reported 20/20 PASS — because every
// assertion it made (Cyrillic prose, PHP diagnostics, chat widget, console
// errors) is satisfied by a page whose images are missing. A 404 on a
// referenced asset was asserted by nothing. For a script whose own header calls
// it the go/no-go instrument for the root cutover, "the server is actually
// serving what the markup asks for" is close to the most important thing it can
// check.
//
// MEASURED IN THE BROWSER, NOT BY GREPPING THE MARKUP, and that is the whole
// reason it lives here. A regex over src=/href= sees only what is written in
// the HTML: it misses fonts and background images referenced from CSS, anything
// a script requests, and every URL assembled at runtime. The browser knows the
// true request list because it made the requests.
//
// requestWillBeSent IS NOT OPTIONAL BOOKKEEPING. Network.loadingFailed carries
// a requestId and no URL, so without the map a hard failure reports that
// something failed while being unable to say what — which is worse than silence
// because it cannot be acted on. The map is how a failure gets a name.
//
// MUST BE AWAITED before the first navigation, for the same reason as
// onConsoleError: Network.enable is what starts the event flow, and a load
// already in flight when it lands is never reported.
async function onSubresourceFailure(session, cb) {
	if (typeof session.onEvent !== 'function') {
		throw new Error('cdp session has no onEvent -- connect() is too old to deliver CDP events');
	}

	const urls = new Map();

	session.onEvent('Network.requestWillBeSent', params => {
		if (params.requestId && params.request && params.request.url) {
			urls.set(params.requestId, params.request.url);
		}
	});

	session.onEvent('Network.responseReceived', params => {
		const r = params.response || {};
		if (typeof r.status === 'number' && r.status >= 400) {
			cb({ url: r.url || urls.get(params.requestId) || '(url unknown)',
				status: r.status, type: params.type || '', reason: 'http ' + r.status });
		}
		urls.delete(params.requestId);
	});

	session.onEvent('Network.loadingFailed', params => {
		// canceled is not a failure: a navigation away from a page with requests
		// still open cancels them, and this probe navigates twenty times in one
		// session. Reporting those would make every page after the first dirty.
		if (params.canceled) { urls.delete(params.requestId); return; }
		cb({ url: urls.get(params.requestId) || '(url unknown)', status: 0,
			type: params.type || '', reason: params.errorText || 'loading failed' });
		urls.delete(params.requestId);
	});

	await session.cmd('Network.enable');
}

module.exports = { connect, open, evaluate, pressTab, screenshot, httpJson, onConsoleError, onSubresourceFailure };
