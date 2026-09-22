---
quick_id: 260922-i38
slug: php-runtime-selftests
date: 2026-09-22
status: complete
commits:
  - a9d9997 fix(upload) — resolve both paths before the temp-directory containment test
---

# A PHP runtime, and the checks that had been waiting for one

`open_count` 35 → 33 (three closed, one new entry raised). PHP **8.5.10** installed via
Homebrew — an exact match for the host's 8.5.10, which matters because a `php -l` gate that
lints against different semantics than production is theatre.

Extensions confirmed present: `gd`, `exif`, `mbstring`, `openssl`, `json`, `fileinfo`, and
`imagecreatefromwebp`.

## Results

| check | outcome |
|---|---|
| `php -l`, all 45 PHP-bearing files in `src/` + `scripts/` | **0 failures** |
| `scripts/settings-selftest.php` (#29) | **26/26** |
| `scripts/notify-selftest.php` (#40) | **9/9** |
| `scripts/upload-selftest.php` (#19) | **4/10 → 10/10** after fixing the bug it found |

The linter was shown able to fail (seeded parse error → exit 255), and upload-selftest was
mutation-tested (forcing `$torin_scale = 1.0` → the scaling assertion fails with "returned
3000x1500"). The mutant was reverted; `git diff` confirmed only the intended guard change
remained.

## The bug the first-ever run found

`torin_normalise_upload()` compared `tempnam()`'s return against `sys_get_temp_dir()` as raw
strings. `tempnam()` returns an **already-resolved** path while `sys_get_temp_dir()` returns the
**symlinked** spelling — on macOS `/var` → `/private/var`. The guard therefore refused *every*
upload and logged "temp path escaped the system temp directory": a false refusal dressed as
containment, which the visitor experiences as «файлът не е разпознат като снимка», indistinguishable
from a genuinely bad file.

It does not affect the host (`/tmp` is not a symlink there) and none of this is deployed. But it
fires on any host whose temp path passes through a symlink, and `realpath()` on both sides is
strictly **stronger** than the comparison it replaces — it collapses `..` and resolves symlinks
before the prefix test. The selftest repeated the identical mistake and was fixed the same way.

Six assertions that could never previously be reached now run — including **orientation 6
returning a width/height swap**, the server half of ledger 17, which no check had ever touched.

## Closed

- **#19** upload-selftest — executed, 10/10, and it earned its keep on the first run
- **#29** settings-selftest — executed, 26/26
- **#30** `php -l` on the five 04-06 files — all clean (`settings`, `banner`, `site-config`,
  `jsonld`, `footer`), as are `header.php` and `category-page.php` from 04-08

## Raised

- **#54** — upload-selftest's containment assertion **cannot detect a disabled guard**. Observed
  during mutation testing: with the condition replaced by `if (false)` the assertion still
  passed, because it only checks that the happy path returns paths under the temp dir, which
  `tempnam()` guarantees by itself. The one control between a visitor upload and code execution
  on a host that maps `.html` to PHP has **no negative-path test**.

## Deliberately NOT closed, with the half that is now discharged noted

- **#47** — `header.php` now parses clean, but the entry is also about nothing from 04-08 being
  deployed. The syntax half is done; the deploy half is not.
- **#34** — spam-guard parses (`php -l`), retiring "greps are structural and do not prove the
  file parses". Its three live gates are untouched.
- **#40** — the selftest passes, but the entry's substance is the *live* all-channels-failed
  page returning 200 with preserved values. Still owed.
- **#17** — the server side is now proven by test. A real handset portrait through
  `kontakti.html`, checked upright in Telegram, is still owed.
- **#35** — no `spam-guard-selftest.php` exists. The runtime blocker is gone, so it is now
  straightforwardly authorable; that is a larger piece of work than this task.
- **#21** — local GD has WebP, which says **nothing** about the host's GD build. Unchanged.

## Noted and dismissed

PHP 8.5 deprecates `imagedestroy` (8 call sites), so the CLI emits deprecation notices. This is
**not** a production risk: the host's `error_reporting` is 22519, and decoding that bitmask shows
`E_DEPRECATED` (8192) is **not** reported. Worth knowing only if `error_reporting` is ever
widened to `E_ALL`.
