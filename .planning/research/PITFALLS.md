# Domain Pitfalls

**Domain:** Small-business (local repair shop) website redesign — SEO-sensitive migration, Bulgarian/Cyrillic content, plain-FTP shared-hosting deploy, no CI/CD
**Researched:** 2026-08-04
**Confidence:** MEDIUM overall (HIGH for findings grounded in direct inspection of `site-current/`; MEDIUM for general web-research findings, cross-corroborated across multiple independent sources per topic)

This file is grounded in two things: (1) direct inspection of the live site's downloaded mirror at `site-current/` (215 files pulled via FTP), and (2) web research on SEO-migration, Cyrillic-web, FTP-deploy, and local-business-site pitfalls. Findings tagged **[VERIFIED IN SITE-CURRENT]** are facts about torin.bg today, not general theory — treat those as the highest-priority items.

## Critical Pitfalls

### Pitfall 1: Redesign silently breaks URLs and kills existing rankings/backlinks

**What goes wrong:**
The redesign changes filenames, folder structure, or introduces a new templating/build system, and pages that used to live at `laptopi.html`, `za-bateriite.html`, `tokov-udar.html`, etc. either disappear, get renamed, or start returning different content at the same URL without anyone mapping the change. Google has indexed these exact URLs for years; when they 404 or silently change meaning, the site loses the ranking signal and any external backlinks pointing at them go dead.

**Why it happens:**
Redesigns are approached as a visual/content project, not a migration project. Nobody writes down "here is the full list of URLs Google currently knows about" before starting, so there's nothing to check the new site against at the end.

**Consequences:**
Ranking and traffic drops that can take months to recover, loss of any backlink equity from Bulgarian directories/forums/partners that link to specific pages (e.g. a forum post linking directly to `zalivane-technosti.html`), and Google Search Console reporting a spike in 404s / "not found" for previously-indexed URLs.

**Prevention:**
- Before any redesign work starts, freeze a complete URL inventory: crawl `site-current/` (16 HTML pages: `index`, `about`, `laptopi`, `profilaktika-laptop`, `optimizatsiq`, `mehanichni-problemi`, `za-bateriite`, `tokov-udar`, `zalivane-technosti`, `rezervni-chasti`, `warrently`, `uslovia`, `covid`, `test-laptop`, `problem-stari`, `msg`) and cross-check against Google Search Console's "Pages" report for URLs Google actually has indexed (the live set may differ slightly from the FTP mirror — e.g. `test-laptop.html` / `problem-stari.html` may or may not be linked/indexed).
- Decide the new site's URL for every one of those existing paths before writing any redirect. If a page's content is merged into another page in the new IA, redirect it to the closest topical match, not the homepage.
- If URLs change at all (even just the extension, e.g. `laptopi.html` → `/laptopi/`), implement 301 (permanent) redirects for every old → new mapping, not 302. This preserves ~90-99% of link equity when done as a clean 1:1 map; a homepage catch-all redirect preserves far less.
- **[VERIFIED IN SITE-CURRENT]** Current slugs are already transliterated Latin (not Cyrillic) — this matches the researched SEO best practice for Cyrillic-language sites (see Pitfall 4) and is worth *keeping*, not "modernizing" into Cyrillic URLs.

**Warning signs:**
- No written old→new URL mapping exists before the new site is built.
- Nobody has pulled the current list of indexed URLs from Google Search Console.
- Post-launch: Search Console "Pages" report shows a rising count of "Not found (404)" for previously "Indexed" URLs; Search traffic in GSC Performance drops noticeably in the two weeks after launch.

**Phase to address:** Pre-launch/migration-planning phase (before any content/template rebuild), plus a dedicated redirect-implementation step immediately before cutover.

---

### Pitfall 2: `.htaccess` redirects and Google verification file get lost during FTP cutover

**What goes wrong:**
**[VERIFIED IN SITE-CURRENT]** The current `.htaccess` only contains a cPanel-generated PHP handler stub (no redirect rules today), and there is a Google Search Console site-verification file (`google1718743335455f1c.html`) sitting at the site root. Neither of these is "content" a designer would think to carry over — they're exactly the kind of file that gets skipped when someone manually re-uploads a new set of pages over FTP, because nobody remembers they exist or why.

