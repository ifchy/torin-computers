---
quick_id: 260922-rmw
slug: improve-msg-html-confirmation-copy-and-visibility
date: 2026-09-22
description: Owner-supplied replacement copy for the confirmation page, plus the visibility fix — the two paragraphs are muted and have no gap between them.
---

# Confirmation page — new copy, and make it legible

## What the owner asked for

New copy for the two paragraphs (supplied verbatim), and: "the general visibility of the two
paragraphs need to be improved. text color is slightly lighter in color there is no spacing
between them."

Both complaints are accurate, and they have different causes.

## Cause 1 — the colour

`.svc__intro` carries `color: var(--c-ink-muted)`.

| Token | Value | On white |
|---|---|---|
| `--c-ink-muted` | `#55637a` | **6.08:1** |
| `--c-ink` | `#1f2a3c` | **14.44:1** |

Muted is correct on a service page, where `.svc__intro` is a lede under an `<h1>` introducing
the prose below it. On `msg.html` that same class is carrying the page's *entire* message — the
one thing the visitor came here to read — in the type colour reserved for supporting text.

## Cause 2 — the gap

`base.css` zeroes `p` margins. `.svc__intro` supplies its own `margin-block-start`, so the FIRST
paragraph is spaced from the `<h1>`. The second paragraph has no class at all, and the rule that
would space it — `.svc__block p { margin-block-start: var(--sp-md) }` — only applies inside
`.svc__block`. This section is a plain `.container`. So the two paragraphs butt together with
exactly 0px between them.

## Approach, and the thing not to do

**`.svc__intro` is NOT changed globally.** It is the muted lede on fifteen-plus service pages and
that is a deliberate Phase-2 decision (components.css:1025 documents the type system reasoning).
Recolouring it here would silently restyle every service page to fix one confirmation page.

Instead `msg.html`'s container takes a `confirm` hook and components.css gains one small scoped
block. The owner's markup is applied exactly as supplied, `svc__intro` included — the scope
overrides the colour rather than removing the class.

## Tasks

1. **`src/msg.html`** — replace both paragraphs with the supplied copy; add `confirm` to the
   container class. Update the header comments: they currently document the old sentences, and
   argue at length for the «тръгва» wording that the new copy replaces with «ще получите
   потвърждение». Record that the photo-acknowledgement sentence («Ако сте приложили снимки, те
   също стигнаха до нас.») was dropped by owner decision, and that the assertion about the
   confirmation email is now stronger than the file's original reasoning allowed — so that a
   future reader does not "restore" it as an accident.

2. **`src/css/components.css`** — one scoped block: full `--c-ink`, `--sp-md` between consecutive
   paragraphs, `max-width: var(--measure)` for line length.

## Verify

- Rendered: both paragraphs carry the new text, computed colour `rgb(31, 42, 60)` on both, and a
  real measured gap between them.
- The service pages' `.svc__intro` still computes `rgb(85, 99, 122)` — the global meaning is
  untouched.
- `php -l` clean; full sweep still 20/20.

## Out of scope

The confirmation EMAIL is not touched. Its structure is a separate question the owner asked
alongside this one and is answered in the summary, not changed here.
