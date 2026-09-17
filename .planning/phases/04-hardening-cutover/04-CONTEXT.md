# Phase 4: Hardening & Cutover - Context

**Gathered:** 2026-09-17
**Status:** Ready for planning

<domain>
## Phase Boundary

This phase makes the contact path real and secure, makes working hours and holiday
closures owner-editable, makes service demand observable, and moves the rebuilt site
from `public_html/new/` to the live domain root without losing a single indexed URL.

**In scope:** the contact form (it does not exist yet — there is no `<form>` and no
`mailer.php` anywhere in `src/`), the PHP runtime upgrade that precedes it, photo
uploads, notification delivery, analytics, the owner-editable settings file, the
holiday banner, image/caching performance work, `robots.txt`/`sitemap.xml`, and the
cutover itself.

**Not in scope:** new content, new service pages, visual redesign of anything other
than the holiday banner and the CTA slot vacated by the Viber button.

</domain>

<decisions>
## Implementation Decisions

### PHP runtime and host

- **D4-01:** **Upgrade PHP off 5.2.17 to the newest stable version the control panel
  offers**, and do it FIRST — before any form code is written. Rationale is recorded
  because it was argued explicitly: the code needs **zero changes** (a tree-wide grep
  for every construct PHP 7 removed — `ereg`, `split`, `mysql_*`, `each()`,
  `create_function`, magic quotes, `$HTTP_*_VARS` — returns **nothing**; the codebase
  was deliberately written 5.2-safe, which is a strict subset of what 8.x runs).
  Building the contact form on 5.2 would mean hand-writing compensating controls for
  `max_input_vars` and `max_file_uploads` — the two guards protecting a public POST
  endpoint, both introduced in **5.3.9** and absent from 5.2 — and then having them be
  dead weight after a later upgrade.
  — **Reversibility:** costly — reverting means switching the panel back AND restoring
  the matching `AddHandler` name, then re-verifying all 19 pages. Not hard, but never
  a one-liner.

- **D4-02:** **The `AddHandler` line is the entire risk of D4-01, and it is tested on
  `/new/` first.** Every one of the 19 pages executes only because `src/.htaccess` says
  `AddHandler application/x-httpd-php52 .html .htm`. Alt-PHP handler names are
  version-pinned and vary by host (`x-httpd-php74`, sometimes
  `x-httpd-alt-php74___lsphp`). A mismatch serves raw PHP source or 500 on every page
  at once. `/new/` has its own `.htaccess`, so it is switched and verified there while
  root is untouched.

- **D4-03:** **An account-wide version switch cannot break the current live site** —
  verified, not assumed. The live root `.htaccess` contains only a cPanel-generated
  handler block ("inherits the PHP package"), **no live `.html` file contains any PHP**,
  and the only PHP at root is `mailer.php`, which is forward-compatible. This was the
  expected blocker and it is not one.

- **D4-04:** **Watch for cPanel rewriting the handler block at cutover.** cPanel writes
  its own handler block into the root `.htaccess` when the PHP version changes. At
  cutover our hand-written `AddHandler` and that auto-generated block will coexist in
  the same file and may conflict.

### Contact form — notification and delivery

- **D4-05:** **Telegram bot is the primary notification channel; email is the backup.**
  A single HTTPS POST to `api.telegram.org`, free, no deliverability concept — it
  reaches the device or Telegram retries. `sendMediaGroup` puts the damage photos
  directly into the owner's phone notification, so he can see a cracked screen and
  call back with a price without opening a computer. Group-capable, so more than one
  person can receive enquiries without sharing a mailbox.
  — **Reversibility:** reversible — the notification layer is one module behind one
  interface; adding or dropping a channel does not touch the form.

- **D4-06:** **Outbound HTTPS from PHP on `bell.host.bg` is UNVERIFIED and must be
  probed before D4-05 is committed to.** Budget shared hosts sometimes block outbound
  connections or disable cURL/`allow_url_fopen`. This is the same class of unknown as
  the Phase 1 `AddHandler` discovery — it needs a live probe, not an assumption, and it
  is sequenced early, alongside the PHP upgrade.

