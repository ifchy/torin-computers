# Phase 3: Content & Trust-Signal Build-Out - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-08-11
**Phase:** 3-Content & Trust-Signal Build-Out
**Areas discussed:** Owner-input gaps, Legacy copy treatment, Publish gate & new pages, Legacy page dispositions, Brand-logo row, Google rating badge, Warranty summary, Battery regeneration (DIFF-02), plus four reconciliation decisions forced by the user-supplied task list

---

## Area selection

User selected **all eight** offered gray areas, and added that a prepared list of SEO/conversion
changes existed and should be taken into account. That file — `torin-new-build-tasklist.md` — was
supplied mid-discussion and became the largest single input, reframing four areas before they were
individually discussed.

**User's framing of the file:** *"all that is in the file is just a suggestion that is supposed to
help improving what is already been planned here"* — advisory, not authoritative. This is what
allowed four of its structural proposals to be rejected without conflict.

Todo cross-reference: two pending todos matched at score 0.2 (`redraw-category-icons`,
`verify-viber-button-before-launch`). User selected **Neither**.

---

## URL structure (forced by the task list)

| Option | Description | Selected |
|--------|-------------|----------|
| Keep locked URLs | SEO-04 stands; existing 16 filenames unchanged, new pages additive | ✓ |
| Hybrid — new pages only | Keep 16, adopt keyword-rich slugs for genuinely new pages | |
| Adopt the full new map | Rewrite all URLs with 301s as the task list proposes | |