**Why it happens:**
FTP deploys are visual/file-by-file, not declarative. There's no manifest of "these files must exist regardless of redesign" — the deployer just uploads whatever's in the new build folder, and anything not in that folder that isn't explicitly noticed gets left alone (lucky) or overwritten/removed (unlucky) depending on whether the deploy is additive or a full folder swap.

**Consequences:**
Losing the Search Console verification file means the account holder can no longer manage the property in GSC (submit sitemaps, view real ranking data, request re-indexing) until re-verified through another method. Losing/overwriting `.htaccess` without carrying forward the redirect rules planned in Pitfall 1 undoes the whole redirect strategy at the exact moment it matters most (cutover).

**Prevention:**
- Before touching anything, download and archive the current `.htaccess`, the Google verification HTML file, `robots.txt` (currently absent — see Pitfall 8), and `favicon.ico`/`.well-known/` contents as a fixed "must-carry" checklist.
- Treat `.htaccess` as an append target, not a file to regenerate from scratch: the new redirect rules from Pitfall 1 get added to it, cPanel's auto-generated PHP handler block stays untouched.
- Re-verify Search Console ownership via an alternate method (DNS TXT record or Google Analytics/Tag Manager verification) as a belt-and-suspenders step, independent of the file staying in place.

**Warning signs:**
- The deploy process is "delete everything in `public_html` and upload the new build" rather than a reviewed file-by-file diff.
- Nobody has a checklist of root-level non-content files to preserve.
- Post-launch: Search Console shows "Ownership verification failed" or the property becomes inaccessible.

**Phase to address:** Deploy/cutover phase — build a "must-carry root files" checklist as part of the deployment plan, verified against the pre-redesign FTP mirror already pulled into `site-current/`.

---

### Pitfall 3: FTP-only deploy with no CI/CD leaves no rollback path when something breaks

**What goes wrong:**
Deploys happen by hand-uploading files over FTP directly to the live `public_html` on shared hosting. If a page is half-uploaded when the connection drops, if a new page references a CSS/JS asset that wasn't uploaded yet, or if a bad file overwrites a working one, the live site breaks — visibly, for real visitors — with no automated way to detect it and no one-click way to go back to the last-known-good state.

**Why it happens:**
Plain FTP to shared hosting has no built-in versioning, no atomic deploy (uploads happen file-by-file, not as one all-or-nothing swap), and no diff/audit trail of what changed. This is an explicit, accepted constraint of the project (no hosting migration), not a mistake to "fix" by switching hosts — but it means the *process* around FTP needs to compensate for what the tooling doesn't provide.

**Consequences:**
Visible breakage for real visitors and search crawlers during the exact window competitors could be indexed instead; no fast way to know "what changed" when something looks wrong two days after a deploy that touched a dozen files; potential for a genuinely lost site if a full-folder overwrite goes wrong and there's no backup.

**Prevention:**
- Keep the redesigned site under git version control locally (the plan already does this — `site-current/` is committed as baseline) even though the *deploy target* is FTP. Git gives the audit trail and diff-ability that FTP itself doesn't; FTP just becomes the "publish" step, not the source of truth.
- Before every deploy, pull a fresh full FTP mirror of the current live `public_html` into a timestamped local backup folder — this is the actual rollback mechanism when there's no hosting-level staging/rollback feature. (The project already has a working FTP-mirror pull script/workflow from initial research — reuse it as a pre-deploy backup step, every time, not just once.)
- Deploy the full new build as a complete, tested set in one FTP session rather than trickling individual file edits onto the live site over multiple sessions; test the complete build locally (open it from a local server, not `file://`, to catch relative-path issues) before uploading anything.
- Where the hosting panel supports it, stage the new site in a subfolder or subdomain with `robots.txt` disallow / `noindex` set, do a final click-through there, then move it into place — this approximates a staging environment without needing new infrastructure.

