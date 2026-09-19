# 260919-m0i — Evidence

Live email header-injection patch for `public_html/mailer.php` on torin.bg.

**Status of this file: PARTIAL.** The source-level gates are complete and measured.
The four live-host steps (backup, pre-patch control POST, deploy, post-patch POSTs)
were NOT run by the executor — they require the FTP credentials and a live-root
write, both of which are the developer's to perform. Their sections below carry the
exact commands and empty result slots. **Nothing in those slots is predicted,
inferred, or filled in advance.** An empty slot means the measurement has not
happened.

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

### Not verified: does the patched PHP parse?

No `php` binary is installed on this machine and the Docker daemon is not running,
so `php -l` could not be run. The patch uses no syntax newer than PHP 5.2 (G9), but
**"it should parse" is not "it parses."** Two mitigations, in order:

1. Run `php -l` before deploying if any PHP is reachable (section 3, step 0).
2. If not: the normal-submission measurement in section 4 is a runtime parse proof.
   A parse error returns `500` with a non-empty body, which is not `302` with a
   zero-byte body. Rollback is one command (section 6).

---

## 2. Live backup — NOT RUN (developer step)

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

### Result — PENDING

```
snapshot path:
mailer.php non-empty:
.html count:
G1 against snapshot:
git hash-object:
```

---

## 3. Pre-patch runtime control POST — NOT RUN (developer step)

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

### Result — PENDING

```
UTC timestamp:
status line:
location header:
content-length header:
body bytes (wc -c):
```

---

## 3b. Deploy — NOT RUN (developer step)

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

### Result — PENDING

```
uploaded bytes:
exit status:
```

---

## 4. Post-patch measurements — NOT RUN (developer step)

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

### Results — PENDING

```
4a UTC timestamp:
4a status line:
4a location header:
4a content-length header:
4a body bytes:

4b UTC timestamp:
4b status line:
4b location header:
4b content-length header:
4b body bytes:
```

---

## 5. The three messages the human must inspect

All three land at **office@torin.bg**. View **raw source / full headers** for each —
Gmail "Show original", Outlook File > Properties > Internet headers, Thunderbird
Ctrl+U. The rendered message does not show what matters.

| # | Marker / identity | Sent | Must contain | Must NOT contain |
|---|-------------------|------|--------------|------------------|
| 1 | `X-Torin-Injection-Test: PRE-PATCH-260919` | section 3, **before** deploy | the marker header **PRESENT** | — |
| 2 | normal, `torin-m0i-test@example.com` | section 4a, after deploy | `From: TORIN.bg Message Form <office@torin.bg>`; a `Reply-To:` holding the submitted address; the blue/orange table with Име / E-mail / Телефонен номер / Съобщение and the Bulgarian heading | any header beginning `X-Torin-` |
| 3 | `X-Torin-Injection-Test: POST-PATCH-260919` | section 4b, after deploy | `From: TORIN.bg Message Form <office@torin.bg>`; the body table rendering the submitted values as text | **any** `X-Torin` header; **any** `Reply-To:` header |

Two of these deserve emphasis:

- **Message 1's marker must be PRESENT.** If it is absent, the injection was never
  demonstrated, so its absence from message 3 proves nothing and the entire
  verification is void — regardless of how green section 1 looks. Report it rather
  than treating it as good news.
- **Message 3 must have NO `Reply-To:` at all.** Its absence is the intended
  behaviour: the submitted value was not a valid address, so no such header was
  emitted. A `Reply-To:` present here would mean the validation is being bypassed.

Also on message 2: **press Reply.** The `To:` field must populate with
`torin-m0i-test@example.com`, not with office@torin.bg. That is the shop owner's
existing workflow and the thing this change was most likely to break.

Delete all three once inspected so they are not mistaken for real enquiries.

### Findings — PENDING

```
message 1 found:            marker present:
message 2 found:            From:                    Reply-To:            X-Torin absent:   Reply populates:
message 3 found:            X-Torin absent:          From:                Reply-To absent:
```

---

## 6. Rollback

The pre-patch bytes are on disk from section 2 and their content hash is known
(`fd4deb874f2a4cf2de134fd6db732d8db7f5d8de`), so a restore is verifiable rather than
hopeful.

```bash
cd /Users/alabala/Documents/projects/torin
SNAP=$(ls -1dt backups/*/ | head -1)
git hash-object "${SNAP}public_html/mailer.php"   # must print fd4deb87...
cp "${SNAP}public_html/mailer.php" site-current/mailer.php
TORIN_LIVE_DEPLOY_CONFIRM=1 scripts/deploy-live.sh mailer.php
```

Then re-run the section 4a normal submission and confirm `302` / `location:
msg.html` / zero-byte body.

**Rolling back re-opens the vulnerability.** It is the right trade only if the patch
broke the form for real visitors, and section 4a is what decides that — not a
hunch.
