---
gsd_state_version: 1.0
milestone: v1.0
milestone_name: milestone
current_phase: 4
current_phase_name: Hardening & Cutover
status: planning
stopped_at: "**Phase 3.5 fully executed; 03.5-07 Task 2's blocking live checkpoint CLOSED.** The user"
last_updated: "2026-09-15T18:07:17.005Z"
last_activity: 2026-09-15
last_activity_desc: Phase 03.5 complete, transitioned to Phase 4
progress:
  total_phases: 4
  completed_phases: 4
  total_plans: 30
  completed_plans: 30
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-08-04)

**Core value:** A visitor with a specific repair problem must immediately see that Torin fixes exactly that, and find a clear path to contact the shop.
**Current focus:** Phase 03.5 — content-truth-revision (executed, NOT complete)

## Current Position

Phase: 4 — Hardening & Cutover
Plan: Not started
Status: Ready to plan
        live measurement. SC-1 stays PARTIAL because the sweep **confirmed** its defect.
Next: **Phase 4 — Hardening & Cutover.** The gap-closure plan (03.5-08) that owned
`src/remont-na-portove.html` is DONE, merged and live-verified. `scripts/truth-gate.js` was
deliberately NOT amended — see F2 below; that remit carries forward, it is not a blocker.
Last activity: 2026-09-15 — Phase 03.5 complete, transitioned to Phase 4
        `03.5-TRUTH-AUDIT.md` completed

Progress: [██████████] 100%

> ⚠ **The 100% is plan completion over plans that EXIST — it is not project completion, and it is
> not phase completion.** 30 of 30 written plans have SUMMARYs. Phase 4 has no plans authored yet
> and contributes 0/0 to that ratio; the bar will fall when Phase 4 is planned. It is derived from
> disk by `state.update-progress`, which counts SUMMARY files and cannot see an open defect.

**Phase 3.5 is CLOSED. What remains open is an owner decision, not engineering:**

1. ✅ **The one defect is FIXED, redeployed and re-measured (03.5-08, 2026-09-15).**
   `src/remont-na-portove.html` no longer claims chip replacement. Served body returns **0** for
   the cure claim; tree-wide `grep` for it returns **0**. The declined-bridge sweep on that page
   returns **1** — the surviving legitimate *cause* at line 102, which the fix was required to
   leave intact, on the same rule under which `tokov-udar.html` returns 3 and is MET. Page
   `200 warn=0`, `svc-page` probe PASS, body grew 24,646 → 24,732 B.

2. **DIFF-04 and CONTENT-01 are ADVANCED, NOT MET.** `kat-6` remains `'published' => false`,
   blocked on OWNER-QUESTIONS #3a–#3f plus riders #32 and #33. The page renders correctly live;
   **a page that renders perfectly and is not published is still not published**, and no
   measurement can answer an owner question. This is the only thing still outstanding from
   Phase 3.5, and it needs the owner, not a plan.

Full evidence record: `.planning/phases/03.5-content-truth-revision/03.5-TRUTH-AUDIT.md`
(13 findings routed to gap closure, F1–F13).

## Performance Metrics

**Velocity:**

- Total plans completed: 22
- Average duration: - min
- Total execution time: 0 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 01 | 5 | - | - |
| 02 | 9 | - | - |
| 03.5 | 8 | - | - |

**Recent Trend:**

- Last 5 plans: -
- Trend: -

*Updated after each plan completion*
**Per-Plan Metrics:**

| Plan | Duration | Tasks | Files |
|------|----------|-------|-------|
| Phase 01 P02 | 18min | 2 tasks | 2 files |
| Phase 01 P01 | 18min | 2 tasks | 7 files |
| Phase 01 P4 | 55min | 2 tasks | 1 files |
| Phase 01 P03 | 20min | 2 tasks | 3 files |
| Phase 01 P05 | 15min | 2 tasks | 15 files |
| Phase 02 P01 | 55min | 2 tasks | 15 files |
| Phase 02 P02 | 24min | 2 tasks | 4 files |
| Phase 02 P03 | 62min | 2 tasks | 8 files |
| Phase 02 P04 | 40min | 3 tasks | 17 files |
| Phase 02 P05 | 20 min | 2 tasks | 2 files |
| Phase 02 P06 | 12 min | 2 tasks | 2 files |
| Phase 02 P08 | 50m | 3 tasks | 5 files |
| Phase 02 P09 | 40min | 3 tasks | 2 files |

## Accumulated Context

### Decisions

Decisions are logged in PROJECT.md Key Decisions table.
Recent decisions affecting current work:

