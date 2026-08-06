---
schema_version: 1
open_count: 4
waived_count: 0
fixed_count: 0
total_count: 4
last_updated: 2026-08-06T03:41:56.325Z
---

# Broken Windows Ledger

> Cross-phase defect register. `/gsd-ship` blocks while `open_count > 0`.
> Waive with `gsd-tools windows waive <id> "<reason>"` (reason required).
> Mark fixed with `gsd-tools windows fixed <id>`.

| id | phase | kind | file | line | description | status | reason | recorded_at | resolved_at |
|----|-------|------|------|------|-------------|--------|--------|-------------|-------------|
| 1 | 02 | stub | src/includes/category-page.php |  | Six template slots (intro, warranty/TRUST-03, process, FAQ, related, prices) render nothing on all three category pages — content is Phase 3 per D-25 | open |  | 2026-08-06T03:41:56.147Z |  |
| 2 | 02 | stub | src/index.html |  | DIFF-02 (battery regeneration) ships inside a collapsed disclosure — knowingly unmet, must not pass silently in Phase 3 verification (D-13 / OWNER-QUESTIONS #9) | open |  | 2026-08-06T03:41:56.205Z |  |
| 3 | 02 | stub | src/includes/site-config.php |  | hours, viber and notice are [ASSUMED] — OWNER-QUESTIONS #20/#21/#8 block the Phase 4 cutover | open |  | 2026-08-06T03:41:56.265Z |  |
| 4 | 02 | stub | src/js/site.js |  | JS-disabled nav gap: with script blocked the six category links are unreachable from the nav; mitigated by the homepage card grid, accepted not solved | open |  | 2026-08-06T03:41:56.325Z |  |

````json
[
  {
    "id": 1,
    "kind": "stub",
    "phase": "02",
    "file": "src/includes/category-page.php",
    "line": null,
    "description": "Six template slots (intro, warranty/TRUST-03, process, FAQ, related, prices) render nothing on all three category pages — content is Phase 3 per D-25",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.147Z",
    "resolved_at": null
  },
  {
    "id": 2,
    "kind": "stub",
    "phase": "02",
    "file": "src/index.html",
    "line": null,
    "description": "DIFF-02 (battery regeneration) ships inside a collapsed disclosure — knowingly unmet, must not pass silently in Phase 3 verification (D-13 / OWNER-QUESTIONS #9)",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.205Z",
    "resolved_at": null
  },
  {
    "id": 3,
    "kind": "stub",
    "phase": "02",
    "file": "src/includes/site-config.php",
    "line": null,
    "description": "hours, viber and notice are [ASSUMED] — OWNER-QUESTIONS #20/#21/#8 block the Phase 4 cutover",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.265Z",
    "resolved_at": null
  },
  {
    "id": 4,
    "kind": "stub",
    "phase": "02",
    "file": "src/js/site.js",
    "line": null,
    "description": "JS-disabled nav gap: with script blocked the six category links are unreachable from the nav; mitigated by the homepage card grid, accepted not solved",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.325Z",
    "resolved_at": null
  }
]
````
