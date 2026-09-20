---
phase: 04-hardening-cutover
plan: 02
subsystem: contact
tags: [contact-form, notifications, telegram, php, honeypot, seo]
status: complete
requires:
  - "04-01 measured host capabilities (ext:curl, outbound:curl443)"
  - "A Telegram bot token and chat id in /home/torin/torin-secrets.php, chmod 600"
provides:
  - "torin_render_contact_form($values, $errors) — the one copy of the enquiry form markup"
  - "torin_notify($payload, $photos, $secrets) — the any-one-wins notification fan-out"
  - "torin_notify_telegram() — the first registered driver"
  - "contact-send.php — the POST endpoint and the single non-5.2 file (quarantine boundary)"
  - "kontakti.html — the contact page at its locked slug"
  - "site-config.php key 'secrets_path'"
  - "css/base.css token '--form-measure'"
  - "icons.php glyph 'alert' (17th)"
affects:
  - "04-03 (uploads fill torin_collect_uploads() behind the same handler call)"
  - "04-04 (five CTA slots point at kontakti.html; photo-resize.js and the client validator)"
  - "04-05 (mail driver beside telegram; HMAC time-trap; rate limiter; error re-render)"
  - "04-07 (analytics events on this form)"
tech-stack:
  added: []
  patterns:
    - "RESEARCH A-1 — notification fan-out behind one interface, any-one-wins"
    - "RESEARCH A-3 — the non-5.2 dialect quarantined in exactly one file"
    - "UI-SPEC C-3 — one form partial, two callers"
    - "POST/Redirect/GET with 303 See Other"
key-files:
  created:
    - src/kontakti.html
    - src/contact-send.php
    - src/includes/contact-form.php
    - src/includes/notify.php
  modified:
    - src/includes/site-config.php
    - src/includes/header.php
    - src/includes/icons.php
    - src/css/components.css
    - src/css/base.css
decisions:
  - "The secrets path is recorded in site-config.php; the token never enters the repository, a shell variable or a command line"
  - "torin_notify() takes $secrets as a third argument so notify.php never touches the filesystem"
  - "The phone list renders above the form rather than inside «Къде да ни намерите»"
  - "The last breadcrumb carries an href because jsonld.php reads the key unconditionally"
  - "Every $_POST read goes through one accessor that treats a non-string as absent"
  - "The file input ships native and unclassed; ::file-selector-button styling is 04-03/04-04's"
metrics:
  duration: "~2 sessions (spanned an API limit reset, then a deploy + live-verify continuation the next day; wall-clock not recorded)"
  completed: 2026-09-20
  tasks_completed: 3
  tasks_total: 3
actuals:
  tokens: 18600
  tasks: 3
  commits: 5
---

# Phase 4 Plan 02: The Contact Spine Summary

The contact path works end to end on staging. A visitor fills the form at
`https://torin.bg/new/kontakti.html`, `contact-send.php` validates and redirects with
`303 See Other`, and a Telegram message arrives on the owner's phone with Cyrillic intact.
All seven of the plan's automated checks pass against the live host, and the human
confirmed a real handset submission reached them. A trapped submission reaches nobody.

Two things the live pass does **not** prove, carried forward rather than glossed: the
honeypot has not been tested against browser autofill, and the notification *failure* path
has never been exercised. Both are in `.planning/WINDOWS.md` (entries 14 and 15).

## What Was Built

**`src/includes/contact-form.php`** — `torin_render_contact_form($values, $errors)`. One
function, zero output on include, which is what lets `kontakti.html` and
`contact-send.php` share one copy of the markup. Seven controls in UI-SPEC C-3's order
(`device`, `fault`, `photos[]`, `name`, `phone`, `email`, `consent`), identical `.field`
anatomy on every one: visible label above the control, `aria-describedby` naming both the
help node and the error node unconditionally, `data-err` carrying the exact server string.
The honeypot follows C-6 exactly — plausible name, off-viewport via a stylesheet rule
rather than an inline style, `aria-hidden` on the wrapper, `tabindex="-1"`, and
`autocomplete="off"`. Consent renders unchecked unconditionally and ignores `$values`.

**`src/kontakti.html`** — the contact page, shaped like `msg.html`. `Cache-Control:
no-store` is sent before the header require. Three sections, tint alternated by hand. Not
one contact literal is typed on the page; every phone, the email, the address, the map URL
and the hours are read from `$site` and escaped at output.

