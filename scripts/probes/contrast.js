//
// contrast.js — shared in-page contrast helpers, injected as a string.
//
// These run INSIDE the page, so they are exported as source text rather than
// as functions.
//
// The `surfaceOf` helper deliberately does NOT walk ancestors' backgroundColor
// to find "the background behind element X". That approach silently skips any
// gradient-painted surface -- `.hero` is painted with radial-gradient +
// linear-gradient, so its computed backgroundColor is transparent and a naive
// walk falls straight through to <body> white. During Phase 2 that produced a
// reported focus ring of 1.38:1 for a ring that actually measures 9.49:1.
//
// Resolve surfaces from their design tokens instead (--c-ink-deep etc.), which
// is also what the stylesheets' own comments reason about.
//
const HELPERS = `
const __lum = c => {
  const [r, g, b] = c.map(v => { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};
const __parse = s => {
  const m = String(s).match(/rgba?\\(([^)]+)\\)/);
  if (m) return m[1].split(',').slice(0, 3).map(x => parseFloat(x));
  const h = String(s).trim().match(/^#([0-9a-fA-F]{6})$/);
  if (h) { const n = parseInt(h[1], 16); return [(n >> 16) & 255, (n >> 8) & 255, n & 255]; }
  const h3 = String(s).trim().match(/^#([0-9a-fA-F]{3})$/);
  if (h3) { const t = h3[1]; return [parseInt(t[0]+t[0],16), parseInt(t[1]+t[1],16), parseInt(t[2]+t[2],16)]; }
  return null;
};
const __ratio = (a, b) => {
  if (!a || !b) return null;
  const l1 = __lum(a), l2 = __lum(b);
  const hi = Math.max(l1, l2), lo = Math.min(l1, l2);
  return +(((hi + 0.05) / (lo + 0.05)).toFixed(2));
};
const __token = n => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
const __tokenRgb = n => __parse(__token(n));
`;

module.exports = { HELPERS };