**Warning signs:**
- Deploys happen ad hoc, file by file, without a documented "this deploy touches these N files" list.
- No local backup of the live site exists from immediately before the most recent deploy.
- The only source of truth for "what the live site currently looks like" is the live site itself (i.e., if it broke right now, there'd be no fast way to know what it looked like an hour ago).

**Phase to address:** Deployment/tooling-setup phase, established once and reused for every subsequent deploy, including post-launch content edits.

---

### Pitfall 4: Cyrillic-specific rendering/SEO issues introduced by new fonts, templates, or tooling

**What goes wrong:**
A new visual design typically means new webfonts, new icon sets, and sometimes a new build tool. Any of these can silently mishandle Cyrillic: a trendy display font licensed/bundled as Latin-only renders Bulgarian text as tofu boxes or falls back to a generic system font that clashes with the rest of the design; a build tool or editor saves a file in the wrong encoding and re-introduces mojibake (маймуница) in body copy; and — **[VERIFIED IN SITE-CURRENT]** — the current site already has `<html lang="en">` on every single page despite 100% Bulgarian content, which is wrong today and an easy thing to carry forward unnoticed into the redesign.

**Why it happens:**
Font selection is usually done by browsing preview specimens that default to Latin sample text, so Cyrillic coverage never gets checked before a font is chosen and integrated. Encoding bugs happen when files pass through tools/editors that don't default to UTF-8, or when a CMS/build step reads/writes without an explicit UTF-8 flag. The `lang="en"` attribute gets left on a boilerplate/theme's default `<html>` tag because it has zero visible effect on rendering, so nobody notices.

**Consequences:**
Tofu boxes or ugly fallback-font rendering directly undermine the "modern look and feel" goal of this redesign — the whole point of the project fails silently for exactly the audience it's built for. Mojibake in body text destroys credibility instantly. Wrong `lang` attribute doesn't break rendering but actively works against SEO/accessibility: it can affect how screen readers pronounce Bulgarian text, and can be a (minor but real) signal-quality issue for Google's language/region understanding of the page.

**Prevention:**
- Whenever a new webfont is chosen, explicitly verify its Cyrillic subset before adopting it — check the font's own documentation/specimen for a Cyrillic character set, not just Latin. Google Fonts entries show subset coverage; confirm "Cyrillic" is listed, not just "Latin" and "Latin Extended."
- For any decorative/display font that turns out Latin-only, scope it with CSS `unicode-range` to Latin characters only (headings/UI chrome using Latin brand names, etc.) and pair it with a Cyrillic-complete fallback (e.g. Noto Sans, or a body font confirmed to cover Cyrillic) for all actual Bulgarian copy — don't let a Latin-only font be the sole `font-family` for any element that will ever contain Bulgarian text.
- Enforce UTF-8 everywhere, consistently: file encoding in the editor/build tool, `<meta charset="utf-8">` in every page head (already correct today — keep it), and the HTTP `Content-Type` header if server-side rendering or forms are involved (`mailer.php` already correctly uses `htmlentities($x, ENT_QUOTES, 'UTF-8')` for form output — carry that pattern forward for any new server-side code).
- Fix `<html lang="en">` → `<html lang="bg">` on every page as part of the redesign — trivial to do, easy to forget, real (if small) SEO/accessibility value.
- After the new templates are built, do a manual visual pass over every page specifically looking for tofu boxes, wrong glyph shapes, or fallback-font mismatches — this is not something automated tooling reliably catches for a single-language Cyrillic site.

**Warning signs:**
- A new font was chosen by eyeballing a Latin specimen only.
- No one has opened every new page and read the Bulgarian body text character-by-character looking for broken glyphs after font/template changes.
- `<html lang="en">` (or any non-`bg` value) is still present after redesign.
- Any step in the build/deploy pipeline (editor, build tool, FTP client charset setting) doesn't have UTF-8 explicitly configured.

**Phase to address:** Visual/design-system phase (font selection with Cyrillic-coverage check built into the acceptance criteria) and a dedicated Cyrillic-QA pass before launch, distinct from generic cross-browser QA.

---

### Pitfall 5: Content/duplicate-title/no-meta-description gaps get carried forward instead of fixed

**What goes wrong:**
**[VERIFIED IN SITE-CURRENT]** Every one of the 16 pages on the live site currently ships the exact same `<title>` tag — "ТОРИН КОМПЮТЪРС - ТОТАЛЕН РЕМОНТ НА ЛАПТОПИ" — with zero variation between the homepage, the battery page, the liquid-damage page, the warranty page, etc. And no page has a `<meta name="description">` tag at all. These are foundational on-page SEO elements, and their absence has presumably suppressed how well individual service pages (not just the homepage) rank and how their snippets look in Google search results, for as long as the site has existed. A redesign is the natural moment to fix this — but it's equally the natural moment to *reproduce* the same gap in a new template if nobody explicitly plans unique titles/descriptions per page as a deliverable.

**Why it happens:**
The original "Liquid" template likely hardcoded the title in a shared header include and nobody customized it per page when content was added over time. A redesign that focuses on visuals can easily repeat this pattern if the new template/build system also centralizes the `<head>` without a mechanism (or checklist) for per-page metadata.

**Consequences:**
Continued underperformance of individual service pages in search (e.g. someone searching specifically for "смяна на матрица лаптоп" — screen replacement — has no differentiated, keyword-relevant title/description pointing them to the exact right page); missed opportunity that this redesign is explicitly meant to capture per PROJECT.md's core value ("a visitor with a specific problem must immediately see Torin fixes exactly that").

**Prevention:**
- Require unique `<title>` and `<meta name="description">` for every page as an explicit build checklist item, written to reflect that specific page's service (battery, liquid damage, warranty, etc.), not just the homepage tagline.
- If the new build uses any templating/includes for the `<head>`, make title/description parameters explicitly required per page (fail the build / flag visibly if a page reuses the default), rather than something that silently falls back to a shared default.
- While doing this, also add Open Graph tags (`og:title`, `og:description`, `og:image`) per page — cheap to add at the same time, improves how pages look when shared/linked from Bulgarian Facebook/Viber, which is a realistic referral channel for a local repair shop.

**Warning signs:**
- Viewing page source on any two different new pages shows the same `<title>`.
- No `<meta name="description">` present anywhere.
- The new template's `<head>` is a single shared include with no per-page override wired up yet.

**Phase to address:** Content/IA phase (define per-page title/description as content deliverables alongside body copy) and template-build phase (wire the mechanism to actually use them).

---

## Moderate Pitfalls

### Pitfall 6: Image-heavy pages stay slow after the redesign

**What goes wrong:**
**[VERIFIED IN SITE-CURRENT]** The current `assets1/` folder is ~14MB with 81 images, several individual photos in the 100-290KB range (e.g. `adaptor.png` 288KB, laptop model photos 150-260KB each) — these are typical "upload straight from phone/camera, never resized or compressed" assets. A visual redesign easily makes this worse (bigger hero images, more photography) rather than better if performance isn't an explicit constraint, and Google's own data shows mobile bounce probability jumps ~32% as load time goes from 1s to 3s — directly relevant since local-service searches skew heavily mobile.

**Prevention:**
Resize every image to its actual maximum display dimension before upload (no 4000px-wide photos serving into a 400px card), compress/convert to WebP with a fallback, set explicit `width`/`height` attributes on every `<img>` to prevent layout shift, and lazy-load below-the-fold images. Treat this as a build-step checklist item (image optimization pass before every deploy), not a one-time cleanup, since shared hosting + FTP means there's no automatic image pipeline doing this for you.

---

### Pitfall 7: Contact form has no spam protection

**What goes wrong:**
**[VERIFIED IN SITE-CURRENT]** `mailer.php` accepts POST fields (`name`, `mail`, `mobile`, `message`), correctly HTML-encodes them for output, but has no CAPTCHA, honeypot field, or rate-limiting visible. A public-facing PHP mail form with no bot mitigation is a common spam-bot target, and on shared hosting a script mailing out large volumes of spam through the account's mail relay can get the domain/IP blacklisted, which would actively hurt deliverability of real customer inquiry notifications.

**Prevention:**
Add a simple honeypot hidden field (bots fill every field, humans don't see/fill a visually-hidden one) as a cheap first line of defense, and/or a lightweight CAPTCHA (e.g. Google reCAPTCHA v3, invisible) if spam becomes a measured problem. Keep the existing `htmlentities(...,'UTF-8')` encoding pattern for any new form-handling code — it's already correctly Cyrillic-safe.

---

### Pitfall 8: No `robots.txt` or `sitemap.xml` today — easy to forget adding them in the redesign too

**What goes wrong:**
**[VERIFIED IN SITE-CURRENT]** Neither `robots.txt` nor `sitemap.xml` exists at the site root currently. Their absence isn't actively harmful today (no robots.txt means "crawl everything," which is fine for this site), but a redesign is the natural point to add both — a sitemap makes new/changed URLs discoverable to Google faster after the exact kind of structural change this project involves, and a `robots.txt` becomes actively necessary if any staging subfolder/subdomain is used during the rebuild (see Pitfall 3) to keep it out of the index.

**Prevention:**
Add a `sitemap.xml` listing the final post-redesign URL set and submit it in Google Search Console right after cutover (this also gives a fast way to confirm which URLs Google has picked up post-migration). Add a minimal `robots.txt` allowing everything on production, and use a separate disallow-all `robots.txt` on any staging location.

---

## Minor Pitfalls

### Pitfall 9: `covid.html` and other stale/seasonal content ship in the redesign unreviewed

**What goes wrong:** **[VERIFIED IN SITE-CURRENT]** The mirror includes `covid.html` and a `covid-19/` folder — leftover pandemic-era content (likely hours/safety-notice info) that's almost certainly irrelevant in 2026 and would look dated/careless if carried into a "modernized" redesign untouched.

**Prevention:** Explicitly review every existing page during content migration and decide keep/retire/merge — don't assume "existing page = still needed." If it's retired, 301-redirect its URL to the most relevant current page rather than 404ing it (see Pitfall 1).

---

### Pitfall 10: `otpuska.js` / holiday-hours banner logic silently dropped or silently kept wrong

**What goes wrong:** The current `index.html` calls `showHolidays()` on `<body onload>`, backed by `otpuska.js` — a small script presumably showing seasonal "closed for holidays" banners. This kind of small behavioral script is easy to either lose entirely in a rebuild (no equivalent feature planned) or keep with stale hardcoded dates from years ago.

**Prevention:** Explicitly ask the owner whether this feature (holiday/closure announcements) should be replaced with a maintained equivalent in the new site (even something as simple as a manually-edited banner flag), rather than silently dropping or silently carrying forward outdated logic.

---

## Phase-Specific Warnings

| Phase Topic | Likely Pitfall | Mitigation |
|-------------|-----------------|------------|
| Content/IA planning | Duplicate titles/no meta descriptions carried forward (Pitfall 5); stale content like `covid.html` copied as-is (Pitfall 9) | Require unique title/description per page as explicit deliverable; explicit keep/retire/merge decision per existing page |
| Visual/design system (fonts, icons) | Cyrillic glyph gaps in chosen fonts (Pitfall 4) | Verify Cyrillic subset before adopting any font; `unicode-range` scoping for Latin-only display fonts |
| Template/build | `lang="en"` and shared-title patterns silently reproduced (Pitfall 4, 5); no per-page metadata mechanism wired up | Fix `lang="bg"`; wire per-page title/description as required template params |
| Pre-launch / migration planning | URL structure changes without redirect map (Pitfall 1); must-carry root files lost (Pitfall 2) | Full URL inventory + GSC cross-check before rebuild starts; must-carry checklist (`.htaccess`, Google verification file, `robots.txt`, favicon) |
| Deployment/tooling setup | No rollback path on FTP-only deploy (Pitfall 3) | Git as source of truth even with FTP publish target; pre-deploy full-mirror backup every time; staging subfolder with noindex |
| Asset/performance pass | Image-heavy pages stay slow post-redesign (Pitfall 6) | Resize/compress/WebP + explicit width/height + lazy-load as a build checklist, not a one-off |
| Forms/contact | Spam-unprotected `mailer.php` pattern carried forward (Pitfall 7) | Add honeypot/CAPTCHA to any rebuilt contact form; keep existing UTF-8-safe encoding pattern |
| Cutover/launch | `robots.txt`/`sitemap.xml` still missing at launch (Pitfall 8); Search Console verification lost (Pitfall 2) | Add both files at cutover, submit sitemap in GSC same day; verify GSC ownership survives via a second method (DNS/Analytics) |

## Sources

- [SEO Migration Checklist: Redesign Without Losing Rankings — Mindesigns](https://mindesigns.com.au/blog/the-seo-migration-checklist)
- [SEO Migration Checklist — Brand Vision](https://www.brandvm.com/post/seo-migration-checklist)
- [SEO website migration mistakes checklist — Business Image](https://business-image.co.uk/seo-website-migration-mistakes/)
- [Website Migration SEO: Rebuild Without Losing Rankings — Rubik Digital](https://www.rubikdigital.co.uk/knowledge/website-migration-seo)
- [Website Redesign SEO Checklist — SEOptimer](https://www.seoptimer.com/blog/website-redesign-seo/)
- [Why 301 Redirects Matter for SEO — Namesilo](https://www.namesilo.com/blog/en/websites-hosting/why-301-redirects-matter-for-seo--when-to-use-them)
- [How Changing URLs Affects SEO — Americaneagle.com](https://www.americaneagle.com/insights/blog/post/how-changing-urls-affects-seo)
- [301 Redirects and Website Redesigns — Circle S Studio](https://circlesstudio.com/blog/301-redirects-and-website-redesigns/)
- [How to Fix (and Avoid) Traffic Drops after a Website Redesign — WebFX](https://www.webfx.com/blog/web-design/fix-avoid-traffic-drops-website-redesign/)
- [Why your organic traffic dropped after your website redesign — Salt Agency](https://salt.agency/blog/organic-traffic-dropped-after-website-redesign/)
- [URL Slug Best Practices for SEO-Friendly URLs — DevToolHub](https://devtoolhub.net/blog/url-slug-best-practices/)
- [URL Slugs: Rules, SEO Impact, and Transliteration — Rich Dev Tools](https://richdevtools.com/articles/web/url-slug-best-practices)
- [Multilingual SEO: A Guide to URL Structure — Search Engine Journal](https://www.searchenginejournal.com/multilingual-seo-url-structure/298747/)
- [Mojibake — Languages Wiki](https://worldlanguages.fandom.com/wiki/Mojibake)
- [UTF-8 problems with Bulgarian (Cyrillic) — Simple Machines community](https://www.simplemachines.org/community/index.php?topic=247270.0)
- [UTF-8 mojibake — a practical guide — brokkr.net](https://brokkr.net/2022/04/20/fun-with-character-encoding-errors-part-i/)
- [The Tofu Symbol: When fonts get confused — SimpleLocalize](https://simplelocalize.io/blog/posts/tofu-symbol/)
- [Fallback font — Wikipedia](https://en.wikipedia.org/wiki/Fallback_font)
- [What is Tofu? — Codepoints Blog](https://blog.codepoints.net/what-is-tofu.html)
- [Best Practices in Web Development Workflows — Pantheon.io](https://pantheon.io/learning-center/webops/web-development-workflow)
- [The Site Is on Fire. Here's Your FTP Password. Good Luck. — Jonas Kamsker](https://blog.kamsker.at/blog/fixing-the-site/)
- [Git vs FTP for Website Deployment — 1Byte](https://blog.1byte.com/git-vs-ftp-for-website-deployment/)
- [What Is a Staging Environment? — HostAdvice](https://hostadvice.com/blog/web-hosting/security/what-is-a-staging-environment/)
- [Steps for a bulletproof staging site — Managed Hosting Partners](https://managedhosting.partners/blog/steps-for-a-bulletproof-staging-site-and-why-every-website-needs-one/)
- [Website Mistakes Local Businesses Make: 2026 Fix Guide — Webby](https://www.webby.net.au/blog/marketing-how-to/website-mistakes-local-businesses-make-2026-fix-guide/)
- [Why Trust Signals Are the Missing Link on Most Local Business Websites — Best Version Media](https://www.bestversionmedia.com/why-trust-signals-are-the-missing-link-on-most-local-business-websites/)
- [The 7 Trust Signals Missing From Most Professional Service Websites — Code Conspirators](https://www.codeconspirators.com/the-7-trust-signals-missing-from-most-professional-service-websites-with-examples/)
- [Core Web Vitals for Contractor Websites — PushLeads](https://pushleads.com/core-web-vitals-for-contractor-websites/)
- [How to improve Core Web Vitals in 2025 — OWDT](https://owdt.com/insight/how-to-improve-core-web-vitals/)
- Direct inspection of `site-current/` (local FTP mirror of live torin.bg, pulled 2026-08-04): `.htaccess`, `index.html` and all 16 page `<head>` blocks, `mailer.php`, `assets1/` image sizes, `google1718743335455f1c.html` verification file, absence of `robots.txt`/`sitemap.xml`/`<meta name="description">`, `<html lang="en">` on every page, identical `<title>` across all pages.

---
*Pitfalls research for: Bulgarian small-business (computer/laptop repair shop) website redesign — SEO-preserving migration, Cyrillic content, plain-FTP deploy*
*Researched: 2026-08-04*
