---
phase: 04-hardening-cutover
plan: 03
subsystem: contact
tags: [uploads, photos, telegram, gd, exif, security, progressive-enhancement]
status: complete
requires:
  - "04-02 contact spine (torin_notify interface, contact-send.php, kontakti.html)"
  - "04-01 measured runtime: ext:gd, ext:exif, ext:fileinfo, ext:curl all yes on PHP 8.5.10"
provides:
  - "torin_normalise_upload($tmpPath, $maxEdge) — structural verify, orientation, decode, re-encode"
  - "torin_collect_uploads($filesEntry, $limits) — ceilings + per-file Bulgarian rejection reasons"
  - "torin_release_uploads($paths) — idempotent temp-file release"
  - "torin_notify_tg_call/_tg_text/_tg_file — the multipart-or-JSON Telegram transport"
  - "torin_notify_clamp($text, $limit) — one clamp, two budgets (4096 message, 1024 caption)"
  - "js/photo-resize.js — the browser downscale and the .filelist rows"
  - "src/.user.ini — upload_max_filesize 10M, post_max_size 60M"
  - "scripts/fetch-remote.sh — read one remote file over FTPS, no server-side artefact"
  - "scripts/probes/photo-filelist.js — CDP file-selection render proof"
  - "css .filelist / .filelist__row / __thumb / __name / __size / __remove, ::file-selector-button"
affects:
  - "04-05 (mail driver takes the same $photos array; the error re-render should carry the photos branch)"
  - "04-06 (its .user.ini FilesMatch deny is belt-and-braces — the host already denies the name)"
  - "04-09/04-10 (the .user.ini follows the directory at cutover; post_max_size must not be lost)"
tech-stack:
  added: []
  patterns:
    - "RESEARCH C-3 — verify structurally, read orientation first, decode, re-encode, random name"
    - "RESEARCH P-4 — zero/one/many branch across the notification methods"
    - "RESEARCH P-6 — the browser downscale is an optimisation, never the enforcement point"
    - "UI-SPEC C-4 — the native file control stays native, styled via ::file-selector-button"
key-files:
  created:
    - src/includes/upload.php
    - src/.user.ini
    - src/js/photo-resize.js
    - scripts/upload-selftest.php
    - scripts/fetch-remote.sh
    - scripts/probes/photo-filelist.js
  modified:
    - src/includes/notify.php
    - src/contact-send.php
    - src/includes/contact-form.php
    - src/css/components.css
    - .planning/phases/04-hardening-cutover/04-HOST-CAPABILITIES.md
decisions:
  - "The .user.ini TIGHTENS the host rather than raising it — the 2M ceiling D4-13 fought vanished with the 8.5 upgrade, leaving a 200M buffering surface instead"
  - "Aspect ratio is checked explicitly: a uniform downscale is invariant under it, so the re-encode does NOT guarantee it the way it guarantees bytes and pixels"
  - "The one-photo branch sends a second text message only when the caption clamp actually bit, and falls back to text if the photo call fails"
  - "The page-scoped script is emitted from contact-form.php, not header.php — $torin_extra_head is reset inside header.php and is the dev switcher's, not a page hook"
  - "The remove glyph ships in an inert <template> so icons.php stays the one writer of every glyph"
  - "The host error_log is read over FTPS rather than through a token-gated PHP reader in the document root (P-10)"
metrics:
  duration: "one session"
  completed: 2026-09-20
  tasks_completed: 3
  tasks_total: 3
actuals:
  tokens: 21600
  tasks: 3
  commits: 7
---

# Phase 4 Plan 03: Photographs of the Damage Summary

A visitor can attach up to five photographs, the server treats every byte as hostile, and
the owner's phone gets the picture. Verified against the live host, not asserted: zero,
one and three photographs each returned `303`, every Telegram call answered `ok:true`, and
the host `error_log` reports `leftover=0` on the delivered path and on the refused path.
A file whose bytes are PHP is refused by name-independent structural parse. A sixth
photograph is refused with a message naming both numbers. In a real headless browser at
360px the three chosen files shrank from 1,239,057 B to 362,545 B and the page did not
overflow by a pixel.

