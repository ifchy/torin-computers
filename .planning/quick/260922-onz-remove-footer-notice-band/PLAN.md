---
quick_id: 260922-onz
slug: remove-footer-notice-band
date: 2026-09-22
description: Remove the footer notice band entirely — owner decision; it is a duplicate of the hours line 34 lines below it
---

# Remove the footer notice band

## The decision, and the evidence behind it

**Owner decision, 2026-09-22: the band goes away entirely.** It is not the holiday banner, and
nothing in the project ever established that it should exist.

What it renders, at the top of the footer on all 19 pages:

> Работно време: понеделник – петък, 8:00 – 16:00 ч.

What `footer.php:68` renders 34 lines lower in the **same footer**:

> Понеделник – Петък, 8:00 – 16:00

Same fact, same clock icon, one page.

**Origin.** `site-current/otpuska.js`, whose live value was
`В А Ж Н О !!! НОВО Работно време: понеделник до петък 8:00 до 16:00 часа`, with a commented-out
alternative `Офисът няма да работи на 04.07.2022`. That one script did two jobs: temporary
closure notices, and a temporary "**НОВО** — the hours have changed" announcement.

Phase 4 split them. The closure half became `banner.php` — owner-controlled and auto-expiring,
which is what OWNER-QUESTIONS #8 actually answered. The hours-announcement half became this
band. But that half was **inherently temporary**: it announced a change. Carried forward
permanently, and with the "НОВО" dropped, it became a duplicate of the hours line.

Ledger 33 held it open pending an answer to #8 — but #8 asked about the holiday banner, and was
answered on 2026-09-11. It never covered this band. That mis-citation is why the marker
outlived its question.

## Tasks

1. `src/includes/site-config.php` — remove the `'notice' => ''` key, the `$site['notice'] = ...`
   composition at line 441, and the `[ASSUMED]` comment block describing it. `$torin_days` stays:
   `$site['hours']` uses it too.
2. `src/includes/footer.php` — remove the `if ($site['notice'] !== '')` block and its `<p
   class="notice notice--info">`, plus the comment block explaining the band.
3. `scripts/settings-selftest.php` — its final assertion reads "the three rendered hours
   consumers all read one composed value" and tests `$site['notice']`. With the band gone there
   are **two** consumers; update the assertion rather than deleting it, so the one-source
   property is still enforced.

## Verify

- No reference to `$site['notice']` or `'notice'` survives in `src/`.
- `php -l` clean on both changed PHP files.
- All four selftests green; settings-selftest still asserts the one-source property.
- `.notice--info` CSS: check whether any other element uses that class before removing it.

## Out of scope

`banner.php` and the holiday/closure strip are untouched — that is the mechanism OWNER-QUESTIONS
#8 approved and 04-06 delivered. This removes only the hours band.
