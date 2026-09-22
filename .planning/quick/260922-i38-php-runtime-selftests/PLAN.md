---
quick_id: 260922-i38
slug: php-runtime-selftests
date: 2026-09-22
description: Install a PHP runtime and discharge the ledger's largest blocked cluster — three selftests and the php -l obligation
---

# A PHP runtime, and the checks that have been waiting for one

## Why this unblocks so much at once

Eight open ledger entries name the same single missing dependency — no `php` binary and no
running Docker daemon on the build machine. This is the largest blocked cluster in the phase:

| entry | what it wants |
|---|---|
| 19 | `scripts/upload-selftest.php` has never executed, in either direction |
| 29 | `scripts/settings-selftest.php` has never executed — 22 assertions |
| 40 | `scripts/notify-selftest.php` has never executed |
| 35 | no `spam-guard-selftest.php` was ever authored (no runtime to author it against) |
| 30 | **highest-priority pre-deploy action** — `php -l` on five files from 04-06 |
| 47 | `header.php` syntax-unverified, edited by three plans (04-06/07/08) without once being parsed |
| 34 | spam-guard: "balance checks and greps are STRUCTURAL and do not prove the file parses" |
| 28 | part of G1's logic half, closable without a deploy once a runtime exists (per entry 29) |

Entries 30 and 47 are the sharp ones: `header.php` is included by **every** page, and a parse
error there takes all 19 down at once — which is the precise failure mode 04-06 exists to
prevent, arriving from the other direction.

## Version choice

Host runs **PHP 8.5.10** (cPanel selection, 04-HOST-CAPABILITIES.md). Homebrew's default `php`
formula is **8.5.10** — an exact match. Anything older would lint against different semantics
than production, which for a `php -l` gate is the whole point.

## Tasks

1. **Install** `php` via Homebrew. Confirm version, and confirm `gd` (upload-selftest calls
   `imagecreatetruecolor`) and `exif` are present — GD's **WebP decoder is a separate build
   option**, which is exactly what ledger 21 turns on, so record whether
   `imagecreatefromwebp` exists.
2. **`php -l` the syntax-unverified files** — the five from 04-06 (`settings.php`,
   `banner.php`, `site-config.php`, `jsonld.php`, `footer.php`) plus `header.php` and
   `category-page.php` from 04-08, and every other PHP file in `src/`. This is entry 30/47 and
   costs seconds.
3. **Run the three selftests.** Record real output. **A failure here is a RESULT, not a
   setback** — these encode behaviour nobody has ever executed, and the honest outcome is
   whatever they say.
4. Update the ledger per what actually happened. Close only what the evidence closes.

## What this does NOT close

The selftests run under the **CLI SAPI on macOS against a different filesystem, a different
temp directory and a different Apache**. They prove *logic*, not *deployment*. Every live gate
(28's six, 34's three, 38's SMTP cascade, 42's HTTP deny block) still needs the server.

Authoring the missing `spam-guard-selftest.php` (entry 35) is a larger piece of work than this
task; note the runtime is now available for it and leave it.

## Standing rule for this task

This project has found seven defective check commands in one phase, every one by running it
rather than reading it. A selftest that passes on its first run deserves the same suspicion —
confirm each can also fail.
