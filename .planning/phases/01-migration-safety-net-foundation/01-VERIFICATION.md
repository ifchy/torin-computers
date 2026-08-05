---
phase: 01-migration-safety-net-foundation
verified: 2026-08-05T12:00:00Z
status: passed
uat_signoff: 2026-08-05T07:35:00Z
score: 16/17 must-haves verified
behavior_unverified: 0
overrides_applied: 0
human_verification:
  - test: "Simulate an interrupted/dropped FTP connection mid-transfer while running scripts/backup-live-site.sh (e.g. kill the connection partway through the assets1/ recursion) and confirm the script's file-count/size checks catch the shortfall and exit non-zero rather than reporting success."
    expected: "Script exits non-zero with an explicit 'truncated pull' message; no `Backup complete` line is printed; the partial backups/<timestamp>/ directory is left on disk but not represented as a successful/complete snapshot."
    why_human: "This must-have is tagged `verification: backstop` in 01-03-PLAN.md's frontmatter (a non-inferable, spec-less edge probe about interrupted transfers). Code inspection confirms the HTML_COUNT!=16 and assets1-size-floor checks exist and are structurally capable of catching a truncated pull, and the two live-caught bugs during Task 1 development (bad directory-type heuristic, unencoded space in filename) prove the script does abort on real, unexpected failures encountered so far — but no one has exercised the specific 'dropped connection mid-transfer' scenario this truth describes. Symbol presence + adjacent evidence is not the same as direct observation of this exact failure mode, so per the honest-verifier abstention protocol this is flagged rather than silently marked VERIFIED."
  - test: "Confirm (owner/maintainer sign-off) that the four judgment-tier prohibitions below were genuinely honored in spirit, not just in the letter of the deliverable."
    expected: "Each prohibition item's disposition (see Prohibitions table) is accepted as correct by a human reviewer, since judgment-tier prohibitions are LLM-assessed and non-authoritative by design."
    why_human: "Per the judgment-tier prohibition protocol (ADR-550 D3/D4), a non-authoritative LLM verdict on a MUST-NOT constraint must be flagged for explicit human resolution at the end-of-phase checkpoint rather than silently counted as passed. My verdicts below (all 'resolved, evidence found') are LLM judgment, not deterministic test results — see Prohibitions table for the specific evidence backing each one."
---

# Phase 1: Migration Safety Net & Foundation Verification Report

**Phase Goal:** Before any rebuild work touches the live site, a complete safety net guarantees URL/ranking continuity and rollback capability, and the PHP-include technical foundation is proven to work on the actual host.
**Verified:** 2026-08-05
**Status:** passed (human_needed → passed after UAT sign-off 2026-08-05T07:35:00Z; see `01-UAT.md`, both items marked pass, owner accepted code-inspection evidence for the interrupted-transfer test and confirmed all 4 judgment-tier prohibitions)
**Re-verification:** No — initial verification

## Goal Achievement

### Roadmap Success Criteria (authoritative contract)

