---
phase: 03-content-trust-signal-build-out
plan: 04
subsystem: differentiator depth pages
tags: [php52, content, diff-01, diff-02, diff-03, seo-01, warranty, routing, terminology]
status: complete

requires:
  - 03-01 torin_render_service_page(), the $page key contract, $site['warranty'] keyed set
  - 03-02 torin_render_evidence(), src/img/repairs/ (41 photographs), the homepage DIFF-02/DIFF-03 blocks
  - Phase 2 categories.php + torin_category_href() publish gate
provides:
  - src/za-bateriite.html — DIFF-02 depth (regeneration process, cell sourcing, the battery warranty)
  - src/test-laptop.html — DIFF-01 self-diagnostic with per-symptom routing into the services
  - src/profilaktika-laptop.html — DIFF-03 depth (reflow equipment, flux, temperature and durability evidence)
  - first live use of 'warranty_key' => 'battery'
affects:
  - the homepage DIFF-02/DIFF-03 blocks now have real hand-off destinations
  - categories 3 and 5 have a profilaktika page to cross-list into (D-28)

tech-stack:
  added: []
  patterns:
    - a differentiator page carries no cat_id and supplies its own display name
    - every category route on a content page resolves through torin_category_href()
    - a prohibited literal is described in a comment, never quoted, so its own gate stays mechanical
    - «екран» carries customer-facing copy; «матрица» is kept only as a part-level term

key-files:
  created: []
  modified:
    - src/za-bateriite.html
    - src/test-laptop.html
    - src/profilaktika-laptop.html

decisions:
  - the battery evidence strip uses baterii.jpg alone, not the two labelled schematic diagrams the plan named — the strip crops every figure to 100x100 and a cropped block diagram proves nothing
  - the profilaktika strip substitutes profilaktika14 (a magnified pair of torn contact pads) for a second whole-board shot, because it is the only photograph in the set that shows the damage the callout describes
  - «матрица» is retained once on test-laptop.html for the lamp and inverter, which belong to the panel as a part

metrics:
  duration: ~2h
  completed: 2026-08-23

actuals:
  tokens: 71000
  tasks: 3
  commits: 4
---

# Phase 3 Plan 04: Differentiator Depth Pages Summary

The three differentiators now have the depth pages the homepage blocks hand off to — battery
regeneration told in full without the dead specialist-site pointer, the self-diagnostic routed from
every symptom group into the service that fixes it, and the chip-level evidence lifted out of a page
about cleaning — **with every live check unrun, blocked on the same deploy permission gate plans
03-01 and 03-02 hit.**

## What Was Built

**Task 1 — «Регенерация на батерии за лаптоп»** (`d9f7c37`)

`src/za-bateriite.html`, 883 Bulgarian words, rendered through `torin_render_service_page()` with no
`cat_id` — battery regeneration is not one of the six categories, it cuts across them, so the page
supplies its own display name exactly as the D3-03 child records do.

The page is the **first consumer of `'warranty_key' => 'battery'`**. It authors no warranty literal;
the term and its detail line come from `$site['warranty']['battery']`, and the service term every
other repair page renders comes from the same set. That is what makes the two numbers a *distinction*
rather than a contradiction a customer discovers by opening two tabs. The callout block states the
distinction plainly — a service on hardware the shop did not build versus a product the shop does
build — and is marked `[ASSUMED]` against OWNER-QUESTIONS #23 in source.

The legacy page's closing pointer to the specialist battery site is **dropped**, and the comment
recording why cites `D3-12` by decision id and deliberately does not spell the domain: a repo-wide
grep asserts the domain's absence, and a comment naming it would trip that gate against correct code.
This is the convention plan 03-02 established for four other prohibitions.

**Task 2 — «Тествай сам своя лаптоп», routed** (`11c9438`, terminology follow-up `7406312`)

