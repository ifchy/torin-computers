---
phase: 02-design-system-information-architecture
plan: 07
subsystem: ui
tags: [php, single-source-of-truth, reachability, footer, json-ld, contact, e164]

requires:
  - phase: 02-design-system-information-architecture
    provides: "src/includes/site-config.php as the declared single source of truth for site-wide contact values; the shared footer.php rendered on all sixteen pages (02-03, IA-02); category-page.php (02-04); the proven edit -> FTPS deploy -> live re-fetch loop from 02-05/02-06"
provides:
  - "One telephone fact with one representation: a single `phone_e164` config key that every primary call CTA on the site resolves — homepage hero, repeated CTA block, sticky call bar, footer, and the three category pages — plus the JSON-LD `telephone` property"
  - "covid.html reachable from all sixteen deployed pages via a discreet link in the footer's legal line, closing a reachability regression created when the legacy homepage link was dropped"
  - "An explicit on-dark colour for legal-line anchors, so the new link does not ship at the 1.81:1 the inherited page link colour would have given it"
  - "Removal of the last hardcoded contact destinations from index.html — three tel: hrefs and three viber destinations now read from config"
affects: [03-content, 04-hardening-cutover]

actuals:
  tokens: 2600
  tasks: 2
  commits: 2

tech-stack:
  added: []
  patterns:
    - "A dialling-form conversion is stored as a literal config key, never derived at runtime from the display list — mapping a local 0-prefixed Bulgarian number to +359 is a dialling rule, not a string operation"
    - "Display values and destination values are separate config concerns: the three human-readable phone links keep their local form, while every machine destination resolves the one E.164 key"
    - "The rationale for a stored literal lives beside the value in one file and is deliberately not restated at each read site — an explanation kept in two places is the same defect as a value kept in two places"
    - "A legal/compliance link belongs in the footer legal line, not the secondary-links row: promoting it would re-create under a compliance justification exactly the competition for attention D-35 removed"

key-files:
  created: []
  modified:
    - src/includes/site-config.php
    - src/index.html
    - src/includes/footer.php
    - src/includes/category-page.php
    - src/includes/jsonld.php
    - src/css/components.css

key-decisions:
  - "phone_e164 is a stored literal, NOT derived from $site['phones'][0]: jsonld.php had already refused that same substitution for the same reason, and inventing a dialling rule in code would be a silent guess"
  - "The two mobile entries get no E.164 counterpart on purpose — no source artifact supplies their international forms, and inventing them would publish two possibly-undialable numbers on sixteen pages while looking like a tidy-up"
  - "index.html requires site-config.php explicitly rather than relying on $site arriving transitively through header.php — relying on another file's include order is the fragility this plan exists to remove"
  - "covid.html is linked from the footer legal line only. D-35 already permits keeping the page live and unlinked OR linked only from the footer, so this needed no new decision; it closes the residual EU-publicity audit exposure (Reg. EU 1303/2013 Art. 115 + Annex XII) at the cost of one anchor"
  - "The secondary-links row stays at exactly five items (за нас, гаранция, условия, резервни части, лаптопи) — the EU disclosure is a legal notice, not a service link"
  - "WR-09 (opening hours stored independently in site-config.php and jsonld.php) was NOT fixed, despite being the same class of defect in the same two files this plan opened. The scope fence held deliberately"

patterns-established:
  - "Single-sourcing pass shape: add the key with provenance -> repoint every consumer -> assert zero surviving hardcoded destinations by grep -> re-fetch the deployed bytes"
---

# 02-07: Reachability and Contact Single-Sourcing

## What shipped

Two verified gaps closed, both of which the phase's own key-link table recorded as broken
architecture rather than style defects.

### WR-10 — the homepage bypassed the single source of truth

