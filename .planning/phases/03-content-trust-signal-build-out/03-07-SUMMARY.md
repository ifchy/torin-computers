---
phase: 03-content-trust-signal-build-out
plan: 07
subsystem: category-2 child pages — hinges, ports, power jack; publish gate opened
tags: [php52, seo-01, trust-03, one-way-door, content, escalation-copy, p8-carryover]
status: complete

requires:
  - 03-01 torin_render_service_page(), the $page key contract, the services.php child records
  - 03-03 the APPROVED slug set, the built-sibling-list convention, the terminology rule
  - 03-05 names this plan as owner of the jack-side share of the displaced power-circuit copy
provides:
  - src/smyana-na-panti.html, src/remont-na-portove.html, src/smyana-na-buksa.html
  - all five category-2 child records published — the D3-03 split is complete
  - the jack-to-board escalation argument, carried at the shop's own strength
  - the P-8 jack-side carryover, discharged in the power-jack page's own words
affects:
  - 03-09 (site-wide title uniqueness — all five titles and word counts are tabled below)
  - ekran-klaviatura-portove.html hub — its built child list now resolves all five to their own URLs with ZERO edits to that file

tech-stack:
  added: []
  patterns:
    - a page data array gets a PAGE-SPECIFIC name, never a generic $torin_page
    - a record is flipped to published only in the change that lands its file
    - a comment must not quote a gated heading string verbatim, or it inflates the gate's own count

key-files:
  created:
    - src/smyana-na-panti.html
    - src/remont-na-portove.html
    - src/smyana-na-buksa.html
  modified:
    - src/includes/services.php

decisions:
  - the link label on the jack page's urgent block is «Ремонт на дънни платки», deliberately not the kat-4 record name
  - related lists are BUILT from published siblings rather than the plan's fixed "two siblings"
  - the plan's `zalivane-technosti.html` source-literal gate is replaced by a resolved href plus a live gate

metrics:
  duration: ~50m
  completed: 2026-08-26

actuals:
  tokens: 68000
  tasks: 2
  commits: 2
---

# Phase 3 Plan 07: Hinges, Ports and Power Jack Summary

Shipped the remaining three category-2 child pages and opened the publish gate on all five, completing
the five-way split D3-03 was accepted for — **with every live check unrun**, blocked on the same deploy
permission gate plans 03-01, 03-02 and 03-03 all hit.

## The five children — full set for plan 03-09's uniqueness pass

| Slug | `$torin_title` | Chars | Bulgarian words | Published by |
|---|---|---|---|---|
| `smyana-na-ekran.html` | Смяна на екран на лаптоп София · Торин | 38 | 691 | 03-03 |
| `smyana-na-klaviatura.html` | Смяна на клавиатура на лаптоп София · Торин | 42 | 647 | 03-03 |
| **`smyana-na-panti.html`** | Смяна на панти на лаптоп София · Торин | 37 | **783** | **03-07** |
| **`remont-na-portove.html`** | Ремонт на USB и HDMI портове на лаптоп · Торин | 45 | **710** | **03-07** |
| **`smyana-na-buksa.html`** | Смяна на захранваща букса на лаптоп · Торин | 42 | **789** | **03-07** |

All five titles are distinct. Word counts are **Cyrillic tokens only**, measured over the page data
array with comments stripped — see deviation 1 for why the plan's own word gate cannot be trusted.

`grep -c "'published' => true" src/includes/services.php` → **5**. `'published' => false` → **0**.

## What Was Built

**Task 1 — hinges and ports** (`81052e2`)

Both pages carry no category record, read their display name from the record, take the default warranty
key and use three-level breadcrumbs — the shape plan 03-03 established on the first two children.

The port page carries the shop's own south-bridge argument from `mehanichni-problemi.html`: on many
boards the USB jack pins go straight into the south bridge, so a short at the connector can take the
chip with it. It is carried at that strength and no higher, with no probability attached.