| # | Success Criterion | Status | Evidence |
|---|---|---|---|
| 1 | Complete inventory of all 16 live page URLs exists, cross-checked against GSC (or documented substitute), each URL's fate documented | ✓ VERIFIED | `01-URL-INVENTORY.md` lists all 16 filenames with non-blank disposition (14 keep-as-is, 2 decision-pending). Header explicitly labels the cross-check as a site-mirror + public-search **substitute**, not a real GSC export (D-08/D-09 honestly documented as unavailable). |
| 2 | New page templates use the exact same filenames/URLs as the live site — no visitor-facing link changes | ✓ VERIFIED | `diff <(ls src/*.html \| grep -v phptest) <(ls site-current/*.html)` — confirmed identical 16-filename set, same case/extension. All 16 URLs live-curled to `https://torin.bg/new/<file>` this session, all returned HTTP 200 (see Behavioral Spot-Checks). |
| 3 | Pre-deploy backup-and-rollback process established and proven: full local backup exists, git is source of truth, process exercised at least once | ✓ VERIFIED | Two full snapshots on disk (`backups/20260805T064013Z/`, `backups/20260805T071121Z/`), each containing 16/16 pages + 7/7 must-carry files + 4/4 must-carry directories (~12MB assets1/). Git rollback drill confirmed in `git log`: commit `e582dac` immediately followed by `Revert "..."` commit `7d6cdeb`, which touches only `ROLLBACK-DRILL.md` (1 file, 3 deletions) — the file is absent from disk today. GitHub off-site remote (`origin` → `github.com/ifchy/torin-computers.git`) confirmed wired and in sync: `git ls-remote origin main` == `git rev-parse main` == `4ccc05f...` exactly. |
| 4 | PHP-include foundation (header.php/footer.php/site-config.php, .htaccess PHP-as-.html) scaffolded and verified working via local preview against the real host | ✓ VERIFIED | `src/includes/{header,footer,site-config}.php` exist, PHP 5.2-safe (`array()`, `dirname(__FILE__)`, no closures/namespaces — confirmed by inspection). `src/.htaccess` uses the real working directive `AddHandler application/x-httpd-php52 .html .htm` (CloudLinux Alt-PHP). Live-curled `https://torin.bg/new/index.html` this session: HTTP 200, body contains "ТОРИН" (Cyrillic site title rendered through the include, not raw PHP source). |

**Score:** 4/4 roadmap success criteria verified.

### Plan-Level Must-Have Truths (supporting detail)

