---
phase: 03-content-trust-signal-build-out
plan: 09
subsystem: seo-metadata
tags: [seo, metadata, gate, encoding, security]
status: complete
requires:
  - "03-01..03-08 page metadata (all 22 authored pages on disk)"
  - "src/includes/header.php per-page metadata mechanism (02-RESEARCH N-5)"
  - "src/includes/site-config.php base_url"
provides:
  - "scripts/seo-metadata-check.js — reusable site-wide SEO-01 gate, source and live modes"
  - "Homepage keyword-first title and description (index.html)"
  - "Literal title on ekran-klaviatura-portove.html (last derived title removed)"
  - "23-row finished metadata baseline for a Phase 4 crawl to compare against"
affects:
  - "All 23 served pages' <title> and <meta name=description>"
tech-stack:
  added: []
  patterns:
    - "Dependency-free Node gate, scripts/*-check.* convention, exit 0/1"
    - "Length measured in Unicode code points on an NFC-normalised string, never octets"
    - "Gate reads header.php's fallback literals at runtime rather than copying them"
key-files:
  created:
    - scripts/seo-metadata-check.js
  modified:
    - src/index.html
    - src/ekran-klaviatura-portove.html
    - src/about.html
    - src/problem-stari.html
    - src/remont-na-portove.html
    - src/rezervni-chasti.html
    - src/smyana-na-buksa.html
    - src/smyana-na-ekran.html
    - src/smyana-na-klaviatura.html
    - src/smyana-na-panti.html
    - src/tokov-udar.html
  deleted:
    - src/phptest.html
decisions:
  - "Length is code points on an NFC-normalised string; nfc and bmp-only prove code points, graphemes and UTF-16 units coincide for this content rather than assuming it"
  - "NO_CTA_EXPECTED added as a fourth named list so uslovia.html is not forced to grow a sales call inside a privacy declaration"
  - "src/phptest.html deleted rather than excluded from the gate — removing it makes 'every src/*.html' and 'every reachable page' the same set"
  - "ekran-klaviatura-portove.html title converted from derived to literal; rename safety retained via the title-matches-page rule"
  - "rel=canonical deliberately deferred to Phase 4 cutover"
metrics:
  duration: ~35 min
  completed: 2026-08-26
  tasks: 3
  commits: 3
actuals:
  tokens: 78000
  tasks: 3
  commits: 3
---

# Phase 03 Plan 09: Site-Wide SEO-01 Closure Summary

Built a dependency-free 18-rule Node gate that measures title/description quality in Unicode
code points across every served page, then closed the 11 real defects it found — including the
homepage, the one page no earlier plan claimed.

## What Was Built

`scripts/seo-metadata-check.js` (479 lines) checks all 23 served pages against 18 named rules in
source mode, and additionally against the served response in `--live` mode. Source mode now exits
0 on the whole tree.

The gate is a script rather than a grep for one reason: all 23 titles and descriptions are
Cyrillic, and UTF-8 encodes Cyrillic at two octets per character. A shell length check reads a
correct 129-character description as 230 and rejects it — wrong by ~1.8x, in the direction that
throws out good copy and admits copy that is genuinely too long.

## The Finished Metadata Set

The Phase 4 crawl baseline. `CTA at` is the code-point index where the call to action begins;
budget is under 95 (mobile truncation).

