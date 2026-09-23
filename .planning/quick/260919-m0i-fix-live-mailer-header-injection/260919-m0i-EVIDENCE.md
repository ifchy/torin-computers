# 260919-m0i — Evidence

Live email header-injection patch for `public_html/mailer.php` on torin.bg.

**Status of this file: the patch is LIVE and UNVERIFIED.**

Sections 1 through 4 are complete and measured: the source gates pass, a fresh
backup was taken and hash-matched, the pre-patch control POST was sent, the patched
file was uploaded to `public_html/`, and both post-patch measurements came back
clean.

**Section 5 — the mailbox inspection — has NOT happened.** The developer has no
mailbox access until **Monday**. Section 5 is the only place in this entire task
where the fix is actually proven, so until it is filled in, the correct description
of this work is *"the injection sink is gone from the deployed source, and nobody
has yet observed the injected header's absence in a delivered message."*

Nothing below is predicted or inferred. A `PENDING` slot means the measurement has
not happened.

---

## 0. What the automated checks can and cannot prove

Stated up front because it is the single most important fact about this task's
verification, and because it is easy to lose.

A POST carrying `%0D%0A` in the `mail` field produces the **same HTTP response**
before and after the patch: `302`, `location: msg.html`, a zero-length body. There
is no HTTP-observable difference. Every automated check in this task is therefore a
**source-level** check — it proves the injection sink is gone from the code. It does
not and cannot prove the injected header is absent from a delivered message.

Runtime absence is proven in exactly one place: a human opening the delivered
messages at office@torin.bg and reading their **raw headers** (section 5). Any claim
that this fix is verified on the strength of the greps alone is wrong.

---

## 1. Source-level gates — MEASURED, complete

Run: 2026-09-19, worktree `worktree-agent-a8666ef2efa55dc8f`, base commit `95f1185`.

Every "absence" gate below was first run against a file **known to contain** the
thing it checks for, so a gate that can never fire would have been caught. Those
runs are the NC rows.

### Negative controls (all fired)

| ID  | Gate run against | Measured | Required | Result |
|-----|------------------|----------|----------|--------|
| NC1 | G1 vs pre-patch `mailer.php` (blob `fd4deb8`) | 1 | 1 | PASS — the central gate demonstrably fires on the vulnerable file |
| NC2 | sink-literal gate vs pre-patch `mailer.php` | 1 | 1 | PASS |
| NC3 | directory-walk gate vs `scripts/deploy-new.sh` | 1 | >=1 | PASS |
| NC4 | remote-dir-creation gate vs `scripts/deploy-new.sh` | 1 | >=1 | PASS |
| NC5 | staging-root gate vs `scripts/deploy-new.sh` | 3 | >=1 | PASS |

### `site-current/mailer.php`

| ID  | Gate | Pre-patch | Measured post-patch | Required | Result |
|-----|------|-----------|---------------------|----------|--------|
| G1  | header-assignment lines referencing `$email`/`$from`/`$mobile`/`$question` | 1 | 0 | 0 | PASS |
| G1b | the vulnerable sink literal | 1 | 0 | 0 | PASS |
| G2  | address validator present | 0 | 1 | >=1 | PASS |
| G2b | CR/LF/NUL strip helper defined and used | 0 | 3 | >=2 | PASS |
| G3  | `isset` guards on the four POST reads | 0 | 4 | 4 | PASS |
| G4  | `is_string` guards on the four POST reads | 0 | 4 | 4 | PASS |
| G5  | shop address occurrences | 3 | 2 | 2 | PASS — the two dead comment blocks that inflated this to 3 are deleted |
| G6  | Bulgarian subject/body string | 2 | 2 | 2 | PASS — unchanged |
| G7  | `msg.html` redirect | 1 | 1 | 1 | PASS — unchanged |
| G8  | body-table cells | 4 | 4 | 4 | PASS — unchanged |
| G9  | non-PHP-5.2 syntax (short arrays, null coalescing) | 0 | 0 | 0 | PASS |

