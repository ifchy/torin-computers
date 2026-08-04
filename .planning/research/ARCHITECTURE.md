# Architecture Research

**Domain:** Content-heavy small-business service site (computer/laptop repair shop), static/PHP deploy to plain FTP shared hosting
**Researched:** 2026-08-04
**Confidence:** MEDIUM (cross-checked web sources on established, widely-documented patterns; one item — `.htaccess` AddType for `.html` — should be spike-verified against the live host early, see Anti-Patterns/Open Questions)

## Standard Architecture

### System Overview

```
┌───────────────────────────────────────────────────────────────────┐
│                        Browser (Visitor)                           │
│   GET /laptopi.html   GET /about.html   POST /mailer.php           │
└───────────────┬───────────────────────────────┬────────────────────┘
                 │                               │
┌────────────────┴──────────────┐   ┌────────────┴───────────────────┐
│  Static assets                │   │  Page templates (per page)      │
│  /assets/css                  │   │  index.html  laptopi.html       │
│  /assets/js                   │   │  about.html  ... (parsed as PHP)│
│  /assets/img                  │   │  each = include(header)         │
│  (no build step, plain files) │   │        + page content           │
│                                │   │        + include(footer)        │
└────────────────────────────────┘   └────────────┬───────────────────┘
                                                    │ include()
                                      ┌─────────────┴──────────────────┐
                                      │  includes/header.php            │
                                      │  includes/footer.php            │
                                      │  includes/site-config.php       │
                                      │  (phone, email, hours, nav,     │
                                      │   service list — single source) │
                                      └─────────────┬──────────────────┘
                                                     │
                                      ┌──────────────┴──────────────────┐
                                      │  mailer.php (form handler)      │
                                      │  validate → honeypot check →    │
                                      │  send (PHPMailer/SMTP) →        │
                                      │  redirect to confirmation state │
                                      └──────────────────────────────────┘
┌───────────────────────────────────────────────────────────────────┐
│         Shared hosting: bell.host.bg — cPanel + Apache + PHP        │
│         No database · No Node/server runtime · FTP-only deploy      │
└───────────────────────────────────────────────────────────────────┘
```

The current site already proves PHP execution works on this host (`mailer.php` runs and sends mail today, and `.htaccess` shows a cPanel-generated PHP handler block). That single fact is the load-bearing architectural decision for this whole project: **PHP is available at request time**, so the site does not need to be purely static, and does not need a Node.js build pipeline to solve the "repeated header/footer" problem. The lightest tool that already fits the hosting is native PHP `include()`.

### Component Responsibilities

| Component | Responsibility | Typical Implementation |
|-----------|----------------|------------------------|
| Page templates | Page-specific content only (title, body copy, service details) | One `.php` file per URL (e.g. `laptopi.php`, `about.php`), each wrapping content between an included header and footer |
| Shared layout includes | Doctype/head/nav/header markup and footer markup, identical across every page | `includes/header.php`, `includes/footer.php` — plain PHP `include()`, no templating language/DSL needed |
| Site config / "facts" file | Single source of truth for phone number, email, address, opening hours, service list, social links | `includes/site-config.php` — plain PHP variables/array, included by header/footer/contact section |
| Static assets | CSS, JS, images, fonts | `/assets/css`, `/assets/js`, `/assets/img` — no bundler required for a ~15-20 page brochure site |
| Contact form handler | Validate input, block spam, send email, show confirmation | `mailer.php` (already exists) — needs hardening, see Data Flow |
| Deploy | Move edited files from local machine to the live `public_html` | FTP client (FileZilla — already in use per `filezilla-server-data.xml`) or `lftp mirror` for scripted sync |

## Recommended Project Structure

```
torin/
├── site-current/            # existing live-site FTP mirror — reference baseline (already present)
├── src/                     # new source tree, mirrors what gets uploaded to public_html
│   ├── includes/
│   │   ├── header.php       # <head>, opening <body>, nav — one copy, included everywhere
│   │   ├── footer.php       # footer markup, shared scripts, closing tags
│   │   └── site-config.php  # phone, email, address, hours, service list, nav links, social
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   ├── index.html           # parsed as PHP (see .htaccess note below) — homepage
│   ├── laptopi.html         # laptop repair services page
│   ├── mehanichni-problemi.html
│   ├── zalivane-technosti.html
│   ├── tokov-udar.html
│   ├── za-bateriite.html
│   ├── profilaktika-laptop.html
│   ├── optimizatsiq.html
│   ├── rezervni-chasti.html
│   ├── warrently.html
│   ├── uslovia.html
│   ├── about.html
│   ├── mailer.php           # form handler (hardened version of current)
│   ├── msg.html             # confirmation/thank-you page
│   └── .htaccess            # cPanel PHP handler + AddType for .html-as-PHP
├── .planning/
└── filezilla-server-data.xml   # gitignored — real hosting credentials
```