**`src/includes/notify.php`** — `torin_notify($payload, $photos, $secrets)` returning
`array('ok' => bool, 'channels' => array('telegram' => bool))`. One driver registered.
Each driver call is wrapped in `catch (Exception)` **and** `catch (Throwable)`, so a
Telegram outage or an engine-level error degrades to `ok => false` instead of 500-ing a
form the visitor just spent two minutes on. The Telegram driver posts a `json_encode`d
body with bounded connect and transfer timeouts, verifies the peer certificate explicitly,
clamps the message to Telegram's 4096-character ceiling, and treats a 200 carrying
`"ok":false` as failure.

**`src/contact-send.php`** — the one file not in the 5.2-safe dialect. POST-only gate,
honeypot checked first and answered with a response byte-identical to success,
oversized-POST detection, five validated fields plus consent, `303 See Other` to
`msg.html` on success, and an honest failure page carrying the shop's number when every
channel fails. No exception text, no path and no submitted value reaches the page; failures
go to `error_log` with a correlation id only.

**Modified:** the nav «Контакти» item now points at `kontakti.html` and carries the
`aria-current` ternary (`index.html#contact-us` is untouched and still resolves); a 17th
icon `alert`; the form component in `components.css`; `--form-measure` in `base.css`; and
`secrets_path` in `site-config.php`.

## Verification Status

### Deploy

`scripts/deploy-new.sh` ran on 2026-09-20 with the nine files of this slice and reported
`Deploy complete: 9 file(s) -> public_html/new/`. CSS was comment-stripped in transit, as
the script always does: `components.css` 42148 -> 15569 B, `base.css` 14885 -> 4287 B.

### The seven automated checks — all pass, against the live host

| # | Check | Result |
|---|---|---|
| V1 | `kontakti.html` returns HTTP 200 | **PASS** |
| V2 | `cache-control: no-store` present on that response | **PASS** |
| V3 | All seven named fields, plus `photos[]` and `id="contact-form"`, in the served markup | **PASS** |
| V4 | Zero `Warning:` / `Notice:` / `Fatal error` in the rendered body | **PASS** |
| V5 | A valid POST to `contact-send.php` returns 303 | **PASS** — and `location: msg.html`, so POST/Redirect/GET is confirmed, not assumed |
| V6 | A POST with `website=http://spam.example` returns 303 (trap answered identically to success) | **PASS** |
| V7 | `header.php` references `kontakti.html` | **Condition TRUE** — but the check *as written in the plan* is not portable and reports a false failure on macOS. See below. |

V4 passing matters more than its one-line phrasing suggests: `display_errors` is still `1`
in this subtree (04-HOST-CAPABILITIES BLOCKER 1), so any PHP diagnostic would have printed
into the page body. A clean body across V1/V4 plus a working 303 on V5 is also what retires
the syntax question the missing lint left open — a parse error under this configuration
produces a visible fatal, not a silent one.

### V7 is a broken check, not a broken condition — and it will recur

The plan writes V7 as:

```sh
bash -c 'grep -L "kontakti.html" src/includes/header.php' | wc -l | grep -qx 0
```

This reports failure on macOS while the thing it measures is true. BSD `wc` right-pads its
count to a fixed width — the byte stream is seven spaces, then `0`, then a newline,
confirmed with `od -c` — and `grep -qx 0` anchors both ends of the line, so it never
matches. GNU `wc` emits `0\n` unpadded and the identical check passes on Linux. The
underlying condition holds: `src/includes/header.php:264` contains
`<li><a class="nav__link" href="kontakti.html"…>Контакти</a></li>`. The padding-tolerant
form returns 0 and passes:

```sh
N=$(grep -L "kontakti.html" src/includes/header.php | wc -l | tr -d ' '); [ "$N" = "0" ]
```

This is recorded because it is not local to this plan: `wc -l | grep -qx N` appears seven
times across five plans in this phase — `04-01`, `04-02`, `04-04`, `04-05`, `04-07` — and
four of those five are still unexecuted. Anyone running them on macOS should expect a false
failure. `.planning/WINDOWS.md` entry 16; details in `deferred-items.md`.

### What was still never linted

