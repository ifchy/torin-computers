---
phase: 03-content-trust-signal-build-out
plan: 06
subsystem: legal and utility page port
tags: [php52, legal, compliance, gdpr, eu-publicity, seo-01, trust-03, assets, owner-questions]
status: complete

requires:
  - Phase 2 header.php / footer.php / site-config.php (the non-category page frame)
  - 03-01 $site['warranty'] keyed set — this page is what its href points at
  - 03-02 components.css .svc__block / .svc__block--prose prose rhythm
provides:
  - src/uslovia.html — privacy declaration scoped to this site alone
  - src/warrently.html — the FULL warranty terms the shared summary is drawn from
  - src/covid.html — EU funding disclosure on ASCII asset paths
  - src/msg.html — form-submission confirmation
  - src/laptopi.html, src/rezervni-chasti.html — two honest, call-to-confirm sales pages
  - src/img/eu/ — three programme logos renamed to ASCII on port
affects:
  - the warranty summary on every service page (its href now resolves to real terms)
  - footer.php's disclosure link (its target is no longer a stub)
  - plan 03-09's tree-wide stub and SEO-01 assertions (six of sixteen pages cleared here)

tech-stack:
  added: []
  patterns:
    - a non-category page owns <main> and builds two sections — plain h1+intro, then ONE tinted section holding every prose block
    - ported prose lives in a PHP array and is escaped once at the point of output; the legacy markup is left behind entirely
    - a compliance field is rendered with the spelling it was published under, and the discrepancy is cited by owner-question number in a source comment rather than corrected
    - an asset renamed for path safety keeps byte-identical payload — a compliance logo is not re-encoded

key-files:
  created:
    - src/img/eu/eu-erdf.png
    - src/img/eu/opic.png
    - src/img/eu/logos.png
  modified:
    - src/uslovia.html
    - src/warrently.html
    - src/covid.html
    - src/msg.html
    - src/laptopi.html
    - src/rezervni-chasti.html

decisions:
  - the privacy declaration was narrowed, not deleted — the dead domain and its conjunction removed, every other clause untouched
  - the 5-6 h/day warranty condition is reproduced in full on warrently.html in the shop's published wording; the D3-10 reframing stays confined to the shared per-page summary, and a source comment forbids harmonising the two
  - the «Венера-АКС ООД» results paragraph is ported byte-faithful with no bracketed editorial note; OWNER-QUESTIONS #28 owns the decision
  - sips was NOT applied to the three logos — it inflated them by 16.5 KB and re-encoded compliance assets that carry ~50 bytes of metadata between them
  - covid.html deliberately does NOT read the shared address config: the beneficiary address of a 2020 contract is a historical record field, not the current contact address
  - neither sales page is padded to the 600-word service-page bar — the thinness is the finding, not a gap to fill

metrics:
  duration: ~2h across two sessions
  completed: 2026-08-23

actuals:
  tokens: 7900
  tasks: 3
  commits: 4
---

# Phase 3 Plan 06: The Legal and Utility Page Port Summary

Six of the sixteen locked URLs stopped being stubs — the privacy declaration narrowed to a site that
still exists, the full warranty terms landed on the page every service page's summary points at, the
EU funding disclosure moved onto ASCII asset paths with its published wording untouched, and the two
sales pages now say something true about a business line nobody has confirmed is running.

## What Was Built

**Task 1 — the privacy declaration and the full warranty terms** (`f79854a`)

`src/uslovia.html` carries the declaration from `site-current/uslovia.html:109` with exactly one
substantive edit: the sentence that extended consent-and-processing commitments to a **second domain
that no longer exists** is narrowed to this site alone. The second domain and its conjunction are
removed; no other clause is reworded, and the declaration is not deleted. The removed string is
deliberately not spelled anywhere in the file — the do-not-import rule is enforced by a grep over
`src/`, and a comment naming the domain would trip its own gate. The retention commitment (data
deleted once the enquiry is satisfied) is kept verbatim, with a source comment binding Phase 4's
contact-form work to it.

`src/warrently.html` carries the terms from `site-current/warrently.html:105-135` — 1,620 words
against the 400-word floor. Every term survives: the one-month period for all services, free warranty
service at the office with transport at the customer's cost, the >90% success rate, the micro-crack
rationale, the cooling-system correction, what voids the warranty, the re-repair and refund rules,
and a fresh one-month period after a successful re-repair.