### Structure Rationale

- **`includes/` holds only shared chrome and facts, never page-specific content.** This is the single biggest structural fix versus the current site, where the full `<head>`/header markup (~60+ lines of vendor CSS/JS links, meta tags, nav) is copy-pasted into all 15-plus HTML files. That duplication is *why* the current site is expensive to touch — updating one nav link or the phone number means editing every page by hand.
- **Page filenames keep the existing `.html` extension and URL paths** (`laptopi.html`, `about.html`, etc.) rather than renaming to `.php`. Changing extensions changes URLs, which risks losing existing search rankings and breaking any inbound links/bookmarks/QR codes already pointing at `torin.bg/laptopi.html`. cPanel hosts (which this is, per the existing `.htaccess` PHP handler block) universally support telling Apache to parse `.html` as PHP via `AddType application/x-httpd-php .html .htm` in `.htaccess`. This lets every page use `<?php include ?>` while the public URL never changes. **Verify this works on `bell.host.bg` with a one-file spike before committing the whole site to it** — it is standard cPanel behavior but should be confirmed against this specific host, not assumed.
- **`site-config.php` is deliberately just PHP variables, not a database or CMS.** For a ~15-20 page brochure site that changes rarely (phone number, hours, maybe a promo line), a single config file included everywhere gives 90% of the benefit of a CMS ("change it once, it updates everywhere") without any of the cost (no database, no login system, no software to patch).
- **`assets/` replaces the old `assets1/vendors/...` sprawl.** The current site loads jQuery UI, Font Awesome, ScrollMagic, pagePiling, Modernizr, and a full Bootstrap-era vendor CSS bundle for what is fundamentally a content site. A rebuild should trim this to what the new design actually needs — this is a phase-ordering signal, not just a folder-naming one (see Build Order below).

## Architectural Patterns

### Pattern 1: PHP-include layout (no templating engine, no build step)

**What:** Split the shared page chrome into `header.php` and `footer.php`; every page includes both and puts its own content between them.
**When to use:** Any multi-page site on PHP-capable shared hosting where a Node build pipeline is not otherwise justified. This is the primary recommendation for Torin.
**Trade-offs:** Zero new tooling, edits go live the instant the edited file is FTP-uploaded (no "build then upload" step to forget), works with the hosting exactly as it exists today. Downside: no templating conveniences (loops, conditionals feel like raw PHP, not a DSL) — irrelevant at this page count, and PHP itself already provides `if`/`foreach` when needed.

**Example:**
```php
<!-- laptopi.html (parsed as PHP via .htaccess AddType) -->
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="service-detail">
  <h1>Ремонт на лаптопи</h1>
  <!-- page-specific content only -->
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
```

```php
<!-- includes/site-config.php -->
<?php
$site = [
  'phone'   => '+359 88 ...',
  'email'   => 'office@torin.bg',
  'address' => '...',
  'hours'   => 'Пон–Пет 09:00–18:00',
  'nav'     => [
    ['label' => 'Лаптопи', 'href' => 'laptopi.html'],
    ['label' => 'Заляти течности', 'href' => 'zalivane-technosti.html'],
    // ...
  ],
];
```

```php
<!-- includes/header.php uses $site -->
<?php require __DIR__ . '/site-config.php'; ?>
<a href="tel:<?= htmlspecialchars($site['phone']) ?>"><?= htmlspecialchars($site['phone']) ?></a>
```

### Pattern 2: Hardened contact form handler

**What:** `mailer.php` validates and sanitizes input, silently rejects bot submissions via a honeypot field, sends through authenticated SMTP instead of raw `mail()`, and redirects to a confirmation state.
**When to use:** Any public-facing form on shared hosting where deliverability and spam matter — directly applicable to the existing `mailer.php`.
**Trade-offs:** A few extra lines of validation/honeypot logic cost almost nothing. Switching from bare `mail()` to PHPMailer+SMTP adds one small vendored library (no Node/Composer required — PHPMailer ships as plain PHP files) but meaningfully improves the odds the owner actually receives the message, which is the core value of the entire site per the project's "clear path to contact" goal.

