---
quick_id: 260923-fno
slug: re-theme-to-theme-a
date: 2026-09-23
description: Owner reverses D-02a — the site ships the logo palette (Theme A) instead of the current-site palette (Theme B).
---

# Re-theme to Theme A — the logo's own colours

## What the owner said

> "there is no theme switcher which is ok, but there has to be some misunderstanding because
> currently exactly the opposite theme of that being chosen by the owner is being used"

## The investigation first, because "wrong colours are live" has an obvious wrong diagnosis

Nothing is mis-wired. Every independent source agrees:

| Source | Value |
|---|---|
| `OWNER_ANSWERS.md:489` | "use theme B" |
| `OWNER-QUESTIONS.md:590` | "**Theme B** confirmed by the owner" |
| Deleted dev switcher (`ec3595c^`) | «Тема B» → base tokens · «Тема A» → `[data-theme="a"]` override |
| `base.css` `:root` | `#ffc70a` + `#0e305d` |
| **Live staging, measured** | `--c-brand #ffc70a`, `--c-ink-deep #0e305d`, no `data-theme`, no cookie, no theme-A stylesheet |
| **The old torin.bg CSS** | 4× `#ffc70a`, 4× `#0e305d`, **0×** `#fbad03`, **0×** `#0547dc` |

So the labels were not crossed and no stale cookie or undeleted stylesheet is overriding
anything. Theme B really is the current-site palette, Theme A really is the logo palette, and
Theme B is what shipped — exactly as recorded. **The disagreement is about the choice, not the
implementation.**

## Why it happened, and the part worth keeping

`OWNER-QUESTIONS.md:591` said, at the time the decision was taken:

> "Still worth showing the owner both at `torin.bg/new` before cutover, since it's their brand."

**That never happened.** The dev switcher was deleted in 04-07 as scheduled cleanup, and the
owner first saw the palette on real pages only after the comparison mechanism was gone. A colour
decision taken from a written description — "amber + navy, matching the current site" — is a much
weaker decision than the same one taken by looking, and the record knew that and said so.

The lesson is not "don't delete dev scaffolding". It is that **a follow-up written into the record
as worth doing is not a plan until someone owns it**; the same shape as the Phase 3.5 finding that
an unowned tree-wide sweep is invisible to every plan.

## Tasks

1. **`src/css/base.css`** — swap the ten themed tokens for the Theme A values recovered from
   `ec3595c^:src/css/theme-a.css`. The ten stay grouped: that grouping was kept at 04-07
   specifically so a future re-theme would touch one block, and this is that re-theme. The
   theme-invariant tokens below are not touched. Rewrite the D-02a comment, which currently
   asserts Theme B ships, to record the reversal and its date.

| Token | Theme B (was) | Theme A (now) |
|---|---|---|
| `--c-brand` | `#ffc70a` | `#fbad03` |
| `--c-brand-dim` | `#e8b400` | `#dd9803` |
| `--c-ink-deep` | `#0e305d` | `#0547dc` |
| `--c-ink-deep-2` | `#1a4f8f` | `#1c56d8` |
| `--c-ink-deepest` | `#0a2547` | `#062f8f` |
| `--c-on-brand` | `#16223a` | `#1a1200` |
| `--c-link` | `#0b4a9c` | `#0440c2` |
| `--c-link-hover` | `#093a7a` | `#0334a0` |
| `--c-shadow-tint` | `14 48 93` | `5 71 220` |
| `--c-hero-glow` | `rgba(255,199,10,.16)` | `rgba(251,173,3,.16)` |

2. **Record the reversal without rewriting history.** The original answers stay exactly as they
   are in `OWNER_ANSWERS.md` and `OWNER-QUESTIONS.md` — they are a true record of what was decided
   then. A dated reversal note is appended to #10, plus a STATE.md decision line.

## Contrast, computed before applying

All pairings pass AA **and** AAA. Margins are tighter than Theme B but none is near a threshold.

| Pairing | Theme A | Theme B (was) |
|---|---|---|
| text on amber button | **9.80:1** | 10.14:1 |
| link on white | **8.34:1** | 8.49:1 |
| ink-deep on white | **7.14:1** | 13.13:1 |
| white on ink-deepest | **11.60:1** | 15.35:1 |

## Verify

- Phase 2 already measured Theme A live via `?theme=a`: 28 controls, **0** without a focus ring,
  worst **4.50:1**; light-surface controls keep a navy ring at **7.71:1**. Baking the override into
  `:root` must reproduce those numbers exactly — if it does not, a token was mistyped.
- `scripts/probes/contrast.js` and `scripts/probes/focus-rings.js` against staging after deploy.
- Full cutover sweep, 20/20.

## Out of scope

The logo files, photography and the holiday banner's own dark fill are untouched. This is the ten
colour tokens and nothing else — the same colour-only boundary `theme-a.css` itself observed.