Two things this does **not** prove, carried forward rather than glossed: **no photograph
with an EXIF orientation tag has ever been through either half of this pipeline**, and
**nobody has looked at the owner's phone**. Both are in `.planning/WINDOWS.md` (17, 18).

## What Was Built

**`src/includes/upload.php`** — `torin_normalise_upload()` runs RESEARCH C-3's order
exactly: `getimagesize()` structural parse, a `finfo` sniff **alongside** it (failing
closed if fileinfo is ever absent rather than degrading to extension-trust),
`exif_read_data()` **before** any transform, a full GD decode, all eight EXIF
orientations, an aspect-ratio refusal, a downscale to 1600px, and a re-encode at quality
82 into `tempnam(sys_get_temp_dir(), 'torin_')`. The resample runs even at 1:1 onto an
opaque white canvas, so a PNG or WebP with alpha does not composite against black on the
way to JPEG — one code path, no branch exercised only by the file type nobody tested with.
The containment claim is *asserted at runtime*: a path that does not start with the system
temp directory is unlinked and refused rather than trusted.

`torin_collect_uploads()` walks `$_FILES`' five-parallel-array shape, skips
`UPLOAD_ERR_NO_FILE` slots (so an empty control is zero photographs and not one bad one),
enforces the count, per-file and total ceilings **before** anything reaches a decoder,
reads the byte count off the disk rather than from the request, and returns both the paths
and a list of Bulgarian reasons keyed by **position** — never by the visitor's filename,
which is attacker-controlled and would be landing in a page.

Neither function ever receives the client filename or the client-supplied content type.
They are not parameters, so no later edit can start trusting them.

**`src/.user.ini`** — `upload_max_filesize = 10M`, `post_max_size = 60M`. See Deviations 1.

**`src/includes/notify.php`** — one transport helper that speaks JSON or multipart (and
never sets a JSON content type on a multipart body, which produces a failure that looks
like a network fault), plus the three-shape driver. The media-group method refuses fewer
than two items, which is the entire reason the one-photo branch exists; the two-to-five
branch sends the text **first** so a failed group still leaves the enquiry delivered.

**`src/contact-send.php`** — collects after field validation and before the secrets file,
releases the temp files on the one line both outcomes pass through, and registers the same
release with the engine via `register_shutdown_function` so a fatal cannot leak a
photograph. Logs a leftover count on both the delivered and the refused path.

**`src/js/photo-resize.js`** — `createImageBitmap(file, {imageOrientation:'from-image'})`,
canvas downscale, `toBlob` at 0.82, `DataTransfer`. Rows carry a preview drawn from the
already-downscaled file, an ellipsised name, the post-downscale size and a labelled remove
button. Deferred submit under a ten-second ceiling. Returns immediately if any of the four
APIs is missing.

## Verification Status

Everything below was run against `https://torin.bg/new/` after
`scripts/deploy-new.sh` uploaded the slice on 2026-09-20.

### The three notification shapes — all live, all 303

| Run | Photos | Status | `error_log` correlation line |
|---|---|---|---|
| A | 0 | **303** → `msg.html` | `photos=0 leftover=0` |
| B | 1 (2400px JPEG) | **303** | `photos=1 leftover=0` |
| C | 3 (JPEG 2400px, rotated JPEG, PNG) | **303** | `photos=3 leftover=0` |

**Why the 303 is stronger evidence than it looks.** `contact-send.php` redirects only when
`torin_notify()` returns `ok => true`, and there is exactly one registered channel — so a
303 means the Telegram API itself answered `ok:true`. And the driver logs a distinct line
for every failure mode it has (`telegram transport failed (<method>)`, `telegram rejected
the call (<method>)`, `sendPhoto failed, falling back to text only`). **The host
`error_log` carries none of them for any of these runs.** So on run B the single-photo
call succeeded and the fallback never fired, and on run C both the message and the media
group succeeded — the `||` in the return value did not have to carry either one.

