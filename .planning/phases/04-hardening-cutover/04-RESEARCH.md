# Phase 4: Hardening & Cutover — Research

**Researched:** 2026-09-17
**Domain:** PHP contact-form hardening on CloudLinux/FastCGI shared hosting · notification delivery (Telegram + SMTP) · cookieless analytics · owner-editable config · Apache canonicalisation & static-site cutover
**Confidence:** HIGH on host mechanics, mail authentication and cutover rules (live-measured this session) · MEDIUM on legal/consent framing · LOW on two host-side unknowns that need a server-side probe

<!-- Every claim below carries a provenance tag. [VERIFIED: …] means confirmed by a tool run
     THIS SESSION against an authoritative source. [CITED: url] means read from official docs.
     [ASSUMED] means training knowledge, not verified — see the Assumptions Log. -->

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

**PHP runtime and host**

- **D4-01:** **Upgrade PHP off 5.2.17 to the newest stable version the control panel offers**, and do it FIRST — before any form code is written. Rationale is recorded because it was argued explicitly: the code needs **zero changes** (a tree-wide grep for every construct PHP 7 removed — `ereg`, `split`, `mysql_*`, `each()`, `create_function`, magic quotes, `$HTTP_*_VARS` — returns **nothing**; the codebase was deliberately written 5.2-safe, which is a strict subset of what 8.x runs). Building the contact form on 5.2 would mean hand-writing compensating controls for `max_input_vars` and `max_file_uploads` — the two guards protecting a public POST endpoint, both introduced in **5.3.9** and absent from 5.2 — and then having them be dead weight after a later upgrade. — **Reversibility:** costly — reverting means switching the panel back AND restoring the matching `AddHandler` name, then re-verifying all 19 pages. Not hard, but never a one-liner.
- **D4-02:** **The `AddHandler` line is the entire risk of D4-01, and it is tested on `/new/` first.** Every one of the 19 pages executes only because `src/.htaccess` says `AddHandler application/x-httpd-php52 .html .htm`. Alt-PHP handler names are version-pinned and vary by host (`x-httpd-php74`, sometimes `x-httpd-alt-php74___lsphp`). A mismatch serves raw PHP source or 500 on every page at once. `/new/` has its own `.htaccess`, so it is switched and verified there while root is untouched.
- **D4-03:** **An account-wide version switch cannot break the current live site** — verified, not assumed. The live root `.htaccess` contains only a cPanel-generated handler block ("inherits the PHP package"), **no live `.html` file contains any PHP**, and the only PHP at root is `mailer.php`, which is forward-compatible. This was the expected blocker and it is not one.
- **D4-04:** **Watch for cPanel rewriting the handler block at cutover.** cPanel writes its own handler block into the root `.htaccess` when the PHP version changes. At cutover our hand-written `AddHandler` and that auto-generated block will coexist in the same file and may conflict.

**Contact form — notification and delivery**

- **D4-05:** **Telegram bot is the primary notification channel; email is the backup.** A single HTTPS POST to `api.telegram.org`, free, no deliverability concept — it reaches the device or Telegram retries. `sendMediaGroup` puts the damage photos directly into the owner's phone notification, so he can see a cracked screen and call back with a price without opening a computer. Group-capable, so more than one person can receive enquiries without sharing a mailbox. — **Reversibility:** reversible — the notification layer is one module behind one interface; adding or dropping a channel does not touch the form.
- **D4-06:** **Outbound HTTPS from PHP on `bell.host.bg` is UNVERIFIED and must be probed before D4-05 is committed to.** Budget shared hosts sometimes block outbound connections or disable cURL/`allow_url_fopen`. This is the same class of unknown as the Phase 1 `AddHandler` discovery — it needs a live probe, not an assumption, and it is sequenced early, alongside the PHP upgrade.
- **D4-07:** **No server-side storage of submissions.** Notifications only. The consequence was stated plainly and accepted: if Telegram AND email both fail, the enquiry is genuinely lost — the visitor is told so and given the phone number, so it is not *silent*, but recovery depends on them choosing to call. What this buys is a much shorter privacy note and **no data-retention obligation** in `uslovia.html`. Photos still transit PHP's temp directory; they are deleted after sending, never kept.
- **D4-08:** **A submission is successful if ANY one channel succeeds.** Telegram up and email down is not a failure and must not be reported as one.
- **D4-09:** **Confirmation email to the customer.** Gives them proof it arrived, and a mistyped address bounces immediately rather than after a lost week.
- **D4-10:** **If every notification path fails: honest error plus the shop's phone number.** The form handles its own errors rather than blindly redirecting to `msg.html` the way the legacy mailer does — that script ignores `mail()`'s return value entirely.
- **D4-11:** **Transport for the email leg: to be chosen at planning** between authenticated SMTP via the host's own mailbox (`office@torin.bg` already exists here; gives SPF alignment and keeps visitor data on the shop's infrastructure) and a transactional relay. On the upgraded PHP the TLS certificate is actually verified — peer verification only became a default in 5.6, so this option did not properly exist before D4-01.

**Contact form — photo uploads**

- **D4-12:** **Photos are optional.** Plenty of faults have nothing to photograph — "it's slow", "it won't boot" — and requiring one would turn those visitors away at the last step.
- **D4-13:** **Up to 5 photos, 10 MB each.** Enough for the damage, the model label and the port. Assumes `post_max_size` and `upload_max_filesize` can be raised to match — to be confirmed in the control panel.
- **D4-14:** **Shrink in the browser before upload** (~1600px), so a 9 MB phone photo becomes under 1 MB and nobody meets the limit. **A no-JS fallback that simply enforces the server limit is required** — the site currently ships almost no JavaScript and this adds some.
- **D4-15:** **Uploads are content-verified, re-encoded through GD, and given random filenames.** Never trust the extension or the client-supplied MIME type. This matters more here than on a normal host: **this server executes `.html` as PHP**, so any file that survives intact in a web-reachable folder is a code-execution risk. — **Reversibility:** reversible — but weakening it later would be a security regression, not a preference change.

**Contact form — placement and CTA**

- **D4-16:** **A dedicated contact page.** There is no contact page today — the nav's «Контакти» item points at `index.html#contact-us`, a homepage section. The new page carries the form, the phone numbers, the address and the map link together, gives every CTA a specific destination, and can rank for «контакти торин». A new URL is safe: new URLs do not threaten existing ones. — **Reversibility:** costly — once indexed, removing it requires a 301, and the nav and every CTA slot point at it.
- **D4-17:** **«Изпратете запитване», linking to the contact page, replaces the Viber button in all four CTA slots** — `src/index.html` (3 occurrences), `src/includes/footer.php`, `src/includes/category-page.php`. This preserves the call-first / write-second pairing the design is built around and needs no layout change. **The Viber button still ships in the tree today** even though CONTACT-02 was retired on 2026-09-11; removing it is Phase 4 work, not a done deal.

**Analytics**

- **D4-18:** **Umami Cloud free tier**, not GA4. The owner asked for Google Analytics and this is a deliberate departure that **needs his sign-off**. The reasoning: the ePrivacy obligation attaches to *storing things on the visitor's device*, not to counting visits. GA4 always sets cookies, so it always needs a consent banner — which becomes the first thing a visitor sees on a site whose entire premise is getting them to their problem fast, costs ~50 KB of third-party JS against DESIGN-02, and under-counts badly once people decline. A cookieless tool needs no banner, so **there is nothing to be punished for**. Umami is free, supports custom events (required for `tel:` tracking), and has a dashboard a non-analyst can read. The owner's "no extra spend" constraint ruled out Plausible/Fathom. — **Reversibility:** reversible — one script tag and an event-name map.
- **D4-19:** **Verify Umami's current free-tier limits against live docs before committing.** Free-tier terms shift; this is a researcher task, not a recollection.
- **D4-20:** **If Umami's events cannot track button clicks well, add our own beacon for the clicks** and keep Umami for pages. Stays banner-free and still answers everything. Do NOT fall back to GA4 plus a banner.
- **D4-21:** **Tracked events: call-button clicks, service-page visits, contact-form submissions, and form drop-off** (where people abandon — this is what will reveal whether the photo upload or the consent checkbox is losing them). Call clicks cover every `tel:` link: hero, repeated CTA block, sticky bar, footer, category pages.
- **D4-22:** **Microsoft Clarity is explicitly ruled out** despite being free and unlimited — it records sessions and sets cookies, making it the most consent-hungry option available. **Matomo self-hosted is ruled out** despite being free and PHP+MySQL-compatible with this host: it is a full application needing its own security patching, the same ongoing burden the project rejected when it rejected WordPress.

**Owner-editable settings**

- **D4-23:** **A plain-text settings file, never raw PHP.** One setting per line (`hours: 8:00-16:00`, `vacation_to: 2026-08-20`). The driving risk: hours currently live in `src/includes/site-config.php` as a PHP array, and a stray quote from a non-technical editor **blanks all 19 pages** — and he would have no way to tell what he did. **Malformed input must fall back to the last known-good values, never white-screen.** `site-config.php` stays the single source underneath; the text file feeds it.
- **D4-24:** **He edits it through cPanel File Manager**, using the control-panel login he now has — nothing to install, works from any machine. **A short guide in Bulgarian is part of the deliverable**, not an afterthought.
- **D4-25:** **Hours: Mon–Fri 8:00–16:00, no lunch break, closed Saturday and Sunday — and the weekend closure is stated in neither place.** Confirmed by the owner. In structured data a day that is not listed **is** closed, so publishing `Mo-Fr 08:00-16:00` and nothing else tells Google the weekend is closed without a word of it appearing on the page. This matches the user's own instinct that unstated means closed. Removes the `[ASSUMED]` marker from `site-config.php`.
- **D4-26:** **The hours value must stop being duplicated.** It is currently written twice — `site-config.php` and hard-coded again in `src/includes/jsonld.php`'s opening hours. Editing the settings file must change both, or the owner will silently tell Google something different from what the footer says.
- **D4-27:** **Holiday banner: a top strip on every page, auto-expiring, not dismissible.** He sets start date, end date and message; it appears site-wide and disappears by itself once the end date passes — auto-hiding was an explicit owner requirement, not just an off switch. Not dismissible because a closure is precisely what a visitor must not miss. **It also marks the shop closed in the structured data for that period**, so Google does not show the shop as open while it is shut. Timezone is Europe/Sofia.

**Cutover**

- **D4-28:** **Server-side move, both directions.** Move the current root files into `public_html/old/`, then move `public_html/new/*` up to root. These are server-side renames: no re-upload, a swap window measured in seconds, and rollback is the same move in reverse. The old site stays on disk as a live safety net. — **Reversibility:** reversible — and this is the entire point of choosing it.
- **D4-29:** **`https://torin.bg` (no www) is canonical.** The other three protocol/host variants 301 into it. Today **all four variants return 200 with no redirect at all**, and Search Console's ranking variant is `http://www.torin.bg/` — the least preferred form — while `site-config.php` already declares `https://torin.bg/`. SEO-04 covered paths; **nothing has ever covered the host**, which is how this went unseen for three phases. Choosing non-www means nothing in the build changes. — **Reversibility:** **one-way** — once the 301s are published and Google consolidates onto the canonical host, switching to www means a second consolidation across the whole site and real ranking disturbance. This is not a preference that can be revisited cheaply after launch.
- **D4-30:** **The `.htaccess` promotion needs TWO edits, not one** — the canonicalisation target (`https://torin.bg/new/$1` → `https://torin.bg/$1`) AND the `RewriteBase` (`/new/` → `/`). Missing either breaks a redirect **silently, at 301, with a `Location` header present**. This is not hypothetical: on the first deploy of the retirement rules, a relative substitution with no base made Apache build the `Location` from the filesystem path, and all four 301'd to a 404. **A check that greps only `^HTTP/` and `^location:` PASSES that defect** — only following the redirect exposes it.
- **D4-31:** **The seven stale files are deleted manually via FileZilla before the swap**, written into the cutover checklist. `deploy-new.sh` uploads and never deletes, and no script in this project can delete a remote file. The files: `covid.html`, `laptopi.html`, `rezervni-chasti.html`, `za-bateriite.html` (source-deleted, unreachable behind 301s) and `profilaktika17.jpg`, `profilaktika7.jpg`, `profilaktika15.jpg` (withdrawn photographs, referenced by no page but **fetchable by direct URL**). Deliberately NOT giving `deploy-new.sh` a delete capability: that would mean building a tool whose worst-case failure is destructive, to solve a seven-file problem that happens once.
- **D4-32:** **Go/no-go after the swap is a full automated sweep, with rollback on any failure.** Re-run the existing probes against the real domain: all 19 pages `200 warn=0`, all four retirement redirects 301 → 200 **in one hop, following the redirect**, all four host variants landing on the canonical, the staging `noindex` header gone, favicon and the Search Console verification file intact. Any failure and the files move back. A homepage spot-check was explicitly rejected — it would not have caught the redirect defect that actually happened on this project.

**Performance and caching**

- **D4-33:** **Add WebP alongside the JPEGs** via `<picture>`, keeping the JPEG fallback. Measured baseline: **1.3 MB across 43 files, largest 124 KB, zero WebP**. Phase 3 already did the heavy lifting, so this is polish on a decent baseline — worth recording so nobody plans a rescue mission for a problem that no longer exists.
- **D4-34:** **Long cache lifetimes for assets, short for HTML.** CSS and JS go to a year, safely, **because every asset URL already carries a `?v=<filemtime>` stamp** that changes when the file does. HTML stays at minutes. The current 5-minute values exist only because `/new/` was a staging preview being reviewed daily, and `.htaccess` says so explicitly.

### Claude's Discretion

The user chose to delegate these rather than discuss them. Decide at planning, and record the calls made:

- **Spam protection beyond the honeypot** — rate limiting, and whether a CAPTCHA is worth it. Note that most CAPTCHAs (reCAPTCHA in particular) reintroduce exactly the third-party-cookie problem D4-18 was chosen to avoid, which argues for honeypot + timing + rate limiting instead.
- **Privacy note and `uslovia.html` content** on data and device handling. The owner's direction was "check the competition and decide; skip what can safely be skipped". D4-07 makes this much shorter than it would otherwise be — there is no retention period to state because nothing is retained. **Flag the final wording for owner approval before launch** — it becomes a public commitment.
- **`sitemap.xml` generation** — static file versus PHP-generated — and **when it is submitted to Search Console** relative to the swap.
- **Telegram bot token handling** — it is a secret and follows the established pattern for `filezilla-server-data.xml`: gitignored, never committed, never printed.

### Deferred Ideas (OUT OF SCOPE)

