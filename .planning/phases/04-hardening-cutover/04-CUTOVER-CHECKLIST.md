# 04 — Cutover Checklist

> **Status:** authored in plan 04-09. **EXECUTED BY PLAN 04-10, WHICH IS NOT YET AUTHORISED.**
> Nothing in this document has been performed. Plan 04-09 deployed nothing and ran
> `scripts/deploy-live.sh` zero times.
>
> **How to read this file.** Every step is written to be executed by hand, in order, by a
> person at a terminal and an FTP client. Steps marked **GATE** must be *true* before the next
> step starts — a gate that cannot be satisfied is a STOP, not a note. Steps marked
> **UNRUN** describe checks that have never executed anywhere, because the build machine has
> no `php` binary and no running Docker daemon; they are specifications until the day someone
> runs them, and must never be reported as passing on the strength of having been written.

---

## The trap that governs this entire document

**Any check run against the live origin during Phase 4 measured the OLD build.**

Nothing from plans 04-01 through 04-09 is deployed. `robots.txt`, `sitemap.xml`, the 43 WebP
siblings, the contact page, the PHP includes and this promoted `.htaccess` all either 404 or
serve pre-phase values on the origin right now. A green live result collected during this
phase is evidence about *the site that is currently deployed* and says nothing whatsoever
about the work in the tree.

