<!-- GSD:project-start source:PROJECT.md -->

## Project

**Torin Computers Website Redesign**

A redesign of torin.bg — the website for ТОРИН КОМПЮТЪРС, a Bulgarian computer/laptop/electronics repair shop. The current site is a static HTML page built years ago on a purchased jQuery/Bootstrap template ("Liquid") and looks visibly outdated next to competitors. This project modernizes the look and feel, closes feature/content gaps versus the top Bulgarian repair-shop competitors, and keeps the site fully in Bulgarian.

**Core Value:** A visitor with a specific problem (cracked screen, spilled liquid, dead motherboard, drained battery...) must immediately see that Torin fixes exactly that, and find a clear path to contact the shop. If the redesign doesn't make that obvious faster than the competition does, it hasn't worked.

### Constraints

- **Language**: All content must be in Bulgarian — no other language versions in scope
- **Hosting**: Must deploy to the existing FTP/shared hosting at `bell.host.bg` — no infrastructure migration
- **Content emphasis**: The six service categories listed under Active Requirements must be prominently featured — this was explicit owner direction, not inferred

<!-- GSD:project-end -->

<!-- GSD:stack-start source:research/STACK.md -->

## Technology Stack

## Recommendation: Reskin vs Full Rebuild

| | Reskin in place | Full rebuild (Astro) |
|---|---|---|
| Speed to ship | Faster for a single visual pass | Slower up front (new project scaffold) |
| Fixes 17-page duplication | No — problem persists | Yes — one layout component |
| Ceiling on "modern" | Limited by template's HTML/CSS bones | High — clean semantic HTML, modern CSS |
| Performance/Core Web Vitals | Constrained by jQuery/ScrollMagic/pagePiling cruft still loaded | Near-zero JS by default, fast by construction |
| New tooling to learn | None (edit HTML/CSS/JS directly, same as today) | Node.js + npm build step (dev-time only) |
| FTP deploy | Unchanged — same as today | Unchanged in kind — still upload static files, via FileZilla or CI |
| Ongoing content edits | Edit HTML directly, still duplicated across pages | Edit a single Markdown/`.astro` file per page — no duplication |
| Best when | Timeline/budget is the hard constraint and duplication is tolerable | Redesign is meant to last and reduce future maintenance cost |

## Recommended Stack