| Page | Title | Title cp | Desc cp | CTA at |
|---|---|---:|---:|---:|
| `about.html` | За ТОРИН КОМПЮТЪРС — сервиз в София от 1993 г. · Торин | 54 | 132 | 44 |
| `covid.html` | Проект BG16RFOP002-2.073 · Торин Компютърс | 42 | 131 | n/a |
| `ekran-klaviatura-portove.html` | Екран, клавиатура и портове на лаптоп · Торин | 45 | 139 | 70 |
| `index.html` | Ремонт на лаптопи и компютри в София · Торин | 44 | 129 | 48 |
| `laptopi.html` | Употребявани лаптопи · Торин | 28 | 134 | 83 |
| `mehanichni-problemi.html` | Ремонт на счупен лаптоп София · Торин | 37 | 121 | 76 |
| `msg.html` | Съобщението е изпратено · Торин | 31 | 87 | n/a |
| `optimizatsiq.html` | Оптимизация и ускоряване на лаптоп · Торин | 42 | 120 | 75 |
| `pregryavane-ohlazhdane.html` | Прегряване на лаптоп и профилактика · Торин | 43 | 122 | 86 |
| `problem-stari.html` | Ремонт на стара и нестандартна техника · Торин | 46 | 136 | 48 |
| `profilaktika-laptop.html` | Профилактика на лаптоп и реболинг на чипове · Торин | 51 | 129 | 33 |
| `remont-na-portove.html` | Ремонт на USB и HDMI портове на лаптоп · Торин | 46 | 127 | 63 |
| `rezervni-chasti.html` | Резервни части за лаптопи · Торин | 33 | 130 | 45 |
| `smyana-na-buksa.html` | Смяна на захранваща букса на лаптоп · Торин | 43 | 136 | 45 |
| `smyana-na-ekran.html` | Смяна на екран на лаптоп София · Торин | 38 | 137 | 41 |
| `smyana-na-klaviatura.html` | Смяна на клавиатура на лаптоп София · Торин | 43 | 137 | 48 |
| `smyana-na-panti.html` | Смяна на панти на лаптоп София · Торин | 38 | 136 | 34 |
| `test-laptop.html` | Тествай сам лаптопа си — диагностика · Торин | 44 | 126 | 74 |
| `tokov-udar.html` | Ремонт след токов удар и повреден адаптер · Торин | 49 | 137 | 69 |
| `uslovia.html` | Общи условия и поверителност · Торин | 36 | 136 | n/a |
| `warrently.html` | Гаранционни условия · Торин Компютърс | 37 | 138 | n/a |
| `za-bateriite.html` | Регенерация на батерии за лаптоп · Торин | 40 | 137 | 43 |
| `zalivane-technosti.html` | Ремонт на залят лаптоп и дънна платка · Торин | 45 | 124 | 71 |

All 23 titles are 28–54 code points (budget 55). All 23 descriptions are 120–139 except
`msg.html` at 87, which is the one documented floor exemption.

## The Four SEO-01 Edges — Discharged

| Edge | Rules that discharge it | What they assert |
|---|---|---|
| **adjacency** | `unique-title`, `unique-desc`, `distinct-lead`, `distinct-desc-head` | Titles and descriptions pairwise distinct after NFC + lowercase + whitespace collapse, over the full 23-page set. `distinct-lead` is the one that matters for the five category-2 siblings: five pages separated only by a suffix would each be string-unique and mutually interchangeable in a result list. `distinct-desc-head` requires the first 60 normalised code points to differ. Additionally proven against the server by direct comparison (23/23 distinct titles, 23/23 distinct descriptions). |
| **empty** | `assigned-before-include`, `nonempty`, `not-fallback` | On this site the meaningful empty state is not a blank string but silent inheritance of the site-level default. `not-fallback` reads header.php's two fallback literals **at runtime**, so the rule cannot drift from the mechanism it guards. This caught a real case: the served homepage title was byte-identical to the fallback default, so index.html was indistinguishable from a page that assigned nothing at all. |
| **encoding** | `nfc`, `bmp-only`, `no-mixed-script`, plus the code-point basis of `title-len`, `desc-len`, `cta-position` | Length is code points on an NFC-normalised string. `nfc` (no combining marks) and `bmp-only` (code-point count equals UTF-16 unit count) together *prove* code points, grapheme clusters and UTF-16 units are the same number for this content rather than assuming it. `no-mixed-script` rejects any maximal letter-run containing both a Cyrillic and a Latin letter — the homoglyph case that passes byte equality, code-point equality and human proofreading simultaneously. Whole-Latin tokens (`USB`, `HDMI`, `BG16RFOP002`) are unaffected. No octet-length expression exists anywhere in the checker; asserted in Task 1 and again in Task 3. |
| **ordering** | `keyword-first`, `suffix`, `no-legacy-suffix`, `title-matches-page` | Structural: the brand may occupy only the trailing separator-delimited segment and may never open the title. Semantic: `title-matches-page` requires the title's leading segment to share a 5+ letter token with that page's own `<h1>`, resolved from the `'h1'` data key or a literal `<h1>` element, and **failing rather than passing** when unresolvable. |

None is recorded as partially covered or deferred.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] `assigned-before-include` matched a comment instead of the include statement**

- **Found during:** Task 1, first gate run
- **Issue:** `includeLine()` matched any line containing `header.php`. Three pages
  (`smyana-na-panti`, `smyana-na-buksa`, `remont-na-portove`) carry a head comment naming
  `header.php` while documenting the `$torin_page` global-scope defect. The gate read that comment
  as the include, placed it above the metadata assignments, and failed three **correct** pages.
- **Fix:** Match the actual `require`/`include` statement, not any mention of the filename.
- **Commit:** c4095bc

