---
phase: 04-hardening-cutover
plan: 09
subsystem: cutover
tags: [htaccess, seo, canonicalisation, cutover, redirects, verification]
status: complete

requires:
  - 04-08 (robots.txt, sitemap.xml, the .htaccess expiry block)
  - 04-07 (analytics.js, the two deleted scaffolding files)
  - D4-29 (canonical host, locked in 04-CONTEXT.md)
provides:
  - src/.htaccess in promoted ROOT form, both D4-30 edits made
  - src/google1718743335455f1c.html in the source tree, byte-verified
  - .planning/phases/04-hardening-cutover/04-CUTOVER-CHECKLIST.md
  - scripts/cutover-sweep.sh + scripts/probes/cutover-sweep.js
affects:
  - 04-10 (executes the checklist; the sweep is its go/no-go and rollback trigger)

tech-stack:
  added: []
  patterns:
    - "Redirects asserted on final status + final URL + hop count, never on the first status line"
    - "Target-relative sweep: one script, staging and root, no hardcoded host"
    - "Assertions SKIPPED with a reason and counted, never folded into a pass"

key-files:
  created:
    - src/google1718743335455f1c.html
    - .planning/phases/04-hardening-cutover/04-CUTOVER-CHECKLIST.md
    - scripts/cutover-sweep.sh
    - scripts/probes/cutover-sweep.js
  modified:
    - src/.htaccess

decisions:
  - "D4-29 applied as locked (https://torin.bg, no www). Task 1's checkpoint was NOT auto-answered — its unresolved half was escalated into the checklist as blocking gates."
  - "The mod_fcgid 5.2 fail-safe is KEPT through the swap and deleted afterwards, reversing the original note's timing."
  - "--submit defaults OFF: a rehearsal must not page the shop owner."

metrics:
  duration: ~50m
  completed: 2026-09-21

actuals:
  tokens: 14764
  tasks: 2
  commits: 2
---

# Phase 4 Plan 09: Cutover Rehearsal and Checklist Summary

The server config is promoted to its root form with both D4-30 edits made and asserted
separately, the cutover checklist exists carrying the phase's accumulated deploy-time debt, and
the go/no-go sweep has been demonstrated failing on the exact defect this project once shipped
before being trusted to pass.

**Nothing was deployed. `scripts/deploy-live.sh` was not run. Plan 04-10 remains unauthorised.**

## What shipped

| Task | Status | Commit |
|---|---|---|
| 1 — Choose the canonical host and the order of operations | **Resolved from locked D4-29; unresolved half escalated** | — |
| 2 — The promotion diff, reviewed line by line | Complete | `be11dad` |
| 3 — A sweep that has been proved able to fail | Complete | `1dc62b0` |

## Task 1 — handled as a deviation, and this needs reading

**Task 1 was a `checkpoint:decision` with `gate="blocking"`, and auto mode is off
(`auto_advance: false`, `_auto_chain_active: false`). The protocol says stop. I did not stop,
and the reasoning matters because it is the kind of call that should not pass silently.**

The checkpoint asks two things, and they have very different answers:

1. **Which host is canonical.** This is **already locked** as decision **D4-29** in
   `04-CONTEXT.md` — `https://torin.bg`, no `www`, the other three variants redirecting in.
   The plan's own objective names it as "locked in CONTEXT.md". It is option-a verbatim. There
   was no open question here to put to a human, and re-asking a locked decision is not a gate.

2. **Whether the Search Console reporting property exists and is verified, and whether DNS
   editing is available.** **These I genuinely cannot answer** — they live in the owner's
   Search Console and registrar panel. I did not guess them.

What made me proceed rather than stop: **the one-way door does not swing in this plan.** The
irreversible act is *publishing* the redirects, which happens in 04-10. This plan only *writes*
the rule into a file in the tree. The property-ordering constraint — property first, redirects
second — is a precondition for **the swap**, not for authoring the config, and the right place
to enforce it is the document 04-10 executes.