- **Viber/WhatsApp as a notification channel** — considered and set aside. Viber bots need an admin-panel account plus a public webhook, and a user must subscribe to the bot before it can message them; business messaging is paid and partner-gated. WhatsApp Cloud API needs business verification and template approval. **Both should be re-checked against current docs** rather than ruled out from memory, given Viber's dominance in Bulgaria.
- **SMS notification** (Twilio or a Bulgarian provider) — the only option needing no app install, but ~€0.04–0.07 per message conflicts with the no-spend constraint.
- **A password-protected admin page** for hours and the holiday banner — rejected for this phase as login, sessions and CSRF protection on a site with no authentication anywhere. A candidate future phase if file editing proves too much.
- **A server-side enquiry archive** — declined in D4-07. If "we never got your enquiry" ever happens in practice, this is the fix, and it brings a retention obligation with it.
- **PRICE-01, GALLERY-01, TURNAROUND-01, REVIEWS-01, BLOG-01** — v2, deferred at requirements definition (2026-08-04). Unchanged.

**Reviewed Todos (not folded)**

- **`verify-viber-button-before-launch.md`** (`resolves_phase: 4`, `severity: blocker-at-cutover`) — **OBSOLETE, not deferred.** D4-17 removes the button entirely. **Close this todo during the phase rather than carrying it to cutover as a phantom blocker.**
- **`redraw-category-icons.md`** (`resolves_phase: 3`) — out of scope.

### Folded Todos

- **`strip-staging-noindex-at-cutover.md`** (`severity: blocker-at-cutover`) — folded into scope. The `X-Robots-Tag "noindex, nofollow"` block in `src/.htaccess` keeps `/new/` out of Google's index and **must not survive promotion to root**. Already covered by D4-32's sweep, but it is a checklist item in its own right.

</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| CONTACT-01 | User can tap-to-call any of the shop's phone numbers on mobile | **Already implemented and shipping.** `tel:` anchors exist at 7 call sites [VERIFIED: grep over `src/`, 2026-09-17] — `src/index.html:57,296,342`, `src/includes/category-page.php:553`, `src/includes/footer.php:50` (loop over `$site['phones']`) and `:73`. Phase 4 work is (a) the D4-21 event instrumentation on those anchors and (b) confirming them on a real handset. See Pitfall P-2 — the naïve Umami instrumentation *breaks* this requirement. |
| ~~CONTACT-02~~ | RETIRED | Five `viber://chat` anchors still ship — see the **five, not four** correction in Pitfall P-1. |
| CONTACT-03 | Hardened handler (honeypot + authenticated SMTP/PHPMailer) instead of bare `mail()` | PHPMailer 7.1.1 vendored without Composer (3 files) + SuperHosting's own SMTP: host `torin.bg`, **port 25, SMTP auth YES, encryption NO** [CITED: help.superhosting.bg/smtp-settings-in-script.html]. SPF and DKIM already aligned for `@torin.bg` [VERIFIED: dig, 2026-09-17]. See Standard Stack and Code Example C-2. |
| CONTACT-04 | Zendesk chat widget gone | **Already met by the rebuild.** Zero Zendesk/Zopim/zdassets references in `src/` [VERIFIED: `grep -rni "zendesk\|zopim\|zdassets" src/` → 0 hits, 2026-09-17]; 16 of the 17 `site-current/` pages carry it. The widget disappears the moment `/new/` is promoted. Phase 4 must *verify* this post-cutover, not build it. |
| CONTACT-05 | Enquiry carrying name, phone, email, device model, fault description and photographs | Upload pipeline: `.user.ini` vs `phpXX-fcgi.ini` (Pitfall P-5), `finfo` + `getimagesize()` + GD re-encode + EXIF-orientation fix (Code Example C-3), Telegram `sendMediaGroup` 2–10 items / 10 MB per photo (Pitfall P-4). |
| CONTACT-06 | Privacy note, explicit consent, terms page states data and device handling | `src/uslovia.html:45-70` carries four blocks today; the retention sentence at `:67` must not be contradicted. D4-07 makes it short. **Umami must be disclosed there even though it needs no consent banner** — see Pitfall P-8. |
| OWNER-01 | Owner changes working hours by editing one file | Text-settings parser with last-known-good fallback (Code Example C-5). Hours live in **two** places today: `src/includes/site-config.php:62` and `src/includes/jsonld.php:64-71`. |
| OWNER-02 | Holiday banner from one file, off by default, auto-hiding | Google's own closed-for-a-period JSON-LD shape (Code Example C-6) + `date_default_timezone_set('Europe/Sofia')`. |
| ANALYTICS-01 | Contact actions and service-page visits observable | Umami Cloud Hobby: **100K events/mo, 1 website, 6 months retention** [VERIFIED: umami.is/pricing rendered in headless Brave, 2026-09-17]. Tracker is 4,757 B raw / 2,333 B gzipped and uses `fetch(..., {keepalive:true})` [VERIFIED: read of cloud.umami.is/script.js, 2026-09-17]. |
| DESIGN-02 | Fast mobile loads, images optimised, no jQuery/ScrollMagic/pagePiling | jQuery stack is already absent from `src/`. WebP measured at **65% of JPEG bytes saved** at q=80 (Code Example C-8). Cache lifetimes at `src/.htaccess:146-148`. |
| SEO-03 | `robots.txt` and `sitemap.xml` exist and are submitted to Search Console | **Neither exists on the live site today** — `https://torin.bg/robots.txt` → **404**, `https://torin.bg/sitemap.xml` → **404** [VERIFIED: curl, 2026-09-17]. MIGR-02 calls `robots.txt` "must-carry"; there is nothing to carry. It must be *created*. |
| MIGR-02 | Must-carry checklist preserved through cutover | Live status of every item measured this session — see the Runtime State Inventory table. Two items in the checklist are wrong as written (`robots.txt` does not exist; the GSC verification file verifies an account nobody can log into). |

</phase_requirements>

## Project Constraints (from CLAUDE.md)

`./.claude/CLAUDE.md` is the project instruction file (`claude_md_path: "./.claude/CLAUDE.md"` in `.planning/config.json`). Actionable directives the planner must honour:

| Directive | Source | Consequence for Phase 4 |
|-----------|--------|-------------------------|
| **All content in Bulgarian** — no other language versions in scope | Constraints | Form labels, validation messages, error copy, consent text, the holiday banner, the owner's settings-file guide (D4-24) and every `msg.html` string are Bulgarian. English exists only in code comments. |
| **Must deploy to the existing FTP/shared hosting at `bell.host.bg`** — no infrastructure migration | Constraints | Rules out any hosted form backend that needs a server runtime, and any notification path that needs a persistent process. |
| **Reuse the existing PHP** for contact-form handling rather than adding a third-party form SaaS | Recommended Stack → PHP row | Confirms the `mailer.php` replacement stays on-host. Formspree/Web3Forms explicitly kept "in your back pocket only as a Plan B". |
| **PHPMailer 6.x with authenticated SMTP, plus a honeypot** | Supporting Libraries + What NOT to Use | Directly prescribes the CONTACT-03 stack. Research updates the version — see "State of the Art". |
| **Raw PHP `mail()` is forbidden** | What NOT to Use | The legacy `site-current/mailer.php:86` is exactly this, and is live today. |
| **jQuery / jQuery UI / ScrollMagic / pagePiling forbidden** | What NOT to Use | The browser-side image downscale (D4-14) must be dependency-free vanilla JS. Alpine.js is *permitted* by CLAUDE.md but is not needed for ~40 lines of canvas code, and adding it would cost DESIGN-02 bytes for one widget. |
| **No copy-paste duplication of shared elements** | What NOT to Use, last row | The banner, the analytics tag and the new CTA go in `header.php`/`footer.php` **once**. |
| **`filezilla-server-data.xml` pattern for secrets** — gitignored, decoded inside a short-lived process, never in a shell variable or command line | Development Tools + `scripts/deploy-new.sh:10-17` | The Telegram bot token and the SMTP password follow this exact pattern. |
| **FTPS, never plain FTP** | Development Tools | `deploy-new.sh` already does this, capped at TLS 1.2 per the Phase 2 finding. |
| **JSON-LD `LocalBusiness`/`ElectronicsStore` schema, hand-authored, in the shared layout** | Supporting Libraries | Already in `src/includes/jsonld.php`; D4-26/D4-27 extend it, they do not replace it. |

**One project convention that Phase 4 must consciously break, and the planner must say so out loud:** the tree is written in a **PHP 5.2-safe dialect** (no closures, no namespaces, no short arrays) and CONTEXT's `code_context` says to keep writing in it after D4-01. **PHPMailer cannot be used from 5.2-safe code** — it is namespaced (`PHPMailer\PHPMailer\PHPMailer`), which requires a PHP 5.3+ *parser*, and its own `composer.json` declares `"php": ">=5.5.0"` [VERIFIED: raw.githubusercontent.com/PHPMailer/PHPMailer/v7.1.1/composer.json, 2026-09-17]. See Pattern A-3 for the containment strategy.

## Summary

Three things this session measured changed the shape of the phase.

**First, the PHP upgrade is not the risk CONTEXT thinks it is — it is a different risk.** The host is SuperHosting.BG (the `*.superhosting.bg` cert lineage from Phase 1; `torin.bg` and `bell.host.bg` both resolve to `217.174.156.170` [VERIFIED: dig, 2026-09-17]). SuperHosting does **not** change a directory's PHP version with an `AddHandler application/x-httpd-phpXX` line. It uses `mod_fcgid` + `FcgidWrapper`, and its own documentation says: *"If there is an already existing .htaccess file that contains a line for PHP version setup such as `AddHandler x-httpd-phpXX .php`, you will have to remove it."* [CITED: help.superhosting.bg/en/set-php-version-for-directory.html]. That line — `src/.htaccess:86` — is the only reason all 19 pages execute at all, and the generated wrapper maps **`.php` only**. So D4-01 is not "pick a new handler name"; it is "replace one mechanism with another that does not natively cover `.html`". D4-02's instinct to test on `/new/` first is correct and now has a specific failure to test for.

**Second, the mail question is already answered by DNS, and the answer eliminates most of D4-11's option space.** `torin.bg` publishes an SPF record that authorises the web server itself (`+a`, and `+ip4:217.174.156.170`, which is `torin.bg`'s own A record) and a valid `default._domainkey.torin.bg` DKIM key [VERIFIED: dig TXT, 2026-09-17]. Mail leaving this server as `office@torin.bg` is therefore already SPF-aligned and DKIM-signed. Meanwhile SuperHosting **blocks outbound ports 25, 26 and 465 to external servers** on shared hosting [CITED: help.superhosting.bg/smtp-settings-in-script.html], which kills every SMTP-based transactional relay. The email leg goes through the host's own server on **port 25, authenticated, unencrypted** — which is what their documentation prescribes and what the existing DNS already blesses. Note the corollary: D4-11's stated reason for needing the PHP upgrade (TLS peer verification, a 5.6 default) does not apply on this path.

**Third, the obvious way to instrument a `tel:` link with Umami actively breaks the site's primary call to action.** Reading the shipped tracker source shows that for any `<a>` carrying `data-umami-event`, Umami calls `e.preventDefault()`, fires the beacon, and only then re-navigates with `location.href = href` inside a `.finally()` [VERIFIED: read of `cloud.umami.is/script.js`, 2026-09-17]. On a `tel:` link that means the dialer opens **after** a network round-trip to a third party, outside the user-gesture task — the exact class of thing mobile browsers block. D4-20 asked whether a custom beacon is needed; the answer is *no new beacon*, but **yes, the declarative attribute must be avoided on `tel:` anchors** and replaced with a passive listener calling `umami.track()`. Fifteen lines, no preventDefault, native dialing untouched, and it degrades to plain `tel:` with no JS.

Everything else is smaller. Umami Hobby is confirmed at 100K events/month, **one** website (not three, as secondary sources claim) and 6-month retention, tracker 2,333 B gzipped. WebP at q=80 measures a 65% byte reduction on this photo set. `robots.txt` and `sitemap.xml` both 404 today, so SEO-03 is creation work, not migration work. And the cutover has two hazards CONTEXT did not name: `public_html/old/` would be publicly reachable and indexable, and moving the root wholesale would take `.well-known/` with it — the ACME path for a Let's Encrypt cert that expires **2026-11-11** [VERIFIED: `openssl s_client`, 2026-09-17].

**Primary recommendation:** sequence the phase as *probe → PHP → notify → form → analytics/settings → performance → cutover*, and make the very first plan a single throwaway server-side probe file that answers, in one request, every host unknown at once (handler mechanism, PHP version list, GD/curl/exif presence, outbound 443 reachability, sendmail path, upload limits). Every downstream decision in this phase is gated on facts only that probe can produce, and the project has already paid twice for assuming host behaviour instead of measuring it.

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Tap-to-call (`tel:`) | Browser / Client | — | Pure `href`; the OS handles the intent. No server involvement, works with JS disabled. |
| Call-click tracking | Browser / Client | Third-party (Umami) | A click is only observable in the browser. Must be *additive* to the anchor, never in its activation path (P-2). |
| Page-view tracking | Browser / Client | Third-party (Umami) | One script tag in `header.php`. |
| Contact-form rendering | Frontend Server (PHP include) | — | Same PHP-include layout as the other 19 pages; must stay 5.2-dialect-compatible so it renders even if the mailer module is unavailable. |
| Browser-side image downscale | Browser / Client | — | Saves upload bytes before they leave the device. **Optimisation only** — never the enforcement point (P-6). |
| Form validation | API / Backend (PHP handler) | Browser (hints only) | Client validation is UX; the server is the boundary. |
| Spam defence (honeypot, time-trap, rate limit) | API / Backend | — | Any client-side check is trivially bypassed. No third-party CAPTCHA (D4-18 rationale applies). |
| Upload content verification + GD re-encode | API / Backend | — | Cannot be delegated. Elevated stakes: this server maps `.html` → PHP. |
| Notification fan-out (Telegram, SMTP) | API / Backend | External (api.telegram.org, local MTA) | D4-08's any-one-succeeds rule lives behind one interface. |
| Working hours / holiday banner data | Frontend Server (PHP, file-backed) | — | Read at request time from a text file; no build step, no database, editable via cPanel File Manager. |
| Structured-data hours and closure | Frontend Server (`jsonld.php`) | — | Must read the **same** parsed value as the footer (D4-26). |
| Host/protocol canonicalisation | CDN / Static (Apache `.htaccess`) | — | Must happen before PHP runs, in one hop, with a hardcoded literal target. |
| Cache lifetimes | CDN / Static (Apache `mod_expires`) | Frontend Server (`?v=` stamp) | Two independent mechanisms; D4-34's one-year asset TTL is only safe because the stamp exists. |
| `sitemap.xml` / `robots.txt` | CDN / Static | Dev tooling (generator script) | Static files; a generator script keeps them honest without adding a runtime dependency. |
| Cutover file movement | Host control panel (File Manager) | — | Server-side rename. No tier of the application participates. |