**The 5-6 hours a day requirement is reproduced in full, in the shop's own framing, alongside its
stated reason.** A source comment records the split explicitly — the shared summary in
`site-config.php` carries the D3-10 reframing, this page carries the original — and instructs a later
editor not to "harmonise" the two by weakening this page or hardening the summary. OWNER-QUESTIONS
#23 owns whether the clause is a real condition; until it is answered neither text invents a term.

**Task 2 — the funding disclosure, its assets, and the confirmation page** (`0fef579`, `1569722`)

Three programme logos ported into a new `src/img/eu/`, renamed to ASCII:

| Source filename | New filename | Intrinsic px | Bytes |
|---|---|---|---|
| `ЕС+ЕФРР+496х379.png` | `eu-erdf.png` | 348 × 266 | 26,539 |
| `ОПИК+496х379.png` | `opic.png` | 298 × 228 | 27,837 |
| `logos-500x153.png` | `logos.png` | 500 × 153 | 46,740 |

**The source filenames lie about their own dimensions** — both Cyrillic names claim 496×379 and
neither is. The `width`/`height` attributes in the page are the true intrinsic pixels read from the
IHDR chunks, not the filename claims. All three were verified as **real PNGs by magic bytes**
(`89504e470d0a1a0a`), not by extension — the check plan 03-02 learned to run after finding a GIF
wearing a `.jpg` extension elsewhere in the tree.

`src/covid.html` carries the contract number, the programme, the priority axis, the procedure, the
goals, the indicators, the amounts, the funding split, the co-financing percentages, the beneficiary,
both addresses, the ЕИК, the contract number and the implementation dates — as published. The three
logos render as plain `<img>` with explicit dimensions and alt text naming the programme each
represents (the legacy page shipped the European Union emblem under `alt="Blog Single"`).

**The results paragraph is ported exactly as published, including «Венера-АКС ООД».** Not corrected,
not paraphrased around, no bracketed editorial note in the rendered page. A source comment cites
OWNER-QUESTIONS **#28** as the item that owns the decision, with #4 and #25 as surrounding context.

`src/msg.html` is a three-sentence confirmation: the message arrived, we reply in the shop's working
hours, calling is faster if it cannot wait, and if nothing arrives within one working day the address
or phone was probably mistyped — the one genuinely useful sentence the legacy page had, minus its
exclamation mark. Hours and phone numbers are **not** restated; `footer.php` renders them from the
shared config at the bottom of the same page.

**Task 3 — the two sales pages, ported honestly** (`31d3f7b`)

Both carry an `[ASSUMED]` source comment against OWNER-QUESTIONS #15 and state that retirement, if
the sales line turns out to be dormant, is an owner decision and a Phase 4 cutover question rather
than something to act on here. Neither states a price, a delivery time, a stock quantity or a
warranty term. `laptopi.html` frames availability as something to confirm by phone.
`rezervni-chasti.html` explains that parts are sourced per repair and that the model number alone
rarely identifies the right part, links to `test-laptop.html` for visitors who do not know what
failed, and drops the legacy page's hardcoded adapter part-number list — content that goes stale
silently and that #15 has not confirmed is still stocked.

## SEO-01 across the six pages

| Page | Title chars | Desc chars |
|---|---|---|
| `uslovia.html` | 36 | 136 |
| `warrently.html` | 37 | 138 |
| `msg.html` | 31 | 87 |
| `laptopi.html` | 28 | 134 |
| `rezervni-chasti.html` | 33 | 137 |
| `covid.html` | 42 | 131 |

Six unique titles, six unique descriptions, every title ≤ 60 and every description ≤ 155. `msg.html`
is deliberately short — it is a destination, not a content page. Neither `covid.html` nor
`uslovia.html` chases a repair keyword; both have no commercial intent.

## «матрица» → «екран»: checked, one deliberate retention

The locked terminology decision was applied to all six pages. **Five of the six contain neither
term.** The single occurrence is `src/rezervni-chasti.html:48` — «с различни матрици, различни
клавиатури и различни захранващи платки» — which is the bare LCD panel enumerated as an **orderable
part alongside other parts**, i.e. exactly the part-specification context in which the decision
retains «матрица» as the more precise term. No change made, and none should be.