There is no `php` binary on the build machine and no running Docker daemon, so `php -l` was
never run on any of these files. The substitutes used at authoring time — a read-through and
a throwaway brace/paren/tag-balance checker that is not a parser — remain what they were.
The live checks above are now the real evidence, and they are better evidence than a lint
would have been, but they are evidence about the paths that were exercised, which is not
every path. See Residual Risks.

### Static assertions checked at authoring time, and still holding

| Acceptance criterion | Result |
|---|---|
| `contact-form.php` defines `torin_render_contact_form(` | yes, exactly one `<form>`, one `id="contact-form"` |
| All seven control names plus `photos[]` present | yes — `device fault name phone email consent website t` + `photos[]` |
| `notify.php` contains `function torin_notify(` and `'channels'` | yes, 2 occurrences of `'channels'` |
| `contact-send.php` contains `REQUEST_METHOD` and `FILTER_VALIDATE_EMAIL` | yes |
| `contact-send.php` contains **zero** occurrences of the legacy bare-send call syntax | yes — 0 (see Deviations, it was 1 on first draft) |
| `grep -rn "contact-send" src/includes/header.php src/includes/footer.php` | no matches |
| `icons.php` contains `case 'alert':`, header counts read 17 | yes; 17 `case` labels in code, comment says 17 |
| `header.php` references `kontakti.html` | yes, line 264 |
| 5.2-safe dialect holds in the three new non-quarantined files | yes — no `__DIR__`, no `[]`, no `??`, no closures, no return types, no `<?=` |
| `declare(strict_types=1)` appears in exactly one file | yes, `contact-send.php` |
| Gzipped CSS delta ≤ 1.5 KB | **+259 B** (see below) |

### Task 3 — the human handset test: PASSED

Task 3 is the `gate="blocking"` human-verify that exists because no `curl` can prove a
message landed on a phone. What was done and what was reported:

The orchestrator sent three POSTs to the live endpoint — two valid (`Тест`, `Тест2`) and
one carrying a filled honeypot. The human then opened the page and submitted the form
themselves from a browser. Their report, verbatim:

> got 2 messages, both in Cyrillic, no third also submitted the form myself and got the
> message in Telegram

Read at the precision it supports:

| Claim | Basis |
|---|---|
| The notification path works end to end to a real device | **Observed.** Messages arrived. |
| Cyrillic survives form -> PHP -> `json_encode` -> Telegram unmangled | **Observed.** Both messages readable in Bulgarian. |
| The honeypot suppresses the trapped submission | **Observed.** Three POSTs went out, exactly two messages arrived, and the third was the trapped one. |
| The visitor is not told the trap fired | **Observed** at the HTTP layer (V6: the trap returns the same 303 as success). |
| A human can complete and submit the form from a real handset | **Observed.** The human's own submission arrived. |
| A refresh of the confirmation page does not resubmit | **Inferred, not observed.** The human did not report step 4. It is precluded by construction — V5 measured `303` + `location: msg.html`, so a refresh re-issues a GET for `msg.html`, and there is no POST body to replay. This is a sound inference about a mechanism, not a sighting of the outcome. |
| The honeypot does not fire on browser autofill | **NOT established.** See Residual Risks. |

Steps 5 and 6 of the plan's checklist — the phone numbers still tappable, and the 360px
weighting of the form against the phone list — were not separately reported. Nothing
suggests a problem with either, and a regression in either would be visible to anyone who
opened the page, but "not reported" is not "confirmed". 04-04 touches this page's CTAs and
is the natural place to look.

## CSS Budget

Measured with `gzip -c` on the **deploy-time output**, not the source — `deploy-new.sh`
strips CSS comments through `scripts/lib/strip-css-comments.py` before upload, so source
size is not what ships.

| File | Before (gz, stripped) | After (gz, stripped) | Delta |
|---|---:|---:|---:|
| `components.css` | 2808 B | 3055 B | +247 B |
| `base.css` | 1684 B | 1696 B | +12 B |
| **Total** | | | **+259 B** |

Against the 1.5 KB (1536 B) gzipped budget for the whole phase's CSS, this plan spends
**17%**, leaving 1277 B for the holiday banner, the `.filelist` photo rows and the
`::file-selector-button` styling still to come.

## Deviations from Plan

### 1. [Rule 1 — Bug] The last breadcrumb needed an `href`