`src/test-laptop.html`, 786 Bulgarian words. The source page's four symptom groups are kept intact and
each now closes with a link into the service that fixes that class of fault:

| Group | Routes to | Resolved how |
|---|---|---|
| Не се включва или не дава признаци на живот | kat-4 · Заливане и ремонт на дънни платки | `torin_category_href()` → published → `zalivane-technosti.html` |
| Включва се, но не стига до Windows | kat-3 · Оптимизация | `torin_category_href()` → published → `optimizatsiq.html` |
| Звук, WiFi или USB не работят | kat-2 · Екран, клавиатура и портове | `torin_category_href()` → **unpublished** → `index.html#kat-2` |
| Батерията или зарядното | Регенерация на батерии | `za-bateriite.html` (not a category; no gate to route through) |

**Not one category filename is hand-typed on the page**, and «Свързани услуги» is built by *iterating*
`$torin_categories`, so both the labels and the hrefs come from the data file. Publishing kat-2 or
kat-5 later is a boolean flip in `categories.php` with zero edits here — a hand-typed filename would
have 404'd on exactly the day someone published the category, the one day nobody would be looking for
a routing bug.

The deprecated instruction is gone: the live environment boots from a **USB stick**, not a burned
optical disc. Everything else — the memory test, the temperature monitor, the 75-80 degree stop rule
— is the shop's own copy, carried over. No `warranty_key` is set, so the renderer emits no warranty
section; a warranty block under a page of diagnostic steps is noise.

**Task 3 — «Профилактика на лаптоп»** (`24939c9`)

`src/profilaktika-laptop.html`, 926 Bulgarian words, doing two jobs: it is the one profilaktika page
categories 3 and 5 cross-list into (D-28), and it is DIFF-03's evidence page.

Every technical claim is quoted at the strength the shop's own copy states it. The infrared source is
described as being outside the visible spectrum and the manufacturer's answer is reported as given —
*that is the German maker's know-how* — with the page saying explicitly that it does not claim more
because it was not told more. The 90 percent figure is attributed as the shop's own accumulated
result **and the copy says in the same breath that it is not a guarantee**, which is why the warranty
section below it carries a term rather than a percentage. No certification and no exclusivity is
claimed anywhere.