Consequence for this checklist: **every live measurement recorded in any 04-xx summary must be
re-taken after the swap.** They are baselines, not passes. (Ledger #50, which supersedes #13 —
#13 was closed in error and should be read as still open.)

---

## Section 0 — BLOCKING PRE-FLIGHT GATES

These four are unresolved as of 04-09. **Do not begin the swap until each is answered.**

### GATE 0.1 — The reporting property must exist and be verified BEFORE any redirect ships

The canonical host is decided: **`https://torin.bg` (no `www`) is canonical** — locked decision
D4-29, confirmed by plan 04-09 against the live origin on 2026-09-21:

| variant | status | hops |
|---|---|---|
| `http://torin.bg/` | 200 | 0 |
| `https://torin.bg/` | 200 | 0 |
| `http://www.torin.bg/` | 200 | 0 |
| `https://www.torin.bg/` | 200 | 0 |

All four serve the same content with **no redirect at all**, so ranking signals are split four
ways. Publishing the canonicalisation rule fixes that — and is **one-way**: once Google
consolidates onto the apex, moving to `www` later means a second consolidation across the whole
site with real ranking disturbance.

**In a URL-prefix property those four variants are four different properties.** The moment the
redirects go live, traffic moves to the canonical form and the property holding this site's
history falls to zero — during precisely the window in which this phase's success is judged by
watching for new errors and ranking drops.

- [ ] **Identify which Search Console property currently holds this site's data, and how it is
      verified.** Unresolved: the only discoverable token is `google1718743335455f1c.html`,
      dated 2020; there is no verification meta tag on the live homepage and no verification
      DNS record — yet sixteen months of impression data exists. Something verifies an active
      property that has not been identified. *(Settings → Ownership verification)*
- [ ] **Create and verify the property that will carry post-launch traffic, BEFORE the swap.**
      Prefer a **domain-level** property: it unifies all four variants and is immune to any
      file move. Failing that, a URL-prefix property on `https://torin.bg/`.
      *(Add property)*
- [ ] **GATE:** that property is verified and reporting. A checklist whose property step comes
      *after* the swap is blind during the only window that matters.

### GATE 0.2 — Is DNS record editing available at all?

- [ ] Check cPanel → Zone Editor, or the registrar's DNS panel.

This single answer decides two things: whether a **domain-level** property (GATE 0.1) is
possible, and whether a mail authentication policy record is possible at all. **If DNS is
inaccessible:** record it and move on — the mail policy record is out of scope, and Search
Console verification stays dependent on a file that a directory move can orphan. That is a
risk to record, not a blocker. See Section 3 note on the verification file.

### GATE 0.3 — A second, move-proof Search Console verification method

The current verification depends on a single file at the document root. This cutover **moves
the document root**. If that file is left behind, verification lapses and the property stops
reporting — during the observation window.

- [ ] Establish a second method (DNS TXT if GATE 0.2 allows; otherwise the HTML meta tag in
      `includes/header.php`, which travels with the build and cannot be orphaned by a move).

### GATE 0.4 — Owner sign-off on public commitments

- [ ] The privacy / terms wording on `uslovia.html` is flagged for **owner approval before
      launch** — it becomes a public commitment the moment it is served from the root.

---

## Section 1 — Pre-flight, in this order

### 1.1 — `php -l` ON EVERY PHP FILE. BEFORE ANYTHING ELSE. (ledger #47, #30)

**RESOLVED 2026-09-24 — a `php` binary now exists on the build machine (PHP 8.5.10 cli, the
same 8.5 line the host runs).** The blocker this section was written around is gone. Both boxes
below are measured, not asserted.

`src/includes/header.php` is the acute case: it has been edited by **three separate plans**
(04-06, 04-07, 04-08) without once being parsed, and it is `include`d by **all 19 pages**. One
parse error there takes the whole site down at once — the precise failure mode this phase
exists to prevent, arriving from the other direction. Five more files from 04-06 are in the
same state.

- [x] **RUN 2026-09-24** — `php -l` on `src/includes/header.php` **first**: *No syntax errors
      detected.* Then every other PHP file in the tree, and all 21 `.html` pages (PHP wearing a
      `.html` extension, parsed as code on this host).
      `php -v` → `PHP 8.5.10 (cli)`
      `for f in $(find src scripts -name '*.php' -not -path '*/vendor/*'); do php -l "$f"; done` → **22 files, 0 errors**
      `for f in $(find src -maxdepth 1 -name '*.html'); do php -l "$f"; done` → **21 files, 0 errors**
- [x] **GATE PASSED 2026-09-24: zero parse errors across 43 files.** `header.php` — the acute
      case, edited by 04-06/04-07/04-08 and included by every page without once being parsed —
      is clean.

**Deploy the PHP includes TOGETHER, not one at a time.** These files include each other, so a
partial upload can leave the site calling a function that has not landed yet.

### 1.2 — The four self-tests that have never run (ledger #19, #29, #35, #40)

All four waited on the same single missing dependency: a PHP runtime. **It now exists
(PHP 8.5.10 cli), and three of the four were run on 2026-09-24. All three pass.**

- [x] **RUN 2026-09-24 — PASS 18/18** — `php scripts/upload-selftest.php` (#19). Authored as
      the RED half of a TDD cycle whose GREEN had never been observed. It is observed now; the
      suite has grown to 18 behaviours and includes the negative-path containment cases added
      when ledger #54 was closed. **GREEN observed for the first time.**
- [x] **RUN 2026-09-24 — PASS 27/27** — `php scripts/settings-selftest.php` (#29). Covers the
      behaviour block, the date gate and the structured-data entry counts (the checklist said
      22; the suite is now 27).
- [x] **RUN 2026-09-24 — PASS 9/9** — `php scripts/notify-selftest.php` (#40). The
      all-channels-failed branch is now proven at runtime, not merely encoded.
- [x] **RUN 2026-09-24 — PASS 27/27** — `php scripts/spam-guard-selftest.php` (#35). **This
      checklist entry was wrong: the file WAS authored**, at commit c6cce02, and carries 27
      assertions rather than the 9 predicted — the decoy field, timestamp signing (forged,
      tampered, malformed, too-fast, stale, inclusive bounds), the per-address throttle, and
      source-level assertions including `hash_equals`, no session, no superglobal in logs and
      the PHP 5.2 short-array contract.

A test that has never run is a **specification, not a gate.** That rule is what made these runs
necessary. **All four are now gates: 18/18 + 27/27 + 9/9 + 27/27 = 81 assertions, zero unrun.**

**Additional evidence available for the first time, 2026-09-24 (not previously obtainable).**
`04-HOST-CAPABILITIES.md` records that the host masks `E_DEPRECATED` (`error_reporting = 22519`),
and notes the consequence: *"the sweep cannot be used as evidence that the tree is
deprecation-clean."* With a local 8.5 runtime that evidence can now be produced directly. All 21
pages were executed through `php -S` under `error_reporting=E_ALL, display_errors=1` and their
served bodies scanned for rendered diagnostics:

    php -d error_reporting=E_ALL -d display_errors=1 -S 127.0.0.1:8099 _router.php
    # per page: curl the body, grep -oiE '(Deprecated|Warning|Notice|Fatal error|Parse error):'

**Result: 21/21 pages HTTP 200, zero diagnostics of any severity rendered into any body.** The
page-render path is deprecation-clean on 8.5 as measured, not as asserted.

One deprecation *does* exist off that path: `src/includes/upload.php` calls `imagedestroy()` at
8 sites (plus 1 in the selftest), deprecated since 8.5. It surfaced only because the local run
used `E_ALL`. **It is not a cutover blocker** — the host's `error_reporting` masks `E_DEPRECATED`,
so it cannot render for a visitor — and `imagedestroy()` has been a no-op since PHP 8.0, so the
calls are inert either way. Recorded as cheap post-launch cleanup, not a gate.

### 1.3 — Two cheap open decisions — BOTH RESOLVED 2026-09-24

- [x] **#43 — DECIDED AND IMPLEMENTED 2026-09-24 (option b).**
      `scripts/lib/strip-js-comments.py` mirrors the existing CSS stripper and `deploy-new.sh`
      runs both from one `resolve_upload_path` case. Measured, gzipped: **analytics.js
      1964 → 782 B against its ≤1024 B budget — inside, with 242 B of headroom**;
      photo-resize.js 2042 → 1492 B against 2048 B (six bytes of headroom became 556);
      site.js 1672 → 550 B; form-validate.js 2379 → 783 B. The JS stripper **refuses (exit 2)
      rather than guess at a regex literal** — guessing wrong truncates a file silently — and a
      refusal lands in the existing fail-open branch, shipping source unchanged.
      `scripts/strip-js-selftest.sh` proves it: **21/21**, covering ASI newline preservation,
      comment openers inside strings and template literals, division-vs-regex against
      photo-resize.js's real expressions, refusal on three regex positions, and every file in
      `src/js/` still parsing after stripping.
      *Superseded original text:* **#43 — the JS comment-stripping decision.** `analytics.js` gzips to 1959 B against a
      ≤1024 B budget. **The code alone is 972 B — inside budget; the comments are the entire
      overage.** There is no build step, so comments are wire bytes. Second file to hit this
      exact wall (#20: `photo-resize.js`, 2042 B against 2048 B, six bytes of headroom). Pick
      one: **(a)** accept and raise the budget to ~2.0 KB; **(b)** add deploy-time JS comment
      stripping to `deploy-new.sh`, the way CSS already goes through
      `scripts/lib/strip-css-comments.py` — brings `analytics.js` to ~972 B and gives
      `photo-resize.js` real headroom; **(c)** strip by hand and lose the documentation.
      **Recommended: (b).**
- [x] **#44 — DONE 2026-09-24.** The `.focus()` call now lives in `src/js/site.js` as **its own
      IIFE** — deliberately not folded into the existing one, which returns early on `if (!nav)`;
      error-band focus must not acquire a hidden dependency on a nav element existing. A pointer
      comment is left at the old site in `analytics.js` so it is not re-added there. Both files
      pass `node --check`.
      *Superseded original text:* **#44 — move the C-8 error-band `.focus()` call out of
      `analytics.js` into `src/js/site.js`.** **A two-line move.** Content blockers commonly match the filename
      `analytics.js` by pattern; a blocked file means error-band focus silently stops working
      for exactly the keyboard and screen-reader users who need it, landing them at the top of
      the document instead of on the explanation of what went wrong. `site.js` is immune and is
      the right home for accessibility behaviour.

### 1.4 — Ordering constraint: the analytics disclosure must not ship ahead of the tracker (#24)

`uslovia.html` carries a disclosure naming an analytics processor. It is **accurate only once
`src/js/analytics.js` is actually loading.** Both are in the tree now, so this is satisfied by
deploying them together — but it is recorded because the failure is silent and one-directional:

- [ ] **GATE:** `uslovia.html` and `js/analytics.js` go up in the **same** deploy. The
      disclosure must never be live ahead of the tracker it describes. (If analytics is ever
      dropped, DELETE the disclosure block rather than leaving it.)

### 1.5 — Backup

- [ ] Run `scripts/backup-live-site.sh` and confirm the archive is complete and restorable.
      This is the floor under every rollback below (MIGR-03).

---

## Section 2 — The swap, in order

**Mechanism (D4-28): server-side renames, both directions.** No re-upload, a swap window
measured in seconds, and rollback is the same move in reverse.

- [ ] **2.0 — Retarget `base_url`, regenerate the sitemap, repoint the robots directive. BEFORE
      the rename** (ledger #51). **The swap re-uploads nothing** — it is a server-side rename, so
      whatever these three files say at the moment of the move is what the live root publishes.
      Three files in the tree still encode the `/new/` staging segment:

      1. **`src/includes/site-config.php`** — change `'base_url'` from `https://torin.bg/new/`
         to **`https://torin.bg/`**, the same canonical target D4-30 fixes in `.htaccess:70`.
         This is the only place the staging segment appears in a page-serving file, and every
         `rel=canonical` and JSON-LD `BreadcrumbList` URL on the site is built from it.
      2. **`scripts/gen-sitemap.sh`** — regenerate `src/sitemap.xml`. It reads `base_url` from
         the config and refuses to hard-code a host, so this is the step that turns 19 `/new/`
         `<loc>` entries into 19 root URLs. Confirm **19**, and that `msg.html` is still absent
         (it is deliberately noindexed — see #49; 20 is the wrong number). `src/` holds 21
         `.html` files and the sitemap lists 19 on purpose: `msg.html` is noindexed, and
         `google1718743335455f1c.html` is the Search Console verification token, which is not a
         page. Both generator and checker exclude it by rule, and the checker now *rejects* it
         if it ever appears in the sitemap.
      3. **`src/robots.txt`** — change the `Sitemap:` directive **by hand** to
         `https://torin.bg/sitemap.xml`. It is a static file and the **second** place in the
         tree encoding `/new/`; nothing regenerates it for you.
      4. **`scripts/sitemap-check.sh`** — run it offline. Checks D and E exist for exactly this
         moment: D asserts every `<loc>` derives from `base_url`, E asserts the robots directive
         does too. Both must pass before you go any further.
      5. **Deploy exactly those three, by explicit path:**
         `scripts/deploy-new.sh includes/site-config.php sitemap.xml robots.txt`

      > **Name the three paths rather than running a bare no-argument deploy.** A no-argument run
      > uploads everything under `src/`, which is a far larger blast radius than this step needs.
      > It is no longer *dangerous* — `deploy-new.sh` now refuses the root `src/.htaccess` in
      > code (skipped in a no-argument run, hard error when named explicitly, override
      > `TORIN_DEPLOY_HTACCESS=1`), so the D4-30 catastrophe described in step 2.4 can no longer
      > arrive through the deploy script. Keep the explicit form anyway: a deploy that names what
      > it changes is one you can reason about afterwards.

      > **Why this goes before the rename, not after.** Swapping first and fixing after leaves a
      > window in which the live root publishes canonical, JSON-LD and sitemap URLs pointing
      > into `/new/`, while `robots.txt` says `Allow: /`. Nothing stops a crawl during that
      > window. Doing it first costs only a brief cosmetic mismatch on a staging tree that is
      > about to stop existing.

- [ ] **2.1 — Run cPanel → Select PHP Version *against `public_html/`* so the panel REGENERATES
      `php.fcgi` and `php85-fcgi.ini` there.**

      > **CORRECTED 2026-09-24, on owner information plus direct measurement.** This step was
      > written as "set PHP 8.5 at the root". **That half is already done and has been for a
      > while: the 8.5 selection is account-wide, not `/new/`-only.** Measured at the live root:
      > `curl -sI https://torin.bg/mailer.php` → `x-powered-by: PHP/8.5.10`. There is **no
      > version risk in the move**, and the owner's inference is right as far as version goes.
      >
      > **The step survives for a different reason, and it is not the version.** What the
      > account-wide selection does *not* do is put a wrapper in the root directory:
      >
      > | path | status | meaning |
      > |---|---|---|
      > | `torin.bg/php.fcgi` | **404** | **absent** |
      > | `torin.bg/new/php.fcgi` | 200 | present |
      > | `torin.bg/error_log` | 403 | exists, denied — calibrates the signal |
      > | `torin.bg/definitely-not-here.txt` | 404 | absent — calibrates the signal |
      >
      > 403-for-denied and 404-for-absent are distinguishable on this host, so the 404 is a real
      > absence, not a deny rule.
      >
      > The promoted `.htaccess` names `/home/torin/public_html/php.fcgi` in **three**
      > `FcgidWrapper` directives. `mailer.php` proves `.php` runs on 8.5 at the root through the
      > account handler with **no local wrapper at all** — but nothing proves `.html` does, and
      > **`.html` → PHP is supplied purely by that `AddHandler`/`FcgidWrapper` block.** Every
      > page on this site is `.html`.
      >
      > **MOVING THE STAGING WRAPPER UP IS NOT SUFFICIENT, and this is the trap.** `/new/php.fcgi`
      > was fetched and read; its body is:
      >
      > ```
      > DEFAULTPHPINI=/home/torin/public_html/new/php85-fcgi.ini
      > exec /opt/cpanel/ea-php85/root/usr/bin/php-cgi -c ${DEFAULTPHPINI}
      > ```
      >
      > The absolute path is **inside the file**, not just in its location. Carried up to the root
      > by step 2.3 it would point at an ini that no longer exists — and `php-cgi` does **not**
      > fail loudly on a missing `-c`; it falls back to the system ini. So the failure mode is not
      > the 500 this step originally predicted. It is **silent**: every limit the ini governs
      > (`upload_max_filesize`, `post_max_size`, `memory_limit`) reverts to
      > `/opt/cpanel/ea-php85/root/etc/php.ini` defaults, which is precisely what the photo-upload
      > pipeline depends on. Regenerate via the panel, or hand-correct the moved file's
      > `DEFAULTPHPINI` line — and verify by re-probing, not by reading.

- [ ] **2.1b — `php.fcgi` and `php85-fcgi.ini` are PUBLICLY READABLE on staging right now**
      (both `200`; the wrapper discloses the account home path and the PHP binary path). The
      promoted root `.htaccess` closes this with
      `<FilesMatch "^(settings\.txt|\.user\.ini|php\.fcgi|php[0-9]*-fcgi\.ini)$">`, so the
      swap fixes it — **confirm it is 403 after the swap** rather than assuming the block landed.

- [ ] **2.2 — Move the current live root OUTSIDE the document root.**
      Move it to `/home/torin/old-site/` — **NOT** to `public_html/old/`.
      **A subdirectory of the document root would publish a complete crawlable copy of every
      retired page and of the unstaffed Zendesk chat widget that CONTACT-02 exists to remove**
      (T-04-50). The old site stays on disk as a live safety net; it must not stay *reachable*.
      Leave the Section 4 files where they are — do not sweep them along with the move.

- [ ] **2.3 — Move `public_html/new/*` up to `public_html/`.**

- [ ] **2.4 — Confirm `.htaccess` landed at the root** and is the promoted form. Spot-check the
      two edits by eye before running anything: the rewrite base is `/`, and the
      canonicalisation substitution target is the bare apex. **One being right does not imply
      the other** — that is the whole of warning D4-30.

- [ ] **2.5 — Confirm `src/google1718743335455f1c.html` is present at the root.** Verified
      byte-identical to what the live root serves (53 bytes, no trailing newline, compared with
      `cmp` in 04-09). **Trap specific to this host:** its `.html` extension is executed as PHP
      here, so after promotion the file is *parsed before being served*. It must come back
      byte-identical — the sweep fetches and compares it rather than checking it merely exists.

- [ ] **2.6 — Remove the staging subtree** once the root is confirmed serving.

---

## Section 3 — The manual deletion list: TWENTY-ONE files, by exact path

**Why this is manual.** `scripts/deploy-new.sh` **uploads and never deletes**, and no script in
this project can delete a remote file. Giving the deploy script a delete capability was
deliberately rejected (D4-31): that means building a tool whose worst-case failure is
destructive, to solve an eleven-file problem that happens once.

> **NAME THE CLASS, NOT JUST THESE FILES (ledger #45).** Every file deletion in this project
> has this same gap: removing a file from `src/` removes it from the tree and leaves it live on
> the server forever. **Any future deletion needs a manual removal pass too.** This list is one
> instance of a standing problem, not a one-off.

Delete each of the following by hand in FileZilla. Each path is **relative to the document
root after the swap**.

1. `covid.html` — retired page; source-deleted, unreachable behind its retirement redirect.
2. `laptopi.html` — retired page; source-deleted, unreachable behind its retirement redirect.
3. `rezervni-chasti.html` — retired page; source-deleted, unreachable behind its redirect.
4. `za-bateriite.html` — retired page; source-deleted, unreachable behind its redirect.
5. `img/repairs/profilaktika7.jpg` — withdrawn photograph; referenced by no page, but **fetchable by direct URL** (confirmed 200 on 2026-09-21).
6. `img/repairs/profilaktika15.jpg` — withdrawn photograph; same, confirmed 200 on 2026-09-21.
7. `img/repairs/profilaktika17.jpg` — withdrawn photograph; same, confirmed 200 on 2026-09-21.
8. `css/theme-a.css` — development scaffolding, deleted from the tree in 04-07, **still resident on the server** because the deploy script never deletes (confirmed 200 on 2026-09-21).
9. `includes/dev-switcher.php` — development scaffolding, deleted from the tree in 04-07, still resident (confirmed 200 on 2026-09-21). **This has been rendering on all 19 staging pages since Phase 2 — leaving it after cutover would publish it.**
10. `header.js` — superseded by the rebuild; still returning 200 at the live root (`04-RESEARCH.md:916`, VERIFIED curl 2026-09-17; re-confirmed 200 on 2026-09-21).
11. `otpuska.js` — superseded by the rebuild; still returning 200 at the live root (same source, re-confirmed 200 on 2026-09-21).

> **Entries 10 and 11 are the two most likely to fall off this list.** They are what expanded
> it from seven files to eleven *after* D4-31 was written, so every older reference to "the
> seven stale files" is short by exactly these two. Check them by name.

### Added 2026-09-24 — the withdrawn evidence photographs (ten more)

The owner asked for the evidence strips to come off `mehanichni-problemi.html` and
`zalivane-technosti.html`. The references were removed and **the files were deleted from the
tree**, so by the rule stated above they are now stranded on the server. Each photograph has a
`.webp` sibling; **both halves of every pair must go**, because the `<picture>` source and the
`<img>` fallback are separate fetchable URLs.

12. `img/repairs/meh-prob2.jpg` — impact damage; strip withdrawn 2026-09-24.
13. `img/repairs/meh-prob2.webp` — sibling of the above.
14. `img/repairs/meh-prob3.jpg` — impact damage; strip withdrawn 2026-09-24.
15. `img/repairs/meh-prob3.webp` — sibling of the above.
16. `img/repairs/meh-prob5.jpg` — impact damage; strip withdrawn 2026-09-24.
17. `img/repairs/meh-prob5.webp` — sibling of the above.
18. `img/repairs/zalivane1.jpg` — liquid damage; strip withdrawn 2026-09-24.
19. `img/repairs/zalivane1.webp` — sibling of the above.
20. `img/repairs/zalivane2.jpg` — liquid damage; strip withdrawn 2026-09-24.
21. `img/repairs/zalivane2.webp` — sibling of the above.

> **These ten are the newest entries and therefore the likeliest to be missed**, exactly as
> entries 10 and 11 were when they expanded the list from seven. If the swap happens before
> they are deleted, two sets of photographs the owner has withdrawn stay publicly fetchable at
> the live root by direct URL — the same defect Phase 3.5 already had to correct once.

- [ ] All twenty-one deleted.
- [ ] **GATE:** re-fetch each of the twenty-one and confirm **404**. Deleting and not checking is
      how a file survives a deletion pass.

---

## Section 4 — Leave these at the root. Do not move them.

- [ ] `.well-known/` — **the certificate challenge directory.** The TLS certificate renews
      within weeks of an autumn cutover, and a missing challenge path **fails renewal silently
      until the certificate expires** (T-04-52). By then the whole site is untrustworthy in
      every browser.
- [ ] `cgi-bin/` — the host's script directory.
- [ ] The host-generated **error log** at the root. It stays, and it stays **refused over
      HTTP** — the root is about to gain executing pages and a submission handler, so it will
      start accumulating entries that must not be publicly readable (T-04-51). The sweep
      **asserts** it is refused rather than merely observing that it looks refused.
- [ ] `php.fcgi`, `php85-fcgi.ini`, `.user.ini`, `settings.txt` — generated or owner-edited,
      all four denied by the authorisation block in `.htaccess`.

**Watch item (T-04-53):** cPanel may **rewrite its own handler block into the root
`.htaccess`**, where it will coexist with the hand-written one. If pages start 500ing or
serving as source after a panel visit, look here first — the panel's block covers `.php` only,
and every page on this site is PHP wearing a `.html` extension.

---

## Section 5 — Go/no-go sweep

**Rollback on any failure.** A homepage spot-check was explicitly rejected as the go/no-go: it
would not have caught the redirect defect this project actually shipped.

- [ ] Run `scripts/cutover-sweep.sh --target https://torin.bg` and read the verdict.

> **Staging rehearsal, 2026-09-24: PASS — 36 pass / 0 fail / 4 skipped.** Run after the icon,
> evidence-strip and script deploys, with rendering enabled. All **93** assets present *and*
> byte-identical to the tree (section 3c, ledger #56). All **20** pages rendered: 95–1070
> Cyrillic tokens each against a floor of 40, one `<h1>` each, no runtime diagnostics, no chat
> widget, **0 same-origin subresource failures** (19 third-party, the umami beacon blocked by
> Brave shields — reported, never fatal, per ledger #55).
>
> **This does NOT authorise the cutover** and the script says so itself: it was not run against
> the root, so `robots.txt`, `sitemap.xml`, the verification file and the *absence* of the
> staging noindex header were all skipped. Section 0 is also still unanswered.
>
> **Comment-stripped JS was verified as WORKING, not merely loading** — a dead listener raises
> no console error either, which is the ledger #54 lesson. Two probes against the served,
> stripped files: the nav opens on click (`aria-expanded` false→true, list really
> `display:block`), closes on a second click, and closes on Escape; and the relocated C-8
> error-band focus still fires. Wire weight now `analytics.js` **764 B** gzipped against a
> 1024 B budget, `photo-resize.js` **1474 B** against 2048 B.

The sweep **follows every redirect to its terminal response** and asserts the final status and
the final URL, not the first status line. This is the single most important property of the
instrument: **a check that reads only the first response line and the `Location` header PASSES
the exact defect this project shipped** — four indexed URLs returning a correct-looking `301`
straight to a missing page, with nothing in any build failing.

It covers: all 19 pages 200 with zero runtime warnings and a substantive Cyrillic token count;
the four retirement redirects and the four host variants, each to its terminal state with a hop
count; the staging `noindex` header **absent**; the verification file byte-identical; favicon,
`robots.txt` and `sitemap.xml` present; the host error log refused; the Zendesk widget absent
from every page; the contact page reachable with its `no-store` directive intact.

- [ ] **GATE:** sweep passes. **Any** failure → Section 7.
- [ ] **UNRUN, manual** — submit **one real enquiry** through the live form and confirm it
      arrives. The sweep cannot prove delivery, only that the endpoint answered.

---

## Section 6 — After the swap

### 6.1 — Re-run everything that was measured against the old build (#50)

- [ ] `node scripts/seo-metadata-check.js --live` — the 11 tuned pages served **pre-plan**
      metadata throughout this phase, so the check reported served-matches-source on exactly
      those 11 while measuring the old build. Re-run, then close ledger #50.
- [ ] `scripts/asset-version-check.sh` — the `?v=<filemtime>` stamps are the **precondition**
      for the one-year CSS/JS cache lifetimes in `.htaccess`, not a nicety. A year-long
      lifetime on an unstamped URL is unreachable by any correction.
- [ ] `scripts/sitemap-check.sh --live` — **and only then** submit `sitemap.xml` in Search
      Console. **Do not submit if step 2.0 was skipped** (ledger #51): un-regenerated, the file
      lists 19 `https://torin.bg/new/` staging URLs, and submitting those is the exact opposite
      of the URL continuity this cutover exists to preserve. The check is what tells you which
      state you are in — if `base_url` was retargeted but the sitemap was not regenerated, D
      fails; if the sitemap was regenerated but `robots.txt` was not repointed, E fails.
- [ ] Re-run the rendered probes against the root origin.

### 6.2 — The SMTP-to-sendmail cascade, never once observed (#38)

**The functional heart of the notification design, and no real authentication failure has ever
been watched falling through.** It cannot be simulated on the build machine — it needs a live
host, a relay to fail against, and a deliberately wrong credential. Run **all three** cases:

- [ ] **(a) NO CREDENTIAL** — the shipped state. Submit the form. Expect log
      `mail no smtp credential, using sendmail`, then `mail delivered via=sendmail`.
- [ ] **(b) WRONG CREDENTIAL** — add `'smtp_password' => 'definitely-wrong'` to
      `/home/torin/torin-secrets.php`. Submit. Expect
      `mail smtp failed before acceptance, falling through to sendmail`, then
      `mail delivered via=sendmail`, a 303, and the email to arrive.
- [ ] **(c) CORRECT CREDENTIAL** — expect `mail delivered via=smtp` and **no** fall-through line.

> **IN ALL THREE CASES THE ENQUIRY MUST ARRIVE EXACTLY ONCE.** Two copies means the
> pre/post-acceptance boundary is wrong, which is the single outcome this design exists to
> prevent. The boundary is implemented by observing the relay's literal `354` reply rather than
> parsing PHPMailer's localised exception text; ambiguity resolves to **DO NOT RETRY**.

- [ ] Remove the deliberately wrong credential afterwards.

### 6.3 — The photo pipeline's human-eyes items

None of these can be closed by a script. Each needs a person looking at a screen.

- [ ] **#17 — one PORTRAIT photograph from a real handset**, submitted through `kontakti.html`,
      **checked upright in Telegram.** Every fixture used so far was produced by macOS `sips`,
      which bakes rotation into pixels and writes **no orientation tag** — and a file with no
      tag passes a completely broken pipeline identically to a correct one. This is the plan's
      own backstop truth EA-07; it is unverified on **both** sides.
- [ ] **#18 — somebody actually LOOKS at the owner's phone.** Every API call returned `ok:true`
      and the photos reached Telegram's servers, but that is an assertion about the API's
      answer, not about what rendered. Confirm: the single photo shows the enquiry **as its
      caption**, and three photos arrive as **ONE media group**, not three separate messages.
      One human glance closes it.
- [ ] **#21 — submit one WebP, or drop `webp` from the accept list.** The form advertises
      `accept=image/jpeg,image/png,image/webp` and routes WebP through
      `imagecreatefromwebp()` behind a `function_exists` guard, but no WebP was ever submitted.
      **GD's WebP decoder is a separate build option from `ext-gd` itself**, which is what the
      probe measured. If it is absent, every WebP the picker happily offers is refused with
      «файлът не е разпознат като снимка». Either prove it works or stop advertising it.

### 6.4 — Retire the transition guard

- [ ] Confirm `x-powered-by` at the root does **not** report PHP 5.2. *(Expected to pass: the
      root already reports `PHP/8.5.10` today, measured 2026-09-24 on `mailer.php`. Run it
      anyway — the point is to confirm the fail-safe is not what is holding the site up.)*
- [ ] **Only then**, delete the `<IfModule !mod_fcgid.c>` fail-safe block from the root
      `.htaccess`. It is kept through the swap on purpose: if the Section 2.1 panel step is
      missed, the block degrades the failure from "20 pages served to the public as readable
      PHP source" to "still on 5.2, which is where we already were". **A fallback quietly
      keeping 5.2 alive must never be allowed to pass as success.**

### 6.5 — Observation window

- [ ] Watch Search Console for new 404s and ranking movement.

> **Read the success criterion correctly or it fails by construction.** Google's own guidance
> is that consolidation after a protocol/host change takes **a few weeks or more**, and that no
> change-of-address tool is involved for a same-domain protocol or prefix move. **Movement
> during consolidation is expected and normal.** The criterion is *no unexplained drops and no
> new missing pages* — not *no movement*.

---

## Section 7 — Rollback

**The same move in reverse**, and it is seconds, which is the entire reason D4-28 chose
server-side renames over a re-upload.

1. Move `public_html/*` (the new site) back down to `public_html/new/`.
2. Move `/home/torin/old-site/*` back up to `public_html/`.
3. Restore the previous root `.htaccess` from the Section 1.5 backup.
4. Re-point cPanel → Select PHP Version back at `public_html/new/` so the wrapper path in the
   staging `.htaccess` resolves again.
5. Re-fetch the homepage and confirm the old site is serving.

**Rollback trigger:** any Section 5 gate failing. **If the sweep cannot fail, the rollback can
never fire** — which is why the sweep is required to have been demonstrated failing against a
known-bad case before it is trusted to pass a real one.

> **One thing rollback does NOT undo.** The canonicalisation redirects are a **one-way door**
> (D4-29). Moving files back restores the content; it does not un-consolidate a search index
> that has begun following `301`s to the apex. If the swap is rolled back, the redirects
> published in step 2.4 keep pointing at the apex — which is still correct for the old site,
> since it is served on the same four variants. Rolling back the *content* is cheap; rolling
> back the *canonical host choice* is not, and is not attempted here.