That is as far as measurement reaches. What *rendered* on the phone is `WINDOWS.md` 18.

### The refusals — all live

| Run | Input | Status | What the visitor was shown |
|---|---|---|---|
| D | a file whose bytes are `<?php echo shell_exec(...)`, named `.jpg`, declared `image/jpeg` | **422** | «Снимка 1: файлът не е разпознат като снимка (JPEG, PNG или WebP).» |
| E/F | six photographs | **422** | «Може да прикачите най-много 5 снимки, а са приложени 6.» |
| G | a valid JPEG padded to 11,124,811 B | **422** | «Снимка 1: файлът е твърде голям.» |
| H | a 65,000,000 B request body | **422** | «Файловете са твърде големи и не стигнаха до сървъра…» |

Every one of those four response bodies contains **zero** occurrences of `Warning:`,
`Fatal error`, `/home/` or any fragment of the submitted payload — checked, not assumed,
and it matters because `display_errors` is still `1` in this subtree (BLOCKER 1).

Run F's correlation line is the sharp one:

```
torin contact 7949302a: refused photos=5 rejected=1 leftover=0
```

Five photographs normalised successfully, one refusal, **nothing left on disk**. The
refused path is where *more* temp files exist, not fewer, and it is now the observable one.

**G and H together are the `.user.ini` measurement**, and they work because the SAPI and
the application produce *different* strings for the same condition — «файлът е твърде
голям» is `UPLOAD_ERR_INI_SIZE`, which only PHP can raise, while the application's own
ceiling says «файлът е по-голям от 10 MB». Full write-up appended to
`04-HOST-CAPABILITIES.md`. **H also exercises, for the first time, the oversized-POST
branch 04-02 shipped and never ran.**

### The browser half — rendered, at 360×640

`scripts/probes/photo-filelist.js` drives a real selection through CDP
`DOM.setFileInputFiles`, because a `FileList` cannot be set from page script and therefore
cannot be reached by any HTTP client.

```
PHOTO_FILES="<long-cyrillic-name>.jpg:<t2>.jpg:<t3>.png" \
  scripts/render-check.sh scripts/probes/photo-filelist.js \
  https://torin.bg/new/kontakti.html 360 640
```

```json
{ "fileButtonMinHeight": "44px", "fileButtonBorder": "2px solid",
  "scrollWidth": 360, "innerWidth": 360, "rows": 3, "selectedFiles": 3,
  "selectedBytes": 362545, "originalBytes": 1239057, "shrankTo": "29%",
  "thumbsDrawn": 3, "removeGlyphs": 3, "removeLabelled": 3,
  "longestName": "снимка-на-счупен-екран-на-лаптоп-lenovo-thinkpad-t480-отпред-и-отзад-2026.jpg",
  "longestNameClipped": true, "rowWidth": 328,
  "submitLabel": "Изпратете запитване", "submitDisabled": false,
  "templateRendered": true, "noOverflow": true, "pass": true }
```

The probe asserts the downscale **by bytes**, not by the presence of a row: a list that
renders while the replacement silently failed is the exact failure a row-count assertion
would bless. 1,239,057 → 362,545 B is the script working. `thumbsDrawn: 3` means three
object URLs decoded to non-zero `naturalWidth`, so the previews are real images and not
empty boxes. `longestNameClipped: true` at `rowWidth: 328` is `min-width: 0` doing its job.

With scripting disabled, `scripts/probes/svc-page.js` at 360×640 reports
`horizontalScroll: false`, one `h1`, no empty headings — `.filelist` never renders and
nothing about the page depends on it.

### The plan's automated gates