- [Research]: PHP-include restructuring chosen over Astro/Node rebuild — solves the real maintainability defect (duplicated header/nav/footer) with zero new tooling and the lowest risk to existing URL/SEO continuity.
- [Roadmap]: Phase 1 isolates all migration-safety work (URL inventory, backup/rollback discipline, PHP-include foundation) before any visual rebuild starts, per research's top pitfall (broken URLs/lost rankings).
- [Roadmap]: Cutover folded into Phase 4 alongside hardening rather than its own phase — coarse granularity setting, and cutover has only one directly-owned requirement (MIGR-02); it is sequenced last regardless.
- [Phase ?]: MIGR-01 URL inventory: test-laptop.html resolved keep-as-is per DIFF-01; covid.html and problem-stari.html left decision-pending (owner sign-off required, not silently retired)
- [Phase ?]: PHP-as-.html handler on bell.host.bg requires AddHandler application/x-httpd-php52 (CloudLinux Alt-PHP version-specific name), not the generic application/x-httpd-php variants
- [Phase ?]: FTPS to bell.host.bg uses curl --ftp-ssl -k: TLS handshake succeeds but the shared-hosting wildcard cert (*.superhosting.bg) doesn't match the vanity hostname, so hostname verification is skipped while transport stays encrypted
- [Phase ?]: 01-04: GitHub off-site backup wired to private repo torin-computers, verified in sync with local main
- [Phase ?]: MIGR-03: scripts/backup-live-site.sh detects FTP directory-vs-file type from the LIST type column (not name heuristics) and percent-encodes remote paths -- both discovered necessary via live run against bell.host.bg (dotted directory name, spaced filename)
- [Phase ?]: 01-05: All 16 live page filenames scaffolded and live-verified under public_html/new/ - SEO-04 URL-preservation guarantee proven site-wide, completing Phase 1
- [Phase ?]: 02-01: mod_deflate + mod_expires ARE available on bell.host.bg — production CSS costs 6,268 B gzipped vs 14,322 B raw, so the 20 KB budget is a target, not a hard wire cost
- [Phase ?]: 02-01: FTPS to bell.host.bg must cap the data channel at TLS 1.2 — TLS 1.3 aborts any upload over ~16 KB with 451 AFTER reporting bytes sent, silently failing every binary asset
- [Phase ?]: 02-01: Sofia Sans V1 shaping gate passed 15/15 (bg default outlines vs .loclRUS) — Bulgarian letterforms confirmed independent of browser locl support
- [Phase ?]: closeAll() skips any disclosure whose aria-controls panel contains the button just used — the research JS would otherwise collapse the mobile panel when Услуги is tapped inside it
- [Phase ?]: The mobile nav panel is in flow but right-aligned under the hamburger, not full-width: display:contents would risk the nav landmark and an absolutely-positioned brand breaks if the logo is ever redrawn taller
- [Phase ?]: geo coordinates are emitted as JSON strings, not floats — schema.org accepts Text, and PHP 5.2 float serialisation risks precision artefacts
- [Phase ?]: The 02-RESEARCH V3 PHP-5.2 lint regex false-positives on any config subscript right of a =>, which the plan's own key_link requires — narrow it to short-array literals
- [Phase ?]: The three unpublished category page files are deferred to Phase 3 (D-23/D-25): torin_category_href() already routes cards and dropdown to homepage anchors, so publishing is create-a-file plus flip-a-boolean
- [Phase ?]: The category template guards the intro and the TRUST-03 warranty summary like optional blocks — D-24 puts them in the spine but D-25 assigns their content to Phase 3, and a spine slot shipping an empty heading is the thin-content shape the publish gate exists to prevent
- [Phase ?]: The 56px call-bar reserve is scoped with body:not(:has(.callbar)) rather than a positive :has() — :not() is non-forgiving, so a browser without :has() keeps the safe unconditional reserve instead of letting the bar occlude the homepage footer
- [Phase ?]: 02-05: CR-01/CR-02 closed by scoping the WINNING selector, never escalating the loser — .hero p (0,1,1) became .hero__inner > p:not(.trust-badge) (0,2,1), and the on-dark focus ring got a (0,3,0) per-surface group; no !important, no id selector, no @layer
- [Phase ?]: 02-05: a focus ring drawn with outline-offset is painted on the surface BEHIND the control, so its contrast is measured against that surface — never against the button fill the old comment reasoned about
- [Phase ?]: 02-05: .callbar is a position:fixed sibling of <main>, inside neither .hero nor .site-footer — it must be named explicitly in every dark-surface rule group; omitting it is what produced CR-02
- [Phase ?]: 02-05: 02-REVIEW.md's 5.16:1 and 8.7:1 focus-ring figures are THEME A values reported as if they were the shipping theme — recomputed Theme B measures 9.49:1 and 11.09:1
- [Phase ?]: 02-06: the no-script nav ships via a <noscript> override stylesheet, not by shipping the nav open and collapsing it with JS — the latter has an inherent flash on every one of 16 page loads and suppressing it needs a render-blocking capability marker, i.e. a second writer of nav state
- [Phase ?]: 02-06: with scripting blocked the ENTIRE five-item nav was hidden below 56.25rem, not just the six category links — header.php's old comment understated it, and an understated record makes a later phase close the wrong defect
- [Phase ?]: 02-06: the hamburger and Услуги button are display:none in the no-script rendering because each carries a collapsed-state ARIA attribute — left visible over an open list they would announce the opposite of what is rendered, which is worse than absent
- [Phase ?]: 02-06: IA-02 deliberately NOT flipped to Complete — every rendered no-script check is unrun (no automatable browser), and commit abd5ba8 already reverted this exact class of premature flip once
- [Phase ?]: G-02-1 closed with ?v=<filemtime> query-string stamping in PHP, not per-version file renames — rename is manual and every manual step is eventually skipped
- [Phase ?]: Sofia Sans woff2 preload deliberately NOT version-stamped: it must byte-match the @font-face src in base.css, and a query string defeats font-swap.js's *.woff2 block glob, which would blind plan 02-09's G-02-4 gate rather than fail it
- [Phase ?]: Staging text/css and both JavaScript media types bounded to max-age=300 as a second line of defence behind the stamp; Phase 4 raises this once proven in production (DESIGN-02)
- [Phase ?]: 02-09: no bold local() name form resolves in Chromium (Arial Bold, Arial-BoldMT, Helvetica Neue Bold, HelveticaNeue-Bold all reject despite the files being on disk) — only the FAMILY name resolves, so a metric-adjusted fallback can only be sourced from a regular face
- [Phase ?]: 02-09: the fallback ships as ONE font-weight:400 face, not two — declaring a 700 face from a regular local file suppresses synthetic bolding and renders the heading light (canvas ink 32694 vs 40347 synthesised, against Sofia 700's 40762)
- [Phase ?]: 02-09: size-adjust 97% shipped, 9 points below the measured 106% two-line cliff — margin deliberately on the narrow side, since one percent too wide costs a whole 36.8px line while too narrow costs nothing
- [Phase ?]: 02-09: an unresolved @font-face falls to the last-resort font, which is NARROWER than Sofia Sans and sets the hero h1 at exactly 73.6px — the target height, so a fully broken measurement reads as a perfect match; every scan iteration now carries a resolution guard

- [Phase 3]: 03-01: the plan's PHP-5.2 short-array gate regex `(=>[^;]*\]|\[\s*[^]]*=>)` is broken — it matches any array read to the right of `=>`, returning 5 hits on untouched jsonld.php, so it could never pass. Plans 03-02…03-09 inherit it; use `(=>|=)[[:space:]]*\[|return[[:space:]]+\[` instead.
- [Phase 3]: 03-01: `deploy-new.sh` is denied to subagents by the permission classifier — the user must run it via `!`. It is staging-only (`public_html/new/`, hardcoded), and since no local PHP interpreter exists the deploy IS the only PHP 5.2 syntax check available.
- [Phase 3]: 03-01 live-verified: served page returns 200 with keyword h1, breadcrumbs, urgent block, warranty term line and BreadcrumbList; svc-page probe PASS at 360x640 and 1440x900 (sectionCount 3 -> 8 is the positive control).

- [Phase 3]: CSS budget resolved by stripping comments at DEPLOY time (scripts/lib/strip-css-comments.py, wired into deploy-new.sh), not by deleting them. Source keeps every comment; the wire does not pay. Production CSS 22,714 -> 5,309 B gzipped; headroom went from -2,234 B (already over) to +15,171 B. Stripper is string-aware, not a regex: components.css:853 has content:"/" and one more character would let a regex truncate the file. Brace balance verified identical in all four stylesheets.
- [Phase 3]: 03-02 live-verified PASS at 360x640 and 1440x900. Two findings: (a) torin_render_evidence() at category-page.php:265 suppresses the whole strip when photo files are absent on the server — correct defensive design, but it means photos must be deployed alongside code or the strip silently vanishes; (b) the probe could not measure its own strongest assertion because evidence images are loading="lazy" below the fold and were never fetched — run() now scrolls them in and awaits load under a bounded 5s cap before measuring.
- [Phase 3]: 03-02: CSS transfer budget is EXHAUSTED — 20,453 of 20,480 B gzipped, 27 B left for seven remaining plans. Root cause measured: components.css is 60% comments by raw bytes; stripping them drops its gzip from 12,015 to 2,849 B. The comments ship because there is no build step.
- [Phase 3]: 03-02: Google rating badge is built and gated OFF (`gbp_badge_enabled => false` in site-config.php) plus an emptiness safety net. Enabling needs a boolean flip plus THREE values, not two — the Google Business Profile URL has never been captured in this repo.
- [Phase 3]: 03-02: plan-named DIFF photos were wrong — profilaktika3 is a dust-clogged heatsink not the infrared station, and baterry.jpg/baterry2.jpg are schematic diagrams unreadable at a 100x100 crop. profilaktika6.jpg is a GIF wearing a .jpg extension.
- [Phase 3]: 03-02: photo port used `jpegtran -copy none -optimize`, not the plan's `sips -q80` which would have INFLATED the set by 147 KB and added a second lossy generation.

- [Phase 3]: OWNER DECISION 2026-08-20 — the site prefers «екран» over «матрица» in customer-facing text. Child slug becomes `smyana-na-ekran.html`, NOT `smyana-na-matrica.html`. Rule for executors: customer-facing titles, h1s, nav labels, meta descriptions and body prose use «екран»; «матрица» is retained ONLY where it is the more precise term — the bare LCD panel as a part (distinct from the whole lid/screen assembly), part specifications, and where a customer arrives repeating another shop's wording. Best practice is to introduce «матрица» once on the screen page as a synonym so BOTH terms are present for search, while «екран» carries the headings. Note the live site currently uses матриц- 14x vs екран 5x, so this reverses existing usage deliberately.
- [Phase 3]: 03-05: category 6 shipped with ZERO invented claims and its promotion gated on a boolean. The out-of-scope section was deliberately OMITTED rather than guessed — that is the gap with the highest operational cost, since the page generates enquiries with nothing steering the wrong ones away. Six gaps tabulated in 03-05-SUMMARY keyed to OWNER-QUESTIONS #3a-#3f, plus four [ASSUMED] service strings for item-by-item confirmation.
- [Phase 3]: 03-05: the battery/adapter article was never at risk — src/problem-stari.html was a Phase-2 stub; the real article lives in site-current/problem-stari.html, untouched. Its content is now split across four locations by design.
- [Phase 3]: 03-05 found Task 3's live gates self-contradictory: it demanded the footer covid.html link survive AND the live homepage return zero BG16RFOP matches, but footer.php renders on the homepage and its link text is literally «Проект BG16RFOP002-2.073». Regated on stated intent (no EU content outside the footer legal line).
- [Phase 3]: 03-05 raised the «Венера-АКС ООД» copy-paste error as OWNER-QUESTIONS #28 — note a numbering collision is possible, the orchestrator already filed #25-#27 concurrently. Reconcile at merge.

- [Phase 3]: 03-04: the plan-named battery photos baterry.jpg/baterry2.jpg are labelled SCHEMATIC DIAGRAMS, unreadable at the 100x100 evidence crop — 03-02 and 03-04 reached this independently. Used baterii.jpg instead; predicted "two and three photographs" is actually ONE and three. profilaktika16.jpg swapped for profilaktika14.jpg (the only file showing the torn contact pads the callout describes). profilaktika14.jpg was NOT uploaded by 03-02 and must be in the wave deploy.
- [Phase 3]: 03-04: src/index.html mis-captions baterii.jpg as "opened for cell replacement" — the photo shows a MELTED CASE. Outside 03-04's declared files; belongs to whoever owns the homepage next.
- [Phase 3]: 03-04: every ported claim on za-bateriite.html (10-degree delta, 90% success rate, Texas Instruments certification, Panasonic sourcing) is ~2019 shop copy, UNVERIFIED by this project. za-bateriite.html now also STATES a rationale for the longer battery warranty term (repair = service on hardware the shop did not build; regenerated pack = product it does build) — if that reasoning is wrong the site asserts something the shop does not stand behind. Ties to OWNER-QUESTIONS #23.
- [Phase 3]: 03-06: plan's `sips` instruction on the EU logos GREW the three PNGs by 16,510 bytes and re-encoded them — a chunk inventory showed nothing to strip. Reverted to byte-identical copies. Verified as real PNGs by magic bytes, not extension; both Cyrillic-named files claim 496x379 and neither is.
- [Phase 3]: 03-06: BSD grep has no -P. Any plan using `grep -cP` for non-ASCII checks must use `LC_ALL=C grep -c '[^ -~]'` instead.
- [Phase 3]: warrently.html now reproduces the 5-6 h/day clause in the shop's published wording while the shared per-page summary carries the D3-10 reframing. A source comment FORBIDS harmonising the two in either direction until OWNER-QUESTIONS #23 is ruled on.

- [Phase 3]: SLUG SET LOCKED by 03-03, 03-07 builds against it: smyana-na-ekran.html (published), smyana-na-klaviatura.html (published), smyana-na-panti.html, remont-na-portove.html, smyana-na-buksa.html (gated). The amendment landed BEFORE publication so nothing was indexed under the old slug and no redirect exists. Verified: `smyana-na-matrica` appears nowhere in src/ or scripts/.
- [Phase 3]: 03-03: the plan's word-count gate COUNTS ENGLISH COMMENTS. smyana-na-klaviatura passed the mechanical gate at 918 while sitting at 595 Cyrillic words, under the real 600 bar. Fixed by adding a fifth FAQ, not padding (now 670 measured). Any later plan leaning on that gate must measure Cyrillic separately.
- [Phase 3]: 03-03: the plan's hub self-link count is wrong — expect 4 not 3. header.php renders the Услуги dropdown through torin_category_href(), so publishing kat-2 adds an ekran-klaviatura-portove.html occurrence to the nav on every page including the hub itself.
- [Phase 3]: 03-03 chose «Екран или видеочип?» over the plan-mandated «Матрица или видеочип?» — arguable, since the heading contrasts the panel AS A PART against a chip, which is the sanctioned матрица exception. Went with екран because the rule names headings explicitly. «матрица» still appears twice in the page copy as real part references, so the search term is not lost. One-string edit to reverse.
- [Phase 3]: src/includes/categories.php has an ODD single-quote count (139) at HEAD from straight apostrophes in English comments («category's», «owner's»). Pre-existing, benign, but the quote-balance gate is USELESS on that file — do not trust it there.
- [Phase 3]: zsh does not word-split unquoted variable expansions. A `for f in $FILES` loop over a space-separated string silently treats the whole string as ONE filename. Use an array. Same trap that once made three render-check runs measure the same viewport.

- [Phase 3]: LIVE DEFECT FOUND AND FIXED 2026-08-26 — header.php:33 assigned `$torin_page = basename($_SERVER['SCRIPT_NAME'])` into the GLOBAL scope of every page that includes it. Four Wave 3 pages had named their own $page data array `$torin_page`; header.php silently overwrote each with the filename string. PHP 5.2 answers $string['intro'] by casting the key to 0 and returning character ZERO, so every lookup returned one letter, all six foreach loops warned, and the pages still returned HTTP 200 while rendering «s» where their prose belonged. Renamed to `$torin_nav_current` (nav-specific, contained: 4 usages, all inside header.php). Pages grew 13.5KB -> 23KB once content actually rendered. NO LOCAL GATE COULD CATCH THIS — it needs a live PHP interpreter, which is why the deploy is the only real syntax/runtime check.
- [Phase 3]: svc-page.js gated INCONCLUSIVE on .svc__block--urgent unconditionally. That block is liquid-damage-specific — measured, only zalivane-technosti.html declares one — so every correctly-built child page reported INCONCLUSIVE, which trains the reader to ignore the verdict. Now opt-in via SVC_EXPECT_URGENT=1; still reported unconditionally as hasUrgentBlock. Proven live in BOTH directions: zalivane+flag PASSES, child+flag correctly goes INCONCLUSIVE. Breadcrumb and warranty gates remain unconditional.

- [Phase 3]: 03-07: all five category-2 children published ('published' => true reads 5, false reads 0). Cyrillic word counts: ekran 691, klaviatura 647, panti 783, portove 710, buksa 789. All titles distinct.
- [Phase 3]: HARDENING DONE — every page data array now carries a page-specific name ($torin_ekran_page, $torin_klav_page, $torin_tok_page, $torin_about_page, plus 03-07/03-08's). No code reference to the generic $torin_page survives anywhere in src/*.html; only explanatory comments. header.php uses $torin_nav_current. Belt and braces: the include no longer squats the name AND no page depends on that.
- [Phase 3]: CONVENTION worth carrying — a source comment must never quote a gated string verbatim. 03-07 hit this: `grep -c 'Защо не отлагате смяната на буксата'` returned 2 because a head comment quoted the heading.
- [Phase 3]: 03-08: the plan's homepage-anchor gate `grep -oc 'index.html#kat-' -eq 1` can NEVER pass — two consumers render every category (card grid + Услуги dropdown), so each unpublished category emits TWO anchors. Correct assertion is one distinct CATEGORY: `grep -o 'index.html#kat-[0-9]' | sort -u | wc -l` -> 1, survivor #kat-6.
- [Phase 3]: VERIFIED — index.html does NOT need re-deploying when a category is published. It reads categories.php at request time, so publishing kat-5 flipped its card and dropdown from anchor to page link with no index.html upload. Only #kat-6 remains as an anchor, correctly gated pending OWNER-QUESTIONS #3.
- [Phase 3]: 03-08 category depth in Cyrillic words: mehanichni 1028, optimizatsiq 1070, pregryavane 955 — all above the 600 bar, and 1 & 3 slightly over the stated 600-1000 band. Left rather than cutting good copy. The plan's `wc -w` gate would have read 1457/1373/1217, inflated by English comments.

- [Phase 3]: SEO-01 CLOSED and independently verified live 2026-08-26: 23 pages, 23 distinct titles, 0 duplicates, 0 empty descriptions, description length 87-139 chars, X-Robots-Tag noindex present on 23/23 (staging, correct — stripping it is the Phase 4 cutover todo). scripts/seo-metadata-check.js runs on bare Node 20 with no dependencies; `--live` checks served output, no argument checks source.
- [Phase 3]: 03-09 DELETED src/phptest.html with user approval. It was a local-only Phase 1 spike file, 404 on staging and production, whose full record survives in 01-01-SUMMARY.md. Reason: deploy-new.sh with NO ARGUMENTS uploads all of src/ (scripts/deploy-new.sh:161), so the file was one no-arg deploy from publishing phpversion() output. The GSD cleanup helper blocked the merge on branch_contains_deletions; merged manually after review.
- [Phase 3]: 03-09 found THREE of its own plan's verify commands broken, none masking a real defect but two producing false failures: (a) `grep -c 'if (!isset($torin_title))'` returns 0 because BSD grep mishandles the `$` — use grep -cF; (b) a price/turnaround grep matches EUR inside the English word "amateur" in a comment; (c) the quote-parity check fails on the UNTOUCHED tree (169 quotes at HEAD, unpaired ones are English possessives in // comments) and can never pass.
- [Phase 3]: 03-09 added a separate NO_CTA_EXPECTED list rather than widening LONG_SUFFIX_OK — uslovia.html is a privacy declaration with no call to action, and widening the suffix list would have changed its RENDERED suffix to satisfy an unrelated rule.

- [Phase 3]: VERIFIED 2026-08-26, status human_needed. 7 requirements Complete (TRUST-01, TRUST-03, DIFF-01/02/03, CONTENT-02, SEO-01); TRUST-02 and CONTENT-01 stay Pending by explicit decision, not defect. Verifier re-derived the headline assertions with its OWN parser rather than trusting the project's gates: 23/23 non-empty and distinct titles and descriptions, zero script-mixed words, exactly one h1 per page, all 45 distinct relative link targets return 200, zero PHP warnings, main-content Cyrillic counts 767-1165, 7 probe runs all PASS.
- [Phase 3]: W-1 SUBSTANTIVE — za-bateriite.html advertises a 1-YEAR battery warranty whose «Пълни гаранционни условия» link lands on warrently.html, which states «за всички сервизни дейности и услуги е 1 месец» and never mentions batteries. Inherited from the legacy site and correctly left unharmonised per OWNER-QUESTIONS #23, but Phase 3 RAISED the exposure by giving batteries their own page. Must become a named rider on #23 — never fixed by guessing which term the shop honours.
- [Phase 3]: W-3 the dev theme switcher renders on all 23 staging pages — documented Phase-2 artefact with a Phase-4 removal step, not a leak. W-4 google1718743335455f1c.html (Search Console token) has no counterpart in src/ — MIGR-02/Phase 4, easy to lose at cutover.
- [Phase 3]: TRUST-02's ENABLED render path has never executed anywhere — no local PHP runtime and staging serves the disabled branch. Presence and wiring at both call sites (index.html:285, category-page.php:561) are proven; rendering is NOT. Recorded as behavior_unverified, not as passing.

- [Phase 3/4]: SCOPE CHANGE 2026-09-11 (OWNER-QUESTIONS #31) — battery regeneration, BGA/reballing and the sales line are DISCONTINUED by the business. DIFF-02 and DIFF-03, both verified Complete on 2026-08-26, describe services the shop no longer offers. Only DIFF-01 (self-diagnostic) survives. 57 chip-level claims across 9 files; battery content across 8. This is a requirements revision, not gap closure — Phase 3 built what was specified.
- [Phase 3/4]: Category 4 re-scoped by the owner: «Заливане и ремонт на дънни платки» now covers liquid cleaning, corrosion removal, NON-BGA component-level soldering, and board replacement where the case calls for it. Everything chip-level goes (infrared station, AMTECH, 90% claim, reballing stages, северен/южен мост, видеочип).
- [Phase 3/4]: Three deployed repair photos are reballing photos and must be withdrawn with the claims: profilaktika17.jpg (infrared station), profilaktika7.jpg (pads before new balls), profilaktika15.jpg (hot-air-gun damage).
- [Phase 3/4]: Retirement is by 301 REDIRECT, never deletion — za-bateriite.html, laptopi.html and rezervni-chasti.html are all indexed URLs from the original 16. Targets still to be chosen.
- [Phase 3/4]: Category 1/2 boundary redefined (OWNER-QUESTIONS #17): cat 1 = physical/mechanical damage, cat 2 = electronic malfunction. Customer-facing form of the same rule: «има видима повреда» vs «изглежда здрав, но не работи». Exposes two shipped inconsistencies — svc-panti is parented to kat-2 while kat-1's symptom line claims «разхлабени панти», and kat-2's symptom line leads with «пукнат екран» which is physical damage. The five child pages are COMPONENT pages and should NOT be re-parented; both categories link to the relevant ones.
- [Phase 3]: LIVE FACTUAL ERROR — site-config.php:175 ships 'Apple' in the brand row, but the owner excludes Apple and Chromebook. The staging site currently advertises a brand the shop avoids.
- [Phase 4]: Viber button DROPPED in favour of the contact form — reverses D-16 (chat as equal-weight primary action) and retires the verify-viber-button-before-launch todo. Contact form confirmed with name, phone, email, device model, fault description AND photo upload.
- [Phase 4]: NEW SCOPE from owner — (a) owner-editable working-hours file the site reads, (b) owner-editable holiday banner, off by default, auto-expiring, (c) Google Analytics on contact buttons and service-page visits, (d) a services list for owner-supplied turnaround times. Prices will NEVER be published — they change too dynamically.
- [Phase 3]: GBP verified live 2026-09-11 — rating 4.7, 157 reviews, profile https://maps.google.com/?cid=7041654319750291392. Owner wants the review count rounded with a '+' so it stays accurate longer. GBP business name is «Torin Kampani» (the legal entity, in Latin); owner wants it changed to the trade name.
- [Phase 3]: Legal entity ТОРИН КЪМПАНИ ООД, trade name ТОРИН КОМПЮТЪРС, address «ул. Свети Иван Рилски 46» WITHOUT the № sign (chosen for consistency with GBP). ЕИК still NOT provided — the one thing the legal pages actually need.
- [Phase 3]: Warranty is 1 month for all EXCEPT category 6. Free diagnostics applies to categories 1-5 only and must be reworded «първоначална» (initial). Both ship on many pages.

- [Phase 4 — CUTOVER BLOCKER, found 2026-09-12]: **No host canonicalisation exists.** All four variants return 200 with no redirect: `http://www.torin.bg/`, `http://torin.bg/`, `https://torin.bg/`, `https://www.torin.bg/`. The same content is served on four hosts, so ranking signals are split four ways. Google Search Console shows the variant actually indexed and ranking is **`http://www.torin.bg/`** (23 clicks / 16,845 impressions) — the LEAST preferred form (insecure + www), while the rebuilt site's `site-config.php:237` sets `base_url` to `https://torin.bg/`. At cutover this must be resolved deliberately: pick one canonical host, 301 the other three to it, and make sure the chosen canonical is the one GSC's history attaches to — or the ranking history sits on a host that now redirects away. SEO-04 covers URL *paths*; nothing in the project covers the *host*, which is how this went unnoticed for three phases.
- [Phase 4]: GSC data point 2026-09-12 — homepage 23 clicks on 16,845 impressions. Worth checking average position before drawing conclusions (a 0.14% CTR is normal at position 40 and alarming at position 5), but it is the first real traffic number the project has had.

- [Phase 4 / SEO]: **GSC 16-month window shows ONLY the homepage in Performance → Pages.** None of the 22 other pages appears — including the four retiring URLs, which is what made the redirect decision low-risk. Two candidate explanations and they matter very differently: (a) the identical/empty titles and descriptions across all 16 legacy pages (the exact defect SEO-01 existed to fix) suppressed the whole site, so Google only ever surfaced the homepage; or (b) the GSC property is URL-prefix scoped to one host and the other three un-canonicalised variants hold the rest. **Do not conclude (a) without ruling out (b)** — check whether the property is Domain or URL-prefix. If (a) holds, Phase 3's SEO-01 closure (23 distinct titles and descriptions, verified live) is the single highest-value change made so far, and post-cutover indexing is the test. Worth watching deliberately rather than discovering by accident.

- [Phase 3.5]: **The blast-radius figure has no single true value, and the missing ingredient was METHOD, not token set.** All three historical figures reproduce exactly and all three are correct: **57 in 9 files** (`grep -roiE 'BGA|реболинг|ребол|чипсет|видеочип|инфрачервен|AMTECH|дозапояване|северен мост|южен мост' --include='*.html'` over `src/` at `98994e3`), **95 in 11 files** (same command, `|ниво чип|регенерац` added), **165 in 21 files across 42 scanned** (`node scripts/truth-gate.js`, wider token set and wider scope). With the token set held exactly constant, method alone moves SET-95 across a 51% spread: 95 case-insensitive occurrences, 83 case-sensitive, 63 lines, 87 per-token sum. **Rule: no figure may be published without its token set AND the exact command that produced it** — the command carries the method implicitly; a prose citation does not.
- [Phase 3.5]: **`scripts/truth-gate.js` is the phase's single token-set owner — it is CALLED, never re-derived**, and it prints its own Class-A and Class-B token lists on every run. That is the pattern: a gate that publishes its own bound. Post-wave-2 it reports `class-A hits: 0 in 0 files, 38 scanned, exit 0` — real, reproducible, **and bounded by a token set that is measurably incomplete for Bulgarian inflection** (see the blocker on F2). Published with its bound, never bare.
- [Phase 3.5]: **The vocabulary ruling: component-level survives, chip-level does not.** A Class-B token may survive only where it describes a SYMPTOM, a DIAGNOSTIC OBSERVATION or a CAUSE — never work Torin performs, equipment Torin owns, or an outcome Torin achieves. 44 survivors across 12 files were each placed in one of the three; **exactly one could not be placed**, and it is the open defect. The honest replacement for a withdrawn chip-level remedy is the one `zalivane-technosti.html` already uses: at that level of damage **the board is replaced, not the chip**. Fixes of this kind are vocabulary corrections, never excisions — the surrounding safety argument is true and must survive.
- [Phase 3.5]: **The four retired URLs and their locked 301 targets** (D3.5-09; SEO-05 named four all along, not three): `covid.html` → `about.html`, `laptopi.html` → `index.html`, `rezervni-chasti.html` → `ekran-klaviatura-portove.html`, `za-bateriite.html` → `zalivane-technosti.html`. All four live-verified 301 → 200 in **one hop**, twice: 2026-09-12 and again 2026-09-15 against the redeployed tree. `laptopi → index` is flagged, not clean — «Употребявани лаптопи» has no surviving equivalent and Google treats an irrelevant 301 as a soft 404; accepted because GSC's 16-month window shows none of the four in Performance → Pages, so no measurable ranking equity is at stake.
- [Phase 3.5]: **`TRUST_EXPECT_EVIDENCE` was added as an OPT-IN flag to `scripts/probes/trust-signals.js`** (03.5-02) because the homepage legitimately has no evidence strip once the two retired differentiator sections were removed — a gate asserting a strip must exist would fail on the correct page. **Proven in both directions live 2026-09-15:** flag absent → `evidenceStrips: 0` → `PASS`; flag present → the same `evidenceStrips: 0` → `INCONCLUSIVE` with the absence named in the `inconclusive` array. A gate that cannot fail proves nothing; this one demonstrably can.
- [Phase 3.5]: **⚠ A `PASS` from `trust-signals.js` is NOT evidence the rating badge rendered.** `scripts/probes/trust-signals.js:39-41` exempts the badge from the verdict expression — written when the badge's absence was the specified, shipped state. This phase inverted that state and the exemption did not move with it. **Always read `ratingBadgePresent` and `brandItemCount` out of the JSON explicitly.** The exemption itself is not yet fixed.
- [Phase 3.5]: **`150+` in the rating badge is deliberate and must never be "corrected" to 157** (D3.5-07). The live count on 2026-09-11 was 157; the rounded floor is the owner's explicit request so the figure stays true as the count climbs without a redeploy. Changing it to an exact number is a regression, not a fix.
- [Phase 3.5]: **The live deploy is the ONLY PHP check this project has** — no local interpreter exists — and HTTP 200 does not settle it. The `$torin_page` collision once served 200 on four pages while rendering the single letter «s» where prose belonged, and passed every local gate across five plans. **The Cyrillic token count of the served body is the real tell.** The 2026-09-15 sweep is the first PHP execution ever for twelve page files plus `includes/categories.php`; all 19 pages returned `200 warn=0` with substantive bodies.
- [Phase 3.5]: **RENDERED figures run ABOVE SOURCE and the two are never conflated.** Measured 2026-09-15: `zalivane-technosti` 1066 SOURCE / 1260 RENDERED, `profilaktika-laptop` 555 / 736, `index` 235 / **414** (the ~308 rendered figure that stood in the record was an estimate and is now replaced by its measurement). Every page draws text at render time from `categories.php`, `brand-row.php`, `site-config.php` and `rating-badge.php`. Likewise `sectionCount: 6` RENDERED is the documented SOURCE+1 — the brand row injects at render time and carries no section literal — **not a discrepancy**.
- [Phase 3.5]: **SEVEN defective check commands were found in this phase, every one by RUNNING it rather than reading it**, and five of them sit in the live-verification chain: a deploy command missing three image files (would have emptied an evidence strip at HTTP 200), a `strips=1` expectation on two pages that declare no strip by design, a photo regex whose character class excludes the hyphen (`photos=1` against a true 3), a brand-row count matching substrings rather than elements (8 against a true 7), and a line-oriented `sed` over a multiline JSON-LD block (reported malformed; it is valid). **Four of the five fabricate a defect on a correct page; the fifth manufactures a real one. Not one fails toward a missed defect.** Amended rule: **no command may be trusted until it has been run against a case whose answer is known independently of the command.**
- [Phase 3.5]: **An audit plan owns no source file.** A fix discovered at audit time is ROUTED to a gap-closure plan with its own gate, never committed alongside the audit — a commit that both fixes and audits cannot be trusted to have done either. `truth-gate.js` was likewise **not** amended: changing its token set would change the meaning of every figure in six SUMMARYs.

### Pending Todos

None yet.

### Blockers/Concerns

- [Phase 1]: RESOLVED (01-01) — `.htaccess` `.html`-as-PHP behavior spike-verified live against `bell.host.bg`. Neither `AddType application/x-httpd-php` nor `AddHandler application/x-httpd-php5`/`php-script`/`application/x-httpd-php5` (AddType variant) worked (raw source served, or 500 Internal Server Error). The host runs CloudLinux "Alt-PHP" (`/opt/alt/php52/...` confirmed via `error_log`), which requires the fully-versioned handler name: `AddHandler application/x-httpd-php52 .html .htm`. This is now what `src/.htaccess` uses; live-verified via `curl https://torin.bg/new/phptest.html` → `PHP-IN-HTML-OK 5.2.17`.
- [Phase 1]: RESOLVED (01-01) — PHP version live-reconfirmed as **5.2.17** via direct `phpversion()` probe (not just the `X-Powered-By` header this time), executing through the confirmed `.html`-as-PHP handler. Composer availability still unconfirmed (no shell/SSH access to test `composer --version`). Open question carried to Phase 4: does a cPanel/control-panel login exist for `bell.host.bg`, separate from the FTP-only credentials in `filezilla-server-data.xml`? The host's CloudLinux Alt-PHP setup (confirmed this session) normally exposes PHP-version switching via cPanel's "MultiPHP Manager"/"Select PHP Version" — if a cPanel login exists, bumping PHP version for PHPMailer 6.x (Phase 4) is likely straightforward; if not, PHPMailer 6.x (which requires PHP ≥5.5) cannot be used and an older PHPMailer major version or alternative mail library must be selected instead (RESEARCH.md Pitfall B / Open Question 1).
- [Phase 3]: Category 6 (non-standard electrical equipment) has no existing content — needs direct owner input on scope before this content can be written.
- [Phase 3]: Google Business Profile status (active with reviews?) unverified — confirm before committing to the Google rating badge.
- [Phase 3/4]: Real price ranges, before/after photos, and turnaround-time commitments are v2 (deferred) but were owner-input-dependent gaps noted in research; not blocking v1.
- [Phase 4]: Holiday-banner script (`otpuska.js`) intent needs an explicit owner decision (keep with maintained equivalent, or drop).
- [Phase 1/3]: `covid.html`'s fate is undecided — pending-owner-decision, not silently retired. Content is slated to move to the About page per CONTENT-02 (Phase 3), but retiring the currently-indexed URL itself needs explicit owner sign-off before doing so, since an unreviewed retirement risks losing existing ranking/traffic. See `01-URL-INVENTORY.md`.
- [Phase 1]: `problem-stari.html`'s fate is undecided — pending-owner-decision. No current v1 requirement addresses this page; suspected content overlap with `mehanichni-problemi.html` is unconfirmed. Flag for owner review before any retire/merge/redirect decision is made. See `01-URL-INVENTORY.md`.
- [Phase 2] Web-font swap backstop is observed but UNMITIGATED: cold-cache first paint shows a brief flash of invisible text (font-display: swap's block period). No layout shift (CLS 0.00053). Closing it properly means size-adjusting the fallback stack — deliberately deferred, not fixed in 02-01.
- [Phase 2] UI-SPEC's above-the-fold arithmetic assumed the hero min-height would bind, but the two CTA labels (178.2px + 189.4px) cannot share a 328px content box at 360px, so the CTA block is intrinsically two rows. Plans adding hero content must re-check the 42% budget.
- OWNER-QUESTIONS #20 (working hours) and #21 (chat-capable number) block the Phase 4 cutover: both ship on all 16 pages and #20 also feeds Google's structured data
- [Phase 2] 02-05 rendered-geometry and keyboard human-checks are UNRUN: no automatable browser on the build machine (no Chrome/Chromium/Playwright; Safari remote automation disabled behind a GUI-only setting). The hero-stack figure in components.css is DERIVED (241.6px), not measured, and the FOUT backstop is re-opened by the 8px stack change.
- [Phase 2] 02-06 rendered no-script checks are UNRUN (same no-browser blocker as 02-05): nav visibility/activation at 360/900/1440px, header focus order, and scrollWidth<=innerWidth with scripting disabled. The UI-SPEC overflow backstop is RE-OPENED by 02-06's desktop layout and abstains to human_needed. Also open: 'flex: 1 0 100%' on the mid-list has-sub item splits the visible top-level links across two wrapped rows rather than one.

- [Phase 3.5 — ✅ CLOSED 2026-09-15 by gap-closure plan 03.5-08]: **`src/remont-na-portove.html` published a claim to a discontinued service, and no longer does.** The remedy clause «единственото решение тогава е подмяна на самия чип» **is no longer in the tree** — `grep -rnoiE 'подмяна на самия чип|смяна на чипа|подмяна на чипа' src/` returns **0**, and the served body returns 0 for it. It was replaced with board replacement, wording reused from `src/test-laptop.html:141` and `src/zalivane-technosti.html:156`, nothing composed; the mechanism, the cause and the escalation warning «не отлагайте порт…» all survived intact, and the page GREW 24,646 → 24,732 B rather than being hollowed. **LIVE after redeploy:** the declined-bridge sweep on that page returns **1**, not 2 — the single surviving hit is the legitimate *cause* at line 102, the same rule under which `tokov-udar.html` returns 3 and is MET. A future re-audit must NOT read nonzero there as failure. **Why it survived the whole phase, worth keeping:** (a) `truth-gate.js` carries `southbridge`/`northbridge` as base literals «южен мост»/«северен мост» while every tree occurrence is declined, so those two literals have never matched anything — see F2; (b) no content plan owned the file; (c) it has zero typed inbound links. **The difference in outcome from the near-identical claim on `mehanichni-problemi.html:111`, which 03.5-06 corrected, was ownership, not difficulty** — that is the transferable lesson: a tree-wide sweep must be owned by someone, or unowned files are invisible to every plan.
- [Phase 3.5 — F2, high]: **`truth-gate.js`'s Class-A literals miss Bulgarian inflection.** `grep -rnoiE 'южен мост|северен мост' src/` returns **nothing** while the declined forms return five hits. Remit for the gap-closure plan: (1) add declined forms or switch those two to stem matching; (2) audit the WHOLE Class-A list against Bulgarian inflection — these two are the *known*-affected entries, not necessarily the only ones; (3) re-run every figure in `03.5-TRUTH-AUDIT.md` and state what changed. Not fixed in the audit plan because amending the token set changes the meaning of every figure in six SUMMARYs.
- [Phase 3.5 — cutover]: **Three withdrawn photographs and four source-deleted pages remain on the staging server.** `profilaktika17.jpg`, `profilaktika7.jpg`, `profilaktika15.jpg`, plus `covid.html`, `laptopi.html`, `rezervni-chasti.html`, `za-bateriite.html`. `deploy-new.sh` uploads and **never deletes**, and no script in this project can delete a remote file. The photographs are referenced by no page (live-verified 0) but stay **fetchable by direct URL**; the pages are unreachable behind the 301s. **Manual FileZilla pass at cutover.** Phase 4 decision: give `deploy-new.sh` a delete capability with an explicit allowlist, or write manual cleanup into the cutover checklist permanently.
- [Phase 3.5 — cutover-blocking]: **The `.htaccess` retirement block needs TWO edits at Phase 4 promotion, not one:** the canonicalisation target changes from `https://torin.bg/new/$1` to `https://torin.bg/$1`, AND the `RewriteBase` changes from `/new/` to `/`. **Missing either breaks a redirect silently, at 301, with a `Location` header present** — which is exactly what happened on the first deploy, when a relative substitution in a per-directory `.htaccess` made Apache build the `Location` from the filesystem path. A check that greps only `^HTTP/` and `^location:` PASSES that defect; only following the redirect exposes it. Without promotion, four of the original sixteen indexed URLs 404 silently, because the source files are deleted and nothing in a build fails when an `.htaccess` block is left behind.

## Deferred Items

Items acknowledged and carried forward from previous milestone close:

| Category | Item | Status | Deferred At |
|----------|------|--------|-------------|
| v2 requirements | PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01 | Deferred | Requirements definition (2026-08-04) |

## Session Continuity

Last session: 2026-09-15
Stopped at: **Phase 3.5 fully executed; 03.5-07 Task 2's blocking live checkpoint CLOSED.** The user
ran `scripts/deploy-new.sh` (24 files); the orchestrator ran all eight live measurements.
`03.5-TRUTH-AUDIT.md` now carries them: 19/19 pages at `200 warn=0`, served Class-A tokens 0 on all
19, all four redirects 301 → 200 in one hop, every evidence strip correct, badge and brand row live
on three pages, the keyed category-6 warranty carve-out rendering, and all six rendered probes at
expectation. **9 of 9 success criteria MET** (SC-3, SC-7, SC-8, SC-9 promoted on measurement;
SC-1 closed by gap-closure plan 03.5-08 and verified live).
**The phase does NOT close:** one live-confirmed OPEN DEFECT and two owner-blocked requirements.
Resume file: none — **next action is to PLAN a gap-closure phase** owning
`src/remont-na-portove.html` (F1) and `scripts/truth-gate.js` (F2), plus the eleven lower-severity
findings F3–F13 recorded in `03.5-TRUTH-AUDIT.md`.
