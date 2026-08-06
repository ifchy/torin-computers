---
phase: 02-design-system-information-architecture
reviewed: 2026-08-06T00:00:00Z
depth: standard
files_reviewed: 34
files_reviewed_list:
  - .gitignore
  - scripts/backup-live-site.sh
  - scripts/deploy-new.sh
  - src/.htaccess
  - src/about.html
  - src/covid.html
  - src/css/base.css
  - src/css/components.css
  - src/css/layout.css
  - src/css/theme-a.css
  - src/includes/categories.php
  - src/includes/category-page.php
  - src/includes/dev-switcher.php
  - src/includes/footer.php
  - src/includes/header.php
  - src/includes/icons.php
  - src/includes/jsonld.php
  - src/includes/site-config.php
  - src/index.html
  - src/js/site.js
  - src/laptopi.html
  - src/mehanichni-problemi.html
  - src/msg.html
  - src/optimizatsiq.html
  - src/phptest.html
  - src/problem-stari.html
  - src/profilaktika-laptop.html
  - src/rezervni-chasti.html
  - src/test-laptop.html
  - src/tokov-udar.html
  - src/uslovia.html
  - src/warrently.html
  - src/za-bateriite.html
  - src/zalivane-technosti.html
findings:
  critical: 6
  warning: 17
  info: 15
  total: 38
status: issues_found
---

# Phase 02: Code Review Report

**Reviewed:** 2026-08-06
**Depth:** standard
**Files Reviewed:** 34
**Status:** issues_found

## Summary

Reviewed the Phase 2 design-system and IA implementation: two FTPS shell scripts, one `.htaccess`, eight PHP includes, sixteen `.html`-served-as-PHP pages, four stylesheets, and the single JS file.

**What the focused-attention areas actually turned up:**

- **PHP 5.2 compatibility: clean.** A full scan for `[]` short arrays, `<?=`, `__DIR__`, `??`, closures, `namespace`, `::class` and `goto` across `src/` returns zero hits. `dirname(__FILE__)` and `array()` are used consistently. No finding.
- **Output escaping / XSS in the PHP includes: clean.** `dev-switcher.php` whitelists both `$_GET['theme']` and `$_COOKIE['torin_theme']` against a hardcoded array with `in_array(..., true)` and emits only code-chosen literals; array-injection (`?theme[]=a`) is correctly rejected by the strict `in_array`. `jsonld.php` encodes rather than hand-writes, and PHP 5.2's unconditional `/` escaping does close the `</script>` break-out. Every `$site` interpolation in `footer.php`/`header.php`/`category-page.php` passes through `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`. No XSS finding.
- **Shell injection / quoting in the deploy scripts: no injection found**, but two real runtime breaks (see CR-04, WR-02) and a credential-persistence gap (WR-03).

The defects that matter are elsewhere, and they are concentrated in **CSS specificity** and **deploy/`.htaccess` hygiene**, not in the places the plan armoured most heavily. Two of them make the site's primary conversion elements literally invisible: the hero trust badge computes to **1.06:1** contrast (the code comment claims 10.14:1) and the focus ring on every primary CTA in the hero, footer and call bar computes to **1.03–1.21:1**. Both are pure specificity accidents — the intended token values are correct, the selectors that carry them lose.

Also: the deploy script's unfiltered `find` publishes `phptest.html`, whose own header comment asserts it is not uploaded; and the `.htaccess` promotion instruction, followed literally at Phase 4, would carry `X-Robots-Tag: noindex` onto the production root.

Contrast figures below are computed WCAG 2.x relative-luminance ratios, not estimates.

## Narrative Findings (AI reviewer)

## Critical Issues

### CR-01: Hero trust badge renders at 1.06:1 contrast — `.hero p` beats `.trust-badge`

**File:** `src/css/components.css:49-52` and `src/css/components.css:208-220` (markup: `src/index.html:23`)

**Issue:** `.trust-badge` is a `<p>` inside `.hero__inner`, so both rules match. Specificity:

- `.hero p` → `(0,1,1)` — one class, one type
- `.trust-badge` → `(0,1,0)` — one class

`(0,1,1)` wins regardless of source order, so the badge's `color: var(--c-on-brand)` (#16223a) is **overridden** by `color: var(--c-on-dark-muted)` (#c9d6ea). The rendered pair is **#c9d6ea on #ffc70a = 1.06:1** (Theme A: #c9d6ea on #fbad03 = 1.29:1). "Безплатна диагностика" — a stated conversion element (D-15) — is effectively unreadable, on the homepage, above the fold, in both themes. The comment on line 207 asserting "10.14:1" describes the value that never applies.

The same collision also silently breaks the hero height arithmetic documented at `components.css:23-31`: `.hero p { margin-block-start: var(--sp-md) }` overrides both `.trust-badge { margin-block-start: var(--sp-sm) }` and the `@media (min-width: 35rem)` override at line 733 (`var(--sp-lg)`, also `(0,1,0)`), so the measured 249.6px stack is wrong by 8px at mobile and 8px at ≥35rem.

**Fix:** Scope `.hero p` so it cannot reach the badge, rather than raising the badge's specificity (which would start a war):

```css
/* components.css:49 — target only the hero's prose paragraph */
.hero__inner > p:not(.trust-badge) {
	color: var(--c-on-dark-muted);
	margin-block-start: var(--sp-md);
}
```

Then re-verify the hero stack height, since the badge's own `--sp-sm`/`--sp-lg` margins now apply.

---

### CR-02: Focus ring on every primary CTA on a dark surface computes to 1.03–1.21:1

**File:** `src/css/base.css:157-168`

