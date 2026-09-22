---
quick_id: 260922-dlw
slug: close-audited-ledger-entries
date: 2026-09-22
description: Close six audited Broken Windows entries and correct the 04-04 status inconsistency in ROADMAP.md
---

# Close audited ledger entries + correct 04-04 status

## Origin

A full audit of all 42 open `.planning/WINDOWS.md` entries, run 2026-09-22 against the
tree, the live origin, and the build machine. Every closure below rests on a measurement
taken during that audit, recorded here so the closure is not another unexplained one —
ledger entry 50 exists precisely because entry 13 was closed without a recorded reason.

**The environment premise behind the unrun-verify entries was re-confirmed, not assumed:**
`command -v php` is empty and the Docker daemon is not running. Entries 19/28/29/30/34/35/40/47
are therefore honest and stay open.

## Task 1 — Ledger closures (six entries)

Use `node ~/.claude/gsd-core/bin/gsd-tools.cjs windows ...`. Do NOT hand-edit the
WINDOWS.md table; the frontmatter counts must stay in sync with the rows.

### `fixed` — the repair landed in the tree and is grep-verifiable

| id | Evidence |
|----|----------|
| 1 | All six category pages supply `intro`/`warranty_key`/`faq`/`related`; five of six also supply `process`. The sixth slot, `prices`, is deliberately unset by recorded decision D3-06 (PRICE-01 is v2), documented in-file at `src/mehanichni-problemi.html:170`. Phase 3 delivered what the entry was waiting on. |
| 16 | The non-portable `wc -l \| grep -qx 0` idiom has **zero** occurrences in `scripts/`. It survives only in six historical plan/summary docs, `deferred-items.md` (which documents it as a trap), and WINDOWS.md itself. The **unexecuted** `04-10-PLAN.md` is clean, so no future executor inherits it. |
| 48 | All four mismeasuring gates repaired. `scripts/asset-version-check.sh:195` now uses `EXPECTED_MAXAGE=31536000` with an exact comparison, replacing the prefix-match `max-age=3` that matched both 300 and 31536000. |

### `waive` — premise stale or tracked elsewhere (reason required and preserved)

| id | Reason |
|----|--------|
| 2 | DIFF-02 retired 2026-09-11 (`REQUIREMENTS.md:29`, `ROADMAP.md:18`) — battery regeneration discontinued by the business. No regeneration claim remains in `src/index.html`. Guards a requirement that no longer exists. |
| 27 | Stale three ways: OWNER-QUESTIONS #23 ANSWERED 2026-09-11; `za-bateriite.html` deleted from the tree in Phase 3.5; `site-config.php` implements exactly the answered rule. Recorded during 04-04 *after* the question was already answered. |
| 45 | Discharged by 04-09 — `04-CUTOVER-CHECKLIST.md` Section 3 items 8–9 list both paths and line 217 names the class citing this entry. The underlying fact still holds (both files re-confirmed HTTP 200 on staging 2026-09-22) but is now tracked by the checklist's gated deletion pass. |

**Verify:** `windows status` shows `open_count` 42 → 36, `waived_count` 3, `fixed_count` 14.

## Task 2 — ROADMAP.md 04-04 correction

`04-04` is checked `[x]` and counted as executed, but
`.planning/phases/04-hardening-cutover/04-04-SUMMARY.md` reads `status: blocked`: Task 3, a
`gate="blocking"` human checkpoint for owner sign-off on published legal text, was never
started. No flip commit exists — contrast 04-05, which has an explicit
`status blocked -> complete` in `ca701d0`.

**Do NOT flip the `- [x]` checkbox at line 250.** The plan was genuinely executed (2 of 3
tasks, code complete); flipping the box risks a tool or a later session re-dispatching
finished work. Correct the misleading parts instead:

- **line 250** — append an explicit blocked annotation to the existing text.
- **line 233** — `**Plans**: 9/10 plans executed` → state 8 complete, 1 executed-but-blocked
  (04-04), 1 not started (04-10).
- **line 284** — progress table `9/10` cell reflects the same; status stays `In Progress`.

Ledger entries 23, 25 and 26 all depend on that same unrun Task 3 checkpoint and remain
correctly open.

## Commits

Two atomic commits: ledger closures, then the ROADMAP correction.

## Out of scope

The remaining 36 open entries were audited and confirmed real. No code changes.
