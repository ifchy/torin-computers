---
quick_id: 260922-dx2
slug: cutover-sitemap-regeneration-step
date: 2026-09-22
status: complete
commits:
  - bfb8229 fix(sitemap) — not every src/*.html is a page; exclude the GSC token
  - 9379900 docs(04-09) — sequence the sitemap regeneration into the cutover (#51)
---

# Cutover checklist: sequence the sitemap regeneration (ledger #51)

Ledger #51 closed. `open_count` 36 → 35.

## What was wrong

Section 6.1 said `scripts/sitemap-check.sh --live, and submit sitemap.xml` with no step
anywhere telling the operator to regenerate it first. Un-regenerated, the file lists 19
`https://torin.bg/new/` staging URLs — submitting those is the opposite of the URL continuity
the cutover exists to preserve.

## What shipped

**Step 2.0**, before the rename, covering the three files that still encode `/new/`:
`site-config.php` `base_url` → `https://torin.bg/`, regenerate via `gen-sitemap.sh`, hand-repoint
the `robots.txt` `Sitemap:` directive, run `sitemap-check.sh` offline, then deploy exactly those
three by explicit path.

Numbered **2.0 rather than a new 2.1 so nothing renumbers** — Section 6.4 cites "the Section 2.1
panel step" and Section 7 cites step 2.4, both by number. Section 6.1's submit line is now gated
on 2.0 and names which check fails in which half-done state.

Two orderings made explicit: it goes **before** the rename (the swap re-uploads nothing, so a
later fix leaves a crawlable window where the root publishes `/new/` URLs under `Allow: /`); and
the deploy must **name its three paths** (a bare no-argument `deploy-new.sh` uploads everything
under `src/`, `.htaccess` included).

## The defect found while doing it

`sitemap-check.sh` **exited 1 against the tree**, and `gen-sitemap.sh --stdout` showed why:
regenerating today emitted **20** `<loc>`s, the twentieth being the Search Console verification
token `google1718743335455f1c.html`.

Both scripts derived "indexable page" as every `src/*.html` minus `$torin_robots ... noindex`.
The token is 53 bytes of plain text with no PHP and no such line, so neither could see it. It
entered `src/` in 04-09, *after* 04-08 generated the committed sitemap — so the committed file
was right and a regeneration would not have been.

This was a precondition, not a tangent: without it, the step above would have had the operator
publish their verification token as a page.

Fix: in both scripts, a file is a page only if it includes `includes/header.php` — derived, not
listed, consistent with `gen-sitemap.sh`'s refusal to carry hard-coded slugs.

Verified rather than reasoned about:

- regenerated output is **byte-identical** to the committed `src/sitemap.xml` (19 locs, no google
  entry) — the fix restores intent rather than changing output
- `sitemap-check.sh` PASSes, exit 0
- and **can still fail**, proved three ways against `--sitemap` fixtures: a dropped page →
  MISSING; the token listed → **EXTRA** (rejected, not merely ignored); a root-form sitemap
  against a `/new/` config → D/E drift caught loudly

## Reported, deliberately not fixed here

**`src/.htaccess` is not actually excluded from `scripts/deploy-new.sh`.** Ledger 52 asserts it
"is OUT OF SCOPE for deploy-new.sh", but that scope exists only as a comment in `.htaccess` and
a note in checklist step 2.4 — no code enforces it. `deploy-new.sh:161` builds its no-argument
file list from `find . -type f ! -name '.DS_Store'`, which includes dotfiles. A bare
`scripts/deploy-new.sh` run therefore uploads the now-root-form `.htaccess` into
`public_html/new/`, pointing `RewriteBase` at `/` from a file living in `/new/` — the exact
D4-30 catastrophe, which this project has already hit once in production.

Step 2.0 routes around it by mandating explicit paths, but the landmine is still armed for every
other deploy. The durable fix is a refusal in `deploy-new.sh` for `.htaccess` unless named
explicitly. Left to the user's call, since it changes deploy behaviour.
