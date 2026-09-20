---
schema_version: 1
open_count: 22
waived_count: 0
fixed_count: 5
total_count: 27
last_updated: 2026-09-20T16:00:59.898Z
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
| 4 | 02 | stub | src/js/site.js |  | JS-disabled nav gap: with script blocked the six category links are unreachable from the nav; mitigated by the homepage card grid, accepted not solved | fixed |  | 2026-08-06T03:41:56.325Z | 2026-08-06T20:55:27.306Z |
| 5 | 02 | unrun-verify | src/css/components.css |  | Hero stack height at 360x640 not re-measured after the CR-01 badge-margin fix: no Chrome/Chromium/Playwright on the build machine and Safari remote automation is disabled, so the comment records 241.6px as DERIVED (249.6 minus the 8px margin delta), not measured | fixed |  | 2026-08-06T13:58:23.175Z | 2026-08-06T20:55:13.586Z |
| 6 | 02 | unrun-verify | src/css/base.css |  | Keyboard focus-ring human-check (six dark-surface CTAs, both themes, plus the light-surface CTA staying navy) not observed in a browser — ratios are computed, the rendered ring is not yet seen | fixed |  | 2026-08-06T13:58:23.244Z | 2026-08-06T20:55:27.370Z |
| 7 | 02 | unrun-verify | src/css/components.css |  | FOUT/web-font-swap backstop re-opened by 02-05: the hero stack changed by 8px, so the Sofia Sans fallback-reflow check against hero CTA displacement on a throttled connection is unclosed, not inherited | open |  | 2026-08-06T13:58:23.302Z |  |
| 8 | 02 | unrun-verify | src/css/no-js.css |  | No-script rendered nav human-check unrun: five top-level items + six category links visible/activatable at 360/900/1440px, and neither disclosure control visible or Tab-reachable. No automatable browser on the build machine. | fixed |  | 2026-08-06T14:09:24.452Z | 2026-08-06T20:55:27.435Z |
| 9 | 02 | unrun-verify | src/css/no-js.css |  | UI-SPEC 'overflow' backstop RE-OPENED by 02-06's desktop no-script layout: scrollWidth <= innerWidth with scripting disabled at 900px and 1440px is unmeasured. Abstains to human_needed; must not be recorded as passing. | fixed |  | 2026-08-06T14:09:24.518Z | 2026-08-06T20:55:27.500Z |
| 10 | 02 | deviation | src/css/no-js.css |  | Residual, not closed by 02-06: scripting ENABLED but site.js failing to load/throw leaves the nav hidden below 56.25rem. Closing it needs a scripting-capability marker written before first paint, which the project deliberately does not have. | open |  | 2026-08-06T14:09:24.578Z |  |
| 11 | 02 | deviation | src/css/no-js.css |  | 02-06 desktop no-script row shape: 'flex: 1 0 100%' on .nav__item--has-sub (plan-mandated, grep-asserted) splits the four visible top-level links across two wrapped rows rather than one, because the has-sub item sits mid-list. Navigable and in-flow, but the plan's human-check phrasing 'five top-level items still read as a horizontal row' is NOT satisfied as worded. Open. | open |  | 2026-08-06T14:09:24.636Z |  |
| 12 | 02 | deviation | scripts/probes/contrast.js |  | contrast.js exports { HELPERS } and is not a runnable probe, but 02-09 Task 3 and the phase docs invoke it via render-check.sh (probe.run is not a function); the trust-badge 10.14:1 baseline has no committed probe that reproduces it | open |  | 2026-08-09T14:16:43.110Z |  |
| 13 | 03 | unrun-verify | scripts/seo-metadata-check.js |  | 03-09 live gate NOT RUN: deploy unavailable in executor context, so the 11 tuned pages still serve pre-plan metadata. 'node scripts/seo-metadata-check.js --live' currently reports served-matches-source on exactly those 11. Deploy then re-run to close. | open |  | 2026-08-26T08:07:31.628Z |  |
| 14 | 04 | unrun-verify | src/includes/contact-form.php |  | 04-02 Task 3: honeypot autofill false-positive UNCONFIRMED. The human's manual handset submission succeeded, but they did not state whether browser autofill/password-manager was active, which is the plan's named number-one honeypot false-positive source. A false positive silently discards a real enquiry and is invisible to both parties. Re-test with autofill explicitly on. | open |  | 2026-09-20T10:55:10.721Z |  |
| 15 | 04 | unrun-verify | src/includes/notify.php |  | 04-02: the notification FAILURE path has never been exercised. Every live check drove the success branch; no check made api.telegram.org unreachable, so the visitor-facing 'every channel failed' page and the error_log correlation-id branch in contact-send.php are unproven at runtime. 04-05 adds a second driver and should exercise this while it is there. | open |  | 2026-09-20T10:55:16.705Z |  |
| 16 | 04 | deviation | .planning/phases/04-hardening-cutover/04-02-PLAN.md |  | 04-02 verify V7 is NOT PORTABLE and reports a false failure on macOS: 'grep -L PATTERN FILE \| wc -l \| grep -qx 0' never matches on BSD wc, which pads its count to seven spaces then 0 (confirmed by od -c); GNU wc emits an unpadded 0 and the same check passes on Linux. The underlying condition was TRUE. Every later plan using the 'wc -l \| grep -qx N' idiom has the same defect; use N=$(... \| wc -l \| tr -d ' '); [ "$N" = "0" ] instead. | open |  | 2026-09-20T10:55:25.672Z |  |
| 17 | 04 | unrun-verify | src/includes/upload.php |  | 04-03: the PORTRAIT-ORIENTATION path is unverified end to end, on BOTH sides. The server reads exif_read_data() Orientation before re-encoding and photo-resize.js passes imageOrientation:'from-image', but every fixture used in live testing was produced by macOS sips, which bakes rotation into pixels and writes NO orientation tag — and a file with no tag passes a completely broken pipeline identically to a correct one (P-7's named warning sign). Requires ONE portrait photograph taken on a real handset, submitted through kontakti.html, checked upright in Telegram. This is the plan's own backstop truth EA-07. | open |  | 2026-09-20T11:52:11.837Z |  |
| 18 | 04 | unrun-verify | src/includes/notify.php |  | 04-03: nobody has LOOKED at the owner's phone. The host error_log carries no 'telegram transport failed' / 'sendPhoto failed' / 'telegram rejected the call' line for any of the 0/1/3-photo runs, so every API call returned ok:true and the photos reached Telegram's servers — but that is an assertion about the API's answer, not about what rendered. Unconfirmed: the single photo shows the enquiry as its caption, and the three arrive as ONE media group rather than three separate messages. One human glance closes it. | open |  | 2026-09-20T11:52:20.639Z |  |
| 19 | 04 | unrun-verify | scripts/upload-selftest.php |  | 04-03: scripts/upload-selftest.php has NEVER BEEN EXECUTED, in either direction. It was authored before includes/upload.php as the plan's tdd=true RED half, but the build machine has no php binary and no running Docker daemon, so RED was never observed failing and GREEN was never observed passing. The eight behaviours it encodes are a specification, not a gate. Six of them are covered indirectly by the live server checks in 04-03-SUMMARY; the orientation one is not covered at all. Run 'php scripts/upload-selftest.php' the moment a PHP runtime exists. | open |  | 2026-09-20T11:52:27.963Z |  |
| 20 | 04 | deviation | src/js/photo-resize.js |  | 04-03: photo-resize.js measures 2042 B gzipped against a 2048 B gate — SIX bytes of headroom. The UI-SPEC 2 KB budget and this tree's comment convention are in direct conflict for JS, because deploy-new.sh comment-strips CSS but not JS, so prose in a .js file is wire cost while prose in a .css file is free. The file's comments were cut to five to fit and the reasoning moved into 04-03-SUMMARY.md. The real fix is to route .js through scripts/lib/ a comment stripper in deploy-new.sh the same way CSS goes through strip-css-comments.py; until then the next comment added to this file breaks the gate. | open |  | 2026-09-20T11:52:35.755Z |  |
| 21 | 04 | unrun-verify | src/includes/upload.php |  | 04-03: the WebP decode branch is unexercised. The form advertises accept=image/jpeg,image/png,image/webp and torin_normalise_upload() routes WebP through imagecreatefromwebp() behind a function_exists guard, but no WebP was ever submitted. GD's WebP decoder is a separate build option from ext-gd itself, which the probe measured; if it is absent, every WebP the picker happily offers is refused with 'файлът не е разпознат като снимка'. Either submit one WebP, or drop webp from the accept list so the copy stops advertising it. | open |  | 2026-09-20T11:52:43.288Z |  |
| 22 | 04 | deviation | src/contact-send.php |  | 04-03: a refused photograph still costs the visitor their typed description. Per-file rejection reasons DO surface as a field-level error on the photo control (T-04-17, measured: 'Снимка 1: файлът не е разпознат като снимка' and 'Може да прикачите най-много 5 снимки, а са приложени 6'), but contact-send.php renders the 04-02 honest-failure page rather than re-rendering the form, so the device model and fault description are lost. torin_render_contact_form() already accepts and escapes $values; 04-05 owns wiring the re-render and should route the photos branch through it. | open |  | 2026-09-20T11:52:51.426Z |  |
| 23 | 04 | unrun-verify | src/uslovia.html |  | 04-04: EVERY live gate in this plan is unrun — six of them, all blocked by one cause and all closed by one action. scripts/deploy-new.sh and php are both denied to subagents by the permission classifier, so neither a staging deploy nor a local PHP render was possible from the executor. The six: (1) homepage carries the new label 3x with zero PHP warnings; (2) no horizontal overflow at 360x640 plus the measured call-bar button width via scripts/render-check.sh scripts/probes/svc-page.js; (3) uslovia.html returns 200 with zero PHP warnings; (4) uslovia.html body is Cyrillic; (5) uslovia.html names the processor (grep Umami); (6) no new SEO metadata failure for uslovia via node scripts/seo-metadata-check.js --live. This is an environment boundary, not a defect, and is NOT a reason to treat these as passing. Deploy the seven named paths, then run all six. | open |  | 2026-09-20T16:00:35.305Z |  |
| 24 | 04 | deviation | src/uslovia.html |  | 04-04: the analytics disclosure on uslovia.html names a processor the site does not yet load. grep -rn 'umami' src/ returns nothing — there is no js/analytics.js and no Umami script tag as of this plan. The disclosure is correct AT CUTOVER, when 04-07 wires the tracker, and inaccurate in the other direction until then. Staging carries X-Robots-Tag: noindex so nothing is publicly committed yet, and a boxed comment at the block states the coupling and says to DELETE the block if analytics is dropped. THIS BLOCK MUST NOT REACH PRODUCTION AHEAD OF THE TRACKER — 04-07 and 04-10 both need to honour that ordering. | open |  | 2026-09-20T16:00:42.305Z |  |
| 25 | 04 | stub | src/uslovia.html |  | 04-04: the device-data commitment is ABSENT BY CHOICE. UI-SPEC S11 sketched a terms block covering what happens to a customer's data on a device left for repair; OWNER-QUESTIONS #27 is open, so no sentence was written — publishing a plausible-sounding commitment nobody agreed to is the exact failure mode threat T-04-18 names. Recorded in the page's header comment so the gap is visible to the next reader rather than silently missing. Needs the owner's answer, then a block written to match it. | open |  | 2026-09-20T16:00:48.141Z |  |
| 26 | 04 | stub | src/remont-na-portove.html |  | 04-04: carried-forward ROADMAP item 3 residue is untouched, as plan 04-04 directed. The unconfirmed symptoms line on remont-na-portove.html and the three owner questions behind it are deliberately not closed here. Raise at the 04-04 Task 3 owner checkpoint (he is reading the legal pages anyway) and record the answers in OWNER_ANSWERS.md. | open |  | 2026-09-20T16:00:53.877Z |  |
| 27 | 04 | deviation | src/warrently.html |  | 04-04: the warranty-term rider is still open and is a published contradiction. The battery warranty term stated on za-bateriite.html and the one-month general term on warrently.html disagree (OWNER-QUESTIONS #23). Not 04-04's to fix; folded into its Task 3 owner checkpoint because the owner is reading the legal pages anyway. Must be reconciled before cutover — two different warranty promises on one site is the kind of thing a customer quotes back at you. | open |  | 2026-09-20T16:00:59.898Z |  |

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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.325Z",
    "resolved_at": "2026-08-06T20:55:27.306Z"
  },
  {
    "id": 5,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/components.css",
    "line": null,
    "description": "Hero stack height at 360x640 not re-measured after the CR-01 badge-margin fix: no Chrome/Chromium/Playwright on the build machine and Safari remote automation is disabled, so the comment records 241.6px as DERIVED (249.6 minus the 8px margin delta), not measured",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T13:58:23.175Z",
    "resolved_at": "2026-08-06T20:55:13.586Z"
  },
  {
    "id": 6,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/base.css",
    "line": null,
    "description": "Keyboard focus-ring human-check (six dark-surface CTAs, both themes, plus the light-surface CTA staying navy) not observed in a browser — ratios are computed, the rendered ring is not yet seen",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T13:58:23.244Z",
    "resolved_at": "2026-08-06T20:55:27.370Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.452Z",
    "resolved_at": "2026-08-06T20:55:27.435Z"
  },
  {
    "id": 9,
    "kind": "unrun-verify",
    "phase": "02",
    "file": "src/css/no-js.css",
    "line": null,
    "description": "UI-SPEC 'overflow' backstop RE-OPENED by 02-06's desktop no-script layout: scrollWidth <= innerWidth with scripting disabled at 900px and 1440px is unmeasured. Abstains to human_needed; must not be recorded as passing.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T14:09:24.518Z",
    "resolved_at": "2026-08-06T20:55:27.500Z"
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
  },
  {
    "id": 12,
    "kind": "deviation",
    "phase": "02",
    "file": "scripts/probes/contrast.js",
    "line": null,
    "description": "contrast.js exports { HELPERS } and is not a runnable probe, but 02-09 Task 3 and the phase docs invoke it via render-check.sh (probe.run is not a function); the trust-badge 10.14:1 baseline has no committed probe that reproduces it",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-09T14:16:43.110Z",
    "resolved_at": null
  },
  {
    "id": 13,
    "kind": "unrun-verify",
    "phase": "03",
    "file": "scripts/seo-metadata-check.js",
    "line": null,
    "description": "03-09 live gate NOT RUN: deploy unavailable in executor context, so the 11 tuned pages still serve pre-plan metadata. 'node scripts/seo-metadata-check.js --live' currently reports served-matches-source on exactly those 11. Deploy then re-run to close.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-08-26T08:07:31.628Z",
    "resolved_at": null
  },
  {
    "id": 14,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/contact-form.php",
    "line": null,
    "description": "04-02 Task 3: honeypot autofill false-positive UNCONFIRMED. The human's manual handset submission succeeded, but they did not state whether browser autofill/password-manager was active, which is the plan's named number-one honeypot false-positive source. A false positive silently discards a real enquiry and is invisible to both parties. Re-test with autofill explicitly on.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T10:55:10.721Z",
    "resolved_at": null
  },
  {
    "id": 15,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/notify.php",
    "line": null,
    "description": "04-02: the notification FAILURE path has never been exercised. Every live check drove the success branch; no check made api.telegram.org unreachable, so the visitor-facing 'every channel failed' page and the error_log correlation-id branch in contact-send.php are unproven at runtime. 04-05 adds a second driver and should exercise this while it is there.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T10:55:16.705Z",
    "resolved_at": null
  },
  {
    "id": 16,
    "kind": "deviation",
    "phase": "04",
    "file": ".planning/phases/04-hardening-cutover/04-02-PLAN.md",
    "line": null,
    "description": "04-02 verify V7 is NOT PORTABLE and reports a false failure on macOS: 'grep -L PATTERN FILE | wc -l | grep -qx 0' never matches on BSD wc, which pads its count to seven spaces then 0 (confirmed by od -c); GNU wc emits an unpadded 0 and the same check passes on Linux. The underlying condition was TRUE. Every later plan using the 'wc -l | grep -qx N' idiom has the same defect; use N=$(... | wc -l | tr -d ' '); [ \"$N\" = \"0\" ] instead.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T10:55:25.672Z",
    "resolved_at": null
  },
  {
    "id": 17,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/upload.php",
    "line": null,
    "description": "04-03: the PORTRAIT-ORIENTATION path is unverified end to end, on BOTH sides. The server reads exif_read_data() Orientation before re-encoding and photo-resize.js passes imageOrientation:'from-image', but every fixture used in live testing was produced by macOS sips, which bakes rotation into pixels and writes NO orientation tag — and a file with no tag passes a completely broken pipeline identically to a correct one (P-7's named warning sign). Requires ONE portrait photograph taken on a real handset, submitted through kontakti.html, checked upright in Telegram. This is the plan's own backstop truth EA-07.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:11.837Z",
    "resolved_at": null
  },
  {
    "id": 18,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/notify.php",
    "line": null,
    "description": "04-03: nobody has LOOKED at the owner's phone. The host error_log carries no 'telegram transport failed' / 'sendPhoto failed' / 'telegram rejected the call' line for any of the 0/1/3-photo runs, so every API call returned ok:true and the photos reached Telegram's servers — but that is an assertion about the API's answer, not about what rendered. Unconfirmed: the single photo shows the enquiry as its caption, and the three arrive as ONE media group rather than three separate messages. One human glance closes it.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:20.639Z",
    "resolved_at": null
  },
  {
    "id": 19,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/upload-selftest.php",
    "line": null,
    "description": "04-03: scripts/upload-selftest.php has NEVER BEEN EXECUTED, in either direction. It was authored before includes/upload.php as the plan's tdd=true RED half, but the build machine has no php binary and no running Docker daemon, so RED was never observed failing and GREEN was never observed passing. The eight behaviours it encodes are a specification, not a gate. Six of them are covered indirectly by the live server checks in 04-03-SUMMARY; the orientation one is not covered at all. Run 'php scripts/upload-selftest.php' the moment a PHP runtime exists.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:27.963Z",
    "resolved_at": null
  },
  {
    "id": 20,
    "kind": "deviation",
    "phase": "04",
    "file": "src/js/photo-resize.js",
    "line": null,
    "description": "04-03: photo-resize.js measures 2042 B gzipped against a 2048 B gate — SIX bytes of headroom. The UI-SPEC 2 KB budget and this tree's comment convention are in direct conflict for JS, because deploy-new.sh comment-strips CSS but not JS, so prose in a .js file is wire cost while prose in a .css file is free. The file's comments were cut to five to fit and the reasoning moved into 04-03-SUMMARY.md. The real fix is to route .js through scripts/lib/ a comment stripper in deploy-new.sh the same way CSS goes through strip-css-comments.py; until then the next comment added to this file breaks the gate.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:35.755Z",
    "resolved_at": null
  },
  {
    "id": 21,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/upload.php",
    "line": null,
    "description": "04-03: the WebP decode branch is unexercised. The form advertises accept=image/jpeg,image/png,image/webp and torin_normalise_upload() routes WebP through imagecreatefromwebp() behind a function_exists guard, but no WebP was ever submitted. GD's WebP decoder is a separate build option from ext-gd itself, which the probe measured; if it is absent, every WebP the picker happily offers is refused with 'файлът не е разпознат като снимка'. Either submit one WebP, or drop webp from the accept list so the copy stops advertising it.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:43.288Z",
    "resolved_at": null
  },
  {
    "id": 22,
    "kind": "deviation",
    "phase": "04",
    "file": "src/contact-send.php",
    "line": null,
    "description": "04-03: a refused photograph still costs the visitor their typed description. Per-file rejection reasons DO surface as a field-level error on the photo control (T-04-17, measured: 'Снимка 1: файлът не е разпознат като снимка' and 'Може да прикачите най-много 5 снимки, а са приложени 6'), but contact-send.php renders the 04-02 honest-failure page rather than re-rendering the form, so the device model and fault description are lost. torin_render_contact_form() already accepts and escapes $values; 04-05 owns wiring the re-render and should route the photos branch through it.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:51.426Z",
    "resolved_at": null
  },
  {
    "id": 23,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/uslovia.html",
    "line": null,
    "description": "04-04: EVERY live gate in this plan is unrun — six of them, all blocked by one cause and all closed by one action. scripts/deploy-new.sh and php are both denied to subagents by the permission classifier, so neither a staging deploy nor a local PHP render was possible from the executor. The six: (1) homepage carries the new label 3x with zero PHP warnings; (2) no horizontal overflow at 360x640 plus the measured call-bar button width via scripts/render-check.sh scripts/probes/svc-page.js; (3) uslovia.html returns 200 with zero PHP warnings; (4) uslovia.html body is Cyrillic; (5) uslovia.html names the processor (grep Umami); (6) no new SEO metadata failure for uslovia via node scripts/seo-metadata-check.js --live. This is an environment boundary, not a defect, and is NOT a reason to treat these as passing. Deploy the seven named paths, then run all six.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:35.305Z",
    "resolved_at": null
  },
  {
    "id": 24,
    "kind": "deviation",
    "phase": "04",
    "file": "src/uslovia.html",
    "line": null,
    "description": "04-04: the analytics disclosure on uslovia.html names a processor the site does not yet load. grep -rn 'umami' src/ returns nothing — there is no js/analytics.js and no Umami script tag as of this plan. The disclosure is correct AT CUTOVER, when 04-07 wires the tracker, and inaccurate in the other direction until then. Staging carries X-Robots-Tag: noindex so nothing is publicly committed yet, and a boxed comment at the block states the coupling and says to DELETE the block if analytics is dropped. THIS BLOCK MUST NOT REACH PRODUCTION AHEAD OF THE TRACKER — 04-07 and 04-10 both need to honour that ordering.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:42.305Z",
    "resolved_at": null
  },
  {
    "id": 25,
    "kind": "stub",
    "phase": "04",
    "file": "src/uslovia.html",
    "line": null,
    "description": "04-04: the device-data commitment is ABSENT BY CHOICE. UI-SPEC S11 sketched a terms block covering what happens to a customer's data on a device left for repair; OWNER-QUESTIONS #27 is open, so no sentence was written — publishing a plausible-sounding commitment nobody agreed to is the exact failure mode threat T-04-18 names. Recorded in the page's header comment so the gap is visible to the next reader rather than silently missing. Needs the owner's answer, then a block written to match it.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:48.141Z",
    "resolved_at": null
  },
  {
    "id": 26,
    "kind": "stub",
    "phase": "04",
    "file": "src/remont-na-portove.html",
    "line": null,
    "description": "04-04: carried-forward ROADMAP item 3 residue is untouched, as plan 04-04 directed. The unconfirmed symptoms line on remont-na-portove.html and the three owner questions behind it are deliberately not closed here. Raise at the 04-04 Task 3 owner checkpoint (he is reading the legal pages anyway) and record the answers in OWNER_ANSWERS.md.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:53.877Z",
    "resolved_at": null
  },
  {
    "id": 27,
    "kind": "deviation",
    "phase": "04",
    "file": "src/warrently.html",
    "line": null,
    "description": "04-04: the warranty-term rider is still open and is a published contradiction. The battery warranty term stated on za-bateriite.html and the one-month general term on warrently.html disagree (OWNER-QUESTIONS #23). Not 04-04's to fix; folded into its Task 3 owner checkpoint because the owner is reading the legal pages anyway. Must be reconciled before cutover — two different warranty promises on one site is the kind of thing a customer quotes back at you.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:59.898Z",
    "resolved_at": null
  }
]
````