## Deviations from Plan

**1. [Rule 1 — Bug] The PHP-5.2 short-array gate regex is broken (inherited, pre-flagged)**

- **Found during:** all three tasks.
- **Issue:** `grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` matches any line that *reads* an array value to
  the right of `=>`, so it fires on untouched, valid PHP 5.2 and can never pass. Independently found
  and documented by plan 03-01.
- **Fix:** substituted the gate testing the same stated intent — no `[]` literals:
  `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['`. Returns nothing on all six pages.

**2. [Rule 1 — Bug] `sips` inflated the compliance logos instead of stripping metadata**

- **Found during:** Task 2.
- **Issue:** the plan instructs running each logo through `sips` once "to strip metadata without
  resizing." Run as written it **grew the set by 16,510 bytes** (26,539→29,248; 27,837→30,500;
  46,740→57,878) because `sips` re-encodes rather than strips. A chunk inventory of the sources shows
  there was nothing to strip: `eu-erdf` and `opic` carry only `sRGB` (1 B), `gAMA` (4 B) and `pHYs`
  (9 B) ancillary chunks, and `logos.png` carries **none at all** — roughly 50 bytes of metadata
  across all three. The instruction's cost was a second encoding generation of a compliance
  document's own logos in exchange for nothing.
- **Fix:** reverted to byte-identical copies of the published assets. This is also the better posture
  independently: the logos of a funding disclosure should be the published files.
- **Files:** `src/img/eu/*.png`. **Commit:** `0fef579`.

**3. [Rule 3 — Blocking] The non-ASCII filename gate cannot run on macOS**

- **Found during:** Task 2 verification.
- **Issue:** `ls src/img/eu/ | grep -cP '[^\x00-\x7F]'` — BSD grep has no `-P`, so the gate errors out
  rather than passing or failing.
- **Fix:** substituted `ls src/img/eu/ | LC_ALL=C grep -c '[^ -~]'`, which tests the same property.
  Returns 0. The `+`-sign gate ran as written and also returns 0.

**4. [Rule 2 — Missing critical functionality] The disclosure did not state the EU support**

- **Found during:** Task 2.
- **Issue:** the publicity obligation's *actual required element* is a short description of the
  project "highlighting the EU financial support" (RESEARCH C-1). The legacy page's opening line
  named the project but never said it was EU-supported; the funding split only appears fourteen
  fields lower.
- **Fix:** the page intro now names ОПИК 2014-2020 and its co-financing by the European Union through
  the European Regional Development Fund. This is the standard programme formula and is entailed by
  the record already on the page (8 500 европейско / 1 500 национално). **No published field was
  edited to achieve it** — the addition is a framing sentence, not a change to any ported value.

**5. [Rule 3 — Blocking] The owner-question number for the company-name discrepancy was unreachable**

- **Found during:** Task 2.
- **Issue:** the plan says to cite the number plan 03-05 assigned, but 03-05 was executing
  concurrently in a sibling worktree and its SUMMARY did not exist here. `OWNER-QUESTIONS.md` at my
  base commit stopped at #27.
- **Fix:** cited #4 (which carries the same three-way choice as a note) as an interim, then corrected
  to **#28** in `1569722` once the coordinator supplied it. Both numbers now appear, correctly
  scoped.

**6. [Rule 1 — Bug, OUT OF SCOPE — handed off] The «усливията» misspelling is not on this page**

- **Found during:** Task 1.
- **Issue:** D3-13 and the UI-SPEC copy rules name `усливията` → `условията` as a fix owed by the
  port of `uslovia.html`. It is not on that page. The single occurrence in the whole legacy tree is
  **`site-current/index.html:321`**, in the link text of the consent line under the contact form
  («се съгласявате с *усливията* за поверителност»).
- **Action:** not fixed — `src/index.html` is outside this plan's declared `files_modified` and a
  sibling agent was editing it concurrently. `src/uslovia.html` carries the correct form
  («условията»); the misspelled form appears nowhere in `src/`. **Whoever owns `src/index.html`'s
  consent line must confirm the link text reads «условията».**

**7. [Typography, not substance] Inconsistent quote marks normalised on port**

