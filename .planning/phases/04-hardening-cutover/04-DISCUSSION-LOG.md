# Phase 4: Hardening & Cutover - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-09-17
**Phase:** 4-Hardening & Cutover
**Areas discussed:** Mail transport & PHP version, Photo uploads, Analytics & consent, Owner-editable hours & banner, Cutover mechanics & www canonical, Replacing the Viber CTA, Performance & caching

The user selected **all seven** offered gray areas. Three rounds were interrupted by
clarification requests before being re-asked — those exchanges are recorded below because
the reasoning changed the options, not just the answers.

---

## Mail transport & PHP version

### Clarification requested before answering

The user asked: *"how big of a security concern is staying with 5.2 versus upgrading to
7.4+ considering the type of site torin.bg is and also how much time and effort would it
require to upgrade the php version, what implications does that have, would it require a
lot of other things to be upgraded and what are the risks if we go down this path"*

Findings that were produced in response, and that changed the decision:

- A tree-wide grep for every construct PHP 7 removed (`ereg`, `split`, `mysql_*`,
  `each()`, `create_function`, magic quotes, `$HTTP_*_VARS`) returned **nothing**. Code
  changes required: zero.
- `max_input_vars` and `max_file_uploads` — the two guards protecting a public POST
  endpoint — both arrived in **5.3.9** and do not exist in 5.2.
- TLS peer verification for SMTP only became a default in **5.6**.
- The live root `.htaccess` holds only a cPanel-generated handler block, and **no live
  `.html` file contains PHP** — so an account-wide version switch cannot break the current
  live site. This was the expected blocker and it was not one.
- Realistic threat model: not data theft, but the site being used to host spam/phishing
  → Google Safe Browsing blacklists torin.bg → the SEO continuity four phases have
  protected evaporates.

**Outcome:** *"let's upgrade the php version, re-ask the questions"*

### PHP version target

| Option | Description | Selected |
|--------|-------------|----------|
| Newest stable the panel offers | Longest support runway; code needs no changes | ✓ |
| 7.4 conservative | Smaller jump, but already EOL since 2022 | |
| Decide live in the panel | No assumption about host support | |

### Second clarification — deliverability

The user asked: *"which of the methods has the least chance of email not being delivered
and is there a way to receive 100% of the client requests even if it would mean not using
email"*, then followed with: *"was wondering if there is a way to use telegram, viber or
any other messengers available and the form to submit messages there"*

Key points raised in response:
- Ranking: transactional relay > authenticated SMTP > `mail()`. But **"delivered" ≠
  "seen"** — the real loss mode is a spam folder or an unread mailbox.
- Telegram Bot API: free, a single HTTPS POST, push notification, `sendMediaGroup` carries
  the photos inline, group-capable.
- Viber: bot needs an admin-panel account plus a public webhook, users must subscribe
  first, business messaging is paid and partner-gated — flagged as needing verification
  against current docs rather than being ruled out from memory.
- **Outbound HTTPS from PHP on this host is unverified** and must be probed.

### Notification channel

| Option | Description | Selected |
|--------|-------------|----------|
| Telegram bot, email as backup | Push with photos inline; email keeps a paper trail | ✓ |
| Telegram only | Simplest, but no searchable archive | |
| Email only via transactional relay | No new app for the owner; still email | |
| Telegram + SMS | Works with no app installed; costs per message | |

### Server-side storage

| Option | Description | Selected |
|--------|-------------|----------|
| File-based outside the web root | Nothing can ever be lost; zero dependencies | |
| MySQL table | Queryable; more moving parts | |
| No — notifications only | No retention obligation, shorter privacy note | ✓ |

**Notes:** This choice cuts against the user's own "100%" framing and the consequence was
stated back explicitly — if both channels fail the enquiry is lost, though the visitor is
told and given the phone number. Accepted in exchange for the simpler privacy position.

### Autoresponder

| Option | Description | Selected |
|--------|-------------|----------|
| Yes — confirmation email | Proof for the customer; bounces reveal typos immediately | ✓ |
| No — on-screen confirmation is enough | Less to build | |

### Failure behaviour