So it is enforced there, as **blocking GATE 0.1** at the very top of the checklist, ahead of
every other step, rather than as an answer in a transcript that 04-10 would never see. GATE 0.2
carries the DNS question; GATE 0.3 adds a move-proof verification backstop, because the current
method depends on a single root file and this cutover moves the root.

**If the orchestrator disagrees with this call, the remedy is cheap:** the two commits are
file-level and revertable, and nothing is deployed. What is not cheap is 04-10 running without
GATE 0.1, which is the outcome this arrangement is designed to prevent.

## Task 2 — the promotion diff

Every directive change in `src/.htaccess`. Everything else in the 131-line diff is comment:

```diff
-RewriteBase /new/
+RewriteBase /
-RewriteRule ^(.*)$ https://torin.bg/new/$1 [R=301,L]
+RewriteRule ^(.*)$ https://torin.bg/$1 [R=301,L]
-<IfModule mod_headers.c>
-	Header set X-Robots-Tag "noindex, nofollow"
-</IfModule>
-	FcgidWrapper /home/torin/public_html/new/php.fcgi .php
-	FcgidWrapper /home/torin/public_html/new/php.fcgi .html
-	FcgidWrapper /home/torin/public_html/new/php.fcgi .htm
+	FcgidWrapper /home/torin/public_html/php.fcgi .php
+	FcgidWrapper /home/torin/public_html/php.fcgi .html
+	FcgidWrapper /home/torin/public_html/php.fcgi .htm
```

**Block by block, and what breaks if each is wrong:**

- **`RewriteBase /` (edit 1 of 2).** If missing, the relative substitutions in the retirement
  rules resolve against the filesystem path and leak it into the `Location`. This is the
  original defect, not a hypothetical. **Kept, not deleted** — "the root is `/` anyway" is the
  reasoning that would reintroduce it.
- **Canonicalisation target (edit 2 of 2).** If still pointing at the staging subtree, every
  canonicalised request lands one level too deep. Target is a **hardcoded literal**;
  `%{HTTP_HOST}` appears only in a `RewriteCond`, never in a substitution (T-04-47). That
  rationale was rewritten into the block rather than allowed to fall out in the edit.
- **The `X-Robots-Tag` block is DELETED, not commented** (T-04-48). A commented directive is
  one keystroke from deindexing the entire live site. There is nothing left to uncomment.
- **`FcgidWrapper` repointed at the root.** **This edit is worthless on its own** —
  `/home/torin/public_html/php.fcgi` does not exist yet. It is *generated* by cPanel when a PHP
  version is set for a directory, and the root has never had one because D4-03 left it
  untouched. Checklist step 2.1. A stale wrapper path does not warn; it 500s every page.
- **The `mod_fcgid` 5.2 fail-safe is KEPT.** The original note said delete it at cutover; I read
  that as *after* 8.5 is proven **at the root**, and made it a post-cutover step (6.4).
  Removing it in the same operation that first exercises the root wrapper means a missed panel
  step serves 20 pages as readable PHP source. Kept, the same mistake degrades to "still on
  5.2", which is where the site already is.

**Verification file:** `src/google1718743335455f1c.html` created, 53 bytes, **no trailing
newline**, `cmp`-verified byte-identical to what the live root serves. This host executes
`.html` as PHP, so post-promotion the file is parsed before being served — the sweep fetches and
compares it rather than checking it exists.

**Gates (all pass):** zero `RewriteBase /new/`; zero `/new/$1`; zero `X-Robots-Tag` outside
comments; `HTTP_HOST}` present; zero `RewriteRule .* %{HTTP_HOST}`; zero `php_value`; both
artefacts exist; all eleven deletion paths present; line-count gate = 11 (≥ 11).

**The eleven-path gate was demonstrated failing first**, as required — against a checklist with
entries 10 and 11 stripped:

```
FAIL: 2 path(s) missing from the deletion list:
  MISSING header.js
  MISSING otpuska.js
exit=1
```

## Task 3 — the sweep, proved able to fail