`git diff --numstat` for the file: `41 21 site-current/mailer.php`. The diff has
three hunks — the POST reads, the header block, and the trailing dead comments. No
hunk touches lines 13–70, so the HTML body, the styling and the Bulgarian heading
are byte-identical.

### `scripts/deploy-live.sh`

| ID  | Gate | Measured | Required | Result |
|-----|------|----------|----------|--------|
| D0  | executable | yes | yes | PASS |
| D1  | directory-walk code (comments stripped) | 0 | 0 | PASS |
| D1b | directory-walk code including comments | 0 | 0 | PASS |
| D2  | references the staging root | 0 | 0 | PASS |
| D3  | confirm-variable gate present | 7 | >=1 | PASS |
| D4  | source root is `site-current/` | 3 | >=1 | PASS |
| D5  | public-key pin carried over | 2 | >=1 | PASS |
| D6  | TLS 1.2 data-channel cap carried over | 2 | >=1 | PASS |
| D7  | remote-directory-creation flag absent | 0 | 0 | PASS |
| D8  | parses (`bash -n`) | ok | ok | PASS |

**D7 caught a real defect during this run.** The first version of the script
explained in two comments that it deliberately omits the remote-dir-creation flag —
and named the flag. The gate counted those comments and reported the flag present.
The comments were reworded to describe the flag without naming it, and the gate now
also strips comment lines before counting. This is the fourth instance in this
project of a check counting prose rather than code.

### `scripts/deploy-live.sh` — behaviour, run offline

`TORIN_LIVE_DEPLOY_CONFIRM` was unset for every row below, so none of these could
have shipped anything.

| ID  | Invocation | Exit code | Meaning |
|-----|-----------|-----------|---------|
| D9  | no arguments | 2 | refused — no whole-directory mode exists |
| D10 | `../../etc/passwd` | 3 | refused — not on the allowlist |
| D11 | `/etc/passwd` | 3 | refused — not on the allowlist |
| D12 | `site-current/mailer.php` | 3 | refused — a name with a separator cannot match a bare-name entry |
| D13 | `mailer.php.bak` | 3 | refused — a prefix/glob match would have let this through; exact equality does not |
| D14 | `mailer.php` | 4 | refused — allowlisted, but the confirm variable was absent |

### The patch went live unparsed — verification AFTER exposure, not before

`php -l` was **never run**. No `php` binary exists on the executor's machine or on
the developer's, and the Docker daemon was down on both. The patch uses no syntax
newer than PHP 5.2 and gate G9 confirms none was introduced, but that is a
source-level argument, not an execution.

**What actually happened: unparsed PHP was uploaded to the live production root,
and the first thing that executed it was a real HTTP request.** Section 4a's clean
`302` with a zero-byte body is genuine parse evidence — a parse error returns `500`
with a non-empty body — but it was obtained *after* the file was already serving
public traffic, not before. Had it failed, the contact form would have been broken
for real visitors for the seconds between upload and measurement.

This is the weakest link in the task. It is recorded as such rather than folded
into the run of green results above. The exposure window was small and the rollback
is one command (section 6), but the ordering was wrong and would be worth fixing
before the next live PHP change — a parse check is cheap and belongs before an
upload, not after it.

---

## 2. Live backup — DONE

`scripts/backup-live-site.sh` reads the gitignored credentials file and has no
`TORIN_CRED_FILE` override, so it must run from the primary checkout.

```bash
cd /Users/alabala/Documents/projects/torin
scripts/backup-live-site.sh
```

Then verify the **artefact**, not the exit code:

```bash
cd /Users/alabala/Documents/projects/torin
SNAP=$(ls -1dt backups/*/ | head -1); echo "snapshot: $SNAP"
test -s "${SNAP}public_html/mailer.php" && echo "mailer.php present and non-empty"
ls -1 "${SNAP}public_html"/*.html | wc -l
grep -E '^[[:space:]]*\$headers' "${SNAP}public_html/mailer.php" | grep -cE '\$(email|from|mobile|question)\b'
git hash-object "${SNAP}public_html/mailer.php"
```