- **D4-07:** **No server-side storage of submissions.** Notifications only. The
  consequence was stated plainly and accepted: if Telegram AND email both fail, the
  enquiry is genuinely lost — the visitor is told so and given the phone number, so it
  is not *silent*, but recovery depends on them choosing to call. What this buys is a
  much shorter privacy note and **no data-retention obligation** in `uslovia.html`.
  Photos still transit PHP's temp directory; they are deleted after sending, never kept.

- **D4-08:** **A submission is successful if ANY one channel succeeds.** Telegram up and
  email down is not a failure and must not be reported as one.

- **D4-09:** **Confirmation email to the customer.** Gives them proof it arrived, and a
  mistyped address bounces immediately rather than after a lost week.

- **D4-10:** **If every notification path fails: honest error plus the shop's phone
  number.** The form handles its own errors rather than blindly redirecting to
  `msg.html` the way the legacy mailer does — that script ignores `mail()`'s return
  value entirely.

- **D4-11:** **Transport for the email leg: to be chosen at planning** between
  authenticated SMTP via the host's own mailbox (`office@torin.bg` already exists here;
  gives SPF alignment and keeps visitor data on the shop's infrastructure) and a
  transactional relay. On the upgraded PHP the TLS certificate is actually verified —
  peer verification only became a default in 5.6, so this option did not properly exist
  before D4-01.

### Contact form — photo uploads

- **D4-12:** **Photos are optional.** Plenty of faults have nothing to photograph — "it's
  slow", "it won't boot" — and requiring one would turn those visitors away at the last
  step.

- **D4-13:** **Up to 5 photos, 10 MB each.** Enough for the damage, the model label and
  the port. Assumes `post_max_size` and `upload_max_filesize` can be raised to match —
  to be confirmed in the control panel.

- **D4-14:** **Shrink in the browser before upload** (~1600px), so a 9 MB phone photo
  becomes under 1 MB and nobody meets the limit. **A no-JS fallback that simply enforces
  the server limit is required** — the site currently ships almost no JavaScript and this
  adds some.

- **D4-15:** **Uploads are content-verified, re-encoded through GD, and given random
  filenames.** Never trust the extension or the client-supplied MIME type. This matters
  more here than on a normal host: **this server executes `.html` as PHP**, so any file
  that survives intact in a web-reachable folder is a code-execution risk.
  — **Reversibility:** reversible — but weakening it later would be a security
  regression, not a preference change.

### Contact form — placement and CTA

- **D4-16:** **A dedicated contact page.** There is no contact page today — the nav's
  «Контакти» item points at `index.html#contact-us`, a homepage section. The new page
  carries the form, the phone numbers, the address and the map link together, gives every
  CTA a specific destination, and can rank for «контакти торин». A new URL is safe: new
  URLs do not threaten existing ones.
  — **Reversibility:** costly — once indexed, removing it requires a 301, and the nav
  and every CTA slot point at it.

- **D4-17:** **«Изпратете запитване», linking to the contact page, replaces the Viber
  button in all four CTA slots** — `src/index.html` (3 occurrences),
  `src/includes/footer.php`, `src/includes/category-page.php`. This preserves the
  call-first / write-second pairing the design is built around and needs no layout
  change. **The Viber button still ships in the tree today** even though CONTACT-02 was
  retired on 2026-09-11; removing it is Phase 4 work, not a done deal.

### Analytics

- **D4-18:** **Umami Cloud free tier**, not GA4. The owner asked for Google Analytics and
  this is a deliberate departure that **needs his sign-off**. The reasoning: the ePrivacy
  obligation attaches to *storing things on the visitor's device*, not to counting
  visits. GA4 always sets cookies, so it always needs a consent banner — which becomes
  the first thing a visitor sees on a site whose entire premise is getting them to their
  problem fast, costs ~50 KB of third-party JS against DESIGN-02, and under-counts badly
  once people decline. A cookieless tool needs no banner, so **there is nothing to be
  punished for**. Umami is free, supports custom events (required for `tel:` tracking),
  and has a dashboard a non-analyst can read. The owner's "no extra spend" constraint
  ruled out Plausible/Fathom.
  — **Reversibility:** reversible — one script tag and an event-name map.