The hinge page's prose block makes the argument the site has never made: the hinge is metal but its
mounting points are plastic, so a stiffening hinge transfers its load into the corpus and tears its own
seat out — and the display cable running through the hinge is chafed at every opening. That is why
waiting costs more of the assembly.

**Task 2 — the power jack, and the gate** (`ddb05a2`)

`smyana-na-buksa.html` carries the one earned urgent-tone block of the five. The escalation is the
shop's own: a jack soldered through every layer of the board arcs once it works loose, can put supply
voltage onto layers that should not carry it, and the result is board damage.

**The P-8 carryover from plan 03-05 is discharged in the block's second paragraph**, rewritten rather
than copied: the `Charger` circuit sits directly on the laptop's input power rails — precisely the path
that runs through the jack — and is therefore sensitive to current and voltage arriving through that
input outside normal values, which is exactly what a loose connector produces at every make-and-break.
The depth stays on the board-repair page. A mechanical check confirms **no sentence longer than 90
characters is shared verbatim** with either `zalivane-technosti.html` (31 sentences compared) or
`problem-stari.html` (37 compared).

All three remaining flags flipped **in the same commit that landed the last file**, so no record was
ever published ahead of its page.

## Terminology check — «екран» over «матрица» (stated as required)

**Checked on all three pages. «матрица» appears in none of them, and the check is recorded in each
file's head comment so a later pass does not read the absence as an oversight.**

- `smyana-na-panti.html` — names the screen once, where the failing hinge chafes the display cable. Says **«екран»**, as the rule requires for customer-facing prose. No bare-panel part reference exists here, so the sanctioned «матрица» exception does not apply.
- `remont-na-portove.html` — discusses an **external** monitor over HDMI, so it says «външен монитор» and «екран». No panel-as-a-part reference.
- `smyana-na-buksa.html` — neither term appears; nothing on the page concerns the screen at all.

The rule was checked deliberately rather than skipped, and each file says so in a comment stating it
reverses live-site usage — the note that stops a later "correction" pass from undoing the decision.

## Deviations from Plan

**1. [Inherited, confirmed on all four gate bugs] The plan's gates carried all four known defects**

