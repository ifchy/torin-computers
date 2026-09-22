---
quick_id: 260922-hbk
slug: deploy-htaccess-guard
date: 2026-09-22
description: Enforce in code what ledger 52 only asserts — deploy-new.sh must refuse src/.htaccess
---

# Make `.htaccess` actually out of scope for `deploy-new.sh`

## The gap

Ledger 52 states that `src/.htaccess` "is OUT OF SCOPE for `deploy-new.sh`". **Nothing
enforces that.** The scope exists as a boxed comment at the top of `src/.htaccess` and a
sentence in cutover checklist step 2.4 — both addressed to a human who is paying attention.

`scripts/deploy-new.sh:161` builds its no-argument file list from:

```sh
find . -type f ! -name '.DS_Store'
```

`find -type f` matches dotfiles, so `.htaccess` is in that list. `REMOTE_ROOT` is hardcoded
to `public_html/new` (line 41), and `src/.htaccess` was promoted to **root form** in 04-09 —
its `RewriteBase` and canonicalisation target both address `/`.

So a bare `scripts/deploy-new.sh` uploads a file whose rewrite base is `/` into a directory at
`/new/`. **That breaks every redirect in it**, and it is not hypothetical: this project already
shipped it once, producing 301s whose `Location` was
`https://torin.bg/home/torin/public_html/new/<target>` — a server path leaked into a public URL
that 404s on follow while the 301 itself looks perfectly correct.

Uploading this file through this script is **always** wrong, because the script only ever writes
to the staging subtree.

## The guard

Refuse by basename, so no path spelling slips through (`.htaccess`, `./.htaccess`,
`includes/.htaccess`).

Two behaviours, because the two invocation modes deserve different answers:

| Mode | Behaviour | Why |
|---|---|---|
| **No argument** (deploy everything) | **Skip it**, print a prominent notice naming the reason | The operator means "deploy the site". Aborting the routine full deploy over a file they never named is a papercut that teaches people to work around the guard. |
| **Explicitly named** | **Hard error, exit 1** | They asked for this file specifically. That is exactly the invocation that produces the D4-30 catastrophe, and exactly when a speed bump is worth having. |

Escape hatch: `TORIN_DEPLOY_HTACCESS=1`, following the `TORIN_LIVE_DEPLOY_CONFIRM=1` precedent
in `deploy-live.sh` — a deliberate, greppable opt-in rather than a flag that tab-completion can
produce.

## Verify — the guard must be shown to FIRE, not just to exist

This project's standing rule is that a check only ever shown to pass proves nothing. All
assertions run with **no credentials**, so nothing reaches the network:

1. No-arg mode excludes `.htaccess` from the file list and says so.
2. Explicitly named → exit 1 with a reason, before any upload.
3. `TORIN_DEPLOY_HTACCESS=1` + explicitly named → proceeds past the guard.
4. A normal explicit deploy (`sitemap.xml robots.txt`) is unaffected.
5. `bash -n scripts/deploy-new.sh` parses.

## Out of scope

`scripts/deploy-live.sh` already has a stricter allowlist model and is not touched.