**Example (illustrative, not final):**
```php
<?php
// mailer.php
if (!empty($_POST['website'])) { exit; } // honeypot field, humans leave it blank

$name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
$email   = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
$mobile  = trim(filter_input(INPUT_POST, 'mobile', FILTER_SANITIZE_SPECIAL_CHARS));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

if (!$name || !$email || !$message) {
    header('Location: index.html?form=error');
    exit;
}

// send via PHPMailer + authenticated SMTP instead of bare mail()
// ... PHPMailer setup using host SMTP creds or a transactional provider ...

header('Location: msg.html');
exit;
```

### Pattern 3: Local preview, then FTP upload (no CI/CD)

**What:** Since PHP pages can't just be opened as local files (the `<?php include ?>` won't execute), preview locally with PHP's built-in dev server (`php -S localhost:8000 -t src/`), confirm the page renders correctly, then upload only the changed files via FTP.
**When to use:** Every edit cycle, including small text/phone-number tweaks.
**Trade-offs:** Requires PHP installed locally (already true on most dev machines; trivial via Homebrew on macOS). No CI pipeline needed — the site is small enough that "build" is instantaneous (there is no build, PHP is interpreted). Manual FTP is acceptable at this scale; `lftp mirror --reverse --only-newer` is worth adopting once the file count grows, to avoid re-uploading the whole site every time and reduce the chance of overwriting a file with a stale local copy.

## Data Flow

### Contact Form Flow (the core conversion path per PROJECT.md's "Core Value")

```
Visitor fills form on any service page
    ↓ (POST)
mailer.php
    ↓ honeypot check → silently discard if bot
    ↓ validate/sanitize fields (name, email, phone, message)
    ↓ send via PHPMailer + authenticated SMTP (not bare mail())
    ↓ redirect
msg.html (confirmation shown to visitor)
    ↓ (out of band)
office@torin.bg mailbox (owner sees the inquiry)
```

This is the single most business-critical data flow on the site — PROJECT.md's Core Value is explicitly "find a clear path to contact the shop." The existing `mailer.php` already implements the mechanics (POST → PHP → `mail()` → redirect to `msg.html`) but has no spam protection and uses bare `mail()`, which is the most common cause of "the form works but the owner never sees the message" on shared hosting.

### Content Edit Flow

```
Developer/owner edits a page (.html/.php) or includes/site-config.php locally
    ↓
Preview via `php -S localhost:8000 -t src/`
    ↓
FTP upload changed file(s) only (FileZilla or lftp)
    ↓
Live immediately — no build step, no cache to invalidate beyond the browser
```

### Shared-Fact Propagation

```
includes/site-config.php  (phone, hours, nav, service list — edited once)
    ↓ included by
includes/header.php  +  includes/footer.php  +  contact section
    ↓ included by
every page
```

A phone-number or hours change is a one-file edit that propagates everywhere, instead of the current copy-paste-into-every-page pattern.

## Scaling Considerations

Traffic scale is not a meaningful concern here — this is a single local repair shop's brochure site, not a product with growth curves. The more relevant "scale" axis is **content volume over time**:

| Scale | Architecture Adjustments |
|-------|--------------------------|
| Current: ~15-20 pages, rarely-changing content | PHP includes as described — no build tool needed |
| Growth: site adds a blog/articles section (e.g. repair tips, SEO content) with dozens of posts | Reconsider a static site generator (e.g. Eleventy) at that point — looping over many content files by hand in PHP becomes tedious; an SSG's content-collection model fits better once "many similar pages" appears |
| Growth: owner wants true self-service editing (no developer involved) | Add a small, purpose-built admin form scoped to the few fields that actually change (hours, promo banner, prices) rather than installing a general-purpose CMS — see Anti-Patterns |

### Scaling Priorities

1. **First likely pressure point:** content volume, not traffic. If the redesign later adds a blog/tips section for SEO, re-evaluate the "no build tool" decision at that point — it was made for the current ~15-20 page scope, not as a permanent constraint.
2. **Second:** deliverability of the contact form as the site (hopefully) drives more inquiries — authenticated SMTP now avoids having to revisit this under pressure later.

## Anti-Patterns

### Anti-Pattern 1: Copy-pasting the full header/footer into every page (the current site's actual pattern)

**What people do:** Duplicate the entire `<head>` block, nav, and footer markup into every `.html` file — which is exactly what the current 15-plus-page site does today (each page independently repeats ~60+ lines of vendor CSS/JS links and markup).
**Why it's wrong:** Every content/nav/contact-info change requires editing every page by hand; pages drift out of sync over time (already visible in the current codebase — different pages likely carry slightly different vendor script sets).
**Do this instead:** `includes/header.php` and `includes/footer.php`, included once per page (Pattern 1 above).

