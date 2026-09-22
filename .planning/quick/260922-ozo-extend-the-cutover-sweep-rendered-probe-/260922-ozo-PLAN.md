---
quick_id: 260922-ozo
slug: extend-the-cutover-sweep-rendered-probe
date: 2026-09-22
description: Extend the cutover sweep's rendered probe from index.html to all deployed pages, and fix the dead console-error hook
---

# Extend the rendered probe across every deployed page

## Why

`scripts/cutover-sweep.sh` step [9] renders **one page**. The other nineteen are asserted by
HTTP status alone — `expect_status … 200`. This project has already shipped a 200 that rendered
the single letter «s» where prose belonged (the `$torin_page` collision, STATE.md Phase 3.5),
and the reason the Cyrillic token count exists at all is that a status code did not catch it.
Nineteen of twenty pages are currently covered by exactly the check that was already proven
insufficient.

## The second defect, found while running the sweep on 2026-09-22

`scripts/probes/cutover-sweep.js:48` guards its console-error collector:

```js
if (typeof cdp.onConsoleError === 'function') {
```

`scripts/lib/cdp-client.js:209` exports `{ connect, open, evaluate, pressTab, screenshot, httpJson }`.
There is no `onConsoleError`, anywhere in the tree. **The guard has never once been true.**

So `"consoleErrors": []` in every sweep result this project has ever recorded was not a
measurement — it was an empty array that no code could have filled. The probe's own
`'console errors during load'` failure branch has never been reachable. The staging run earlier
today reported it as part of a PASS.

That is the exact failure mode this codebase already names in `svc-page.js` and in the probe's
own comments: a pass asserted over an absent surface. Extending it across twenty pages without
fixing it would multiply a false assurance by twenty.

## The collision to avoid

`cutover-sweep.sh` reads the verdict with a glob over the **whole** probe output:

```bash
case "$RENDER_OUT" in
  *'"verdict": "PASS"'*) pass "rendered probe on index.html" ;;
```

Per-page records each carrying a `"verdict"` key would let one passing page satisfy that match
while the run as a whole failed. Per-page key is `pageVerdict`; the aggregate is `sweepVerdict`;
the shell matches only the aggregate.

## Tasks

1. **`scripts/lib/cdp-client.js`** — make CDP *events* reachable. `connect()`'s message handler
   currently dispatches only replies (`msg.id`) and drops every event. Add an event registry and
   expose `session.onEvent(method, fn)`, then add and export `onConsoleError(session, cb)`:
   `Runtime.enable` plus `Runtime.consoleAPICalled` (type `error`/`assert`) and
   `Runtime.exceptionThrown`. Await the enable so it lands before the first navigation.

2. **`scripts/probes/cutover-sweep.js`** — iterate a URL list in ONE browser session: read
   `SWEEP_URLS` in Node *above* the evaluate string (same discipline as `SWEEP_MIN_CYRILLIC`;
   `process.env` does not exist in page scope), then `cdp.open` + `cdp.evaluate` per URL, reusing
   the one session. Falls back to `[opts.url]` when unset, so a manual single-page run is
   unchanged. Rename the in-page `verdict` to `pageVerdict`. Attribute console errors per page by
   slicing the buffer around each navigation. Wrap each page so one thrown navigation does not
   lose the other nineteen results. Aggregate into `sweepVerdict`: FAIL if any page failed,
   INCONCLUSIVE if any asserted nothing (**including an empty URL list, and including console
   errors being unmeasurable**), PASS only if every page passed.

3. **`scripts/cutover-sweep.sh`** — build the URL list in the step [3] loop that already walks
   `src/*.html` and skips `google*.html`, so the rendered set is the same set by construction,
   not a second hardcoded list that can drift. Pass it as `SWEEP_URLS`. Match `"sweepVerdict"`.
   Print a one-line-per-page digest plus full detail for any non-PASS page, not twenty raw JSON
   blobs.

## Verify

- `node -e "require('./scripts/lib/cdp-client.js').onConsoleError"` resolves to a function.
- A deliberately-thrown page error is *caught* by the new hook — prove the branch fires rather
  than assuming it, since "it returned []" is what we are fixing.
- `bash -n` clean on the sweep script.
- Single-URL invocation of the probe still works with `SWEEP_URLS` unset.
- Full sweep against staging `https://torin.bg/new` reports 20 pages rendered, no `--submit`.

## Out of scope

The root/live profile. This runs against staging only; the cutover go/no-go is unchanged in
kind and still must be run against the root by `04-CUTOVER-CHECKLIST.md`.