- **Short-array regex.** The plan's `(=>[^;]*\]|\[\s*[^]]*=>)` matches any line *reading* an array value right of `=>` and can never pass. Substituted `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['` — clean on all four files.
- **Word count counts English comments.** The plan's `tr -cs '[:alpha:][:digit:]'` gate counts comment words. Measured Cyrillic tokens separately instead; all three pages clear the real 600-word bar with margin (783 / 710 / 789), so the substitution changed no outcome here — but it is the honest number.
- **BSD grep has no `-P`.** The plan's `grep -oP "(?<='page' *=> *')[^']*"` loop for the page-existence gate cannot run on this machine. Replaced with `grep -oE "'page' *=> *'[^']*'"` and an explicit `ls` of all five files.
- **zsh does not word-split.** The plan's `for f in $FILES` form was avoided; file lists are passed as explicit arguments.

**2. [Rule 3 — Blocking] The plan's slug `smyana-na-matrica.html` does not exist**

Plan 03-07 was written before plan 03-03's checkpoint amended the screen slug to `smyana-na-ekran.html`.
Every reference — the `read_first`, the hinge page's cross-link target, the anti-hand-typing gate — was
resolved against the **actual** record (`svc-ekran`), which is what the plan's own "Flagged planner
assumptions" section instructs. Nothing was ever indexed under the old slug, so no redirect is involved.

**3. [Rule 2 — Missing critical functionality] Page data arrays are page-specific, not `$torin_page`**

The two shipped siblings name their data array `$torin_page`, which is the exact name that caused the
2026-08-26 live defect: `header.php` assigned `$torin_page` into every including page's global scope,
and on PHP 5.2 `$string['intro']` returns character zero, so four pages served a single letter at
HTTP 200. `header.php` is fixed (`$torin_nav_current`), but this plan's three pages use
`$torin_panti_page`, `$torin_portove_page` and `$torin_buksa_page`, and each file carries a head comment
explaining why. **The two 03-03 siblings still use `$torin_page` and are safe only because header.php
was renamed** — logged below as a deferred hardening item, not touched here (out of this plan's files).

**4. [Rule 1 — Bug] The plan's `zalivane-technosti.html` source-literal gate contradicts the project's own convention**

The plan gates on `grep -c 'zalivane-technosti.html'` in the port and jack page **source**. Satisfying it
literally would mean hand-typing a filename, which is exactly what the D-23 gate and threat T-03-34
exist to prevent — and would 404 if kat-4 were ever gated. Both pages resolve the href through
`torin_category_href($torin_cat4)` instead. Gate replaced with: (a) the source assertion that the block
link resolves through the accessor, verified; (b) the **live** assertion that the served HTML contains
`zalivane-technosti.html` — listed below as NOT RUN. The prohibition gate on child filenames returns
clean across the hub and all five children.

**5. [Rule 2 — Bug the gate would have caught] A comment quoting the gated heading inflated its own count**

`grep -c 'Защо не отлагате смяната на буксата'` returned **2**: the real heading, plus a head comment
quoting it while explaining why the urgent tone is earned. Comments are stripped at deploy so the served
page was always correct, but the gate as written would have failed. Trimmed the comment to «Защо не
отлагате…» and noted inside it why the full string is not repeated. Now returns 1. Worth carrying
forward as a convention: **a comment must not quote a gated string verbatim.**

**6. [Deviation from plan text, following 03-03's convention] `related` lists are built, not fixed**

The plan specifies "across to two siblings". All three pages instead **filter published siblings from the
record set**, the convention 03-03 established precisely so that this plan's publishing would surface the
new children on the existing pages with zero edits to them. That is now realised: `smyana-na-ekran.html`
and `smyana-na-klaviatura.html` gain three sibling links each without being touched. Every href resolves
through `torin_service_href()`; no filename is typed.

**7. [Judgement] The jack page's block link label is not the kat-4 record name**

The plan mandates the label `Ремонт на дънни платки`; the record name is «Заливане и ремонт на дънни
платки». Followed the plan. In context the visitor has just been told a connector can become a board
problem, so leading with «Заливане» would read as a non-sequitur. The **href** is still resolved from the
record, so a D-40 rename cannot strand the destination — only the contextual label is local, and a
comment says so.

## Precondition — reported, not silently passed

The plan's Task 2 precondition requires `filezilla-server-data.xml` at the repo root. **It is absent from
this worktree** (it is gitignored, so it does not propagate to linked worktrees) but **present at the
main repo root**, `/Users/alabala/Documents/projects/torin/filezilla-server-data.xml`. `deploy-new.sh`
resolves `CRED_FILE="${TORIN_CRED_FILE:-${REPO_ROOT}/filezilla-server-data.xml}"`, so the orchestrator
running from the main checkout after merge satisfies it with no override needed.

This did not become a halt because deploy is denied to executor agents by the environment classifier
regardless — the live sweep was never mine to run. It is recorded here so the orchestrator knows the
precondition is satisfied on its side, and so nobody re-derives the worktree/credential interaction.

## Live and rendered verification — NOT RUN

**Nothing in this section has been executed. No live check is claimed as passing.**

`scripts/deploy-new.sh` is denied to executor agents; it was not retried. There is **no local PHP
interpreter** on this machine (`command -v php` → nothing), so deploying and fetching the URL is the only
real syntax and runtime check available, and it has not happened. The 2026-08-26 `$torin_page` defect is
the standing proof that every local gate can pass on a genuinely broken page.

As a **partial** proxy only: paren/brace/bracket balance outside string literals and comments is `0/0/0`
with no unterminated string on all four touched files, and single-quote counts are even on all four.
This catches an unclosed `array()`; it does not prove the files parse.

### Deploy command

No page in this plan uses an evidence strip, so **no photo files are involved** — `torin_render_evidence()`
is never called and its silent-drop-on-missing-file behaviour cannot bite here.

```
scripts/deploy-new.sh includes/services.php \
  smyana-na-panti.html remont-na-portove.html smyana-na-buksa.html
