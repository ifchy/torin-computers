# Phase 1: Migration Safety Net & Foundation - Context

**Gathered:** 2026-08-04
**Status:** Ready for planning

<domain>
## Phase Boundary

Before any rebuild work touches the live site, establish: a complete URL inventory with continuity guarantees, a proven backup/rollback process, and a working PHP-include technical foundation verified on the real host. This phase delivers no visitor-facing changes — it's the safety net and scaffold everything else builds on.

</domain>

<decisions>
## Implementation Decisions

### Preview & Deploy Workflow
- **D-01:** All Phase 1-4 build work happens in a `public_html/new/` subfolder on the same live host (`bell.host.bg`), reachable at `torin.bg/new` for live preview throughout the project. The real site at `torin.bg` (root of `public_html/`) is untouched until the Phase 4 cutover swap.
- **D-02:** Claude handles all FTP uploads to `torin.bg/new` as work progresses — the user does not need to use FileZilla or any FTP client themselves. Just visit the URL to check progress.
- **D-03:** The `.htaccess` `.html`-as-PHP spike-verification (needed to confirm pages can keep their `.html` filenames while being parsed as PHP) happens inside `public_html/new/` — no cPanel/hosting-panel access needed, no risk to the live site. — **Reversibility:** reversible — isolated subfolder, no production impact.

### Backup & Version Control
- **D-04:** Git remains the local source of truth (already initialized). In addition, push the repo to a **private GitHub repository** as an off-site backup, protecting against local machine loss.
- **D-05:** GitHub CLI (`gh`) is not installed/authenticated in this environment. The user will create an empty private repo on github.com themselves and share the remote URL — Claude cannot create it directly. **Open item, blocks nothing else — proceed and wire up the remote once the URL is provided.**

### URL Canonicalization
- **D-06:** Live-site check found all four URL variants (`http://torin.bg`, `https://torin.bg`, `http://www.torin.bg`, `https://www.torin.bg`) currently serve identical content with `200 OK` and no redirect between them — a real duplicate-content exposure, not hypothetical. — **Reversibility:** reversible — a redirect rule, easy to adjust.
- **D-07:** Phase should canonicalize to **`https://torin.bg`** (non-www, HTTPS) — matches what the site's existing branding/internal links already use. Redirect `www→non-www` and `http→https`. This is new work not yet captured as its own line item in ROADMAP.md/REQUIREMENTS.md — treat it as part of delivering SEO-04 (URL preservation) and the Phase 1 foundation, since it directly protects the same ranking-continuity goal.

### Search Console Access — DEFERRED
- **D-08:** The site already has a Google Search Console ownership-verification file (`google1718743335455f1c.html`) at its root, meaning *some* Google account previously verified GSC access to torin.bg — but neither the user nor Claude currently has that access.
- **D-09:** User will ask the shop owner (customer) whether they have that Google account / can grant access — **this will not happen within the next 12-14 hours at minimum**. Decision: **proceed with Phase 1 planning and execution without GSC access now.** Do not block on it. Fall back to inferring the URL list from the live site itself (already have full FTP mirror in `site-current/`) plus public `site:torin.bg` Google search checks. Re-incorporate real GSC data into the URL inventory retroactively once/if access is obtained — this is a follow-up, not a phase blocker.

### Claude's Discretion
- Backup format/mechanism beyond "git + GitHub" (e.g., whether to also keep a raw zip snapshot) — Claude's call during planning.
- Exact `.htaccess` spike-test method (what harmless test file/route to use in `public_html/new/` to confirm PHP-as-`.html` behavior) — Claude's call.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Project & Requirements
- `.planning/PROJECT.md` — project context, constraints, hosting details
- `.planning/REQUIREMENTS.md` — v1 requirements (MIGR-01, MIGR-03, SEO-04 map to this phase)
- `.planning/ROADMAP.md` §Phase 1 — phase goal and success criteria

### Research (directly informs this phase)
- `.planning/research/PITFALLS.md` — Pitfalls 1-3 (broken URLs, lost must-carry files, no rollback path) are exactly what this phase exists to prevent
- `.planning/research/ARCHITECTURE.md` — PHP-include pattern, `.htaccess` `.html`-as-PHP approach, deploy workflow
- `.planning/research/SUMMARY.md` §"Resolving the Stack/Architecture Tension" — why PHP-include was chosen over an Astro rebuild; load-bearing for how this phase's foundation should be built

### Live Site Baseline
- `site-current/` — full FTP mirror of the live site (215 files), the authoritative source for the URL inventory and must-carry file checklist
- `site-current/.htaccess` — currently just the cPanel-generated stub comment, no active rules — confirms nothing existing needs preserving beyond the file's presence
- `site-current/mailer.php` — current contact-form handler (bare `mail()`, no honeypot) — not this phase's job to harden (that's Phase 4/CONTACT-03), but relevant context for the PHP-include foundation work

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `site-current/google1718743335455f1c.html` — Google Search Console verification file, must be carried into the new build unchanged
- `site-current/favicon.ico`, `site-current/.htaccess` — must-carry root files identified during discussion

### Established Patterns
- Site currently has zero build tooling — plain HTML/CSS/JS + PHP, deployed via direct FTP file placement. The new foundation should preserve this "no build step" simplicity per the research-backed PHP-include decision.
- Host (`bell.host.bg`) confirmed running PHP already (`mailer.php` is live and functional).

### Integration Points
- `public_html/new/` — new subfolder to be created via FTP, becomes the working root for Phases 1-4
- `public_html/` (root) — the real live site, untouched until Phase 4 cutover

</code_context>

<specifics>
## Specific Ideas

- User's own framing: "before finishing all work to have the ongoing work accessible on torin.bg/new" — directly shaped D-01/D-02 above.
- All four domain/protocol variants (www/non-www × http/https) return identical `200 OK` content with no redirect — verified live via `curl -I` during this discussion, not assumed.

</specifics>

<deferred>
## Deferred Ideas

- Full GSC-backed URL inventory cross-check (MIGR-01 as originally scoped) — deferred until the user gets Search Console access details from the customer. Proceed now with a site-mirror + public-search-based inventory instead; retrofit GSC data later.
- GitHub remote wiring — deferred until the user creates the private repo and shares the URL.

### Reviewed Todos (not folded)
None — no pending todos matched this phase.

</deferred>

---

*Phase: 1-Migration Safety Net & Foundation*
*Context gathered: 2026-08-04*