**User's choice:** Keep locked URLs.
**Notes:** The task list's Workstream 0 proposed rewriting all sixteen URLs behind a 301 map.
Claude recommended against on the grounds that keyword-in-URL is a small ranking factor and 301s
pass essentially full PageRank, so the upside is marginal, while the downside is a sixteen-URL
migration against the project's single largest stated risk — with Search Console access still
unavailable (OWNER-QUESTIONS #1), meaning nobody could observe whether it worked.

---

## Cluster 2 child pages

| Option | Description | Selected |
|--------|-------------|----------|
| Yes, add child pages | Five children: панти, матрица, клавиатура, USB, букса; cat 2 becomes a hub | ✓ |
| Not this phase | Ship six category pages first, revisit children later | |
| Only the two highest-value | Matrix and hinges only | |

**User's choice:** Yes, add child pages.
**Notes:** The task list's reasoning was accepted — each is a distinct search with its own price
intent, and one page competing for five keywords loses all five. Purely additive, so compatible
with the six-category IA.

---

## Categories 5 and 6

| Option | Description | Selected |
|--------|-------------|----------|
| Keep all six | Honor the owner constraint; cat 6 stays behind the D-23 publish gate | ✓ |
| Park both per the task list | Drop to four categories this phase | |
| Keep 5, park 6 | Cat 5 ships; cat 6 homepage-only | |

**User's choice:** Keep all six.
**Notes:** The task list backlogged Прегряване и охлаждане and Нестандартна техника. Rejected
against PROJECT.md's hard constraint that the six categories are explicit owner direction, IA-01
(Complete) on six sections, and Phase 3 success criterion 5.

---

## Prices and reviews — v1 or v2?

| Option | Description | Selected |
|--------|-------------|----------|
| Promote prices to v1 | Real numbers unblock PRICE-01, price teasers, Offer schema | |
| Promote reviews to v1 | Unblocks TRUST-02 badge with a real number plus quotes | |
| Keep both in v2 | Ship on content and trust signals as scoped | ✓ |

**User's choice:** Keep both in v2.
**Notes:** Claude flagged two non-obvious consequences afterward: the matrix page ships without the
price number the task list says it needs to win, and TRUST-02 (static badge, v1/Phase 3) is
separate from REVIEWS-01 (live widget, v2) — so the reviews line needed a sharper cut. That
prompted the follow-up question below.

---

## Google rating badge — where the reviews line sits

| Option | Description | Selected |
|--------|-------------|----------|
| Static badge only | Hardcoded rating + count in site-config, linking to GBP | ✓ |
| Badge + hand-picked quotes | Plus 2–3 manually chosen quotes on the homepage | |
| Drop TRUST-02 this phase | Defer the badge too | |

**User's choice:** Static badge only.
**Notes:** No API, no per-service quotes, no `/otzivi/` page. Real figure to be confirmed against
GBP before shipping. AggregateRating schema deliberately not emitted, since no reviews are visible
on-page.

---

## Owner-input gaps

| Option | Description | Selected |
|--------|-------------|----------|
| Draft, flag, ship what's unblocked | `[ASSUMED]` markers in-source, cat 6 stays gated | ✓ |
| Batch the questions first, then write | Pause phase 3 until the owner answers | |
| Split: ask about cat 6 only | Send the launch-blocking one now | |

**User's choice:** Draft, flag, ship what's unblocked.

---

## Battery regeneration (DIFF-02)

| Option | Description | Selected |
|--------|-------------|----------|
| Own it properly — distinct block | `za-bateriite.html` as depth page; satisfies DIFF-02 | ✓ |
| Link out to SmartBattery.eu | Per the task list | |
| Keep D-13's folded placement | Status quo, DIFF-02 knowingly downgraded | |

**User's choice:** Own it properly.
**Notes:** User volunteered a fact that decided it — **"smartbattery.eu does not exist anymore and
anything related to it should be fixed or removed."** With no site to link out to, `za-bateriite.html`
must carry the story itself. A follow-up grep found four surviving references, all in
`site-current/` and none yet ported into `src/`, including one in the privacy declaration
(`uslovia.html`) that makes commitments on behalf of a dead site. This supersedes D-13 and resolves
OWNER-QUESTIONS #9.

---

## Legacy copy treatment

| Option | Description | Selected |
|--------|-------------|----------|
| Rewrite service pages, port the rest | Service/content rewritten; legal/utility ported | ✓ |
| Rewrite everything | Every page fresh to the new standard | |
| Port everything, rewrite later | Fastest route off stubs | |

**User's choice:** Rewrite service pages, port the rest.
**Notes:** User added — *"but still make sure when porting improvements will be done where needed"*.
A port is not a copy: dead links, the `усливията` typo, SmartBattery references and stale contact
details get fixed even on ported pages.

---

## problem-stari.html disposition

| Option | Description | Selected |
|--------|-------------|----------|
| Make it the category 6 page | Existing indexed URL becomes Нестандартна техника | ✓ |
| Keep as a general symptom index | A «чести проблеми» router page | |
| Minimal port, decide later | Keep the URL alive, defer the role to the owner | |

**User's choice:** Make it the category 6 page.
**Notes:** Its slug reads as «стари» and cat 6's own symptom line is «нестандартна или стара
техника, която другаде не приемат». Puts cat 6 on an existing indexed URL instead of a new slug and
resolves OWNER-QUESTIONS #5 without retiring anything.

---

## Warranty summary (TRUST-03)

| Option | Description | Selected |
|--------|-------------|----------|
| One shared summary, reframed | Single block via site-config; 5–6h condition reframed | ✓ |
| Per-category warranty text | Different terms per category | |
| Shared summary, terms verbatim | Reuse one block, wording unchanged | |

**User's choice:** One shared summary, reframed.
**Notes:** The live warranty page requires the customer to use the laptop 5–6 hours a day during
the warranty period. Read charitably it is a confidence statement; read as a customer would, it
looks like a way to void a claim. Reframed as the former without dropping it.

---

## Depth bar for the D-23 publish gate

| Option | Description | Selected |
|--------|-------------|----------|
| Adapted DoD, 600–1000 words | Keep achievable items; drop price/review/photo to a v2 list | ✓ |
| Lower bar, publish sooner | Spine only, ~300–400 words | |
| Full DoD, publish nothing without it | Hold everything until prices/reviews/photos exist | |

**User's choice:** Adapted DoD, 600–1000 words.
**Notes:** Supplies the bar D-25 owed and D-23 has been gating against since Phase 2 without one.

---

## Brand-logo row (TRUST-01)

This question was asked twice. On the first pass the user declined to answer and asked to see how
competitors handle it. Claude fetched eight competitor sites live and found **zero of eight use
logo images** — all use plain text, none claims authorized status, and the site prior research
called the "most modern/complete competitor found" (Trierra Soft) omits brands entirely.

| Option | Description | Selected |
|--------|-------------|----------|
| Designed text wordmark row | 6–8 styled wordmarks, «Обслужваме всички марки» + «и др.» | ✓ |
| Real logo images | Greyscale brand marks — unique in this market, but trademark exposure | |
| Long text list, ITServiz-style | 30–50 names, maximum keyword coverage | |
| Skip it, follow Trierra | Drop the row; organize by device type | |

**User's choice:** Designed text wordmark row.
**Notes:** The evidence also corrected TRUST-01's premise — what is universal across competitors is
the *claim* ("all brands serviced"), not a logo row. The requirement inherited a format the market
does not use.

---

## Claude's Discretion

- New page filenames for five child pages and two remaining category pages
- How child pages are represented in data (`categories.php` holds only the six)
- Brand row placement, and which six-to-eight brands appear pending owner confirmation
- Service / FAQPage / BreadcrumbList schema implementation
- Whether EU/COVID content moves to About as prose or a compact block

---

## Deferred Ideas

**To Phase 4:** task list Workstreams 5 and 7 in full — quote form, sticky call bar, thank-you page,
conversion events, contact hardening, cutover, sitemap, crawl validation, GBP URL update, Core Web
Vitals, 404 monitoring. Also GSC/Analytics setup, blocked on OWNER-QUESTIONS #1.

**To v2:** PRICE-01 (including whether dual лв/€ display is now a legal requirement rather than a
convention), REVIEWS-01, GALLERY-01, TURNAROUND-01, an SSD-upgrade child page, standalone BGA page,
per-brand pages, English layer, neighbourhood pages, blog cadence.

**Recorded, not acted on:** express-service tier and declined-repair diagnostics policy — both
operational decisions for the owner. Logo redraw timing, carried from Phase 2.

**Raised and filed separately:** during a git-sync request mid-discussion, an audit of staging
indexability found that `src/.htaccess`'s `X-Robots-Tag "noindex, nofollow"` block would ship
sitewide if the file is promoted to root as its own header comment describes. Filed as
`.planning/todos/pending/strip-staging-noindex-at-cutover.md` (`resolves_phase: 4`). The task list
independently flags the same risk, which is corroboration from a second source.