| # | Truth | Status | Evidence |
|---|---|---|---|
| T1 | `phptest.html` spike proves live PHP execution (01-01) | ✓ VERIFIED | Documented in SUMMARY/STATE.md; probe file since deleted (see T4). Superseded by T's own live re-check of index.html this session. |
| T2 | All 4 URL/protocol variants canonicalize to `https://torin.bg/new/...` in 1 hop (0 for already-canonical) (01-01) | ✓ VERIFIED | Re-ran live this session: `http://torin.bg/new/index.html` → 1 hop; `https://torin.bg/new/index.html` → 0 hops; `http://www.torin.bg/new/index.html` → 1 hop; `https://www.torin.bg/new/index.html` → 1 hop. All land on `https://torin.bg/new/index.html`, matching the plan's exact acceptance criteria. |
| T3 | `X-Robots-Tag: noindex` present on `/new/` responses (01-01) | ✓ VERIFIED | `curl -sI https://torin.bg/new/index.html` this session returns `x-robots-tag: noindex, nofollow`. |
| T4 | `phptest.html` returns 404 after cleanup (01-01) | ✓ VERIFIED | `curl -o /dev/null -s -w "%{http_code}" https://torin.bg/new/phptest.html` this session returned `404`. |
| T5 | STATE.md's PHP-version blocker updated with live-reconfirmed result + cPanel open question, pre-existing rows untouched (01-01) | ✓ VERIFIED | STATE.md lines 90-91 confirm PHP 5.2.17 + CloudLinux Alt-PHP finding + cPanel-login open question appended; other blocker rows present and undisturbed. |
| T6 | All 16 filenames in `01-URL-INVENTORY.md`, non-blank disposition (01-02) | ✓ VERIFIED | Read the file directly — all 16 rows present, each with keep-as-is or decision-pending. |
| T7 | `test-laptop.html` resolved keep-as-is, citing DIFF-01 (01-02) | ✓ VERIFIED | Row explicitly cites DIFF-01 and the self-diagnostic-tool rationale. |
| T8 | `covid.html`/`problem-stari.html` marked decision-pending, cross-referenced in STATE.md (01-02) | ✓ VERIFIED | Both rows marked **decision-pending** (not keep/retire); STATE.md lines 96-97 carry matching Blockers/Concerns entries citing `01-URL-INVENTORY.md`. |
| T9 | Inventory explicitly labeled as GSC-substitute, not a real cross-check (01-02) | ✓ VERIFIED | Header section states in bold: "This is NOT a real GSC Pages-export cross-check." |
| T10 | Backup script produces a complete snapshot (16 pages + 7 files + 4 dirs) when run against the live host (01-03) | ✓ VERIFIED | `backups/20260805T071121Z/public_html/` inspected directly: 16 known page files + `google1718743335455f1c.html` + 1 vendor-asset `demo.html` inside `assets1/` (expected, part of the asset tree) = accounted for; all 7 must-carry root files present; all 4 must-carry directories present; `assets1/` measured at 12MB (du -sk), consistent with the ~14MB baseline and above the script's own 10MB truncation floor. |
| T11 | Backup script exits non-zero with explicit error on FTP failure/0 files, not a silent empty backup (01-03) | ✓ VERIFIED | Code inspection of current `scripts/backup-live-site.sh`: FTPS probe failure → `exit 1` (lines 108-123); post-download `DOWNLOAD_FAILED` flag → `exit 1` (185-188); `HTML_COUNT != 16` → `exit 1` (245-248); `assets1` under floor → `exit 1` (255-258). All paths print an explicit `ERROR:` message before exiting. |
| T12 | Interrupted mid-transfer detection via file-count/size verification (01-03, tagged `verification: backstop`) | ⚠️ ABSTAIN (insufficient_spec) | Code structurally supports this (see T11's evidence), and two *different* live failures were caught and correctly aborted during development (directory-type heuristic bug, unencoded-space filename bug) — but this is a non-inferable, spec-less edge probe and no one has exercised the specific "dropped connection mid-transfer" scenario. Per the honest-verifier protocol, symbol presence is not explicit evidence for a `backstop`-tagged truth. Routed to human verification, not silently passed. |
| T13 | Git rollback drill exercised (commit → revert) before real content work begins (01-03) | ✓ VERIFIED | `git log --oneline -20` shows `e582dac` (drill commit) immediately followed by `7d6cdeb` (`Revert "docs(01-03): MIGR-03 rollback drill commit..."`). `git show --stat 7d6cdeb` touches only `ROLLBACK-DRILL.md` (3 deletions). File confirmed absent from disk. |
| T14 | `origin` remote's `main` HEAD matches local `main` HEAD (01-04) | ✓ VERIFIED | `git ls-remote origin main` == `git rev-parse main` == `4ccc05f0aa45c41de369860c2054c2d5917cc742` (exact match, checked live this session). |
| T15 | Pushed remote contains ≥1 commit, not an empty init (01-04) | ✓ VERIFIED | `git rev-list --count origin/main` returned `31`. |
| T16 | All 16 scaffolded pages under `/new/` are non-empty and return rendered HTML, not raw/unparsed PHP or 0-byte files (01-05) | ✓ VERIFIED | Live-curled all 16 URLs this session — all returned HTTP 200. `index.html`'s body confirmed to contain the real Cyrillic site title (rendered, not raw source); the shared include mechanism (T4 of the roadmap table) already proves PHP execution for every page since they all consume the identical `header.php`/`footer.php`. |
| T17 | New filenames byte-identical (case, extension) to `site-current/` (01-05) | ✓ VERIFIED | Directory listing diff performed this session: `src/` (excluding local-only `phptest.html`) and `site-current/` (excluding non-page root files) contain the exact same 16 names, same case, same `.html` extension. |

**Score:** 16/17 plan-level truths verified; 1 abstained (insufficient_spec, not failed) — see Human Verification.

### Prohibitions (must-NOT checks, judgment-tier — non-authoritative LLM verdict, flagged for human sign-off)

| # | Prohibition | Verdict | Evidence |
|---|---|---|---|
| P1 | MUST NOT unilaterally retire any of the 16 live pages; borderline pages recorded pending-owner-decision (01-02) | Resolved (evidence found) | `covid.html`/`problem-stari.html` marked **decision-pending** in both `01-URL-INVENTORY.md` and `STATE.md` — neither silently retired nor silently kept. |
| P2 | MUST NOT present the site-mirror + public-search substitute as a real GSC cross-check (01-02) | Resolved (evidence found) | Explicit bolded disclaimer in the inventory's header section (quoted above under T9). |
| P3 | MUST NOT report the backup as complete without verifying file count/size roughly match the known inventory (01-03) | Resolved (evidence found) | Script's completeness-verification block (lines 236-258) runs before the `Backup complete` success message; no early/unconditional success path exists in the current script. |
| P4 | MUST NOT rename/restructure any of the 16 live page filenames/extensions during scaffolding (01-05) | Resolved (evidence found) | Filename-set diff (T17) confirms byte-identical match — zero renames introduced. |

### Required Artifacts

| Artifact | Expected | Status | Details |
|---|---|---|---|
| `src/includes/site-config.php` | PHP 5.2-safe `$site` array (phone, email) | ✓ VERIFIED | `array()`, no closures, values match live source (phone from index.html, email from mailer.php). Minor: trailing `?>\n` after close tag (pre-existing code-review WR-05, not fixed — see Anti-Patterns). |
| `src/includes/header.php` | Shared head + contact-info chrome | ✓ VERIFIED | `require_once(dirname(__FILE__).'/site-config.php')`, echoes `$site['phone']`/`$site['email']`. Missing `<body>` opening tag (pre-existing code-review WR-01, not fixed — see Anti-Patterns). |
| `src/includes/footer.php` | Shared footer + dynamic copyright year | ✓ VERIFIED | `<?php echo date("Y"); ?>` present (not hardcoded); closes `</div></body></html>`. |
| `src/.htaccess` | PHP-as-.html handler, canonicalization, noindex, scoped to `/new/` | ✓ VERIFIED | All three directive groups present in the documented order; handler is the confirmed-working `application/x-httpd-php52`. |
| `src/index.html` + 15 siblings | Filename-preserved page skeletons using the include pattern | ✓ VERIFIED | All 16 files (excluding local-only `phptest.html`) wired to both includes (grep-confirmed across every file, not just a sample). |
| `scripts/backup-live-site.sh` | Repeatable, self-verifying curl-based FTP(S) pull into gitignored `backups/` | ✓ VERIFIED | Exists, current version reflects the post-review security fix (see Key Link / Post-Review Fix section below). |
| `backups/` | Gitignored local snapshot storage | ✓ VERIFIED | `.gitignore` line 4 contains `backups/`; `git status` shows the directory untracked; two real snapshots present on disk. |
| `.planning/phases/01-migration-safety-net-foundation/01-URL-INVENTORY.md` | Per-URL disposition table, GSC-substitute labeled | ✓ VERIFIED | Confirmed by direct read (see T6-T9). |

### Key Link Verification

| From | To | Via | Status | Details |
|---|---|---|---|---|
| `src/index.html` (+ 15 siblings) | `src/includes/header.php` | `require_once(dirname(__FILE__).'/includes/header.php')` | ✓ WIRED | Confirmed via grep across all 16 page files. |
| `src/includes/header.php` | `src/includes/site-config.php` | `require_once(dirname(__FILE__).'/site-config.php')`, `$site['phone']`/`$site['email']` | ✓ WIRED | Confirmed in file (line 5, lines 40/51). |
| `.planning/phases/01-migration-safety-net-foundation/01-URL-INVENTORY.md` | `.planning/STATE.md` | Decision-pending rows cross-referenced in Blockers/Concerns | ✓ WIRED | STATE.md lines 96-97 explicitly cite `01-URL-INVENTORY.md` and match the two decision-pending filenames. |
| `scripts/backup-live-site.sh` | `filezilla-server-data.xml` | Runtime credential read, base64-decoded, never hardcoded | ✓ WIRED | `CRED_FILE` variable + Python subprocess parse (lines 24, 48-77); no plaintext password string found anywhere in the script or its git history. |

### Post-Review Fix Verification (CR-01, CR-02, WR-02)

The phase's own code review (`01-REVIEW.md`) found 2 Critical findings in `scripts/backup-live-site.sh`. The orchestrator's claimed fix (commit `f8a61fd`) was independently re-verified against the **current** state of the script, not trusted from the SUMMARY/REVIEW narrative alone:

| Finding | Claimed Fix | Verified in current script? |
|---|---|---|
| CR-01: silent plaintext-FTP fallback on FTPS probe failure | Fail closed by default; plaintext requires explicit `BACKUP_ALLOW_PLAINTEXT_FTP=1` | ✓ Confirmed — lines 108-122: on probe failure, script prints an error and `exit 1` unless `BACKUP_ALLOW_PLAINTEXT_FTP=1` is explicitly set in the environment. |
| CR-02: `-k` disables all TLS cert validation, comment mischaracterized it as hostname-only | Add `--pinnedpubkey` SHA-256 pin alongside `-k`; correct the comment | ✓ Confirmed — line 106-107: `FTP_HOST_PUBKEY_PIN="sha256//..."`, `PROTO_FLAGS=(--ftp-ssl -k --pinnedpubkey "$FTP_HOST_PUBKEY_PIN")`. Comment (lines 91-105) accurately describes `-k`'s full-chain scope and the pin's compensating role. |
| WR-02: unsanitized FTP `LIST` entry names → path-traversal risk | Reject entries containing `/` or `..` | ✓ Confirmed — lines 209-214: `case "$name" in */*|*..*) ... continue ;; esac` before any path is built from the entry name. |

`git show --stat f8a61fd` confirms the diff touched exactly `scripts/backup-live-site.sh` and `01-REVIEW.md` (46 insertions / 9 deletions), consistent with a targeted security fix rather than unrelated changes. The commit message and REVIEW.md's own "Post-Review Fixes" addendum both claim a live re-run confirmed the backup still completes successfully post-fix; a second backup snapshot (`backups/20260805T071121Z/`) exists on disk with a timestamp consistent with that claim (~4 minutes before the fix commit, i.e., produced while the fix was already applied to the working tree, before being committed) and is complete (16/16 pages, 7/7 files, 4/4 dirs, 12MB assets1/).

### Behavioral Spot-Checks (live, this session)

| Behavior | Command | Result | Status |
|---|---|---|---|
| index.html renders through includes (real Cyrillic title, not raw PHP) | `curl -s https://torin.bg/new/index.html \| grep -c "ТОРИН"` | `2` | ✓ PASS |
| phptest.html removed from host | `curl -o /dev/null -s -w "%{http_code}" https://torin.bg/new/phptest.html` | `404` | ✓ PASS |
| X-Robots-Tag noindex present | `curl -sI https://torin.bg/new/index.html \| grep -i x-robots-tag` | `x-robots-tag: noindex, nofollow` | ✓ PASS |
| All 16 pages return 200 | Looped `curl -o /dev/null -s -w "%{http_code}"` over all 16 known filenames | 16/16 returned `200` | ✓ PASS |
| 4-variant canonicalization, exact hop counts | Looped `curl -w "%{http_code} hops=%{num_redirects}" -L` over http/https × www/non-www | http→1 hop, https(canonical)→0 hops, http+www→1 hop, https+www→1 hop, all landing on the canonical URL | ✓ PASS |
| Git rollback drill present in history | `git log --oneline -20` + `git show --stat 7d6cdeb` | Drill commit immediately followed by revert; revert touches only `ROLLBACK-DRILL.md` | ✓ PASS |
| GitHub remote in sync | `git ls-remote origin main` vs `git rev-parse main` | Exact SHA match (`4ccc05f...`) | ✓ PASS |
| No leaked credentials in git history | `git log --all --oneline -- filezilla-server-data.xml`; `git status --short filezilla-server-data.xml`; `git check-ignore -v` | Empty (never committed); gitignored; untracked | ✓ PASS |

### Requirements Coverage

| Requirement | Source Plan | Description | Status | Evidence |
|---|---|---|---|---|
| SEO-04 | 01-01, 01-05 | All existing page URLs preserved unchanged | ✓ SATISFIED | 16/16 filenames byte-identical; PHP-include mechanism proven live; all 16 URLs return 200. |
| MIGR-01 | 01-02 | Complete URL inventory, cross-checked against GSC (or documented substitute) | ✓ SATISFIED | `01-URL-INVENTORY.md` complete, substitute honestly labeled. |
| MIGR-03 | 01-03, 01-04 | Pre-deploy full backup + git-based rollback, proven at least once | ✓ SATISFIED (with 1 abstained sub-truth, T12) | Backup exercised twice; rollback drill exercised once; off-site GitHub mirror wired. |

No orphaned requirement IDs found — REQUIREMENTS.md maps exactly SEO-04, MIGR-01, MIGR-03 to Phase 1, and all three appear in at least one plan's `requirements:` frontmatter field.

### Anti-Patterns Found

| File | Line | Pattern | Severity | Impact |
|---|---|---|---|---|
| `src/includes/header.php` / `footer.php` | header.php: n/a (missing); footer.php:17 | Missing `<body>` opening tag (WR-01, pre-existing, documented in 01-REVIEW.md, not fixed) | Warning | Browsers auto-insert an implicit `<body>`, so all 16 live-verified pages still render (confirmed live this session) — not a functional blocker to Phase 1's goal, but a real HTML-validity bug that will bite CSS/JS targeting `body` in later phases. Already tracked as an open follow-up in `01-REVIEW.md`, not silently missed. |
| `src/includes/site-config.php` | 10 | Trailing `?>` + newline after closing PHP tag (WR-05, pre-existing, not fixed) | Warning | Emits stray whitespace before `<!DOCTYPE html>`; will cause "headers already sent" once any future code path (e.g. hardened `mailer.php`) needs `header()` after this include. Tracked in `01-REVIEW.md`, not fixed this phase. |
| `scripts/backup-live-site.sh` | 145-169, 182-188 | `error_log` can abort an otherwise-complete backup (WR-03); LIST parser breaks on symlink entries (WR-06) | Warning | Both pre-existing, both explicitly left open in `01-REVIEW.md` as "robustness/quality issues, not credential-security defects" — candidates for follow-up, not silent gaps. |
| `src/index.html` | 3-5 | Missing `<h1>` present on all 16 other pages (IN-02, pre-existing, not fixed) | Info | Cosmetic scaffold inconsistency, no functional impact. |
| `src/includes/header.php` | 18 | Hardcoded, identical `<title>` across all pages (IN-01) | Info | Explicitly in-scope for Phase 3 SC6 ("unique `<title>` and meta description... replacing the current identical/empty values") — deferred, not a Phase 1 gap. |

No debt markers (`TBD`/`FIXME`/`XXX`) found in any file modified by this phase (one `mktemp ... XXXXXX` template match is a false positive, not a debt marker).

### Deferred Items

| # | Item | Addressed In | Evidence |
|---|---|---|---|
| 1 | Unique per-page `<title>`/meta description (IN-01) | Phase 3 | Phase 3 SC6: "Every page has a unique `<title>` and `<meta name="description">`... replacing the current identical/empty values across all 16 pages." |

## Gaps Summary

No must-have truth FAILED, no artifact is MISSING/STUB, and no key link is unwired. The phase's four roadmap success criteria are all independently, live-verified true against the actual host and repository — not merely claimed by SUMMARY.md. The post-review security fix (CR-01, CR-02, WR-02) was independently re-verified against the script's **current** on-disk state and matches the commit `f8a61fd` diff.

One plan-level truth (T12, tagged `verification: backstop` — the "interrupted mid-transfer" completeness guarantee) is structurally supported by the code but has not been directly exercised with the specific failure scenario it describes, so per the honest-verifier abstention protocol it is flagged for human/held-out-test confirmation rather than silently marked VERIFIED. Additionally, four judgment-tier prohibitions were evaluated with LLM judgment (all "resolved, evidence found") but are non-authoritative by protocol and flagged for human sign-off at the end-of-phase checkpoint. Neither item blocks the phase's core deliverables, which are otherwise fully proven live.

A handful of pre-existing, already-documented (in `01-REVIEW.md`) Warning/Info-level code-quality issues remain open (missing `<body>` tag, trailing `?>` in site-config.php, backup-script robustness gaps around `error_log` and symlinks) — these were explicitly scoped out as "candidates for follow-up" by the review itself, not silently introduced or missed by this verification.

---

*Verified: 2026-08-05*
*Verifier: Claude (gsd-verifier)*