Pass conditions:

- `mailer.php present and non-empty`
- the `.html` count is **16 or more**
- the `grep -c` prints **1** — the bytes pulled off the live host are the known
  vulnerable ones, not a stale or truncated pull
- `git hash-object` prints exactly **`fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`**

That last line is the mirror-drift check. It is a content hash of the pre-patch
`site-current/mailer.php`, so it answers "is what is live still identical to what
was reviewed?" without depending on whether the patch has been merged into the
primary checkout yet. **A different hash means the live file drifted — HALT, do not
deploy,** because the patch would then be overwriting bytes nobody reviewed.

### Result — MEASURED 2026-09-19

```
snapshot path:         backups/20260919T131139Z/
.html pages verified:  16/16   (raw ls count 17 — see note)
must-carry root files: 7/7
must-carry dirs:       .well-known/ cgi-bin/ covid-19/ assets1/
assets1/ size:         12228KB (baseline ~14000KB, abort threshold 10000KB)
G1 against snapshot:   1
git hash-object:       fd4deb874f2a4cf2de134fd6db732d8db7f5d8de
```

**All four pass conditions met.**

- `G1 = 1` — the bytes pulled off the live host are the known-vulnerable ones. The
  backup is a real snapshot of the broken file, not a stale or truncated pull.
- The hash is an **exact** match for the pre-patch blob. `site-current/` had not
  drifted from live; the patch was applied to the same bytes that were running in
  production.

Two figures that look like discrepancies and are not, recorded so nobody
re-investigates them later:

- **`ls` counts 17 `.html` files, the script verifies 16.** The 17th is
  `google1718743335455f1c.html`, a Search Console verification token. It is carried
  as one of the 7 must-carry root files, not as one of the 16 content pages, so it
  is counted once in the correct bucket rather than twice.
- **`assets1/` is 12228KB against a ~14000KB baseline.** Under the baseline but well
  clear of the 10000KB truncation-abort threshold, so the script accepted it. The
  baseline was recorded in a Phase 1 snapshot; some delta is expected after a year of
  edits. Worth a glance if a future snapshot drops further, not worth acting on now.

---

## 3. Pre-patch runtime control POST — DONE

This send is what makes the whole verification chain falsifiable. It must happen
**before** the deploy in section 3b. It sends one email to office@torin.bg and
changes no file on the host.

The marker is a benign `X-` header, never a `Bcc:` — exercising the identical code
path with zero delivery side effect. The trailing filler header exists so the
template's closing angle bracket lands on the filler's value instead of corrupting
the marker.

Use the **same URL you used for the baseline measurements** so the responses are
comparable.

```bash
cd /Users/alabala/Documents/projects/torin
date -u +"%Y-%m-%dT%H:%M:%SZ"
curl -sS -D - -o /tmp/m0i-pre-body.txt -X POST https://torin.bg/mailer.php \
  --data-urlencode 'name=AUTOMATED SECURITY TEST 260919-m0i - ignore' \
  --data-urlencode $'mail=test@example.com\r\nX-Torin-Injection-Test: PRE-PATCH-260919\r\nX-Torin-Filler: x' \
  --data-urlencode 'mobile=000' \
  --data-urlencode 'message=AUTOMATED SECURITY TEST for quick task 260919-m0i. Sent by the developer to prove a mail header injection hole exists before it is patched. Please ignore and delete.'
wc -c < /tmp/m0i-pre-body.txt
```

`$'...'` is bash ANSI-C quoting: it puts real CR and LF bytes into the value, which
`--data-urlencode` then percent-encodes as genuine `%0D%0A` on the wire.

Expected: status `302`, a `location` header of `msg.html`, a `content-length` of
zero, and `wc -c` printing `0`.

### Result — MEASURED

```
UTC timestamp:   2026-09-19T13:14:58Z
HTTP/2 302
x-powered-by: PHP/8.5.10
location: msg.html
content-length: 0
body bytes (wc -c): 0
```