**Issue:** `:focus-visible` sets `outline-offset: 2px`, so the 3px ring is drawn **entirely outside** the button, on the surrounding surface — never on the amber fill the comment reasons about.

`.btn--primary:focus-visible` (line 168, `(0,2,0)`) and `.hero :focus-visible, .site-footer :focus-visible` (line 163-164, also `(0,2,0)`) tie on specificity; line 168 is later, so it **wins everywhere**, including on dark surfaces where the on-dark ring was explicitly authored. Computed ratios for the ring colour `--c-on-brand` #16223a against the surface it is actually drawn on:

| Location | Surface | Ratio |
|---|---|---|
| Hero CTAs (`index.html:16,20`) | `--c-ink-deep` #0e305d | **1.21:1** |
| Footer CTAs (`footer.php:65-66`) | `--c-ink-deepest` #0a2547 | **1.03:1** |
| Call bar CTAs (`index.html:192-193`) | `--c-ink-deepest` #0a2547 | **1.03:1** |
| Category-page CTAs (`category-page.php:228-229`) | white | 10.14:1 (correct) |

WCAG 2.1 SC 1.4.11 requires ≥3:1 for a focus indicator against adjacent colours. Keyboard users get no visible focus on the site's six most important controls. The rule at line 168 was written specifically to prevent this class of failure and instead causes it.

**Fix:** Restrict the amber-button override to the light-surface case and let the on-dark rule win on dark surfaces:

```css
/* base.css:163-168 */
.btn--primary:focus-visible { outline-color: var(--c-on-brand); }

/* Restated at higher specificity so a dark surface always wins the tie. */
.hero .btn--primary:focus-visible,
.site-footer .btn--primary:focus-visible,
.callbar .btn--primary:focus-visible { outline-color: var(--c-focus-on-dark); }
```