This is the same class as the recorded 03-07 defect (a comment quoting a gated string). It is
worth noting the direction: here the false match failed correct pages rather than passing broken
ones, so it was loud. The reverse would have been silent.

### Deliberate Deviations

**2. `NO_CTA_EXPECTED` added as a fourth named exemption list**

The plan defined the `cta-position` skip set by reference — "the ones in `EXEMPTIONS`, plus the two
documents in `LONG_SUFFIX_OK`" — which conflates two unrelated properties: *being a non-commercial
document* and *being allowed the long brand suffix*. `uslovia.html` (the privacy declaration) is the
first and not the second, so under the plan's wording it would have had to grow a call to action
inside a privacy policy. That is precisely the "writing for a gate rather than for a reader" the
plan forbids elsewhere, and a privacy policy soliciting phone calls also mis-serves the person who
searched for it.

Resolved with a **separate named list**, not by widening `LONG_SUFFIX_OK` — widening that list would
have changed uslovia's *rendered suffix* to satisfy an unrelated rule. The three lists the plan
specified print back byte-identically (`exemptions: msg.html`, `long-suffix: covid.html
warrently.html`, `brand-is-subject: about.html`), so the anti-widening gate still holds; the new list
prints on its own line with its own rationale.

**3. Task 2's ">6 changed lines per file" bound replaced with a stronger structural assertion**

`ekran-klaviatura-portove.html` shows 19 changed lines. Converting its title from derived to literal
necessarily invalidates the seven-line comment that argued *for* deriving it — leaving that comment
would have been a source comment that lies about the code beneath it. There is no way to both fix the
stale comment and stay under six lines.

Replaced with a strictly stronger check that measures the plan's actual intent (T-03-49: metadata
edits must not reach body copy): **every added or removed line across `src/` must be either a PHP
line comment or a `$torin_title`/`$torin_desc` assignment.** A line count cannot distinguish a
comment rewrite from a body-copy edit; this can. It passes — no body copy, no markup and no page
data array was touched.

### Defective Gate Commands in the Plan (corrected, not worked around)

Three of the plan's own verify commands are wrong. Each was corrected and re-run; **none masked a
real defect**, but two would have produced a false failure and one a false pass.

| Plan command | Result | Reality |
|---|---|---|
| `grep -c 'if (!isset($torin_title))' src/includes/header.php` (expects 1) | returns **0** | BSD/macOS grep mishandles the `$` in the BRE. Both fallback guards **are** present at header.php:58 and :61 — confirmed with `grep -cF`, which returns 1 for each. Taking the plan's command at face value would have concluded the fallback had been deleted. |
| `grep -rniE 'лв\.\|лева\|евро\|EUR\|работни дни\|гарантираме, че' src/index.html` (expects no match) | **matches** index.html:201 | `EUR` matches case-insensitively inside the English word "amat**eur**" in a pre-existing comment. A word-boundary version returns no match. No price, turnaround or guarantee claim exists on the homepage. |
| `node -e '...(s.match(/'/g)\|\|[]).length%2'` — odd ASCII single-quote count fails | **fails on the untouched tree** | index.html had **169** quotes at HEAD, already odd. All nine unpaired apostrophes are English possessives inside `//` comments — harmless in PHP. The check can never pass. I still avoided adding any: the file is back to exactly 169, unchanged from HEAD. |

## Security

**T-03-43 (information disclosure) — mitigated.** `src/phptest.html` deleted. It executed
`phpversion()` and printed the host's PHP version to any visitor, and carried no metadata of its own.
`scripts/deploy-new.sh` with no arguments uploads every file under `src/`, so its presence was a
latent deploy rather than a harmless leftover. The handler-probe method it documented survives in
`01-01-SUMMARY.md`. Both `https://torin.bg/new/phptest.html` and `https://torin.bg/phptest.html`
confirmed **404** live. Deleting rather than exempting also means «every file matching `src/*.html`»
and «every page a visitor can reach» are now the same set, so the gate needs no file exception list.

**T-03-48 — mitigated.** The checker touches only public URLs; it opens no credential file and
passes no authentication flag to curl. Asserted by grep.

**T-03-50 (staging noindex) — verified intact.** `src/.htaccess` was not modified by this plan (empty
diff), and the `X-Robots-Tag: noindex, nofollow` header was confirmed present on **all 23 served
URLs**. Stripping it remains a Phase 4 cutover task
(`.planning/todos/pending/strip-staging-noindex-at-cutover.md`).

## Verification

### What the gate measures: SOURCE, not served