**Found during:** Task 2, while wiring `kontakti.html`'s breadcrumbs.
**Issue:** UI-SPEC C-2 specifies the crumb array as
`array(array('text'=>'Начало','href'=>'index.html'), array('text'=>'Контакти'))` — no
`href` on the last entry, because `torin_render_breadcrumbs()` renders the last crumb as a
`<span>`. But `jsonld.php:125` reads `$crumb['href']` **unconditionally** to build an
absolute `item` URL. On PHP 8.5 a missing key there is an `E_WARNING`, and `display_errors`
is still `1` in this subtree (04-HOST-CAPABILITIES BLOCKER 1) — so the warning renders into
the page body, which would fail the plan's own "zero PHP warnings" criterion.
**Fix:** the last crumb carries `'href' => 'kontakti.html'`. The visible renderer decides
link-versus-span by position, not by key presence, so nothing a visitor sees changes; the
JSON-LD gets a correct absolute URL. Every other page in the tree already supplies the key
for the same reason — this was the page that would have been the exception.
**Files:** `src/kontakti.html`. **Commit:** `c9c9be6`.

### 2. [Rule 2 — Missing input validation] Every `$_POST` read routed through one accessor

**Found during:** Task 2, reviewing `contact-send.php`.
**Issue:** the draft read fields as `trim((string) ($_POST['website'] ?? ''))`. A submitter
controls the *shape* of a POST value as freely as its content: `website[]=x` makes
`$_POST['website']` an array. Casting an array to string yields `'Array'` **and** raises an
`Array to string conversion` warning — which, with `display_errors` On, prints into the
response *before* the honeypot's `header('Location: …')` call, converting a redirect into a
"headers already sent" failure. One request shape would have broken the endpoint.
**Fix:** `torin_post(string $key): string` returns `''` for anything that is not a string.
It is now the only way the file touches `$_POST`. A non-string is then rejected by the
ordinary required-field check with the ordinary Bulgarian message.
**Files:** `src/contact-send.php`. **Commit:** `c9c9be6`.

### 3. [Rule 1 — Bug] Acceptance criterion violated by my own comment

**Found during:** Task 2 self-check.
**Issue:** the criterion is that `contact-send.php` contains **zero** occurrences of the
legacy bare-send call syntax, so the old path cannot creep back in during a later edit. The
anti-analog comment block described the three defects of `site-current/mailer.php` and
spelled that call syntax while doing so — tripping the gate from a comment, exactly the
failure `brand-row.php:34-36` and `.htaccess:56-62` already record.
**Fix:** the comment now describes the defect without naming the function, and says why it
does not name it, so a later reader does not "fix" the wording and re-trip the gate.
**Files:** `src/contact-send.php`. **Commit:** `c9c9be6`.

### 4. [Design, documented at the site] Phone list moved above the form

**Issue:** UI-SPEC C-2's section table lists the phones inside «Къде да ни намерите», the
third and last section. This plan's own backstop truth requires the phone list to stay
**above the fold** as the faster alternative. At 360px the third section is far below it.
**Fix:** the numbers render directly under the lede in section 1, as `.btn--secondary`
outline buttons inside the existing `.cta-block__actions` wrapper — no new class, no new
CSS. «Къде да ни намерите» keeps the address, map link, email and hours. Rendering the loop
in both places was the alternative and is worse: `footer.php` already renders the same
three numbers at the bottom of this page, so the spec's placement would have put them on
screen three times. Both renderings read `$site['phones']`, so no drift is possible either
way — this is purely a question of weight. The one amber element on the page remains the
form's submit button.
**Files:** `src/kontakti.html`. **Commit:** `c9c9be6`.

### 5. [Interface refinement] `torin_notify()` takes a third `$secrets` argument

RESEARCH A-1 sketches `torin_notify($payload, $photos)`. The shipped signature is
`torin_notify($payload, $photos, $secrets)`. This is what keeps `notify.php` off the
filesystem entirely: the alternative is reading `secrets_path` from inside `includes/`,
which would put a credential one careless `require` away from the page shell. T-04-08's
mitigation depends on this parameter existing.

### 6. [Scope] `base.css` modified although not in `files_modified`

UI-SPEC's spacing exception 4 states `--form-measure: 34rem` is "declared once in
`base.css` alongside the other three formulas". The plan's `files_modified` list omits
`base.css`. Declaring the token in `components.css` instead would split the formula group
across two files. One line was added to `base.css` (+12 B gzipped), in the declared place.