The source page's working-hours sentence is **not ported**. It states hours that disagree with the
value the rest of the tree renders, and hours are a footer fact with exactly one writer — the `hours`
key in `includes/site-config.php`, read by the footer and by `jsonld.php`. A second copy here would be
a second writer, which is what produced the disagreement in the first place. The omission is recorded
in a source comment naming the key that owns the fact, without restating the hours (which would trip
this task's own gate).

## Word counts and evidence photographs

| Page | Bulgarian words in the data array | Gate reading | Evidence photographs |
|---|---:|---:|---|
| `src/za-bateriite.html` | **883** | 1340 | `baterii.jpg` (370×250) |
| `src/test-laptop.html` | **786** | 968 | none — not a service page |
| `src/profilaktika-laptop.html` | **926** | 1261 | `profilaktika17.jpg`, `profilaktika14.jpg`, `profilaktika15.jpg` (200×200 each) |

All three sit inside the D3-14 600–1000 band. The two columns differ because the plan's word gate
(`sed … | tr -cs …| wc -w`) counts the English rationale comments inside the array range as well; the
first column is Cyrillic tokens only, with `//` lines excluded, and is the number the DoD is about.

**Captions shipped** (each says what the photograph *proves*):

- `Стопен корпус на батериен пакет с нискокачествени клетки`
- `Инфрачервена станция за реболинг на BGA чипове` (UI-SPEC §Copywriting example, mandated)
- `Контактни падове на чип, увредени при неравномерно нагряване`
- `Повреда от нагряване с пистолет за горещ въздух` (UI-SPEC §Copywriting example, mandated)

## Terminology check — «екран» vs «матрица»

The locked owner decision landed after Tasks 1–3 were committed and **was applied**, not skipped.

| Page | Uses of `матриц-` before | After | Action |
|---|---:|---:|---|
| `src/za-bateriite.html` | 0 | 0 | nothing to change — checked, not missed |
| `src/profilaktika-laptop.html` | 0 | 0 | nothing to change — checked, not missed |
| `src/test-laptop.html` | 3 | 1 | two body-prose uses now read «екран» |

No title, `<h1>`, nav label or meta description on any of the three pages used the term at all, so
none needed amending. The one retained use is «лампата или инверторът на матрицата» — the lamp and
the inverter belong to the LCD panel *as a part*, which is precisely the case the decision keeps. The
step above it now reads «повредата е в екрана — в самата матрица или в кабела към нея», which
introduces the synonym exactly once so both terms remain present for search while «екран» carries the
reading. The rationale is written into the page's head comment, flagged as a deliberate reversal of
the live site's habit, so a later reader does not normalise it back.

## Deviations from Plan

**1. [Rule 1 — Bug] The plan's PHP 5.2 short-array gate regex is broken (inherited, third occurrence)**

- **Issue:** `grep -rnE '(=>[^;]*\]|\[\s*[^]]*=>)'` matches any line that *reads* an array value to
  the right of `=>`, so it hits untouched, valid PHP 5.2 and can never pass. Found by 03-01, hit again
  by 03-02, and flagged in this executor's own brief.
- **Fix:** used `grep -rnE '(=>|=)[[:space:]]*\[|return[[:space:]]+\['`, which tests the same stated
  intent — no `[]` literals — and returns nothing on all three files.
- **Files:** none changed; verification method corrected.

**2. [Rule 2 — Missing critical functionality] The battery evidence strip cannot use the two diagrams the plan named**

- **Found during:** Task 1, before writing the array.
- **Issue:** the plan specifies `'evidence'` = `baterry.jpg` + `baterry2.jpg` "with captions describing
  what each diagram shows". Inspected directly: `baterry.jpg` is a 554×357 **labelled block diagram**
  of a pack's protection electronics (SMBus, LDO, I2C, fuse, all captioned in Bulgarian) and
  `baterry2.jpg` is a 528×353 Li-ion cell cutaway. `torin_render_evidence()` renders every figure into
  a fixed **100×100** box under `object-fit: cover`, so both would ship as an unreadable smear.
  UI-SPEC §4 requires a caption describing what the figure **proves** and forbids decorative use; a
  cropped corner of a block diagram satisfies neither, on the one surface whose whole job is to look
  like proof. Plan 03-02 reached the identical conclusion (its deviation 7) and left both files ported
  but unused "for a future battery page that can show them at a legible size".
- **Why not simply show them larger:** that needs a full-width figure component — new CSS and a new
  renderer key — and this plan's `files_modified` is three `.html` pages. Adding it here would also
  collide with three sibling agents running concurrently.
- **Fix:** the strip carries `baterii.jpg` (370×250), a real first-party photograph of a battery pack
  with a **visibly melted case** on the bench next to a lab supply. Caption: `Стопен корпус на
  батериен пакет с нискокачествени клетки` — which is exactly what the page's positioning argues, that
  the shop stopped importing new packs over cell quality. One photograph is a specified state for the
  strip, not a degraded one (UI-SPEC §4 zero-one-many: content-sized columns, no stretched orphan cell).
- **Consequence for the plan's probe table:** it predicted the strip would be exercised "at two and
  three photographs". Actual coverage this plan adds is **one and three**. Two remains uncovered by
  this plan; the homepage DIFF-02 strip is also one.

**3. [Rule 1 — Bug] Two of the three photographs the plan named for DIFF-03 do not show what the plan implies**

- **Found during:** Task 3, inspecting each file rather than trusting its name.
- **Issue:** the plan asks for "the reflow station, a hot-air-gun-damaged board, and a melted-plastic
  example". `profilaktika15.jpg` is the melted-plastic shot and `profilaktika17.jpg` is the machine —
  both fine. There is no third file that adds anything: `profilaktika16.jpg` is a whole nVidia GPU with
  residue around it, which at 100×100 reads as "a circuit board" and proves nothing to a customer.
- **Fix:** used `profilaktika14.jpg`, which the legacy page introduces at :373 as *"реално увеличение
  на маркираната с червено част"* — an extreme magnification of four contact pads, two visibly torn. It
  is the only photograph in the set that actually shows the damage the callout describes, and the
  callout text now points the reader at it. Both mandated captions still ship, truthfully.

**4. [Rule 2] `'related'` on the profilaktika page reads category names from the records**

- **Issue:** first draft hand-typed «Оптимизация» and «Прегряване и охлаждане» as link labels while
  correctly resolving the hrefs through `torin_category_href()`. A D-40 rename would have stranded the
  labels — the exact defect `categories.php` exists to prevent, half-fixed.
- **Fix:** both entries now take `['name']` from the record they route to.

**5. [Locked owner decision, post-hoc] «екран» over «матрица»** (`7406312`)

Applied to `src/test-laptop.html`; the other two pages never used the term. Full detail in the
Terminology check section above.

## Live and rendered verification — NOT RUN

`scripts/deploy-new.sh` is denied to executor agents by the environment permission classifier. It was
attempted **once**, with the `TORIN_CRED_FILE` override, and refused. Nothing was deployed, so every
live assertion in this plan is recorded as **NOT RUN — never as passing**. This is the discipline
Phase 2's `abd5ba8` revert established and that both prior plans in this phase followed.

**There is no local PHP interpreter** (`command -v php` → not found), so deploying and fetching the
URL is the only PHP-syntax check available. In its place, every file was checked by a string-aware
scanner that tracks single-quoted strings and `//` comments and reports parenthesis depth at EOF:
all three return **depth 0, not inside a string**.

### Local gates — RUN AND PASSED

| Gate | za-bateriite | test-laptop | profilaktika |
|---|---|---|---|
| Renders through `torin_render_service_page()` | ✅ | ✅ | ✅ |
| Word count ≥ 600 (plan gate / Cyrillic-only) | ✅ 1340 / 883 | ✅ 968 / 786 | ✅ 1261 / 926 |
| Single-quote balance even | ✅ 214 | ✅ 198 | ✅ 254 |
| Paren depth 0, not in string at EOF | ✅ | ✅ | ✅ |
| PHP 5.2 dialect — no `[]` literals (corrected regex) | ✅ | ✅ | ✅ |
| No `__DIR__` | ✅ | ✅ | ✅ |
| No legacy markup / inline handlers / `javascript:` | ✅ | ✅ | ✅ |
| Stub sentinel sentence gone | ✅ | ✅ | ✅ |
| No second copy of hours or a phone number | ✅ | ✅ | ✅ |
| `'warranty_key'` | ✅ `battery`, no `'warranty' =>` literal, no `'cat_id'` | ✅ absent by design | ✅ `default` |
| `HILUMIN` / `Panasonic` / `ASSUMED` / `D3-12` present | ✅ 3 / 2 / 1 / 1 | — | — |
| `AMTECH` present | — | — | ✅ 2 |
| `torin_category_href` used | — | ✅ 6 | ✅ 2 |
| No hand-typed category filename | — | ✅ | — |
| No optical-media instruction, USB present | — | ✅ (6 USB) | — |
| Named photographs exist on disk | ✅ | — | ✅ 3 |
| `grep -rni 'smartbattery' src/` → no match | ✅ repo-wide | ✅ | ✅ |
| No sentence > 90 chars shared verbatim with `src/index.html` | — | — | ✅ (22 candidates checked, 0 duplicates) |

Pre-deploy baseline recorded as a negative control: `https://torin.bg/new/za-bateriite.html` currently
returns **200 / 8,597 B** — the old stub. A post-deploy reading materially larger than that is the
positive control proving the origin is serving this plan's work, the same way 03-01's
`sectionCount: 3 → 8` was.

### The exact deploy command, including the photo

`torin_render_evidence()` **skips any photograph whose file is absent on the server** and emits
nothing at all if none survive (`category-page.php:265`). Of the four photographs these pages
reference, three were already uploaded by plan 03-02 — **`profilaktika14.jpg` was not**, so omitting it
would silently ship a two-photo strip and fail the three-`img/repairs/` assertion for reasons that
look like a template bug:

```
TORIN_CRED_FILE=/Users/alabala/Documents/projects/torin/filezilla-server-data.xml \
  scripts/deploy-new.sh \
    za-bateriite.html \
    test-laptop.html \
    profilaktika-laptop.html \
    img/repairs/profilaktika14.jpg
```

No PHP include, stylesheet or config file changed in this plan, so page order does not matter here —
the P-10 "includes first" rule has nothing to sequence.

### Checks to run after that deploy

```bash
# all three serve, one h1, zero empty headings
for u in za-bateriite test-laptop profilaktika-laptop; do
  curl -sf -o /dev/null -w "$u %{http_code}\n" "https://torin.bg/new/$u.html"
done

# the keyed warranty set genuinely resolves per page
curl -s https://torin.bg/new/za-bateriite.html | grep -c '1 година гаранция на регенерирана батерия'   # 1
curl -s https://torin.bg/new/za-bateriite.html | grep -c '1 месец гаранция на всеки ремонт'            # 0
curl -s https://torin.bg/new/zalivane-technosti.html | grep -c '1 месец гаранция на всеки ремонт'      # 1
curl -s https://torin.bg/new/profilaktika-laptop.html | grep -c '1 месец гаранция на всеки ремонт'     # 1

# the dead domain has no route in; the strips populated
curl -s https://torin.bg/new/za-bateriite.html | grep -ci 'smartbattery'                                # 0
curl -s https://torin.bg/new/za-bateriite.html | grep -c 'class="evidence"'                             # 1
curl -s https://torin.bg/new/profilaktika-laptop.html | grep -oc 'img/repairs/'                          # 3
curl -s https://torin.bg/new/profilaktika-laptop.html | grep -c 'Инфрачервена станция за реболинг на BGA чипове'  # 1

# no warranty block on the self-diagnostic; four symptom groups; every link resolves
curl -s https://torin.bg/new/test-laptop.html | grep -c 'svc__warranty'                                  # 0
curl -s https://torin.bg/new/test-laptop.html | grep -c 'svc__block'                                     # >= 4
for h in $(curl -s https://torin.bg/new/test-laptop.html | grep -oE 'href="[a-z0-9./#-]+\.html[^"]*"' \
           | sed 's/href="//;s/"//' | sort -u); do
  curl -sf -o /dev/null -w "$h %{http_code}\n" "https://torin.bg/new/${h%%#*}"
done
```

Then the rendered probes at 360×640 and 1440×900:
`scripts/render-check.sh scripts/probes/svc-page.js https://torin.bg/new/profilaktika-laptop.html 360 640`
and the same against the other two. Note the 03-02 finding that still applies: evidence images are
`loading="lazy"` and below the fold, so `trust-signals.js` must scroll each strip into view and await
load before measuring, or it reports `naturalW: 0` and correctly degrades to INCONCLUSIVE.

## For the owner-question batch

**OWNER-QUESTIONS #23 — the battery-versus-service warranty distinction is now stated on the site and
needs one sentence of confirmation.**

The site has carried both terms for years without ever saying why they differ. `za-bateriite.html`
now says, in the shop's voice, that a repair is a service on hardware the shop did not build while a
regenerated pack is a product the shop does build, and that this is why the second term is longer.
Both numbers still render from the single keyed set in `site-config.php`, so they cannot drift apart,
and the `[ASSUMED]` marker stays in source until #23 is answered. **If the assumption is wrong, the
site is currently stating a rationale the shop does not stand behind** — one sentence from the owner
either retires the marker or changes the copy.

Two further things this plan surfaced, worth putting in the same batch:

- Everything ported here is the shop's own published claim and **none of it is independently
  verified by this project** — the temperature delta, the 90 percent rate, the Texas Instruments cell
  certification, the Panasonic sourcing and the equipment description. Nothing was amplified; all of
  it is roughly 2019-vintage copy and should be confirmed as still true before launch.
- The «Гаранция» section's `href` points at `warrently.html`, which still carries the legacy 5–6
  hours/day, 150–200 test-hours condition that D3-10 deliberately reframed in the shared config. The
  reframed summary and the page it links to therefore read differently. Out of scope here — flagged for
  whichever plan owns `warrently.html`.

## Known Stubs

None on the three pages. Two absences are deliberate and gated, not stubs:

- `'prices'` is unset on both service pages by design (D3-06, PRICE-01 is v2).
- `test-laptop.html` sets no `warranty_key`, so the renderer emits no warranty section at all.

## Deferred / out-of-scope findings

Recorded here rather than fixed, because each lies outside this plan's three files and three sibling
agents were editing concurrently.

1. **`baterry.jpg` and `baterry2.jpg` are still ported but unused.** They are genuinely good
   explanatory diagrams and the battery page is the right home for them — but they need a full-width
   figure component (new CSS + a new `$page` key) that no plan has yet written. Deviation 2 above.
2. **The homepage's caption for `baterii.jpg` looks inaccurate.** `src/index.html` captions it
   `Батериен пакет, отворен за подмяна на елементите`; the photograph actually shows a pack with a
   **melted case** beside a lab supply, not one opened for cell replacement. `index.html` is outside
   this plan's `files_modified`. One-line fix for whoever owns the homepage next.
3. **No `Service` or `FAQPage` structured data is emitted anywhere.** D3-14's DoD lists
   "Schema: Service + FAQPage + BreadcrumbList"; `jsonld.php` emits the LocalBusiness type and
   BreadcrumbList only. This is a pre-existing gap — `zalivane-technosti.html` from 03-01 has it too —
   and closing it means editing a shared include, which a parallel-wave agent must not do.

## Threat Flags

None. No new network surface, no new secret, no third-party artefact, no package installed. The
threat register's five mitigations for this plan all hold:

| Threat | Status |
|---|---|
| T-03-17 — legacy markup carried across with the copy | mitigated: strings ported into PHP arrays, every leaf escaped by the renderer; the no-legacy-markup gate passes on all three files |
| T-03-18 — capability and figure claims | mitigated: every figure quoted at source strength; the 90 percent rate explicitly framed as experience, not guarantee; verbatim-duplication check passes so no claim exists in two wordings |
| T-03-19 — a dead external domain re-entering the tree | mitigated: `grep -rni 'smartbattery' src/` returns nothing repo-wide, and the removal rationale cites D3-12 rather than the domain |
| T-03-20 — contradictory warranty terms | mitigated: both terms render from one keyed set; no page authors a literal; the cross-page live assertion is written above and awaits deploy |
| T-03-21 — duplicated contact facts drifting | mitigated: no hours and no phone number on any of the three pages |

## Self-Check: PASSED

- `src/za-bateriite.html` — FOUND (184 lines)
- `src/test-laptop.html` — FOUND (185 lines)
- `src/profilaktika-laptop.html` — FOUND (182 lines)
- `d9f7c37` `11c9438` `24939c9` `7406312` — all FOUND in git log
- `src/img/repairs/baterii.jpg`, `profilaktika17.jpg`, `profilaktika14.jpg`, `profilaktika15.jpg` — all FOUND on disk
