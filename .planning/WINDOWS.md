---
schema_version: 1
open_count: 11
waived_count: 0
fixed_count: 0
total_count: 11
last_updated: 2026-08-06T14:09:24.636Z
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
| 5 | 02 | unrun-verify | src/css/components.css |  | Hero stack height at 360x640 not re-measured after the CR-01 badge-margin fix: no Chrome/Chromium/Playwright on the build machine and Safari remote automation is disabled, so the comment records 241.6px as DERIVED (249.6 minus the 8px margin delta), not measured | open |  | 2026-08-06T13:58:23.175Z |  |
| 6 | 02 | unrun-verify | src/css/base.css |  | Keyboard focus-ring human-check (six dark-surface CTAs, both themes, plus the light-surface CTA staying navy) not observed in a browser — ratios are computed, the rendered ring is not yet seen | open |  | 2026-08-06T13:58:23.244Z |  |
| 7 | 02 | unrun-verify | src/css/components.css |  | FOUT/web-font-swap backstop re-opened by 02-05: the hero stack changed by 8px, so the Sofia Sans fallback-reflow check against hero CTA displacement on a throttled connection is unclosed, not inherited | open |  | 2026-08-06T13:58:23.302Z |  |
| 8 | 02 | unrun-verify | src/css/no-js.css |  | No-script rendered nav human-check unrun: five top-level items + six category links visible/activatable at 360/900/1440px, and neither disclosure control visible or Tab-reachable. No automatable browser on the build machine. | open |  | 2026-08-06T14:09:24.452Z |  |
| 9 | 02 | unrun-verify | src/css/no-js.css |  | UI-SPEC 'overflow' backstop RE-OPENED by 02-06's desktop no-script layout: scrollWidth <= innerWidth with scripting disabled at 900px and 1440px is unmeasured. Abstains to human_needed; must not be recorded as passing. | open |  | 2026-08-06T14:09:24.518Z |  |
| 10 | 02 | deviation | src/css/no-js.css |  | Residual, not closed by 02-06: scripting ENABLED but site.js failing to load/throw leaves the nav hidden below 56.25rem. Closing it needs a scripting-capability marker written before first paint, which the project deliberately does not have. | open |  | 2026-08-06T14:09:24.578Z |  |
| 11 | 02 | deviation | src/css/no-js.css |  | 02-06 desktop no-script row shape: 'flex: 1 0 100%' on .nav__item--has-sub (plan-mandated, grep-asserted) splits the four visible top-level links across two wrapped rows rather than one, because the has-sub item sits mid-list. Navigable and in-flow, but the plan's human-check phrasing 'five top-level items still read as a horizontal row' is NOT satisfied as worded. Open. | open |  | 2026-08-06T14:09:24.636Z |  |

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
  },
  {
    "id": 5,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/components.css",
    "line": null,
    "description": "Hero stack height at 360x640 not re-measured after the CR-01 badge-margin fix: no Chrome/Chromium/Playwright on the build machine and Safari remote automation is disabled, so the comment records 241.6px as DERIVED (249.6 minus the 8px margin delta), not measured",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T13:58:23.175Z",
    "resolved_at": null
  },
  {
    "id": 6,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/base.css",
    "line": null,
    "description": "Keyboard focus-ring human-check (six dark-surface CTAs, both themes, plus the light-surface CTA staying navy) not observed in a browser — ratios are computed, the rendered ring is not yet seen",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T13:58:23.244Z",
    "resolved_at": null
  },
  {
    "id": 7,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/components.css",
    "line": null,
    "description": "FOUT/web-font-swap backstop re-opened by 02-05: the hero stack changed by 8px, so the Sofia Sans fallback-reflow check against hero CTA displacement on a throttled connection is unclosed, not inherited",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T13:58:23.302Z",
    "resolved_at": null
  },
  {
    "id": 8,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/no-js.css",
    "line": null,
    "description": "No-script rendered nav human-check unrun: five top-level items + six category links visible/activatable at 360/900/1440px, and neither disclosure control visible or Tab-reachable. No automatable browser on the build machine.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.452Z",
    "resolved_at": null
  },
  {
    "id": 9,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/no-js.css",
    "line": null,
    "description": "UI-SPEC 'overflow' backstop RE-OPENED by 02-06's desktop no-script layout: scrollWidth <= innerWidth with scripting disabled at 900px and 1440px is unmeasured. Abstains to human_needed; must not be recorded as passing.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.518Z",
    "resolved_at": null
  },
  {
    "id": 10,
    "kind": "deviation",
    "phase": "02",
    "file": "src/css/no-js.css",
    "line": null,
    "description": "Residual, not closed by 02-06: scripting ENABLED but site.js failing to load/throw leaves the nav hidden below 56.25rem. Closing it needs a scripting-capability marker written before first paint, which the project deliberately does not have.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.578Z",
    "resolved_at": null
  },
  {
    "id": 11,
    "kind": "deviation",
    "phase": "02",
    "file": "src/css/no-js.css",
    "line": null,
    "description": "02-06 desktop no-script row shape: 'flex: 1 0 100%' on .nav__item--has-sub (plan-mandated, grep-asserted) splits the four visible top-level links across two wrapped rows rather than one, because the has-sub item sits mid-list. Navigable and in-flow, but the plan's human-check phrasing 'five top-level items still read as a horizontal row' is NOT satisfied as worded. Open.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.636Z",
    "resolved_at": null
  }
]
````
