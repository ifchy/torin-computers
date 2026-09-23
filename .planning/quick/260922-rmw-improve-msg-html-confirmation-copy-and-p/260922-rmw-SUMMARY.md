---
quick_id: 260922-rmw
slug: improve-msg-html-confirmation-copy-and-visibility
date: 2026-09-22
status: complete
commits:
  - 0e9aa02 content(msg) — owner's replacement copy for the confirmation page
  - 94bfb51 fix(css) — make the confirmation page's prose legible
---

# Confirmation page — new copy, and prose you can actually read

Owner-supplied copy applied verbatim, and the two legibility complaints behind it fixed. Both
complaints were accurate and had **different causes**.

## Cause 1 — "text color is slightly lighter"

`.svc__intro` carries `color: var(--c-ink-muted)`.

| Token | Value | On white |
|---|---|---|
| `--c-ink-muted` | `#55637a` | **6.08:1** |
| `--c-ink` | `#1f2a3c` | **14.44:1** |

Muted is correct on a service page, where `.svc__intro` is a lede introducing the prose beneath
it. On `msg.html` the same class was carrying the page's **entire message** — the one thing the
visitor came to read — in the colour reserved for supporting text.

## Cause 2 — "there is no spacing between them"

`base.css` zeroes `p` margins. `.svc__intro` brings its own `margin-block-start`, so the first
paragraph cleared the `<h1>`. The second paragraph has **no class at all**, and the rule that
would space it — `.svc__block p { margin-block-start: var(--sp-md) }` — applies only inside
`.svc__block`. This section is a plain `.container`. The gap was exactly **0px**.

## What was deliberately NOT done

**`.svc__intro` was not recoloured.** It is the muted lede on fifteen-plus service pages and that
is a deliberate Phase-2 type decision. Fixing one confirmation page by restyling every service
page is the trade avoided here: `msg.html`'s container takes a `confirm` hook, and one scoped
block in components.css supplies full ink, `--sp-md` between consecutive paragraphs, and
`max-width: var(--measure)`.

Adjacent-sibling selector for the spacing rather than a margin on every `p` — the first
paragraph's margin is already owned by `.svc__intro`, and two rules setting the same property on
the same element is how they drift apart later.

## Two relaxed constraints, recorded in the file so neither reads as an accident

The previous copy was built around a stated principle: never assert something about *this* reader
that only a genuine submission could have produced. `msg.html` is a plain URL with no server-side
state — anyone can open it directly — and the two deliberately indistinguishable spam rejections
land here too.

1. **The photograph sentence is gone.** «Ако сте приложили снимки, те също стигнаха до нас.» —
   dropped by owner decision, not lost in an edit.
2. **The confirmation email is now asserted, not hedged.** «тръгва» → «ще получите потвърждение
   за изпратеното съобщение». So the page can now promise a confirmation to someone who submitted
   nothing.

Judged acceptable rather than argued down: the very next sentence sends exactly that reader to
the telephone, which is where the shop wants them anyway. The old wording's cost was a weaker
promise to the far larger number of people who *did* submit. A human wrongly caught by the
sub-three-second time trap lands in the same place.

**The principle is now bounded rather than absolute, and the file says where the line moved to:**
«получихме снимките ви» stays out of bounds, because unlike a promise about a future email it
cannot be recovered from by picking up the phone.

## The confirmation email, documented in the page that promises it

The owner asked how the confirmation is organised. It was not changed — it is now described in
`msg.html`'s header, since that page is the only place a visitor is told to expect one:
plain text, subject «Получихме запитването ви — Торин Компютърс», body composed from `$site`
(hours and first phone, never typed literals), **no attachment** (the visitor has their own
photographs; returning them costs mobile data and is the part most likely to trip a spam filter),
**no reply-to** (the shop is the sender, so a reply reaches a real mailbox rather than the
visitor's own address). Sent inside the success branch, so it goes exactly when the enquiry
reached the shop by **any** channel — including Telegram-only delivery. Its own failure is logged
and dropped: the enquiry is already delivered, and refusing to redirect over a bounced courtesy
message would turn a success into a visible failure.

## Verified live, 2026-09-22

| Check | Result |
|---|---|
| Both paragraphs, new text | present verbatim |
| Computed colour | `rgb(31, 42, 60)` on **both** (was muted on the first, and the second inherited body ink) |
| Measured gap | **16px** (was 0) |
| `max-width` | 653px — bounded to `--measure` |
| `.svc__intro` elsewhere | `rgb(85, 99, 122)` on smyana-na-ekran, zalivane-technosti, kontakti, about — **scope does not leak** |
| Console errors | none |
| Full sweep | **20/20 PASS**, 34 pass / 0 fail / 4 skip |

`msg.html` moved 134 → 148 Cyrillic tokens (the new copy is longer). Every other page's count is
unchanged — that is the evidence the `.confirm` scope reached nothing else.

## Still open, and not introduced here

**No confirmation email has ever been proven to arrive in a real inbox.** See
`mailer-patch-awaiting-mailbox-proof`. The page now asserts one more strongly than before, which
raises the cost of that gap slightly. The check is
`scripts/cutover-sweep.sh --target https://torin.bg/new --submit`, which posts a genuine enquiry
and pages a human — owner-triggered only.
