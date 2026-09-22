---
quick_id: 260922-q0v
slug: tidy-the-kontakti-contact-form
date: 2026-09-22
status: complete
commits:
  - 9a36653 fix(css) — make the `hidden` attribute actually binding
  - 1759838 feat(form) — validate the contact form on submit, not while typing
  - d684709 feat(form) — hints become placeholders, reassurances go, validator wired
---

# Tidy the contact form

Secondary text around the seven controls: **14 lines → 2**. Warnings now appear only when the
visitor presses the button, and clear as they are fixed.

## The owner was describing a bug, not a design preference

"the warning msgs not to be visible all the time even when fields are filled in" — measured on
staging before any change: **all 7 `.field__error` nodes carried `hidden`, and all 7 rendered
anyway**, `display: flex`, `visible: true`.

`hidden` is enforced only by the UA stylesheet's `[hidden] { display: none }`, and an
author-origin `display` declaration beats a UA-origin one regardless of specificity.
`components.css:853` set `display: flex` on `.field__error`, so the attribute had never once
taken effect.

`contact-form.php` emits `hidden` conditionally on each node and explicitly documents toggling it
as the mechanism, choosing it over toggling the `aria-describedby` id list. **That intent had been
silently cancelled by CSS for as long as both files existed** — nothing in the markup showed it,
and the server-side error path looked correct in isolation.

The visible consequence: every visitor was told «Моля, попълнете полето.» under a field they had
not typed in yet, with an alert icon beside it. The form read as if it were rejecting them before
they started.

Fixed globally in `base.css` (`[hidden] { display: none !important }`) rather than as
`.field__error[hidden]`. The narrow fix repairs this instance and leaves the trap armed for the
next component that pairs a `display` rule with a sometimes-hidden node. Checked before landing:
those seven nodes are the only `hidden` users in the tree, so nothing relied on the old behaviour.

## Decisions the owner made

| Question | Answer |
|---|---|
| How far do placeholders go? | Real hints (Модел, Какво прави) become placeholders; the Телефон/Имейл **reassurances are dropped**, not rewritten as fake examples |
| After a failed submit? | Flagged fields **clear live** as they are fixed; untouched valid fields are never flagged |
| Снимки (file input, no placeholder possible) | Shortened to «До 5 снимки, всяка до 10 MB.», kept visible |

The visible `<label>` stays on every field. A placeholder that replaces its label is the familiar
accessibility defect — the field loses its name the moment it has a value. Label names the field;
placeholder shows the shape of an answer.

Dropping «Ще получите потвърждение…» loses nothing real: `msg.html:76` already tells the visitor
about the confirmation email at the moment it becomes true, rather than as small print beside an
empty field. Verified before writing it into a comment.

## The trap in wiring the validator

`photo-resize.js:130` registers its own `submit` listener on the same form, which **disables the
submit button and relabels it** «Изпраща се…» / «Подготвяме снимките…».

A validator that called only `preventDefault()` would have left the button **locked disabled on a
form that was not being sent** — a dead page, strictly worse than the clutter this set out to fix.
So `form-validate.js` is emitted **before** `photo-resize.js` (deferred scripts run in document
order, so listener order follows markup order) and calls `stopImmediatePropagation()` as well.
The two are one mechanism, not belt-and-braces.

Related and deliberately left alone: `photo-resize.js:114` calls `form.submit()`, which does not
fire the submit event. Correct — validation already ran on the user-initiated submit that started
the resize, so a check there would be unreachable code.

## Verified in a real browser, against the local tree

Served through `php -S` with a router (this host executes `.html` as PHP; the built-in server does
not), driven with `scripts/render-check.sh`:

| Check | Result |
|---|---|
| Error nodes visible on first load | **0 of 7** (was 7 of 7) |
| Help lines remaining | 2 — photos + the required-fields legend (was 6 + 7 errors + legend) |
| Placeholders | `device`, `fault` only; `name`/`phone`/`email` carry none |
| `aria-describedby` | removed help ids gone, every error id kept |
| Empty submit | 6 required fields flagged, `photos` correctly **not** flagged, focus on `device` |
| Button after rejected submit | **still enabled**, label unchanged — `stopImmediatePropagation` holds |
| Fill one flagged field | only that message clears; the other five stay |
| Valid submit | reached the window with `defaultPrevented: false` — the validator does not cancel it |
| Scripting off | all 7 errors `display: none`, form still posts |
| Console errors | none |

A real POST was never made. The valid-submit test cancelled navigation at window level **after**
reading the verdict, because a genuine submission sends Telegram and email to the shop owner.

Selftests: settings 27/27, notify 9/9, spam-guard 27/27, upload 18/18. `php -l` clean.

## Not done here

`contact-send.php` is untouched — server-side validation remains the source of truth and its error
re-render drives the same markup through the same partial.

## Deployed and re-verified live, 2026-09-22

Owner ran `deploy-new.sh css/base.css includes/contact-form.php js/form-validate.js`.
Re-measured against `https://torin.bg/new/kontakti.html`:

| Check | Live result |
|---|---|
| On load | 0 errors visible, 0 `aria-invalid`, 2 help lines, placeholders on `device`/`fault` only |
| Empty submit | 6 required fields flagged, focus on `device`, **button still enabled and labelled «Изпратете запитване»** |
| Fix one field | `device-err` cleared, the other 5 untouched |
| Valid submit | 0 errors, and the button flipped to disabled/«Изпраща се…» — that is `photo-resize.js` behaving normally, which is positive evidence the validator **allowed** the submit rather than cancelling it |
| Console errors | none |

**No enquiry was sent.** The live test installed a capture-phase `submit` listener on `window`
before anything else, so no path could reach `contact-send.php`. `location.pathname` stayed
`/new/kontakti.html`, confirming it held. (The local run could afford to read the verdict in the
bubble phase; on a host that pages a human, the cancel goes first.)

Full sweep after the change: **20/20 pages PASS**, 34 pass / 0 fail / 4 skip.

`kontakti.html` fell 166 → 95 Cyrillic tokens. The removed strings account for exactly that:
8 (device hint) + 9 (fault hint) + 8 (photos trim) + 5 (phone) + 9 (email) + 32 (seven error
messages) = **71**. Every other page's token count is unchanged, which is the evidence that the
global `[hidden]` rule affected nothing beyond this form.