`--c-focus-on-dark` (#ffd84d) is 5.16:1 on #0e305d and 8.7:1 on #0a2547 — both pass. Note `.callbar` is inside neither `.hero` nor `.site-footer`, so it needs its own selector.

---

### CR-03: `deploy-new.sh` publishes `phptest.html`, disclosing the exact EOL PHP build

**File:** `scripts/deploy-new.sh:115-118`; `src/phptest.html:1-4`

**Issue:** With no arguments the script uploads `find . -type f ! -name '.DS_Store'` — every file under `src/`, including `phptest.html`, which executes:

```php
<?php echo 'PHP-IN-HTML-OK ' . phpversion(); ?>
```

`.htaccess:20` routes `.html` through the PHP handler, so `https://torin.bg/new/phptest.html` returns the precise build string (`5.2.17`) to any anonymous requester. PHP 5.2.17 reached end of life in 2011 and has a long public CVE list; publishing the exact patch level is direct attacker reconnaissance on a host the project cannot patch.

The file's own first line states *"Not uploaded to the live site … Deleted from the live host after use."* — that claim is false as soon as anyone runs the deploy script with no arguments, which is its documented primary usage (`deploy-new.sh:20`). Nothing in the repo enforces the claim.

**Fix:** Two changes, both needed — one enforces, one removes the trap:

```bash
# deploy-new.sh:117 — never publish spike artifacts
done < <(cd "$SRC_ROOT" \
  && find . -type f ! -name '.DS_Store' ! -name 'phptest.html' \
  | sed 's|^\./||' | sort)
```

and move `src/phptest.html` out of the deployable tree entirely (e.g. `.planning/phases/01-*/spikes/phptest.html.txt`), so the "not uploaded" claim is structurally true rather than aspirational. If it has already been deployed, delete it from `public_html/new/` on the host.

---

### CR-04: `backup-live-site.sh` plaintext-FTP fallback crashes on bash 3.2 (the platform's default shell)

**File:** `scripts/backup-live-site.sh:113-114` (also affects lines 139 and 153 via `PROTO_FLAGS`)

**Issue:** Line 20 sets `set -euo pipefail`. Line 113 sets `PROTO_FLAGS=()` (empty array), then line 114 expands `"${PROTO_FLAGS[@]}"`. Under `set -u`, **bash 3.2 treats expansion of an empty array as an unbound variable and aborts**. `/bin/bash` on this machine is 3.2.57 and `bash` on `PATH` resolves to it, so `#!/usr/bin/env bash` picks 3.2. Verified:

```
$ /bin/bash -c 'set -euo pipefail; A=(); echo "${A[@]}"'
/bin/bash: A[@]: unbound variable
```

So `BACKUP_ALLOW_PLAINTEXT_FTP=1` — the documented emergency escape hatch, reachable only when FTPS has already failed and the operator is trying to salvage a pre-deploy backup — dies immediately with `PROTO_FLAGS[@]: unbound variable` instead of connecting. The failure message is unrelated to the actual cause, and the operator is left with no backup while believing a documented recovery path exists. This is a pre-deploy backup script: the moment it is needed is the moment it must not fail obscurely.

**Fix:** Use a bash-3.2-safe empty-array expansion at each of the three call sites:

```bash
"${CURL_BASE[@]}" ${PROTO_FLAGS[@]+"${PROTO_FLAGS[@]}"} --list-only "ftp://..."
```

The `${arr[@]+"${arr[@]}"}` form expands to nothing when the array is empty and is safe under `set -u` on bash 3.2+. Apply at lines 114, 139 and 153. (`deploy-new.sh` is unaffected — its `CURL_BASE` is never empty, and `${#FILES[@]}` on an empty array is legal even in 3.2.)

---

### CR-05: The `.htaccess` Phase-4 promotion instruction, followed literally, deindexes torin.bg

**File:** `src/.htaccess:1-3` vs `src/.htaccess:16-18`

**Issue:** The file's header says:

> At Phase 4 cutover, this file (or its rules) is promoted to the root .htaccess **with the redirect target changed** from `https://torin.bg/new/$1` to `https://torin.bg/$1` (drop /new/).

That names exactly one required edit. But the file also contains:

```apache
<IfModule mod_headers.c>
	Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
```

Promoting the file with only the documented change puts `noindex, nofollow` on **every response from the production root** — all sixteen indexed pages, plus the sitemap Phase 4 is meant to add. `X-Robots-Tag` at the HTTP-header level overrides page-level signals, so nothing else in the redesign can counteract it. The observable result is total loss of organic search visibility for a business whose stated core value is being found by a visitor with a problem, and the cause would be invisible in any HTML-level review.

The comment block at lines 14-15 even explains *why* the header exists (staging-only), which makes the omission at lines 2-3 a trap rather than an oversight a reader would catch.

**Fix:** Make the removal an explicit, enumerated step in the file itself, and fence the block so a copy-paste promotion is loud:

```apache
# ── STAGING ONLY — DELETE BOTH THIS COMMENT AND THE BLOCK BELOW AT CUTOVER ──
# Phase 4 promotion checklist for this file:
#   1. change the redirect target: https://torin.bg/new/$1 -> https://torin.bg/$1
#   2. DELETE the X-Robots-Tag block below (it would deindex the whole site)
#   3. DELETE nothing else
<IfModule mod_headers.c>
	Header set X-Robots-Tag "noindex, nofollow"
</IfModule>
# ── END STAGING ONLY ────────────────────────────────────────────────────────
```

---

### CR-06: Staging noindex depends on an unverified Apache module, with no fallback

**File:** `src/.htaccess:16-18`; `src/includes/header.php:53-79`

**Issue:** The comment at lines 26-29 states plainly that module availability on `bell.host.bg` is **UNVERIFIED**, and correctly guards `mod_deflate` and `mod_expires` for that reason. `mod_headers` gets the same `<IfModule>` guard — but unlike compression and caching, whose absence is merely a missed optimisation, **the absence of `mod_headers` silently removes the only thing keeping a full sixteen-page duplicate of the live site out of Google's index**.

There is no second line of defence: `header.php` emits no `<meta name="robots">`, there is no `<link rel="canonical">` on any page, and `src/` contains no `robots.txt` (which the file's own comment at line 14 notes would not work at this path anyway). The failure is silent — the site looks fine, and the damage (duplicate-content dilution against the very pages SEO-04 is protecting) shows up weeks later in rankings.

**Fix:** Add a page-level fallback that does not depend on any module, gated on the same file-existence signal the dev switcher already uses so it disappears at cutover with `rm dev-switcher.php`:

```php
<?php // header.php, after line 59 — staging noindex fallback (delete at Phase 4
      // cutover together with dev-switcher.php).
      if (file_exists($torin_dev_switcher)) { ?>
<meta name="robots" content="noindex, nofollow">
<?php } ?>
```

Additionally verify `mod_headers` on the host before relying on the header form (`curl -sI https://torin.bg/new/index.html | grep -i x-robots-tag`).

---

## Warnings

### WR-01: `includes/` is directly web-accessible with no guard

**File:** `src/includes/` (all 8 files); no `.htaccess` present in that directory

**Issue:** The deploy script uploads `includes/*.php` into `public_html/new/includes/`, where the PHP handler executes them on direct request. `GET /new/includes/header.php` returns a partial document (doctype, head, header chrome, unclosed `<div id="wrap">`) **and sets the `torin_theme` cookie**; `GET /new/includes/footer.php` returns a footer plus a complete JSON-LD `LocalBusiness` block; `GET /new/includes/jsonld.php` returns the structured data standalone. None of them declare a charset consistently in isolation, and all of them are indexable orphan fragments the moment CR-06's `X-Robots-Tag` stops applying at cutover — including a bare JSON-LD block that could be picked up as a malformed entity for the business.

**Fix:** Ship a deny file alongside the includes (it will be uploaded by the existing `find`, since `find` picks up dotfiles):

```apache
# src/includes/.htaccess
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order allow,deny
	Deny from all
</IfModule>
```

Belt-and-braces (module-independent, matching CR-06's lesson): define `TORIN_ENTRY` in each page before its first `require_once`, and top each include with `if (!defined('TORIN_ENTRY')) { exit; }`.

---

### WR-02: `assets1/` size check aborts silently; its `:-0` fallback is dead code

**File:** `scripts/backup-live-site.sh:250-258`

**Issue:** Under `set -euo pipefail`:

```bash
ASSETS_SIZE_KB=$(du -sk "${BACKUP_ROOT}/assets1" 2>/dev/null | cut -f1)
ASSETS_SIZE_KB=${ASSETS_SIZE_KB:-0}
```

If `assets1/` does not exist (the worst case — nothing mirrored at all), `du` exits non-zero, `pipefail` propagates it, and the assignment's non-zero status trips `set -e`. The script exits **before** line 251, so the `:-0` default is unreachable and the carefully-worded diagnostic at line 256 never prints. Verified:

```
$ bash -c 'set -euo pipefail; S=$(du -sk /nope 2>/dev/null | cut -f1); S=${S:-0}; echo "reached $S"'
$ echo $?
1        # nothing printed
```

It fails safe (no false success), but the operator gets a bare exit 1 with no explanation precisely in the case the check exists to explain.

**Fix:**

```bash
ASSETS_SIZE_KB=$(du -sk "${BACKUP_ROOT}/assets1" 2>/dev/null | cut -f1 || true)
ASSETS_SIZE_KB=${ASSETS_SIZE_KB:-0}
```

---

### WR-03: FTP password survives Ctrl-C — `trap` covers `EXIT` only

**File:** `scripts/backup-live-site.sh:40-43`; `scripts/deploy-new.sh:45-48`

**Issue:** Both scripts write the plaintext FTP password into a `.netrc` file under `$TMPDIR` and register `trap cleanup EXIT`. For an **untrapped** fatal signal, bash terminates via the signal and the `EXIT` trap does not run. `backup-live-site.sh` recursively mirrors `assets1/` (~14 MB over FTP) — a multi-minute run that an operator will plausibly interrupt with Ctrl-C. The file is `0600`, so exposure is limited to the same UID, but it persists indefinitely under a predictable name pattern (`backup-live-site-netrc.*`) in a shared temp directory, which is exactly the persistence the file header claims to have eliminated ("removed via a trap on exit, including on error").

**Fix:** In both scripts:

```bash
trap cleanup EXIT INT TERM HUP
```

---

### WR-04: Credential parser and pubkey pin duplicated verbatim across both scripts

**File:** `scripts/backup-live-site.sh:48-78,106` and `scripts/deploy-new.sh:50-80,92`

**Issue:** The ~30-line embedded Python credential parser is byte-identical in both files, and the security-critical constant

```
FTP_HOST_PUBKEY_PIN="sha256//Z7N5Hk+6AzND7F/ToDmzG91E2tHDk6WVlyWLfDqXcRU="
```

appears twice. The comment at `backup-live-site.sh:101-105` says the pin must be recomputed on certificate rotation — an operator following it will update one file, the other will keep failing (or, worse, be "fixed" by someone deleting `--pinnedpubkey`, which silently degrades `-k` to no verification at all). The two copies have already drifted: `deploy-new.sh` carries `--tls-max 1.2` and `--ftp-create-dirs`, `backup-live-site.sh` does not.

**Fix:** Extract to `scripts/lib/ftp-creds.sh` sourced by both, holding the Python heredoc, `FTP_HOST_PUBKEY_PIN`, and the shared `curl` flags; keep only per-script differences (`--tls-max`, `--ftp-create-dirs`, `REMOTE_ROOT`) in the callers.

---

### WR-05: FTP `LIST` parsing mangles names and breaks on non-standard column counts

**File:** `scripts/backup-live-site.sh:205`

**Issue:**

```bash
name=$(awk '{ $1=$2=$3=$4=$5=$6=$7=$8=""; sub(/^[ \t]+/, ""); print }' <<< "$line")
```

Two defects:

1. **Assumes exactly 8 leading columns.** Standard Unix `LIST` is `perms links owner group size month day time/year name` — but a number of FTP servers (and some shared-hosting configurations) omit the group column, yielding 7 leading fields. Blanking `$8` then destroys the **first token of the filename**, and the entry is downloaded to a truncated local path with no error.
2. **Reassignment collapses whitespace.** Touching any field forces awk to rebuild `$0` with a single `OFS` between every field, so a filename containing two or more consecutive spaces is silently renamed on disk. The comment at line 127-131 confirms the live tree already contains a space-bearing filename (`assets1/img/Preloader-icon ORI.gif`), so multi-space names are not hypothetical for this host.

Both corrupt a *backup* — the artifact whose only job is byte-fidelity before a deploy touches production.

**Fix:** Cut on character offset rather than fields, which the Unix LIST format guarantees is stable relative to the time/year column:

```bash
name=$(printf '%s\n' "$line" | sed -E 's/^([^ ]+ +){8}//')
```

or, more robustly, extract via `awk` without reassignment:

```bash
name=$(awk '{ i = index($0, $9); print substr($0, i) }' <<< "$line")
```

and add an explicit `NF < 9 → error` guard so an unexpected LIST shape aborts loudly instead of corrupting names.

---

### WR-06: `error_log` in `MUST_CARRY_FILES` aborts the entire backup if absent

**File:** `scripts/backup-live-site.sh:165-167,178-188`

**Issue:** `error_log` is listed alongside `.htaccess`, `favicon.ico` and `mailer.php` as a must-carry root file. Every entry in that list is downloaded before any directory recursion, and any failure sets `DOWNLOAD_FAILED=1`, which hard-aborts at line 186 — **before** `.well-known/`, `cgi-bin/`, `covid-19/` and `assets1/` are mirrored. But `error_log` is a transient artifact: it does not exist when PHP has logged nothing, and hosting control panels routinely offer a "clear error log" button that deletes it. The consequence is that a routine, benign server state makes the mandatory pre-deploy backup (MIGR-03) refuse to run at all, at the exact moment someone is about to touch production.

**Fix:** Move it to a separate best-effort tier:

```bash
MUST_CARRY_FILES=( .htaccess favicon.ico google1718743335455f1c.html header.js otpuska.js mailer.php )
OPTIONAL_FILES=( error_log )
...
for f in "${OPTIONAL_FILES[@]}"; do
  echo "  downloading ${f} (optional)"
  download "$f" "${BACKUP_ROOT}/${f}" || echo "  note: ${f} not present on host — skipping" >&2
done
```

Also correct the success line at `backup-live-site.sh:262`, which prints `${#MUST_CARRY_FILES[@]}/${#MUST_CARRY_FILES[@]}` — a tautology that reports "7/7" whether or not anything was verified.

---

### WR-07: Nav disclosures stay open across same-document navigation

**File:** `src/js/site.js:30-59`

**Issue:** The only close paths are: clicking another disclosure button, `Escape` while focus is inside `.nav`, focus leaving `.nav`, and clicking outside `.nav`. Clicking a **link inside the open panel** matches none of them — `nav.contains(e.target)` is true, so the outside-click handler at line 57 returns without closing.

On the homepage this is a live bug, not a theoretical one. Five of the nav's links resolve to the current document:

- `index.html#contact-us` (Контакти, `header.php:136`)
- `index.html#kat-2`, `#kat-5`, `#kat-6` — the three unpublished categories, routed to homepage anchors by `torin_category_href()` (`categories.php:101-106`)

Each is a same-document fragment navigation: no reload, no page teardown, so `aria-expanded="true"` persists. Below 900px `.nav__list` is an **in-flow** panel (`components.css:452-461`), so the browser scrolls to the anchor while the expanded menu still occupies the top of the page — the visitor lands on a category anchor with the menu covering it. Publishing the remaining three categories (D-23) does not fix it, because Контакти is permanently a same-page anchor.

**Fix:** Close on any nav link activation. Two lines, no framework, no change to the aria-only state model:

```js
Array.prototype.forEach.call(nav.querySelectorAll('a[href]'), function (a) {
	a.addEventListener('click', function () { closeAll(null); });
});
```

---

### WR-08: With JS off, the entire mobile navigation disappears — not just the six category links

**File:** `src/includes/header.php:74-77`; `src/css/components.css:452-474`

**Issue:** The comment in `header.php` records the no-JS gap as:

> With it blocked the six category links are unreachable from the nav — an accepted, recorded gap: all six stay reachable from the homepage card grid (IA-01).

That understates the actual behaviour. Below 56.25rem, `.nav__list { display: none }` and the **only** rule that reveals it is `[aria-expanded="true"] + .nav__list` — an attribute written exclusively by `site.js`. With script execution off at mobile width, the served HTML exposes zero navigation links: not Начало, not Лаптопи и части, not Тествай сам, not Контакти, and not the six categories. Only the logo (`header.php:93`) and the five footer links remain.

At desktop width the recorded gap is accurate (the list is `display: flex` unconditionally; only `.nav__sub` needs JS). But the mobile case is the majority of traffic for a local repair shop, and the project treats no-JS degradation as a hard contract — which the `<details>` disclosures honour and the nav does not.

**Fix:** Either make the panel reachable without script — swap the `<button>` + JS for a `<details>`/`<summary>` wrapper at mobile width, matching the pattern already proven on the homepage catch-all — or, at minimum, correct the comment to state the real scope and add a `<noscript>` fallback:

```php
<noscript><style>.nav__list, .nav__sub { display: block; } .nav__toggle { display: none; }</style></noscript>
```

placed in `header.php`'s head. Do not leave the recorded gap describing a smaller failure than the one that ships.

---

### WR-09: Opening hours are stored twice and can silently disagree

**File:** `src/includes/site-config.php:35-41` and `src/includes/jsonld.php:58-67`

**Issue:** `site-config.php` opens with the rule that motivates its own existence (lines 15-19):

> The scalar key this replaced was REMOVED rather than kept alongside — **two representations of one fact silently disagree the day a number changes**, and single-sourcing is the entire reason this file exists.

The hours violate exactly that rule. `$site['hours']` holds the human string `'Понеделник – Петък, 8:00 – 16:00'`; `jsonld.php:63-65` independently hardcodes `dayOfWeek`, `'opens' => '08:00'`, `'closes' => '16:00'`. The comment at `site-config.php:39-40` acknowledges it ("change BOTH when the owner answers") — but a comment is not a mechanism, and this is the value the file itself flags as highest-consequence: the structured-data copy is what Google acts on, so a divergence sends real customers to a closed shop.

Both values are additionally `[ASSUMED]` pending OWNER-QUESTIONS #20, so the edit *will* happen, and it will happen in two files.

**Fix:** Make the machine values the single source and derive the display string, or at minimum co-locate them so one edit covers both:

```php
// site-config.php
'hours_days'  => array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
'hours_opens' => '08:00',
'hours_closes' => '16:00',
'hours'       => 'Понеделник – Петък, 8:00 – 16:00',   // display copy, same source block
```

and have `jsonld.php` read `$site['hours_days'] / ['hours_opens'] / ['hours_closes']`.

---

### WR-10: Homepage dials a different number string than the footer, and bypasses site-config entirely

**File:** `src/index.html:16,20,166,172,192,193` vs `src/includes/footer.php:65-66` and `src/includes/site-config.php:20,45`

**Issue:** `index.html` hardcodes `tel:+35929549710` in three places and `viber://chat?number=%2B35929549710` in three more. `footer.php` and `category-page.php` render `tel:<?php ... str_replace(' ', '', $site['phones'][0]) ?>` → **`tel:029549710`**, and the Viber link from `rawurlencode($site['viber'])`. So a single homepage serves the same phone in two different dial strings — `+35929549710` in the hero/CTA/call bar, `029549710` in the footer. Both dial correctly from a Bulgarian handset, but `029549710` fails from abroad, and the divergence means analytics on `tel:` clicks cannot be aggregated.

More consequentially: editing `site-config.php` — the file whose header declares it the "single source of truth for site-wide contact values" — changes the footer and the three category pages, and leaves the homepage hero, the repeated CTA and the sticky call bar pointing at the old number. That is precisely the "phone number changed on 16 pages but not the 17th" failure the project's stack decision cites as the reason to abandon copy-paste HTML. `index.html:170-171` even asserts the value is a literal "never assembled from a request value (T-02-09)" — true, but it addresses injection, not the single-sourcing defect.

**Fix:** Add an E.164 key to config and consume it everywhere:

```php
// site-config.php
'phone_e164' => '+35929549710',   // E.164 form of phones[0]; jsonld.php's telephone reads this too
```

Then in `index.html`, `footer.php`, `category-page.php` and `jsonld.php:42`:

```php
href="tel:<?php echo htmlspecialchars($site['phone_e164'], ENT_QUOTES, 'UTF-8'); ?>"
href="viber://chat?number=<?php echo rawurlencode($site['viber']); ?>"
```

---

### WR-11: Sticky call bar sits under the iOS home indicator

**File:** `src/css/components.css:330-349`

**Issue:** `.callbar` is `position: fixed; bottom: 0; height: 56px` with no safe-area allowance. On any iPhone with a home indicator (every model since the X), the bottom ~34px of the viewport is the system gesture region: the lower ~60% of both CTA buttons is overlapped by the indicator, taps in that band are intercepted as system gestures, and the effective tap target drops to ~22px — below the 44px minimum the rest of this stylesheet enforces (`.nav__toggle`, `.disc > summary`, `.footer-phone`, `.footer-links a` all carry explicit 44px floors). This is the component whose entire justification (`index.html:186-190`) is keeping the two conversion actions reachable on a small screen.

The reserve on `body` (line 353) has the same problem: `padding-block-end: 3.5rem` matches the bar's nominal 56px but not its safe-area-extended height, so the footer is still partially occluded.

**Fix:**

```css
.callbar {
	height: calc(56px + env(safe-area-inset-bottom, 0px));
	padding-block-end: env(safe-area-inset-bottom, 0px);
}

body {
	padding-block-end: calc(3.5rem + env(safe-area-inset-bottom, 0px));
}
```

`env()` with a fallback degrades to current behaviour on browsers without support.

---

### WR-12: Dev theme switcher overlaps the call bar it is meant to help review

**File:** `src/css/components.css:325-402`

**Issue:** `.dev-switcher` is `position: fixed; right: var(--sp-sm); bottom: var(--sp-sm); z-index: 50`. `.callbar` is `position: fixed; bottom: 0; height: 56px; z-index: 40`. The switcher's box (roughly 8px from the bottom, ~30px tall) lands **inside** the call bar's 56px band, at higher z-index, on the right-hand half — which is the «Пишете във Viber» button (`index.html:193`).

The comment at lines 373-375 reasons only about the hero CTAs ("Pinned bottom-right, away from the hero CTA buttons") and does not account for the call bar, which is `display: none` only at ≥56.25rem. So at every width where the call bar exists — i.e. the entire mobile review surface — the dev tool covers one of the two conversion controls under review, in both themes. The theme comparison this switcher exists to enable cannot be performed on the component most likely to differ between themes.

**Fix:** Lift the switcher clear of the bar at the widths where the bar exists:

```css
.dev-switcher {
	bottom: calc(56px + var(--sp-sm) + env(safe-area-inset-bottom, 0px));
}

@media (min-width: 56.25rem) {
	.dev-switcher { bottom: var(--sp-sm); }
}
```

---

### WR-13: Caching/compression config is wrong for an actively-edited staging subtree and omits JS

**File:** `src/.htaccess:38-55`

**Issue:** Three separate defects in one block:

1. **`ExpiresByType text/css "access plus 7 days"`** on a subtree whose explicit purpose is a live design review, with no fingerprinting on `css/base.css`, `layout.css`, `components.css` or `theme-a.css`. `text/html` is correctly pinned to 0 seconds, so reviewers get fresh markup against week-stale CSS — silently, and the disagreement looks like a CSS bug. Every design iteration in this phase is affected.
2. **JavaScript is absent from `ExpiresByType` entirely.** With `ExpiresActive On` and no `ExpiresDefault`, unlisted types get no `Expires`/`Cache-Control` at all, so `js/site.js` is re-fetched on every page view — the opposite of the CSS problem, and inconsistent with a block whose stated motive (lines 24-26) is that "every CSS/font byte is raw wire cost".
3. **`AddOutputFilterByType DEFLATE` lists `application/javascript` and `application/x-javascript` but not `text/javascript`**, which is what current Apache `mime.types` maps `.js` to. On this host `site.js` is likely served uncompressed.

**Fix:**

```apache
<IfModule mod_deflate.c>
	AddOutputFilterByType DEFLATE text/html text/plain text/css text/xml
	AddOutputFilterByType DEFLATE application/javascript application/x-javascript text/javascript
	AddOutputFilterByType DEFLATE application/json image/svg+xml
</IfModule>

<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType text/html "access plus 0 seconds"
	# Staging: CSS/JS must not outlive a review cycle. Phase 4 raises these
	# together with a fingerprinting scheme (DESIGN-02).
	ExpiresByType text/css "access plus 0 seconds"
	ExpiresByType text/javascript "access plus 0 seconds"
	ExpiresByType application/javascript "access plus 0 seconds"
	ExpiresByType font/woff2 "access plus 1 year"
	ExpiresByType image/png "access plus 30 days"
	ExpiresByType image/svg+xml "access plus 30 days"
</IfModule>
```

---

### WR-14: Unguarded array reads can emit PHP notices into the served HTML

**File:** `src/includes/category-page.php:85-91`; `src/index.html:99-101,134-136`

**Issue:** Three reads assume keys exist with no guard, on a host with no `display_errors` hardening anywhere in the tree (grep for `ini_set`/`error_reporting` across `src/` returns nothing, and PHP 5.2 shared hosting commonly ships `display_errors = On`):

- `torin_render_svc_item()` reads `$item['text']` unconditionally (line 87 and line 90). The documented contract at line 37 says entries carry a `'text'` key, but nothing enforces it; a Phase 3 entry with only `'href'` renders `Notice: Undefined index: text` inside an `<a>` in the page body.
- `index.html:100` and `:135` read `$torin_cat_by_id[$torin_ref]['name']` for hardcoded ids `kat-1/4/5` and `kat-3/5`. The map at lines 83-86 is built from `$torin_categories`, so removing or renaming any of those five ids — an ordinary Phase 3 edit, given `categories.php` invites publish-state changes — emits `Notice: Undefined index: kat-5` into the homepage's catch-all section and renders an empty link.

Both are the kind of defect that ships looking fine and then surfaces in front of a customer after an unrelated content edit.

**Fix:** Guard the reads, and fail closed rather than rendering a broken link:

```php
// category-page.php:85
function torin_render_svc_item($item) {
	if (!isset($item['text']) || trim($item['text']) === '') { return; }
	...
}
```

```php
// index.html:99
<?php foreach (array('kat-1', 'kat-4', 'kat-5') as $torin_ref) {
	if (!isset($torin_cat_by_id[$torin_ref])) { continue; } ?>
```

---

### WR-15: No skip-to-content link on any of the sixteen pages

**File:** `src/includes/header.php:82-140`

**Issue:** Every page opens with the same block of chrome — dev switcher, logo link, hamburger, and a five-item nav that expands to eleven focusable elements when the Услуги disclosure is open — before `<main>`. There is no bypass mechanism, which is WCAG 2.1 SC 2.4.1 (Bypass Blocks, Level A). Keyboard and switch-access users tab through the full header on every one of the sixteen pages. The `.visually-hidden` utility needed to implement it already exists at `base.css:145-154`, and the codebase otherwise cites APG and WCAG SC numbers throughout, so this reads as an omission rather than a deliberate trade-off.

**Fix:** In `header.php`, immediately after `<body class="site-body">`:

```php
<a class="skip-link visually-hidden" href="#main">Към основното съдържание</a>
```

with a `:focus` rule that un-hides it, and add `id="main"` plus `tabindex="-1"` to each page's `<main>` (or move `<main>` into the shared includes).

---

### WR-16: HTTPS canonicalization has no proxy-terminated-TLS fallback

**File:** `src/.htaccess:10-12`

**Issue:** `RewriteCond %{HTTPS} off` is the sole protocol test. On shared hosting where TLS is terminated at a load balancer or CDN edge (a configuration budget hosts add without notice, and one this project cannot control), `%{HTTPS}` reports `off` for requests that arrived over HTTPS. The rule then redirects `https://torin.bg/new/x` to `https://torin.bg/new/x` — an **infinite 301 loop**, served as a permanent redirect that browsers cache, so the outage persists locally even after the `.htaccess` is fixed. The blast radius grows at Phase 4 when this file is promoted to the production root.

**Fix:** Accept the forwarded-protocol header as equivalent:

```apache
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !=https
RewriteRule ^(.*)$ https://torin.bg/new/$1 [R=301,L]

RewriteCond %{HTTP_HOST} ^www\.torin\.bg$ [NC]
RewriteRule ^(.*)$ https://torin.bg/new/$1 [R=301,L]
```

Splitting the two conditions into separate rules is required because the `[OR]` form cannot express "HTTPS off AND not forwarded-https". Both variants still resolve in one hop.

---

### WR-17: Dev theme cookie is scoped to the whole domain, including production

**File:** `src/includes/dev-switcher.php:27`

**Issue:** `setcookie('torin_theme', $torin_theme, time() + 2592000, '/')` sets a 30-day cookie at path `/`, not at `/new/`. Consequences: the cookie is transmitted on every request to the live production site at `torin.bg/` for 30 days after a single staging visit; it survives the Phase 4 `rm src/includes/dev-switcher.php` with nothing to clear it; and it is set without `HttpOnly`, so any script on the domain can read it. None of these is exploitable today, but the file's stated guard model is "its own presence on the server IS the guard" — a domain-scoped cookie outliving the file quietly breaks that invariant.

**Fix:**

```php
setcookie('torin_theme', $torin_theme, time() + 2592000, '/new/', '', true, true);
```

(path scoped to the staging subtree, `secure`, `httponly` — all supported in PHP 5.2's `setcookie` signature).

---

## Info

### IN-01: Two icons are defined and never used

**File:** `src/includes/icons.php:39-42`
**Issue:** `case 'close'` and `case 'check'` are never reached — the only call sites use `phone`, `chat`, `mail`, `pin`, `clock`, `chevron-down`, `menu` and the six dynamic `cat-*` names. The header comment claims "All 15" are in service.
**Fix:** Delete both cases, or note them as reserved for Phase 3/4 in the header comment so the count claim stays true.

### IN-02: `--r-pill` is declared and unused

**File:** `src/css/base.css:97`
**Issue:** Self-documented ("unused in Ph2"). Acceptable as a token-system decision; flagged only so it is not mistaken for live styling.
**Fix:** No change needed this phase.

### IN-03: `.notice--error` / `.notice--success` have no consumer

**File:** `src/css/components.css:644-652`
**Issue:** Self-documented at lines 614-621 as built ahead of Phase 4 CONTACT-03. Dead CSS in the shipped bundle against a stated 20 KB budget.
**Fix:** No change needed; re-check at Phase 4 that both variants gained a consumer.

### IN-04: `$torin_cat_key` is a single-use alias

**File:** `src/mehanichni-problemi.html:22-24`, `src/optimizatsiq.html:22-24`, `src/zalivane-technosti.html:21-23`
**Issue:** `$torin_cat_key = $torin_cat['id'];` is read once, on the next-but-one line, as `'cat_id' => $torin_cat_key`. The indirection adds a name without adding meaning, replicated across three files.
**Fix:** `'cat_id' => $torin_cat['id'],` and drop the variable.

### IN-05: `<div id="wrap">` has no styling anywhere

**File:** `src/includes/header.php:89`; closed at `src/includes/footer.php:93`
**Issue:** No rule in any of the four stylesheets targets `#wrap`. It is a structural leftover of the legacy "Liquid" template that the rebuild otherwise discards, and it costs an open/close split across two include files (documented at `footer.php:3-4` as "the templating contract").
**Fix:** Remove the element and the paired closing tag, simplifying the header/footer contract to body-level.

### IN-06: `.site-header__brand` class is never styled

**File:** `src/includes/header.php:93`
**Issue:** No CSS rule matches `.site-header__brand`; only `.site-header__logo` (the child `<img>`) is styled.
**Fix:** Drop the class, or add the rule it implies (e.g. removing the anchor's default focus/hover treatment).

### IN-07: Legal line carries two classes for one concept

**File:** `src/includes/footer.php:83`; `src/css/layout.css:89-92`; `src/css/components.css:608-612`
**Issue:** `class="site-footer__legal footer-legal"` splits one element's styling across two rules in two files — `max-width`/`color` in layout.css, margins/border in components.css.
**Fix:** Consolidate into `.site-footer__legal` in `components.css` alongside the other footer rules.

### IN-08: `rel="noopener"` without `target="_blank"` is a no-op

**File:** `src/includes/footer.php:35`
**Issue:** `rel="noopener"` only has an effect on links that open a new browsing context. The Google Maps deep link opens in the same tab, so the attribute does nothing.
**Fix:** Either add `target="_blank"` (likely the intent — leaving the site to open Maps in-place loses the visitor) or drop the `rel`.

### IN-09: `torin_esc()` exists but four other files repeat the raw call

**File:** `src/includes/category-page.php:76-78` vs `src/includes/header.php:60,62,124`, `src/includes/footer.php:27,35,50,55,60,65`, `src/index.html:51,54,55,100,135`
**Issue:** The helper was extracted with the correct rationale (PHP 5.2 defaults `htmlspecialchars` to ISO-8859-1, so the charset argument is mandatory), but only one of five files uses it. The remaining ~14 call sites each repeat `ENT_QUOTES, 'UTF-8'` by hand — fourteen chances to omit the charset.
**Fix:** Move `torin_esc()` into `site-config.php` (already required by every consumer) and use it at all call sites.

### IN-10: Empty `<div class="cta-block__form">` ships in the served HTML

**File:** `src/index.html:180`
**Issue:** A placeholder element with no content and, deliberately, no CSS rule referencing it (`components.css:310-315`). It is emitted on the homepage for every visitor and every crawler.
**Fix:** Comment out the node rather than shipping it, so the D-17 "deleting it is a subtraction" property is preserved without an empty element in the output.

### IN-11: `theme-a.css` loads even when Theme B is selected

**File:** `src/includes/dev-switcher.php:34`
**Issue:** `$torin_extra_head` is assigned outside the `if ($torin_theme === 'a')` block, so the override stylesheet is requested on every staging page view in both themes. Harmless (its rules are gated on `[data-theme="a"]`) but it is a render-blocking request that measures nothing.
**Fix:** Move line 34 inside the `if` at lines 31-33.

### IN-12: Copyright year uses server-local time with no timezone set

**File:** `src/includes/footer.php:83`
**Issue:** `date("Y")` with no `date_default_timezone_set()` anywhere in the tree resolves against the host's system timezone, which on shared hosting is commonly UTC. The footer year on all sixteen pages flips two hours late relative to Sofia. Trivial, but it is the one dynamic value in the footer.
**Fix:** `date_default_timezone_set('Europe/Sofia');` in `site-config.php` (safe on PHP 5.2), or hardcode the year.

### IN-13: `list-style: none` without `role="list"` drops list semantics in Safari

**File:** `src/css/components.css:286-291,588-595`; `src/css/layout.css` (nav lists at `components.css:452-468`)
**Issue:** Safari/VoiceOver removes list semantics from a `<ul>` whose `list-style` is `none`, so `.disc__links`, `.footer-links`, `.nav__list` and `.nav__sub` are not announced as lists or item-counted.
**Fix:** Add `role="list"` to the affected `<ul>` elements in `header.php`, `footer.php`, `index.html` and `category-page.php`.

### IN-14: Eyebrow rule can render above a heading-less paragraph

**File:** `src/includes/category-page.php:124-138`
**Issue:** `<span class="eyebrow">` is emitted at line 127, before the `$torin_has_fixes` guard at line 128. A category with symptoms but no `fixes` therefore renders a 40px amber rule directly above a bare `<p class="svc__symptoms">` with no heading between them. The block's own design note (lines 122-123) states each half is guarded "so neither can ever produce a heading standing on its own" — the eyebrow is the one part not guarded.
**Fix:** Move line 127 inside the `if ($torin_has_fixes)` block, or add a second eyebrow inside the symptoms-only branch.

### IN-15: `Свържете се с нас` appears twice as an `<h2>` on every page

**File:** `src/index.html:162` + `src/includes/footer.php:33`; `src/includes/category-page.php:224` + `src/includes/footer.php:33`
**Issue:** Every page renders two identical level-2 headings. Screen-reader users navigating by heading get two indistinguishable "Свържете се с нас" entries, and the heading outline reads as a duplicated section.
**Fix:** Differentiate the footer heading (e.g. «Контакти» or «Къде да ни намерите»), which also better describes its address/phone/email content.

---

_Reviewed: 2026-08-06_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