`node scripts/seo-metadata-check.js` (default mode) measures **the repository**, by parsing the
single-quoted PHP literals out of `src/*.html`. There is no local PHP interpreter on this machine, so
source mode cannot see what the server actually emits. `--live` mode closes that gap by fetching each
page and asserting the served value equals its source literal after HTML decoding.

### Run and passing (source)

- `node scripts/seo-metadata-check.js` → **exit 0**, 23 pages checked, 0 failing.
- Every changed line in `src/` is a comment or a metadata assignment.
- No title carries the all-caps brand suffix; `grep -rn ' · ТОРИН КОМПЮТЪРС' src/*.html` → no match.
- The stub sentinel appears **nowhere** under `src/`, in any file type — the tree-wide assertion
  plans 03-04, 03-05 and 03-06 each deferred because their siblings were running in parallel.
- header.php's two fallback guards intact (verified with `grep -cF`).
- Homepage: title 44 cp, identical to its own `<h1>`; description 129 cp, call at code point 48.

### Run and passing (live, read-only, against the CURRENTLY served tree)

All 23 URLs: HTTP 200, exactly one `<h1>`, non-empty title, non-empty description, zero empty
`<h2>`/`<h3>`, no stub sentinel, `X-Robots-Tag: noindex` present. Served titles 23/23 distinct and
served descriptions 23/23 distinct, proven by direct comparison independently of the checker. Both
`phptest.html` URLs 404.

### NOT RUN — blocked on deploy

**`node scripts/seo-metadata-check.js --live` does NOT pass, and this plan does not claim it does.**

`scripts/deploy-new.sh` was unavailable in this execution context, and `filezilla-server-data.xml` is
not present in the worktree. The 11 pages tuned in Task 2 therefore still serve their pre-plan
metadata.

Live mode was run anyway, for evidence rather than for a pass. It reports **exactly the 11 pages this
plan changed and no others**, each failing `served-matches-source` plus the specific old defect the
tuning fixed. That is the expected signature of a correct gate against an undeployed tree.

One genuinely useful result fell out of it: `ekran-klaviatura-portove.html`'s served **title** already
matches its new source literal (only its description differs), which confirms against the real server
that converting that title from a derived expression to a literal was value-preserving.

**To complete verification, deploy and re-run:**

```
scripts/deploy-new.sh index.html ekran-klaviatura-portove.html about.html \
  problem-stari.html remont-na-portove.html rezervni-chasti.html \
  smyana-na-buksa.html smyana-na-ekran.html smyana-na-klaviatura.html \
  smyana-na-panti.html tokov-udar.html
node scripts/seo-metadata-check.js --live
```

Note `deploy-new.sh` with **no arguments uploads everything under `src/`** — pass the file list.

### Deferred human check

Open four pages on a phone — the homepage, the category-2 hub, one of its five children, and the
warranty page — and read each browser tab title against each page's own heading. Deferred to
end-of-phase per `human_verify_mode: end-of-phase`. Cannot be meaningful before deploy.

## Known Stubs

None introduced by this plan.

Pre-existing and out of scope: `src/includes/categories.php` and `src/includes/services.php` carry
`[ASSUMED] Placeholder customer phrasing pending OWNER-QUESTIONS #16` markers on the category and
service symptom lines. These are content-provenance markers on rendered copy, already tracked as an
open owner question, and are not touched by a metadata plan.

## What Phase 4 Inherits

1. **`$site['base_url']` is the single place the staging path segment lives** —
   `src/includes/site-config.php`, carrying its own cutover-gate comment. At cutover it becomes
   `https://torin.bg/` and that one edit is the whole change. `scripts/seo-metadata-check.js --live`
   builds every URL from it, so the gate follows the cutover with no edit of its own.
2. **`rel=canonical` was deliberately deferred, not forgotten.** RESEARCH recommends a
   self-referencing absolute canonical, but it must emit the production URL, and the staging path
   segment disappears at cutover. Adding it here would have put a `/new/`-dependent absolute URL on
   23 pages one phase before that path stops existing.
3. **The staging noindex header must be stripped at cutover** — verified uniformly applied on all 23
   URLs today, which is what makes that todo actionable rather than a guess.

## Self-Check: PASSED

- `scripts/seo-metadata-check.js` — FOUND
- `src/phptest.html` — CONFIRMED ABSENT (deleted)
- `.planning/phases/03-content-trust-signal-build-out/03-09-SUMMARY.md` — FOUND
- Commit c4095bc — FOUND
- Commit 9d0640e — FOUND
- `node scripts/seo-metadata-check.js` — exit 0, 23/23 pages clean
