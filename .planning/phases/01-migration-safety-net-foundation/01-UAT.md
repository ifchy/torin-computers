---
status: testing
phase: 01-migration-safety-net-foundation
source: [01-VERIFICATION.md]
started: 2026-08-05T07:22:43Z
updated: 2026-08-05T07:22:43Z
---

## Current Test

number: 1
name: Interrupted/dropped FTP connection mid-transfer detection
expected: |
  Simulate an interrupted/dropped FTP connection mid-transfer while running scripts/backup-live-site.sh
  (e.g. kill the connection partway through the assets1/ recursion) and confirm the script's
  file-count/size checks catch the shortfall and exit non-zero rather than reporting success.
  Script should exit non-zero with an explicit "truncated pull" message; no "Backup complete" line
  printed; the partial backups/<timestamp>/ directory left on disk but not represented as a
  successful/complete snapshot.
awaiting: user response

## Tests

### 1. Interrupted/dropped FTP connection mid-transfer detection
expected: |
  Script exits non-zero with an explicit error message; no "Backup complete" success line; partial
  backup directory left on disk but not reported as complete.
  Why human: tagged `verification: backstop` in 01-03-PLAN.md (non-inferable, spec-less edge probe).
  Code inspection confirms the file-count/size checks exist and are structurally capable of catching
  this, and two different real failures were already caught during development, but the specific
  "dropped connection mid-transfer" scenario has not been directly exercised.
result: [pending]

### 2. Judgment-tier prohibition sign-off
expected: |
  Confirm (owner/maintainer sign-off) that these four judgment-tier prohibitions were genuinely
  honored in spirit, not just the letter of the deliverable — all four were evaluated by the
  verifier as "resolved, evidence found" but are LLM judgment, non-authoritative by protocol:
    P1. MUST NOT unilaterally retire any of the 16 live pages; borderline pages recorded
        pending-owner-decision. Evidence: covid.html/problem-stari.html marked decision-pending
        in both 01-URL-INVENTORY.md and STATE.md.
    P2. MUST NOT present the site-mirror + public-search substitute as a real GSC cross-check.
        Evidence: explicit bolded disclaimer in the inventory's header section.
    P3. MUST NOT report the backup as complete without verifying file count/size roughly match
        the known inventory. Evidence: completeness-verification block runs before any
        "Backup complete" message; no early/unconditional success path.
    P4. MUST NOT rename/restructure any of the 16 live page filenames/extensions during
        scaffolding. Evidence: filename-set diff confirms byte-identical match.
result: [pending]

## Summary

total: 2
passed: 0
issues: 0
pending: 2
skipped: 0
blocked: 0

## Gaps
