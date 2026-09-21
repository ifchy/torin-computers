---
phase: 04-hardening-cutover
plan: 07
subsystem: analytics
tags: [umami, cookieless-analytics, tel-links, accessibility, robots-meta, dead-code-removal]

requires:
  - phase: 04-05
    provides: "#form-error band with tabindex=-1, the populated error re-render, and the inert $torin_robots assignment on msg.html"
  - phase: 04-06
    provides: "header.php and site-config.php in their current shape, including the settings.php merge"
  - phase: 04-04
    provides: "the five data-slot write anchors pointing at kontakti.html"
provides:
  - "src/js/analytics.js — the single permitted mechanism for every analytics event on the site"
  - "A passive, capturing, non-cancelling click listener, so a tel: anchor behaves exactly as it does with JavaScript disabled"
  - "A closed-set property guard enforced in code: slot and field names outside two allowlists are dropped"
  - "data-slot on all 7 phone slots and verification of the 5 write slots"
  - "The Umami tracker tag in header.php — once, deferred, no connection hint, no web-vitals attribute, suppressible by blanking one config value"
  - "$torin_robots — the per-page robots emitter, inert unless assigned; activates msg.html's noindex"
  - "$torin_track — the server-rendered event channel that carries form-sent and form-error"
  - "The C-8 error-band focus call, closing the accessibility half of ledger #41(a)"
  - "Deletion of css/theme-a.css and includes/dev-switcher.php from the source tree"
affects: [04-08 sitemap, 04-10 root cutover, 04-04 uslovia privacy disclosure]

actuals:
  tokens: 70557
  tasks: 3
  commits: 2

tech-stack:
  added: ["Umami Cloud (Hobby tier) — one deferred third-party script, no cookie, no consent banner"]
  patterns:
    - "Analytics is additive and never in an activation path: no default cancelled, nothing awaited, no instrumentation between a tap and the dialer"
    - "Event properties as a closed set enforced in code rather than promised in prose"
    - "Server-rendered events declared on <body> by the page, fired by the one client mechanism"
    - "Per-page head directives that emit nothing when unset, so existing pages stay byte-identical"

key-files:
  created: [src/js/analytics.js]
  modified:
    - src/includes/header.php
    - src/includes/site-config.php
    - src/index.html
    - src/includes/footer.php
    - src/includes/category-page.php
    - src/kontakti.html
    - src/msg.html
    - src/contact-send.php
    - src/css/base.css
    - src/css/components.css
    - src/includes/asset-version.php
    - src/includes/contact-form.php
  deleted: [src/css/theme-a.css, src/includes/dev-switcher.php]

key-decisions:
  - "Task 1 resolved by the owner as option-a (Umami Cloud Hobby tier). Commercial use permitted by OWNER CONFIRMATION, not by any public document."
  - "Free-tier limits corrected: 3 websites, not the 1 the plan stated. 100,000 events/month, 6 months retention."
  - "A seventh phone slot, `kontakti`, was added beyond UI-SPEC C-9's six-slot table, because 04-05 created that page after the table was written."
  - "analytics.js exceeds its 1 KB gzipped budget at 1,959 B. Code alone is 972 B; the comments are the entire overage. Reported as a failing gate, not resolved unilaterally."
  - "The C-8 focus call lives in analytics.js as instructed, despite a content-blocker risk attaching to that filename."

patterns-established:
  - "Closed-set enforcement: a value outside a hardcoded allowlist is dropped before it can reach a third party, turning a prose prohibition into a mechanical one"
  - "Gate-aware commenting: a tombstone comment must not name the string its own deletion gate greps for"

requirements-completed: [ANALYTICS-01, CONTACT-01]

