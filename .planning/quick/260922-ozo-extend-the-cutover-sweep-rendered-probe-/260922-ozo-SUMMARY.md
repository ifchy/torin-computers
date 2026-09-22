---
quick_id: 260922-ozo
slug: extend-the-cutover-sweep-rendered-probe
date: 2026-09-22
status: complete
commits:
  - e594567 fix(cdp) — deliver CDP events, so onConsoleError can exist at all
  - 59c346f feat(sweep) — render every deployed page, not just the homepage
  - 0e2f787 feat(sweep) — feed every page from [3] into the rendered probe
---

# The rendered probe now covers all 20 pages, and its console check is real

The cutover sweep rendered `index.html` and status-checked the other nineteen. Both halves of
that are now the same twenty pages, and a third defect found en route — a console-error check
that had never been able to run — is fixed.

## What the sweep could not have caught before

**Nineteen of twenty pages were covered by the one assertion this project has proof does not
settle the question.** `expect_status … 200` is exactly what the `$torin_page` collision passed
while four pages rendered the single letter «s» where prose belonged (STATE.md, Phase 3.5). The
Cyrillic token count exists *because* a 200 did not catch that. It was being applied to the
homepage only.

Now: 20 pages rendered in one browser session, 107–1077 Cyrillic tokens each against a floor of
40, one `<h1>` each, no runtime diagnostics in any body, no chat widget in any DOM.

## The defect found by running it — a check that never ran

`scripts/probes/cutover-sweep.js:48` guarded its collector with
`if (typeof cdp.onConsoleError === 'function')`. `cdp-client.js` exported
`{ connect, open, evaluate, pressTab, screenshot, httpJson }`. **No `onConsoleError` existed
anywhere in the tree, so the guard was never once true.**

Every `"consoleErrors": []` this project has recorded was an empty array that no code could have
filled, and the probe's own `'console errors during load'` failure branch was unreachable. The
09:55 staging run earlier today reported that vacuous `[]` inside a PASS.

The root cause was one level below the missing export: `connect()`'s message handler dispatched
replies (`msg.id`) and dropped everything else. **CDP events carry `method` and never an `id`**,
so console output and uncaught exceptions had no path out of the socket — the export could not
have been written without fixing the pump first.

Fixed with an event registry + `session.onEvent()`, then `onConsoleError()` on top:
`Runtime.consoleAPICalled` (error/assert) **and** `Runtime.exceptionThrown`, which do not
overlap — the second is the one that catches a throw stopping `site.js` mid-file. `Runtime.enable`
is awaited before the first navigation, because a load that starts before it lands reports
nothing.

**Proven, not assumed.** An empty result is the exact bug being fixed, so `[]` could not be the
evidence. A page that calls `console.error` and throws asynchronously returns both:

```
"DELIBERATE console.error 42"
"Error: DELIBERATE uncaught throw"
```

## The collision that was avoided rather than discovered

`cutover-sweep.sh` globs the **whole** probe output for the verdict. Twenty per-page records each
carrying a `"verdict"` key would have let one passing page satisfy `*'"verdict": "PASS"'*` on a
failing run — the aggregate would have been unreadable and the failure invisible. Per-page key is
`pageVerdict`, aggregate is `sweepVerdict`, and the shell matches only the aggregate.

## Discipline carried through

- **One derivation, one set.** The URL list is built inside the existing step [3] loop over
  `src/*.html`, not as a second list. A page cannot be status-checked but not rendered.
- **Unmeasurable is named, never folded into a pass.** If a client cannot deliver events, each
  page is marked INCONCLUSIVE and the digest prints `console errors: NOT MEASURED — empty lists
  below mean nothing`. The flag prints on every run, including passing ones.
- **A sweep over zero pages is INCONCLUSIVE, and an empty URL list FAILs the step.** Deriving
  nothing from `src/` is a broken sweep, not a clean one.
- **One bad page does not cost the other nineteen.** A page that throws mid-render is recorded as
  a FAIL and the loop continues.
- **Unparseable output is never swallowed** — `render-digest.js` exits non-zero having printed
  nothing, and the shell prints the probe's raw output, because a harness or browser error *is*
  the diagnosis.

## Verified

| Check | Result |
|---|---|
| `onConsoleError` exported and delivering | both `console.error` and an uncaught throw captured |
| Single-URL fallback (`SWEEP_URLS` unset) | unchanged — 1 page, PASS |
| Full staging sweep | **20/20 pages rendered**, sweep total 34 pass / 0 fail / 4 skip |
| Digest failure path | FAIL + INCONCLUSIVE pages render with failure, console and diagnostic lines |
| Malformed probe output | exits 1 silently, shell falls back to raw |
| `bash -n`, `node --check` | clean; empty-array guard verified under bash 3.2 `set -u` |

The four skips are unchanged and profile-correct: `robots.txt` and `sitemap.xml` are not
applicable under a path prefix, the GSC token belongs at the true root, and the live submission
was not attempted (it pages the shop owner).

## Out of scope, and one follow-up

The root/live profile is untouched — this was a staging rehearsal and **does not authorise a
cutover**; the go/no-go still must run against the root per `04-CUTOVER-CHECKLIST.md`.

**Follow-up worth logging:** `02-RENDERED-CHECKS.md` still reads "Rendered checks covered
`index.html` only. The other fifteen pages were verified by HTTP sweep." That remains true of the
*Phase 2 criteria probes* (contrast, focus rings, hero stack, no-script nav), which is a different
instrument from this one — but a reader is now likely to conflate the two. Not edited here; a
phase artifact should not be amended from a quick task without the phase owner's call.