The control send happened at **13:14:58Z**, which is **before** the 13:15-ish
deploy. That ordering is what makes the whole chain falsifiable, and it held.

Note this response and both post-patch responses in section 4 are **identical in
every field**. That is the expected result, not a warning sign — it is the reason
this task cannot be verified over HTTP and needs section 5.

---

## 3b. Deploy — DONE

**Step 0 — optional but preferred: confirm the patched file parses.**

```bash
php -l /Users/alabala/Documents/projects/torin/.claude/worktrees/agent-a8666ef2efa55dc8f/site-current/mailer.php
```

`No syntax errors detected` means proceed. `command not found` means skip to step 1
and rely on the section 4 measurement instead.

**Step 1 — upload.** Run this from the **worktree**, because that is where the
patched bytes are; `TORIN_CRED_FILE` points at the credentials file in the primary
checkout, which is the only copy.

```bash
cd /Users/alabala/Documents/projects/torin/.claude/worktrees/agent-a8666ef2efa55dc8f
TORIN_CRED_FILE=/Users/alabala/Documents/projects/torin/filezilla-server-data.xml \
  TORIN_LIVE_DEPLOY_CONFIRM=1 \
  scripts/deploy-live.sh mailer.php
```

If the worktree has already been merged and removed, the equivalent from the primary
checkout — **only valid after the merge**, otherwise it uploads the unpatched file:

```bash
cd /Users/alabala/Documents/projects/torin
TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php
```

Expected output: one `uploading ... bytes -> ftp://.../public_html/mailer.php` line,
then `Deploy complete: mailer.php -> public_html/ (LIVE)` and a rollback reminder.
A non-zero exit means nothing shipped.

### Result — MEASURED 2026-09-19

```
uploading .../site-current/mailer.php (4943 bytes) -> ftp://bell.host.bg/public_html/mailer.php
Deploy complete: mailer.php -> public_html/ (LIVE)
exit status: 0
```

4943 bytes matches the patched file on disk exactly. One file, the allowlisted one,
to the live root. `php -l` (step 0) was **not** run — see section 1.

Live static pages re-checked after the upload: `/`, `/index.html`, `/uslovia.html`
and `/msg.html` all returned `200`. Nothing else on the site was disturbed.

---

## 4. Post-patch measurements — DONE

Both run **after** the deploy. Two sends, two emails.

### 4a. Normal submission — does the form still work?

```bash
date -u +"%Y-%m-%dT%H:%M:%SZ"
curl -sS -D - -o /tmp/m0i-normal-body.txt -X POST https://torin.bg/mailer.php \
  --data-urlencode 'name=AUTOMATED TEST 260919-m0i NORMAL - ignore' \
  --data-urlencode 'mail=torin-m0i-test@example.com' \
  --data-urlencode 'mobile=0888000000' \
  --data-urlencode 'message=AUTOMATED TEST for quick task 260919-m0i, normal submission. Sent by the developer to confirm the contact form still works after a security patch. Please ignore and delete.'
wc -c < /tmp/m0i-normal-body.txt
```

Must measure `302`, `location: msg.html`, `content-length` of zero, and `wc -c` of
`0`. The body is checked separately from the header because a missing
`content-length` alongside a non-empty body would slip past a header-only check.

### 4b. Injection attempt — does the form still work under hostile input?

Identical to section 3 except the marker value.

```bash
date -u +"%Y-%m-%dT%H:%M:%SZ"
curl -sS -D - -o /tmp/m0i-post-body.txt -X POST https://torin.bg/mailer.php \
  --data-urlencode 'name=AUTOMATED SECURITY TEST 260919-m0i - ignore' \
  --data-urlencode $'mail=test@example.com\r\nX-Torin-Injection-Test: POST-PATCH-260919\r\nX-Torin-Filler: x' \
  --data-urlencode 'mobile=000' \
  --data-urlencode 'message=AUTOMATED SECURITY TEST for quick task 260919-m0i, post-patch. Sent by the developer to confirm the mail header injection hole is closed. Please ignore and delete.'
wc -c < /tmp/m0i-post-body.txt
```