`warrently.html` mixed „Торин Къмпани” and „Торин Къмпани“ inside one paragraph; `covid.html` mixed
the two closing marks in the priority-axis field. Both normalised to the Bulgarian „…“. No term, no
value and no name changed. Paragraph breaks were introduced at sentence boundaries in the source
text (the legacy pages ran the whole warranty and the whole grant record as unbroken `<br>`-joined
paragraphs); no sentence was reworded, dropped or softened.

**8. [Process] Task 3's commit was recorded as WIP by the coordinator**

A session usage limit stopped this agent with `laptopi.html` and `rezervni-chasti.html` written but
uncommitted; the coordinator committed them as an explicitly-labelled WIP so they would survive
worktree removal. On resume the Task 3 local gates were run for the first time (all pass) and the
commit was amended to `feat(03-06): port the two sales pages honestly` (`31d3f7b`). No content
changed in the amend.

## Legal and compliance items raised, not acted on

Every one of these is a deliberate stop. None is a developer decision.

| Item | Where | Owner question |
|---|---|---|
| «Венера-АКС ООД» in the results paragraph names a company that is not the beneficiary | `covid.html` | **#28** (also noted under #4) |
| The 5-6 h/day usage requirement — genuine condition or statement of confidence? | `warrently.html` | **#23** |
| Two company names, no ЕИК in the legal text — a customer cannot tell who they contract with | `uslovia.html`, `warrently.html` | **#25** |
| Is the sales line active at all? Both pages are retirement candidates if not | `laptopi.html`, `rezervni-chasti.html` | **#15** |
| Has the EU publicity obligation expired? Page kept live on the safe default | `covid.html` | **#4** |
| What the site should promise about data on devices left for repair | `uslovia.html` | **#27** |
| **NEW — needs filing as #29:** the beneficiary's administrative address is published as «София, ул. Свсиленица 3А», which does not look like a real street name. It is a field in a registered grant record, so it is ported with the spelling it was published under. Whether the typo is in the submission or in the transcription needs the contract to settle. | `covid.html` | **unfiled** |

`OWNER-QUESTIONS.md` is not in this plan's declared `files_modified`, so #29 was **not** filed by this
agent. It needs filing by whoever next owns that file.

## Known Stubs

None in the "renders an empty surface" sense — all six pages render real content and no page carries
the temporary-skeleton sentinel.