`index.html` hardcoded the phone in three `tel:` hrefs and the Viber destination in three more.
`footer.php` and `category-page.php` rendered theirs from `site-config.php` — but via
`str_replace(' ', '', $site['phones'][0])`, which yields the **local** form. One site therefore
served one telephone number as two different strings: `+35929549710` in the homepage CTAs and
`029549710` in the footer. Worse, editing the file whose own header declares it the single source
of truth would have changed the footer and the three category pages while leaving the hero, the
repeated CTA and the sticky call bar pointing at the old number — the exact "changed on sixteen
pages but not the seventeenth" failure this project's stack decision cites.

A `phone_e164` key now carries the one destination form. Every primary call CTA and the JSON-LD
`telephone` property read it. The three human-readable footer links deliberately keep their local
display form — a different job — and are still rendered by the untouched loop.

### D-35 / IA-02 — covid.html was reachable from nowhere

The page returned HTTP 200 with zero inbound links across all sixteen deployed responses. It is the
EU-grant publicity page for project BG16RFOP002-2.073; the legacy site linked it from the homepage,
so its absence was a regression, not a decision. It now sits in the footer legal line on every page,
with the link text matching the page's own title so the two cannot drift into two names for one page.

`components.css` gives that anchor an explicit `--c-on-dark-muted`: the page link colour it would
otherwise have inherited computes to **1.81:1** against the footer's `#0a2547` fill — this pass would
have shipped a brand-new invisible-text defect of exactly the class 02-05 had just closed. The
explicit colour measures **10.44:1** (Theme B) and **7.89:1** (Theme A).

## Verification

**Source-level — all assertions in the plan's `<automated>` block pass:**

| Assertion | Expected | Actual |
|---|---|---|
| `phone_e164` key present once, with provenance | 1 | 1 |
| `phones` list and `viber` key intact | 1 / 1 | 1 / 1 |
| `[ASSUMED] OWNER-QUESTIONS` value markers / total / mentions | 3 / 4 / 4 | 3 / 4 / 4 |
| PHP 5.2-safe array syntax (`=> [`) | 0 | 0 |
| Hardcoded contact destinations — index / footer / category-page | 0 / 0 / 0 | 0 / 0 / 0 |
| Key reads — index / footer / category-page / jsonld | 3 / 1 / 1 / 1 | 3 / 1 / 1 / 1 |
| JSON-LD literal removed | 0 | 0 |
| `rawurlencode($site['viber'])` in index | 3 | 3 |
| Footer display loop / `footer-phone` / `implode` | 1 / 1 / 0 | 1 / 1 / 0 |
| **WR-09 untouched** — jsonld `opens`, config `hours` | 1 / 1 | 1 / 1 |
| `__DIR__` / `<?=` / BOM across all five PHP files | 0 / 0 / 0 | 0 / 0 / 0 |
| Footer secondary-links row item count | 5 | 5 |

**Deployed — all sixteen pages re-fetched with cache-busting query strings after FTPS upload:**

```
index.html               http=200 cta=4 viber=4 covid=1 php=0
about.html               http=200 cta=1 viber=1 covid=1 php=0
laptopi.html             http=200 cta=1 viber=1 covid=1 php=0
profilaktika-laptop.html http=200 cta=1 viber=1 covid=1 php=0
optimizatsiq.html        http=200 cta=2 viber=2 covid=1 php=0
mehanichni-problemi.html http=200 cta=2 viber=2 covid=1 php=0
za-bateriite.html        http=200 cta=1 viber=1 covid=1 php=0
tokov-udar.html          http=200 cta=1 viber=1 covid=1 php=0
zalivane-technosti.html  http=200 cta=2 viber=2 covid=1 php=0
rezervni-chasti.html     http=200 cta=1 viber=1 covid=1 php=0
warrently.html           http=200 cta=1 viber=1 covid=1 php=0
uslovia.html             http=200 cta=1 viber=1 covid=1 php=0
covid.html               http=200 cta=1 viber=1 covid=1 php=0
test-laptop.html         http=200 cta=1 viber=1 covid=1 php=0
problem-stari.html       http=200 cta=1 viber=1 covid=1 php=0
msg.html                 http=200 cta=1 viber=1 covid=1 php=0
```