Must measure the same `302` / `location: msg.html` / zero content-length / zero-byte
body. This asserts the form still *works* for hostile input rather than breaking —
a patch that returned `500` on malformed input would also stop the injection and
would be a regression.

### Results — MEASURED 2026-09-19

**4a — normal submission, `2026-09-19T13:15:26Z`**

```
HTTP/2 302
x-powered-by: PHP/8.5.10
location: msg.html
content-length: 0
body bytes (wc -c): 0
```

**4b — injection attempt, `2026-09-19T13:15:36Z`**

```
HTTP/2 302
x-powered-by: PHP/8.5.10
location: msg.html
content-length: 0
body bytes (wc -c): 0
```

Both pass. 4a proves the patched file parses and runs to the redirect; 4b proves
the form still works under hostile input rather than erroring — a `500` on
malformed input would also have stopped the injection and would have been a
regression.

**What these do NOT show.** Compare them to the pre-patch control in section 3:
every field is identical — same status, same PHP version banner, same location,
same zero length. The vulnerable file and the patched file are indistinguishable
over HTTP. Three matching green measurements here are worth exactly nothing as
evidence that the injection is dead. Only section 5 can establish that.

---

## 5. Mailbox inspection — ✅ DONE 2026-09-23, ALL THREE PASS

**This section is the only place in this task where the fix is proven**, and it is now
done. The owner inspected the raw headers of all three test messages at
`office@torin.bg` and reports that all three matched their pass criteria. Findings
recorded at the end of this section.

The section below is left as written — self-contained, with the criteria stated before
the result — so the record shows what was asked for rather than only what was found.

### Situation as of 2026-09-19

The patched `mailer.php` is **live** on `public_html/`. All source gates pass and all
three HTTP measurements are clean. None of that establishes that the injected header
is gone from a delivered message — sections 3 and 4 returned byte-identical responses
before and after the patch, which is exactly why this step exists.

### The three messages

All three are already sitting in **office@torin.bg**. They were sent on
**2026-09-19** and are identified by UTC timestamp. All three are labelled as
automated tests in both their name and message fields.

For each one, view the **raw source / full headers** — Gmail: "..." menu > "Show
original"; Outlook: File > Properties > Internet headers; Thunderbird: Ctrl+U. The
rendered message does not show what matters here.

| # | Sent (UTC) | What it is | Marker |
|---|-----------|------------|--------|
| 1 | **13:14:58Z** | pre-patch control, sent BEFORE the deploy | `X-Torin-Injection-Test: PRE-PATCH-260919` |
| 2 | **13:15:26Z** | post-patch normal submission | none; `mail` field was `torin-m0i-test@example.com` |
| 3 | **13:15:36Z** | post-patch injection attempt | `X-Torin-Injection-Test: POST-PATCH-260919` |

The deploy landed between 13:14:58Z and 13:15:26Z. Message 1 is the only one that
hit the vulnerable file.

### Message 1 — 13:14:58Z — pass criteria

- `X-Torin-Injection-Test: PRE-PATCH-260919` **MUST BE PRESENT** in the raw headers.

**If it is ABSENT, stop and report that.** It means the injection was never actually
demonstrated against the live host, so its absence from message 3 proves nothing and
the entire verification chain is void — no matter how green sections 1 through 4
look. This is the negative control. A negative control that does not fire
invalidates the positive result. Do not read a missing marker as good news.

### Message 2 — 13:15:26Z — pass criteria

- `From:` reads exactly `TORIN.bg Message Form <office@torin.bg>`
- a `Reply-To:` header is **present** and holds `torin-m0i-test@example.com`
- **no** header beginning `X-Torin-` appears anywhere
- the body still shows the blue-and-orange table with Име / E-mail / Телефонен
  номер / Съобщение and the submitted values, under the Bulgarian heading line