- **D4-19:** **Verify Umami's current free-tier limits against live docs before
  committing.** Free-tier terms shift; this is a researcher task, not a recollection.

- **D4-20:** **If Umami's events cannot track button clicks well, add our own beacon for
  the clicks** and keep Umami for pages. Stays banner-free and still answers everything.
  Do NOT fall back to GA4 plus a banner.

- **D4-21:** **Tracked events: call-button clicks, service-page visits, contact-form
  submissions, and form drop-off** (where people abandon — this is what will reveal
  whether the photo upload or the consent checkbox is losing them). Call clicks cover
  every `tel:` link: hero, repeated CTA block, sticky bar, footer, category pages.

- **D4-22:** **Microsoft Clarity is explicitly ruled out** despite being free and
  unlimited — it records sessions and sets cookies, making it the most consent-hungry
  option available. **Matomo self-hosted is ruled out** despite being free and
  PHP+MySQL-compatible with this host: it is a full application needing its own security
  patching, the same ongoing burden the project rejected when it rejected WordPress.

### Owner-editable settings

- **D4-23:** **A plain-text settings file, never raw PHP.** One setting per line
  (`hours: 8:00-16:00`, `vacation_to: 2026-08-20`). The driving risk: hours currently
  live in `src/includes/site-config.php` as a PHP array, and a stray quote from a
  non-technical editor **blanks all 19 pages** — and he would have no way to tell what he
  did. **Malformed input must fall back to the last known-good values, never
  white-screen.** `site-config.php` stays the single source underneath; the text file
  feeds it.

- **D4-24:** **He edits it through cPanel File Manager**, using the control-panel login
  he now has — nothing to install, works from any machine. **A short guide in Bulgarian
  is part of the deliverable**, not an afterthought.

- **D4-25:** **Hours: Mon–Fri 8:00–16:00, no lunch break, closed Saturday and Sunday —
  and the weekend closure is stated in neither place.** Confirmed by the owner. In
  structured data a day that is not listed **is** closed, so publishing `Mo-Fr
  08:00-16:00` and nothing else tells Google the weekend is closed without a word of it
  appearing on the page. This matches the user's own instinct that unstated means closed.
  Removes the `[ASSUMED]` marker from `site-config.php`.

- **D4-26:** **The hours value must stop being duplicated.** It is currently written
  twice — `site-config.php` and hard-coded again in `src/includes/jsonld.php`'s opening
  hours. Editing the settings file must change both, or the owner will silently tell
  Google something different from what the footer says.

- **D4-27:** **Holiday banner: a top strip on every page, auto-expiring, not
  dismissible.** He sets start date, end date and message; it appears site-wide and
  disappears by itself once the end date passes — auto-hiding was an explicit owner
  requirement, not just an off switch. Not dismissible because a closure is precisely
  what a visitor must not miss. **It also marks the shop closed in the structured data
  for that period**, so Google does not show the shop as open while it is shut.
  Timezone is Europe/Sofia.

### Cutover

- **D4-28:** **Server-side move, both directions.** Move the current root files into
  `public_html/old/`, then move `public_html/new/*` up to root. These are server-side
  renames: no re-upload, a swap window measured in seconds, and rollback is the same
  move in reverse. The old site stays on disk as a live safety net.
  — **Reversibility:** reversible — and this is the entire point of choosing it.

- **D4-29:** **`https://torin.bg` (no www) is canonical.** The other three
  protocol/host variants 301 into it. Today **all four variants return 200 with no
  redirect at all**, and Search Console's ranking variant is `http://www.torin.bg/` —
  the least preferred form — while `site-config.php` already declares
  `https://torin.bg/`. SEO-04 covered paths; **nothing has ever covered the host**, which
  is how this went unseen for three phases. Choosing non-www means nothing in the build
  changes.
  — **Reversibility:** **one-way** — once the 301s are published and Google consolidates
  onto the canonical host, switching to www means a second consolidation across the whole
  site and real ranking disturbance. This is not a preference that can be revisited
  cheaply after launch.