### Anti-Pattern 2: Installing a full CMS (WordPress, or a flat-file CMS like Bludit/Kirby/WonderCMS) just so the owner can edit text

**What people do:** Reach for a CMS the moment "non-technical person needs to edit content" comes up.
**Why it's wrong:** For ~15-20 pages that change a handful of times a year, a CMS is a disproportionate amount of new software to install, secure, and keep patched on shared hosting — WordPress additionally needs a database this project doesn't otherwise require, and even flat-file CMS options add an admin login surface and ongoing update burden for a site that barely changes.
**Do this instead:** Keep frequently-changing facts (phone, hours, promo text) in `site-config.php` with clear Bulgarian comments; if genuine self-service editing is wanted later, build a minimal purpose-scoped admin form (a single password-protected PHP page editing 3-5 known fields) rather than a general CMS. Treat this as a possible future phase, not a v1 requirement — PROJECT.md phrases it as "eventually," not a current mandate.

### Anti-Pattern 3: Sending contact-form mail via bare PHP `mail()` with no spam protection

**What people do:** Call `mail()` directly (as the current `mailer.php` does) and assume it works because no error is thrown.
**Why it's wrong:** Shared-hosting mail servers are frequently misconfigured or reputation-poor, so messages silently land in spam or get dropped — the owner never knows an inquiry was lost. No honeypot also means the form is an open target for spam bots.
**Do this instead:** Honeypot hidden field + server-side validation (cheap, no user friction) and PHPMailer with authenticated SMTP instead of raw `mail()` (Pattern 2 above).

### Anti-Pattern 4: Introducing a Node/SSG build step without a matching deploy discipline

**What people do:** Adopt a static site generator for the "modern" workflow, then forget to rebuild before FTP-uploading, or upload the source templates instead of the built output.
**Why it's wrong:** Causes stale-content bugs (edited the source, forgot to rebuild) or leaks template source files to the public directory.
**Do this instead:** If the project later moves to an SSG (e.g. because content volume grows — see Scaling), pair it with an explicit, written deploy checklist ("build → verify `_site`/output changed → FTP-upload only that output folder") from day one. Not a concern for the recommended PHP-include approach, since there is no separate build output to keep in sync.

### Anti-Pattern 5: Treating the live FTP directory as the source of truth

**What people do:** Edit files directly via cPanel's File Manager or FTP client on production, with no local copy or version history.
**Why it's wrong:** No way to know what changed, no rollback, and local/remote drift accumulates (this appears to already be a risk given the site has evidently been hand-edited over years).
**Do this instead:** Local git repo (already established — `site-current/` is checked into git) is the source of truth; FTP is a one-way deploy target, never edited directly except in a genuine emergency, and any emergency edit gets pulled back into the local repo immediately after.

## Integration Points

### External Services

| Service | Integration Pattern | Notes |
|---------|---------------------|-------|
| `office@torin.bg` mailbox | Contact form target via `mail()`/PHPMailer from `mailer.php` | Core conversion path — deliverability matters most here |
| Google Fonts (`fonts.googleapis.com`, Barlow) | `<link>` in `header.php` | Currently loaded from Google CDN; fine to keep, or self-host for a small perf/privacy gain — low priority |
| Zendesk widget (`static.zdassets.com`) | `<script>` tag currently present on ~16 pages | Decide during redesign whether this stays (chat widget) or is dropped — carries external JS weight; not an architecture blocker either way |
| Google Maps (embedded on `index.html` today) | `<iframe>` embed | Keep for the "find us" / local-business trust signal; no architectural complexity |
| Google Analytics / Search Console | Not currently present on the site (checked — no `gtag`/`googletagmanager` found) | Flagged here as a gap for the FEATURES/roadmap phase, not an architecture decision — adding it is a one-line snippet in `header.php` once `site-config.php`-style shared includes exist |

### Internal Boundaries

