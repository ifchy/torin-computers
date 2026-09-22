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

---

## Addendum — 2026-09-22: ledger 35 closed

`scripts/spam-guard-selftest.php` written and run: **27/27**, `open_count` 33 → 32.

Seven of the nine plan behaviours are exercised for real; the oversized-post branch and the
correlation-id log lines live in `contact-send.php` (a handler that executes on include) and are
asserted at source level, each labelled `SOURCE:` in its own assertion name.

Mutation-tested: honeypot always-true → decoy assertion fails; `hash_equals` → `!==` → the
constant-time source assertion fails; throttle always-allows → two throttle assertions fail.
Each mutant reverted via `git checkout`, tree confirmed clean.

The second mutant is the case for keeping a source-level assertion at all: `!==` is
behaviourally identical — it still rejects every forgery — so no behavioural test can see the
timing regression. Only reading the code catches it.

**Two of my own assertions failed before the code did**, both instances of this project's
standing traps: banning `$field ===` tripped on the legitimate `$field === ''` emptiness check,
and the narrowed version then matched `===` inside the comment that *explains the rule*
("CONSTANT-TIME COMPARISON, never ==="). Comments are stripped before matching now. Ledger 48's
rule generalises: a pattern that reads comments is measuring the wrong text.

All four suites green: spam-guard 27/27, upload 10/10, settings 26/26, notify 9/9.

---

## Addendum 2 — 2026-09-22: ledger 54 closed

The containment decision is extracted as `torin_path_is_contained($real, $root)` — a pure
predicate doing no filesystem work, which is what makes `/tmpevil/x` and `/tmp/../etc/passwd`
testable without creating either. The `realpath()` resolution stays at the call site, where the
paths are real. Call-site behaviour unchanged: the 10 original behaviours still hold, now
**18/18**.

Eight assertions cover what the prefix test has to get right — a nested file is contained; a
sibling sharing a string prefix (`/tmpevil` vs root `/tmp`) is refused; the root itself is
refused however spelled; unrelated absolute paths are refused; any `..` segment is refused
rather than compared (it means the caller skipped `realpath()`, and comparing an unresolved path
is the bypass the guard exists to stop); a trailing slash on the root changes no verdict; empty
and non-string arguments are refused; and the wiring is source-asserted — the call site uses the
predicate **and** unlinks the refused file.

**One assertion failed against the first extraction, and the code was wrong, not the test.**
`/tmp/` tested against root `/tmp` reported *contained*. `realpath()` never emits a trailing
slash so it cannot arrive from the call site, but "the root is not a file inside the root" should
hold however the root is spelled. Both sides are normalised now and the equal case refused
explicitly.

Mutation-tested, each mutant reverted and the file's sha compared back to baseline:

| mutation | result |
|---|---|
| call site → `if (false)` — **the original ledger-54 mutation** | SOURCE assertion fails (previously passed silently) |
| predicate → always true | 5 assertions fail |
| predicate → naive prefix, no trailing slash | 2 fail, including the sibling case |

All four suites green: upload 18/18, spam-guard 27/27, settings 26/26, notify 9/9.
`open_count` 32 → 31.
