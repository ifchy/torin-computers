---
quick_id: 260922-dlw
slug: close-audited-ledger-entries
date: 2026-09-22
status: complete
commits:
  - e2f2f8e docs(windows): close six audited entries — three stale, three discharged
  - 0640f7d docs(roadmap): 04-04 is blocked, not complete — correct the phase-4 counts
---

# Close audited ledger entries + correct 04-04 status

## What was done

A full audit of all 42 open `.planning/WINDOWS.md` entries against the tree, the live
origin, and the build machine. **Six closed, 36 confirmed real and left open.**

`open_count` 42 → 36, `waived_count` 0 → 3, `fixed_count` 11 → 14.

### Closed as `fixed` (repair verified in the tree)

- **1** — the six category pages supply `intro`/`warranty_key`/`faq`/`related`; five of six
  also `process`. The sixth slot, `prices`, is deliberately unset by D3-06 (PRICE-01 is v2).
- **16** — zero occurrences of `wc -l | grep -qx 0` remain in `scripts/`. The decisive
  check was the **unexecuted** `04-10-PLAN.md`, which is clean.
- **48** — `asset-version-check.sh:195` now compares `EXPECTED_MAXAGE=31536000` exactly.

### Closed as `waived` (reason recorded on the row)

- **2** — DIFF-02 retired 2026-09-11; guards a requirement that no longer exists.
- **27** — Q#23 answered 2026-09-11, `za-bateriite.html` deleted in Phase 3.5, and
  `site-config.php` implements the answered rule. Recorded *after* the answer existed.
- **45** — discharged by 04-09's checklist Section 3; the fact still holds (both files
  re-confirmed HTTP 200 on staging) but is tracked there now.

### ROADMAP correction

04-04 was checked `[x]` and counted toward "9/10 plans executed" while
`04-04-SUMMARY.md` reads `status: blocked` — Task 3, a `gate="blocking"` owner sign-off
checkpoint, was never started and no flip commit exists. Counts corrected to 8 complete,
1 blocked, 1 not started.

**The `[x]` was deliberately left set**: the plan genuinely executed (Tasks 1–2, code
shipped), and clearing it invites re-dispatch of finished work. The blocked status is now
stated inline on the plan line instead, with an explicit do-not-re-dispatch note.

## Measurements that did NOT close anything

The environment premise behind the unrun-verify entries was re-confirmed, not assumed:
`command -v php` is empty and the Docker daemon is not running. Entries
19/28/29/30/34/35/40/47 are honest and stay open. Re-measured and confirmed real:
analytics.js at 1959 B gzipped against a 1024 B budget (43); photo-resize.js at 2042 B
against 2048 (20); sitemap at 19 `<loc>` against a plan gate demanding ≥20 (49);
`contrast.js` exporting `{HELPERS}` with no `run` while `render-check.sh:145` calls
`probe.run` (12); `robots.txt`/`sitemap.xml` 404 on the origin (47).

## Notes for whoever picks this up

- **Entries 23, 25 and 26 all hang off 04-04's unrun Task 3 checkpoint.** They close with
  the owner, not with a deploy. Running that checkpoint closes four things at once.
- **Entry 51 is the sharpest remaining paper cut**: the cutover checklist tells the operator
  to submit `sitemap.xml` with no step to regenerate it first, and the file currently holds
  19 `/new/` staging URLs. Following it literally submits staging paths to Google
  immediately after a cutover whose purpose is URL continuity.
- **WINDOWS.md is hostile to column parsing.** Entries 16 and 48 contain literal `|`
  characters, so naive `awk -F'|'` reports two rows fewer than exist and mislabels their
  status. Read the ledger through `gsd-tools windows status --raw`, not through awk — the
  first pass of this audit produced a phantom count discrepancy exactly that way.
- **Still true and unowned until 04-10**: all four root host variants
  (`http`/`https` × `www`) return 200 with no redirect.