| Boundary | Communication | Notes |
|----------|---------------|-------|
| Page templates ↔ `includes/header.php` / `footer.php` | PHP `include()` | One-directional: pages pull in shared chrome, never the reverse |
| Page templates ↔ `includes/site-config.php` | PHP `include()`/`require()`, reads `$site` array | Any page needing phone/hours/nav reads from this one file |
| Contact form markup ↔ `mailer.php` | HTML `<form method="post" action="mailer.php">` | Standard form POST, no JS/AJAX required (progressive-enhancement-friendly; can add AJAX later without changing the backend contract) |
| `mailer.php` ↔ SMTP/mail transport | PHPMailer library call | Swap-in library, no change to the form's HTML contract |
| Local repo ↔ live host | FTP upload (FileZilla / `lftp`) | One-directional, manual trigger, no CI — see Deploy Workflow above |

## Deploy Workflow Summary (FTP/shared-hosting constraint)

This is the explicit constraint the whole architecture is designed around, so it's worth stating plainly:

- **Host:** `bell.host.bg`, cPanel-managed shared hosting, Pure-FTPd for file transfer, Apache+PHP for serving (confirmed: `mailer.php` runs today; `.htaccess` already contains a cPanel-generated PHP handler block).
- **No Node.js, no server-side runtime beyond PHP, no SSH/rsync confirmed available** (FTP is the documented access method) — this rules out any workflow that assumes a remote build step or process manager.
- **No staging environment** — it's a single shared-hosting account with one `public_html`. For a big-bang redesign cutover, consider uploading the new site to a subfolder (e.g. `public_html/staging/`) protected by `.htaccess` basic auth for owner/stakeholder review before swapping it into place, rather than editing the live site in place.
- **Recommended cadence:** build/edit locally → preview with `php -S localhost:8000 -t src/` → FTP-upload only changed files (FileZilla, already the team's tool of choice per `filezilla-server-data.xml`) → spot-check the live URL.
- **Before the first redesign upload:** the existing `site-current/` FTP mirror already serves as a backup/baseline (per PROJECT.md's Key Decisions) — keep it untouched as a rollback reference until the redesign is verified live.
- **Do not change URL paths/extensions** as part of adopting PHP includes — use the `.htaccess` `AddType`-for-`.html` approach (spike-verify against `bell.host.bg` first) so `torin.bg/laptopi.html` keeps working exactly as it does today.

## Sources

- [Deployment — Eleventy](https://www.11ty.dev/docs/deployment/) — confirms SSGs like 11ty need Node only as a build dependency; output is plain static files uploadable to any FTP host (MEDIUM confidence, web search, cross-checked across multiple results)
- [Deploying An 11ty Project To Shared Hosting](https://flamedfury.com/posts/deploying-an-11ty-project-to-shared-hosting/) — practical FTP-based SSG deploy pattern
- [PHP include header/footer best practice — Treehouse Community](https://teamtreehouse.com/community/php-include-headerfooter-best-practice) — baseline PHP include pattern for shared hosting without a build step
- [The Simplest Ways to Handle HTML Includes — CSS-Tricks](https://css-tricks.com/the-simplest-ways-to-handle-html-includes/) — survey of no-build-tool include approaches, including the `.html`-parsed-as-PHP caveat on shared hosting
- [Contact-Form-PHP (GitHub) — honeypot + PHPMailer + SMTP reference implementation](https://github.com/raspgot/Contact-Form-PHP)
- [PHP: Spam Prevention Using Honeypot Method](https://mattkomarnicki.com/articles/spam-prevention-using-honeypot-method)
- [PHP mail() Alternative: 5 Better Ways to Send Email](https://splitforms.com/blog/php-mail-alternative) — documents why bare `mail()` on shared hosting has poor deliverability
- [Sending Emails in PHP: PHPMailer vs mail() Function](https://dev.to/minima_desk_cd9b151c4e2fb/sending-emails-in-php-phpmailer-vs-mail-function-1c28)
- [Best Flat File CMS for Simple and Efficient Websites](https://typemill.net/knowledge-hub/flat-file-cms) and [9 best flat-file CMS in 2026 — Tiiny Host](https://tiiny.host/blog/best-flat-file-cms/) — survey used to justify *not* adopting a flat-file CMS at this scale (Anti-Pattern 2)
- [Deploy a static website with FTP — Sylvain Durand](https://sylvaindurand.org/deploying-a-static-website-with-ftp/) — `lftp mirror` workflow reference
- Direct inspection of `site-current/` (this project's own FTP mirror of the live site) — confirmed: per-page duplicated header/footer markup, bare `mail()` usage in `mailer.php`, existing cPanel-generated `.htaccess` PHP handler block, no Google Analytics/Search Console snippet present (HIGH confidence — primary source, the actual live site)

---
*Architecture research for: content-heavy small-business service site, static/PHP + FTP shared hosting*
*Researched: 2026-08-04*