```

Include first, then pages (P-10 ordering, so a parse error isolates to one URL).

### Checks to run afterwards

| Check | Expected |
|---|---|
| `curl -sf -o /dev/null -w '%{http_code}'` on all **five** child URLs | 200 each |
| zero `<?php` in any served body; zero parse/fatal/warning strings | 0 |
| each child: `grep -c '<h1'` | 1 |
| each child: `class="breadcrumbs"` / `BreadcrumbList` | 1 / 1 |
| each child: `svc__warranty__term` / `brand-row__item` | 1 / 8 |
| each child: `grep -c '<h2'` | ≥ 4 (the P-5 assertion — an empty main returns 0) |
| each child: empty headings `<h[23]></h[23]>` | 0 |
| each child: link back to `ekran-klaviatura-portove.html` | ≥ 1 |
| hub served HTML references all five child filenames | 1 each |
| `smyana-na-buksa.html`: `svc__block--urgent` | exactly 1 |
| `smyana-na-buksa.html` **and** `remont-na-portove.html`: `zalivane-technosti.html` | ≥ 1 each (deviation 4 — this is where that gate moved) |
| `smyana-na-panti.html`: `smyana-na-ekran.html` in served HTML | ≥ 1 (resolved href arrives at the right place) |
| `smyana-na-ekran.html` / `smyana-na-klaviatura.html` still 200, now showing 3 extra sibling links each | 200 |
| `scripts/render-check.sh scripts/probes/svc-page.js <child URL> 360 640` | PASS, `inconclusive: []` |

**Run the probe WITHOUT `SVC_EXPECT_URGENT=1` on the hinge and port pages** — neither is contracted to
carry a first-aid callout. `smyana-na-buksa.html` does carry an urgent block, but it is an escalation
callout rather than the liquid-damage first-aid block that flag gates, so the flag stays off there too;
the urgent block is covered by the `svc__block--urgent` count above instead.

## Known Stubs

None. The `[ASSUMED]` symptom lines on all three pages are **flagged placeholders pending
OWNER-QUESTIONS #16**, not stubs: each renders real customer-facing prose and each is marked in-file as
unconfirmed shop language. They match the convention on all published siblings.

## Deferred / out of scope

- **The two 03-03 sibling pages still name their data array `$torin_page`.** Safe today only because `header.php` was renamed to `$torin_nav_current`. Renaming them to `$torin_ekran_page` / `$torin_klaviatura_page` would remove the standing hazard, but those files are outside this plan's `files_modified` and a sibling agent is active this wave. One-line change per file for a later pass.
- **`src/includes/categories.php` has an odd single-quote count at HEAD, pre-existing** (inherited note from 03-03). Untouched here — plan 03-08's agent owns that file this wave.

## Threat Flags

None. No new network surface, no secret, no third-party artefact, no package installed.

| Threat | Status |
|---|---|
| T-03-33 (publish gate opened ahead of a file) | mitigated — all three flags flipped in the commit that landed the last file; all five `page` values verified against disk; live 200 sweep NOT RUN |
| T-03-34 (hand-typed child hrefs) | mitigated — gate returns clean across the hub and all five children |
| T-03-35 (escalation copy overstated) | mitigated — argument carried at the live copy's own strength; urgent tone used once, heading states the urgency in words |
| T-03-36 (implied commitments) | mitigated — price/turnaround gate clean on all three pages |
| T-03-37 (legacy markup) | mitigated — strings ported, not markup; gate clean |
| T-03-SC (package installs) | n/a — nothing installed |

## Self-Check: PASSED

- `src/smyana-na-panti.html` — FOUND
- `src/remont-na-portove.html` — FOUND
- `src/smyana-na-buksa.html` — FOUND
- `src/includes/services.php` — FOUND, 5 published / 0 unpublished
- `81052e2`, `ddb05a2` — both FOUND in git log
- Files changed vs base: exactly the four declared in `files_modified`; `categories.php` untouched
- No deletions in either commit
- Live checks — correctly recorded as NOT RUN, not as passing