Two pages are **`[ASSUMED]`-gated by design**: `laptopi.html` and `rezervni-chasti.html` describe a
business line whose status is unconfirmed (#15). They are deliberately thin, deliberately not padded
to the D3-14 600-word bar, and deliberately state no price, stock, delivery time or warranty term.
If #15 comes back "dormant," both become 301 candidates at Phase 4 cutover. This is recorded as
intentional in both files' source comments, not as an omission.

One cosmetic redundancy, accepted: `logos.png` is a **combined strip already containing both marks**
that `eu-erdf.png` and `opic.png` carry individually, so the disclosure renders the same two emblems
twice. The plan's gate requires three `<img>` elements under `img/eu/`; all three files were ported
and all three are referenced. If a later pass wants one mark per programme, drop `logos.png` from the
page (not from the directory) and relax that gate.

## Deliberate carve-out from the "no duplicated contact facts" rule

`covid.html` carries `София, ул. Св. Иван Рилски 46` (implementation address), the administrative
address and the ЕИК. The other four prose pages carry no phone, address or hours, and `msg.html`
carries none either — the planner's gates enforce that on five pages and **deliberately omit it for
`covid.html`**. The reason is recorded in the file: these are fields in a 2020 grant record, not
current contact details. Rendering `$site['address']` here would silently rewrite a registered
historical fact the day the shop moves. This is the one place in the plan where a contact-shaped
string is authored into a page on purpose.

## Live Verification — NOT RUN

`scripts/deploy-new.sh` is denied to this agent by the environment permission classifier, as it was
for plans 03-01 and 03-02. **No live check in this plan was executed. None is recorded as passing.**

Every gate above the line ran locally and passed; every gate below it did not run at all.

| Check | Status |
|---|---|
| Stub sentinel absent from all six pages | **PASS** (local) |
| `smartbattery` absent tree-wide from `src/` | **PASS** (local) |
| `усливията` absent / `условията` present in `uslovia.html` | **PASS** (local) |
| `torin.bg` still named in the declaration; `D3-12` cited | **PASS** (local) |
| `warrently.html` states `1 месец`, 1,620 words ≥ 400 | **PASS** (local) |
| `Венера-АКС` present in `covid.html` (ported unchanged) | **PASS** (local) |
| `BG16RFOP002-2.073` present; 3 × `img/eu/` with `width=` and `alt=` | **PASS** (local) |
| `src/img/eu/` — 3 PNGs, ASCII-only names, no `+`, verified by magic bytes | **PASS** (local) |
| No page carries a brand row | **PASS** (local) |
| No inline handler, `javascript:`, or legacy Bootstrap class on any page | **PASS** (local) |
| No `__DIR__`, no `[]` array literal on any page | **PASS** (local) |
| Balanced quotes on all six pages | **PASS** (local) |
| No phone / address / hours on `uslovia`, `warrently`, `msg`, `laptopi`, `rezervni-chasti` | **PASS** (local) |
| No price / delivery / stock language on either sales page | **PASS** (local) |
| `rezervni-chasti.html` → `test-laptop.html`; both sales pages → `index.html#contact-us` | **PASS** (local) |
| Six unique titles and descriptions, all within budget | **PASS** (local) |
| — | — |
| **PHP 5.2 syntax validity of all six pages** | **NOT RUN** — no local PHP interpreter exists; deploying and fetching the URL is the only available syntax check |
| **All six URLs return 200 with one `<h1>` and zero empty headings** | **NOT RUN** |
| **All three `img/eu/` URLs return 200** | **NOT RUN** |
| **A service page's warranty block resolves to `warrently.html`, which states the same term** | **NOT RUN** |
| **The footer disclosure link still resolves from the homepage** | **NOT RUN** |
| **Rendered probe at 360×640 and 1440×900** | **NOT RUN** |

**The exact deploy command a permitted operator must run:**

```
scripts/deploy-new.sh img/eu/eu-erdf.png img/eu/opic.png img/eu/logos.png \
  uslovia.html warrently.html covid.html msg.html laptopi.html rezervni-chasti.html
```

Upload the three PNGs first, then the pages (P-10 ordering — an asset that 404s is a broken image on
one page, whereas a page with a parse error is a blank 200). After it lands, the six URLs to check
are `https://torin.bg/new/{uslovia,warrently,covid,msg,laptopi,rezervni-chasti}.html` and the three
asset URLs under `https://torin.bg/new/img/eu/`.

## Threat Flags

None. This plan adds no endpoint, no auth path, no file-access pattern and no schema at a trust
boundary. It adds three static PNG binaries and six pages of escaped prose. `T-03-30` (non-ASCII
asset paths) is mitigated locally — the live half of its gate is in the NOT RUN table above.

## Calibration note on `actuals`

`7,900` is `chars/4` over the six authored pages, against an estimate of `75,000`. The gap is real
but it is **not** a 9× planning error in the ordinary sense: this plan's cost was overwhelmingly
*reading* — two legacy pages in full, the compliance findings, six owner questions, the warranty
config and the terminology decision — against a small authored output. If future estimates for
port-shaped plans are calibrated from this number alone they will be badly low. The honest reading is
that `estimateTokens` over the diff is the wrong metric for a plan whose work is comprehension, and
the number is recorded unadjusted rather than massaged toward the estimate.

## Self-Check: PASSED

All nine artifacts exist on disk (`src/uslovia.html`, `src/warrently.html`, `src/covid.html`,
`src/msg.html`, `src/laptopi.html`, `src/rezervni-chasti.html`, and the three PNGs under
`src/img/eu/`). All four commits (`f79854a`, `0fef579`, `31d3f7b`, `1569722`) are present in
`git log`. `min_lines` met on every declared artifact: `uslovia.html` 95 ≥ 40, `warrently.html`
123 ≥ 50, `covid.html` 128 ≥ 40.

`REQUIREMENTS.md` was **not** updated. SEO-01 and TRUST-03 are only partially discharged here — the
plan itself assigns the site-wide SEO-01 assertion to plan 03-09, and TRUST-03's per-service summary
belongs to the service pages, not to these six. Marking either complete from this plan would be
false.