- **D4-30:** **The `.htaccess` promotion needs TWO edits, not one** — the canonicalisation
  target (`https://torin.bg/new/$1` → `https://torin.bg/$1`) AND the `RewriteBase`
  (`/new/` → `/`). Missing either breaks a redirect **silently, at 301, with a
  `Location` header present**. This is not hypothetical: on the first deploy of the
  retirement rules, a relative substitution with no base made Apache build the `Location`
  from the filesystem path, and all four 301'd to a 404. **A check that greps only
  `^HTTP/` and `^location:` PASSES that defect** — only following the redirect exposes it.

- **D4-31:** **The seven stale files are deleted manually via FileZilla before the swap**,
  written into the cutover checklist. `deploy-new.sh` uploads and never deletes, and no
  script in this project can delete a remote file. The files: `covid.html`,
  `laptopi.html`, `rezervni-chasti.html`, `za-bateriite.html` (source-deleted, unreachable
  behind 301s) and `profilaktika17.jpg`, `profilaktika7.jpg`, `profilaktika15.jpg`
  (withdrawn photographs, referenced by no page but **fetchable by direct URL**).
  Deliberately NOT giving `deploy-new.sh` a delete capability: that would mean building a
  tool whose worst-case failure is destructive, to solve a seven-file problem that
  happens once.

- **D4-32:** **Go/no-go after the swap is a full automated sweep, with rollback on any
  failure.** Re-run the existing probes against the real domain: all 19 pages `200
  warn=0`, all four retirement redirects 301 → 200 **in one hop, following the
  redirect**, all four host variants landing on the canonical, the staging `noindex`
  header gone, favicon and the Search Console verification file intact. Any failure and
  the files move back. A homepage spot-check was explicitly rejected — it would not have
  caught the redirect defect that actually happened on this project.

### Performance and caching

- **D4-33:** **Add WebP alongside the JPEGs** via `<picture>`, keeping the JPEG fallback.
  Measured baseline: **1.3 MB across 43 files, largest 124 KB, zero WebP**. Phase 3
  already did the heavy lifting, so this is polish on a decent baseline — worth recording
  so nobody plans a rescue mission for a problem that no longer exists.

- **D4-34:** **Long cache lifetimes for assets, short for HTML.** CSS and JS go to a year,
  safely, **because every asset URL already carries a `?v=<filemtime>` stamp** that
  changes when the file does. HTML stays at minutes. The current 5-minute values exist
  only because `/new/` was a staging preview being reviewed daily, and `.htaccess` says
  so explicitly.

### Claude's Discretion

The user chose to delegate these rather than discuss them. Decide at planning, and record
the calls made:

- **Spam protection beyond the honeypot** — rate limiting, and whether a CAPTCHA is worth
  it. Note that most CAPTCHAs (reCAPTCHA in particular) reintroduce exactly the
  third-party-cookie problem D4-18 was chosen to avoid, which argues for honeypot +
  timing + rate limiting instead.
- **Privacy note and `uslovia.html` content** on data and device handling. The owner's
  direction was "check the competition and decide; skip what can safely be skipped".
  D4-07 makes this much shorter than it would otherwise be — there is no retention period
  to state because nothing is retained. **Flag the final wording for owner approval
  before launch** — it becomes a public commitment.
- **`sitemap.xml` generation** — static file versus PHP-generated — and **when it is
  submitted to Search Console** relative to the swap.
- **Telegram bot token handling** — it is a secret and follows the established pattern
  for `filezilla-server-data.xml`: gitignored, never committed, never printed.

### Folded Todos

- **`strip-staging-noindex-at-cutover.md`** (`severity: blocker-at-cutover`) — folded into
  scope. The `X-Robots-Tag "noindex, nofollow"` block in `src/.htaccess` keeps `/new/`
  out of Google's index and **must not survive promotion to root**. Already covered by
  D4-32's sweep, but it is a checklist item in its own right: the code is correct today,
  the risk is purely in carrying the file across.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Owner decisions and requirements