| Option | Description | Selected |
|--------|-------------|----------|
| Honest error + phone number | Visitor knows and has somewhere to go | ✓ |
| Success anyway, log the failure | What the legacy mailer effectively does | |
| Generic error page | Honest but a dead end | |

---

## Photo uploads

### Limits

| Option | Description | Selected |
|--------|-------------|----------|
| Up to 5 photos, 10 MB each | Damage, model label and port | ✓ |
| Up to 3 photos, 5 MB each | Safe within default host limits | |
| One photo only | Smallest attack surface | |

### Oversize handling

| Option | Description | Selected |
|--------|-------------|----------|
| Shrink in the browser before upload | Nobody meets the limit; needs a no-JS fallback | ✓ |
| Reject with a clear message | No JS, but some customers will give up | |
| Accept and downscale server-side | Host's hard cap still binds | |

### Validation

| Option | Description | Selected |
|--------|-------------|----------|
| Verify content, re-encode, random filenames | Matters more here — this host executes `.html` as PHP | ✓ |
| Extension and MIME only | Both attacker-controlled | |

### Required or optional

| Option | Description | Selected |
|--------|-------------|----------|
| Optional | Many faults have nothing to photograph | ✓ |
| Required | Owner always has something to look at | |

---

## Analytics & consent

### Clarification requested before answering

The user said: *"owner is not into spending any extra money on additional tools so
genuinely speaking focus mostly on free alternatives, not sure what are the risks of not
showing the consent banner but prefer to avoid doing things that might be punishable"*

This ruled out Plausible/Fathom (paid) and reframed the question: the ePrivacy obligation
attaches to **storing things on the visitor's device**, not to counting visits — so a
cookieless tool needs no banner and there is nothing to be punished for. Microsoft Clarity
(free, unlimited) was explicitly ruled out as the most consent-hungry option; Matomo
self-hosted was ruled out as another PHP app needing security patching on the shop's host.

### Tool

| Option | Description | Selected |
|--------|-------------|----------|
| Umami Cloud free tier | Cookieless, free, custom events, readable dashboard | ✓ |
| Our own PHP beacon | Free forever, no third party, counts not dashboards | |
| Cloudflare Web Analytics | Free and cookieless, but weak on custom events | |
| GA4 with a consent banner | What the owner named; banner + undercount + 50KB JS | |

**Notes:** A deliberate departure from the owner's explicit request for Google Analytics.
Recorded as needing his sign-off, not merely the user's.

### Fallback if custom events fall short

| Option | Description | Selected |
|--------|-------------|----------|
| Add our own beacon for the clicks | Stays banner-free, answers everything | ✓ |
| Accept page-level data only | Drops one of the owner's two questions | |
| Fall back to GA4 and a banner | Complete data at the cost of the banner | |

### Events tracked

All four selected: call-button clicks, service-page visits, contact-form submissions,
form drop-off.

---

## Owner-editable hours & banner

### Settings format

| Option | Description | Selected |
|--------|-------------|----------|
| Plain-text settings file, not PHP | A typo can't blank all 19 pages | ✓ |
| Password-protected admin page | Friendliest; needs auth on a site with none | |
| Marked block in site-config.php | No parser, but live PHP in untrained hands | |

### Access route

| Option | Description | Selected |
|--------|-------------|----------|
| cPanel File Manager | Browser-based, login he already has | ✓ |
| FileZilla over FTP | More steps, more chances to misplace a file | |
| He doesn't — he asks you or me | Would knowingly not meet OWNER-01 | |

### Unspecified days

**User's choice (free text):** *"use Mon-Fri 8:00-16:00, there is no lunch break and
Saturday and Sunday shop is closed, but if not mentioned at least to me it sounds obvious
that it is closed if it is not mentioned"*

**Notes:** This instinct matches schema.org exactly — a day absent from `openingHours`
**is** closed. So `Mo-Fr 08:00-16:00` alone communicates the weekend closure to Google
without any "closed at weekends" text on the page. No conflict between the user's
preference and the structured-data requirement; both are satisfied by saying less.

### Banner behaviour

