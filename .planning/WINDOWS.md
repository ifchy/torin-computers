---
schema_version: 1
open_count: 30
waived_count: 3
fixed_count: 23
total_count: 56
last_updated: 2026-09-24T07:05:00.000Z
---

# Broken Windows Ledger

> Cross-phase defect register. `/gsd-ship` blocks while `open_count > 0`.
> Waive with `gsd-tools windows waive <id> "<reason>"` (reason required).
> Mark fixed with `gsd-tools windows fixed <id>`.

| id | phase | kind | file | line | description | status | reason | recorded_at | resolved_at |
|----|-------|------|------|------|-------------|--------|--------|-------------|-------------|
| 1 | 02 | stub | src/includes/category-page.php |  | Six template slots (intro, warranty/TRUST-03, process, FAQ, related, prices) render nothing on all three category pages — content is Phase 3 per D-25 | fixed |  | 2026-08-06T03:41:56.147Z | 2026-09-22T06:48:25.799Z |
| 2 | 02 | stub | src/index.html |  | DIFF-02 (battery regeneration) ships inside a collapsed disclosure — knowingly unmet, must not pass silently in Phase 3 verification (D-13 / OWNER-QUESTIONS #9) | waived | DIFF-02 retired 2026-09-11 (REQUIREMENTS.md:29, ROADMAP.md:18) - battery regeneration discontinued by the business. No regeneration claim remains in src/index.html. Entry guards a requirement that no longer exists. | 2026-08-06T03:41:56.205Z | 2026-09-22T06:48:33.869Z |
| 3 | 02 | stub | src/includes/site-config.php |  | hours, viber and notice are [ASSUMED] — OWNER-QUESTIONS #20/#21/#8 block the Phase 4 cutover | fixed |  | 2026-08-06T03:41:56.265Z | 2026-09-20T16:29:03.826Z |
| 4 | 02 | stub | src/js/site.js |  | JS-disabled nav gap: with script blocked the six category links are unreachable from the nav; mitigated by the homepage card grid, accepted not solved | fixed |  | 2026-08-06T03:41:56.325Z | 2026-08-06T20:55:27.306Z |
| 5 | 02 | unrun-verify | src/css/components.css |  | Hero stack height at 360x640 not re-measured after the CR-01 badge-margin fix: no Chrome/Chromium/Playwright on the build machine and Safari remote automation is disabled, so the comment records 241.6px as DERIVED (249.6 minus the 8px margin delta), not measured | fixed |  | 2026-08-06T13:58:23.175Z | 2026-08-06T20:55:13.586Z |
| 6 | 02 | unrun-verify | src/css/base.css |  | Keyboard focus-ring human-check (six dark-surface CTAs, both themes, plus the light-surface CTA staying navy) not observed in a browser — ratios are computed, the rendered ring is not yet seen | fixed |  | 2026-08-06T13:58:23.244Z | 2026-08-06T20:55:27.370Z |
| 7 | 02 | unrun-verify | src/css/components.css |  | FOUT/web-font-swap backstop re-opened by 02-05: the hero stack changed by 8px, so the Sofia Sans fallback-reflow check against hero CTA displacement on a throttled connection is unclosed, not inherited | open |  | 2026-08-06T13:58:23.302Z |  |
| 8 | 02 | unrun-verify | src/css/no-js.css |  | No-script rendered nav human-check unrun: five top-level items + six category links visible/activatable at 360/900/1440px, and neither disclosure control visible or Tab-reachable. No automatable browser on the build machine. | fixed |  | 2026-08-06T14:09:24.452Z | 2026-08-06T20:55:27.435Z |
| 9 | 02 | unrun-verify | src/css/no-js.css |  | UI-SPEC 'overflow' backstop RE-OPENED by 02-06's desktop no-script layout: scrollWidth <= innerWidth with scripting disabled at 900px and 1440px is unmeasured. Abstains to human_needed; must not be recorded as passing. | fixed |  | 2026-08-06T14:09:24.518Z | 2026-08-06T20:55:27.500Z |
| 10 | 02 | deviation | src/css/no-js.css |  | Residual, not closed by 02-06: scripting ENABLED but site.js failing to load/throw leaves the nav hidden below 56.25rem. Closing it needs a scripting-capability marker written before first paint, which the project deliberately does not have. | open |  | 2026-08-06T14:09:24.578Z |  |
| 11 | 02 | deviation | src/css/no-js.css |  | 02-06 desktop no-script row shape: 'flex: 1 0 100%' on .nav__item--has-sub (plan-mandated, grep-asserted) splits the four visible top-level links across two wrapped rows rather than one, because the has-sub item sits mid-list. Navigable and in-flow, but the plan's human-check phrasing 'five top-level items still read as a horizontal row' is NOT satisfied as worded. Open. | open |  | 2026-08-06T14:09:24.636Z |  |
| 12 | 02 | deviation | scripts/probes/contrast.js |  | contrast.js exports { HELPERS } and is not a runnable probe, but 02-09 Task 3 and the phase docs invoke it via render-check.sh (probe.run is not a function); the trust-badge 10.14:1 baseline has no committed probe that reproduces it | open |  | 2026-08-09T14:16:43.110Z |  |
| 13 | 03 | unrun-verify | scripts/seo-metadata-check.js |  | 03-09 live gate NOT RUN: deploy unavailable in executor context, so the 11 tuned pages still serve pre-plan metadata. 'node scripts/seo-metadata-check.js --live' currently reports served-matches-source on exactly those 11. Deploy then re-run to close. | fixed |  | 2026-08-26T08:07:31.628Z | 2026-09-21T18:56:38.709Z |
| 14 | 04 | unrun-verify | src/includes/contact-form.php |  | 04-02 Task 3: honeypot autofill false-positive UNCONFIRMED. The human's manual handset submission succeeded, but they did not state whether browser autofill/password-manager was active, which is the plan's named number-one honeypot false-positive source. A false positive silently discards a real enquiry and is invisible to both parties. Re-test with autofill explicitly on. | open |  | 2026-09-20T10:55:10.721Z |  |
| 15 | 04 | unrun-verify | src/includes/notify.php |  | 04-02: the notification FAILURE path has never been exercised. Every live check drove the success branch; no check made api.telegram.org unreachable, so the visitor-facing 'every channel failed' page and the error_log correlation-id branch in contact-send.php are unproven at runtime. 04-05 adds a second driver and should exercise this while it is there. | open |  | 2026-09-20T10:55:16.705Z |  |
| 16 | 04 | deviation | .planning/phases/04-hardening-cutover/04-02-PLAN.md |  | 04-02 verify V7 is NOT PORTABLE and reports a false failure on macOS: 'grep -L PATTERN FILE \| wc -l \| grep -qx 0' never matches on BSD wc, which pads its count to seven spaces then 0 (confirmed by od -c); GNU wc emits an unpadded 0 and the same check passes on Linux. The underlying condition was TRUE. Every later plan using the 'wc -l \| grep -qx N' idiom has the same defect; use N=$(... \| wc -l \| tr -d ' '); [ "$N" = "0" ] instead. | fixed |  | 2026-09-20T10:55:25.672Z | 2026-09-22T06:48:25.867Z |
| 17 | 04 | unrun-verify | src/includes/upload.php |  | 04-03: the PORTRAIT-ORIENTATION path is unverified end to end, on BOTH sides. The server reads exif_read_data() Orientation before re-encoding and photo-resize.js passes imageOrientation:'from-image', but every fixture used in live testing was produced by macOS sips, which bakes rotation into pixels and writes NO orientation tag — and a file with no tag passes a completely broken pipeline identically to a correct one (P-7's named warning sign). Requires ONE portrait photograph taken on a real handset, submitted through kontakti.html, checked upright in Telegram. This is the plan's own backstop truth EA-07. | open |  | 2026-09-20T11:52:11.837Z |  |
| 18 | 04 | unrun-verify | src/includes/notify.php |  | 04-03: nobody has LOOKED at the owner's phone. The host error_log carries no 'telegram transport failed' / 'sendPhoto failed' / 'telegram rejected the call' line for any of the 0/1/3-photo runs, so every API call returned ok:true and the photos reached Telegram's servers — but that is an assertion about the API's answer, not about what rendered. Unconfirmed: the single photo shows the enquiry as its caption, and the three arrive as ONE media group rather than three separate messages. One human glance closes it. | open |  | 2026-09-20T11:52:20.639Z |  |
| 19 | 04 | unrun-verify | scripts/upload-selftest.php |  | 04-03: scripts/upload-selftest.php has NEVER BEEN EXECUTED, in either direction. It was authored before includes/upload.php as the plan's tdd=true RED half, but the build machine has no php binary and no running Docker daemon, so RED was never observed failing and GREEN was never observed passing. The eight behaviours it encodes are a specification, not a gate. Six of them are covered indirectly by the live server checks in 04-03-SUMMARY; the orientation one is not covered at all. Run 'php scripts/upload-selftest.php' the moment a PHP runtime exists. | fixed |  | 2026-09-20T11:52:27.963Z | 2026-09-22T10:06:08.435Z |
| 20 | 04 | deviation | src/js/photo-resize.js |  | 04-03: photo-resize.js measures 2042 B gzipped against a 2048 B gate — SIX bytes of headroom. The UI-SPEC 2 KB budget and this tree's comment convention are in direct conflict for JS, because deploy-new.sh comment-strips CSS but not JS, so prose in a .js file is wire cost while prose in a .css file is free. The file's comments were cut to five to fit and the reasoning moved into 04-03-SUMMARY.md. The real fix is to route .js through scripts/lib/ a comment stripper in deploy-new.sh the same way CSS goes through strip-css-comments.py; until then the next comment added to this file breaks the gate. | open |  | 2026-09-20T11:52:35.755Z |  |
| 21 | 04 | unrun-verify | src/includes/upload.php |  | 04-03: the WebP decode branch is unexercised. The form advertises accept=image/jpeg,image/png,image/webp and torin_normalise_upload() routes WebP through imagecreatefromwebp() behind a function_exists guard, but no WebP was ever submitted. GD's WebP decoder is a separate build option from ext-gd itself, which the probe measured; if it is absent, every WebP the picker happily offers is refused with 'файлът не е разпознат като снимка'. Either submit one WebP, or drop webp from the accept list so the copy stops advertising it. | open |  | 2026-09-20T11:52:43.288Z |  |
| 22 | 04 | deviation | src/contact-send.php |  | 04-03: a refused photograph still costs the visitor their typed description. Per-file rejection reasons DO surface as a field-level error on the photo control (T-04-17, measured: 'Снимка 1: файлът не е разпознат като снимка' and 'Може да прикачите най-много 5 снимки, а са приложени 6'), but contact-send.php renders the 04-02 honest-failure page rather than re-rendering the form, so the device model and fault description are lost. torin_render_contact_form() already accepts and escapes $values; 04-05 owns wiring the re-render and should route the photos branch through it. | fixed |  | 2026-09-20T11:52:51.426Z | 2026-09-21T16:18:13.827Z |
| 23 | 04 | unrun-verify | src/uslovia.html |  | 04-04: EVERY live gate in this plan is unrun — six of them, all blocked by one cause and all closed by one action. scripts/deploy-new.sh and php are both denied to subagents by the permission classifier, so neither a staging deploy nor a local PHP render was possible from the executor. The six: (1) homepage carries the new label 3x with zero PHP warnings; (2) no horizontal overflow at 360x640 plus the measured call-bar button width via scripts/render-check.sh scripts/probes/svc-page.js; (3) uslovia.html returns 200 with zero PHP warnings; (4) uslovia.html body is Cyrillic; (5) uslovia.html names the processor (grep Umami); (6) no new SEO metadata failure for uslovia via node scripts/seo-metadata-check.js --live. This is an environment boundary, not a defect, and is NOT a reason to treat these as passing. Deploy the seven named paths, then run all six. | open |  | 2026-09-20T16:00:35.305Z |  |
| 24 | 04 | deviation | src/uslovia.html |  | 04-04: the analytics disclosure on uslovia.html names a processor the site does not yet load. grep -rn 'umami' src/ returns nothing — there is no js/analytics.js and no Umami script tag as of this plan. The disclosure is correct AT CUTOVER, when 04-07 wires the tracker, and inaccurate in the other direction until then. Staging carries X-Robots-Tag: noindex so nothing is publicly committed yet, and a boxed comment at the block states the coupling and says to DELETE the block if analytics is dropped. THIS BLOCK MUST NOT REACH PRODUCTION AHEAD OF THE TRACKER — 04-07 and 04-10 both need to honour that ordering. | fixed |  | 2026-09-20T16:00:42.305Z | 2026-09-21T18:08:50.367Z |
| 25 | 04 | stub | src/uslovia.html |  | 04-04: the device-data commitment is ABSENT BY CHOICE. UI-SPEC S11 sketched a terms block covering what happens to a customer's data on a device left for repair; OWNER-QUESTIONS #27 is open, so no sentence was written — publishing a plausible-sounding commitment nobody agreed to is the exact failure mode threat T-04-18 names. Recorded in the page's header comment so the gap is visible to the next reader rather than silently missing. Needs the owner's answer, then a block written to match it. | open |  | 2026-09-20T16:00:48.141Z |  |
| 26 | 04 | stub | src/remont-na-portove.html |  | 04-04: carried-forward ROADMAP item 3 residue is untouched, as plan 04-04 directed. The unconfirmed symptoms line on remont-na-portove.html and the three owner questions behind it are deliberately not closed here. Raise at the 04-04 Task 3 owner checkpoint (he is reading the legal pages anyway) and record the answers in OWNER_ANSWERS.md. | open |  | 2026-09-20T16:00:53.877Z |  |
| 27 | 04 | deviation | src/warrently.html |  | 04-04: the warranty-term rider is still open and is a published contradiction. The battery warranty term stated on za-bateriite.html and the one-month general term on warrently.html disagree (OWNER-QUESTIONS #23). Not 04-04's to fix; folded into its Task 3 owner checkpoint because the owner is reading the legal pages anyway. Must be reconciled before cutover — two different warranty promises on one site is the kind of thing a customer quotes back at you. | waived | Stale on three counts: OWNER-QUESTIONS #23 ANSWERED 2026-09-11 (1 month all repairs, except category 6); za-bateriite.html deleted from the tree in Phase 3.5; site-config.php now implements exactly that answered rule (default + nonstandard exclusion). Recorded during 04-04 after the question was already answered. | 2026-09-20T16:00:59.898Z | 2026-09-22T06:48:33.934Z |
| 28 | 04 | unrun-verify | src/includes/settings.php |  | 04-06 (G1): EVERY live gate in this plan is unrun — same single cause as ledger entry 23, closed by the same single action. deploy-new.sh is denied to subagents (confirmed with the TORIN_CRED_FILE worktree override in place) and no php binary exists on the build machine; the orchestrator independently confirmed 'command -v php' is empty and no Docker daemon is running, so this is an absent runtime, not just a permission denial. Nothing in this plan reached the server. The six unrun gates: (1) a deliberately corrupted settings.txt on staging leaving all 20 pages at HTTP 200 with zero warnings, the corrupted key on its default and EVERY OTHER KEY still applied, both served bodies recorded; (2) settings.txt, .user.ini and php.fcgi each returning 403 or 404 over HTTP — the deny block is written and module-guarded but has never been exercised against a live Apache, and an .htaccess mistake in this tree has historically been a whole-subtree 500; (3) zero PHP warnings across the 20 served pages after the site-config.php / jsonld.php / footer.php changes; (4) banner absent by default on the served homepage, present exactly once on two pages with a current closure, gone with an expired one, present on its end date; (5) the one-edit-three-locations proof, with after values to pair with the recorded before values; (6) three/four/three openingHoursSpecification entries on the served page. | open |  | 2026-09-20T16:28:18.395Z |  |
| 29 | 04 | unrun-verify | scripts/settings-selftest.php |  | 04-06 (G2): scripts/settings-selftest.php has NEVER BEEN EXECUTED, in either direction. 22 assertions covering the behaviour block, the date gate and the structured-data entry counts, authored against a machine with no PHP and no running Docker daemon — exactly as scripts/upload-selftest.php was in 04-03 (ledger entry 19). A test that has never run is a specification, not a gate. Run 'php scripts/settings-selftest.php' the moment a PHP runtime exists; it is the fastest way to close most of G1's logic half without a deploy. | fixed |  | 2026-09-20T16:28:24.222Z | 2026-09-22T10:06:08.500Z |
| 30 | 04 | unrun-verify | src/includes/settings.php |  | 04-06 (G3) — HIGHEST-PRIORITY PRE-DEPLOY ACTION: no PHP file in this plan has been syntax-checked. 'php -l' was unavailable (orchestrator re-confirmed: no php binary, no Docker daemon). FIVE files gained or changed PHP — settings.php, banner.php, site-config.php, jsonld.php, footer.php — and a parse error in any one of them takes down all twenty pages at once, which is the precise failure mode this plan exists to prevent, arriving from the other direction. Run 'php -l' on all five before or immediately after the first deploy, and DEPLOY THEM TOGETHER rather than one at a time: these files include each other, so a partial upload can leave the site referencing a function that has not landed yet. | fixed |  | 2026-09-20T16:28:30.651Z | 2026-09-22T10:06:08.559Z |
| 31 | 04 | deviation | src/includes/banner.php |  | 04-06 (G4) — OPEN OWNER QUESTION created by this plan. The closure strip does not appear until the closure actually starts (Decisions #3). The UI contract's phrasing would have shown it from the moment the dates were set, i.e. as advance notice. One comparison either way and it should be the owner's call, but he has not been asked. Raise at the 04-10 owner checkpoint. Two related plan deviations argued in 04-06-SUMMARY rather than quietly applied: hours_days is an allowlisted token rather than free text bounded at 20 chars (the plan's own headline truth is unreachable otherwise), and the banner message cap is 120 code points rather than 300 characters (the 180px backstop is arithmetically unmeetable at 300). | open |  | 2026-09-20T16:28:37.121Z |  |
| 32 | 04 | unrun-verify | src/settings.txt.example |  | 04-06 (G6): the cPanel File Manager round-trip has NEVER BEEN PERFORMED. The Bulgarian guide (docs/naruchnik-nastroyki.md) tells the owner to copy settings.txt.example, rename it, and edit it in cPanel with UTF-8 encoding — nobody has done this even once. The realistic failure is the panel's editor writing a BOM or a non-UTF-8 encoding; the parser rejects non-UTF-8 values per-key so the failure mode is SAFE, but it would be SILENT: the owner sees his edit ignored with no explanation. One human performing the procedure once, with the guide open, closes both this and the D5 human-judgment item. | open |  | 2026-09-20T16:28:42.942Z |  |
| 33 | 04 | stub | src/includes/site-config.php |  | 04-06 (G5) — NARROWS AND SUPERSEDES LEDGER ENTRY 3. Entry 3 covered three [ASSUMED] markers in site-config.php: hours, viber and notice. TWO ARE NOW RESOLVED — hours is closed by D4-25 and plan 04-06 (one source, four consumers); viber was removed in 04-04 when the chat button was retired from all five CTA slots. ONE REMAINS: the 'notice' marker. OWNER-QUESTIONS #8 asks whether the footer band should exist at all and still has no answer. Its CONTENT is no longer unconfirmed — it is composed from the confirmed hours — but the EXISTENCE question is untouched, and 04-06 deliberately left the marker in place rather than promoting it because it merely looked settled. This narrowed entry is the live one; entry 3 is marked fixed and should be read as superseded by this. | fixed |  | 2026-09-20T16:29:00.052Z | 2026-09-22T14:47:58.019Z |
| 34 | 04 | unrun-verify | src/includes/spam-guard.php |  | 04-05 Task 2: nothing in the spam guard has been executed by a PHP interpreter, in either direction. No php binary and no running Docker daemon on the build machine (orchestrator-confirmed), and deploy-new.sh is denied to subagents, so neither a local render nor a staging deploy was possible. Balance checks and greps are STRUCTURAL and do not prove the file parses. Three live gates are unrun: (a) a POST with a forged timestamp returns no 5xx and produces no notification; (b) a valid submission succeeds and an identical repeat inside the window is rejected, both with timestamps; (c) each rejection produces exactly one correlation line in the host error log with NO submitted field value in it. Run all three the moment the code reaches /new/. This is an environment boundary, not evidence of correctness. | open |  | 2026-09-21T06:08:35.337Z |  |
| 35 | 04 | unrun-verify | src/includes/spam-guard.php |  | 04-05 Task 2: the tdd=true RED/GREEN cycle was NOT performed. With no PHP runtime there is no way to observe a failing test, and this run's scope was limited to three named source files, so no scripts/spam-guard-selftest.php was authored either. The same shape as scripts/upload-selftest.php (ledger 19) and scripts/settings-selftest.php (ledger 29) would close it: nine assertions matching the plan's behavior list, runnable as 'php scripts/spam-guard-selftest.php' once a runtime exists. Three selftests now await the same single missing dependency. | fixed |  | 2026-09-21T06:08:41.039Z | 2026-09-22T10:25:22.948Z |
| 36 | 04 | deviation | src/includes/spam-guard.php |  | 04-05 Task 2: the HMAC signing key and the throttle records live in sys_get_temp_dir(), matching the precedent upload.php:178-194 set for visitor photographs. On a host whose temp directory is genuinely shared between accounts, a neighbouring tenant could read the key or pre-create the file; the exclusive-create mode and symlink refusal close the cheap version of that attack, not the expensive one. Worth ONE LINE of confirmation from the hosting panel that this account has a private temp directory. Second-order effect worth knowing: a temp-dir change or a system reaper ROTATES the key, which makes in-flight forms verify as 'forged' — those get the honest retry page, so the failure is visible and recoverable rather than silent. | open |  | 2026-09-21T06:08:47.618Z |  |
| 37 | 04 | deviation | src/includes/spam-guard.php |  | 04-05 Task 2: the throttle is keyed on REMOTE_ADDR, so a whole office or a mobile carrier behind one NAT address shares a quota of one delivered enquiry per fifteen minutes. The rejection message names the shop's telephone number in the same sentence, so a throttled visitor is never left without a route — but this shape must be understood before the window is ever tightened. Related design call, deliberate and documented: the throttle records DELIVERED ENQUIRIES, not POSTs (via torin_rate_limit_record() inside the success branch), because counting every POST would throttle a visitor correcting a validation error or retrying after a page that told them to retry. | open |  | 2026-09-21T06:08:54.267Z |  |
| 38 | 04 | unrun-verify | src/includes/notify.php |  | 04-05 Task 3 — CASE (a) IS NOW PROVEN LIVE; CASES (b) AND (c) REMAIN. The SMTP-to-sendmail fall-through is the functional heart of Task 3. On 2026-09-22 the owner submitted the real form and all three channels delivered: Telegram message, shop email, customer confirmation. public_html/new/error_log, correlation id f4e59592, 19:33:47-19:33:50 Europe/Sofia, shows BOTH mail legs taking the shipped no-credential path exactly as case (a) predicted — the line "mail no smtp credential, using sendmail" then "mail delivered via=sendmail", twice, once per leg (owner notification + customer confirmation), with photos=0 leftover=0 and NO telegram failure line. THE EXACTLY-ONCE CONDITION HOLDS: two legs, one delivery each, no duplicate. Whole cascade ~3s. STILL UNRUN, and these are the cases that actually exercise a FAILURE falling through: (b) WRONG CREDENTIAL — set smtp_password to a bad value in /home/torin/torin-secrets.php, submit, expect "mail smtp failed before acceptance, falling through to sendmail" then "mail delivered via=sendmail", a 303, and the email to arrive; (c) CORRECT CREDENTIAL — expect "mail delivered via=smtp" and NO fall-through line. IN BOTH, THE ENQUIRY MUST ARRIVE EXACTLY ONCE. Two copies means the pre/post-acceptance boundary is wrong, which is the single outcome this design exists to prevent. The boundary observes the relay literal 354 reply rather than parsing PHPMailer localised exception text; ambiguity deliberately resolves to DO NOT RETRY. | open |  | 2026-09-21T16:17:38.259Z |  |
| 39 | 04 | deviation | src/msg.html |  | 04-05 CROSS-PLAN DEPENDENCY, 04-08 MUST READ THIS: the $torin_robots assignment on msg.html is INERT. The emitter that reads the variable ships in 04-07; header.php does not read it yet. The assignment is in place so 04-07's emitter finds it. IF 04-07 SLIPS OR IS REPLANNED (its Task 1 is a blocking owner gate needing an Umami account and a website id, so this is a live risk), msg.html ships WITHOUT its noindex — and 04-08 must then ensure the sitemap does not list it, since the only other protection against the confirmation page being indexed and entered from search (threat T-04-39) would be gone. 04-08 owns the sitemap. | fixed |  | 2026-09-21T16:17:45.758Z | 2026-09-21T18:08:50.442Z |
| 40 | 04 | unrun-verify | scripts/notify-selftest.php |  | NARROWED 2026-09-24. The selftest half is DISCHARGED: a PHP 8.5.10 runtime now exists and `php scripts/notify-selftest.php` was executed for the first time - 9/9 assertions hold, including 'all channels down reports ok=false', 'telegram reports exactly false', 'mail reports exactly false' and 'the mail driver returns false rather than fatalling without it'. The all-channels-failed branch is therefore proven AT THE UNIT LEVEL, no longer merely encoded. WHAT REMAINS OPEN is the live end-to-end half, which the selftest cannot reach: point telegram_bot_token at a bad value AND make the mail leg fail, submit a real form, and check the PAGE returns 200 (not 5xx), shows the error band, PRESERVES the typed values, renders consent UNCHECKED, contains the phone number 02 9549710, and produces exactly one 'all notification channels failed (telegram=fail mail=fail)' log line carrying no submitted value. That is a rendered-page and log-side assertion, not a function-return assertion. Sibling entries 19 and 29 are now genuinely run (18/18 and 27/27); 35 (spam-guard-selftest) was never authored and remains the one selftest that does not exist. | open |  | 2026-09-21T16:17:52.642Z |  |
| 41 | 04 | deviation | src/includes/contact-form.php |  | 04-05: three small UI/privacy obligations left open because they fall outside this plan's file list, all cheap and all owned elsewhere. (a) ACCESSIBILITY — the error band is focusable but nothing focuses it. UI-SPEC C-8 asks for tabindex=-1 PLUS a programmatic .focus() on render; the attribute and id (#form-error) ship here, but the .focus() call belongs in js/analytics.js. A keyboard or screen-reader user currently lands at the top of the document instead of on the explanation. 04-07 owns that file. (b) ANALYTICS — the UI-SPEC C-9 'form-error' server-rendered event, with its field property, is not emitted by the re-render; 04-07 owns analytics. (c) PRIVACY COPY — uslovia.html does not mention the new customer confirmation email, which is an automated message to the visitor's address and therefore a processing purpose the privacy text should name. 04-04 owns uslovia.html and is parked at its owner sign-off gate, so fold this into that conversation. | fixed |  | 2026-09-21T16:18:00.387Z | 2026-09-21T18:08:50.508Z |
| 42 | 04 | unrun-verify | src/vendor/phpmailer/.htaccess |  | 04-05: CLOSED 2026-09-23. The vendored-library deny block is verified over HTTP on staging — vendor/phpmailer/PHPMailer.php, SMTP.php and Exception.php all return 403, so the deny applies and the library is not fetchable. The entry also carried an operational note: TWO messages leave per submission (owner notification plus customer confirmation), each on its own connection, so worst-case latency was worth one look once the form was in real use. Answered by the same live run that proved ledger 38 case (a) — correlation id f4e59592 completed both legs in ~3 seconds (19:33:47-19:33:50), with no SMTP timeout in the path because the shipped state carries no credential to try. Re-check timings if a credential is ever added. | fixed |  | 2026-09-21T16:18:07.475Z | 2026-09-23T06:12:00.000Z |
| 43 | 04 | deviation | src/js/analytics.js |  | 04-07: THE UI-SPEC JS BUDGET GATE FAILED, AND IS RECORDED AS FAILED RATHER THAN MADE GREEN. analytics.js gzips to 1959 B against a <=1024 B budget (orchestrator independently re-measured: 1959). The decisive measurement is that THE CODE ALONE IS 972 B — inside budget — and the COMMENTS ARE THE ENTIRE OVERAGE. There is no build step in this project, so comments are wire bytes. The executor declined to strip them to make the number go green, on the grounds that the one file whose purpose is preventing a regression a desktop test cannot catch is the worst place to ship undocumented; the orchestrator endorses that call. Contributing cause: the budget was written for 'a delegated listener and an event map', but the file also carries four funnel handlers, two server-declared event readers, closed-set enforcement and the C-8 focus call — the last three assigned AFTER the budget existed. At 1024 B there are 52 bytes left for comments. THIS IS THE SECOND FILE TO HIT THIS EXACT WALL: ledger 20 records photo-resize.js at 2042 B against 2048 B, six bytes of headroom, same root cause. Three resolutions, pick one: (a) accept and raise the figure to ~2.0 KB; (b) implement deploy-time JS comment stripping in deploy-new.sh, the way CSS already goes through strip-css-comments.py — this would bring analytics.js to ~972 B and simultaneously give photo-resize.js real headroom, and is now justified by two files rather than one; (c) strip by hand and lose the documentation. RECOMMENDED: (b). | open |  | 2026-09-21T18:08:17.794Z |  |
| 44 | 04 | deviation | src/js/analytics.js |  | 04-07: the C-8 error-band .focus() call lives in analytics.js, and that is a fragile home for an ACCESSIBILITY behaviour. Content blockers commonly match the filename 'analytics.js' by pattern; a blocked file means the error-band focus silently stops working for exactly those users, so a keyboard or screen-reader user with a blocker lands at the top of the document instead of on the explanation of what went wrong. src/js/site.js would be immune and is the right home for accessibility behaviour. This is a TWO-LINE MOVE. The executor flagged it rather than deviating unilaterally, because the orchestrator had directed the file explicitly (carried from ledger 41a, which itself inherited the location from 04-05's summary) — the orchestrator now endorses the move. Do it as a quick task before cutover; it is cheap and the failure mode is silent. | open |  | 2026-09-21T18:08:25.653Z |  |
| 45 | 04 | unrun-verify | scripts/deploy-new.sh |  | 04-07: THE TWO DELETED SCAFFOLDING FILES STILL EXIST ON THE SERVER. src/css/theme-a.css and src/includes/dev-switcher.php are gone from the tree (plan-mandated, verified: the branch deleted those two files and nothing else), but deploy-new.sh UPLOADS and never DELETES, so both remain live under /new/ until someone removes them by hand. dev-switcher.php is dev scaffolding that has been rendering on all 19 staging pages since Phase 2 — leaving it on the server after cutover would publish it. There is currently nowhere to record this: 04-CUTOVER-CHECKLIST.md does not exist yet; 04-09 creates it. 04-09 MUST carry a manual-removal step for these two paths. Related, same root cause: any future file deletion in this project has the same problem, so the checklist should name the class, not just these two files. | waived | Discharged by 04-09: 04-CUTOVER-CHECKLIST.md lists both paths at Section 3 items 8-9 and names the class at line 217 citing this entry. Underlying fact still true and re-confirmed 2026-09-22 (css/theme-a.css and includes/dev-switcher.php both return 200 on staging), but it is now tracked by the checklist gated deletion pass, so this entry is duplicate bookkeeping. | 2026-09-21T18:08:34.232Z | 2026-09-22T06:48:33.997Z |
| 46 | 04 | deviation | .planning/phases/04-hardening-cutover/04-07-PLAN.md |  | 04-07: the plan's files_modified declaration was incomplete — five files changed that it did not declare: src/contact-send.php, src/kontakti.html, src/css/base.css, src/css/components.css, src/includes/asset-version.php. Each change is defensible (contact-send.php carries the C-9 form-error server-rendered event the orchestrator explicitly assigned from ledger 41b; kontakti.html gained a seventh data-slot for three phone anchors 04-05 added after C-9's slot table was written; the two CSS files and asset-version.php are theme-a removal fallout). NO HARM OCCURRED — 04-07 ran alone in its wave. But files_modified is exactly what the orchestrator's intra-wave overlap check reads to decide whether two plans may run in parallel, so an under-declared list is a latent parallel-execution hazard, not a paperwork issue. Worth a planner-side note that files_modified must be updated when a plan absorbs carried-forward ledger work assigned after planning. | open |  | 2026-09-21T18:08:43.788Z |  |
| 47 | 04 | unrun-verify | src/includes/header.php |  | NARROWED 2026-09-24. The SYNTAX half - which this entry called the highest-risk unrun item in the phase - is DISCHARGED. A PHP 8.5.10 binary now exists on the build machine and `php -l` was run on header.php FIRST, exactly as this entry instructed: 'No syntax errors detected'. Then the whole tree: 22 .php files and 21 .html-as-PHP pages, 43 files total, ZERO parse errors. header.php - edited by 04-06, 04-07 and 04-08 without once being parsed, and included by every page - is clean, so the all-19-pages-at-once outage this entry predicted cannot fire from a parse error. Ledger 30's five 04-06 files are covered by the same run. Beyond syntax, all 21 pages were then EXECUTED under error_reporting=E_ALL via php -S and their served bodies scanned: 21/21 HTTP 200, zero rendered diagnostics of any severity. WHAT REMAINS OPEN is the deploy half: this entry's claim that every live number in 04-08-SUMMARY is a pre-deploy baseline has NOT been re-measured here, and robots.txt, sitemap.xml, the WebP siblings and the .htaccess still need their post-deploy verification at the live root. The standing instruction also still applies: deploy the PHP includes TOGETHER, not one at a time. | open |  | 2026-09-21T18:56:09.283Z |  |
| 48 | 04 | deviation | scripts/asset-version-check.sh |  | 04-08: FOUR GATES IN THIS PLAN DID NOT MEASURE WHAT THEY CLAIMED — a recurring class on this project, worth reading as a pattern rather than four incidents. (1) WORST: a cache assertion written as grep 'cache-control:.*max-age=3' matches BOTH the old value 300 AND the new 31536000, because it is a PREFIX match on the digit 3. It therefore reported green before the change and green after — a gate that could never fail. Replaced with an exact comparison. (2) grep -o '<img[^>]*>' breaks on PHP source because [^>]* stops at the > of ?>, so it reported 9 defects against markup that has none; re-checked with a real parser (2 tags, 0 defects). (3) and (4) two of the executor's own new comments inflated substring counts — the literal <loc> and the word sitemap written as prose — which it reworded rather than let a counter pass for the wrong reason. SAME FAMILY AS ledger 16 (BSD wc padding defeating 'wc -l \| grep -qx 0'). STANDING RULE for this project: never assert a numeric threshold with a substring or prefix grep; extract the value and compare it numerically. | fixed |  | 2026-09-21T18:56:19.412Z | 2026-09-22T06:48:25.930Z |
| 49 | 04 | deviation | .planning/phases/04-hardening-cutover/04-08-PLAN.md |  | 04-08: PLAN DEFECT, not a work defect — the plan's sitemap gate demands >= 20 URLs, but the correct number is 19 and the executor rightly refused to pad it. The arithmetic: 20 page files minus one deliberately noindexed (msg.html, excluded on purpose per threat T-04-39) equals 19, and all 19 return 200 live. Orchestrator independently confirmed post-merge: sitemap.xml contains exactly 19 <loc> entries and zero occurrences of msg.html. The gate figure was written before 04-07 introduced the robots emitter that makes msg.html noindex, so it counts a page the phase then decided to exclude. Treat 19 as correct and fix the gate, not the sitemap. Also recorded: two declared deviations outside files_modified, both justified — header.php (the logo <img> lives there and is the largest per-visit win) and scripts/asset-version-check.sh (its Check C asserted max-age <= 600 with failure text literally reading 'Phase 4 raises this, DESIGN-02', so left untouched it would fail forever once the new .htaccess lands). | open |  | 2026-09-21T18:56:29.519Z |  |
| 50 | 04 | unrun-verify | scripts/seo-metadata-check.js |  | SUPERSEDES LEDGER 13, WHICH THE ORCHESTRATOR CLOSED IN ERROR on 2026-09-21. Entry 13 recorded the 03-09 SEO live gate as unrun pending a deploy, and its own closing condition was literally 'Deploy then re-run to close'. It was marked fixed while processing 04-08 on the mistaken basis that 04-08 completed the SEO work — but 04-08 DEPLOYED NOTHING, as its own summary states plainly. THIS ENTRY IS THE LIVE ONE; treat 13 as still open despite its status. The condition is unchanged: the 11 tuned pages still serve pre-plan metadata on the origin, and 'node scripts/seo-metadata-check.js --live' still reports served-matches-source on exactly those 11 because it is measuring the currently-deployed build. Deploy, then re-run, then close THIS entry. Note the general trap this is an instance of: any check run against the live origin during this phase measures the OLD build, so a green result is evidence about the deployed site and says nothing about the work in the tree. | open |  | 2026-09-21T18:56:55.528Z |  |
| 51 | 04 | deviation | .planning/phases/04-hardening-cutover/04-CUTOVER-CHECKLIST.md |  | 04-09 CHECKLIST OMISSION found by the orchestrator, not the executor: the checklist instructs the operator to SUBMIT sitemap.xml to Search Console (section 6, 'scripts/sitemap-check.sh --live, and submit sitemap.xml') but contains NO STEP TELLING THEM TO REGENERATE IT FIRST. As it stands in the tree, sitemap.xml lists 19 STAGING URLs of the form https://torin.bg/new/<page>, and robots.txt's Sitemap: directive is likewise the absolute URL https://torin.bg/new/sitemap.xml. Following the checklist literally would submit 19 staging paths to Google immediately after a cutover whose entire purpose is URL continuity. THE CODE SIDE IS NOT BROKEN: plan 04-10 owns both src/sitemap.xml and src/includes/site-config.php, so the base_url change and sitemap regeneration have a designed home, and robots.txt carries a boxed warning that its directive is the second place in the tree encoding /new/, with scripts/sitemap-check.sh asserting the two agree and failing loudly. The defect is purely that the operator-facing checklist does not sequence it. ADD AN EXPLICIT STEP in Section 2 (the swap): change base_url from the /new/ staging value to the root, regenerate sitemap.xml, update the robots.txt Sitemap: directive, run sitemap-check.sh to confirm the two agree, and ONLY THEN submit. | fixed |  | 2026-09-21T19:15:34.474Z | 2026-09-22T07:04:56.098Z |
| 52 | 04 | deviation | src/.htaccess |  | 04-09 NEW RISK INTRODUCED BY THIS PLAN, read before anyone runs deploy-new.sh again: src/.htaccess now addresses the document ROOT. Its RewriteBase and canonicalisation target were promoted from the /new/ staging segment to '/' (the two edits ROADMAP carried into Phase 4 as D4-30). Uploading this file to public_html/new/ would point the base at / while the file sits in /new/, BREAKING EVERY REDIRECT IN IT — which is precisely the historical defect this phase already hit once, where retirement rules answered 301 with a Location of https://torin.bg/home/torin/public_html/new/<target>, a server path leaked into a public URL that 404'd on follow while the 301 itself looked perfectly correct. The file now carries a boxed warning at the top and checklist step 2.4 repeats it: src/.htaccess is OUT OF SCOPE for deploy-new.sh and goes up only with the 04-10 root swap. Orchestrator verified post-merge that the file still balances 10 IfModule open/close and 3 FilesMatch open/close. | open |  | 2026-09-21T19:15:43.630Z |  |
| 53 | 04 | unrun-verify | scripts/probes/cutover-sweep.js |  | CLOSED 2026-09-24 as STALE — the premise no longer holds. The rendered probe HAS run, repeatedly, against staging. Quick task 260922-ozo extended it from index.html alone to all 20 pages and ran it: 20 pages rendered in one browser session, 107-1077 Cyrillic tokens each against a floor of 40, one <h1> each, no runtime diagnostics in any body, no chat widget in any DOM. That run also FOUND A REAL DEFECT, which is the strongest evidence it genuinely executed: the probe's console-error collector was guarded by `typeof cdp.onConsoleError === 'function'` and no such export existed anywhere in the tree, so every `"consoleErrors": []` ever recorded was an array no code could have filled. Root cause was one level lower - connect()'s handler dispatched only replies carrying msg.id and dropped CDP events, which carry `method` and never an id. Fixed with an event registry + session.onEvent(). Ledger 55 then records further rendered runs (CDP Network capture, the 19-of-20 ERR_BLOCKED_BY_CLIENT false positive found by running it for real). The Cyrillic, diagnostic and widget assertions are gates now, not a specification. | fixed |  | 2026-09-21T19:15:52.846Z |  | 2026-09-24T08:30:00.000Z |
| 54 | 04 | unrun-verify | scripts/upload-selftest.php |  | upload-selftest's containment assertion CANNOT DETECT A DISABLED GUARD, and this was observed, not theorised: during mutation testing on 2026-09-22 the containment condition in torin_normalise_upload() was replaced with 'if (false)' — the guard entirely dead — and the assertion 'every normalised path sits under the system temp directory' still PASSED. It checks that the happy path returns paths under the temp dir, which tempnam() guarantees on its own; it never feeds the function a path that SHOULD be rejected. So the one control standing between a visitor upload and code execution on a host that maps .html to PHP has no negative-path test. Closing it needs a case where the containment check is made to fail — e.g. stubbing the temp dir to a location the output cannot sit under — and asserting torin_normalise_upload() returns false AND removes the file. Everything else in that suite is genuinely proven: 10/10, mutation-tested. | fixed |  | 2026-09-22T10:06:08.360Z | 2026-09-22T10:36:47.279Z |
| 55 | 04 | deviation | scripts/deploy-new.sh |  | CLOSED 2026-09-24. The sweep now asserts deploy completeness from BOTH directions, and both were proven able to fail before being trusted. Section 3b walks src/ for static assets and asks the origin for each (103 checked on staging), catching a file committed but never uploaded even when no page references it yet. Section 9 captures CDP Network events and fails any page whose SAME-ORIGIN subresources 4xx or never load, catching a reference to a path that was never committed. Either would have caught the 2026-09-23 incident. ONE FALSE POSITIVE FOUND BY RUNNING IT FOR REAL, worth keeping: the synthetic test passed, then the first staging run failed 19 of 20 pages on net::ERR_BLOCKED_BY_CLIENT for gateway.umami.is/api/send — the analytics beacon, blocked by Brave shields, which is the engine this harness drives. Cross-origin failures are now reported but never fatal. Proving a check CAN fail is necessary and not sufficient; only the real run showed it also caught what it should not. WHAT THIS DOES NOT CLOSE: content comparison. A stale-but-present file still passes both checks, so the two-day staleness of 2026-09-23 would NOT be caught. Raised separately as entry 56. | fixed |  | 2026-09-23T16:40:00.000Z | 2026-09-24T07:05:00.000Z |
| 56 | 04 | unrun-verify | scripts/cutover-sweep.sh |  | A STALE-BUT-PRESENT FILE PASSES EVERY CHECK THIS PROJECT HAS. Entry 55 closed the missing-file half of deploy verification; this is the other half. On the morning of 2026-09-23 staging was found to be roughly TWO DAYS behind the tree — the footer band the owner had removed was still live on all 20 pages, and the dev theme scaffolding deleted at 04-07 was still being served. The sweep reported 20/20 PASS throughout, and still would: section 3b asserts a file is PRESENT, and the rendered probe asserts a page renders prose. Neither asks whether the bytes on the origin are the bytes in the commit. WHY IT IS NOT TRIVIAL: deploy-new.sh STRIPS COMMENTS from CSS on upload, so css/*.css is deliberately not byte-identical to src/ and a naive digest comparison would report a permanent false failure on every stylesheet. PHP includes are executed rather than served. The comparison is honest for images, fonts, svg and js, and needs a defined rule for css. SUGGESTED SHAPE: compare a digest of each byte-identical class (img, fonts, svg, js) against the origin, and for css compare the comment-stripped local form against what is served, reusing the same stripping deploy-new.sh already implements so the two cannot drift. Until this exists, nothing distinguishes a current deploy from one that is days old. | open |  | 2026-09-24T07:05:00.000Z |  |

````json
[
  {
    "id": 1,
    "kind": "stub",
    "phase": "02",
    "file": "src/includes/category-page.php",
    "line": null,
    "description": "Six template slots (intro, warranty/TRUST-03, process, FAQ, related, prices) render nothing on all three category pages — content is Phase 3 per D-25",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.147Z",
    "resolved_at": "2026-09-22T06:48:25.799Z"
  },
  {
    "id": 2,
    "kind": "stub",
    "phase": "02",
    "file": "src/index.html",
    "line": null,
    "description": "DIFF-02 (battery regeneration) ships inside a collapsed disclosure — knowingly unmet, must not pass silently in Phase 3 verification (D-13 / OWNER-QUESTIONS #9)",
    "status": "waived",
    "reason": "DIFF-02 retired 2026-09-11 (REQUIREMENTS.md:29, ROADMAP.md:18) - battery regeneration discontinued by the business. No regeneration claim remains in src/index.html. Entry guards a requirement that no longer exists.",
    "recorded_at": "2026-08-06T03:41:56.205Z",
    "resolved_at": "2026-09-22T06:48:33.869Z"
  },
  {
    "id": 3,
    "kind": "stub",
    "phase": "02",
    "file": "src/includes/site-config.php",
    "line": null,
    "description": "hours, viber and notice are [ASSUMED] — OWNER-QUESTIONS #20/#21/#8 block the Phase 4 cutover",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-06T03:41:56.265Z",
    "resolved_at": "2026-09-20T16:29:03.826Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-08-26T08:07:31.628Z",
    "resolved_at": "2026-09-21T18:56:38.709Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T10:55:25.672Z",
    "resolved_at": "2026-09-22T06:48:25.867Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:27.963Z",
    "resolved_at": "2026-09-22T10:06:08.435Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T11:52:51.426Z",
    "resolved_at": "2026-09-21T16:18:13.827Z"
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
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T16:00:42.305Z",
    "resolved_at": "2026-09-21T18:08:50.367Z"
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
    "status": "waived",
    "reason": "Stale on three counts: OWNER-QUESTIONS #23 ANSWERED 2026-09-11 (1 month all repairs, except category 6); za-bateriite.html deleted from the tree in Phase 3.5; site-config.php now implements exactly that answered rule (default + nonstandard exclusion). Recorded during 04-04 after the question was already answered.",
    "recorded_at": "2026-09-20T16:00:59.898Z",
    "resolved_at": "2026-09-22T06:48:33.934Z"
  },
  {
    "id": 28,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/settings.php",
    "line": null,
    "description": "04-06 (G1): EVERY live gate in this plan is unrun — same single cause as ledger entry 23, closed by the same single action. deploy-new.sh is denied to subagents (confirmed with the TORIN_CRED_FILE worktree override in place) and no php binary exists on the build machine; the orchestrator independently confirmed 'command -v php' is empty and no Docker daemon is running, so this is an absent runtime, not just a permission denial. Nothing in this plan reached the server. The six unrun gates: (1) a deliberately corrupted settings.txt on staging leaving all 20 pages at HTTP 200 with zero warnings, the corrupted key on its default and EVERY OTHER KEY still applied, both served bodies recorded; (2) settings.txt, .user.ini and php.fcgi each returning 403 or 404 over HTTP — the deny block is written and module-guarded but has never been exercised against a live Apache, and an .htaccess mistake in this tree has historically been a whole-subtree 500; (3) zero PHP warnings across the 20 served pages after the site-config.php / jsonld.php / footer.php changes; (4) banner absent by default on the served homepage, present exactly once on two pages with a current closure, gone with an expired one, present on its end date; (5) the one-edit-three-locations proof, with after values to pair with the recorded before values; (6) three/four/three openingHoursSpecification entries on the served page.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:28:18.395Z",
    "resolved_at": null
  },
  {
    "id": 29,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/settings-selftest.php",
    "line": null,
    "description": "04-06 (G2): scripts/settings-selftest.php has NEVER BEEN EXECUTED, in either direction. 22 assertions covering the behaviour block, the date gate and the structured-data entry counts, authored against a machine with no PHP and no running Docker daemon — exactly as scripts/upload-selftest.php was in 04-03 (ledger entry 19). A test that has never run is a specification, not a gate. Run 'php scripts/settings-selftest.php' the moment a PHP runtime exists; it is the fastest way to close most of G1's logic half without a deploy.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T16:28:24.222Z",
    "resolved_at": "2026-09-22T10:06:08.500Z"
  },
  {
    "id": 30,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/settings.php",
    "line": null,
    "description": "04-06 (G3) — HIGHEST-PRIORITY PRE-DEPLOY ACTION: no PHP file in this plan has been syntax-checked. 'php -l' was unavailable (orchestrator re-confirmed: no php binary, no Docker daemon). FIVE files gained or changed PHP — settings.php, banner.php, site-config.php, jsonld.php, footer.php — and a parse error in any one of them takes down all twenty pages at once, which is the precise failure mode this plan exists to prevent, arriving from the other direction. Run 'php -l' on all five before or immediately after the first deploy, and DEPLOY THEM TOGETHER rather than one at a time: these files include each other, so a partial upload can leave the site referencing a function that has not landed yet.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T16:28:30.651Z",
    "resolved_at": "2026-09-22T10:06:08.559Z"
  },
  {
    "id": 31,
    "kind": "deviation",
    "phase": "04",
    "file": "src/includes/banner.php",
    "line": null,
    "description": "04-06 (G4) — OPEN OWNER QUESTION created by this plan. The closure strip does not appear until the closure actually starts (Decisions #3). The UI contract's phrasing would have shown it from the moment the dates were set, i.e. as advance notice. One comparison either way and it should be the owner's call, but he has not been asked. Raise at the 04-10 owner checkpoint. Two related plan deviations argued in 04-06-SUMMARY rather than quietly applied: hours_days is an allowlisted token rather than free text bounded at 20 chars (the plan's own headline truth is unreachable otherwise), and the banner message cap is 120 code points rather than 300 characters (the 180px backstop is arithmetically unmeetable at 300).",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:28:37.121Z",
    "resolved_at": null
  },
  {
    "id": 32,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/settings.txt.example",
    "line": null,
    "description": "04-06 (G6): the cPanel File Manager round-trip has NEVER BEEN PERFORMED. The Bulgarian guide (docs/naruchnik-nastroyki.md) tells the owner to copy settings.txt.example, rename it, and edit it in cPanel with UTF-8 encoding — nobody has done this even once. The realistic failure is the panel's editor writing a BOM or a non-UTF-8 encoding; the parser rejects non-UTF-8 values per-key so the failure mode is SAFE, but it would be SILENT: the owner sees his edit ignored with no explanation. One human performing the procedure once, with the guide open, closes both this and the D5 human-judgment item.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-20T16:28:42.942Z",
    "resolved_at": null
  },
  {
    "id": 33,
    "kind": "stub",
    "phase": "04",
    "file": "src/includes/site-config.php",
    "line": null,
    "description": "04-06 (G5) — NARROWS AND SUPERSEDES LEDGER ENTRY 3. Entry 3 covered three [ASSUMED] markers in site-config.php: hours, viber and notice. TWO ARE NOW RESOLVED — hours is closed by D4-25 and plan 04-06 (one source, four consumers); viber was removed in 04-04 when the chat button was retired from all five CTA slots. ONE REMAINS: the 'notice' marker. OWNER-QUESTIONS #8 asks whether the footer band should exist at all and still has no answer. Its CONTENT is no longer unconfirmed — it is composed from the confirmed hours — but the EXISTENCE question is untouched, and 04-06 deliberately left the marker in place rather than promoting it because it merely looked settled. This narrowed entry is the live one; entry 3 is marked fixed and should be read as superseded by this.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-20T16:29:00.052Z",
    "resolved_at": "2026-09-22T14:47:58.019Z"
  },
  {
    "id": 34,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/spam-guard.php",
    "line": null,
    "description": "04-05 Task 2: nothing in the spam guard has been executed by a PHP interpreter, in either direction. No php binary and no running Docker daemon on the build machine (orchestrator-confirmed), and deploy-new.sh is denied to subagents, so neither a local render nor a staging deploy was possible. Balance checks and greps are STRUCTURAL and do not prove the file parses. Three live gates are unrun: (a) a POST with a forged timestamp returns no 5xx and produces no notification; (b) a valid submission succeeds and an identical repeat inside the window is rejected, both with timestamps; (c) each rejection produces exactly one correlation line in the host error log with NO submitted field value in it. Run all three the moment the code reaches /new/. This is an environment boundary, not evidence of correctness.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T06:08:35.337Z",
    "resolved_at": null
  },
  {
    "id": 35,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/spam-guard.php",
    "line": null,
    "description": "04-05 Task 2: the tdd=true RED/GREEN cycle was NOT performed. With no PHP runtime there is no way to observe a failing test, and this run's scope was limited to three named source files, so no scripts/spam-guard-selftest.php was authored either. The same shape as scripts/upload-selftest.php (ledger 19) and scripts/settings-selftest.php (ledger 29) would close it: nine assertions matching the plan's behavior list, runnable as 'php scripts/spam-guard-selftest.php' once a runtime exists. Three selftests now await the same single missing dependency.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T06:08:41.039Z",
    "resolved_at": "2026-09-22T10:25:22.948Z"
  },
  {
    "id": 36,
    "kind": "deviation",
    "phase": "04",
    "file": "src/includes/spam-guard.php",
    "line": null,
    "description": "04-05 Task 2: the HMAC signing key and the throttle records live in sys_get_temp_dir(), matching the precedent upload.php:178-194 set for visitor photographs. On a host whose temp directory is genuinely shared between accounts, a neighbouring tenant could read the key or pre-create the file; the exclusive-create mode and symlink refusal close the cheap version of that attack, not the expensive one. Worth ONE LINE of confirmation from the hosting panel that this account has a private temp directory. Second-order effect worth knowing: a temp-dir change or a system reaper ROTATES the key, which makes in-flight forms verify as 'forged' — those get the honest retry page, so the failure is visible and recoverable rather than silent.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T06:08:47.618Z",
    "resolved_at": null
  },
  {
    "id": 37,
    "kind": "deviation",
    "phase": "04",
    "file": "src/includes/spam-guard.php",
    "line": null,
    "description": "04-05 Task 2: the throttle is keyed on REMOTE_ADDR, so a whole office or a mobile carrier behind one NAT address shares a quota of one delivered enquiry per fifteen minutes. The rejection message names the shop's telephone number in the same sentence, so a throttled visitor is never left without a route — but this shape must be understood before the window is ever tightened. Related design call, deliberate and documented: the throttle records DELIVERED ENQUIRIES, not POSTs (via torin_rate_limit_record() inside the success branch), because counting every POST would throttle a visitor correcting a validation error or retrying after a page that told them to retry.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T06:08:54.267Z",
    "resolved_at": null
  },
  {
    "id": 38,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/notify.php",
    "line": null,
    "description": "04-05 Task 3 — CASE (a) IS NOW PROVEN LIVE; CASES (b) AND (c) REMAIN. The SMTP-to-sendmail fall-through is the functional heart of Task 3. On 2026-09-22 the owner submitted the real form and all three channels delivered: Telegram message, shop email, customer confirmation. public_html/new/error_log, correlation id f4e59592, 19:33:47-19:33:50 Europe/Sofia, shows BOTH mail legs taking the shipped no-credential path exactly as case (a) predicted — the line \"mail no smtp credential, using sendmail\" then \"mail delivered via=sendmail\", twice, once per leg (owner notification + customer confirmation), with photos=0 leftover=0 and NO telegram failure line. THE EXACTLY-ONCE CONDITION HOLDS: two legs, one delivery each, no duplicate. Whole cascade ~3s. STILL UNRUN, and these are the cases that actually exercise a FAILURE falling through: (b) WRONG CREDENTIAL — set smtp_password to a bad value in /home/torin/torin-secrets.php, submit, expect \"mail smtp failed before acceptance, falling through to sendmail\" then \"mail delivered via=sendmail\", a 303, and the email to arrive; (c) CORRECT CREDENTIAL — expect \"mail delivered via=smtp\" and NO fall-through line. IN BOTH, THE ENQUIRY MUST ARRIVE EXACTLY ONCE. Two copies means the pre/post-acceptance boundary is wrong, which is the single outcome this design exists to prevent. The boundary observes the relay literal 354 reply rather than parsing PHPMailer localised exception text; ambiguity deliberately resolves to DO NOT RETRY.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T16:17:38.259Z",
    "resolved_at": null
  },
  {
    "id": 39,
    "kind": "deviation",
    "phase": "04",
    "file": "src/msg.html",
    "line": null,
    "description": "04-05 CROSS-PLAN DEPENDENCY, 04-08 MUST READ THIS: the $torin_robots assignment on msg.html is INERT. The emitter that reads the variable ships in 04-07; header.php does not read it yet. The assignment is in place so 04-07's emitter finds it. IF 04-07 SLIPS OR IS REPLANNED (its Task 1 is a blocking owner gate needing an Umami account and a website id, so this is a live risk), msg.html ships WITHOUT its noindex — and 04-08 must then ensure the sitemap does not list it, since the only other protection against the confirmation page being indexed and entered from search (threat T-04-39) would be gone. 04-08 owns the sitemap.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T16:17:45.758Z",
    "resolved_at": "2026-09-21T18:08:50.442Z"
  },
  {
    "id": 40,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/notify-selftest.php",
    "line": null,
    "description": "NARROWED 2026-09-24. The selftest half is DISCHARGED: a PHP 8.5.10 runtime now exists and `php scripts/notify-selftest.php` was executed for the first time - 9/9 assertions hold, including 'all channels down reports ok=false', 'telegram reports exactly false', 'mail reports exactly false' and 'the mail driver returns false rather than fatalling without it'. The all-channels-failed branch is therefore proven AT THE UNIT LEVEL, no longer merely encoded. WHAT REMAINS OPEN is the live end-to-end half, which the selftest cannot reach: point telegram_bot_token at a bad value AND make the mail leg fail, submit a real form, and check the PAGE returns 200 (not 5xx), shows the error band, PRESERVES the typed values, renders consent UNCHECKED, contains the phone number 02 9549710, and produces exactly one 'all notification channels failed (telegram=fail mail=fail)' log line carrying no submitted value. That is a rendered-page and log-side assertion, not a function-return assertion. Sibling entries 19 and 29 are now genuinely run (18/18 and 27/27); 35 (spam-guard-selftest) was never authored and remains the one selftest that does not exist.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T16:17:52.642Z",
    "resolved_at": null
  },
  {
    "id": 41,
    "kind": "deviation",
    "phase": "04",
    "file": "src/includes/contact-form.php",
    "line": null,
    "description": "04-05: three small UI/privacy obligations left open because they fall outside this plan's file list, all cheap and all owned elsewhere. (a) ACCESSIBILITY — the error band is focusable but nothing focuses it. UI-SPEC C-8 asks for tabindex=-1 PLUS a programmatic .focus() on render; the attribute and id (#form-error) ship here, but the .focus() call belongs in js/analytics.js. A keyboard or screen-reader user currently lands at the top of the document instead of on the explanation. 04-07 owns that file. (b) ANALYTICS — the UI-SPEC C-9 'form-error' server-rendered event, with its field property, is not emitted by the re-render; 04-07 owns analytics. (c) PRIVACY COPY — uslovia.html does not mention the new customer confirmation email, which is an automated message to the visitor's address and therefore a processing purpose the privacy text should name. 04-04 owns uslovia.html and is parked at its owner sign-off gate, so fold this into that conversation.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T16:18:00.387Z",
    "resolved_at": "2026-09-21T18:08:50.508Z"
  },
  {
    "id": 42,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/vendor/phpmailer/.htaccess",
    "line": null,
    "description": "04-05: CLOSED 2026-09-23. The vendored-library deny block is verified over HTTP on staging — vendor/phpmailer/PHPMailer.php, SMTP.php and Exception.php all return 403, so the deny applies and the library is not fetchable. The entry also carried an operational note: TWO messages leave per submission (owner notification plus customer confirmation), each on its own connection, so worst-case latency was worth one look once the form was in real use. Answered by the same live run that proved ledger 38 case (a) — correlation id f4e59592 completed both legs in ~3 seconds (19:33:47-19:33:50), with no SMTP timeout in the path because the shipped state carries no credential to try. Re-check timings if a credential is ever added.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T16:18:07.475Z",
    "resolved_at": "2026-09-23T06:12:00.000Z"
  },
  {
    "id": 43,
    "kind": "deviation",
    "phase": "04",
    "file": "src/js/analytics.js",
    "line": null,
    "description": "04-07: THE UI-SPEC JS BUDGET GATE FAILED, AND IS RECORDED AS FAILED RATHER THAN MADE GREEN. analytics.js gzips to 1959 B against a <=1024 B budget (orchestrator independently re-measured: 1959). The decisive measurement is that THE CODE ALONE IS 972 B — inside budget — and the COMMENTS ARE THE ENTIRE OVERAGE. There is no build step in this project, so comments are wire bytes. The executor declined to strip them to make the number go green, on the grounds that the one file whose purpose is preventing a regression a desktop test cannot catch is the worst place to ship undocumented; the orchestrator endorses that call. Contributing cause: the budget was written for 'a delegated listener and an event map', but the file also carries four funnel handlers, two server-declared event readers, closed-set enforcement and the C-8 focus call — the last three assigned AFTER the budget existed. At 1024 B there are 52 bytes left for comments. THIS IS THE SECOND FILE TO HIT THIS EXACT WALL: ledger 20 records photo-resize.js at 2042 B against 2048 B, six bytes of headroom, same root cause. Three resolutions, pick one: (a) accept and raise the figure to ~2.0 KB; (b) implement deploy-time JS comment stripping in deploy-new.sh, the way CSS already goes through strip-css-comments.py — this would bring analytics.js to ~972 B and simultaneously give photo-resize.js real headroom, and is now justified by two files rather than one; (c) strip by hand and lose the documentation. RECOMMENDED: (b).",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:08:17.794Z",
    "resolved_at": null
  },
  {
    "id": 44,
    "kind": "deviation",
    "phase": "04",
    "file": "src/js/analytics.js",
    "line": null,
    "description": "04-07: the C-8 error-band .focus() call lives in analytics.js, and that is a fragile home for an ACCESSIBILITY behaviour. Content blockers commonly match the filename 'analytics.js' by pattern; a blocked file means the error-band focus silently stops working for exactly those users, so a keyboard or screen-reader user with a blocker lands at the top of the document instead of on the explanation of what went wrong. src/js/site.js would be immune and is the right home for accessibility behaviour. This is a TWO-LINE MOVE. The executor flagged it rather than deviating unilaterally, because the orchestrator had directed the file explicitly (carried from ledger 41a, which itself inherited the location from 04-05's summary) — the orchestrator now endorses the move. Do it as a quick task before cutover; it is cheap and the failure mode is silent.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:08:25.653Z",
    "resolved_at": null
  },
  {
    "id": 45,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/deploy-new.sh",
    "line": null,
    "description": "04-07: THE TWO DELETED SCAFFOLDING FILES STILL EXIST ON THE SERVER. src/css/theme-a.css and src/includes/dev-switcher.php are gone from the tree (plan-mandated, verified: the branch deleted those two files and nothing else), but deploy-new.sh UPLOADS and never DELETES, so both remain live under /new/ until someone removes them by hand. dev-switcher.php is dev scaffolding that has been rendering on all 19 staging pages since Phase 2 — leaving it on the server after cutover would publish it. There is currently nowhere to record this: 04-CUTOVER-CHECKLIST.md does not exist yet; 04-09 creates it. 04-09 MUST carry a manual-removal step for these two paths. Related, same root cause: any future file deletion in this project has the same problem, so the checklist should name the class, not just these two files.",
    "status": "waived",
    "reason": "Discharged by 04-09: 04-CUTOVER-CHECKLIST.md lists both paths at Section 3 items 8-9 and names the class at line 217 citing this entry. Underlying fact still true and re-confirmed 2026-09-22 (css/theme-a.css and includes/dev-switcher.php both return 200 on staging), but it is now tracked by the checklist gated deletion pass, so this entry is duplicate bookkeeping.",
    "recorded_at": "2026-09-21T18:08:34.232Z",
    "resolved_at": "2026-09-22T06:48:33.997Z"
  },
  {
    "id": 46,
    "kind": "deviation",
    "phase": "04",
    "file": ".planning/phases/04-hardening-cutover/04-07-PLAN.md",
    "line": null,
    "description": "04-07: the plan's files_modified declaration was incomplete — five files changed that it did not declare: src/contact-send.php, src/kontakti.html, src/css/base.css, src/css/components.css, src/includes/asset-version.php. Each change is defensible (contact-send.php carries the C-9 form-error server-rendered event the orchestrator explicitly assigned from ledger 41b; kontakti.html gained a seventh data-slot for three phone anchors 04-05 added after C-9's slot table was written; the two CSS files and asset-version.php are theme-a removal fallout). NO HARM OCCURRED — 04-07 ran alone in its wave. But files_modified is exactly what the orchestrator's intra-wave overlap check reads to decide whether two plans may run in parallel, so an under-declared list is a latent parallel-execution hazard, not a paperwork issue. Worth a planner-side note that files_modified must be updated when a plan absorbs carried-forward ledger work assigned after planning.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:08:43.788Z",
    "resolved_at": null
  },
  {
    "id": 47,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "src/includes/header.php",
    "line": null,
    "description": "NARROWED 2026-09-24. The SYNTAX half - which this entry called the highest-risk unrun item in the phase - is DISCHARGED. A PHP 8.5.10 binary now exists on the build machine and `php -l` was run on header.php FIRST, exactly as this entry instructed: 'No syntax errors detected'. Then the whole tree: 22 .php files and 21 .html-as-PHP pages, 43 files total, ZERO parse errors. header.php - edited by 04-06, 04-07 and 04-08 without once being parsed, and included by every page - is clean, so the all-19-pages-at-once outage this entry predicted cannot fire from a parse error. Ledger 30's five 04-06 files are covered by the same run. Beyond syntax, all 21 pages were then EXECUTED under error_reporting=E_ALL via php -S and their served bodies scanned: 21/21 HTTP 200, zero rendered diagnostics of any severity. WHAT REMAINS OPEN is the deploy half: this entry's claim that every live number in 04-08-SUMMARY is a pre-deploy baseline has NOT been re-measured here, and robots.txt, sitemap.xml, the WebP siblings and the .htaccess still need their post-deploy verification at the live root. The standing instruction also still applies: deploy the PHP includes TOGETHER, not one at a time.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:56:09.283Z",
    "resolved_at": null
  },
  {
    "id": 48,
    "kind": "deviation",
    "phase": "04",
    "file": "scripts/asset-version-check.sh",
    "line": null,
    "description": "04-08: FOUR GATES IN THIS PLAN DID NOT MEASURE WHAT THEY CLAIMED — a recurring class on this project, worth reading as a pattern rather than four incidents. (1) WORST: a cache assertion written as grep 'cache-control:.*max-age=3' matches BOTH the old value 300 AND the new 31536000, because it is a PREFIX match on the digit 3. It therefore reported green before the change and green after — a gate that could never fail. Replaced with an exact comparison. (2) grep -o '<img[^>]*>' breaks on PHP source because [^>]* stops at the > of ?>, so it reported 9 defects against markup that has none; re-checked with a real parser (2 tags, 0 defects). (3) and (4) two of the executor's own new comments inflated substring counts — the literal <loc> and the word sitemap written as prose — which it reworded rather than let a counter pass for the wrong reason. SAME FAMILY AS ledger 16 (BSD wc padding defeating 'wc -l | grep -qx 0'). STANDING RULE for this project: never assert a numeric threshold with a substring or prefix grep; extract the value and compare it numerically.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T18:56:19.412Z",
    "resolved_at": "2026-09-22T06:48:25.930Z"
  },
  {
    "id": 49,
    "kind": "deviation",
    "phase": "04",
    "file": ".planning/phases/04-hardening-cutover/04-08-PLAN.md",
    "line": null,
    "description": "04-08: PLAN DEFECT, not a work defect — the plan's sitemap gate demands >= 20 URLs, but the correct number is 19 and the executor rightly refused to pad it. The arithmetic: 20 page files minus one deliberately noindexed (msg.html, excluded on purpose per threat T-04-39) equals 19, and all 19 return 200 live. Orchestrator independently confirmed post-merge: sitemap.xml contains exactly 19 <loc> entries and zero occurrences of msg.html. The gate figure was written before 04-07 introduced the robots emitter that makes msg.html noindex, so it counts a page the phase then decided to exclude. Treat 19 as correct and fix the gate, not the sitemap. Also recorded: two declared deviations outside files_modified, both justified — header.php (the logo <img> lives there and is the largest per-visit win) and scripts/asset-version-check.sh (its Check C asserted max-age <= 600 with failure text literally reading 'Phase 4 raises this, DESIGN-02', so left untouched it would fail forever once the new .htaccess lands).",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:56:29.519Z",
    "resolved_at": null
  },
  {
    "id": 50,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/seo-metadata-check.js",
    "line": null,
    "description": "SUPERSEDES LEDGER 13, WHICH THE ORCHESTRATOR CLOSED IN ERROR on 2026-09-21. Entry 13 recorded the 03-09 SEO live gate as unrun pending a deploy, and its own closing condition was literally 'Deploy then re-run to close'. It was marked fixed while processing 04-08 on the mistaken basis that 04-08 completed the SEO work — but 04-08 DEPLOYED NOTHING, as its own summary states plainly. THIS ENTRY IS THE LIVE ONE; treat 13 as still open despite its status. The condition is unchanged: the 11 tuned pages still serve pre-plan metadata on the origin, and 'node scripts/seo-metadata-check.js --live' still reports served-matches-source on exactly those 11 because it is measuring the currently-deployed build. Deploy, then re-run, then close THIS entry. Note the general trap this is an instance of: any check run against the live origin during this phase measures the OLD build, so a green result is evidence about the deployed site and says nothing about the work in the tree.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T18:56:55.528Z",
    "resolved_at": null
  },
  {
    "id": 51,
    "kind": "deviation",
    "phase": "04",
    "file": ".planning/phases/04-hardening-cutover/04-CUTOVER-CHECKLIST.md",
    "line": null,
    "description": "04-09 CHECKLIST OMISSION found by the orchestrator, not the executor: the checklist instructs the operator to SUBMIT sitemap.xml to Search Console (section 6, 'scripts/sitemap-check.sh --live, and submit sitemap.xml') but contains NO STEP TELLING THEM TO REGENERATE IT FIRST. As it stands in the tree, sitemap.xml lists 19 STAGING URLs of the form https://torin.bg/new/<page>, and robots.txt's Sitemap: directive is likewise the absolute URL https://torin.bg/new/sitemap.xml. Following the checklist literally would submit 19 staging paths to Google immediately after a cutover whose entire purpose is URL continuity. THE CODE SIDE IS NOT BROKEN: plan 04-10 owns both src/sitemap.xml and src/includes/site-config.php, so the base_url change and sitemap regeneration have a designed home, and robots.txt carries a boxed warning that its directive is the second place in the tree encoding /new/, with scripts/sitemap-check.sh asserting the two agree and failing loudly. The defect is purely that the operator-facing checklist does not sequence it. ADD AN EXPLICIT STEP in Section 2 (the swap): change base_url from the /new/ staging value to the root, regenerate sitemap.xml, update the robots.txt Sitemap: directive, run sitemap-check.sh to confirm the two agree, and ONLY THEN submit.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T19:15:34.474Z",
    "resolved_at": "2026-09-22T07:04:56.098Z"
  },
  {
    "id": 52,
    "kind": "deviation",
    "phase": "04",
    "file": "src/.htaccess",
    "line": null,
    "description": "04-09 NEW RISK INTRODUCED BY THIS PLAN, read before anyone runs deploy-new.sh again: src/.htaccess now addresses the document ROOT. Its RewriteBase and canonicalisation target were promoted from the /new/ staging segment to '/' (the two edits ROADMAP carried into Phase 4 as D4-30). Uploading this file to public_html/new/ would point the base at / while the file sits in /new/, BREAKING EVERY REDIRECT IN IT — which is precisely the historical defect this phase already hit once, where retirement rules answered 301 with a Location of https://torin.bg/home/torin/public_html/new/<target>, a server path leaked into a public URL that 404'd on follow while the 301 itself looked perfectly correct. The file now carries a boxed warning at the top and checklist step 2.4 repeats it: src/.htaccess is OUT OF SCOPE for deploy-new.sh and goes up only with the 04-10 root swap. Orchestrator verified post-merge that the file still balances 10 IfModule open/close and 3 FilesMatch open/close.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-21T19:15:43.630Z",
    "resolved_at": null
  },
  {
    "id": 53,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/probes/cutover-sweep.js",
    "line": null,
    "description": "CLOSED 2026-09-24 as STALE — the premise no longer holds. The rendered probe HAS run, repeatedly, against staging. Quick task 260922-ozo extended it from index.html alone to all 20 pages and ran it: 20 pages rendered in one browser session, 107-1077 Cyrillic tokens each against a floor of 40, one <h1> each, no runtime diagnostics in any body, no chat widget in any DOM. That run also FOUND A REAL DEFECT, which is the strongest evidence it genuinely executed: the probe's console-error collector was guarded by `typeof cdp.onConsoleError === 'function'` and no such export existed anywhere in the tree, so every `\"consoleErrors\": []` ever recorded was an array no code could have filled. Root cause was one level lower - connect()'s handler dispatched only replies carrying msg.id and dropped CDP events, which carry `method` and never an id. Fixed with an event registry + session.onEvent(). Ledger 55 then records further rendered runs (CDP Network capture, the 19-of-20 ERR_BLOCKED_BY_CLIENT false positive found by running it for real). The Cyrillic, diagnostic and widget assertions are gates now, not a specification.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-21T19:15:52.846Z",
    "resolved_at": "2026-09-24T08:30:00.000Z"
  },
  {
    "id": 54,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/upload-selftest.php",
    "line": null,
    "description": "upload-selftest's containment assertion CANNOT DETECT A DISABLED GUARD, and this was observed, not theorised: during mutation testing on 2026-09-22 the containment condition in torin_normalise_upload() was replaced with 'if (false)' — the guard entirely dead — and the assertion 'every normalised path sits under the system temp directory' still PASSED. It checks that the happy path returns paths under the temp dir, which tempnam() guarantees on its own; it never feeds the function a path that SHOULD be rejected. So the one control standing between a visitor upload and code execution on a host that maps .html to PHP has no negative-path test. Closing it needs a case where the containment check is made to fail — e.g. stubbing the temp dir to a location the output cannot sit under — and asserting torin_normalise_upload() returns false AND removes the file. Everything else in that suite is genuinely proven: 10/10, mutation-tested.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-22T10:06:08.360Z",
    "resolved_at": "2026-09-22T10:36:47.279Z"
  },
  {
    "id": 55,
    "kind": "deviation",
    "phase": "04",
    "file": "scripts/deploy-new.sh",
    "line": null,
    "description": "CLOSED 2026-09-24. The sweep now asserts deploy completeness from BOTH directions, and both were proven able to fail before being trusted. Section 3b walks src/ for static assets and asks the origin for each (103 checked on staging), catching a file committed but never uploaded even when no page references it yet. Section 9 captures CDP Network events and fails any page whose SAME-ORIGIN subresources 4xx or never load, catching a reference to a path that was never committed. Either would have caught the 2026-09-23 incident. ONE FALSE POSITIVE FOUND BY RUNNING IT FOR REAL, worth keeping: the synthetic test passed, then the first staging run failed 19 of 20 pages on net::ERR_BLOCKED_BY_CLIENT for gateway.umami.is/api/send — the analytics beacon, blocked by Brave shields, which is the engine this harness drives. Cross-origin failures are now reported but never fatal. Proving a check CAN fail is necessary and not sufficient; only the real run showed it also caught what it should not. WHAT THIS DOES NOT CLOSE: content comparison. A stale-but-present file still passes both checks, so the two-day staleness of 2026-09-23 would NOT be caught. Raised separately as entry 56.",
    "status": "fixed",
    "reason": "",
    "recorded_at": "2026-09-23T16:40:00.000Z",
    "resolved_at": "2026-09-24T07:05:00.000Z"
  },
  {
    "id": 56,
    "kind": "unrun-verify",
    "phase": "04",
    "file": "scripts/cutover-sweep.sh",
    "line": null,
    "description": "A STALE-BUT-PRESENT FILE PASSES EVERY CHECK THIS PROJECT HAS. Entry 55 closed the missing-file half of deploy verification; this is the other half. On the morning of 2026-09-23 staging was found to be roughly TWO DAYS behind the tree — the footer band the owner had removed was still live on all 20 pages, and the dev theme scaffolding deleted at 04-07 was still being served. The sweep reported 20/20 PASS throughout, and still would: section 3b asserts a file is PRESENT, and the rendered probe asserts a page renders prose. Neither asks whether the bytes on the origin are the bytes in the commit. WHY IT IS NOT TRIVIAL: deploy-new.sh STRIPS COMMENTS from CSS on upload, so css/*.css is deliberately not byte-identical to src/ and a naive digest comparison would report a permanent false failure on every stylesheet. PHP includes are executed rather than served. The comparison is honest for images, fonts, svg and js, and needs a defined rule for css. SUGGESTED SHAPE: compare a digest of each byte-identical class (img, fonts, svg, js) against the origin, and for css compare the comment-stripped local form against what is served, reusing the same stripping deploy-new.sh already implements so the two cannot drift. Until this exists, nothing distinguishes a current deploy from one that is days old.",
    "status": "open",
    "reason": "",
    "recorded_at": "2026-09-24T07:05:00.000Z",
    "resolved_at": null
  }
]
````