- `.planning/OWNER_ANSWERS.md` — the owner's actual answers. Load-bearing for this phase:
  «Should the contact form exist at all?» (form kept; name/phone/email/device
  model/fault description/photos), «Is there a cPanel or hosting control-panel login?»
  (credentials exist, owner expects to be guided), «Working hours» (8:00–16:00, wants a
  file he can edit), «The holiday banner» (default off, file-controlled, auto-hiding),
  «Google Search Console access» (no existing account; new one available on the Google
  Business account; Google Analytics requested), «Viber — the account still needs
  provisioning» (**answer: the Viber button goes away in favour of the contact form**).
- `.planning/REQUIREMENTS.md` — CONTACT-01/03/04/05/06, OWNER-01/02, ANALYTICS-01,
  DESIGN-02, SEO-03, MIGR-02. CONTACT-02 is RETIRED.
- `.planning/ROADMAP.md` §"Phase 4: Hardening & Cutover" — success criteria, plus the
  four carry-over warnings from Phase 3.5 in the blockquote.
- `.planning/PROJECT.md` — core value, constraints, Key Decisions table.

### Cutover and URL continuity
- `.planning/phases/01-migration-safety-net-foundation/01-URL-INVENTORY.md` — the 16-page
  inventory the cutover sweep checks against.
- `src/.htaccess` — **read in full before touching it.** Carries the two-edit cutover
  warning, the `RewriteBase` defect history, the four retirement rules, the staging
  `noindex` block, and the handler line.
- `.planning/STATE.md` §"Blockers/Concerns" — the three Phase 3.5 cutover entries
  (stale files, the two-edit `.htaccess`, the PHP-version/Composer question).
- `.planning/todos/pending/strip-staging-noindex-at-cutover.md` — folded into scope.

### Code the phase modifies
- `src/includes/site-config.php` — contact values, hours (currently `[ASSUMED]`), the
  `viber` key and its long failure history, warranty keys.
- `src/includes/jsonld.php` — structured data; **carries a second hard-coded copy of the
  opening hours** (D4-26) and the `telephone` property.
- `src/includes/footer.php`, `src/includes/category-page.php`, `src/index.html` — the four
  Viber CTA slots (D4-17).
- `src/msg.html` — the post-submit confirmation page. Its own header comment says Phase 4
  owns the form and that this page must change with it.
- `site-current/mailer.php` — the legacy handler being replaced. Reference for what NOT to
  do: no honeypot, ignores `mail()`'s return value, builds `From:` from user input.
- `scripts/deploy-new.sh` — uploads only, never deletes (D4-31).

### Verification tooling to reuse
- `scripts/render-check.sh`, `scripts/probes/`, `scripts/seo-metadata-check.js`,
  `scripts/asset-version-check.sh` — the existing probe set the cutover sweep (D4-32)
  re-runs against the real domain.
- `.planning/phases/03.5-content-truth-revision/03.5-TRUTH-AUDIT.md` — the live-measurement
  format the sweep should follow, and F1/F2's transferable lesson: **a tree-wide sweep
  must be owned by someone, or unowned files are invisible to every plan.**

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `src/includes/site-config.php` — the established single-source pattern, with provenance
  comments per value. The settings file (D4-23) feeds this rather than replacing it.
- `src/includes/header.php` / `footer.php` — shared layout; the holiday banner (D4-27) and
  the analytics snippet (D4-18) each belong in exactly one of these, once.
- `src/includes/icons.php` (`torin_icon()`) — inline SVG icons; the CTA swap (D4-17) needs
  an icon for the new «Изпратете запитване» button.
- `scripts/probes/` and `scripts/render-check.sh` — a working live-measurement harness,
  already proven across 19 pages. The cutover sweep extends it rather than starting over.
- `src/includes/asset-version.php` (`torin_asset_url()`) — the `?v=<filemtime>` stamp that
  makes D4-34's one-year asset caching safe.

### Established Patterns
- **PHP 5.2-safe dialect throughout** — no closures, no namespaces, no short arrays. Keep
  writing in it even after D4-01: it costs nothing and keeps the tree internally
  consistent.