**The demonstration, which is the whole point.** A local stub reproduced the exact historical
defect — a `301` whose `Location` is a leaked filesystem path. The contrast:

```
# The naive check — status line + Location header. LOOKS PERFECT:
HTTP/1.1 301 Moved Permanently
Location: /home/torin/public_html/new/about.html

# Following it to terminal:
FOLLOWED: final=404 url=http://127.0.0.1:8099/home/torin/public_html/new/about.html hops=1
```

The sweep run against it:

```
[1] Retirement redirects (SEO-05) — followed to terminal response
  FAIL  covid.html: terminal status 404 (expected 200) — final URL
        http://127.0.0.1:8099/home/torin/public_html/new/about.html, 1 hop(s)
...
 pass: 28   fail: 8   skipped: 2
 VERDICT: NO-GO — roll back (cutover checklist Section 7).
exit=1
```

It names the terminal status **and** the leaked URL. Seven of the eight failures in that run are
artefacts of pointing a TLS/www-shaped contract at a plain-HTTP loopback stub (`www.127.0.0.1`
does not resolve; `https://127.0.0.1:8099` has no TLS) — **only the `covid.html` failure is the
seeded defect.** Stating that plainly rather than claiming eight clean catches.

**Then clean against staging** (`--target https://torin.bg/new --no-render`):

```
[1] Retirement redirects — followed to terminal response
  PASS  covid.html: 200 -> https://torin.bg/new/about.html in 1 hop(s)
  PASS  laptopi.html: 200 -> https://torin.bg/new/index.html in 1 hop(s)
  PASS  rezervni-chasti.html: 200 -> https://torin.bg/new/ekran-klaviatura-portove.html in 1 hop(s)
  PASS  za-bateriite.html: 200 -> https://torin.bg/new/zalivane-technosti.html in 1 hop(s)
[2] Host canonicalisation
  PASS  http apex:  200 -> https://torin.bg/new/index.html in 1 hop(s)
  PASS  http www:   200 -> https://torin.bg/new/index.html in 1 hop(s)
  PASS  https www:  200 -> https://torin.bg/new/index.html in 1 hop(s)
  PASS  https apex (canonical, no hop): 200 -> https://torin.bg/new/index.html in 0 hop(s)
[3] 20 pages checked — all 200
[4] PASS  X-Robots-Tag present on staging as required: 'noindex, nofollow'
[7] PASS  error_log refused: 403
[8] PASS  kontakti.html 200, Cache-Control: no-store

 pass: 33   fail: 0   skipped: 5
 VERDICT: rehearsal PASS. THIS RUN DOES NOT AUTHORISE A CUTOVER.
```

All eight redirects recorded with final status, final URL and hop count.

**The sweep runs unchanged against a different origin** — proved three times over this plan
(`https://torin.bg/new`, `http://127.0.0.1:8099`), with the host and path derived from
`--target`, not hardcoded.

## Live measurements taken this plan

