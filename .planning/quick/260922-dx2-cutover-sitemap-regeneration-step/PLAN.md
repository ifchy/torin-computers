---
quick_id: 260922-dx2
slug: cutover-sitemap-regeneration-step
date: 2026-09-22
description: Close ledger #51 — sequence the base_url/sitemap/robots retarget into the cutover checklist before the swap
---

# Cutover checklist: sequence the sitemap regeneration (ledger #51)

## The defect

`04-CUTOVER-CHECKLIST.md` Section 6.1 instructs the operator to
`scripts/sitemap-check.sh --live, and submit sitemap.xml` — with **no step anywhere telling
them to regenerate it first**. As the tree stands:

- `src/includes/site-config.php:312` → `'base_url' => 'https://torin.bg/new/'`
- `src/sitemap.xml` → 19 `<loc>` entries, all `https://torin.bg/new/<page>`
- `src/robots.txt` → `Sitemap: https://torin.bg/new/sitemap.xml`, under `Allow: /`

Following the checklist literally submits 19 staging paths to Google immediately after a
cutover whose entire purpose is URL continuity.

The code side is not broken — `scripts/gen-sitemap.sh` reads `base_url` from the config and
refuses to hard-code a host, and `scripts/sitemap-check.sh` checks D and E assert that every
`<loc>` and the robots directive both derive from it. **Only the operator-facing sequence is
missing.**

## Mechanism constraints that fix the ordering

The swap (D4-28) is a **server-side rename that re-uploads nothing**. Whatever those three
files say at the moment of the rename is what the live root publishes. So the retarget must
land on the server *before* step 2.3, not after.

Swapping first and fixing after would leave a window where the live root publishes canonical,
JSON-LD and sitemap URLs pointing into `/new/` while `robots.txt` says `Allow: /` — nothing
stops a crawl during it.

## Task 1 — Add step 2.0 to Section 2

Insert **before** the existing 2.1, numbered `2.0` so that **no existing step is renumbered**
— Section 6.4 refers to "the Section 2.1 panel step" and ledger 52 refers to step 2.4 by
number. Renumbering would break both.

The step covers, in order: retarget `base_url` to `https://torin.bg/` (the canonical target
D4-30 fixes at `.htaccess:70`); run `scripts/gen-sitemap.sh`; hand-edit the `robots.txt`
`Sitemap:` directive; run `scripts/sitemap-check.sh` offline; deploy exactly those three by
explicit path.

**The deploy must name its three paths explicitly.** A bare no-argument `scripts/deploy-new.sh`
builds its list from `find . -type f` (line 161) and therefore uploads `src/.htaccess` — now in
root form — into `public_html/new/`, which is exactly the D4-30 catastrophe. See "Out of scope"
below.

## Task 2 — Gate the Section 6.1 submit line

`scripts/sitemap-check.sh --live` and the Search Console submission become explicitly
conditional on 2.0 having been done, with the failure mode named.

## Task 3 — ADDED MID-TASK: make regeneration actually correct

Running `scripts/sitemap-check.sh` against the tree revealed it **exits 1 today**, and
`gen-sitemap.sh --stdout` proved why: regenerating right now emits **20** `<loc>` entries,
including `google1718743335455f1c.html` — the Search Console verification token.

Both scripts derive "indexable page" as *every `src/*.html` minus those assigning
`$torin_robots ... noindex`*. The token file is 53 bytes of plain text with no PHP and no
`$torin_robots` line, so neither can see it. It entered `src/` in 04-09, after 04-08 generated
the committed sitemap — which is why the committed file is right and regeneration would not be.

**Without this, the step Task 1 adds would have the operator publish their verification token
as a page.** So the fix is a precondition of the requested work, not a separate concern.

Add to the candidate rule in **both** scripts, kept identical: a file is a page only if it
includes `includes/header.php`. Derived, not listed — consistent with `gen-sitemap.sh`'s stated
refusal to carry hard-coded slugs, and robust to the next static drop-in.

Proof obligations: regenerated output must be **byte-identical** to the committed sitemap, and
the checker must still be demonstrably able to fail.

## Verify

- `grep -c '2.0' Section 2` → the step exists; existing 2.1–2.6 numbering unchanged.
- Section 6.1 submit line references step 2.0.
- No source file changes: this is a checklist-only edit.

## Out of scope — but MUST be reported

`src/.htaccess` is **not actually excluded** from `scripts/deploy-new.sh`. Ledger 52 asserts it
"is OUT OF SCOPE for deploy-new.sh", but that scope exists only as a comment in `.htaccess` and
a note in checklist step 2.4 — there is no code enforcing it, and a no-argument run uploads it.
This is a live landmine independent of #51. Flag to the user; do not silently change deploy
behaviour as part of this task.