## Threat Flags

None. No new network endpoint, auth path, file-access pattern or schema change beyond the
ones the plan's `<threat_model>` already registers. The mitigations assigned to this plan
are all present: POST-only gate and honeypot (T-04-06), `torin_esc()` on every echoed value
including the error branch (T-04-07), secrets outside the document root read only by the
quarantined file (T-04-08), no exception text in output (T-04-09), wrapped drivers with
bounded timeouts (T-04-11).

## Known Stubs

| Stub | File | Why, and which plan resolves it |
|---|---|---|
| `$photos` accepted and unused | `src/includes/notify.php` | Parameter ships now so 04-03 changes the function body only, not the interface. Intentional per the plan's own tracer-stub definition. |
| `contact-send.php` passes `[]` for photos | `src/contact-send.php` | 04-03 fills it from `torin_collect_uploads()`. |
| Hidden `t` field emitted but not verified | `src/includes/contact-form.php` | The time-trap is 04-05's; the field ships now so the shape is fixed. |
| Error re-render not wired | `src/contact-send.php` | The failure page states the problem and links back rather than repopulating the form. 04-05 owns the re-render together with the spam guard that produces most of the errors worth repopulating for. |
| `.field--invalid` has no declarations | `src/css/components.css` | A markup hook for the 04-04 validator; the visible invalid treatment hangs off `aria-invalid`. Documented in place. |
| File input unstyled | `src/includes/contact-form.php` | `::file-selector-button` and `.filelist` are 04-03/04-04's (UI-SPEC C-4). |

None of these prevent the plan's goal, and the live pass confirms it: the spine works end
to end with all six in place.

## Residual Risks

The blocker recorded in the first draft of this summary — deploy and outbound `curl` both
refused by the execution environment — is **resolved**. The deploy ran, all seven checks
ran, and Task 3's human gate passed. What remains open is narrower and is listed here so
the pass is not read as broader than it is. All three are in `.planning/WINDOWS.md`.

**1. The honeypot has not been tested against browser autofill.** (`WINDOWS.md` 14)

The plan names this as the number-one honeypot false-positive source, and it is the one
failure this component can produce that **nobody would ever see**: a password manager fills
the off-viewport decoy, `contact-send.php` treats the submission as spam, the visitor is
shown the same confirmation page a real success produces, and the enquiry is discarded in
silence. Neither the customer nor the shop learns anything went wrong. The human's manual
submission did arrive — but they did not say whether autofill was active, so that arrival
is not evidence either way. Re-test deliberately, with a password manager that fills
aggressively. This is the highest-value unclosed item in the plan.

**2. The notification failure path has never been exercised.** (`WINDOWS.md` 15)

Every check so far drove the success branch. Nothing has made `api.telegram.org`
unreachable, so what a visitor actually sees when every channel fails is unproven at
runtime — as is the `error_log` correlation-id branch. The code for both is written and
wrapped in `catch (Exception)` and `catch (Throwable)`; it has simply never run. 04-05 adds
a second driver and is the natural place to exercise it while the area is already open.

**3. Seven `wc -l | grep -qx N` checks across five plans will report false failures on
macOS.** (`WINDOWS.md` 16)

Detailed under Verification Status. Four of the five plans are unexecuted. The cost is
wasted debugging of a passing condition, not a shipped defect — but it is the kind of false
signal that erodes trust in the verify blocks generally, which is worse than one bad check.

## Self-Check: PASSED

Files claimed created, confirmed present: `src/kontakti.html`, `src/contact-send.php`,
`src/includes/contact-form.php`, `src/includes/notify.php`.

Commits claimed, confirmed in `git log`: `0083f23` (Task 1), `c9c9be6` (Task 2), `0b5b755`
(first summary), `37c6da2` (Task 3 closure). This revision is the fifth.

On the honesty of the claims above: every row marked **Observed** corresponds to something
someone or some check actually saw. The one inference in the Task 3 table — that a refresh
cannot resubmit — is labelled as an inference and its mechanism is given, so a later reader
can reject it if the mechanism turns out not to hold. The three residual risks are recorded
as open in `.planning/WINDOWS.md` rather than described only here, so they survive this
summary scrolling out of context and will block `/gsd-ship` until they are closed or
deliberately waived. No claim is made that the files were linted, that autofill was tested,
or that the failure path was exercised — none of those happened.