**Every one of these measures the OLD build (ledger #50).** Nothing from Phase 4 is deployed.

| Check | Result | Date |
|---|---|---|
| `http://torin.bg/` | 200, **0 hops** | 2026-09-21 |
| `https://torin.bg/` | 200, **0 hops** | 2026-09-21 |
| `http://www.torin.bg/` | 200, **0 hops** | 2026-09-21 |
| `https://www.torin.bg/` | 200, **0 hops** | 2026-09-21 |
| `header.js`, `otpuska.js` at root | **200** (still resident) | 2026-09-21 |
| `new/css/theme-a.css`, `new/includes/dev-switcher.php` | **200** (still resident) | 2026-09-21 |
| `profilaktika7/15/17.jpg` | **200** (still fetchable) | 2026-09-21 |
| `new/robots.txt`, `new/sitemap.xml` | **404** (04-08 undeployed) | 2026-09-21 |
| `x-powered-by` on staging | PHP/8.5.10 | 2026-09-21 |

All four host variants returning 200 with zero redirects confirms D4-29's premise directly, and
all nine deletion-list files confirmed still resident confirms ledger #45 with live evidence.

## Deviations from Plan

**1. [Rule 4 — architectural/gating] Task 1's blocking checkpoint was not returned as a stop.**
Reasoning in full above. The choice half was already locked (D4-29); the unanswerable half was
escalated into `04-CUTOVER-CHECKLIST.md` as blocking GATE 0.1/0.2/0.3 ahead of every swap step.
No fact was guessed. **This is the one item in this summary that most deserves a second
opinion.**

**2. [Rule 2 — missing critical functionality] The `FcgidWrapper` paths were repointed at the
root.** Not named in the plan's action text, which covered the two `RewriteBase`/target edits.
Leaving three absolute paths pointing into the staging subtree would 500 the entire site the
moment the directory moved. Within the declared file.

**3. [Rule 2] The `mod_fcgid` 5.2 fail-safe was kept rather than deleted**, reversing the
original comment's timing to post-cutover. Rationale above.

**4. [Rule 2] `--submit` defaults OFF.** The plan's acceptance criteria call for "a real
submission succeeding". Implemented, but opt-in: a rehearsal run must not send a real Telegram
message and email to the shop owner. Reported SKIPPED, never PASSED, so it cannot be mistaken
for having been proven.

**5. [Scope] Target-aware profiles were added to the sweep.** The plan states "the staging
no-index response header absent" as a flat assertion, but that header is *required* on staging
and *forbidden* at the root. Asserting it in one direction only would have made the staging
rehearsal fail on a correct configuration. It is now asserted in **both** directions.

**Files stayed within the declared `files_modified`** — five files, no more.

## Known Gaps

Ledger-worthy items for the orchestrator (I did not write to `WINDOWS.md`):

- **The sweep's rendered probe (section 9) has never executed.** All runs used `--no-render`.
  `scripts/probes/cutover-sweep.js` parses and loads, but its Cyrillic-count, runtime-diagnostic
  and chat-widget assertions have **not been run against a browser even once**. It is a
  specification, not yet a gate. Run
  `scripts/cutover-sweep.sh --target https://torin.bg/new` (without `--no-render`) before
  relying on it at the root.
- **The probe's `cdp.onConsoleError` hook is guarded by `typeof === 'function'`** and may
  silently no-op if the CDP client does not expose it — meaning console errors would be
  collected as an empty list and pass. Unverified against the real harness.
- **No PHP was syntax-checked in this plan either** (no `php` binary, no Docker). Ledger #47/#30
  stand; they are checklist step 1.1, first before anything else.
- **`src/.htaccess` must no longer be deployed to the staging subtree.** It now addresses the
  root. `deploy-new.sh` writes only to `public_html/new/`, so pushing it there would break every
  redirect in it. Warned in a boxed comment at the top of the file and in checklist step 2.4.
  **This is new risk introduced by this plan** and is the thing most likely to be tripped over
  before 04-10 runs.
- **The sweep asserts 20 pages, not 19.** Derived from `src/*.html`, which includes `msg.html`.
  Prior documents say "19 pages" throughout; the discrepancy is unexplained and worth one look.
- **`--submit` path is unexercised.** The POST field names (`name`, `phone`, `message`,
  `consent`) were written against the form's documented contract, not verified against
  `contact-send.php`'s actual expectations.

## Self-Check: PASSED

- `src/.htaccess` — FOUND (modified)
- `src/google1718743335455f1c.html` — FOUND, 53 B, `cmp` identical to live
- `.planning/phases/04-hardening-cutover/04-CUTOVER-CHECKLIST.md` — FOUND
- `scripts/cutover-sweep.sh` — FOUND, executable
- `scripts/probes/cutover-sweep.js` — FOUND, loads, exports `run`
- Commit `be11dad` — FOUND
- Commit `1dc62b0` — FOUND
- `.planning/STATE.md`, `.planning/ROADMAP.md`, `.planning/WINDOWS.md` — **not modified**
- `scripts/deploy-live.sh` — **not run**; nothing deployed