| # | Gate | Result |
|---|---|---|
| T1-1 | `upload.php` contains the pipeline calls | **13** matches |
| T1-2 | zero non-comment reads of the client-supplied file type | **0** |
| T1-3 | zero short-array constructs (5.2 dialect) | **0** |
| T1-4 | `grep -c public_html src/includes/upload.php` | **0** |
| T1-5 | `curl -sI https://torin.bg/new/.user.ini` | **403** |
| T2-1..4 | `sendMessage` / `sendPhoto` / `sendMediaGroup` / `json_encode` in notify.php | 2 / 2 / 1 / 3 |
| T2-5 | live POST returns 303 | **303** |
| T2-6 | collect-before-notify by line order | 190 < 246 |
| T2-7 | `unlink(` in contact-send.php, above the branch | line 253 |
| T3-1..3 | `createImageBitmap` / `imageOrientation` / form-id guard | present |
| T3-4 | `gzip -c src/js/photo-resize.js \| wc -c` ≤ 2048 | **2042** — see Deviations 4 |
| T3-5 | `photo-resize` in header.php | **0** |
| T3-6 | script tag on served `kontakti.html` | **1** |
| T3-7 | script tag on index / about / warrently / zalivane-technosti | **0, 0, 0, 0** |

Three more, not in the plan, worth having: `includes/upload.php`, `includes/notify.php`
and `includes/contact-form.php` each return **200 with `content-length: 0`** from the live
host. That is the include-boundary contract (no output on include) **and** a real
substitute for the `php -l` this project still cannot run — with `display_errors = 1`, a
parse error in any of them is a visible fatal, not a silent one.

### What was still never linted

There is no `php` binary on the build machine and the Docker daemon is not running, so
`php -l` was not run on any file, and `scripts/upload-selftest.php` has never executed in
either direction (`WINDOWS.md` 19). The live evidence above is better than a lint would
have been for the paths it covers, and says nothing about the paths it does not.

## Deviations from Plan

### 1. [Rule 1 — the plan's premise had expired] `.user.ini` tightens the host instead of raising it

**Found during:** Task 1, reading `04-HOST-CAPABILITIES.md` before writing the file.
**Issue:** the plan says to create `src/.user.ini` "setting `upload_max_filesize` to 10M
and `post_max_size` above five times that". It was written against the measured PHP 5.2
ceiling of 2M/8M, where 10 MB photographs were impossible. 04-01's account-default switch
moved this directory to 8.5, whose ini measures **250M/200M in effect** —
`04-HOST-CAPABILITIES.md` says in as many words that "04-03 needs **no** `.user.ini` work".
**Fix:** the file ships, with both of the plan's numbers, doing the opposite job. Ten
megabytes now *matches the form's own copy* so PHP refuses an oversized photograph at the
SAPI boundary before the pipeline sees it; sixty megabytes *caps* a request body that would
otherwise be two hundred, which is T-04-13's whole subject. The plan's own `key_links`
entry — "the copy never advertises a limit the server does not honour" — is now true in
both directions. Both conditions the plan gated on (`sapi: cgi-fcgi`,
`user_ini.filename: '.user.ini'`) were checked against the measured values first, and both
hold. **Files:** `src/.user.ini`. **Commit:** `c312250`.

### 2. [Rule 2 — missing correctness check] Aspect ratio is checked explicitly

**Found during:** Task 1, against the plan's own truth that "a photograph that passes
server validation is within the notification service's dimension and aspect-ratio limits,
**because the re-encode is what guarantees it**".
**Issue:** the re-encode guarantees the byte ceiling and the combined-dimension rule,
because both fall out of capping the long edge. It does **not** guarantee the ratio: a
uniform downscale is invariant under aspect ratio, so a 12000×200 image is still 60:1 at
1600×27. The single-photo method refuses anything over 20:1, so that truth had exactly one
input shape for which it was false.
**Fix:** an explicit refusal when the long side exceeds twenty times the short one, with
the reason for the check written beside it so it is not later deleted as redundant with the
downscale. **Files:** `src/includes/upload.php`. **Commit:** `c312250`.