- **`torin_esc()` / `htmlspecialchars` on every output** — 40+ call sites. The form's
  redisplay-on-error path must not break this.
- **Provenance comments on every config value**, naming where it came from and which open
  question closes it. New settings follow suit.
- **`[ASSUMED]` markers** for owner-unconfirmed values. D4-25 closes the hours marker;
  do not drop any other marker without its answer.
- **Secrets live outside git** — `filezilla-server-data.xml` is gitignored and decoded only
  inside a short-lived process, never placed in a shell variable or a command line. The
  Telegram token and any SMTP password follow the same pattern.

### Integration Points
- **The nav's «Контакти» item** currently targets `index.html#contact-us` and must be
  repointed at the new contact page (D4-16).
- **All four Viber CTA slots** (D4-17) — three in `index.html`, one each in `footer.php`
  and `category-page.php`.
- **`jsonld.php`** gains the holiday-closure period (D4-27) and loses its duplicate hours
  literal (D4-26).
- **`src/.htaccess`** is touched by D4-02 (handler), D4-30 (two edits), D4-32 (noindex
  removal) and D4-34 (cache lifetimes) — four separate concerns in one file, and the file
  that can take the whole site down.

</code_context>

<specifics>
## Specific Ideas

- The user's framing that drove D4-05/D4-07: **"is there a way to receive 100% of the
  client requests even if it would mean not using email"**. The answer landed on multiple
  independent notification channels rather than durable storage — storage was offered and
  declined in favour of a simpler privacy position.
- **Telegram carrying the photos inline** was the detail that sold it: the owner sees the
  cracked screen on his phone and calls back with a price, without opening a computer.
- **"Not into spending any extra money on additional tools"** — a hard constraint. Every
  tool in this phase must be free, and free-tier terms must be verified rather than
  assumed (D4-19).
- **"Prefer to avoid doing things that might be punishable"** — this is what reframed
  analytics from "how do we do consent properly" to "how do we avoid needing consent at
  all" (D4-18).
- On the weekend hours: *"if not mentioned at least to me it sounds obvious that it is
  closed if it is not mentioned"* — which turned out to be exactly how schema.org
  `openingHours` behaves (D4-25).

</specifics>

<deferred>
## Deferred Ideas

- **Viber/WhatsApp as a notification channel** — considered and set aside. Viber bots need
  an admin-panel account plus a public webhook, and a user must subscribe to the bot
  before it can message them; business messaging is paid and partner-gated. WhatsApp Cloud
  API needs business verification and template approval. **Both should be re-checked
  against current docs** rather than ruled out from memory, given Viber's dominance in
  Bulgaria.
- **SMS notification** (Twilio or a Bulgarian provider) — the only option needing no app
  install, but ~€0.04–0.07 per message conflicts with the no-spend constraint.
- **A password-protected admin page** for hours and the holiday banner — rejected for this
  phase as login, sessions and CSRF protection on a site with no authentication anywhere.
  A candidate future phase if file editing proves too much.
- **A server-side enquiry archive** — declined in D4-07. If "we never got your enquiry"
  ever happens in practice, this is the fix, and it brings a retention obligation with it.
- **PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01** — v2, deferred at
  requirements definition (2026-08-04). Unchanged.

### Reviewed Todos (not folded)
- **`verify-viber-button-before-launch.md`** (`resolves_phase: 4`,
  `severity: blocker-at-cutover`) — **OBSOLETE, not deferred.** It asks someone to confirm
  the Viber button opens a conversation on a real handset. D4-17 removes the button
  entirely, so there is nothing left to verify. **Close this todo during the phase rather
  than carrying it to cutover as a phantom blocker.**
- **`redraw-category-icons.md`** (`resolves_phase: 3`) — out of scope. A Phase 3 content
  and design item awaiting workshop photographs from the owner; nothing in Phase 4 touches
  the category icons.

</deferred>

---

*Phase: 4-Hardening & Cutover*
*Context gathered: 2026-09-17*
