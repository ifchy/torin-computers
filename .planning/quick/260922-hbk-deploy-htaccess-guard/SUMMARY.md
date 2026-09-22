---
quick_id: 260922-hbk
slug: deploy-htaccess-guard
date: 2026-09-22
status: complete
commits:
  - d913c07 fix(deploy) — refuse the root .htaccess in code, not in a comment
  - (checklist note in the following docs commit)
---

# Make `.htaccess` actually out of scope for `deploy-new.sh`

## What was wrong

Ledger 52 asserts `src/.htaccess` "is OUT OF SCOPE for `deploy-new.sh`". Nothing enforced it —
the scope was a comment in `src/.htaccess` and a sentence in checklist step 2.4. `find -type f`
matches dotfiles, `REMOTE_ROOT` is hardcoded to `public_html/new`, and the file is in root form
since 04-09, so a bare run uploaded a `RewriteBase /` file into `/new/`. The project already
shipped that defect once.

## What shipped

`is_root_htaccess()` plus two behaviours: **skip loudly** on a no-argument run, **hard error**
when named explicitly. Override `TORIN_DEPLOY_HTACCESS=1`, matching the
`TORIN_LIVE_DEPLOY_CONFIRM` precedent in `deploy-live.sh`.

Checklist step 2.0's warning was rewritten — it claimed a bare run uploads `.htaccess`, which is
no longer true. It now recommends explicit paths for blast radius rather than for safety.

## The guard's first version was wrong, and the test caught it

Matching on **basename** also blocked `src/vendor/phpmailer/.htaccess` — a deny block that must
ship, and whose absence is the whole of ledger 42. Suppressing it would have silently undone
04-05's work and left the vendored library fetchable.

A nested `.htaccess` is scoped to its own directory and carries no `RewriteBase`; only the root
file was promoted. The predicate matches the root path alone (`${1#./}` = `.htaccess`), so the
`./` spelling is covered too.

This is the third time in two sessions that running a check rather than reading it changed the
answer — see ledger 48's standing rule.

## Proof

All cases run against a stub credential pointing at `127.0.0.1`, so no upload could reach a real
host. `guard_fired` reads stderr for the refusal; `reached_upload` counts upload attempts.

| invocation | exit | guard fired | reached upload |
|---|---|---|---|
| `.htaccess` | 1 | yes | 0 |
| `./.htaccess` | 1 | yes | 0 |
| `TORIN_DEPLOY_HTACCESS=1 .htaccess` | 1 | no | 1 |
| `sitemap.xml robots.txt` | 1 | no | 2 |
| `vendor/phpmailer/.htaccess` | 1 | no | 1 |

No-argument run: **143 files**, root `.htaccess` skipped with a notice, `vendor/phpmailer/.htaccess`
still present. (Every exit is 1 because the stub host refuses connections — which is itself the
evidence that nothing real was uploaded.) `bash -n` parses.

## Ledger 52 deliberately left open

Its risk is now materially mitigated — the code enforces what it only asserted — but the entry
also carries the reminder that `src/.htaccess` **goes up by hand with the 04-10 root swap**.
That is still owed, so closing the entry would lose a live cutover obligation. Worth re-reading
at 04-10 rather than retiring now.