coverage:
  - id: D1
    description: "No analytics mechanism sits in the activation path of a phone anchor; tapping a number opens the dialer exactly as with JS disabled"
    requirement: CONTACT-01
    verification:
      - kind: other
        ref: "grep -v '^[[:space:]]*//' src/js/analytics.js | grep -c 'preventDefault' -> 0"
        status: pass
      - kind: other
        ref: "grep -rc 'data-umami-event' src/ | awk sum -> 0, and each of the 9 tel:/mailto: anchor lines inspected individually"
        status: pass
      - kind: other
        ref: "grep -c 'passive' src/js/analytics.js -> 2 (click listener registered {capture:true, passive:true})"
        status: pass
      - kind: manual_procedural
        ref: "Real-handset tap test on torin.bg/new — NOT RUN (no deploy available to this executor)"
        status: unknown
    human_judgment: true
    rationale: "The plan itself mints this as a backstop truth: a desktop manual test passes this defect because a tel: link does nothing there either way. The static assertions prove no default-prevention is registered, which is the plan's own named substitute for a handset — but the handset test remains the honest confirmation and has not been run."
  - id: D2
    description: "All 7 phone slots and 5 write slots carry data-slot, so call and write are comparable surface by surface"
    requirement: ANALYTICS-01
    verification:
      - kind: other
        ref: "grep over src/: 7 distinct phone slots (hero, cta, callbar, category, kontakti, footer-list, footer-cta), 5 write slots (hero, cta, callbar, category, footer)"
        status: pass
      - kind: e2e
        ref: "curl counts on served output at homepage/category/service page"
        status: unknown
    human_judgment: false
  - id: D3
    description: "The contact funnel is observable end to end: form-start, form-photos, form-consent, form-submit, form-error, form-sent"
    requirement: ANALYTICS-01
    verification:
      - kind: other
        ref: "src/js/analytics.js handlers + $torin_track declared by src/msg.html and src/contact-send.php"
        status: pass
      - kind: e2e
        ref: "Live submission producing events in the Umami dashboard"
        status: unknown
    human_judgment: true
    rationale: "Whether the six events actually arrive, and read sensibly as a funnel, can only be judged in the dashboard after a real deploy and a real submission."
  - id: D4
    description: "Exactly one third-party origin loads, deferred, with no connection hint and no web-vitals attribute; the site functions fully with it blocked"
    requirement: ANALYTICS-01
    verification:
      - kind: other
        ref: "grep -c 'preconnect|dns-prefetch' src/includes/header.php -> 0; grep -c 'data-performance' -> 0"
        status: pass
      - kind: automated_ui
        ref: "Request-blocking render-harness run against the deployed homepage"
        status: unknown
    human_judgment: false
  - id: D5
    description: "The dev theme scaffolding is out of the source tree with zero references, and no shipping token changed colour"
    verification:
      - kind: other
        ref: "test ! -f on both paths; grep -rc 'dev-switcher|theme-a' src/ -> 0; grep -rc 'torin_extra_head' src/ -> 0"
        status: pass
      - kind: other
        ref: "git diff -U0 src/css/base.css | grep '^[-+]--' -> no token lines; --c-brand #ffc70a before and after"
        status: pass
      - kind: e2e
        ref: "404 on both remote paths after the manual cutover deletion pass"
        status: unknown
    human_judgment: false
  - id: D6
    description: "A page may declare a robots directive that renders only when set; msg.html gets its noindex and existing pages stay byte-identical"
    verification:
      - kind: other
        ref: "grep -c 'torin_robots' src/includes/header.php -> 4; only src/msg.html assigns it; emitter is an echo inside an if, so the unset case produces no output"
        status: pass
      - kind: e2e
        ref: "curl: 0 robots tags on the homepage, exactly 1 on msg.html"
        status: unknown
    human_judgment: false
  - id: D7
    description: "The C-8 error band is focused on render, so keyboard and screen-reader users land on the explanation"
    verification:
      - kind: other
        ref: "src/js/analytics.js — document.getElementById('form-error').focus()"
        status: pass
      - kind: manual_procedural
        ref: "Keyboard/screen-reader pass on a live validation failure"
        status: unknown
    human_judgment: true
    rationale: "Whether focus lands somewhere a screen reader announces usefully is a judgment about what the user hears, not about whether a method was called."

