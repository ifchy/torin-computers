# MIGR-03 Rollback Drill

This file itself is the drill artifact. It is committed to `main` with a conventional commit message, then immediately reverted via `git revert --no-edit HEAD`, proving that the project's git-based rollback mechanism (git-as-source-of-truth, `git revert` to undo a bad change) works end-to-end before any real content work begins. Expected end state: this file no longer exists on disk, and `git log` shows both the drill commit and its "Revert ..." commit, with the revert touching only this one file.
