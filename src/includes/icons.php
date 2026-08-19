<?php
// includes/icons.php — PHP 5.2-safe inline-SVG icon library. Emits nothing on
// include; torin_icon($name) RETURNS markup so no path is ever pasted twice.
// Replaces the Font Awesome / liquid-icon webfonts (~75 KB uncompressed to draw
// a handful of glyphs) that the legacy header referenced without ever loading.
// All 16 are hand-authored original geometry on a 24x24 grid, decorative, and
// inherit colour via currentColor. An unknown name returns an empty string
// rather than emitting broken markup.
//
// 15 of the 16 are stroke-only (`fill="none" stroke="currentColor"`). 'star' is
// the single deliberate exception and says why at its own case.
function torin_icon($name) {
	switch ($name) {
	// --- The six owner-priority categories (D-09/D-40, subjects per UI-SPEC C-1)
	case 'cat-1':   // laptop with a fracture line across the lid corner
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 6.5h14v9H5zM2.5 18.5h19M16.5 6.5l-2.4 3.4 3 1-2.2 3"/></svg>';
	case 'cat-2':   // screen panel lifting away, ribbon connector visible
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3.5 7h10v8h-10zM2 18.5h20M16.5 5.5l4 1.2-1.4 8.6-4-1.2M15.6 9h3.4M15.3 11.2h3.4"/></svg>';
	case 'cat-3':   // rising arrow over a laptop silhouette
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 6.5h14v9H5zM2.5 18.5h19M7.5 13l3-3 2.5 2.5 4-4.5M14.5 7.5h2.5V10"/></svg>';
	case 'cat-4':   // droplet over a chip on a board grid
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 2.5S8.5 6.6 8.5 8.8a3.5 3.5 0 0 0 7 0C15.5 6.6 12 2.5 12 2.5ZM8.5 14h7v7h-7zM10.5 14v-1.6M13.5 14v-1.6M8.5 16.5H6M8.5 19H6M15.5 16.5H18M15.5 19H18"/></svg>';
	case 'cat-5':   // fan blades with heat waves
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M10 20.5a7 7 0 1 1 0-14 7 7 0 0 1 0 14ZM10 13.5v.01M10 13.5 6.2 11.3M10 13.5l3.8-2.2M10 13.5v4.4M17.5 3.2c1.1 1.2 1.1 2.4 0 3.6M20.8 2.4c1.6 1.8 1.6 3.6 0 5.4"/></svg>';
	case 'cat-6':   // wrench crossing an unusual instrument outline
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3.5 12.5h8v8h-8zM6 18.2a3 3 0 0 1 4-2.9M21 4.2a3.6 3.6 0 0 1-4.8 4.8l-6 6-2-2 6-6A3.6 3.6 0 0 1 19 2.2l-2.4 2.4 1.8 1.8Z"/></svg>';
	// --- Nine utility icons
	case 'phone':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6.5 3.5 9 8l-2 1.5a12 12 0 0 0 5.5 5.5L14 13l4.5 2.5-1 3a2 2 0 0 1-2.2 1.1A16 16 0 0 1 2.4 6.7a2 2 0 0 1 1.1-2.2Z"/></svg>';
	case 'chat':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20.5 11.5a8 8 0 0 1-11.7 7.1l-5.3 1.4 1.4-5.3A8 8 0 1 1 20.5 11.5Z"/></svg>';
	case 'mail':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 5h18v14H3zM3.5 6l8.5 6 8.5-6"/></svg>';
	case 'pin':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 21.5s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11ZM12 8a2.6 2.6 0 1 1 0 5.2A2.6 2.6 0 0 1 12 8Z"/></svg>';
	case 'clock':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3.5a8.5 8.5 0 1 1 0 17 8.5 8.5 0 0 1 0-17ZM12 7.2V12l3.2 2.1"/></svg>';
	case 'chevron-down':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 9.5 6 6 6-6"/></svg>';
	case 'menu':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3.5 7h17M3.5 12h17M3.5 17h17"/></svg>';
	case 'close':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18"/></svg>';
	case 'check':
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="m4.5 12.5 5 5 10-11"/></svg>';
	case 'star':   // filled five-point star for the Google rating badge (TRUST-02)
		// The ONE deliberate exception to this file's stroke-only house style.
		// Every other glyph is `fill="none" stroke="currentColor"`; a stroked
		// star is an outline of ten thin segments and at 1em it reads as a
		// snowflake or a smudge, not as a star — the shape carries its meaning
		// through its SOLID silhouette. So this one is `fill="currentColor"`
		// with no stroke at all. It is not an oversight and it must not be
		// "normalised" back into the house style.
		return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="m12 3 2.7 5.85 6.3.72-4.7 4.3 1.28 6.23L12 16.98 6.42 20.1 7.7 13.87 3 9.57l6.3-.72Z"/></svg>';
	}
	return '';
}
?>