| Option | Description | Selected |
|--------|-------------|----------|
| Top strip site-wide, auto-expiring, not dismissible | A closure is what a visitor must not miss | ✓ |
| Same, but dismissible | Risks someone dismissing and forgetting | |
| Homepage only | Most visitors arrive on service pages from search | |

---

## Cutover mechanics & www canonical

### Swap method

| Option | Description | Selected |
|--------|-------------|----------|
| Server-side move, both directions | Seconds-long window; rollback is the reverse move | ✓ |
| Upload over the top of the root | Every old template file survives as an orphan | |
| Clear the root, then upload | Clean tree, but a genuinely broken window | |

### Canonical host

| Option | Description | Selected |
|--------|-------------|----------|
| `https://torin.bg` — no www | Matches the build; nothing changes | ✓ |
| `https://www.torin.bg` | Matches what ranks today; undoes three phases of config | |

**Notes:** Today all four protocol/host variants return 200 with **no redirect at all**,
and GSC's ranking variant is `http://www.torin.bg/` — the least preferred form. SEO-04
covered paths; nothing ever covered the host.

### Stale file cleanup

| Option | Description | Selected |
|--------|-------------|----------|
| Manual FileZilla pass in the checklist | Seven files, once, no destructive tooling | ✓ |
| Give deploy-new.sh a delete capability | Repeatable; worst-case failure is destructive | |
| Leave them — unreachable | Still fetchable by direct URL | |

### Go/no-go

| Option | Description | Selected |
|--------|-------------|----------|
| Full automated sweep, rollback on any failure | Follows redirects, not just headers | ✓ |
| Spot-check homepage and a service page | Would not have caught the defect that actually happened | |

---

## Replacing the Viber CTA

### Form location

| Option | Description | Selected |
|--------|-------------|----------|
| A dedicated contact page | Every CTA gets a destination; can rank for «контакти торин» | ✓ |
| A homepage section, as the nav assumes | No new page; long scroll from a service page | |
| Both | Most paths; two instances to keep consistent | |

### CTA replacement

| Option | Description | Selected |
|--------|-------------|----------|
| «Изпратете запитване» linking to the form | Keeps the call-first/write-second pairing | ✓ |
| An email link | Loses device model and photos | |
| Nothing — call button alone | Recreates the gap that killed the Viber button | |

---

## Performance & caching

### Images

| Option | Description | Selected |
|--------|-------------|----------|
| Add WebP alongside the JPEGs | ~30% smaller; polish on an already-decent baseline | ✓ |
| Leave them as they are | Spend the effort on the contact path | |
| Re-compress the JPEGs only | Smaller win, touches no markup | |

**Notes:** Measured during discussion — 1.3 MB across 43 files, largest 124 KB, zero WebP.
Phase 3 had already done the heavy lifting.

### Cache lifetimes

| Option | Description | Selected |
|--------|-------------|----------|
| Long for assets, short for HTML | Safe because of the existing `?v=<filemtime>` stamp | ✓ |
| Moderate everywhere | Forgiving if the stamp misbehaves | |
| Keep staging values, raise after launch | Launching knowingly slower | |

---

## Claude's Discretion

Offered as a final round and explicitly delegated — *"I'm ready for context — you decide
those"*:

- Spam protection beyond the honeypot (rate limiting; whether a CAPTCHA is worth
  reintroducing the third-party-cookie problem D4-18 avoids)
- Privacy note and `uslovia.html` data/device wording — to be flagged for owner approval
- `sitemap.xml` generation method and submission timing relative to the swap
- Telegram bot token handling

Also decided without asking, and recorded in CONTEXT.md: a submission counts as successful
if any one channel succeeds; photos transit PHP's temp directory and are deleted after
sending.

## Deferred Ideas

- Viber/WhatsApp as notification channels — set aside, but flagged for verification
  against current docs rather than ruled out from memory
- SMS notification — conflicts with the no-spend constraint
- A password-protected admin page for hours/banner — a candidate future phase
- A server-side enquiry archive — the fix if "we never got your enquiry" ever happens
- `verify-viber-button-before-launch.md` — **obsolete**, not deferred; the button is being
  removed, so close the todo rather than carrying it to cutover as a phantom blocker
- `redraw-category-icons.md` — out of scope, a Phase 3 item awaiting owner photographs
