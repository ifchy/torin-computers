# Phase 1: Migration Safety Net & Foundation - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-08-04
**Phase:** 1-Migration Safety Net & Foundation
**Areas discussed:** Off-site backup, Legacy URLs / domain variants, PHP/host verification access, Preview setup, GitHub backup, URL canonicalization, GitHub repo creation, Search Console access

---

## Preview / staging setup

User raised (via freeform "Other" response) that they weren't sure whether work-in-progress could be checked via GitHub without FTP upload, and described wanting ongoing work visible at `torin.bg/new` before everything is finished.

Claude explained: GitHub stores code history but doesn't serve the live site — visibility on `torin.bg/new` requires actual FTP upload to the host, which Claude offered to handle throughout the project.

| Option | Description | Selected |
|--------|-------------|----------|
| torin.bg/new subfolder, Claude handles FTP uploads | Build in a subfolder on the same host, live preview throughout, cutover only at Phase 4 | ✓ |
| Something different | User describes an alternative | |

**User's choice:** Confirmed — exactly that.
**Notes:** This also resolves the separate "PHP/host verification access" question — the `.htaccess` `.html`-as-PHP spike test can happen safely inside `/new` without needing cPanel access.

---

## Off-site backup (GitHub)

| Option | Description | Selected |
|--------|-------------|----------|
| Private GitHub repo | Off-machine backup, browsable history/diffs | ✓ |
| Local git only | No remote backup | |

**User's choice:** Yes, private GitHub repo.
**Notes:** `gh` CLI not installed/authenticated in this environment — Claude cannot create the repo directly.

| Option | Description | Selected |
|--------|-------------|----------|
| User creates repo, shares URL | User creates empty private repo on github.com | ✓ |
| User installs/auths gh CLI | User runs `gh auth login` themselves | |
| Skip GitHub for now | Stay local-only | |

**User's choice:** User will create it and share the URL.

---

## Legacy URLs / domain variants

Claude checked live via `curl -I` against all four variants (`http://torin.bg`, `https://torin.bg`, `http://www.torin.bg`, `https://www.torin.bg`) before asking — found all four return `200 OK` with identical content, no redirects between them.

| Option | Description | Selected |
|--------|-------------|----------|
| Canonicalize to https://torin.bg | Redirect www→non-www and http→https | ✓ |
| Different canonical | User specifies alternative | |
| Leave as-is | Don't touch redirect rules this phase | |

**User's choice:** Yes — canonicalize to `https://torin.bg`.

---

## Search Console access

Claude asked twice — first pass user asked "what is Search Console and why is it important," so Claude gave a plain-language explanation (what GSC is, why redesigns risk losing ranking silently, what the existing `google1718743335455f1c.html` verification file implies about a pre-existing but currently inaccessible account).

| Option | Description | Selected |
|--------|-------------|----------|
| Ask the customer | Check with shop owner for the original Google account/access | ✓ |
| Use own account, re-verify fresh | Immediate access, loses historical data | |
| Skip GSC entirely | Public-methods-only, no account | |

**User's choice:** Will ask the customer, but explicitly noted this can't happen for at least 12-14 hours — asked to proceed without it now and add details once obtained.

**Notes:** Decision: proceed with Phase 1 without GSC access. Fall back to `site-current/` mirror + public `site:torin.bg` search checks for the URL inventory. Retrofit real GSC data later as a follow-up, not a phase blocker.

---

## Claude's Discretion

- Backup format/mechanism beyond git + GitHub (e.g. raw zip snapshots)
- Exact `.htaccess` spike-test method/file used to verify `.html`-as-PHP behavior in `public_html/new/`

## Deferred Ideas

- Full GSC-backed URL inventory cross-check — deferred pending customer response, to be retrofitted into the URL inventory later
- GitHub remote wiring — deferred until user provides the repo URL