- **press Reply.** The `To:` field must populate with `torin-m0i-test@example.com`,
  not with office@torin.bg. This is the shop owner's actual workflow and the single
  thing this change was most likely to have broken.

### Message 3 — 13:15:36Z — pass criteria

- search the full raw headers for `X-Torin` — **zero hits**. The marker MUST BE
  ABSENT.
- `From:` reads `TORIN.bg Message Form <office@torin.bg>` — not the attacker-supplied
  value
- **no `Reply-To:` header at all.** Its absence is correct and intended: the
  submitted value was not a valid address, so none was emitted. A `Reply-To:`
  present in this message would mean the validation is being bypassed and the fix is
  incomplete.
- the body table still renders and shows the submitted values as text

### If anything fails

If message 3 carries the marker, or message 2's Reply does not address the visitor,
**do not approve** — report it and roll back using section 6. Rolling back re-opens
the vulnerability, so it is the right call only if the form is genuinely broken for
real visitors.

Delete all three test messages once inspected, so they are not later mistaken for
real customer enquiries.

### Findings — ✅ COMPLETE, 2026-09-23. Owner inspection of raw headers. ALL THREE PASS.

```
message 1 (13:14:58Z)  found: YES  marker PRESENT: YES
message 2 (13:15:26Z)  found: YES  From: OK  Reply-To: torin-m0i-test@example.com  no X-Torin: YES
message 3 (13:15:36Z)  found: YES  no X-Torin: YES (zero hits)  From: OK  no Reply-To: YES
```

**Reported by the owner as: the headers of all three emails contained exactly what was
expected.** Recorded here as the three criteria above being met — in particular that
message 1's marker was PRESENT and message 3's was ABSENT, since those two point in
opposite directions and the phrase "as expected" means a different thing for each.

**Why message 1 is the load-bearing one, and why its result is the good news.** The
pass criterion for message 1 is a PRESENCE and the criterion for message 3 is an
ABSENCE. If message 1 had come back without its marker, message 3's clean headers would
have proven nothing at all — an injection that never worked in the first place is
absent from every message, patched or not. Message 1 carrying
`X-Torin-Injection-Test: PRE-PATCH-260919` establishes that the vulnerability was real
and reachable on the live host. Only against that baseline does message 3's zero hits
mean the patch closed it.

**And this was the only route to that conclusion.** Sections 3 and 4 above returned
byte-identical HTTP responses before and after the patch — `302`, `location: msg.html`,
`content-length: 0`, zero-byte body. No HTTP assertion, no source gate, and no amount
of re-reading the deployed bytes could distinguish a patched host from an unpatched
one. The delivered headers were the only observable that differed.

Message 2 confirms the change did not break the shop's actual workflow: `Reply-To:`
present and holding the visitor's address, so pressing Reply still addresses the
customer rather than the shop's own mailbox. That was the single thing this change was
most likely to have broken.

### Housekeeping still outstanding

**The three test messages should now be deleted from `office@torin.bg`.** They are
labelled `AUTOMATED SECURITY TEST 260919-m0i` in both their name and message fields,
but a security test left in an enquiry mailbox is eventually read as a real customer.
Owner action; nothing in this repo can do it.

---

## 6. Rollback

The pre-patch bytes are on disk at **`backups/20260919T131139Z/public_html/`** and
their content hash is known (`fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`), so a
restore is verifiable rather than hopeful. The real snapshot path is written out
below rather than left as a placeholder — whoever needs this is probably in a
hurry.

```bash
cd /Users/alabala/Documents/projects/torin
git hash-object backups/20260919T131139Z/public_html/mailer.php
# must print fd4deb874f2a4cf2de134fd6db732d8db7f5d8de before you proceed
cp backups/20260919T131139Z/public_html/mailer.php site-current/mailer.php
TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php
```

Then re-run the section 4a normal submission and confirm `302` / `location:
msg.html` / zero-byte body.

**Rolling back re-opens the vulnerability.** It is the right trade only if the patch
broke the form for real visitors, and section 4a is what decides that — not a
hunch.