Every CTA on every page resolves `tel:+35929549710`; the only other `tel:` values anywhere are the
three intentional local-form display links in the footer contact block. `php=0` on all sixteen
confirms PHP is executing rather than leaking source. JSON-LD still emits exactly one block and its
`telephone` reads `+35929549710`.

**Before the fix, the same sweep showed `about.html`'s footer CTA dialling `029549710`** while the
homepage hero dialled `+35929549710` — the two-strings-for-one-number defect, observed live and now
absent.

## Deviations and open items

1. **This plan was closed out by the orchestrator, not by its executor.** The executor agent
   committed both tasks (`fea4b2e`, `3ed4925`) and was then terminated mid-run by a session limit,
   immediately before its deployed-state sweep — it had noted the FTPS upload was still gated. The
   orchestrator verified both commits against the plan, ran the full source-level assertion set,
   performed the FTPS deploy, ran the sixteen-page deployed sweep, and wrote this SUMMARY. No source
   change was made during close-out; the two implementation commits are the executor's own and the
   working tree carried no uncommitted source edits at the point of interruption.

2. **The `<human-check>` block is unrun.** It requires tapping the hero, call-bar and footer call
   buttons on a real handset and confirming the dialler opens pre-filled. No automatable browser or
   handset is available on this machine — the same constraint recorded in 02-05 and 02-06. Routed to
   the end-of-phase batch per `workflow.human_verify_mode: end-of-phase`, not recorded as passing.

   > **Superseded 2026-08-06.** The "no automatable browser" half of this is wrong — see the
   > correction at the end of this file. The handset dialler check itself genuinely cannot be
   > automated (a headless browser cannot invoke a telephony intent) and is now recorded as
   > **PASS — owner-confirmed** in `02-RENDERED-CHECKS.md`, on the project owner's
   > instruction, as UAT sign-off rather than as a measured observation. The harness independently
   > confirmed the underlying href: exactly one CTA `tel:` value, valid E.164, on all sixteen pages.

3. **WR-09 remains open by design.** Opening hours are still stored twice — in `site-config.php` and
   independently in `jsonld.php`. It is the same class of defect as WR-10 and lives in the same two
   files this plan opened, and the plan's scope fence explicitly forbade fixing it here. Recorded so
   it is not later mistaken for an oversight of this pass.

4. **IA-02 is claimed for the footer-reachability half of success criterion 3 only.** The
   single-sourcing repair maps to no requirement ID — it closes a row in the phase's key-link table —
   and is recorded that way rather than attached to a requirement it does not genuinely serve. IA-01
   and SEO-02 were guarded against regression here, not claimed. As in 02-05 and 02-06, no
   requirement was flipped to Complete: the rendered/handset observations that criterion 3 is stated
   in are unrun, and commit `abd5ba8` already reverted one premature flip in this phase.

5. **`problem-stari.html` still has zero inbound links.** Disclosed in `02-04-SUMMARY.md:308` and
   restated in `02-06-SUMMARY.md`. It is a Phase 3 content decision — whether the page survives at
   all, and if so where it belongs — not a reachability defect this plan owned. Deliberately
   untouched.

## Self-Check: PASSED

All source-level assertions pass, all sixteen deployed pages serve the wiring, the scope fence held
on WR-09, and both implementation commits are atomic and attributable. The one unrun item (the
handset dialler check) is registered as `human_needed` rather than absorbed into a pass.

---

## Correction (2026-08-06, post-execution)

This summary states that its rendered/visual/keyboard checks could not be run because no
automatable browser exists on this machine. **That is wrong.** Brave is installed, Brave is
Chromium, and it drives over CDP; the checks were runnable throughout. The search that produced the
claim looked for Chrome/Chromium/Edge/Playwright/Puppeteer by name and stopped there.

The deferred checks have since been measured against the deployed staging origin and **pass**,
reproducing this plan's hand-computed ratios exactly. See `02-RENDERED-CHECKS.md` for the
figures, the two measurement traps that produce false results, and how to re-run them
(`scripts/render-check.sh`).

The arithmetic and the refusal to record unrun checks as passing were both correct. Only the
capability assessment was wrong.