### Core Technologies

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| Astro | 7.1.x (7.1.6 latest as of late July 2026) | Static site generator / build tool | Purpose-built for content-heavy, mostly-static sites like this one. Ships zero JS by default ("islands" only where needed), has first-class Markdown/content-collections support for per-service-page content, built-in image optimization at build time (no server needed), and its default/static output mode compiles to plain files deployable to any static host including FTP shared hosting. Explicitly documented deploy path to FTP/shared hosting (`astro build` → upload `dist/` contents to `public_html/`). |
| Tailwind CSS | 4.1.x / 4.3 minor (current stable major: v4) | Utility-first CSS framework | Compiles to a single static CSS file at build time — no runtime dependency, perfect fit for static hosting. v4's CSS-first config and Rust-based (Oxide) engine make builds fast and the config approachable. Directly replaces the Bootstrap 4 grid/utility classes in the current template with a smaller, purpose-built output (only the classes actually used ship to production). |
| Node.js | 22 LTS or newer (Astro 6/7 require Node ≥22.0.0 — dropped 18.x/20.x support) | Build-time tooling only | **Not required on the host.** Node runs only on the developer's machine or in CI to produce the static `dist/` output that gets FTP-uploaded. The shared-hosting constraint in this project ("no Node/server-side runtime available on the host") is about the production server, not the build machine — this distinction is the crux of why an SSG is still viable here. |
| PHP | Whatever version `bell.host.bg` currently runs (already proven working via `mailer.php`) | Contact form submission handling | The host already supports PHP — confirmed by the live site's working `mailer.php`. Reuse this rather than adding a third-party form SaaS: zero new dependency, zero new account/API key to manage, keeps the trust boundary (visitor data) entirely on the business's own infrastructure. |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| Alpine.js | v3.x current stable line (~7–15KB gzipped depending on plugins) | Small interactive islands (mobile nav toggle, FAQ accordions, image lightbox) | Use instead of jQuery for the handful of interactive widgets the redesign needs. No build step of its own, HTML-attribute-driven, drops in as a single `<script>` tag — a near drop-in mental model for whoever previously touched the jQuery-based interactivity in the Liquid template, but far smaller and not deprecated. Astro's own component islands may make this unnecessary for anything componentized in `.astro` files — reach for Alpine only for truly page-level vanilla-HTML interactivity (e.g. inside a Markdown-rendered page). |
| `@astrojs/sitemap` | Latest matching Astro 7.x | Automatic `sitemap.xml` generation | Add once, at build time, for all statically-generated routes — important for local SEO discoverability. (Note: this integration has a known limitation with SSR/`output:"server"` mode where it can't see content-collection routes — irrelevant here since this project should use `output: 'static'` throughout.) |
| `astro:assets` (built-in, uses Sharp under the hood) | Bundled with Astro core | Image optimization/responsive images at build time | Replaces manual image resizing/compression. Runs entirely at build time on the dev machine — output is plain optimized `<img>`/`<picture>` markup, no server-side image processing needed at runtime, so it's fully compatible with static FTP hosting. Important for repair-shop photos (before/after, shop photos) which are often uploaded oversized. |
| PHPMailer | 6.x | Reliable authenticated SMTP email sending from `mailer.php` | Recommended upgrade over raw PHP `mail()`. Shared-hosting `mail()` calls are frequently flagged as spam or silently dropped by receiving mail servers because they lack proper SPF/DKIM alignment; PHPMailer + authenticated SMTP (e.g. the hosting provider's own SMTP relay, or a transactional email service) meaningfully improves inquiry deliverability — directly serves the core value ("visitor... finds a clear path to contact the shop"), since a lead that vanishes because an email got spam-filtered is a lost customer. |
| JSON-LD `LocalBusiness`/`ElectronicsStore` schema | schema.org (no library — hand-authored JSON-LD block per page or one shared partial) | Local SEO structured data | Add a JSON-LD script block (name, address, phone, geo, openingHours, areaServed, sameAs → Google Business Profile) to the site layout. Use the most specific schema.org type available (e.g. `ElectronicsStore` or `ProfessionalService` rather than generic `LocalBusiness`) per Google's own guidance. This is markup, not a package — trivial to hand-author once and reuse via the shared Astro layout. |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| FileZilla (already in use) | Manual FTP deploy of the built `dist/` folder | Keep using it for the first deploy and for one-off manual pushes — zero new tooling to learn for the simplest workflow. Confirmed as already the project's FTP client (`filezilla-server-data.xml` in repo root, gitignored). |
| GitHub Actions + `SamKirkland/FTP-Deploy-Action` | Optional automated deploy on push to main | The standard, widely-used GitHub Action for syncing a local build output to shared hosting over FTP/FTPS, uploading only changed files. Worth adding once the rebuild is stable, so future content edits (a Markdown file change) auto-deploy without a manual FileZilla step. Use FTPS not plain FTP for encrypted credentials in transit; store host/user/pass as GitHub Secrets; double-check the `server-dir` target matches `public_html` to avoid a "green" workflow that silently deploys to the wrong folder. |
| VS Code (or any editor) | Editing `.astro`/Markdown content and Tailwind classes | No special IDE requirement; Astro has a first-party VS Code extension for `.astro` syntax highlighting/IntelliSense. |

## Installation

# Scaffold a new Astro project (interactive)

# Core additions once scaffolded

# Optional: small interactivity islands

# Dev dependency (already bundled with astro create, listed for clarity)

# Build for FTP deploy — output lands in dist/

# PHP side (server, no npm involved) — upgrade mailer.php's mail sending

# (or vendor the library directly if Composer isn't available on the shared host —

#  common on budget shared hosting; download PHPMailer's release zip and include it manually)

## Alternatives Considered

| Category | Recommended | Alternative | Why Not (or: when it's the better call) |
|----------|-------------|-------------|-------------------------------------------|
| Static site generator | Astro 7.x | Eleventy (11ty) 3.1.x | Eleventy is a perfectly valid, even simpler, choice — plain templating (Nunjucks/Liquid/Markdown) with no component/JS framework baked in at all, arguably an even better fit for a JS-light brochure site. Astro is preferred here because its content-collections model, built-in image pipeline, and component islands give a cleaner authoring experience for ~17 structured service pages, and its ecosystem/integrations (sitemap, SEO helpers) are more turnkey. If the team strongly prefers to avoid any JSX-like `.astro` component syntax and wants the absolute simplest mental model, Eleventy is a solid MEDIUM-confidence alternative. |
| Static site generator | Astro 7.x | Hugo | Hugo (Go-based) is extremely fast and also FTP-deployable, but its templating language (Go templates) and content model are a steeper, less transferable learning curve for a small project with likely occasional maintenance by a web developer rather than a Go specialist. Not recommended unless the team already knows Hugo. |
| CSS approach | Tailwind CSS v4 | Bootstrap 5 (upgrade path from the current Bootstrap-era template) | Bootstrap 5 is a legitimate, lower-effort choice if the reskin-in-place path is chosen, since it's conceptually closest to what's already there (drop jQuery dependency, most of Bootstrap 4→5 is a class-rename exercise) and the current template's grid/components map fairly directly. Tailwind is preferred for the full-rebuild path because it produces smaller, more purpose-built CSS and pairs naturally with Astro's component model, but it requires learning utility-class conventions from scratch. |
| Content editing model | Markdown/`.astro` files edited by the developer, deployed via FTP (manual or CI) | Git-based CMS (Sveltia CMS, successor to the now-unmaintained Decap CMS) giving a non-technical owner a browser-based content editor | Not recommended as an initial-phase requirement — PROJECT.md doesn't specify the shop owner will personally edit code/content going forward, and this class of site (a ~17-page brochure site for a repair shop) has a low content-change cadence (prices, hours, an occasional new service line). A Git-based CMS adds real setup complexity (GitHub OAuth app or a Cloudflare Worker auth proxy) for a low-frequency editing need. Flag this as a candidate **future phase** if the owner later asks for true self-service editing without involving a developer. |
| Contact form backend | Existing PHP `mailer.php`, hardened (honeypot + PHPMailer/SMTP) | Formspree / Web3Forms (form-backend-as-a-service) | Both are solid no-backend options for a purely static site with zero server-side capability, but this host already runs PHP successfully — reusing it avoids a third-party dependency, monthly submission caps (Web3Forms free tier: 250/mo), and an extra account to manage. Keep Formspree/Web3Forms in your back pocket only as a Plan B if PHP `mail()`-based deliverability from this specific shared host proves unreliable after launch. |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| jQuery + jQuery UI + ScrollMagic + pagePiling (current template's vendor stack) | Heavy, dated dependency chain; ScrollMagic/pagePiling implement scroll-hijacking full-page-slide effects that actively harm mobile usability, accessibility, and Core Web Vitals (a known modern anti-pattern for content sites) — exactly the kind of thing that makes a site "feel outdated" even after a visual refresh | Alpine.js for the few genuinely interactive widgets; plain CSS transitions/scroll-snap for any scroll effects, used sparingly |
| Decap CMS (formerly Netlify CMS) | No longer actively maintained by Netlify; community activity has slowed significantly — a real risk to adopt fresh in 2026 | If a Git-based CMS is wanted later, use Sveltia CMS (actively maintained, config-compatible successor) instead |
| Raw PHP `mail()` for the contact form | Frequently flagged as spam or silently dropped by receiving mail servers on shared hosting because it lacks proper SPF/DKIM alignment — directly undermines the site's core value (visitor inquiries reaching the shop) | PHPMailer with authenticated SMTP, plus a honeypot field for spam filtering |
| A full JS framework with SSR/SSG needing a persistent Node server (Next.js in server mode, Nuxt in SSR mode, SvelteKit with a Node adapter) | These require a running Node process on the host to serve pages dynamically — explicitly incompatible with plain-FTP/no-server-runtime shared hosting unless configured for full static export, which defeats the purpose of choosing them over Astro/Eleventy in the first place | Astro or Eleventy in static output mode, which are purpose-built for "build once, upload static files" |
| WordPress (or any other database-backed CMS) | Requires MySQL + PHP app runtime, ongoing plugin/core security patching, and a fundamentally different (and heavier) hosting/maintenance model than what a 17-page static brochure site needs; also a common source of the exact "why does this look like every other small-business WordPress site" problem this redesign is trying to escape | Astro/Eleventy static rebuild, or the PHP-include reskin path |
| Full page rebuild in raw HTML with continued copy-paste of header/nav/footer across every page (i.e. "reskin without fixing the duplication") | Perpetuates the current site's core maintainability defect — every nav/footer/contact-info change has to be manually repeated across ~17 files, and it's easy to miss one, causing silent inconsistency (a phone number changed on 16 pages but not the 17th) | Either the Astro rebuild (component-based layout) or, at minimum, a PHP-include refactor (`header.php`/`footer.php`) if staying in the reskin path |

## Stack Patterns by Variant

- Astro 7.x, static output mode (`output: 'static'` explicit in `astro.config.mjs`)
- Tailwind CSS v4 via `@astrojs/tailwind`
- Each of the six emphasized service categories (breakage repair, screen/keyboard/USB-port/hinge replacement, optimization, liquid/motherboard damage, fan replacement, non-standard electrical equipment) as a structured content-collection entry or dedicated `.astro` page — enables consistent per-service CTAs and easy addition of new service pages later
- `@astrojs/sitemap` + hand-authored JSON-LD `ElectronicsStore`/`ProfessionalService` schema in the shared layout
- Contact form: plain HTML `<form action="mailer.php" method="post">` pointing at the existing (hardened) PHP endpoint on the same host — Astro's static pages can `POST` to any same-origin PHP script without needing an Astro server adapter
- Deploy: `npm run build` locally, then FTP-upload `dist/` contents to `public_html/` (manually via FileZilla initially; optionally automate later with `FTP-Deploy-Action`)
- Because [Bulgarian is the only language](file:///Users/alabala/Documents/projects/torin/.planning/PROJECT.md) required, skip Astro's i18n routing entirely — keep URL structure flat and simple
- Refactor the existing 17 HTML pages into PHP includes first (`header.php`, `nav.php`, `footer.php`), renaming pages to `.php` — this alone removes the multi-file duplication problem with zero new tooling
- Replace jQuery/jQuery UI/ScrollMagic/pagePiling with Bootstrap 5 (class-rename migration from the current Bootstrap-era markup) + Alpine.js for any remaining interactive widgets
- Keep and harden the existing `mailer.php` (honeypot + PHPMailer/SMTP)
- Deploy: unchanged — continue editing files directly and FTP-uploading via FileZilla, same workflow as today
- Accept that this path does not achieve the visual/technical ceiling the full rebuild does, and that future content edits still touch multiple files for any shared-element change

## Version Compatibility

| Package A | Compatible With | Notes |
|-----------|-----------------|-------|
| Astro 7.1.x | Node.js ≥22.0.0 | Astro 6 dropped Node 18.x/20.x support (needs Vite 7 worker-thread improvements and Node 22's native fetch); confirm the developer's local/CI Node version is 22 LTS or newer — this has zero bearing on the host, which never runs Node |
| Astro 7.x | Tailwind CSS v4 via `@astrojs/tailwind` | Standard, well-documented integration pairing; no known incompatibilities as of this research date |
| `@astrojs/sitemap` | Astro `output: 'static'` | Works correctly for statically-generated routes including content-collection pages. Known limitation: cannot discover content-collection routes when `output: 'server'` (SSR) is used — irrelevant here since this project should stay fully static |
| PHPMailer 6.x | PHP version already running on `bell.host.bg` | Verify the host's PHP version (check via a `phpinfo()` probe or hosting control panel) before assuming Composer availability — many budget shared hosts support PHP but not CLI Composer; PHPMailer can be vendored manually (download + include) if Composer isn't available |

## Sources

- [Astro.js releases: all versions and changelog — AstroBuild](https://astrobuild.eu/en/releases) — MEDIUM confidence (web search, cross-referenced with GitHub releases and Astro blog)
- [Astro 7.0 blog post — astro.build](https://astro.build/blog/astro-7/) — MEDIUM confidence
- [How to Deploy an Astro Site to FTP and Shared Hosting — Production Starter](https://productionstarter.dev/blog/how-to-deploy-astro-to-ftp) — MEDIUM confidence, directly confirms the FTP deploy path this recommendation relies on
- [Deploy your Astro Site to Hostinger — Astro Docs](https://docs.astro.build/en/guides/deploy/hostinger/) — MEDIUM confidence, official docs analog for shared-hosting deploy pattern
- [Eleventy Release History — 11ty.dev](https://www.11ty.dev/docs/versions/) — MEDIUM confidence
- [Eleventy Deployment docs — 11ty.dev](https://www.11ty.dev/docs/deployment/) — MEDIUM confidence, confirms FTP-to-any-static-host deploy model
- [Tailwind CSS Blog — tailwindcss.com](https://tailwindcss.com/blog) — MEDIUM confidence
- [6 Best Decap CMS Alternatives in 2025 — daily.dev](https://daily.dev/posts/7ntqnrkpb) — MEDIUM confidence, corroborated across multiple independent listicles on Decap's maintenance status
- [SamKirkland/FTP-Deploy-Action usage discussion — InMotion Hosting / Medium](https://www.inmotionhosting.com/support/website/git/deploy-files-github-actions/) — MEDIUM confidence
- [Web3Forms vs Formspree comparisons — wmtips.com, splitforms.com](https://www.wmtips.com/technologies/compare/formspree-vs-web3forms/) — MEDIUM confidence
- [Local Business Schema Best Practices — SMA Marketing](https://www.smamarketing.net/blog/local-business-schema-best-practices) — MEDIUM confidence
- [Local SEO Schema: Complete Guide — Search Engine Journal](https://www.searchenginejournal.com/how-to-use-schema-for-local-seo-a-complete-guide/294973/) — MEDIUM confidence
- [Alpine.js in 2026 — DEV Community](https://dev.to/sahilkhurana/alpinejs-in-2026-the-7-kb-script-tag-that-replaced-jquery-and-when-to-actually-use-it-1129) — MEDIUM confidence
- PHP include-based templating pattern (header.php/footer.php via `require_once`) — MEDIUM confidence, standard/well-established pattern corroborated across multiple developer-forum sources (SitePoint, Treehouse)
- PHP honeypot + PHPMailer/SMTP contact-form hardening — MEDIUM confidence, corroborated across multiple sources (PHPpot, dev.to, OSTraining)
- Flat-file CMS options (SiteCake, Kirby) for non-technical static-site editing — MEDIUM confidence; noted as a deferred/optional future-phase consideration, not part of the core stack recommendation
- Astro Node.js ≥22 requirement (Astro 6/7 dropped Node 18.x/20.x) — MEDIUM confidence, single-search corroboration; **recommend re-verifying against official Astro docs at implementation time** since Node version requirements change with each Astro major release
- Direct inspection of `/Users/alabala/Documents/projects/torin/site-current/` (17 HTML pages, `mailer.php`, no `package.json`/build tooling present) — HIGH confidence, primary-source project inspection

<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->

## Conventions

Conventions not yet established. Will populate as patterns emerge during development.
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->

## Architecture

Architecture not yet mapped. Follow existing patterns found in the codebase.
<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->

## Project Skills

No project skills found. Add skills to any of: `.claude/skills/`, `.agents/skills/`, `.cursor/skills/`, `.github/skills/`, or `.codex/skills/` with a `SKILL.md` index file.
<!-- GSD:skills-end -->

<!-- GSD:workflow-start source:GSD defaults -->

## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:

- `$gsd-quick` for small fixes, doc updates, and ad-hoc tasks
- `$gsd-debug` for investigation and bug fixing
- `$gsd-execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->

## Developer Profile

> Profile not yet configured. Run `$gsd-profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