duration: 41min
completed: 2026-09-21
status: complete
---

# Phase 04 Plan 07: Analytics and Scaffolding Removal Summary

**The shop can now see which contact surfaces get used and which service pages get visited, through one passive listener that never touches the tap-to-dial path — and the development theme switcher that has been rendering on all 19 staging pages since Phase 2 is out of the tree.**

## Performance

- **Duration:** ~41 min
- **Tasks:** 3 of 3 (Task 1 was resolved by the owner before execution began)
- **Files modified:** 12 modified, 1 created, 2 deleted

## Accomplishments

- **The primary conversion action was protected, deliberately rather than incidentally.** `data-umami-event` appears zero times in `src/`, and that zero was verified by inspecting each of the nine `tel:`/`mailto:` anchor lines individually — not by trusting a tree-wide count that would read the same if the attribute simply never existed. `analytics.js` cancels no default and awaits nothing; its click listener is registered `{capture: true, passive: true}`.
- **Event properties are a closed set enforced in code.** Two hardcoded allowlists — eight slot names, seven field names — gate every property before it can be emitted. A value outside them is dropped. This converts the plan's `<prohibitions>` entry from a promise into a mechanism.
- **Ledger #39 is closed.** `header.php` now emits `$torin_robots`, so `msg.html`'s noindex — assigned but inert since 04-05 — finally renders. **04-08 can rely on this for the sitemap.**
- **Ledger #41(a) is closed.** The `#form-error` band is now focused on render.
- **Ledger #41(b) is closed.** `form-error` is emitted from the server re-render with its `field` property, and `form-sent` from `msg.html`, which closes the funnel at both ends.
- **The dev scaffolding is gone from the tree** with zero remaining references, and no shipping design token changed.

## Task Commits

1. **Task 1: Analytics tool — owner sign-off** — no commit; resolved by the owner on 2026-09-21 (see Decisions).
2. **Task 2: One passive listener, twelve labelled anchors, and a dialer nobody touched** — `e741954` (feat)
3. **Task 3: Delete the development scaffolding and add the robots mechanism** — `ec3595c` (chore)

## Task 1 resolution (recorded, not re-raised)

**Selected: option-a** — Umami Cloud, Hobby tier. Recorded in `site-config.php` as `umami_website_id` with full provenance.

| Item | Value | Provenance |
|---|---|---|
| Website id | `51b8d23c-9410-4a41-8e56-3a63a3835bb2` | Owner, 2026-09-21. Not a secret — it ships in the HTML of every page by construction. |
| Free-tier events | 100,000 / month | Re-verified 2026-09-21 per D4-19 |
| Free-tier websites | **3** | Re-verified 2026-09-21. **The plan says "one website" — that figure is out of date and this is the correction.** |
| Retention | 6 months | Re-verified 2026-09-21 per D4-19 |
| Commercial use | Permitted | **OWNER CONFIRMATION, not a public document** |

**On the commercial-use question, stated precisely because it is the one thing the plan said research could not settle.** There is no public prohibition *and* no public permission. `umami.is/pricing` and `umami.is/terms` are JavaScript-rendered and return only their titles to a fetcher; the Cloud FAQ says only that the tier is "great for personal projects and low traffic websites", which is marketing copy rather than a restriction clause. The permission on record is the owner's. A completed enquiry costs at most 6 events against a 100,000/month allowance, so the usage question is not close either way.

## Files Created/Modified