### 3. [Rule 2 — lead preservation] Two narrow extra calls in the one-photo branch

**Found during:** Task 2, reconciling two of the plan's own behaviour bullets.
**Issue:** "One photograph: exactly one outbound call" and "a caption longer than the
ceiling is truncated rather than rejected, **and the full fault description still reaches
the owner in the text message**" cannot both hold literally when there is no text message.
The caption budget is 1024 characters and `contact-send.php` allows a 1024-character fault
description on its own, so the composed enquiry reaches roughly 1600 — the clamp bites on
long submissions, which are exactly the ones carrying the most detail.
**Fix:** the caption is clamped unconditionally, and **only when the clamp actually bit**
does the full text follow as its own message. On a short enquiry — the common case — it is
still exactly one call. Separately, if the photo call *fails*, the text goes on its own
rather than the enquiry being lost with the photograph; that is the same reasoning the plan
already applies by sending the text first in the two-to-five branch.
**Files:** `src/includes/notify.php`. **Commit:** `d6c6dbd`.

### 4. [Constraint conflict, resolved against the comment convention] The JS budget

**Found during:** Task 3. The first draft measured **3313 B gzipped** against a 2048 B
gate. The code alone is ~1476 B; the rest was prose.
**Issue:** `deploy-new.sh` comment-strips CSS through `strip-css-comments.py` but does
nothing to JS. So a comment in a `.css` file costs visitors **zero** — UI-SPEC §Conflicts
C-3 measured exactly that and concluded "the comments are not to be stripped" — while the
identical comment in a `.js` file is wire cost on every load. This tree's convention is
that every non-obvious block names the defect it prevents; that convention and this budget
are in direct conflict, and the budget is the one with an automated gate.
**Fix:** the file carries five comments and no more, chosen for what cannot be recovered
by reading the code: the P-6 prohibition, why closures hold the entry and not the index,
why it is `createImageBitmap` and not `drawImage`, that the preview is not a second decode,
and what the ten-second ceiling submits. Everything else moved here. Final: **2042 B, six
bytes of headroom** — which is not a comfortable pass, and the next comment added to that
file breaks the gate. The real fix is to strip JS comments at deploy the way CSS already
is; logged as `WINDOWS.md` 20.
**Files:** `src/js/photo-resize.js`. **Commit:** `8a6be22`.

### 5. [Rule 3 — the plan's wiring point does not exist] The script is emitted from the form partial

