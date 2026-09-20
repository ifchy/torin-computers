/* photo-resize.js — kontakti.html only (04-03). Shrinks chosen photos in the
   browser and draws the file list.

   AN OPTIMISATION, NEVER THE ENFORCEMENT POINT (P-6): contact-send.php
   enforces count, size, type, decodability and dimensions regardless. Missing
   any API below, the native input submits the originals.

   Sparse comments: 2 KB gzipped budget. Reasoning in 04-03-SUMMARY.md. */
(function () {
	'use strict';
	var form = document.getElementById('contact-form');
	if (!form) { return; }
	var input = document.getElementById('photos');
	var btn = form.querySelector('button[type="submit"]');
	if (!input || !btn) { return; }
	if (!window.createImageBitmap || !window.DataTransfer || !window.File ||
		!document.createElement('canvas').toBlob) { return; }

	var MAX = 1600, Q = 0.82;
	// f original, o replacement, d finished either way. Closures hold the
	// ENTRY, never an index: an index goes stale when a row is removed
	// mid-flight, landing that result on the wrong photo.
	var items = [], urls = [], list = null, left = 0, deferred = false, timer = 0;
	var resting = btn.textContent;
	var glyph = document.getElementById('photos-icon');
	glyph = glyph ? glyph.innerHTML : '';

	function commit() {
		var dt = new DataTransfer();
		items.forEach(function (it) { dt.items.add(it.o || it.f); });
		input.files = dt.files;
	}

	// The preview is the downscaled file; never a second decode.
	function fill(it, li) {
		if (!it.o) { return; }
		var u = URL.createObjectURL(it.o);
		urls.push(u);
		li.querySelector('.filelist__thumb').src = u;
		li.querySelector('.filelist__size').textContent = Math.round(it.o.size / 1024) + ' KB';
	}

	function rows() {
		urls.forEach(function (u) { URL.revokeObjectURL(u); });
		urls = [];
		if (list) { list.parentNode.removeChild(list); list = null; }
		if (!items.length) { return; }
		list = document.createElement('ul');
		list.className = 'filelist';
		items.forEach(function (it) {
			var li = document.createElement('li');
			li.className = 'filelist__row';
			li.innerHTML = '<img class="filelist__thumb" alt="" width="48" height="48">' +
				'<span class="filelist__name"></span><span class="filelist__size"></span>' +
				'<button class="filelist__remove" type="button"></button>';
			li.querySelector('.filelist__name').textContent = it.f.name;
			var rm = li.querySelector('.filelist__remove');
			rm.innerHTML = glyph;
			rm.setAttribute('aria-label', 'Премахнете ' + it.f.name);
			rm.addEventListener('click', function () { drop(it); });
			fill(it, li);
			list.appendChild(li);
		});
		input.parentNode.appendChild(list);
	}

	function drop(it) {
		var i = items.indexOf(it);
		if (i < 0) { return; }
		items.splice(i, 1);
		if (!it.d) { left--; }
		commit();
		rows();
		tick();
	}

	function tick() {
		if (left > 0) { return; }
		commit();
		if (deferred) { send(); }
	}

	function shrink(it) {
		// createImageBitmap, not <img> + drawImage: canvas DISCARDS the
		// orientation tag and a portrait phone photo arrives a quarter turn
		// out. A screenshot has no tag and passes a broken pipeline.
		createImageBitmap(it.f, { imageOrientation: 'from-image' }).then(function (bmp) {
			var s = Math.min(1, MAX / Math.max(bmp.width, bmp.height));
			var c = document.createElement('canvas');
			c.width = Math.max(1, Math.round(bmp.width * s));
			c.height = Math.max(1, Math.round(bmp.height * s));
			c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
			if (bmp.close) { bmp.close(); }
			c.toBlob(function (blob) {
				if (blob) { it.o = new File([blob], 'photo.jpg', { type: 'image/jpeg' }); }
				finished(it);
			}, 'image/jpeg', Q);
		}, function () { finished(it); });
	}

	function finished(it) {
		if (it.d) { return; }
		it.d = true;
		left--;
		rows();
		tick();
	}

	function send() {
		clearTimeout(timer);
		deferred = false;
		btn.disabled = true;
		btn.textContent = 'Изпраща се…';
		form.submit();
	}

	input.addEventListener('change', function () {
		clearTimeout(timer);
		deferred = false;
		btn.disabled = false;
		btn.textContent = resting;
		items = [].map.call(input.files, function (f) {
			return { f: f, o: null, d: false };
		});
		left = items.length;
		rows();
		items.forEach(shrink);
	});

	form.addEventListener('submit', function (e) {
		if (left < 1) {
			btn.disabled = true;
			btn.textContent = 'Изпраща се…';
			return;
		}
		e.preventDefault();
		if (deferred) { return; }
		deferred = true;
		btn.disabled = true;
		btn.textContent = 'Подготвяме снимките…';
		// On expiry the input goes as it stands: the originals.
		timer = setTimeout(send, 10000);
	});
})();
