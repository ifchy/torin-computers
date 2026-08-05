---
phase: 01-migration-safety-net-foundation
plan: 3
subsystem: infra
tags: [bash, curl, ftp, ftps, python3, git, backup, rollback]

# Dependency graph
requires: []
provides:
  - "scripts/backup-live-site.sh — repeatable, self-verifying curl-based FTP(S) pull of bell.host.bg's live public_html/ into a gitignored backups/<UTC-timestamp>/ snapshot"
  - "Live-exercised proof that the git-based rollback mechanism (commit → git revert) works end-to-end"
affects: [01-05, phase-4-cutover]

actuals:
  tokens: 2350
  tasks: 2
  commits: 4

tech-stack:
  added: []
  patterns:
    - "FTP directory-type detection via full LIST output (leading d/- column), not NLST name heuristics — at least one live directory name (jqury.mb.YTPlayer) contains dots, which broke a naive 'has a dot = file' guess"
    - "URL-encode remote FTP paths before building curl ftp:// URLs — at least one live filename contains a literal space (Preloader-icon ORI.gif)"
    - "Credentials decoded from filezilla-server-data.xml entirely inside a short-lived Python subprocess, written straight to a chmod-600 mktemp .netrc file consumed via curl --netrc-file, trap-cleaned on exit — password never touches a shell variable, command line, or stdout"

key-files:
  created:
    - scripts/backup-live-site.sh
  modified:
    - .gitignore

key-decisions:
  - "Backup format: directory-mirror snapshot under backups/<UTC-timestamp>/public_html/, not a zip — diffable/inspectable, no zip tooling dependency (Claude's discretion per 01-CONTEXT.md)"
  - "Directory recursion reads the FTP LIST type column directly instead of guessing file-vs-directory from the entry name — discovered live that a dotted name (jqury.mb.YTPlayer) is actually a directory, which would have silently corrupted a name-based heuristic"
  - "Remote paths are percent-encoded before being placed in curl ftp:// URLs — discovered live that assets1/img/Preloader-icon ORI.gif contains a literal space, which curl otherwise rejects as a malformed URL"

patterns-established:
  - "Any future FTP-pull tooling in this repo should reuse the LIST-based type-detection + percent-encoding pattern in scripts/backup-live-site.sh rather than NLST + naive name heuristics"

requirements-completed: [MIGR-03]

coverage:
  - id: D1
    description: "scripts/backup-live-site.sh pulls a full local snapshot of bell.host.bg's live public_html/ (16 pages, 7 must-carry root files, 4 must-carry directories) into a timestamped, gitignored backups/ directory, self-verifying completeness before reporting success"
    requirement: "MIGR-03"
    verification:
      - kind: e2e
        ref: "bash scripts/backup-live-site.sh (live run against bell.host.bg) — completed 16/16 pages, 7/7 must-carry files, 4/4 must-carry directories, assets1/ 12228KB > 10000KB truncation threshold"
        status: pass
    human_judgment: false
  - id: D2
    description: "Git-based rollback mechanism (commit → git revert) exercised live: a drill file was committed, then reverted, proving the safety net works before any real content work begins"
    requirement: "MIGR-03"
    verification:
      - kind: e2e
        ref: "git log --oneline -3 showing drill commit e582dac immediately followed by revert commit 7d6cdeb; git show --stat 7d6cdeb touches only ROLLBACK-DRILL.md"
        status: pass
    human_judgment: false

duration: 20min
completed: 2026-08-05
status: complete
---

# Phase 1 Plan 3: Migration Safety Net (Backup + Rollback Drill) Summary

**Self-verifying curl/FTPS backup script that pulled a full live snapshot of bell.host.bg (16 pages, 188 assets1/ files, ~12MB) once for real, plus a live-exercised git commit→revert rollback drill**

## Performance

- **Duration:** 20 min
- **Started:** 2026-08-05T09:31:00+03:00 (approx.)
- **Completed:** 2026-08-05T09:45:21+03:00
- **Tasks:** 2
- **Files modified:** 3 (2 created, 1 modified; ROLLBACK-DRILL.md created then reverted per its own drill design)

## Accomplishments

- Wrote and ran `scripts/backup-live-site.sh` against the real live host, producing `backups/20260805T064013Z/public_html/` containing all 16 known `.html` pages, all 7 must-carry root files, and all 4 must-carry directories (`.well-known/`, `cgi-bin/`, `covid-19/`, `assets1/` — 188 files, ~12MB).
- The script self-verifies its own completeness (exact page count, non-trivial `assets1/` size) and exits non-zero on partial pulls rather than silently reporting success — proven by two live failures it correctly caught and I fixed before the successful run (see Deviations).
- Exercised the git-based rollback mechanism for real: committed `ROLLBACK-DRILL.md`, then ran `git revert --no-edit HEAD`, confirming the drill file is gone from disk and both the drill commit and its revert appear in `git log`.
- Confirmed via a targeted grep of the last 4 commits' full diffs that the real (decoded) FTP password never appears in any committed content.