| File | What changed |
|---|---|
| `src/js/analytics.js` | **New.** The only mechanism permitted to fire an analytics event: one passive capturing click listener, four funnel handlers, two server-declared event readers, closed-set property guards, and the C-8 focus call. |
| `src/includes/header.php` | Tracker tag (once, deferred, no connection hint, no web-vitals attribute, suppressed when the id is blank); `analytics.js` via the asset-stamp helper; `$torin_track_attr` on `<body>`; the `$torin_robots` emitter; and the four dev-scaffolding deletions. |
| `src/includes/site-config.php` | `umami_website_id` with provenance, verified limits, the plan-figure correction and the owner-confirmation note. Blanking it removes the third party from all 20 pages. |
| `src/index.html` | `data-slot` on the hero, CTA and callbar phone anchors. |
| `src/includes/footer.php` | `data-slot` on the phones loop (`footer-list`, 3 rendered anchors) and the footer call button (`footer-cta`). |
| `src/includes/category-page.php` | `data-slot="category"` on the service-page call CTA. |
| `src/kontakti.html` | `data-slot="kontakti"` on the phones loop, with a comment explaining why this slot is absent from C-9's table. |
| `src/msg.html` | `$torin_track = 'form-sent'`, with its over-counting characterised honestly. |
| `src/contact-send.php` | `$torin_track` built from the same fixed field whitelist the in-page link list uses; the stale "`.focus()` belongs to a file this plan does not touch" comment corrected. |
| `src/css/components.css` | `.dev-switcher` rules deleted (32 lines). |
| `src/css/base.css` | Three comments referencing the deleted override reworded; **no token value touched**. |
| `src/includes/asset-version.php`, `src/includes/contact-form.php` | Stale references to the deleted partial and the deleted extra-head slot corrected. |

## Event property inventory (complete)

Every property name that can ever be emitted by this site. There are **two**.

| Event | Property | Permitted values (the complete closed set) |
|---|---|---|
| `call-click` | `slot` | `hero`, `cta`, `callbar`, `category`, `kontakti`, `footer-list`, `footer-cta` |
| `write-click` | `slot` | `hero`, `cta`, `callbar`, `category`, `footer` |
| `form-error` | `field` | `device`, `fault`, `photos`, `name`, `phone`, `email`, `consent` |
| `form-start`, `form-photos`, `form-consent`, `form-submit`, `form-sent` | *(none)* | — |

