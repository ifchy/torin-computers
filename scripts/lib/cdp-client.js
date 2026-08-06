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
	ws.on(msg => {
		if (msg.id && pending.has(msg.id)) {
			pending.get(msg.id)(msg);
			pending.delete(msg.id);
		}
	});

	const cmd = (method, params) => new Promise(resolve => {
		const id = ++nextId;
		pending.set(id, resolve);
		ws.send({ id: id, method: method, params: params || {} });
	});

	return { ws: ws, cmd: cmd };
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

module.exports = { connect, open, evaluate, pressTab, screenshot, httpJson };
