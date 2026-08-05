# Phase 1: URL Inventory (MIGR-01)

**Built:** 2026-08-05
**Requirement:** MIGR-01 — complete URL inventory of all 16 live pages, cross-checked against Search Console before rebuild work starts.

## Cross-check method — explicitly labeled as a GSC substitute

**Google Search Console (GSC) access is unavailable this session** (D-08/D-09 in `01-RESEARCH.md`). Neither the user nor Claude currently has access to the Google account that previously verified ownership of `torin.bg` (evidenced by `google1718743335455f1c.html` already present at the site root). Per D-09, this inventory substitutes:

1. A **direct `site-current/` mirror listing** (a full FTP pull of the live site, re-verified fresh this session — see "Drift check" below), and
2. A **public `site:torin.bg` search check**, attempted as the D-09 substitute signal.

**This is NOT a real GSC Pages-export cross-check.** It must be retrofitted with actual GSC data (page inventory, index-coverage status, any pages GSC knows about that aren't in `site-current/`) once GSC access is obtained. Until then, treat every disposition below as based on file-mirror + public-search evidence only, not authoritative Search Console index data.

### `site:torin.bg` search attempt — result

Attempted a public web search for `site:torin.bg` as the D-09 substitute signal. **The query could not be executed in this session**: no interactive web-search tool was available, and a scripted `curl` request to `google.com/search?q=site:torin.bg` returned an HTTP 302 consent-wall redirect rather than search results (Google blocks unauthenticated/scripted search requests before they reach real results). Stating this explicitly per the plan's instruction, rather than fabricating a result. **Action for follow-up:** re-run this check manually in a browser, or once GSC access is obtained, replace this whole substitute step with a real GSC Pages export.

### Drift check — site-current/ vs. 01-RESEARCH.md's verified 16-file listing

Re-ran a fresh top-level directory listing of `site-current/` this session (`ls -la site-current/`) and diffed it against `01-RESEARCH.md`'s "Code Examples" section verified 16-filename list (dated 2026-08-04). **Result: no drift** — all 16 filenames match exactly, same set, no additions or removals since research was done.

## Live page inventory (16 pages)

| Filename | Full URL | Disposition | Rationale |
|---|---|---|---|
| index.html | https://torin.bg/index.html | keep-as-is | Homepage — core page, no ambiguity. |
| about.html | https://torin.bg/about.html | keep-as-is | Company info page — core page, no ambiguity. |
| covid.html | https://torin.bg/covid.html | **decision-pending** | Content is EU-project/COVID material that CONTENT-02 (Phase 3) directs to be moved off homepage prominence and folded into About; `01-RESEARCH.md` Pitfall D flags this as a likely-retire candidate, but retiring an indexed URL unilaterally risks losing existing ranking/traffic to it. Recorded as pending-owner-decision, not silently resolved here — see STATE.md Blockers/Concerns. |
| laptopi.html | https://torin.bg/laptopi.html | keep-as-is | Core service page (laptop repair) — no ambiguity. |
| mehanichni-problemi.html | https://torin.bg/mehanichni-problemi.html | keep-as-is | Core service page (mechanical problems) — no ambiguity. |
| msg.html | https://torin.bg/msg.html | keep-as-is | Contact-form success/response page (targeted by `mailer.php`'s redirect) — functionally load-bearing, must be preserved. |
| optimizatsiq.html | https://torin.bg/optimizatsiq.html | keep-as-is | Core service page (optimization) — no ambiguity. |
| problem-stari.html | https://torin.bg/problem-stari.html | **decision-pending** | No current v1 requirement explicitly addresses this page ("old problems"); its content overlap with `mehanichni-problemi.html` is suspected but not confirmed. Recorded as pending-owner-decision per the plan's explicit prohibition against unilaterally retiring a borderline page — see STATE.md Blockers/Concerns. |
| profilaktika-laptop.html | https://torin.bg/profilaktika-laptop.html | keep-as-is | Core service page (maintenance/prophylaxis) — no ambiguity. |
| rezervni-chasti.html | https://torin.bg/rezervni-chasti.html | keep-as-is | Core service page (spare parts) — no ambiguity. |
| test-laptop.html | https://torin.bg/test-laptop.html | keep-as-is | `01-RESEARCH.md` Pitfall D flags this as borderline, but DIFF-01 explicitly requires the self-diagnostic tool ("Тествай сам своя лаптоп") to be surfaced as a homepage-level differentiator, not buried or retired — this page is the tool's existing URL and must be kept and promoted, not treated ambiguously. |
| tokov-udar.html | https://torin.bg/tokov-udar.html | keep-as-is | Core service page (power-surge damage) — no ambiguity. |
| uslovia.html | https://torin.bg/uslovia.html | keep-as-is | Terms page — no ambiguity. |
| warrently.html | https://torin.bg/warrently.html | keep-as-is | Warranty page — no ambiguity, also feeds TRUST-03. |
| za-bateriite.html | https://torin.bg/za-bateriite.html | keep-as-is | Core service page (batteries) — no ambiguity, also feeds DIFF-02. |
| zalivane-technosti.html | https://torin.bg/zalivane-technosti.html | keep-as-is | Core service page (liquid/motherboard damage) — no ambiguity, also feeds DIFF-03. |

**16/16 filenames present, each with a non-blank disposition.** Order above matches the stable order already verified in `01-RESEARCH.md`'s "Code Examples" section (site-current/ directory listing) — this ordering carries no ranking/priority meaning, it exists purely for cross-reference readability against the research artifact.

**Adjacency note:** This is a flat, 16-entry inventory. Each filename above is dispositioned independently; no entries are merged, compared, or resolved against one another (not applicable to MIGR-01's nature — this requirement is about per-URL preservation, not content consolidation).

## Must-carry root files (out of this phase's scope to migrate — recorded for Phase 4 / MIGR-02)

These 7 non-page root files must survive the Phase 4 cutover unchanged. They are not part of this phase's work; recorded here so Phase 4's cutover checklist (MIGR-02) has a ready-made source list.

| File | Disposition | Rationale |
|---|---|---|
| .htaccess | must-carry | Root Apache config — currently a no-op cPanel-generated stub (verified in `01-RESEARCH.md`); the live root version must not be touched until Phase 4 cutover per D-01. |
| favicon.ico | must-carry | Site favicon, referenced from every page's `<head>`. |
| google1718743335455f1c.html | must-carry | Google Search Console site-ownership verification file (D-08) — removing it would revoke prior GSC verification for whichever account holds it. |
| header.js | must-carry | Site JS asset referenced across pages. |
| otpuska.js | must-carry | Holiday-banner script — STATE.md already flags its long-term intent (keep vs. drop) as a Phase 4 open decision; must-carry for now regardless of that eventual outcome. |
| mailer.php | must-carry | Contact-form backend — confirmed live and functional (`X-Powered-By: PHP/5.2.17`, redirects to msg.html). Hardening is CONTACT-03 (Phase 4), not this phase. |
| error_log | must-carry | Host-generated PHP error log — not application content, but present at root; flagged so Phase 4's cutover checklist doesn't accidentally treat it as stray content to discard. |

## Must-carry root directories (out of this phase's scope to migrate — recorded for Phase 4 / MIGR-02)

| Directory | Disposition | Rationale |
|---|---|---|
| .well-known/ | must-carry | Contains its own `.htaccess` (verified verbatim in `01-RESEARCH.md`/`01-PATTERNS.md`) granting public access to `.txt` files — commonly used for ACME/SSL domain-validation challenges. Carry forward unchanged. |
| cgi-bin/ | must-carry | Standard cPanel-provisioned directory; not application content, but part of the account's baseline structure. |
| covid-19/ | must-carry | Companion asset directory to `covid.html` — its own fate is tied to whatever decision is eventually made about `covid.html` (see decision-pending row above), but until that decision is made, this directory must not be dropped. |
| assets1/ | must-carry | All current CSS/JS/font/vendor assets referenced by every live page's `<head>`/footer — must survive unchanged until the visual rebuild phases replace it piece by piece. |

## Summary

- 16/16 live pages inventoried, each with a non-blank disposition.
- 14 pages: keep-as-is (12 with no ambiguity, plus test-laptop.html explicitly resolved to keep-as-is per DIFF-01).
- 2 pages: decision-pending (covid.html, problem-stari.html) — recorded as explicit open decisions, not silently retired or silently kept. Cross-referenced in `.planning/STATE.md`'s Blockers/Concerns section.
- 7 must-carry root files + 4 must-carry root directories recorded for Phase 4/MIGR-02 consumption.
- Cross-check data source is explicitly a site-mirror + public-search substitute, not a real GSC export — must be retrofitted with actual Search Console data once access is obtained (D-08/D-09).