**Found during:** Task 3. The plan says to wire the script into `kontakti.html`; the
obvious mechanism is `$torin_extra_head`, echoed at `header.php:185`.
**Issue:** `header.php:50` **assigns `$torin_extra_head = ''`** before the dev-switcher
include — inside the file, after a page has already been given its chance to set it. Any
page-level assignment is overwritten. That variable is the dev switcher's, not a page hook,
and the phase's own artefact list has it being removed.
**Fix:** `contact-form.php` emits the `<script … defer>` at the end of the form it owns.
The script and the DOM it drives cannot be deployed apart, `header.php` keeps zero
references (the plan's own gate), and the served `kontakti.html` carries exactly one tag
while four other pages carry none — all measured above. `defer` on a `src` script is
honoured wherever the tag sits. **Files:** `src/includes/contact-form.php`. **Commit:**
`8a6be22`.

### 6. [Rule 2 — unobservable promise] The leftover count, and an FTPS reader to see it

**Found during:** after Task 2, against the acceptance criterion that the temp directory
holds no leftover "asserted by a post-send count reported in the handler's `error_log`
correlation line, and read out of the host `error_log`". Neither existed.
**Issue:** "no copy of a visitor's photograph survives the request" was true by
construction and **invisible to everyone**. The visitor sees a redirect, the owner sees a
message, and a surviving temp file sits where neither can look. D4-07 buys this site a
short privacy note *because* nothing is kept; an unobservable version of that promise is
one refactor away from being false and nobody noticing.
**Fix:** `contact-send.php` logs `photos=N leftover=M` on the delivered path and
`refused photos=N rejected=R leftover=M` on the refused one — counts and the request id,
never a filename, a path or a submitted value. Reading it needed a channel: the obvious
one, a token-gated PHP reader in the document root, is precisely Pitfall P-10 on the
endpoint this phase exists to harden. `scripts/fetch-remote.sh` reads one remote file over
the FTPS channel that already exists, is read-only by construction, and **puts nothing on
the server**. It reuses `backup-live-site.sh`'s credential handling verbatim, including the
public-key pin that is what actually authenticates the peer given that `-k` is unavoidable
on this host's certificate. **Files:** `src/contact-send.php`, `scripts/fetch-remote.sh`.
**Commit:** `1c40cfd`.

### 7. [Scope] A CDP probe for the browser half

`scripts/probes/photo-filelist.js` is not in the plan's file list. The plan's acceptance
criterion asks for `render-check.sh scripts/probes/svc-page.js … with three files selected
and one long filename`, and `svc-page.js` cannot select files — nothing can, from page
script, by design. Without `DOM.setFileInputFiles` the entire browser half of this plan
would have shipped with **no evidence that the script runs at all**: if a feature guard
were wrong the file would return silently, the form would still work because the server
enforces everything, and no other check in this plan would notice. **Commit:** `c235ca1`.

### 8. [Scope] A test harness that has never run

`scripts/upload-selftest.php` was authored before `upload.php` as the RED half of the
plan's `tdd="true"` contract. It cannot be executed here. It is committed as a
specification and labelled as one in its own header — see Residual Risks 3.

## CSS Budget

Measured on the **deploy-time output**, since `deploy-new.sh` strips CSS comments before
upload and source size is not what ships.

| File | Before (gz, stripped) | After (gz, stripped) | Delta |
|---|---:|---:|---:|
| `components.css` | 3055 B | 3233 B | **+178 B** |

Phase running total against the 1.5 KB (1536 B) gzipped budget: 04-02 spent 259 B, this
plan spends 178 B, **437 B of 1536 B used (28%)**, leaving 1099 B for the holiday banner
and whatever 04-04 and 04-06 need.

## Threat Flags

None. No new network endpoint, auth path or schema change beyond what the plan's
`<threat_model>` registers. Disposition of the seven `mitigate` rows assigned here:

| Threat | State |
|---|---|
| T-04-12 upload executed as code | **Mitigated and measured.** Nothing is written outside the system temp directory, and the containment is asserted at runtime rather than assumed. Run D shows PHP source refused. |
| T-04-13 resource exhaustion | **Mitigated and measured.** Runs G and H: the SAPI refuses an 11 MB file and a 65 MB body, both before the pipeline. |
| T-04-14 EXIF forwarded to a third party | **Mitigated by construction, not measured.** The GD re-encode drops every metadata block. No fixture carried GPS or device metadata to confirm it against — but the same gap is `WINDOWS.md` 17, and closing that closes this. |
| T-04-15 `$_FILES` metadata trusted | **Mitigated and gated.** Zero non-comment reads; neither the filename nor the declared type is even a parameter. |
| T-04-16 `.user.ini` fetchable | **Mitigated by the host, better than expected.** 403 by name, evaluated before the existence check — see `04-HOST-CAPABILITIES.md`. 04-06's deny should still ship; a host-level rule is not ours. |
| T-04-17 a refusal costing the submission | **Partly mitigated.** The reason is field-level and names the position, measured in runs D and F. The typed description does **not** yet survive — `WINDOWS.md` 22. |
| T-04-SC package-manager installs | **None.** No npm, pip or cargo. `photo-resize.js` is hand-authored and gated at 2 KB. |

## Known Stubs

| Stub | File | Why, and which plan resolves it |
|---|---|---|
| Photo rejection loses the typed description | `src/contact-send.php` | The field-level error is correct and measured; the form re-render that would preserve `$values` is 04-05's, and `torin_render_contact_form()` already accepts and escapes them. `WINDOWS.md` 22. |
| WebP accepted in copy, decoder unexercised | `src/includes/upload.php` | Guarded by `function_exists('imagecreatefromwebp')` and refused with a reason if absent. No WebP was ever submitted. `WINDOWS.md` 21. |
| `scripts/upload-selftest.php` never executed | `scripts/upload-selftest.php` | No PHP runtime on the build machine. `WINDOWS.md` 19. |

None of these prevents the plan's goal, and the live runs confirm it: photographs reach
the owner and no copy survives the request.

## Residual Risks

All five are in `.planning/WINDOWS.md` so they survive this summary scrolling out of
context and block `/gsd-ship` until closed or waived.

**1. No photograph with an orientation tag has ever been through this pipeline.**
(`WINDOWS.md` 17) — the highest-value unclosed item, and the one this plan is most likely
to be wrong about. Every fixture was produced by macOS `sips`, which bakes rotation into
pixels and writes no orientation tag. P-7 names this exact test as the one that passes a
broken pipeline: a file with no tag is handled identically by a correct implementation and
by one that discards orientation entirely. Both halves are affected — the server's
`exif_read_data` → `imagerotate` chain and the browser's `imageOrientation: 'from-image'`.
One portrait photograph, taken on a real handset, submitted through `kontakti.html`, looked
at in Telegram, closes it. This is the plan's own `backstop` truth EA-07 and it was always
going to need a human.

**2. Nobody has looked at the owner's phone.** (`WINDOWS.md` 18) — the API said `ok:true`
to every call and logged no failure, so the photographs reached Telegram's servers. Whether
the single photo *shows* the enquiry as its caption, and whether the three arrive as one
media group rather than three separate messages, is a rendering question the API's answer
does not contain. One glance.

**3. `scripts/upload-selftest.php` has never run.** (`WINDOWS.md` 19) — RED was never
observed failing and GREEN was never observed passing. The eight behaviours are a
specification. Six of them are covered indirectly by the live runs above; the orientation
one is covered by nothing, which is risk 1 again from the other side.

**4. `photo-resize.js` has six bytes of headroom.** (`WINDOWS.md` 20) — a gate that
passes at 2042/2048 is a gate that fails on the next comment. The fix is deploy-time JS
comment-stripping, not more prose-shaving.

**5. The WebP branch is unexercised.** (`WINDOWS.md` 21) — GD's WebP decoder is a separate
build option from `ext-gd`, and only `ext-gd` was probed. If it is absent, every WebP the
picker offers is refused. Either submit one, or drop `webp` from the `accept` list so the
copy stops advertising it.

## Self-Check: PASSED

Files claimed created, confirmed present: `src/includes/upload.php`, `src/.user.ini`,
`src/js/photo-resize.js`, `scripts/upload-selftest.php`, `scripts/fetch-remote.sh`,
`scripts/probes/photo-filelist.js`.

Commits claimed, confirmed in `git log`: `fe68e3e`, `c312250`, `d6c6dbd`, `8a6be22`,
`1c40cfd`, `bcdb683`, `c235ca1`.

On the honesty of the claims above: every figure in the Verification Status section came
out of a command run this session against the live host or a real headless browser, and
the commands are recorded beside their results. The one place a reader could over-read this
summary is the phrase "every Telegram call answered `ok:true`" — that is an inference from
two measurements (a 303, which requires `ok => true`, and the *absence* of any failure line
in the host `error_log`), and it is sound only because the driver logs a distinct line for
every failure mode it has. It is not a sighting of a message on a phone, and this summary
does not claim one. No claim is made that the files were linted, that the self-test ran,
that any photograph carried an orientation tag, or that a WebP was ever accepted.
