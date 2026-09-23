---
quick_id: 260923-fno
slug: re-theme-to-theme-a
date: 2026-09-23
status: complete
commits:
  - c200a21 fix(probe) — measure the theme, stop inferring it from the URL
  - d723ef2 feat(theme) — ship Theme A, owner reverses D-02a
---

# Theme A ships — D-02a reversed by the owner

The site now uses the logo's own amber `#fbad03` and electric blue `#0547dc`, replacing the
current-site amber `#ffc70a` and navy `#0e305d`.

## "The wrong colours are live" has an obvious wrong diagnosis, so it was checked first

Every independent source agreed **before** anything was changed:

| Source | Value |
|---|---|
| `OWNER_ANSWERS.md:489` | "use theme B" |
| `OWNER-QUESTIONS.md:590` | "**Theme B** confirmed by the owner" |
| Deleted dev switcher (`ec3595c^`) | «Тема B» → base tokens · «Тема A» → `[data-theme="a"]` |
| Live staging, measured | `--c-brand #ffc70a`, `--c-ink-deep #0e305d`, no `data-theme`, no cookie, no theme-A stylesheet |
| The old `torin.bg` CSS | 4× `#ffc70a`, 4× `#0e305d`, **0×** `#fbad03`, **0×** `#0547dc` |

No crossed labels, no stale cookie, no undeleted override. Theme B really was the current-site
palette the answer described, and it really was what shipped. **The disagreement was about the
choice, not the implementation** — which is why this is recorded as a reversal rather than a fix.

## Why it happened, which is the part worth keeping

`OWNER-QUESTIONS.md:591`, written when the decision was taken:

> "Still worth showing the owner both at `torin.bg/new` before cutover, since it's their brand."

**That was never owned by any plan.** The dev switcher was deleted on schedule at 04-07, and the
owner's first real look at the palette on real pages came after the comparison mechanism was gone.
The choice was made from a written description — "amber + navy, matching the current site" — and a
colour chosen by description is a weaker decision than the same colour chosen by looking.

A follow-up recorded as *worth doing* is not a plan until someone owns it. Same shape as the Phase
3.5 finding that an unowned tree-wide sweep is invisible to every plan; here it cost a full
re-theme performed after the tooling that would have prevented it had been deleted.

## Two things the token abstraction did not cover

The ten tokens were grouped at 04-07 precisely so a re-theme would touch one block, and that
grouping earned its keep — the swap was a single edit. But **grepping the outgoing hexes across
`src/` found two things the block did not reach**, and neither would have surfaced by trusting the
abstraction:

1. **`header.php:129` — `<meta name="theme-color" content="#ffc70a">`.** The one brand colour that
   *cannot* be a CSS token: a meta tag is not styled, and its value goes to the browser chrome (the
   address-bar tint on Android Chrome), not to the page. Now `#fbad03`, carrying a comment saying a
   future re-theme must edit it by hand and that a grep for the outgoing hex is what catches it.
2. **Two stale contrast comments** in `base.css` and `components.css` still asserting Theme B ships
   and quoting its ratios as the live ones.

## Contrast — re-measured, not assumed

`--c-focus-on-dark` (`#ffd84d`) is theme-**invariant**, but every fill beneath it moved, so the
rings had to be re-measured rather than carried forward.

`scripts/probes/focus-rings.js`, 1440×900: **30 controls, 0 without a ring, worst 4.50:1**,
light-surface ring `rgb(4, 64, 194)` at **7.71:1**, zero SC 1.4.11 failures. Those reproduce Phase
2's recorded Theme A figures (worst 4.50:1, light-surface 7.71:1) **exactly** — which is also the
evidence that all ten tokens were transcribed correctly.

| Pairing | Theme A (now) | Theme B (was) | |
|---|---|---|---|
| text on amber button | 9.80:1 | 10.14:1 | AA + AAA |
| link on white | 8.34:1 | 8.49:1 | AA + AAA |
| ink-deep on white | 7.14:1 | 13.13:1 | AA + AAA |
| white on ink-deepest | 11.60:1 | 15.35:1 | AA + AAA |
| light-surface focus ring | **18.58:1** | 15.86:1 | improved |
| worst dark-surface ring | **4.50:1** | 4.69:1 | 1.5× the 3:1 floor |

The number to watch is the worst dark-surface ring: 4.50:1, down from 4.69:1. Still comfortably
above the floor, but it is the first thing to re-check if those fills are ever lightened.

## A probe that would have lied from now on

`focus-rings.js` reported `theme` from `opts.url.indexOf('theme=a')`. Correct only while the
switcher existed and `?theme=a` selected the override — with Theme A baked into `:root` it would
have labelled **every** future run "B" regardless of what was on screen. It already did: the first
post-re-theme run reported `"theme": "B"` while measuring `#fbad03`.

Harmless to the verdict, which is computed from measured ratios — but the label is what a reader
believes, and this artefact exists to be trusted later by someone who cannot re-run it. Now derived
from the computed `--c-brand` token, with the raw value reported alongside so an unrecognised
palette cannot masquerade as either known one.

## Record, not rewrite

The Phase 2 answers in `OWNER_ANSWERS.md` and `OWNER-QUESTIONS.md` are left **exactly as written** —
they are a true record of what was decided then and why. A dated reversal box is appended to #10.

## Correction to this task's own plan

The plan said to verify with `scripts/probes/contrast.js`. That file is **not a runnable probe** —
it exports a `HELPERS` source string injected into the page by other probes, and has no `run()`.
Contrast is covered by `focus-rings.js`, which uses those helpers; the ratios above come from it
and from direct computation.