**None of these can carry a submitted value.** Slot names come from developer-authored markup; field *names* (never values, never the server's message) come from a fixed whitelist in `contact-send.php` and are independently re-checked against a second hardcoded list in `analytics.js` before emission. Two gates on one string.

**Not instrumented, deliberately:** the two `mailto:` anchors (`footer.php:63`, `kontakti.html:159`) carry no `data-slot` and fire nothing. C-9 defines "write" as the `kontakti.html` anchors; a mail-client handoff is also a custom-scheme navigation and is left entirely alone.

## Slot inventory

**7 phone slots / 5 write slots**, counted on source. Two slots are loops:

| Surface | Slot | Source lines | Rendered anchors |
|---|---|---|---|
| Hero / CTA / callbar | `hero`, `cta`, `callbar` | `index.html` ×3 | 3 (homepage) |
| Service-page CTA | `category` | `category-page.php` | 1 (per service page) |
| Contact page numbers | `kontakti` | `kontakti.html` | **3** (one loop) |
| Footer numbers | `footer-list` | `footer.php` | **3** (one loop) |
| Footer call button | `footer-cta` | `footer.php` | 1 (every page) |

Rendered per page: homepage **7** phone anchors (the figure C-9's table describes), a service page 5, the contact page 7.

## Decisions Made

1. **A seventh phone slot, `kontakti`, was added beyond UI-SPEC C-9's table.** C-9 enumerates six slots because it was written before 04-05 created `kontakti.html`. Leaving those three anchors unlabelled would have made calls from the highest-intent page on the site the one call surface nobody could count. Recorded in the markup rather than silently added.
2. **The tracker tag is conditional on a non-empty `umami_website_id`.** Blanking one config value removes the third party from all 20 pages with no other edit — an off switch that does not require re-reading this plan.
3. **`analytics.js` loads after the tracker, and that order is explicitly documented as *not* load-bearing.** Both are deferred, `analytics.js` re-reads `window.umami` at each call rather than capturing it, and a missing tracker is a silent no-op. Written down so a later reader does not harden a dependency that does not exist.
4. **The C-8 focus call lives in `analytics.js`**, as the carried-forward ledger instructed — see Known Gaps for the risk this accepts.

## Deviations from Plan

### Auto-fixed issues

**1. [Rule 3 — Blocking] The scaffolding deletion required six files, not the four the plan listed**

- **Found during:** Task 3
- **Issue:** The plan's own gates demand `grep -rc 'dev-switcher\|theme-a' src/` and `grep -rc 'torin_extra_head' src/` both sum to `0` tree-wide. References lived in four files outside the plan's list: `components.css` (the `.dev-switcher` rules), `base.css` (three comments), `asset-version.php` (one comment) and `contact-form.php` (one comment). Deleting only the listed files would have left the gates failing and, worse, left `.dev-switcher` CSS shipping on every page for an element that can no longer render.
- **Fix:** Deleted the CSS block; reworded the four stale comments so each explains what was removed instead of referring to it as live.
- **Verification:** Both summed counts are now `0`.
- **Committed in:** `ec3595c`

**2. [Rule 1 — Bug] Deleting `$torin_html_attr` would have left an undefined variable echoed into `<html>`**

- **Found during:** Task 3
- **Issue:** The plan names three dev-only blocks and the extra-head line. A fourth site existed: `<html lang="bg"<?php echo $torin_html_attr; ?>>`. Removing the assignment without removing the echo emits a PHP notice into the document on all 20 pages — which the plan's own "zero PHP warnings" gate would have caught in production rather than here.
- **Fix:** `<html lang="bg">`.
- **Committed in:** `ec3595c`

**3. [Rule 2 — Missing functionality] `kontakti.html`'s three phone anchors were uninstrumented**

- **Found during:** Task 2
- **Issue:** 04-05 added a phone list to the contact page after C-9's slot table was authored, so the site's highest-intent call surface would have emitted nothing.
- **Fix:** `data-slot="kontakti"`, added to the `analytics.js` allowlist, documented in the markup and in this SUMMARY.
- **Committed in:** `e741954`

**4. [Rule 2 — Missing functionality] `contact-send.php` was edited although it is not in the plan's file list**

- **Found during:** Task 2
- **Issue:** Ledger #41(b) assigns the `form-error` event to this plan, and that event can only be declared where the server knows which fields failed.
- **Fix:** `$torin_track` built from the same fixed field whitelist already used for the in-page error links.
- **Committed in:** `e741954`

**Total deviations:** 4 auto-fixed (1 × Rule 1, 2 × Rule 2, 1 × Rule 3). No scope creep — every one was required either by a gate the plan itself wrote or by a ledger item the orchestrator assigned.

## Verification

### Gates that passed

| Gate | Result |
|---|---|
| Non-comment `preventDefault` in `analytics.js` | **0** |
| `passive` registered on the click listener | **2 occurrences** |
| `data-umami-event` across `src/` | **0** — and each of the 9 `tel:`/`mailto:` anchor lines checked individually |
| Connection hints in `header.php` | **0** |
| Web-vitals attribute in `header.php` | **0** |
| Both scaffolding files absent | **yes** |
| `dev-switcher` / `theme-a` references across `src/` | **0** |
| `torin_extra_head` references across `src/` | **0** |
| `torin_html_attr` / `torin_theme` references across `src/` | **0** |
| `torin_robots` present in `header.php` and `msg.html` | **4 / 2** |
| Pages assigning `$torin_robots` | **only `msg.html`** (the default-unset proof, checked statically) |
| No design token changed | **no token line in the `base.css` diff**; `--c-brand` is `#ffc70a` before and after |
| `node --check src/js/analytics.js` | **pass** |

### Gates that FAILED

**`gzip -c src/js/analytics.js | wc -c` is 1,959 B against a ≤1,024 B budget (UI-SPEC §Performance Budget). This gate does not pass and is not being reported as passing.**

The measurement that matters for the decision: **the code alone gzips to 972 B — inside budget. The comments are the entire overage.** This project has no build step; `src/` is FTP-uploaded verbatim, so comments are wire bytes.

The budget was sized in the UI-SPEC as "a delegated listener and an event map". The shipped file is that plus four funnel handlers, two server-declared event readers, closed-set property enforcement, and the C-8 focus call — the last three assigned to this plan *after* the budget was written (two by ledger #41, one by the plan's own prohibition). There is no comment budget left at 1,024 B: 52 bytes.

I did not resolve this unilaterally in either direction. Stripping the comments would meet the number while shipping an undocumented file whose entire reason for existing is to prevent a regression that a desktop test cannot catch — the plan's own critical hazard. The comments that survive are only those that stop a specific future edit (why the declarative attribute is forbidden; why the property lists are closed; why a focus call lives in an analytics file). **Three options for the phase owner:** accept the overage as documentation cost; raise the UI-SPEC figure for this file to ~2.0 KB; or add a deploy-time comment-strip step, which this project has deliberately avoided. The file is `defer`red and off the critical path, so the UI-SPEC's 37.1 KB critical-path figure is unaffected either way.

### Gates that could NOT be run here

The executor has **no PHP binary, no Docker daemon, and no deploy path** (`scripts/deploy-new.sh` is denied to subagents). Every `curl https://torin.bg/new/...` gate in the plan is therefore **unrun**, and would in any case have measured the *currently deployed* build rather than this work. **None of these is reported as passing:**

- Served-output counts of `tel:` anchors and `data-slot` attributes
- Exactly one third-party script origin on a served page
- `analytics.js` present exactly once on every page
- Zero theme attributes on the served homepage
- Zero robots tags on the served homepage, exactly one on `msg.html`
- All 20 pages returning 200 with zero PHP warnings after the deletions
- The homepage rendering completely and dialling with the third-party origin blocked at the network level
- `php -l` on any changed PHP file

The PHP edits were reviewed by eye instead. The riskiest is `header.php` running inside `torin_send_fail_page()`'s function scope — `$site` reaches it through that function's existing `global $site`, and `$torin_track` is assigned in the same scope immediately before the include, which is the pattern `torin_render_banner($site)` already relies on at that call site.

## Known Gaps

1. **`analytics.js` is 935 B over its gzipped budget.** Full reasoning above. Needs an owner decision, not a silent acceptance.
2. **A content blocker matching the filename `analytics.js` would also disable the C-8 error-band focus.** EasyPrivacy-class lists carry generic rules for that path. The focus call was placed there because ledger #41(a) directed it and this plan owns the file; `js/site.js` would be immune and is arguably the right home for an accessibility behaviour. **Recommend moving it in a later plan** — it is two lines. The pre-existing defect simply persists for blocker users rather than being newly introduced.
3. **The tap-to-dial truth is verified by proxy, not on a handset.** The plan itself mints this as a backstop; the static proof (no default-prevention registered anywhere on those anchors) is the substitute it names. A real-handset tap after deploy is still the honest confirmation and has not happened.
4. **`form-sent` over-counts.** `msg.html` is a plain URL with no server state: direct visits, bookmarks, refreshes and the two indistinguishable spam rejections all fire it. Making it exact needs a token or a cookie, and a cookie re-opens the consent question D4-18 exists to close. **Read it as an upper bound on completed enquiries.** Characterised in the file itself.
5. **No analytics with scripting off.** Accepted and stated per the plan: counts under-report by the no-JS share.
6. **No integrity pin is possible on the vendor script** (T-04-37, accepted): the vendor updates it in place. Mitigated by `defer`, no connection hint, and the site functioning fully without it — the last part asserted by construction here, not yet measured with a blocking run.
7. **Umami commercial-use permission has no artefact behind it.** The owner's confirmation is the only record. **Request: retain whatever he relied on** — a support reply, or the terms he accepted at signup — with the project. A licensing permission with nothing behind it is worth one line in the record.

## Cutover checklist — REQUIRED ADDITION

`04-CUTOVER-CHECKLIST.md` **does not exist yet**, so this is recorded here for whichever plan creates it. The deploy script uploads and never deletes, and nothing in this project can remove a remote file:

- [ ] Manually delete `new/css/theme-a.css` from the server
- [ ] Manually delete `new/includes/dev-switcher.php` from the server
- [ ] Verify both by fetching each path and getting a 404

**Removing them from the source tree does NOT remove them from the server.** Until that manual pass happens, the switcher partial still exists remotely — and because its own guard is file existence, it will keep rendering on staging pages even though `header.php` no longer includes it. (`header.php` no longer references it at all, so the switcher will not render; the *stylesheet* and the *partial* simply remain fetchable as orphaned files. Both are T-04-38 information disclosure and neither is closed until fetched-and-404.)

## Carried-forward ledger items

| Ledger | Status after this plan |
|---|---|
| **#39** — `$torin_robots` inert on `msg.html` | **CLOSED.** The emitter ships in `header.php`; `msg.html` gets its noindex. **04-08 can now rely on this for the sitemap.** |
| **#41(a)** — error band focusable but unfocused | **CLOSED**, with the content-blocker caveat at Known Gap 2. |
| **#41(b)** — `form-error` event not emitted | **CLOSED.** Emitted with its `field` property from the server re-render. |
| **#24** — `uslovia.html` names Umami before the tracker exists | **CAN BE CLOSED ONCE THIS DEPLOYS.** The disclosure becomes accurate the moment the tracker tag reaches production. `uslovia.html` was **not** edited — 04-04 owns it and is parked at an owner gate. Note the ordering that ledger insists on still holds and is now satisfiable: the tracker and the disclosure ship together, or the disclosure must not ship. |

## New ledger items for the orchestrator to file

The orchestrator owns `.planning/WINDOWS.md`; these were not written there.

| Kind | File | Description |
|---|---|---|
| `deviation` | `src/js/analytics.js` | 04-07: exceeds the UI-SPEC ≤1.0 KB gzipped budget at 1,959 B (code alone 972 B; comments are the overage). Needs an owner decision: accept, raise the budget, or add a deploy-time comment strip. |
| `deviation` | `src/js/analytics.js` | 04-07: the UI-SPEC C-8 error-band `.focus()` lives in a file whose name content blockers commonly match. `js/site.js` would be immune. Two-line move, recommended for a later plan. |
| `unrun-verify` | `src/includes/header.php` | 04-07: all nine served-output `curl` gates and `php -l` are unrun — no PHP binary, no Docker, no deploy path available to the executor. Must be run after the next deploy. |
| `unrun-verify` | `src/js/analytics.js` | 04-07: the tap-to-dial truth is verified only by proxy (no default-prevention registered). A real-handset tap after deploy is outstanding. |
| `deviation` | `src/includes/site-config.php` | 04-07: Umami commercial-use permission rests on owner confirmation with no retained artefact. Request the support reply or accepted terms be kept with the project. |

## Dependencies for later plans

- **04-08 (sitemap)** depends on the `$torin_robots` emitter shipped here. `msg.html` now carries `noindex, follow`, and the sitemap must still not list it — the two are belt and braces, not alternatives.
- **04-10 (root cutover)** must honour ledger #24's ordering and must carry the two manual server deletions above.
- **04-04 (`uslovia.html`)** should fold ledger #41(c) — the confirmation-email processing purpose — into its parked owner conversation. Untouched here.

## Self-Check: PASSED

- `src/js/analytics.js` — FOUND
- `src/css/theme-a.css` — correctly ABSENT
- `src/includes/dev-switcher.php` — correctly ABSENT
- Commit `e741954` — FOUND
- Commit `ec3595c` — FOUND
