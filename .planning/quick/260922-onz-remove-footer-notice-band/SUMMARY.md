---
quick_id: 260922-onz
slug: remove-footer-notice-band
date: 2026-09-22
status: complete
commits:
  - 2eb5f08 feat(footer) — remove the notice band entirely, owner decision
---

# Remove the footer notice band

Ledger 33 closed. `open_count` 31 → 30.

## What it was, and where it came from

The band rendered «Работно време: понеделник – петък, 8:00 – 16:00 ч.» at the top of the footer
on every page. `footer.php:68` rendered «Понеделник – Петък, 8:00 – 16:00» **34 lines below it**
— same fact, same clock icon, one page.

Its origin, which nobody in the project could state: `site-current/otpuska.js`, live value
`В А Ж Н О !!! НОВО Работно време: понеделник до петък 8:00 до 16:00 часа`, with a commented-out
alternative `Офисът няма да работи на 04.07.2022`. One script, two jobs — temporary closure
notices, and a temporary announcement that the hours **had changed**.

Phase 4 split them. The closure half became `banner.php`, owner-controlled and auto-expiring —
that is what OWNER-QUESTIONS #8 approved. The hours-announcement half became this band. But that
half was inherently temporary: it announced a *change*. Made permanent and stripped of its
«НОВО», it could only ever be a duplicate.

## Why ledger 33 outlived its own question

Entry 33 held the `[ASSUMED]` marker open "until #8 itself is answered". **#8 was answered on
2026-09-11** — "keep, rebuilt as owner-controlled", which 04-06 delivered as `banner.php` +
`settings.txt`. It asked about the holiday banner and never covered this band. The entry's
characterisation of #8 as "asks whether the footer band should exist at all" did not match what
#8 asks, and that mis-citation is the whole reason the marker survived.

## Removed

The `'notice'` key, its composition, the `[ASSUMED]` comment block, the footer render block, and
the now-dead `.notice--info` CSS rule.

**Kept deliberately:** the base `.notice` grid — shared by `.notice--error`, which
`contact-send.php` renders live, and copied verbatim by the closure strip. And `$torin_days`,
which `$site['hours']` still uses.

## Test change

`settings-selftest`'s "the three rendered hours consumers all read one composed value" is
**narrowed to two rather than deleted** — the one-source property (D4-26) is what stops this site
publishing disagreeing copies of its hours, and two consumers still enforce it. A second
assertion now fails if a `'notice'` key ever returns, since nothing else in the suite watches the
footer's composition.

## Verified by rendering, not by grepping

All **21 pages** render with zero PHP warnings, zero `.notice--info` occurrences, the `<footer>`
element intact and the single hours line still present.

All four selftests green: settings **27/27**, upload 18/18, spam-guard 27/27, notify 9/9.

## Note for the owner conversation

This removes one item from that list. Still open and genuinely unanswered: OWNER-QUESTIONS **#16**
(what customers actually say when they call) and **#27** (customer data on devices left for
repair), plus **#32/#33** in that file (category-6 diagnostics pricing), plus the 04-04 Task 3
sign-off on published legal text, plus ledger 31's advance-notice question which has never been
put to him.