## Task Commits

Each task was committed atomically:

1. **Task 1: Write and exercise the live-site backup script** - `aa998f7` (feat)
2. **Task 2: Exercise the git-based rollback drill** - `e582dac` (docs, the drill commit) followed by `7d6cdeb` (the revert, generated by `git revert`)

**Plan metadata:** (this commit, following SUMMARY.md write)

## Files Created/Modified

- `scripts/backup-live-site.sh` - Repeatable FTPS/FTP backup script: decodes `filezilla-server-data.xml` credentials at runtime via a short-lived Python subprocess (password never enters a shell variable or the command line), pulls the 16 known pages + 7 must-carry root files + 4 must-carry directories into `backups/<UTC-timestamp>/public_html/`, and self-verifies page count + `assets1/` size before reporting success
- `.gitignore` - Added `backups/` (appended, existing `filezilla-server-data.xml`/`.DS_Store` lines untouched)
- `.planning/phases/01-migration-safety-net-foundation/ROLLBACK-DRILL.md` - Created, committed, then reverted as the drill itself; does not exist on disk in the final state (by design)

## Decisions Made

- **Backup format:** directory-mirror snapshot, not a zip archive (Claude's discretion per 01-CONTEXT.md) — directly diffable/inspectable, no new tooling dependency.
- **Directory-type detection:** switched from an initial name-based heuristic (dot in name = file) to reading the FTP LIST type column directly, after discovering live that `assets1/vendors/jqury.mb.YTPlayer` is a directory despite its dotted name — a heuristic based on the live server's actual response, not a documentation guess.
- **URL encoding:** added percent-encoding of remote FTP paths after discovering live that `assets1/img/Preloader-icon ORI.gif` contains a literal space, which curl rejected as a malformed URL without it.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Directory-vs-file detection heuristic was wrong for a real live directory name**
- **Found during:** Task 1, first live run of the recursion logic against `assets1/vendors/`
- **Issue:** Initial implementation used NLST (name-only listing) and guessed "contains a dot → file, else → directory." `assets1/vendors/jqury.mb.YTPlayer` is a real directory on the live host whose name contains dots, so the script tried to download it as a file and failed (`curl: (78) The file does not exist`), correctly aborting rather than silently reporting a partial backup as complete.
- **Fix:** Switched `list_dir()` to use FTP's full LIST output (Unix `ls -l`-style, with an explicit `d`/`-` type column) instead of NLST, and parse the type column directly instead of guessing from the name.
- **Files modified:** `scripts/backup-live-site.sh`
- **Verification:** Re-ran the script; `assets1/vendors/jqury.mb.YTPlayer/` was correctly recursed into and its contents downloaded.
- **Committed in:** `aa998f7` (Task 1 commit — the fix was made before the task's first commit, not as a separate follow-up)

**2. [Rule 1 - Bug] curl rejected a live filename containing a literal space**
- **Found during:** Task 1, second live run, downloading `assets1/img/`
- **Issue:** `assets1/img/Preloader-icon ORI.gif` contains a literal space in its filename. The unencoded remote path produced `curl: (3) URL rejected: Malformed input to a URL function`, and the script correctly aborted rather than silently omitting the file.
- **Fix:** Added a `urlencode_path()` helper (Python `urllib.parse.quote`, keeping `/` as a literal separator) applied to every remote path before it's placed in a curl `ftp://` URL.
- **Files modified:** `scripts/backup-live-site.sh`
- **Verification:** Re-ran the script; the file downloaded successfully and the run completed with 16/16 pages, 7/7 must-carry files, all 4 directories mirrored.
- **Committed in:** `aa998f7` (Task 1 commit)

---

**Total deviations:** 2 auto-fixed (both Rule 1 — bugs surfaced only by running the script against the real live host, exactly the point of "exercise it for real" per this plan's objective)
**Impact on plan:** Both fixes were necessary for the backup script to actually complete a full, accurate pull — without them the script would have either crashed mid-run or (in a less defensive design) silently produced an incomplete backup. No scope creep: both fixes are confined to `scripts/backup-live-site.sh`'s directory-recursion and URL-building logic, the exact area the plan scoped for this task.

## Issues Encountered

None beyond the two auto-fixed issues above (both documented as deviations, not left as open problems).

## User Setup Required

None - no external service configuration required. The script reuses the existing `filezilla-server-data.xml` credentials already in place from project setup; no new accounts, API keys, or dashboard configuration were introduced.

## Next Phase Readiness

- MIGR-03's backup-and-rollback discipline is proven, not just documented: a real backup snapshot exists on disk (`backups/20260805T064013Z/public_html/`, gitignored, ~12MB) and the git revert path has been exercised end-to-end.
- `scripts/backup-live-site.sh` is ready for reuse before any future FTP upload in this project (Plan 01-05's page-skeleton uploads, and every later phase's content/visual work).
- No blockers carried forward from this plan.

---
*Phase: 01-migration-safety-net-foundation*
*Completed: 2026-08-05*