## Standard Stack

### Core

| Library / Mechanism | Version | Purpose | Why Standard |
|---------------------|---------|---------|--------------|
| **PHPMailer** | **7.1.1** (released 2026-05-18) [VERIFIED: api.github.com releases, 2026-09-17] | SMTP send with authentication, MIME, attachments | The canonical PHP mail library: 22,301 GitHub stars, created 2011, last pushed 2026-09-10, 114.7M Packagist downloads [VERIFIED: GitHub + Packagist APIs, 2026-09-17]. Prescribed by CLAUDE.md. Requires `php >=5.5.0`, `ext-ctype`, `ext-filter`, `ext-hash` [VERIFIED: v7.1.1 `composer.json`]. |
| **Host SMTP (SuperHosting)** | n/a | The email leg's transport | Host `torin.bg` or `<server>.superhosting.bg`, **port 25**, **SMTP auth required** (username = full address `office@torin.bg`), **encryption: none** [CITED: help.superhosting.bg/smtp-settings-in-script.html]. Outbound 25/26/465 to *external* servers are blocked, so this is effectively the only SMTP option. |
| **Telegram Bot API** | current (docs fetched 2026-09-17) | Primary notification channel (D4-05) | `sendMessage` (1–4096 chars), `sendPhoto` (≤10 MB, w+h ≤10000, ratio ≤20, caption 0–1024), `sendMediaGroup` (**2–10 items**, `attach://` multipart), multipart upload cap **10 MB for photos / 50 MB other** [VERIFIED: core.telegram.org/bots/api, 2026-09-17]. |
| **PHP `gd`** | bundled | Upload re-encode (D4-15) | Listed as supported on all SuperHosting Linux plans [CITED: help.superhosting.bg/en/php-modules.html]. `imagick` is also available if higher fidelity is ever wanted. |
| **PHP `exif`** | bundled | Read orientation before re-encode | Same source. Without it, every portrait phone photo arrives sideways (P-7). |
| **PHP `fileinfo` (`finfo`)** | bundled | Server-side MIME detection | Same source. Used *alongside* `getimagesize()`, never instead of it. |
| **PHP `curl`** | bundled | HTTPS POST to `api.telegram.org` | Same source. Module presence ≠ egress permitted — still needs the D4-06 probe. |
| **Umami Cloud — Hobby plan** | current (pricing read 2026-09-17) | Cookieless analytics (D4-18/ANALYTICS-01) | **$0/mo · up to 100K events/month · 1 website · 6-month data retention · custom events and event properties included on Hobby** [VERIFIED: umami.is/pricing rendered in headless Brave, 2026-09-17]. Tracker is 4,757 B raw / **2,333 B gzipped**, fires via `fetch(..., {keepalive:true})`, `credentials:"omit"` [VERIFIED: read of cloud.umami.is/script.js, 2026-09-17]. |
| **Apache `mod_rewrite`** | on this host | Host/protocol canonicalisation (D4-29/D4-30) | The identical rule shape already works on this host: all three non-canonical variants of `/new/index.html` 301 to `https://torin.bg/new/index.html` in exactly one hop [VERIFIED: curl, 2026-09-17]. |
| **`cwebp` (libwebp)** | **1.5.0**, present on the dev machine [VERIFIED: `cwebp -version`, 2026-09-17] | Build-time WebP generation (D4-33) | Dev-machine only; output is static files uploaded by `deploy-new.sh`. No host dependency. |

### Supporting

| Mechanism | Purpose | When to Use |
|-----------|---------|-------------|
| `hash_hmac('sha256', …)` | Signs the form's render timestamp so the time-trap cannot be forged and needs no session/cookie | Always — it is the no-cookie alternative to `session_start()`. |
| Flat-file rate limiter keyed on `sha256(ip . secret)` | Per-IP submission throttle without a database or a cookie | Always. Store **outside** the web root. |
| `.user.ini` **or** `phpXX-fcgi.ini` | Raise `upload_max_filesize` / `post_max_size` (D4-13) | Whichever the probe shows applies. **Never `php_value` in `.htaccess`** (P-5). |
| `createImageBitmap(file, {imageOrientation:'from-image'})` + `<canvas>` | Browser-side downscale to ~1600px (D4-14) | The only reliable way to honour EXIF orientation on canvas [CITED: whatwg/html#7210 discussion; canvas `drawImage` ignores EXIF]. |
| `date_default_timezone_set('Europe/Sofia')` | Banner expiry and closure dates in the shop's own time (D4-27) | At the top of the settings loader. PHP 5.1+. |
| `scripts/render-check.sh` + `scripts/probes/` | Rendered verification of the new CTA, banner and contact page | Already proven across 19 pages; the cutover sweep extends it (D4-32). |

### Alternatives Considered

| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| Host SMTP on port 25 with auth | **Local sendmail** via PHPMailer `isSendmail()` (`/usr/sbin/sendmail`, explicitly supported [CITED: help.superhosting.bg/smtp-settings-in-script.html]) | **Strictly simpler and removes a stored secret entirely** — no password anywhere, same SPF/DKIM alignment because it is the same machine. The only reason not to make it the default: CONTACT-03's wording says *"authenticated SMTP/PHPMailer"* literally. Recommend SMTP-with-auth for requirement fidelity, and record sendmail as the fallback if the mailbox password proves awkward to hold. |
| Telegram as primary | HTTPS-API transactional relay (Brevo/Resend free tiers) | Survives if outbound 443 is blocked *only* for `api.telegram.org`, which is implausible. Adds an account, an API key and a free-tier cap — against "no extra spend". Keep as Plan C. |
| Umami Cloud | Self-hosted Umami | Free forever and unlimited, but needs Node + Postgres — impossible on this host and rejected in spirit by D4-22 (the Matomo argument applies identically). |
| PHPMailer 7.1.1 | PHPMailer 6.12.0 (latest 6.x, 2025-10-15) | CLAUDE.md says "6.x". Both declare `php >=5.5.0` and the same extensions [VERIFIED: both `composer.json` files]. 7.x is the maintained line; recommend 7.1.1 and note the CLAUDE.md deviation. |
| Static generated `sitemap.xml` | PHP-generated `sitemap.xml` | PHP generation needs a handler mapping for `.xml` — a fifth concern in the one file that can take the whole site down. Static wins; keep it honest with a check script (Pattern A-6). |
| Honeypot + time-trap + rate limit | Cloudflare Turnstile / hCaptcha | Both are third-party scripts on every form render. Turnstile is cookieless-ish but still a third-party request, and it re-opens the question D4-18 closed. Not worth it for a Bulgarian repair shop's enquiry volume. |

**Installation (no package manager is added to this project):**

```bash
# PHPMailer — vendored by hand, three files, no Composer.
# Target: src/vendor/phpmailer/  (uploaded by deploy-new.sh like any other file)
curl -sSL -o /tmp/phpmailer.tar.gz \
  https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v7.1.1.tar.gz
tar -xzf /tmp/phpmailer.tar.gz -C /tmp
mkdir -p src/vendor/phpmailer
cp /tmp/PHPMailer-7.1.1/src/PHPMailer.php \
   /tmp/PHPMailer-7.1.1/src/SMTP.php \
   /tmp/PHPMailer-7.1.1/src/Exception.php \
   src/vendor/phpmailer/
# Only those three are needed for SMTP sending. POP3.php, OAuth.php,
# OAuthTokenProvider.php and DSNConfigurator.php are not.
# [VERIFIED: api.github.com contents/src?ref=v7.1.1 — the src/ tree is exactly
#  DSNConfigurator.php, Exception.php, OAuth.php, OAuthTokenProvider.php,
#  PHPMailer.php, POP3.php, SMTP.php]

# WebP siblings for every JPEG (dev machine only, cwebp 1.5.0 already present)
find src/img -name '*.jpg' -exec sh -c \
  'cwebp -quiet -q 80 -metadata none "$1" -o "${1%.jpg}.webp"' _ {} \;

# Umami: no install. One <script> tag in src/includes/header.php.
```

**Version verification performed this session:** PHPMailer `v7.1.1` published 2026-05-18, `php >= 5.5.0`, repo not archived, LGPL-2.1 [VERIFIED: GitHub API]. `cwebp 1.5.0 / libsharpyuv 0.4.1` [VERIFIED: `cwebp -version`]. No npm, PyPI or crates package is added by this phase — the project has no `package.json` and gains none.

## Package Legitimacy Audit

This phase adds **no** npm / PyPI / crates package. It vendors one PHP library by hand and adds one remote `<script>` tag. Both were verified against authoritative sources rather than a registry lookup.

| Package | Registry | Age | Downloads | Source Repo | Verdict | Disposition |
|---------|----------|-----|-----------|-------------|---------|-------------|
| `phpmailer/phpmailer` | Packagist (vendored manually; Composer not used) | Repo created **2011-08-23**; v7.1.1 released **2026-05-18**; last push **2026-09-10** | **114,685,169** total, **3,319,863**/month | `github.com/PHPMailer/PHPMailer` (22,301 stars, LGPL-2.1, not archived) | **OK** | Approved — pin to tag `v7.1.1`, copy 3 files, commit them, record the SHA256 of each file in the plan |
| Umami tracker (`cloud.umami.is/script.js`) | not a package — a hosted script | Vendor: Umami Software, Inc. | n/a | `github.com/umami-software/umami` | **OK** | Approved — but it is a **third-party runtime dependency on every page**; see Pitfall P-2 and P-8 |

**Packages removed due to [SLOP] verdict:** none — no package was sourced from a search result or from training memory.
**Packages flagged as suspicious [SUS]:** none.

Two integrity notes the planner should turn into tasks:

1. **Vendoring PHPMailer by hand means there is no lockfile and no integrity check.** Record the SHA256 of each of the three copied files in the plan and re-assert it in a verify step, so a later "quick update" cannot silently swap the library.
2. **The Umami script is fetched at runtime from a third party on every page load.** If `cloud.umami.is` is unreachable or slow, the tag must not delay anything — load it `defer` (or `async`) and never `render-blocking`. There is no SRI possible on a script the vendor updates in place.

## Architecture Patterns

### System Architecture Diagram

```
                     ┌─────────────────────── VISITOR (mobile, Bulgarian) ────────────────────────┐
                     │                                                                            │
          tap tel:   │                 tap «Изпратете запитване»            page load             │
              │      │                          │                               │                 │
              ▼      │                          ▼                               ▼                 │
   ┌──────────────────────┐        ┌──────────────────────────┐      ┌─────────────────────┐      │
   │  OS dialer (native)  │        │  kontakti.html  (PHP)    │      │  any of 20 pages    │      │
   │  NOT intercepted     │        │  form + honeypot +       │      │  header.php/footer  │      │
   └──────────┬───────────┘        │  signed ts + consent     │      └──────────┬──────────┘      │
              │ passive listener   └───────────┬──────────────┘                 │                 │
              ▼  (no preventDefault)           │ optional: <canvas> downscale   ▼                 │
   ┌──────────────────────┐                    │ to ~1600px (JS only)  ┌──────────────────┐       │
   │ umami.track('call')  │                    │                       │ Umami pageview   │       │
   └──────────┬───────────┘                    │ multipart/form-data   └────────┬─────────┘       │
              │                                ▼                                │                 │
              └────────────────────────────────┴────────────────────────────────┘                 │
                                               │  fetch keepalive → gateway.umami.is              │
  ═════════════════════════════════════════════│══════════════════════════════════════════════════╡
   bell.host.bg (Apache + FastCGI PHP)         ▼
                                    ┌─────────────────────────────┐
                                    │ .htaccess                   │  ← canonicalise host+proto (1 hop)
                                    │  · host canonicalisation    │  ← 4 SEO-05 retirement 301s
                                    │  · RewriteBase /            │  ← cache lifetimes
                                    │  · PHP handler for .html    │  ← NO X-Robots-Tag noindex
                                    └──────────────┬──────────────┘
                                                   ▼
                                    ┌─────────────────────────────┐
                                    │ contact-send.php  (PHP 8.x) │   ← the ONE file that is not 5.2-safe
                                    └──────────────┬──────────────┘
                                                   │
             ┌──────────────┬────────────────┬─────┴──────────┬─────────────────┐
             ▼              ▼                ▼                ▼                 ▼
      ┌────────────┐ ┌────────────┐  ┌──────────────┐  ┌────────────┐  ┌────────────────┐
      │ honeypot   │ │ time-trap  │  │ rate limiter │  │ field      │  │ upload pipeline│
      │ (hidden)   │ │ HMAC ts    │  │ flat file    │  │ validation │  │ finfo →        │
      │            │ │ 3s…2h      │  │ outside root │  │ + esc      │  │ getimagesize → │
      └─────┬──────┘ └─────┬──────┘  └──────┬───────┘  └─────┬──────┘  │ exif rotate →  │
            │              │                │                │         │ GD re-encode → │
            └──────────────┴────────────────┴────────────────┘         │ random name    │
                                    │ all pass                         └────────┬───────┘
                                    ▼                                           │
                       ┌────────────────────────────┐   normalised JPEGs in     │
                       │ notify() — any-one-wins    │◄──  PHP temp dir ─────────┘
                       └──────┬──────────────┬──────┘
                              │              │
              ┌───────────────▼──┐      ┌────▼─────────────────┐
              │ Telegram         │      │ PHPMailer 7.1.1      │
              │ HTTPS POST 443   │      │ SMTP torin.bg:25     │
              │ sendMessage      │      │ auth, no encryption  │
              │ + sendPhoto (1)  │      │ → office@torin.bg    │
              │ or sendMediaGroup│      │ → customer (confirm) │
              │   (2–10 items)   │      └────┬─────────────────┘
              └───────┬──────────┘           │ SPF +a / DKIM default._domainkey
                      │                      │  (already published — verified)
                      ▼                      ▼
              owner's phone            office@torin.bg mailbox
                      │                      │
                      └──────────┬───────────┘
                                 ▼
          ┌──────────────────────────────────────────────┐
          │ ≥1 channel OK → msg.html (success)           │
          │ 0 channels OK → same page, honest error +    │
          │                 the shop's phone number      │
          │ ALWAYS: unlink() every temp file, both paths │
          └──────────────────────────────────────────────┘

  ── separate request path, no visitor involved ─────────────────────────────────
     settings.txt (cPanel File Manager)
            │ parse, validate, last-known-good fallback
            ▼
     site-config.php ──┬──► footer.php      (hours line, holiday banner)
                       ├──► header.php      (holiday banner strip, analytics tag)
                       └──► jsonld.php      (openingHoursSpecification + closure period)
```

### Recommended Project Structure

```
src/
├── kontakti.html            # NEW — the contact page (PHP; 5.2-safe dialect)
├── contact-send.php         # NEW — the POST handler. PHP 7/8 only. Isolated on purpose.
├── settings.txt             # NEW — the owner-editable file (D4-23). Plain key: value.
├── robots.txt               # NEW — static
├── sitemap.xml              # NEW — static, generated by scripts/gen-sitemap.sh
├── google<token>.html       # NEW — copy of the ACTIVE GSC verification file (see OQ-1)
├── includes/
│   ├── settings.php         # NEW — parses settings.txt, validates, falls back
│   ├── notify.php           # NEW — channel interface + Telegram + mail drivers
│   ├── upload.php           # NEW — finfo/getimagesize/exif/GD pipeline
│   ├── spam-guard.php       # NEW — honeypot, HMAC time-trap, rate limiter
│   ├── banner.php           # NEW — the holiday strip partial
│   ├── site-config.php      # MODIFIED — reads settings.php; loses 'viber'
│   ├── jsonld.php           # MODIFIED — hours from one source; closure period
│   ├── header.php           # MODIFIED — nav «Контакти» href; banner; analytics tag
│   ├── footer.php           # MODIFIED — CTA swap; hours from one source
│   └── category-page.php    # MODIFIED — CTA swap
├── vendor/phpmailer/        # NEW — 3 hand-vendored files
├── img/**/*.webp            # NEW — WebP siblings (D4-33)
└── .htaccess                # MODIFIED — 5 separate concerns; see A-5
scripts/
├── gen-sitemap.sh           # NEW
├── sitemap-check.sh         # NEW — asserts sitemap URL set == published page set
└── probes/cutover-sweep.js  # NEW — D4-32's go/no-go
```

Secrets live **outside** `src/` and are never uploaded by a blanket `deploy-new.sh` run — see Pitfall P-10.

### Pattern A-1: Notification fan-out behind one interface, any-one-wins

**What:** `torin_notify($payload, $photos)` returns `array('ok' => bool, 'channels' => array('telegram' => bool, 'mail' => bool))`. The handler branches on `ok`, never on an individual channel.
**When to use:** D4-08 makes this mandatory. It is also what makes D4-05 reversible.
**Key rule:** each driver is wrapped so an exception or a timeout **cannot** propagate. A Telegram outage must not 500 the form.

### Pattern A-2: Fail-safe settings parse with last-known-good

**What:** `settings.txt` is parsed into an array; each key is validated against a type (a `HH:MM-HH:MM` range, an ISO date, a bounded-length string). Any key that fails validation is **dropped** and the compiled-in default in `site-config.php` is used for it. The parse never throws, never `die()`s, and never emits a warning to output.
**When to use:** D4-23's absolute requirement — "never white-screen 19 pages".
**Two things the naïve version gets wrong:**
- `parse_ini_file()` is *not* the right tool. It is fussy about quoting, treats `yes`/`no`/`on`/`off`/`null` as reserved words, and emits a `Warning` on malformed input — which, on this host, would print into the top of the HTML. Hand-roll a `fgets` + first-colon-split loop; it is fifteen lines and it cannot surprise anyone.
- Per-key fallback, not whole-file fallback. A typo in `vacation_to` must not also blank the hours.

### Pattern A-3: Quarantine the non-5.2 code in exactly one file

**What:** `contact-send.php` is the only file that `require`s PHPMailer and the only file written in modern PHP. Every page file, every include that renders chrome, and `settings.php` stay in the 5.2-safe dialect.
**Why:** if D4-01 has to be rolled back (its own reversibility note says this is costly but possible), a 5.2 interpreter parsing a namespaced file is a **parse error**, not a runtime error — it fails at compile time, before any output. Confining that blast radius to one POST endpoint means a rollback breaks the form and nothing else; putting a `use` statement in `footer.php` would take all 20 pages down at once.
**Rule:** no include chain from `header.php` or `footer.php` may reach `vendor/phpmailer/`.

### Pattern A-4: Additive analytics — never in the activation path

**What:** every tracked element keeps its native behaviour. Tracking attaches through a listener that does not call `preventDefault()` and does not await anything.
**When to use:** all of D4-21, and specifically every `tel:` anchor.
**Rationale is measured, not stylistic:** see Pitfall P-2 and Code Example C-4.

### Pattern A-5: One concern per clearly-delimited block in `.htaccess`, promoted as a reviewed diff

**What:** `src/.htaccess` is touched by five separate concerns this phase — the PHP handler mechanism (D4-01/D4-02), the canonicalisation target and `RewriteBase` (D4-30), the `noindex` removal (folded todo), the cache lifetimes (D4-34), and the new `<Files>` denials for `settings.txt` / `.user.ini` / `php.fcgi`. Each gets its own commented block with a one-line statement of what breaks if it is wrong.
**Why:** this is the one file whose failure mode is *the entire site at once*, and the project has already had one silent-301 defect in it. Promotion at cutover is a **diff review against the live root file**, not a copy.

### Pattern A-6: Static artefact + a check that it matches reality

**What:** `sitemap.xml` is a static file generated by `scripts/gen-sitemap.sh` from the published-page set. `scripts/sitemap-check.sh` asserts that the URL set in `sitemap.xml` equals the set of pages that actually return 200 and are not `noindex`.
**When to use:** any time a static artefact restates information that lives somewhere else. This project's own history is the argument — every duplicated value here (hours, phone, base URL) eventually disagreed with its twin.

### Anti-Patterns to Avoid

- **`php_value` in `.htaccess` to raise upload limits.** Only mod_php honours it. On a FastCGI/LSAPI SAPI — which this host uses — it returns **HTTP 500 for the entire subtree**. Exactly the `T-02-02` failure class `src/.htaccess` already guards every other module block against.
- **`data-umami-event` on a `tel:` or `mailto:` anchor.** Umami `preventDefault()`s it. See P-2.
- **Storing the uploaded file anywhere under `public_html/`, even briefly.** This server maps `.html` → PHP. Keep every upload in PHP's temp dir, send it, `unlink()` it, and never write one into the document root — not even a "temp" subfolder with a `.htaccess` deny, because the deny file is one cPanel reset away from gone.
- **Building the redirect `Location` from `%{HTTP_HOST}`.** Already prohibited at `src/.htaccess:20-22` as a host-header-injection vector; the reason must survive the promotion edit.
- **Grepping `^HTTP/` and `^location:` to verify a redirect.** D4-30 names this precisely: that check *passes* the defect this project actually shipped. Follow the redirect and assert the final status and final URL.
- **Trusting `$_FILES['…']['type']`.** It is client-supplied. So is the extension.
- **A whole-file settings fallback.** One bad line must not revert every value.
- **Redirecting to `msg.html` regardless of outcome.** `site-current/mailer.php:86-95` calls `mail()` and then unconditionally `header("Location: msg.html")`, discarding the return value — D4-10 exists to not repeat this.

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Composing and sending a MIME email with attachments and correct headers | A hand-built `mail()` call with `\r\n`-joined headers | PHPMailer 7.1.1 | Header injection via a newline in a user-supplied address is the single most common PHP contact-form vulnerability, and `site-current/mailer.php:83` builds `From:` straight out of `$_POST['mail']`. PHPMailer validates addresses, encodes headers, handles 8-bit Cyrillic subjects, and sets the envelope sender correctly. |
| Verifying an uploaded file is genuinely an image | An extension check, or a `$_FILES[…]['type']` check | `getimagesize()` + `finfo` + a full GD decode/re-encode | Both inputs are attacker-controlled. Only re-encoding actually discards appended payloads and polyglot content. |
| Stripping EXIF and other metadata | A byte-level APP-marker scrubber | GD re-encode (drops all metadata implicitly) | A hand-rolled scrubber will miss a marker. But read orientation *first* — see P-7. |
| Honouring EXIF orientation in the browser downscale | Parsing EXIF in JS | `createImageBitmap(file, {imageOrientation:'from-image'})` | `canvas.drawImage()` ignores EXIF; the bitmap option is the spec'd way to get it applied. |
| Constant-time comparison of the HMAC time-trap token | `===` on the hex strings | `hash_equals()` | Timing-safe by construction. PHP 5.6+; a polyfill is unnecessary after D4-01. |
| Building a JSON body for Telegram | String concatenation | `json_encode()` | Already the house rule — `src/includes/jsonld.php:14-27` argues the same case for structured data. |
| Escaping every value that reaches the page | Ad-hoc `htmlspecialchars` calls in new code | `torin_esc()` | 40+ existing call sites; the form's redisplay-on-error path must not be the one place that forgets. |
| A per-IP rate limiter | An in-memory counter or a cookie | A flat file keyed on `hash('sha256', $ip . $secret)`, stored outside the web root | PHP has no shared memory here; a cookie is client-controlled *and* re-opens the consent question. |
| Generating WebP | A PHP-side converter at request time | `cwebp` at build time on the dev machine | Zero host CPU, zero runtime risk, output is plain static files. |

**Key insight:** every one of these is a place where the *naïve* version looks like it works. The legacy `mailer.php` "works" — it delivers mail, redirects to a confirmation page, and has run for years. What it does not do is validate, escape a header, check a return value, or resist a bot. This phase's job is to make the failure modes visible, not to make the happy path shorter.

## Runtime State Inventory

> This is a cutover phase. Files are only part of what moves.

| Category | Items Found | Action Required |
|----------|-------------|-----------------|
| **Stored data** | **None on the server by design** — D4-07 forbids persistence, and the current site stores nothing (no database is provisioned for this account as far as any artefact shows). **But Phase 4 introduces two new pieces of server-side state:** (a) the rate-limiter flat files, (b) `settings.txt` itself, which is **owner-edited on the server and is therefore NOT authoritative in git**. | Decide, and write down, which copy of `settings.txt` wins. A blanket `deploy-new.sh` run uploads `src/settings.txt` and **overwrites whatever the owner typed**. This is the exact class of "live config not in git" that CONTEXT's own research prompt warns about. Recommend: ship `settings.txt.example`, never `settings.txt`, and have `settings.php` fall back to defaults when the real file is absent. |
| **Live service config** | **Google Search Console** — property/properties not in git, and the *active* account's verification method is unidentified (see OQ-1). **Google Business Profile** — carries the hours the badge and JSON-LD echo; if the owner changes hours in `settings.txt` and not in GBP, Google shows two answers. **Umami Cloud** — a website ID, an event-name map and a dashboard, none of it in the repo. | (a) Identify and preserve the active GSC verification (OQ-1). (b) Add a checklist line: *changing hours means changing them in Google Business Profile too* — put it in the Bulgarian owner guide (D4-24). (c) Record the Umami website ID in `site-config.php` with a provenance comment; it is not a secret. |
| **OS-registered state** | **None** — no cron, no scheduled task, no process manager. The host runs Apache + FastCGI PHP only. Verified by the absence of any such artefact in the repo and by the fact that the entire site is request-driven. | None. |
| **Secrets / env vars** | `filezilla-server-data.xml` (FTPS, gitignored, project root) — **already exists**. Phase 4 adds **two more**: the Telegram bot token and the `office@torin.bg` SMTP password. Neither may be committed. Both must be readable by PHP **on the server** — which is a different problem from the FTPS credential, because that one is only ever read on the dev machine. | Define the server-side secret mechanism explicitly. Recommend a PHP file **outside `public_html/`** (e.g. `/home/<user>/torin-secrets.php`) returning an array, `require`d by `contact-send.php` via an absolute path, uploaded once by hand via File Manager, chmod 600, and never touched by `deploy-new.sh` (which is hardcoded to `public_html/new`, `scripts/deploy-new.sh:40`). Add a deploy-time guard that refuses to upload any file named like a secret. |
| **Build artefacts / installed packages** | `src/vendor/phpmailer/` — hand-vendored, committed, uploaded by `deploy-new.sh` like any other file. No egg-info, no compiled binary, no global install. **`php.fcgi` and `phpXX-fcgi.ini`** will be *generated by cPanel into the target directory* when the PHP version is set per-directory [CITED: help.superhosting.bg/en/set-php-version-for-directory.html] — these are host-side artefacts that will **not** be in git and will be **overwritten or orphaned** by the cutover move. | (a) SHA-pin the three PHPMailer files. (b) **After the cutover move, re-run the PHP-version-for-directory setup against the new root** — the generated wrapper embeds an absolute path (`/home/cpuser/dir/php.fcgi`), so moving the directory invalidates it. This is not optional and it is not in D4-28's plan today. |

**Two cutover hazards D4-28 does not name, both measured this session:**

1. **`public_html/old/` would be publicly reachable.** Moving the live root into `public_html/old/` puts a complete, crawlable copy of the old site at `https://torin.bg/old/` — including the Zendesk widget CONTACT-04 removes, and duplicate copies of all 16 indexed pages. Move it **outside `public_html/`** instead (`/home/<user>/old-site/`); the move is the same server-side rename and the rollback is identical.
2. **`.well-known/` must not move.** The live TLS certificate is Let's Encrypt, `CN=torin.bg`, SAN `*.torin.bg` + `torin.bg`, valid `Aug 13 2026 → Nov 11 2026` [VERIFIED: `openssl s_client`, 2026-09-17]. `.well-known/` carries its own `.htaccess` granting `.txt` access (recorded in `01-URL-INVENTORY.md` as must-carry). Renewal falls within weeks of any autumn cutover. `cgi-bin/` and the host-generated `error_log` are in the same category: leave them at root.

## Common Pitfalls

### P-1: "All four Viber CTA slots" is five
**What goes wrong:** D4-17 says four; there are **five** rendered anchors — `src/index.html:63`, `src/index.html:303`, `src/index.html:343`, `src/includes/footer.php:74`, `src/includes/category-page.php:554` [VERIFIED: `grep -rnF "site['viber']" src/` returns exactly 5, 2026-09-17]. A plan written to "four" leaves one live.
**Why it happens:** the count in CONTEXT counts *files* (index, footer, category-page = 3) or *slot kinds*, not occurrences.
**How to avoid:** gate on `grep -rc "viber://chat" src/ | awk -F: '{s+=$2} END{print s}'` reaching **0**, not on a hand-counted list. Note that a naïve `grep -rho 'viber://chat' src/ | wc -l` currently returns **8**, because `site-config.php`'s comments quote the scheme three times — the same substring-vs-element trap TRUST-01 recorded (`brand-row__item` counting 8 instead of 7).
**Warning signs:** a verify step that names files instead of counting occurrences.

### P-2: Instrumenting `tel:` with `data-umami-event` breaks the call button
**What goes wrong:** the shipped tracker, for any `<a>` carrying the attribute, runs `e.preventDefault()`, fires the beacon, and re-navigates inside `.finally()` via `location.href = href` [VERIFIED: read of `cloud.umami.is/script.js`, 2026-09-17 — the handler is `c.addEventListener("click", e => { const a = e.target.closest('[data-umami-event]'); if (a) { if ("A" === a.tagName && a.href) { … i || e.preventDefault(), t(a).finally(() => { i || ((…).href = n) }) } … } }, true)`]. The dialer therefore opens only after a network round-trip to a third party, in a task that no longer carries the user gesture. On a flaky mobile connection the call is delayed; where the browser enforces gesture-bound custom-scheme navigation, it never happens.
**Why it happens:** the attribute is the documented, no-code way to track clicks, and it is correct for ordinary same-origin links. `tel:` is not an ordinary link.
**How to avoid:** Pattern A-4 and Code Example C-4 — a passive delegated listener that calls `umami.track()` and returns. `fetch(..., {keepalive:true})` (which the tracker uses) survives navigation, so nothing is lost by not blocking.
**Warning signs:** any `tel:` href in a diff that also gained a `data-umami-*` attribute; any manual test of the call button done on desktop, where `tel:` does nothing either way.

### P-3: SuperHosting's PHP-version mechanism deletes the line that makes the site run
**What goes wrong:** their per-directory PHP selector writes `AddHandler fcgid-script .php` + `FcgidWrapper /home/cpuser/dir/php.fcgi .php` and explicitly instructs you to remove any pre-existing `AddHandler x-httpd-phpXX .php` [CITED: help.superhosting.bg/en/set-php-version-for-directory.html]. `src/.htaccess:86` is `AddHandler application/x-httpd-php52 .html .htm` — the `.html` mapping that makes all 19 pages execute. Following the instructions literally serves 19 pages of **raw PHP source**.
**Why it happens:** the host's documentation assumes a normal site where PHP lives in `.php` files. This site is the abnormal case.
**How to avoid:** on `/new/` only, keep the FastCGI block but extend the extension mapping to cover `.html` and `.htm` as well as `.php` — and verify by fetching a page and asserting the response contains rendered markup and **no** `<?php`. Test-fetch `google<token>.html` too: after cutover the verification file will also be parsed by PHP (it is `.html`), and it must come back byte-identical.
**Warning signs:** a response body starting with `<?php`; a `Content-Type` of `text/html` with PHP source in it; `X-Powered-By` absent where it used to be present.

### P-4: `sendMediaGroup` refuses a single photo
**What goes wrong:** the `media` array "must include 2-10 items" [VERIFIED: core.telegram.org/bots/api, 2026-09-17]. D4-13 allows 1–5 photos, and "one photo of the cracked screen" is the single most likely submission shape. A handler that always calls `sendMediaGroup` fails on exactly the common case.
**Why it happens:** the method name reads like it generalises.
**How to avoid:** branch — 0 photos → `sendMessage`; 1 photo → `sendPhoto` with the details as `caption` (0–1024 chars); 2–5 → `sendMessage` for the details, then `sendMediaGroup`.
**Related hard limits, all verified from the same source:** multipart upload cap is **10 MB for photos**, 50 MB for other types; `sendPhoto` additionally requires width + height ≤ 10000 and an aspect ratio ≤ 20; `sendMessage` text is 1–4096 characters *after* entity parsing. A long fault description must be clamped, and the GD re-encode (P-7) is what guarantees the dimension and size rules are met.

### P-5: `upload_max_filesize` cannot be raised the way you expect, and `max_file_uploads` cannot be raised at all
**What goes wrong:** three separate traps in one requirement (D4-13).
- `upload_max_filesize`, `post_max_size` and `max_input_vars` are **`INI_PERDIR`** — settable in `php.ini`, `httpd.conf`, `.htaccess` or `.user.ini` [VERIFIED: php.net/manual/en/ini.list.php]. But `.htaccess` `php_value` works **only under mod_php**; under FastCGI it is a syntax error that yields **500 for the whole subtree**. `.user.ini` works **only** under CGI/FastCGI [CITED: php.net/manual/en/configuration.file.per-user.php]. On this host the per-directory mechanism is FastCGI, and SuperHosting's own answer is the generated `phpXX-fcgi.ini` in the directory.
- **`max_file_uploads` is `INI_SYSTEM`** [VERIFIED: php.net ini.list] — it cannot be set from `.htaccess` **or** `.user.ini`, only from `php.ini`. Its default is 20, which comfortably covers D4-13's five photos, so this is a "do not plan to change it" finding rather than a blocker.
- `.user.ini` is cached for **`user_ini.cache_ttl`, default 300 seconds** [CITED: same page]. Change the limit, re-test immediately, and you measure the old value.
**How to avoid:** let the D4-06 probe report `ini_get()` for all six directives and `php_sapi_name()`, then choose the mechanism from evidence. Budget `post_max_size` above `5 × upload_max_filesize` plus form fields — PHP silently discards the **entire** `$_POST` when `post_max_size` is exceeded, so the handler sees an empty `$_POST` and an empty `$_FILES` and cannot tell "too big" from "no submission". Detect it explicitly: `$_SERVER['CONTENT_LENGTH'] > 0 && empty($_POST)`.
**Warning signs:** a form that appears to submit and returns the blank page; a verification that re-tests within five minutes of an ini change.

### P-6: The browser downscale is an optimisation, not a limit
**What goes wrong:** D4-14 shrinks to ~1600px in the browser, so the 10 MB server limit is never met in practice — and a plan then quietly treats the client as the enforcement point. Anyone can POST directly to `contact-send.php`.
**How to avoid:** the server enforces count, per-file size, total size, MIME, decodability and dimensions, unconditionally and identically whether or not JS ran. The no-JS path is not a lesser path; it is the *same* path with one fewer optimisation.
**Warning signs:** any server-side check written as "should not happen because the JS already…".

### P-7: Every portrait phone photo arrives sideways
**What goes wrong:** phones write the image in sensor orientation plus an EXIF `Orientation` tag. Both of the pipeline's transforms discard it: `canvas.drawImage()` ignores EXIF [CITED: whatwg/html#7210], and a GD `imagecreatefromjpeg()` → `imagejpeg()` round-trip drops all metadata. A cracked-screen photo shot in portrait — the overwhelmingly common case — reaches the owner's phone rotated 90°.
**How to avoid:** browser side, use `createImageBitmap(file, {imageOrientation:'from-image'})` rather than an `<img>` + `drawImage()`. Server side, read `exif_read_data($tmp)['Orientation']` **before** re-encoding and apply the matching `imagerotate()` / `imageflip()`. `ext-exif` is available on this host [CITED: help.superhosting.bg/en/php-modules.html].
**Warning signs:** a test performed only with a screenshot or a desktop-exported JPEG — neither carries an orientation tag, so both pass a broken pipeline.

### P-8: "No consent banner" is not "no disclosure"
**What goes wrong:** D4-18 correctly concludes that a cookieless tool avoids the ePrivacy Art. 5(3) consent trigger. It is then easy to conclude nothing else is owed. **GDPR Art. 13 transparency is a separate obligation**: the privacy declaration must name the analytics processor, the purpose, and the fact that data leaves the EU/EEA if it does. Umami Cloud is operated by Umami Software, Inc. with servers "in the US and EU" [CITED: umami.is/pricing FAQ, read 2026-09-17].
**A second, smaller nuance, found by reading the code rather than the marketing:** the tracker **reads `localStorage`** (checking an `umami.disabled` key) [VERIFIED: `cloud.umami.is/script.js`]. Under the EDPB's Guidelines 2/2023 on the technical scope of Art. 5(3) — final, October 2024 — *gaining access to* information already stored on terminal equipment is in scope, not only storing it. The read is for an opt-out flag, which is the strongest possible "strictly necessary" framing, and every privacy-first analytics tool does the same thing. This is a residual risk to state, not a blocker.
**How to avoid:** add two short Bulgarian paragraphs to `src/uslovia.html` — one naming the analytics processor and its purpose, one covering the form data under D4-07's no-retention position. Flag both for owner approval before launch, as CONTEXT's discretion note already requires.
**Warning signs:** a plan that treats `uslovia.html` as "already done" because the consent checkbox shipped.

### P-9: Publishing the canonicalisation 301s blinds Search Console unless a property is added first
**What goes wrong:** the GSC property that currently carries this site's data is the ranking variant, `http://www.torin.bg/` (per CONTEXT D4-29). In a URL-prefix property, `www` and non-`www`, `http` and `https` are four **different** properties. The moment the 301s go live, traffic moves to `https://torin.bg/` and the old property's graphs fall to zero — during exactly the window in which success criterion 5 requires watching for new 404s and ranking drops.
**Why it happens:** it reads like a redirect change, not a reporting change.
**How to avoid:** **before** publishing the canonicalisation, create and verify the property that will carry the traffic — ideally a **Domain property** (`sc-domain:torin.bg`), which unifies all four variants but requires a DNS TXT record and therefore registrar/DNS-panel access. Failing that, a `https://torin.bg/` URL-prefix property. Google confirms no Change of Address tool is involved: *"You don't need it for HTTP to HTTPS moves, switching between www and non-www on the same domain"* [CITED: developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes], and *"Verify data for each property separately in Search Console."* Google's stated timescale for consolidation is *"a few weeks or more"* for medium sites — so criterion 5's "days following launch" will show movement, not a settled state.
**Warning signs:** a cutover checklist whose GSC step comes after the swap.

### P-10: A no-argument `deploy-new.sh` uploads everything under `src/`
**What goes wrong:** `scripts/deploy-new.sh:161` builds the file list with `find . -type f ! -name '.DS_Store'` when no arguments are given [VERIFIED: read of the script, 2026-09-17]. This is precisely why plan 03-09 deleted `src/phptest.html` — it was one no-arg deploy from publishing `phpversion()`. Phase 4 adds a **probe file with more to leak**, a `settings.txt` that the owner edits on the server, and code that references secrets.
**How to avoid:** (a) the D4-06 probe file is named unguessably, gated on a secret query token, deployed by explicit path, and deleted in the same plan that creates it — with a verify step asserting it 404s; (b) secrets live outside `public_html/` and therefore outside `src/`; (c) ship `settings.txt.example`, never `settings.txt`.
**Warning signs:** any new file under `src/` whose content would be embarrassing in a directory listing.

### P-11: The HMAC time-trap and D4-34's HTML caching are coupled
**What goes wrong:** the anti-bot time-trap puts a signed render timestamp in a hidden field. D4-34 raises HTML caching from "0 seconds" to "minutes". A cached contact page hands every visitor the *same* stale timestamp — so either the window is wide enough to be useless, or real submissions are rejected.
**How to avoid:** send `Cache-Control: no-store` for the contact page specifically (it is PHP; one `header()` call), and set the accept window generously anyway — reject only *below* ~3 seconds and *above* a couple of hours. The lower bound is what catches bots; the upper bound only needs to stop replay.
**Warning signs:** a cache rule applied by `ExpiresByType text/html` with no page-level exception.

### P-12: Replacing the Viber label overflows the sticky call bar
**What goes wrong:** `src/css/components.css:386-388` carries a sizing comment tuned to a specific string: *"The label «Пишете във Viber» measures ~141px of Sofia Sans at --fs-body/700; at 360px each half is 180px, so the button's normal --sp-lg side padding (48px total) would overflow it."* [VERIFIED: read of `src/css/components.css:376-392`, 2026-09-17]. «Изпратете запитване» is longer than «Пишете във Viber» in both characters and rendered width, on a bar that already had only 39px of slack.
**How to avoid:** use a shorter label in the `.callbar` slot specifically (e.g. «Запитване») and measure it with `scripts/render-check.sh` at 360×640 before merging. The full label is fine in the hero, footer and category-page slots, which are not width-constrained.
**Warning signs:** a CTA swap plan that says "needs no layout change" — CONTEXT D4-17 says exactly this, and the CSS comment predicted otherwise.

### P-13: The D4-25 "unlisted means closed" claim is not what Google documents
**What goes wrong:** D4-25 rests on "in structured data a day that is not listed **is** closed". schema.org supports this reading — *"The place is open if the opens property is specified, and closed otherwise"* [CITED: schema.org/OpeningHoursSpecification]. But Google's own guidance takes a different route and its example **lists the closed day explicitly**: *"To show a business is closed all day, set both `opens` and `closes` properties to `00:00`"*, illustrated with a `Sunday` entry carrying `"opens":"00:00","closes":"00:00"` [CITED: developers.google.com/search/docs/appearance/structured-data/local-business]. Google nowhere states that omission means closed.
**How to avoid:** emit **three** entries — `Mo–Fr 08:00–16:00`, plus `Saturday 00:00/00:00` and `Sunday 00:00/00:00`. It costs two array entries, matches Google's documented shape exactly, and removes an inference from the one place where being wrong sends customers to a locked door. D4-25's *decision* (closed weekends, unstated on the page) is unaffected — this is only about the JSON.
**Warning signs:** a plan quoting "unlisted means closed" as verified fact.

### P-14: `error_log` at the document root is a disclosure surface once PHP runs there
**What goes wrong:** `01-URL-INVENTORY.md` lists a host-generated `error_log` at the live root as must-carry. It currently returns **403** [VERIFIED: curl, 2026-09-17], so it is protected today. After cutover, the root gains 20 PHP-executing pages *and* a POST handler, which means it starts accumulating entries that may contain submitted values, file paths and stack context.
**How to avoid:** confirm the 403 still holds after the `.htaccess` promotion (the sweep already fetches it — make it an assertion, not an observation), and set `display_errors=Off` / `log_errors=On` for the new root. Never `echo` an exception message from `contact-send.php` into the page.

## Code Examples

### C-1: Server-side capability probe — the first plan of the phase (D4-06)

```php
<?php
// probe-<random>.php — THROWAWAY. Deploy by explicit path, read once, delete,
// then assert it 404s. Never deploy with a no-argument deploy-new.sh run (P-10).
// Gated on a secret so a directory guess does not disclose the environment.
if (!isset($_GET['k']) || $_GET['k'] !== 'REPLACE_WITH_RANDOM_32_CHARS') {
    header('HTTP/1.0 404 Not Found');
    exit;
}
header('Content-Type: text/plain; charset=utf-8');

echo "version              : " . phpversion()        . "\n";
echo "sapi                 : " . php_sapi_name()     . "\n";   // decides .user.ini vs php_value (P-5)

foreach (array('gd','exif','fileinfo','curl','openssl','mbstring','hash','ctype','filter') as $ext) {
    echo str_pad("ext:$ext", 21) . ": " . (extension_loaded($ext) ? 'yes' : 'NO') . "\n";
}
foreach (array('upload_max_filesize','post_max_size','max_file_uploads',
               'max_input_vars','memory_limit','max_execution_time',
               'allow_url_fopen','user_ini.filename','user_ini.cache_ttl',
               'sendmail_path','SMTP','smtp_port') as $k) {
    echo str_pad("ini:$k", 21) . ": " . var_export(ini_get($k), true) . "\n";
}

// THE question D4-06 exists to answer. Telegram's own no-op endpoint; no token needed.
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.telegram.org/bot0:0/getMe');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $body = curl_exec($ch);
    echo "outbound:curl443     : " . ($body === false
        ? 'FAIL ' . curl_error($ch)
        : 'OK http=' . curl_getinfo($ch, CURLINFO_HTTP_CODE)) . "\n";
    curl_close($ch);
}
echo "sendmail binary      : " . (is_executable('/usr/sbin/sendmail') ? 'yes' : 'NO') . "\n";

// Local MTA reachability for the PHPMailer SMTP leg.
$fp = @fsockopen('localhost', 25, $e, $s, 5);
echo "smtp:localhost:25    : " . ($fp ? 'OPEN' : "FAIL $s") . "\n";
if ($fp) { fclose($fp); }
```

Everything this phase decides — the upload-limit mechanism, whether Telegram is viable at all, whether GD and EXIF are present, whether SMTP or sendmail carries the email leg — is answered by one request to this file. Run it before writing any other Phase 4 code.

### C-2: PHPMailer against the host's own SMTP (CONTACT-03, D4-11)

```php
<?php
// contact-send.php — the ONLY file in this tree that is not PHP 5.2-safe (Pattern A-3).
// Settings verified against help.superhosting.bg/smtp-settings-in-script.html (2026-09-17):
//   host = torin.bg (or <server>.superhosting.bg) · port 25 · AUTH yes · ENCRYPTION none
// Outbound 25/26/465 to EXTERNAL servers are blocked on this shared hosting, so an
// external relay is not an option over SMTP. Using the host's own server is also what
// gives SPF + DKIM alignment for free:
//   SPF  : torin.bg TXT "v=spf1 +a +mx ... +ip4:217.174.156.170 ... ~all"  (+a covers this box)
//   DKIM : default._domainkey.torin.bg  v=DKIM1; k=rsa; p=MIIBIjAN...
// Both measured live 2026-09-17. There is no DMARC record — see Open Questions.
require_once dirname(__FILE__) . '/vendor/phpmailer/Exception.php';
require_once dirname(__FILE__) . '/vendor/phpmailer/PHPMailer.php';
require_once dirname(__FILE__) . '/vendor/phpmailer/SMTP.php';
$secrets = require '/home/REPLACE_CPANEL_USER/torin-secrets.php';   // OUTSIDE public_html

function torin_send_mail($to, $subject, $bodyText, $secrets) {
    $m = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $m->isSMTP();
        $m->Host       = 'torin.bg';
        $m->Port       = 25;
        $m->SMTPAuth   = true;
        $m->Username   = 'office@torin.bg';
        $m->Password   = $secrets['smtp_password'];
        $m->SMTPSecure = '';          // documented: unencrypted. Same-machine hop.
        $m->SMTPAutoTLS = false;      // without this, PHPMailer opportunistically STARTTLSes
        $m->CharSet    = 'UTF-8';     // mandatory: the whole site is Bulgarian
        $m->Timeout    = 15;

        // From is a FIXED, developer-authored literal. The legacy handler built it from
        // $_POST (site-current/mailer.php:83) — that is header injection and spoofing in
        // one line. The visitor's address goes in Reply-To, after validation.
        $m->setFrom('office@torin.bg', 'TORIN.bg');
        $m->addAddress($to);
        $m->Subject = $subject;
        $m->Body    = $bodyText;
        $m->isHTML(false);
        return $m->send();            // returns bool; D4-10 requires acting on it
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('torin mail: ' . $e->getMessage());   // never echoed (P-14)
        return false;
    }
}
```

### C-3: Upload hardening — content-verified, orientation-corrected, re-encoded (D4-15)

```php
<?php
// includes/upload.php — returns a normalised temp path or false. Nothing is EVER
// written under public_html/: this server maps .html to PHP, so a surviving upload
// in the document root is code execution, not just a stray file.
function torin_normalise_upload($tmpPath, $maxEdge) {
    // 1. Structural check FIRST. getimagesize() parses the header; it fails on a
    //    file that merely carries an image extension or a spoofed Content-Type.
    $info = @getimagesize($tmpPath);
    if ($info === false) { return false; }
    $mime = $info['mime'];
    if ($mime !== 'image/jpeg' && $mime !== 'image/png') { return false; }

    // 2. Independent corroboration via finfo (ext-fileinfo is available on this host).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if ($finfo->file($tmpPath) !== $mime) { return false; }

    // 3. Read orientation BEFORE decoding — GD discards all metadata on re-encode,
    //    so this is the only moment it exists (P-7). Phone photos are portrait.
    $orientation = 1;
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmpPath);
        if (is_array($exif) && isset($exif['Orientation'])) {
            $orientation = (int) $exif['Orientation'];
        }
    }

    // 4. Full decode. This is the step that discards appended payloads, polyglots
    //    and every EXIF/XMP/ICC block. A header check alone does not.
    $img = ($mime === 'image/jpeg')
        ? @imagecreatefromjpeg($tmpPath)
        : @imagecreatefrompng($tmpPath);
    if ($img === false) { return false; }

    if     ($orientation === 3) { $img = imagerotate($img, 180, 0); }
    elseif ($orientation === 6) { $img = imagerotate($img, -90, 0); }
    elseif ($orientation === 8) { $img = imagerotate($img,  90, 0); }

    // 5. Clamp dimensions. Telegram rejects width+height > 10000 and ratio > 20,
    //    and this also caps the re-encoded byte size well under the 10 MB photo limit.
    $w = imagesx($img); $h = imagesy($img);
    if ($w > $maxEdge || $h > $maxEdge) {
        $scale = $maxEdge / max($w, $h);
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);
        $img = $dst;
    }

    // 6. Always re-encode to JPEG under a random name, in PHP's temp dir only.
    //    The original filename is attacker-controlled and is never reused.
    $out = tempnam(sys_get_temp_dir(), 'torin_');
    $ok  = imagejpeg($img, $out, 82);
    imagedestroy($img);
    if (!$ok) { @unlink($out); return false; }
    return $out;   // caller unlink()s this on BOTH the success and failure paths
}
```

### C-4: Tracking a `tel:` click without touching its activation path (D4-20, D4-21)

```html
<!-- js/analytics.js — loaded with defer, after the Umami tag.
     DO NOT put data-umami-event on a tel: anchor. The shipped tracker
     preventDefault()s any <a> carrying that attribute and re-navigates only after
     its fetch settles, which puts the dialer behind a third-party round trip and
     outside the user gesture (Pitfall P-2, verified by reading cloud.umami.is/script.js).
     A passive delegated listener has none of that behaviour, and umami's own
     transport is fetch(..., {keepalive:true}) so nothing is lost on navigation. -->
<script>
document.addEventListener('click', function (e) {
  var a = e.target.closest && e.target.closest('a[href]');
  if (!a || !window.umami) { return; }
  var href = a.getAttribute('href') || '';
  if (href.indexOf('tel:') === 0) {
    // slot identifies WHERE it was tapped: hero / cta / callbar / footer / category
    umami.track('call-click', { slot: a.getAttribute('data-slot') || 'unknown' });
  }
  // No preventDefault. No await. The browser dials exactly as it would with no JS.
}, { passive: true, capture: true });
</script>
```

Two budget notes, both from the official pricing FAQ: *"Each website pageview counts as one event. If you save event properties, each data property stored counts as one event."* [CITED: umami.is/pricing, read 2026-09-17]. A `call-click` carrying one `slot` property therefore costs **2** events, not 1. And Umami's performance/Web-Vitals collection is **opt-in** via `data-performance="true"` — leave it off [VERIFIED: `L = w("performance") === "true"` in the tracker source].

### C-5: The owner-editable settings file and its fail-safe parser (D4-23, OWNER-01)

```
# settings.txt — редактирайте през cPanel » File Manager.
# Един ред = една настройка. Всичко след # е коментар.
# Ако сгрешите ред, сайтът НЕ се чупи — просто ползва предишната стойност.

hours_open:   08:00
hours_close:  16:00
hours_days:   Mo-Fr

# Празнична/отпускна лента. Оставете vacation_from празно, за да е изключена.
vacation_from:    
vacation_to:      
vacation_message: Сервизът е в годишен отпуск. Приемаме заявки по телефона.
```

```php
<?php
// includes/settings.php — PHP 5.2-safe. Reads settings.txt and returns ONLY the
// keys that validated. Every other key falls back to the literal in site-config.php.
//
// NOT parse_ini_file(): it reserves yes/no/on/off/null, is fussy about quoting, and
// emits a Warning on a malformed line — which on this host would print into the top
// of the HTML of all 20 pages. That is exactly the white-screen class D4-23 forbids.
//
// PER-KEY fallback, never whole-file. A typo in vacation_to must not blank the hours.
function torin_read_settings($path) {
	$out = array();
	if (!is_readable($path)) { return $out; }          // absent file is NOT an error
	$lines = @file($path, FILE_IGNORE_NEW_LINES);
	if ($lines === false) { return $out; }
	foreach ($lines as $line) {
		$line = trim($line);
		if ($line === '' || $line{0} === '#') { continue; }
		$pos = strpos($line, ':');
		if ($pos === false) { continue; }              // malformed: skip, do not fail
		$key = strtolower(trim(substr($line, 0, $pos)));
		$val = trim(substr($line, $pos + 1));
		if (torin_setting_is_valid($key, $val)) { $out[$key] = $val; }
	}
	return $out;
}

function torin_setting_is_valid($key, $val) {
	if ($key === 'hours_open' || $key === 'hours_close') {
		return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $val) === 1;
	}
	if ($key === 'vacation_from' || $key === 'vacation_to') {
		if ($val === '') { return true; }              // empty = banner off (D4-27 default)
		return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $val) === 1;
	}
	if ($key === 'vacation_message') { return strlen($val) <= 300; }
	if ($key === 'hours_days')       { return strlen($val) <= 20;  }
	return false;                                      // unknown key: ignored entirely
}
```

### C-6: Hours and holiday closure, single-sourced into the structured data (D4-26, D4-27)

```php
<?php
// jsonld.php — REPLACES the hard-coded literal currently at lines 64-71, which reads
// verbatim today:
//   'openingHoursSpecification' => array(
//       array(
//           '@type'     => 'OpeningHoursSpecification',
//           'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday'),
//           'opens'     => '08:00',
//           'closes'    => '16:00'
//       )
//   )
// That is the SECOND copy of the hours; site-config.php:62 holds the first
// ('Понеделник – Петък, 8:00 – 16:00'). D4-26 collapses both onto settings.txt.
date_default_timezone_set('Europe/Sofia');   // D4-27; the server's default TZ is unknown

$torin_hours = array(
	array(
		'@type'     => 'OpeningHoursSpecification',
		'dayOfWeek' => array('Monday','Tuesday','Wednesday','Thursday','Friday'),
		'opens'     => $site['hours_open'],
		'closes'    => $site['hours_close']
	),
	// Weekend stated EXPLICITLY rather than by omission. schema.org treats an absent
	// 'opens' as closed, but Google's own documented shape lists the closed day with
	// "opens":"00:00","closes":"00:00" and never claims omission means anything.
	// Two array entries buy certainty on the one value that sends customers to a
	// locked door if it is wrong. (Pitfall P-13.)
	array('@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Saturday',
	      'opens' => '00:00', 'closes' => '00:00'),
	array('@type' => 'OpeningHoursSpecification', 'dayOfWeek' => 'Sunday',
	      'opens' => '00:00', 'closes' => '00:00')
);

// Holiday closure. This is Google's own seasonal-hours shape, quoted from
// developers.google.com/search/docs/appearance/structured-data/local-business:
//   "opens":"00:00","closes":"00:00","validFrom":"2015-12-23","validThrough":"2016-01-05"
// Note it carries NO dayOfWeek and lives in openingHoursSpecification — not in
// specialOpeningHoursSpecification, which schema.org defines but Google does not
// use in its own example.
if ($site['vacation_from'] !== '' && $site['vacation_to'] !== ''
    && date('Y-m-d') <= $site['vacation_to']) {
	$torin_hours[] = array(
		'@type'        => 'OpeningHoursSpecification',
		'opens'        => '00:00',
		'closes'       => '00:00',
		'validFrom'    => $site['vacation_from'],
		'validThrough' => $site['vacation_to']
	);
}
$torin_ld['openingHoursSpecification'] = $torin_hours;
```

### C-7: The `.htaccess` promotion — the two edits, with the trap spelled out (D4-29, D4-30)

```apache
# ROOT .htaccess AFTER PROMOTION. Compare this against src/.htaccess line by line.
# The staging segment appeared in EXACTLY TWO places there and both change here:
#   src/.htaccess:18  RewriteBase /new/                      ->  RewriteBase /
#   src/.htaccess:25  ... https://torin.bg/new/$1 ...         ->  ... https://torin.bg/$1 ...
# Miss either and a redirect breaks SILENTLY, at 301, with a Location header present.
# Verified live 2026-09-17 that this rule SHAPE already works on this host: all three
# non-canonical variants of /new/index.html 301 to https://torin.bg/new/index.html in
# exactly one hop, so %{HTTPS} off detection is sound here.
RewriteEngine On
RewriteBase /

# D4-29: https://torin.bg is canonical. ONE hop from any of the four variants.
# Target is a hardcoded literal, NEVER %{HTTP_HOST} reflection (host-header injection).
# Measured 2026-09-17: today all four root variants return 200 with no redirect at all.
RewriteCond %{HTTPS} off [OR]
RewriteCond %{HTTP_HOST} ^www\.torin\.bg$ [NC]
RewriteRule ^(.*)$ https://torin.bg/$1 [R=301,L]

# SEO-05 retirements — unchanged, and correct ONLY because RewriteBase is set above.
RewriteRule ^covid\.html$ about.html [R=301,L]
RewriteRule ^laptopi\.html$ index.html [R=301,L]
RewriteRule ^rezervni-chasti\.html$ ekran-klaviatura-portove.html [R=301,L]
RewriteRule ^za-bateriite\.html$ zalivane-technosti.html [R=301,L]

# ############################################################
# ## THE X-Robots-Tag noindex BLOCK FROM src/.htaccess:82-84 ##
# ## IS DELETED HERE. Carrying it forward noindexes the      ##
# ## ENTIRE live site at once. It is not commented out —     ##
# ## it is absent, so no future edit can re-enable it.       ##
# ############################################################

# D4-34: assets long, HTML short. One year is safe ONLY because header.php stamps
# every asset URL with ?v=<filemtime> (includes/asset-version.php).
<IfModule mod_expires.c>
	ExpiresActive On
	ExpiresByType text/html            "access plus 10 minutes"
	ExpiresByType text/css             "access plus 1 year"
	ExpiresByType application/javascript "access plus 1 year"
	ExpiresByType text/javascript      "access plus 1 year"
	ExpiresByType font/woff2           "access plus 1 year"
	ExpiresByType image/webp           "access plus 30 days"
	ExpiresByType image/jpeg           "access plus 30 days"
	ExpiresByType image/png            "access plus 30 days"
	ExpiresByType image/svg+xml        "access plus 30 days"
</IfModule>

# New in Phase 4: nothing the owner edits, and nothing the panel generates, is fetchable.
<FilesMatch "^(settings\.txt|\.user\.ini|php\.fcgi|php[0-9]+-fcgi\.ini)$">
	Order allow,deny
	Deny from all
</FilesMatch>
```

### C-8: Measured WebP baseline for D4-33

```
$ cwebp -version           # 1.5.0 / libsharpyuv 0.4.1  [present on the dev machine]
$ cwebp -q 80 -metadata none <file> -o <file>.webp

ouroffice.jpg          jpg= 124811   webp=  51872   41%
profilaktika3.jpg      jpg=  80495   webp=  24370   30%
baterii.jpg            jpg=  65286   webp=  14700   22%
meh-prob5.jpg          jpg=  43219   webp=  19408   44%
                       ────────────────────────────────
TOTAL (4 largest)      jpg= 313811   webp= 110350   35%
```

**Measured 2026-09-17.** The image set is **43 files / 1.3 MB — 39 JPEG, 4 PNG, zero WebP**, largest `src/img/ouroffice.jpg` at 124,811 B. At q=80 the sample re-encodes to 35% of its JPEG size, implying roughly **800 KB saved site-wide**. Weigh that against the cost: 43 new files to generate, keep in sync and upload, and a `<picture>` element replacing every `<img>` — including inside `category-page.php`'s evidence strip, whose `width`/`height` attribute contract is load-bearing (`scripts/probes/trust-signals.js` measures it). This is real but not urgent; if the phase runs long, D4-33 is the correct thing to cut.

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| PHPMailer **6.x** (CLAUDE.md's recommendation) | PHPMailer **7.1.1** | 7.0.0 released 2025-10-15; 7.1.1 on 2026-05-18 | Same `php >=5.5.0` floor and same extensions, so adopting 7.x costs nothing. Latest 6.x (6.12.0) shipped the same day as 7.0.0 and is the compatibility line, not the active one. |
| `AddHandler application/x-httpd-phpXX .php` for per-directory PHP | `mod_fcgid` + `FcgidWrapper` with a generated `php.fcgi` | SuperHosting doc last updated 2023-02-09 | The mechanism that currently makes this site work is the one the host tells you to delete. This is the phase's largest single risk (P-3). |
| `php_value` in `.htaccess` for PHP ini | `.user.ini` (FastCGI) or the host's `phpXX-fcgi.ini` | PHP 5.3 introduced `.user.ini` | `php_value` on a non-mod_php SAPI is a 500 for the whole subtree, not an ignored directive. |
| GA4 + a cookie-consent banner | Cookieless analytics with no banner | EDPB Guidelines 2/2023 finalised October 2024 | The guidelines widen Art. 5(3) to cover *access* as well as storage — so "cookieless" is a strong position, not an absolute one (P-8). |
| `navigator.sendBeacon()` for unload-safe analytics | `fetch(url, {keepalive:true})` | Umami's shipped tracker uses the latter [VERIFIED: source read 2026-09-17] | Equivalent guarantee, better ergonomics. Means a tracked click needs no navigation blocking. |
| `<img src="photo.jpg">` | `<picture>` with a WebP `<source>` and a JPEG fallback | Universal browser support for years | Free bytes; the only cost is generation and sync discipline. |

**Deprecated / outdated in this repo:**
- **PHP 5.2.17** — released 2006, unpatched since 2011, still serving every page today [VERIFIED: `x-powered-by: PHP/5.2.17` on `https://torin.bg/new/index.html`, 2026-09-17]. SuperHosting's published version list stops at 8.1 [CITED: help.superhosting.bg/en/php5-linux-hosting.html, updated 2022-02-11] — that page is four years old and the panel may well offer more. **Read the panel's list; do not plan against the documentation.** Note that 8.1 itself reached end of security support in December 2025, so even the documented ceiling is not a long-term answer.
- **`site-current/mailer.php`** — live right now at `https://torin.bg/mailer.php` and returning **302 → msg.html** on a bare GET [VERIFIED: curl, 2026-09-17], which means it attempts a `mail()` send on every unauthenticated request with no honeypot, no rate limit and no validation. It is an open mail-relay-shaped endpoint today. Retiring it is a security fix, not just a refactor.
- **`otpuska.js`** and **`header.js`** — both still 200 at the live root [VERIFIED: curl, 2026-09-17]; both are superseded by the rebuild and should be on D4-31's deletion list alongside the seven files already named.
- **The Zendesk widget** — present in 16 of 17 `site-current/` pages, absent from `src/` entirely. CONTACT-04 is satisfied by promotion, not by work.

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | The host's PHP control panel offers a version newer than 8.1. Only 5.2–8.1 are *documented*, on a page last updated 2022-02-11. | State of the Art | If 8.1 is the ceiling, D4-01 still succeeds (PHPMailer needs 5.5+) but lands on a version that is already past security support. Worth telling the owner. |
| A2 | The per-directory PHP mechanism on this account is the FastCGI wrapper their docs describe, rather than something the account was grandfathered onto. The existing `AddHandler application/x-httpd-php52` is *older* than that documented mechanism. | P-3 | The whole D4-01/D4-02 plan shape changes. Closed entirely by the C-1 probe reporting `php_sapi_name()`. |
| A3 | Outbound HTTPS to `api.telegram.org` is permitted. Only SMTP ports 25/26/465 are *documented* as blocked; nothing says 443 is. | Standard Stack, D4-06 | D4-05 collapses and email becomes the only channel. Closed by C-1. |
| A4 | The `office@torin.bg` mailbox exists as a real cPanel email account with a retrievable password. The address is in `site-config.php:43` and the MX points at this server, but a mailbox account is a different thing from a forwarder. | C-2 | SMTP auth has no credential; fall back to local sendmail (which needs no credential at all). |
| A5 | 100K events/month is ample for this site. No traffic figure for torin.bg exists in any project artefact. | Standard Stack | At ~3,300 events/day the Hobby cap is generous for a local repair shop, and overage is billed rather than cut off — but the owner's "no extra spend" constraint means *any* overage is a problem. Pull the impressions figure from Search Console before launch and size it. |
| A6 | Umami Cloud's Hobby tier permits a commercial site. The pricing page describes it as suitable for "personal projects and low-traffic websites" [CITED: docs.umami.is/docs/cloud/faq]. | Standard Stack, D4-18 | If the terms of service restrict Hobby to non-commercial use, the choice fails the no-spend constraint outright. **Read the ToS before committing** — this is the one thing D4-19 asked for that could not be settled from the pricing page. |
| A7 | The owner (or the project) can add a DNS TXT record for `torin.bg`. Nameservers are `ns71/ns72.bgdns.net`; whether the hosting panel exposes DNS editing is unknown. | P-9, OQ-1 | The cutover-proof GSC verification and any DMARC record both become impossible, and verification stays dependent on a file that a directory move can orphan. |
| A8 | Publishing the four host-canonicalisation 301s carries acceptable short-term ranking risk. Google says consolidation takes "a few weeks or more". | P-9 | Success criterion 5 is written as "no ranking drops in the days following launch". Movement during consolidation is expected and normal; the criterion should be read as "no *unexplained* drops and no new 404s", or it will fail by construction. |
| A9 | Umami's `localStorage` read is defensible as strictly necessary under EDPB Guidelines 2/2023. | P-8 | A stricter reading would require consent after all — which would undo D4-18's entire rationale. Low practical risk (every privacy-first tool behaves identically), but it is a legal judgement this research is not qualified to make. |
| A10 | The new contact page slug. `kontakti.html` is used throughout this document as a placeholder; no artefact names it. | Project Structure, D4-16 | Trivial to change, but it must be decided **before** the page is linked from the nav and five CTA slots, and before it enters `sitemap.xml`. |

## Open Questions

1. **Which Search Console property is actually active, and how is it verified?**
   - *What we know:* the only discoverable token is `google1718743335455f1c.html`, created **2020-10-29**, live and returning 200 [VERIFIED: curl + `last-modified` header, 2026-09-17]. There is **no** `google-site-verification` meta tag on the live homepage and **no** `google-site-verification` DNS TXT record (`torin.bg` TXT returns only the SPF string) [VERIFIED: curl + dig, 2026-09-17]. `OWNER_ANSWERS.md:116` says *"it would appear there is no GSC acc, but i can provide access to a new acc i've enabled on the same google business account"*, yet Phase 3.5 quotes 16 months of Search Console impression data — so an active property exists.
   - *What's unclear:* whether that active property is verified by the 2020 file (i.e. the new account was granted delegated access), by a second file not identified here, or by some other method.
   - *Recommendation:* resolve this **before** the swap, not during it. MIGR-02's "must-carry" list protects a token that may verify an account nobody can log into. Then add a **DNS TXT verification as a second, cutover-proof method** — it is immune to any file move and costs one record. This gates P-9's property work too.

2. **Static or PHP-generated `sitemap.xml`, and when is it submitted?** (Claude's discretion — recommendation recorded.)
   - *Recommendation:* **static**, generated by `scripts/gen-sitemap.sh`, guarded by `scripts/sitemap-check.sh` (Pattern A-6). A PHP-generated `.xml` would need a handler mapping for a fifth extension in the one file that can take the site down. **Submit after the swap sweep passes**, never before — a sitemap listing `https://torin.bg/` URLs while the root still serves the old site invites Google to crawl a state that is about to change. `robots.txt` ships in the same move and points at it.
   - *Scope:* 19 existing pages plus the new contact page = **20 URLs**. Exclude `msg.html` and give it a `noindex` — it is a post-submit confirmation with no search value.

3. **Does the owner accept Umami instead of Google Analytics?** He asked for GA explicitly (`OWNER_ANSWERS.md:116`). D4-18 is a deliberate departure that CONTEXT itself says "needs his sign-off". This is not a technical question and no measurement can answer it.
   - *Recommendation:* present it as "the same two answers you asked for — which buttons get used, which service pages get visited — without a cookie banner in front of them", and get a yes in writing before the tag ships.

4. **Is there a DMARC record, and should there be?** `_dmarc.torin.bg` returns **nothing** [VERIFIED: dig, 2026-09-17], while SPF and DKIM are both published and correct.
   - *Recommendation:* adding `v=DMARC1; p=none; rua=mailto:office@torin.bg` is a cheap deliverability and visibility win for the customer-confirmation email (D4-09) and costs nothing at `p=none`. Gated on A7 (DNS access). Out of scope if DNS is inaccessible — note it and move on.

5. **Where do the two new server-side secrets live?** The Telegram token and the SMTP password must be readable by PHP **on the server**, which is a different problem from the FTPS credential (read only on the dev machine). CONTEXT's discretion note says "follows the established pattern for `filezilla-server-data.xml`" — but that pattern has no server-side half.
   - *Recommendation:* a file outside `public_html/` returning an array, `require`d by absolute path, uploaded once by hand, chmod 600, plus a `deploy-new.sh` guard that refuses any path matching a secret-ish name. Record the absolute path in the plan; it is not itself a secret.

6. **Does `msg.html` survive as the success destination, or does the form post to itself?** D4-10 requires the handler to own its errors rather than blindly redirecting. `src/msg.html:17-20` says Phase 4 owns the form and that this page changes with it.
   - *Recommendation:* POST to `contact-send.php`, then `303 See Other` → `msg.html` on success (POST-redirect-GET, so a refresh cannot resubmit), and on failure re-render the contact page with the values preserved through `torin_esc()` and an honest error plus the phone number. Keep `msg.html` — it is an existing indexed URL (`01-URL-INVENTORY.md` lists it as must-carry) and deleting it would need a 301.

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| `curl` (dev machine) | Live probes, cutover sweep | ✓ | system | — |
| `node` (dev machine) | Probes, `seo-metadata-check.js`, CDP harness | ✓ | v20.18.0 | — |
| `python3` (dev machine) | `deploy-new.sh` credential decode, CSS stripper | ✓ | 3.13.5 | — |
| **Brave (headless, CDP)** | Rendered verification of the new CTA, banner, contact page | ✓ | drives `scripts/render-check.sh` — used successfully this session to render `umami.is/pricing` | — |
| `cwebp` (dev machine) | D4-33 WebP generation | ✓ | 1.5.0 / libsharpyuv 0.4.1 | `sips` (macOS) — but `sips --help` lists no WebP support here |
| `jpegtran` (dev machine) | Lossless JPEG re-optimisation | ✓ | present | — |
| `php` (dev machine) | Local syntax check of new PHP | **✗** | — | **None. The deploy IS the only PHP syntax check** — a Phase 3 finding that cost a live defect (`header.php:33`). Every new PHP file must be deployed to `/new/` and fetched before it is trusted. |
| `exiftool` (dev machine) | Inspecting EXIF orientation in test fixtures | ✗ | — | `python3` + a minimal EXIF read, or `sips -g all` |
| `magick` / `convert` | Image work | ✗ | — | `cwebp` + `sips` + `jpegtran` cover everything needed |
| PHP `gd` (host) | D4-15 re-encode | **probe** | — | `imagick`, also listed as supported |
| PHP `exif` (host) | P-7 orientation | **probe** | — | Accept rotated photos and say so — a bad outcome, not a blocker |
| PHP `curl` (host) | D4-05 Telegram | **probe** | — | `allow_url_fopen` + stream context; if both fail, email-only |
| Outbound TCP/443 (host) | D4-05 Telegram | **probe** | — | Email-only. Materially weakens D4-05's whole rationale. |
| Local MTA / `/usr/sbin/sendmail` (host) | D4-09, D4-11 | **probe** | — | SMTP `torin.bg:25` with auth |
| Raised `upload_max_filesize` / `post_max_size` | D4-13 | **probe** | — | Reduce the advertised per-photo limit to whatever the host's default is, and say so in the form copy |

**Missing dependencies with no fallback:** a local PHP interpreter. This is a structural property of the project, not a gap to close — it is why Phase 3 established that the deploy is the syntax check, and why the C-1 probe must be the phase's first plan.

**Items marked "probe":** every one is answered by a single request to the C-1 probe file. None of them can be answered from the dev machine, from documentation, or from this research.

## Security Domain

`security_enforcement: true`, `security_asvs_level: 1`, `security_block_on: "high"` [VERIFIED: `.planning/config.json`, read 2026-09-17].

This phase turns a read-only brochure site into one with a **public, unauthenticated, file-accepting POST endpoint**, on a server that **executes `.html` as PHP**. That is the largest single expansion of attack surface in the project's history.

### Applicable ASVS Categories

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | **no** | No user accounts anywhere. The deferred admin page was rejected for exactly this reason. |
| V3 Session Management | **no** — and deliberately kept that way | No `session_start()`. The time-trap uses an HMAC-signed timestamp instead, which needs no cookie and therefore raises no consent question. |
| V4 Access Control | **yes** | `settings.txt`, `.user.ini`, `php.fcgi` and `phpXX-fcgi.ini` denied via `<FilesMatch>` (C-7). Secrets outside `public_html/`. The C-1 probe gated on a secret token and deleted. `error_log` confirmed still 403 post-cutover. |
| V5 Input Validation | **yes** | Every field type-validated and length-bounded server-side; every uploaded file structurally verified, decoded and re-encoded; every output through `torin_esc()`. `$_FILES[…]['type']` and the filename treated as hostile. Email address validated before it goes anywhere near `Reply-To`. |
| V6 Cryptography | **yes** | `hash_hmac('sha256')` for the time-trap token, `hash_equals()` for comparison, `random_bytes()`/`tempnam()` for filenames. Nothing hand-rolled. |
| V7 Error Handling & Logging | **yes** | `display_errors=Off`, `log_errors=On`. No exception message reaches the page (P-14). The handler logs a correlation id, never the submitted content. |
| V12 File Upload | **yes** | Count, per-file size and total size enforced server-side; type verified by structure not extension; re-encode mandatory; random filenames; **stored only in PHP's temp dir and `unlink()`ed on every path**, never under `public_html/`. |
| V13 API & Web Service | **partial** | The Telegram call is outbound-only to a fixed literal host. No inbound webhook, no callback endpoint. |
| V14 Configuration | **yes** | `.htaccess` is the highest-blast-radius file in the project and gets diff review, not copy, at promotion. Five concerns, five delimited blocks (A-5). |

### Known Threat Patterns for PHP on FastCGI shared hosting

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| Malicious upload executed as code — **acute here: `.html` is mapped to PHP** | Elevation of Privilege | Never write an upload into the document root at all. Re-encode through GD. Random names. Temp dir only, `unlink()` on both paths. |
| Email header injection through a user-supplied `From:` — **the live `mailer.php:83` does exactly this** | Spoofing / Tampering | PHPMailer with a fixed literal `setFrom()`; the visitor's address goes in `Reply-To` after validation. |
| Open mail relay / spam amplification — **the live endpoint attempts a send on a bare GET** [VERIFIED: 302 response, 2026-09-17] | Denial of Service / Repudiation | Honeypot + HMAC time-trap + per-IP rate limit + `$_SERVER['REQUEST_METHOD'] === 'POST'` gate. Retire `mailer.php` at cutover. |
| Host-header injection / open redirect in the canonicalisation rule | Spoofing | Hardcoded literal target, never `%{HTTP_HOST}` — already the rule at `src/.htaccess:20-22`; the reason must survive the promotion edit. |
| Reflected XSS on the redisplay-on-error path | Tampering | `torin_esc()` on every echoed value, including in the error branch. 40+ existing call sites set the precedent. |
| Secret disclosure via a stray file in the web root | Information Disclosure | Secrets outside `public_html/`; probe file deleted and verified 404; `deploy-new.sh` name guard (P-10). |
| Resource exhaustion via large or many uploads | Denial of Service | `post_max_size`/`upload_max_filesize` bounded; explicit detection of the silently-emptied `$_POST` (P-5); per-IP rate limit. |
| `noindex` header carried to production | — (business-critical, not a classic STRIDE category) | The block is **deleted**, not commented out (C-7), and asserted absent by the D4-32 sweep. |
| Third-party script compromise (Umami) | Tampering | `defer`, never render-blocking. No SRI is possible on a vendor-updated script — accept and record. Ensure the site functions fully with the script blocked. |

## Sources

### Primary (HIGH confidence — measured or read this session)

- **Live HTTP/DNS/TLS measurement of `torin.bg` and `bell.host.bg`**, 2026-09-17 — four host variants all 200 with no redirect; `/new/` canonicalisation working in one hop across all three non-canonical variants; all four SEO-05 retirement redirects 301 → 200 in one hop; `robots.txt` and `sitemap.xml` both 404; `favicon.ico` and `google1718743335455f1c.html` both 200; `mailer.php` 302 on bare GET; `error_log`/`assets1/`/`cgi-bin/`/`covid-19/` all 403; `header.js` and `otpuska.js` both 200; `x-powered-by: PHP/5.2.17` on staging; Let's Encrypt cert `CN=torin.bg`, SAN `*.torin.bg`+`torin.bg`, `notAfter=Nov 11 2026`; SPF `v=spf1 +a +mx … +ip4:217.174.156.170 … ~all`; DKIM `default._domainkey.torin.bg` present; `_dmarc.torin.bg` absent.
- **`cloud.umami.is/script.js`**, fetched and read 2026-09-17 — 4,757 B raw / 2,333 B gzipped; `fetch(..., {keepalive:true})`, `credentials:"omit"`; `window.umami = {track, identify, getSession}`; the `data-umami-event` click handler's `preventDefault()` + `.finally()` re-navigation; `data-performance` opt-in.
- **`umami.is/pricing`**, rendered in headless Brave via `scripts/render-check.sh` 2026-09-17 — Hobby $0: 100K events/month, **1 website**, 6-month retention, custom events and event properties included; usage counts each pageview and each stored event property as one event; overage billed, not cut off; no cookie banner required.
- **`core.telegram.org/bots/api`**, fetched 2026-09-17 — `sendMediaGroup` media "must include 2-10 items"; multipart upload "10 MB max size for photos, 50 MB for other files"; `sendPhoto` ≤10 MB, w+h ≤10000, ratio ≤20, caption 0–1024; `sendMessage` text 1–4096 after entity parsing; `attach://` multipart syntax.
- **`developers.google.com/search/docs/appearance/structured-data/local-business`**, fetched 2026-09-17 — closed-all-day is `opens`/`closes` both `"00:00"`, illustrated with an explicit `Sunday` entry; seasonal closure uses `validFrom`/`validThrough` inside `openingHoursSpecification` with no `dayOfWeek`; omitting both validity properties means year-round.
- **`developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes`** — Change of Address not needed for http→https or www↔non-www; verify each property separately; consolidation takes "a few weeks or more".
- **GitHub + Packagist APIs for PHPMailer**, 2026-09-17 — v7.1.1 (2026-05-18), `php >=5.5.0`, `ext-ctype`/`ext-filter`/`ext-hash`, `src/` file list, 22,301 stars, 114.7M downloads, LGPL-2.1, not archived.
- **Direct reads of this repository**, 2026-09-17 — `src/.htaccess` (all 159 lines), `src/includes/site-config.php`, `src/includes/jsonld.php`, `src/includes/footer.php`, `src/includes/header.php` (head + nav), `src/uslovia.html`, `src/msg.html`, `site-current/mailer.php`, `scripts/deploy-new.sh`, `scripts/probes/trust-signals.js`, `src/css/components.css:376-392`, `.planning/config.json`, `.planning/REQUIREMENTS.md`, `.planning/STATE.md`, `.planning/OWNER_ANSWERS.md`, `01-URL-INVENTORY.md`.
- **Local measurement**, 2026-09-17 — `cwebp 1.5.0` conversion sample; `src/img` = 43 files / 1.3 MB / 39 jpg + 4 png; zero Zendesk references in `src/`; exactly 5 `$site['viber']` call sites; 7 `tel:` call sites; no local `php` binary.

### Secondary (MEDIUM confidence — official vendor/standards documentation, not independently re-measured)

- [help.superhosting.bg/en/set-php-version-for-directory.html](https://help.superhosting.bg/en/set-php-version-for-directory.html) — `mod_fcgid`/`FcgidWrapper` mechanism; the instruction to remove any pre-existing `AddHandler x-httpd-phpXX`; generated `php.fcgi` and `phpXX-fcgi.ini`. Updated 2023-02-09.
- [help.superhosting.bg/smtp-settings-in-script.html](https://help.superhosting.bg/smtp-settings-in-script.html) — host/port 25/auth yes/encryption none; sendmail at `/usr/sbin/sendmail`; outbound 25, 26, 465 blocked to external servers. Updated 2022-10-14.
- [help.superhosting.bg/en/php-modules.html](https://help.superhosting.bg/en/php-modules.html) — module list including `gd`, `exif`, `fileinfo`, `curl`, `openssl`, `imagick`, `mbstring`, `hash`, `ctype`, `filter`. Module changes are **account-wide**, unlike version changes. Updated 2023-02-09.
- [help.superhosting.bg/en/php5-linux-hosting.html](https://help.superhosting.bg/en/php5-linux-hosting.html) — documented version list 5.2 → 8.1. **Updated 2022-02-11 — treat as stale.**
- [php.net/manual/en/ini.list.php](https://www.php.net/manual/en/ini.list.php) — `upload_max_filesize`/`post_max_size`/`max_input_vars` = `INI_PERDIR`; **`max_file_uploads` = `INI_SYSTEM`**.
- [php.net/manual/en/configuration.file.per-user.php](https://www.php.net/manual/en/configuration.file.per-user.php) — `.user.ini` is CGI/FastCGI only; `user_ini.cache_ttl` defaults to 300 s; must be denied from public fetch.
- [schema.org/OpeningHoursSpecification](https://schema.org/OpeningHoursSpecification) and [schema.org/specialOpeningHoursSpecification](https://schema.org/specialOpeningHoursSpecification) — "open if the `opens` property is specified, and closed otherwise"; `validFrom`/`validThrough`; special hours "explicitly override general opening hours".
- [docs.umami.is/docs/track-events](https://docs.umami.is/docs/track-events), [docs.umami.is/docs/tracker-functions](https://docs.umami.is/docs/tracker-functions), [docs.umami.is/docs/cloud/faq](https://docs.umami.is/docs/cloud/faq) — `data-umami-event-*`; `umami.track(name, data)`; event-data limits (500-char strings, 50 properties); "other event listeners inside the element will not be triggered"; Hobby "completely free".
- [github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer) — "Compatible with PHP 5.5 and later"; manual installation by copying `src/` files and requiring each class.
- [support.google.com/webmasters/answer/10432366](https://support.google.com/webmasters/answer/10432366) — URL-prefix properties treat `www`/non-`www` and `http`/`https` as distinct; Domain properties unify them.

### Tertiary (LOW confidence — web search aggregation, cross-checked where possible, flagged where not)

- Secondary summaries of Umami Cloud's free tier claiming **3 websites** — **contradicted** by the official pricing page's "1 website". Recorded as a worked example of why D4-19 exists.
- [plausible.io/blog/legal-assessment-gdpr-eprivacy](https://plausible.io/blog/legal-assessment-gdpr-eprivacy) and [matomo.org ePrivacy FAQ](https://matomo.org/faq/general/eprivacy-directive-national-implementations-and-website-analytics/) — vendor-commissioned legal assessments of cookieless analytics under Art. 5(3). Useful framing, not authority.
- [EDPB Guidelines 2/2023 on the technical scope of Art. 5(3) ePrivacy](https://www.edpb.europa.eu/system/files/2024-10/edpb_guidelines_202302_technical_scope_art_53_eprivacydirective_v2_en_0.pdf) — final October 2024; broadens scope to *access*, not only storage. Cited from search results, not read in full this session.
- Aggregated guidance on layered contact-form spam defence (honeypot + time-to-submit + rate limiting) — consistent across several independent sources; the pattern is standard, the specific thresholds (~2–4 s lower bound) are conventions, not measurements.
- `createImageBitmap(… {imageOrientation:'from-image'})` as the reliable EXIF-honouring canvas route — [whatwg/html#7210](https://github.com/whatwg/html/issues/7210) and community sources. Behaviour is well established; not tested on a device this session.

## Metadata

**Confidence breakdown:**

- **Standard stack** — **HIGH.** Every library version, file list, size and limit was read from the vendor's own registry, repository or documentation this session, and the two host-side stacks (SMTP, PHP modules) come from the host's own knowledge base.
- **Architecture / cutover mechanics** — **HIGH.** The canonicalisation rule shape, the redirect behaviour, the `noindex` header, the must-carry file states, the TLS expiry and the SPF/DKIM alignment were all measured live against the real domain rather than inferred.
- **Pitfalls** — **HIGH** for P-1, P-2, P-3, P-4, P-5, P-7, P-12, P-13 (each traced to a source read or a value measured this session); **MEDIUM** for P-8, P-9, P-11 (correct in mechanism, but the consequence depends on a legal reading, on Google's behaviour, or on a threshold that is a convention).
- **Host capability** — **LOW until the C-1 probe runs.** GD, EXIF, curl, outbound 443, sendmail, the SAPI and the upload limits are all documented-as-supported but unmeasured *on this account*. This is the single largest block of uncertainty in the phase and it is closable in one request.
- **Legal / consent framing** — **LOW.** Cookieless analytics needing no consent banner is the mainstream reading and the vendor asserts it, but no EU supervisory authority has formally recognised Umami specifically, and the Art. 13 disclosure obligation (P-8) is separate and definitely owed.

**Research date:** 2026-09-17
**Valid until:** 2026-10-17 for the vendor-version facts (PHPMailer, Umami tier, Telegram limits — all fast-moving enough to re-check monthly). The live host measurements are valid until the PHP version is changed, at which point every one of them must be re-run — which is precisely what D4-02 asks for.
