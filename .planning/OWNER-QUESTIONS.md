# Questions for the Shop Owner

**Purpose:** A single running list of everything that needs an answer from the customer (ТОРИН КОМПЮТЪРС), so questions can be batched into occasional conversations instead of contacting them repeatedly.

**How to use:** Take a batch to the owner, record answers inline under each item, and change the status. Append new questions as they surface in any phase — never replace this file.

**Status key:** `OPEN` · `ASKED` · `ANSWERED` · `OBSOLETE`

**Note on numbering:** Item numbers are stable IDs referenced from phase CONTEXT.md files — they are grouped by category, not sequential. Never renumber existing items; new questions take the next unused number.

*Created: 2026-08-05 (during Phase 2 discussion)*
*Last updated: 2026-09-13 (Phase 3.5 execution, plan 03.5-05 — #32–#35 appended, all four recording
gaps the content-truth revision opened and could not honestly fill. Purely additive: no existing
entry was edited, re-opened or renumbered.)*

*Previously: 2026-08-19 (Phase 3 execution — #3 expanded into a full brief; #25–#27 added; every
open item reviewed and given the specifics it was missing)*

---

## Start here — if the owner only has twenty minutes

Ordered by what unblocks the most work per minute of their time. Everything else can wait for a
second conversation.

| # | Question | Why it is first |
|---|---|---|
| **3** | Category 6 — what is it? | The one page of sixteen with **zero** source material. Cannot be written without them. |
| **16** | What do customers actually say when they call? | Feeds every category's symptom line and much of the search traffic. Only they know it. |
| **20** | Working hours | Ships on all 16 pages *and* into Google's structured data. Wrong value sends customers to a closed shop. |
| **7** | GBP rating, review count, profile URL | Four values, two minutes with the profile open. Unblocks the rating badge, already built and waiting. |
| **23** | Warranty terms — and the 5–6 hours clause | Ships on every category page. The clause needs a ruling we should not make for them. |
| **24** | Free diagnostics — what if they decline? | A one-sentence answer that is either a strong differentiator or a dispute at the counter. |
| **22** | Which brands — Apple especially | A drafted list is live and marked `[ASSUMED]`. Needs a strike-through pass. |

**Two that need someone else's involvement, so worth starting early because they have lead time:**
**#1** (Google Search Console access) and **#6** (hosting control panel).

**One the owner owes an action, not an answer:** **#21** — provisioning a Viber account on
`088 9458404`. Decided already; nothing ships until it exists.

---

## Blocking — work cannot complete correctly without these

### 1. Google Search Console access
**Status:** ANSWERED 2026-09-11
**Question:** Which Google account verified ownership of torin.bg? The verification file `google1718743335455f1c.html` is live on the site, so someone has access. Can they grant it, or share the login?
**Why it matters:** Needed to cross-check the URL inventory against real indexed pages, to submit the sitemap, and to watch for ranking loss at cutover.
**Blocks:** MIGR-01 (retrofit), SEO-03, cutover monitoring in Phase 4
**Raised:** Phase 1 (D-08/D-09), still open

**If the account genuinely cannot be found, we are not stuck** — verification can be re-established
a different way, but each needs something only the owner can provide:
- **DNS TXT record** — needs the domain registrar login (who is the registrar for torin.bg?)
- **HTML file upload** — we already have FTP access, so this one we can do alone *if* you confirm
  it is acceptable to add a file to the site root
- **Google Analytics / Tag Manager** — is either already installed? (Nothing was found in the page
  source, but an account may exist.)

**Three more things worth asking in the same breath:**
- Is there a **Bing Webmaster Tools** account? Bing is small in Bulgaria but free to submit to.
- Has the site **ever been penalised or deindexed**, or had a manual action? If so we need to know
  before cutover, not after.
- Are there **other domains** pointing at this site (e.g. a `.com`, or an old domain redirecting in)?
  Those need handling at cutover too.
**Answer:** **No GSC account exists.** The owner will provide access to a **new** account created under the same
Google Business account. Registrar, Bing, penalty history and other-domain questions remain unasked.

**NEW SCOPE REQUEST:** add **Google Analytics** tracking for (a) the contact buttons and (b) which
service pages are visited, so the shop can see which services draw the most interest. This is new
work, not part of Phase 3 — it needs its own plan.


---

### 2. Should the contact form exist at all?
**Status:** ANSWERED 2026-09-11
**Question:** The new design leads with phone call + Viber/WhatsApp as the primary contact actions. Is a written enquiry form still wanted alongside them, or should it be dropped?
**Why it matters:** Determines whether `mailer.php` needs hardening at all (PHPMailer/SMTP/honeypot work in Phase 4), and how the CTA blocks are laid out.
**Blocks:** Phase 2 CTA design (partially — designed so removal is a subtraction), CONTACT-03 in Phase 4
**Raised:** Phase 2 discussion

**If the answer is "keep it", these all need answering too** — none can be guessed:
- **Which email address** should submissions go to? The current `mailer.php` sends somewhere; is
  that address still monitored, and by whom?
- **How quickly** does someone typically reply? If the form promises a response time, it has to be
  one the shop can meet.
- **Which fields** are actually useful — name, phone, email, device model, description of the fault?
  Should phone be required, given the shop prefers to call back?
- Should customers be able to **attach a photo** of the damage? (Technically more work, but for a
  repair shop it is genuinely useful.)
- **GDPR:** a form collecting personal data needs a privacy note and, in practice, a consent
  checkbox. Is there an existing privacy policy, or does one need writing? (`uslovia.html` exists —
  does it cover this?)

**If the answer is "drop it":** note this interacts with question **#21** — the Viber button is
currently a dead end for anyone without Viber installed, and the form is the obvious fallback for
those visitors. Dropping both leaves phone as the only path.
**Answer:** **Keep it.** Fields confirmed: **name, phone, email, device model, fault description, and photo
upload.** Photo upload was previously listed as optional-extra; it is now required scope.

Still unanswered and needed before build: destination email address, expected reply time, whether
phone is mandatory, and the GDPR consent/privacy position.


---

### 3. Category 6 — «Сервиз на нестандартно ел. оборудване»: what is actually in scope?
**Status:** ANSWERED (partial) 2026-09-11
**Blocks:** Phase 3 plan 03-05, Phase 4 cutover, ROADMAP success criterion 5
**Raised:** Phase 1, escalated in Phase 2, expanded 2026-08-19 during Phase 3 execution

**The situation in one line:** this is one of the six headline categories the owner explicitly asked
to feature, and there is **not one sentence about it anywhere on the current site**. Everything on
the new page will be written from the owner's answers here. Nothing can be inferred.

**Why it is now urgent rather than merely open.** Plan 03-05 builds this page in the current wave.
The page ships with structure, SEO metadata and CTAs regardless, but every factual claim on it has
to come from these answers. Until then it carries only what is provable from existing site content,
which is nothing — so it is the one page of sixteen at real risk of shipping thin, and a thin page
on a headline category damages rankings for the whole site rather than just itself.

**⚠ A complication found on 2026-08-19 that the owner should know about.** D3-05 assigns category 6
to the existing URL `problem-stari.html`, chosen because the slug reads as «стари» and category 6's
symptom line is «нестандартна или стара техника, която другаде не приемат». But that page's actual
content is about something else entirely: it is a long, genuinely expert technical article on
**low-quality batteries and adapters damaging the motherboard's charging circuitry** — the Charger
and StandBy processors, charge-current control, what fails and why. That is category 4 material, or
DIFF-02 battery material. It is not «нестандартна техника».

So the repurpose would overwrite real, substantial content with new content on an unrelated topic.
Three things follow, and the owner should decide, not the developer:

- **(a)** Is that battery/adapter article worth keeping? It reads as the most technically credible
  writing on the site and may be attracting search traffic for adapter/charging queries.
- **(b)** If it is worth keeping, where should it live — folded into category 4, or into
  `za-bateriite.html` (already the DIFF-02 battery depth page)?
- **(c)** If it moves, category 6 needs a different home: a new slug, or one of the other
  currently-unused pages.

---

#### 3a. What equipment does this cover?
Please be concrete — a list of actual things customers have brought in is far more useful than a
category name.

- What **kinds of devices**, beyond laptops and PCs? (Examples of the shape of answer: industrial
  controllers, medical or lab equipment, audio amplifiers, power supplies, TVs and monitors, kitchen
  or household appliance boards, car electronics, arcade or gaming hardware, test instruments,
  CNC/machine control boards, old/vintage computers…)
- Is there an **age or era** angle — genuinely old equipment for which parts are no longer made?
- Is there a **"nobody else will touch it"** angle — devices other shops refuse, and if so, *why*
  do they refuse (no parts, no schematics, no manufacturer support, too labour-intensive)?
- Are there **specific brands or device families** worth naming, the way the brand row names laptop
  brands?

#### 3b. What work is actually performed?
- What are the **three to five most common jobs** in this category?
- Is it mostly **board-level electronics** (the same BGA/soldering/chip-level skill the site already
  advertises), or does it also cover mechanical work, rewiring, or part fabrication?
- Does it use the **same equipment** already described on the site — the infrared BGA station, AMTECH
  flux — or different tooling?
- Roughly **how often** does this work come in? Is it a steady line, or occasional?

#### 3c. What is explicitly OUT of scope?
This matters as much as what is in scope: the page will generate enquiries, and the wrong enquiries
waste the shop's time on the phone.

- What do you **not** take? (White goods? Anything mains-powered above a certain rating? Anything
  requiring certification?)
- Are there **safety or legal limits** — equipment needing a licensed electrician, gas appliances,
  medical devices with regulatory constraints?
- Is there a **size or weight** limit given the shop's premises?
- Anything you'd want the page to **actively discourage**, so the phone doesn't ring for it?

#### 3d. Proof and specifics — what makes this credible?
The other five categories will carry real photographs and specific technical claims. This one has
none yet.

- **Photographs** of past non-standard jobs? Even phone snapshots. Nothing else on the page will
  carry the same weight.
- **One or two war stories** — a device someone else refused, what was wrong, how it was fixed. A
  single concrete example outperforms any amount of general description.
- Any **capability worth stating plainly** that a customer wouldn't assume (reverse-engineering
  without schematics, sourcing obsolete components, repairing a board that has no replacement
  available at any price)?

#### 3e. Commercial framing
- How does **pricing** work here — is it always quote-after-inspection, unlike the more predictable
  laptop repairs?
- Does **«безплатна диагностика»** apply to this category too, or is non-standard equipment assessed
  differently? (Interacts with question **#24** — the free-diagnostics promise ships on many pages.)
- Does the **warranty** in question **#23** cover this work on the same terms?
- What **turnaround** is realistic — is it inherently slower because parts must be sourced?

#### 3f. Naming
- The category is currently displayed as **«Нестандартна техника»** (shortened from the owner's
  original «Сервиз на нестандартно ел. оборудване», which is too long for a card). Is that the right
  name, or is there a phrase customers actually use?
- Its symptom line currently reads **«нестандартна или стара техника, която другаде не приемат»** and
  is marked `[ASSUMED]` — it was written by the developer, not the owner. Is it accurate?

**Answer:** **Category 6 is MEDICAL AND INDUSTRIAL EQUIPMENT.**

The owner also made an important structural point: this category sits **far apart** from laptops and
computers, so the service conventions that apply to categories 1-5 **do not transfer** - warranty,
turnaround and free diagnostics all work differently here (see #23 and #24, both of which now
explicitly carve category 6 out).

Still open from the sub-questions: 3a specific device types, 3b the common jobs, 3c what is
explicitly refused (the highest-cost gap - the page generates enquiries with nothing steering wrong
ones away), 3d photographs and war stories, 3e pricing and turnaround specifics, 3f whether
«Нестандартна техника» is the right name.


---

### 4. `covid.html` — has the EU publicity obligation expired?
**Status:** ANSWERED 2026-09-11
**Question:** The page publicises EU project **BG16RFOP002-2.073** (ОПИК 2014-2020, 10 000 лв, beneficiary ТОРИН КЪМПАНИ ООД). EU grants carry mandatory publicity obligations for a defined period. Has that period ended, and is it safe to retire the page?
**Why it matters:** Removing it too early risks an audit finding against the company — a legal/financial risk, not just an SEO one.
**Current plan (safe default):** Remove the content from the homepage, but keep `covid.html` live and unlinked. Costs nothing and carries no risk. Only retire it — with a 301 redirect, never a 404 — once this is confirmed.
**Also worth mentioning:** the page contains a copy-paste error — the results paragraph names **"Венера-АКС ООД"**, a different company.
**Blocks:** Nothing (safe default chosen), but resolves CONTENT-02 fully
**Raised:** Phase 1, detailed in Phase 2

**Specifically needed to close this:**
- **When did the project end?** The publicity obligation runs for a defined period from project
  completion (commonly five years for ОПИК grants, but it is stated in the contract). The contract
  or final report will give the exact date.
- **Is there paperwork** — the grant agreement or a closure letter — that states the publicity
  requirement and its duration? A photo of the relevant clause settles this permanently.
- **Who is the contact** at the managing authority if we need to ask directly?

**Also needs a decision, separately from the retirement question:** the page currently names
**«Венера-АКС ООД»** in its results paragraph — a different company, evidently copy-pasted from
another beneficiary's text. Should we (a) correct it to the right company name, (b) leave it exactly
as-is because it reproduces submitted grant text that shouldn't be altered, or (c) leave it and note
it? This is a judgement call about a document with a compliance dimension, so it should not be a
developer decision.
**Answer:** **Safe to remove.** The owner is not concerned about removing it completely. Developer research
indicates the website-publicity obligation has already expired and only document retention remains.

**Note:** this is research, not a sighted contract clause. Retirement should still use a **301
redirect, never a 404** (the project convention). The «Венера-АКС ООД» question (#28) becomes moot if
the page goes.


---

### 5. `problem-stari.html` — retire, merge, or keep?
**Status:** RESOLVED 2026-08-11 (Phase 3 discussion, D3-05) — kept, repurposed
**Question:** No current requirement covers this page. Content may overlap with `mehanichni-problemi.html` but this is unconfirmed. Keep it, merge it into another page, or retire it with a redirect?
**Why it matters:** It's a currently-indexed URL. Retiring it without review risks losing existing traffic.
**Blocks:** ~~Phase 3 IA finalisation~~ — resolved
**Raised:** Phase 1

**RESOLVED — the page is kept and becomes the category 6 page («Нестандартна техника»).**
Its slug reads as «стари», and category 6's own symptom line in `categories.php` is «нестандартна
или стара техника, която другаде не приемат» — the semantics fit. This puts category 6 on an
existing indexed URL rather than a new slug, inheriting whatever authority it holds, and needs no
new file. Nothing is retired, so no redirect and no ranking risk.

**The overlap suspicion was half right, against the wrong page.** A grep during the Phase 3
discussion found `problem-stari.html:181` duplicates the battery-regeneration paragraph from
`za-bateriite.html:158` **verbatim** — not `mehanichni-problemi.html` as this entry assumed. That
paragraph does not survive the repurpose (it also references the dead SmartBattery.eu domain — see
new item #22 context and D3-12).

**Still worth one sentence at owner review:** confirm the page's current traffic isn't coming from
something the repurpose would break.
**Answer:**

---

### 6. Is there a cPanel / hosting control-panel login for `bell.host.bg`?
**Status:** ANSWERED 2026-09-11
**Question:** Separate from the FTP credentials — is there a hosting control-panel account? If so, what are the details?
**Why it matters:** The host runs **PHP 5.2.17**. PHPMailer 6.x needs PHP ≥5.5. The host uses CloudLinux Alt-PHP, which normally exposes PHP-version switching through cPanel's "MultiPHP Manager" / "Select PHP Version". With a cPanel login, upgrading PHP is straightforward; without one, an older mail library must be chosen instead.
**Blocks:** Phase 4 (CONTACT-03)
**Raised:** Phase 1

**Useful even if no cPanel login exists:**
- **Which email address owns the hosting account?** Support will act on a request from the account
  holder even without a control-panel login — "please switch this account to PHP 7.4" is a normal
  support ticket.
- **Who set the hosting up** — the shop directly, or a developer/agency? If an agency, they may hold
  the login.
- Is there a **billing contact or invoice** for the hosting? Invoices usually name the account.
- Would you be comfortable with us **asking the host to upgrade PHP** on your behalf, if you forward
  one email authorising it?

**Why the PHP version matters beyond email:** PHP 5.2 was released in 2006 and has had no security
patches since 2011. Everything the redesign needs works on it, so this is not blocking — but it is
worth the owner knowing the server software is nineteen years old.
**Answer:** **Control-panel credentials obtained.** The owner has provided them. Walk-through of what is needed
(the PHP version upgrade for the mailer) is deferred until Phase 4 reaches that point.


---

### 20. Working hours — 8:00–16:00 or 9:00–17:00?
**Status:** ANSWERED 2026-09-11
**Question:** The current site contradicts itself about the shop's working hours:

| Source | Hours |
|---|---|
| `index.html`, `about.html` | Понеделник–Петък **8:00–16:00** |
| `profilaktika-laptop.html` | Понеделник–Петък **9:00–17:00** |
| `otpuska.js` banner, labelled «НОВО» | Понеделник–Петък **8:00 до 16:00** |

Which is correct today? (And are Saturday hours ever worked?)
**Why it matters:** Two of the three sources plus the banner agree on 8:00–16:00, so `profilaktika-laptop.html` is almost certainly stale — but this is an inference, not a confirmation. The redesign puts working hours in the footer of **all 16 pages** (D-33) and into the `LocalBusiness` structured data Google reads (D-34), so a wrong value ships site-wide and into search results at once, sending real customers to a closed shop. The interim value is marked `[ASSUMED]` in `src/includes/site-config.php` until this is answered.
**Blocks:** Must be resolved before the **Phase 4 cutover** — not before Phase 2 ends.
**Raised:** Phase 2 research (N-3), filed during plan 02-03

**The full set needed for the structured data**, not just the headline hours — Google renders these
directly in search results, so every gap becomes a guess:
- **Weekday hours** — confirmed start and end.
- **Is there a lunch break** when the shop is closed? Structured data can express it; if it exists
  and we omit it, customers arrive at a locked door.
- **Saturday** — closed, or open some hours? The current site says nothing at all about Saturday.
- **Sunday** — assumed closed; confirm.
- **Public holidays** — closed on all Bulgarian public holidays? (Interacts with **#8**, the holiday
  banner.)
- **Is drop-off different from collection?** Some workshops accept devices later than they handle
  counter enquiries.
- Should the site say anything about **calling ahead**, or is walk-in fine?

**Why this one is worth being fussy about:** the hours ship in the footer of all sixteen pages *and*
into the `LocalBusiness` structured data Google reads. A wrong value does not just look wrong — it
sends real customers to a closed shop, and Google may show "Open now" when you are not.
**Answer:** **8:00–16:00, Monday to Friday.** `profilaktika-laptop.html`'s 9:00–17:00 is stale.

**Independently corroborated 2026-09-11** by reading the Google Business Profile directly, which also
answers what the owner did not state: **Saturday and Sunday closed**, and **no lunch break** listed.

**NEW SCOPE REQUEST:** the hours should live in a **file the owner can edit**, which the site reads
to render them — rather than being changed by a developer. Same pattern as the #8 banner request.
Public holidays, drop-off-vs-collection and call-ahead remain unasked.


---

### 21. Which of the three numbers is reachable on Viber or WhatsApp?
**Status:** OPEN — ESCALATED 2026-08-09, no longer hypothetical
**Question:** Of `02 9549710`, `088 9458404` and `087 9128244` — which one can customers reach on Viber (or WhatsApp)? If more than one, which should the site advertise?
**Why it matters:** D-16 makes chat an equal-weight primary action alongside calling, so the chat button needs one specific number. There is no basis in any existing source for choosing among the three, and the button currently points at the main line as an `[ASSUMED]` placeholder. A chat link to a number that has no Viber account is a dead end on the site's single most important conversion action.
**Blocks:** Phase 4 (CONTACT-02) cannot be verified without it

**CONFIRMED BROKEN 2026-08-09 (UAT test 28, gap G-02-5, severity blocker).** This stopped being a precaution and became a measured defect. Tested on Android against the deployed staging site: pressing «Пишете във Viber» returns Viber's *"the requested page is unavailable. please update to the latest version"*, and the update prompt leads to a store page showing the latest version is already installed — so the client is current and the deep link simply does not resolve. Cause: `02 954 9710` is a Sofia **landline**, and Viber accounts are provisioned against mobile numbers. The two mobiles, `088 9458404` and `087 9128244`, are the only plausible candidates. The prediction written in this entry on the day it was filed — "a dead end on the site's single most important conversion action" — is exactly what happened, on 16 deployed pages.

**Root cause isolated 2026-08-09.** A developer's own number (known to have Viber) was deployed briefly as a control. The button opened a conversation — so the `viber://chat?number=` **scheme is correct** and candidate (b) is eliminated. The defect was purely the number. The dev placeholder has since been removed from the tree; it never needs to ship.

**ALL THREE NUMBERS ELIMINATED BY TEST, 2026-08-09.** Each was deployed to staging in turn and pressed on a real Android handset. All three fail identically:

| Number | Type | Result |
|---|---|---|
| `02 9549710` (`+35929549710`) | landline | no Viber account |
| `087 9128244` (`+359879128244`) | mobile | no Viber account |
| `088 9458404` (`+359889458404`) | mobile | no Viber account |

The first two were each chosen by plausibility and each was wrong, which is why the third was tested rather than assumed. Being a mobile turned out not to be sufficient.

**This is no longer a "which number" question.** The scheme is proven correct and the shop appears to have **no Viber presence on any number it publishes**. So the question the owner must actually answer is a design one under D-16, which makes chat an equal-weight primary action alongside calling:

1. Does the shop have a Viber account at all — perhaps on a number not currently published, or a Viber Business account?
2. If not, should the «Пишете във Viber» button be **removed**, **replaced** (WhatsApp? a written enquiry form? — note this interacts with question #2 above, whether the contact form should exist), or kept with some fallback?
3. If it stays, what should happen for a visitor with no Viber installed? Today that visitor also hits a dead end.

**RESOLVED 2026-08-09 — direction chosen, execution owed by the owner.** The button **stays**, on `088 9458404` (`+359889458404`), and the **owner will provision a Viber account on that number**. So the number in `site-config.php` is now the intended target rather than a placeholder, and it must not be changed while chasing this bug report.

That turns the remaining work into a **cutover gate rather than a code change**: before the site goes live, someone must press «Пишете във Viber» on a real handset with Viber installed and confirm it opens a conversation. Tracked as `.planning/todos/pending/verify-viber-button-before-launch.md` (`resolves_phase: 4`). G-02-5 is downgraded from open blocker to deferred-with-owner-action.

**STILL OPEN — the second half of this question.** What should happen for a visitor who has **no Viber installed at all**? Today the button is a dead end for them too, entirely independently of the account question, and that is not addressed by provisioning an account. If the answer is "nothing, accept it", that should be a recorded decision rather than an oversight. (Interacts with question #2 — whether a written enquiry form should exist as the alternative path.)

**Also needs deciding alongside the number:** what should happen for a visitor who has no Viber installed at all? Today the button is a dead end for them too, with no fallback.

**Raised:** Phase 2 (UI-SPEC C-9), filed during plan 02-03
**Answer:**

---

## Sign-off needed — decisions made on the owner's behalf

### 7. Google Business Profile — is it active, with reviews?
**Status:** OPEN — narrowed 2026-08-11, now a number-confirmation rather than an existence question
**Question:** Does the shop have a live Google Business Profile? Roughly how many reviews and what rating?
**Why it matters:** TRUST-02 calls for a Google rating badge linking to the profile. A badge pointing at an empty or non-existent profile is worse than none.
**Blocks:** TRUST-02 in Phase 3
**Raised:** Phase 1

**Largely answered by the developer's task list (2026-08-11):** aggregators report review counts in
the **128–146** range across different crawl dates. So the profile exists and is healthy — the
"is a badge worth having" half of this question is settled, and TRUST-02 proceeds.

**What is still needed:** the **live rating and review count pulled from GBP itself**, not from an
aggregator. Phase 3 ships these as hardcoded values in `site-config.php` (D3-07), so a wrong number
is a wrong number on every page until someone edits it. Use only the figure GBP shows.

**Also needed:** the profile URL the badge links to.

**Exactly what to write down while looking at the profile** (all four, from GBP itself — not from a
directory site, which is where the 128–146 spread came from):
1. The **rating** as GBP displays it (e.g. `4.8`)
2. The **review count** as GBP displays it
3. The **profile URL** — the "Share" link on the listing
4. The **date** you read them, so we know how stale the figure is

**Then three consistency checks while the profile is open** — these matter more for local search
ranking than the badge does:
- Is the **business name** on GBP exactly the trading name the site uses («ТОРИН КОМПЮТЪРС»)?
- Is the **address** identical, character for character, to `ул. Свети Иван Рилски №46, София 1606`?
  (See new question **#25** — the site itself is inconsistent about `№`.)
- Is the **phone number** on GBP one of the three the site lists, and is it the one that should be
  primary?

Google cross-checks name/address/phone between a site and its profile; a mismatch quietly costs
local ranking. Also worth noting: **which categories** the profile is listed under, and whether the
**opening hours** on GBP match the answer to question **#20**.
**Answer:**

---

### 8. `otpuska.js` holiday banner — keep or drop?
**Status:** ANSWERED 2026-09-11
**Question:** The site has a script that shows a holiday/absence banner. Is this still used? Should the redesign keep an equivalent, or drop it?
**Why it matters:** If kept, it needs a maintained modern replacement rather than the current jQuery-era implementation.
**Blocks:** Phase 4
**Raised:** Phase 1

**To decide this properly:**
- **How often is it actually used** — a couple of times a year for holidays, or more?
- **Who updates it** today, and how? (The current one needs a code edit, which likely means it is
  either never updated or updated by whoever built the site.)
- **What has it said** in the past? A real example tells us what the replacement must support.
- Should it also cover **unexpected closures** (illness, emergency), not just planned holidays?
- If we keep it, would you want to be able to **turn it on and off yourself** without a developer?
  That is buildable, but it changes the design — worth knowing now rather than later.

**Note it interacts with #20 (working hours):** if the shop closes for holidays, the structured data
Google reads should ideally reflect that too, or Google may show the shop as open when it is not.
**Answer:** **Keep, but rebuilt as owner-controlled.** Requirements:
- **Disabled by default.**
- Controlled from a **file the owner can edit** to define a vacation period.
- **Auto-hides once the period expires** - not only when someone remembers to switch it off.
- Redesign suggestions welcome.

Pairs with #20's request for an owner-editable working-hours file - same pattern, likely the same
mechanism.


---

### 9. DIFF-02 downgrade — battery regeneration placement
**Status:** RESOLVED 2026-08-11 (Phase 3 discussion, D3-11) — downgrade reversed, no sign-off needed
**Question:** The requirement says the battery-regeneration story (Panasonic-cell regeneration vs. simply reselling new batteries) should be "surfaced as a distinct differentiator." The current plan puts it in the folded «Не откривате проблема си?» section instead, to keep the six categories undiluted. Is that acceptable, or should it get its own prominent block?
**Why it matters:** It's a genuine competitive advantage no competitor offers. Folding it is a deliberate trade-off, recorded so it doesn't fail verification silently.
**Blocks:** ~~Phase 3 (DIFF-02 verification)~~ — resolved
**Raised:** Phase 2 discussion

**RESOLVED — DIFF-02 gets the prominent treatment the requirement actually asks for.** D-13's
folded placement is superseded. `za-bateriite.html` (a locked, indexed URL) becomes its depth page.
This question asked the owner to sign off on a downgrade; the downgrade no longer exists, so the
sign-off is moot and DIFF-02 moves from "knowingly unmet" to met.

**What forced it:** the developer confirmed on 2026-08-11 that **SmartBattery.eu no longer exists.**
The current site sends battery customers to that "специализиран сайт" from three places and lists
`office@smartbattery.eu` as a contact address in a fourth. With nowhere left to link out to,
`za-bateriite.html` has to carry the regeneration story itself — which is exactly what DIFF-02
wanted in the first place.
**Answer:**

---

### 10. Which theme goes live?
**Status:** CONFIRMED 2026-09-11
**Question:** Two themes will be built and switchable at `torin.bg/new` during development:
- **Theme A — logo colours:** amber `#fbad03` + electric blue `#0547dc`
- **Theme B — current site colours:** amber `#ffc70a` + navy `#0e305d`

Which should ship?
**Why it matters:** The switcher is development-only and gets removed at cutover with one theme hard-baked.
**Blocks:** ~~Phase 4 cutover~~ — resolved
**Raised:** Phase 2 discussion
**Answer:** **Theme B** confirmed by the owner.
 **Theme B** (`#ffc70a` + `#0e305d`) is the default and ships live. Theme A stays in the dev switcher as the comparison option. Still worth showing the owner both at `torin.bg/new` before cutover, since it's their brand.

---

### 11. Is there an original vector or high-resolution logo file?
**Status:** ANSWERED (partial) 2026-09-11
**Question:** The only logo on the site is `torin-logo.png` at **150×80 pixels**. Does an original vector (AI/EPS/SVG/PDF) or larger raster version exist — perhaps from whoever designed it?
**Why it matters:** At 150×80 the logo looks visibly soft on modern phone and laptop screens. If no original exists it will need redrawing from scratch.
**Blocks:** Logo redraw (timing flexible)
**Raised:** Phase 2 discussion

**Worth asking in the same conversation:**
- **Who designed it**, and are they contactable? Designers usually keep source files for years.
- Any **other places the logo exists at higher resolution** — a shop sign, vehicle livery, business
  cards, invoice template, a printer's file, an old Facebook cover?
- Are there **brand guidelines** or agreed exact colours, or were the site's colours simply picked
  at the time? (This bears on question **#10** — we chose Theme B by matching the current site, but
  if there is an official brand colour, that should win.)
- Is the logo **registered as a trademark**? If so the exact form matters legally and it must not be
  redrawn loosely.
- Is there a **favicon** source, or should one be generated from the logo? The current site has none
  at modern sizes.
**Answer:** A **larger raster image exists** and will be provided; delivery method to be arranged. Vector
source, designer contact, brand guidelines and trademark status all still unknown.


---

## Structural — reshapes the site's organisation

### 15. Is the sales line still active, and should it have nav prominence?
**Status:** ANSWERED 2026-09-11
**Question:** The site sells **употребявани лаптопи** (`laptopi.html`) and **резервни части** (`rezervni-chasti.html`) — a second business line alongside repair. Is this still active? The plan gives it a nav item **«Лаптопи и части»**. Is that the right weight, or is repair the only focus now?
**Why it matters:** If still active it's revenue that would otherwise disappear from navigation. If not, the pages should be handled differently.
**Blocks:** Phase 2 nav finalisation
**Raised:** Phase 2 discussion

**If the sales line is still active:**
- Is the **stock on those pages current**, or years out of date? (If stale, showing it is worse than
  not showing it — customers ask for machines that are long gone.)
- Should **prices** be shown, or is it enquire-only?
- Is it **used laptops only**, or also new? Refurbished-with-warranty?
- Do you want to be able to **update stock yourself**? A static site can carry a simple list, but a
  frequently-changing catalogue is a different kind of project and should be scoped separately.
- For **spare parts** — is that retail to walk-in customers, or mainly parts used in your own
  repairs? That changes whether the page is a sales page or a credibility page.

**If it is no longer active:** the two URLs are indexed, so they should be redirected rather than
deleted — tell us where each should point.
**Answer:** **The sales line is DISCONTINUED.** Selling used laptops and spare parts has stopped.

`laptopi.html` and `rezervni-chasti.html` are both indexed URLs, so they retire via **301 redirect,
never deletion**. Redirect targets still to be chosen.


---

### 16. What do customers actually say when they call?
**Status:** DEFERRED 2026-09-11
**Question:** In the owner's own words — what are the most common complaints, phrased the way customers phrase them? (e.g. «прегрява», «не се включва», «изключва се сам», «бавен е», «залях го»…) Ideally 4–6 per service category.
**Why it matters:** The new design puts a symptom line under each category card and builds a symptom-organised «Не откривате проблема си?» section. Customers describe *symptoms*; the six categories are named by *cause*. The owner hears the real phrasing daily — far better than inventing it.
**Blocks:** Phase 3 content
**Raised:** Phase 2 discussion

**The most valuable single answer in this entire document.** Every symptom line, every «Не откривате
проблема си?» entry, and much of the search traffic depends on matching the words customers actually
type and say. Invented phrasing is the difference between a page that ranks and one that does not.

**A structure that makes it easy** — four to six phrases per category, in the customer's words, not
the technician's:

| # | Category | Customer phrases |
|---|---|---|
| 1 | Счупвания и механични повреди | |
| 2 | Екран, клавиатура и портове | |
| 3 | Оптимизация | |
| 4 | Заливане и ремонт на дънни платки | |
| 5 | Прегряване и охлаждане | |
| 6 | Нестандартна техника | |

**Also worth capturing while you are thinking about it:**
- The **wrong self-diagnoses** customers arrive with — "they always say X when it's actually Y".
  These make excellent page content because they answer a question the customer already has.
- The **questions asked on every call** — price, how long, is my data safe, is it worth repairing?
  Answering those on the page reduces phone time.
- Anything customers say in **English or transliterated** («лаптопа ми не буутва», «дъното гърми»)
  — people search that way too.
**Answer:** The meeting ran out of time before reaching this. The developer will instead **research** what users
actually search for in these categories and propose phrasing for owner review.

This leaves every `[ASSUMED]` symptom line on the site unconfirmed, including the six category cards
and the «Не откривате проблема си?» section.


---

### 17. Categories 1 and 2 overlap — confirm the split and the new names
**Status:** ANSWERED 2026-09-11
**Question:** "Ремонт на счупвания" and "Смяна на матрици, клавиатури, USB портове, захранващи букси, панти" describe overlapping work — a cracked screen is both a счупване and a смяна на матрица. As originally written, category 1 had no services that weren't already in category 2.

**Decision taken (developer delegated this to Claude, 2026-08-05):**
- **Category 1 → «Счупвания и механични повреди»** — owns physical/impact damage: паднал лаптоп, счупен корпус, изкривено шаси, **счупени панти**
- **Category 2 → «Екран, клавиатура и портове»** — owns component replacement regardless of cause: матрици, клавиатури, USB/HDMI/аудио жакове, захранващи букси

**⚠ One thing to flag to the owner:** **панти (hinges) moved from category 2 to category 1.** The owner's original list put them under category 2, but under a physical-damage split they belong with breakage. Deliberate, not an oversight — worth a sentence at review.

**Why it matters:** Determines what content each of the two pages carries.
**Blocks:** Nothing — Phase 3 can proceed on this split
**Raised:** Phase 2 discussion
**Answer:** **The boundary is redefined by failure type, not by component.**

- **Category 1** — physical and mechanical damage to components.
- **Category 2** — malfunction or electronic fault.

**Implementation note (developer):** stated that way it is a technician's distinction — a customer
with a dark screen cannot tell which it is, which is what they are paying to find out. The agreed
customer-facing form of the same boundary is the observable proxy:

- **Category 1** — има видима повреда
- **Category 2** — изглежда здрав, но не работи

Revised symptom lines:
- **1 · Счупвания и механични повреди** — паднал лаптоп, счупен корпус, пукнат екран, счупени панти
- **2 · Екран, клавиатура и портове** — екранът не светва, клавиши не реагират, портът не зарежда

**Two inconsistencies this exposes in already-shipped work:**
1. `svc-panti` is parented to `kat-2`, but `kat-1`'s symptom line already claims «разхлабени панти».
2. `kat-2`'s symptom line leads with «пукнат екран», which is physical damage and belongs to
   category 1 under this rule.

**The five child pages are COMPONENT pages and should not be re-parented** — a component fails both
ways, and a cracked screen and a dark screen are the same repair. Both categories link to the
relevant children; `torin_service_href()` already supports this, so it is a data change.


---

### 19. Category 5 renamed — confirm
**Status:** CONFIRMED 2026-09-11
**Question:** "Смяна на вентилатори" describes the *fix*, but customers only know the *symptom* — «прегрява», «шуми», «изключва се сам». They may not recognise the category as theirs. It was also the thinnest of the six, holding essentially one service.

**Decision taken (developer delegated this to Claude, 2026-08-05):** renamed to **«Прегряване и охлаждане»**. Customers recognise the symptom immediately, and it naturally absorbs профилактика (dust cleaning, thermal paste), so the category is no longer thin.

**Is there a reason to keep the fan-replacement framing?** Worth one question at review.

**Full naming set now in use:**
| # | Name |
|---|---|
| 1 | Счупвания и механични повреди |
| 2 | Екран, клавиатура и портове |
| 3 | Оптимизация |
| 4 | Заливане и ремонт на дънни платки |
| 5 | Прегряване и охлаждане |
| 6 | Нестандартна техника |

**Blocks:** Nothing
**Raised:** Phase 2 discussion
**Answer:** **Keep «Прегряване и охлаждане».** The owner confirmed the developer's rename.


---

## Materials needed from the owner

### 12. Workshop photos to replace the six category icons
**Status:** OPEN
**Detail:** See the category photo brief in `.planning/phases/02-design-system-information-architecture/`. The design uses icons now, with every image slot built so a real photo can replace it later without any layout change.
**Blocks:** Nothing (icons ship first) — this is a later quality upgrade
**Raised:** Phase 2 discussion

---

### 13. Remaining site photos
**Status:** OPEN
**Detail:** See the general photo brief in the same folder — hero, workshop, team and trust imagery.
**Blocks:** Nothing immediately
**Raised:** Phase 2 discussion

---

### 14. (v2) Prices, turnaround times, before/after photos
**Status:** OPEN — deferred to v2
**Detail:** Indicative price ranges per category ("от X лв."), realistic turnaround commitments, and before/after repair photos. All deferred out of v1, but all depend on owner-supplied real numbers, so worth collecting whenever convenient.
**Blocks:** v2 requirements PRICE-01, TURNAROUND-01, GALLERY-01
**Raised:** Requirements definition (2026-08-04)

---

---

### 22. Which brands does Torin actually service — and which six to eight should the row name?
**Status:** ANSWERED 2026-09-11
**Question:** TRUST-01 puts a «Обслужваме всички марки» row on the site. Which brands does the shop
genuinely work on, and which six to eight should be named in the row?
**Why it matters:** The list currently in the roadmap — Lenovo, HP, Dell, Asus, Acer, Apple, MSI —
came from requirements drafting, **not from the owner**. Naming a brand the shop doesn't actually
service is a promise it can't keep; omitting one it specialises in loses searches. Apple/MacBook in
particular is worth confirming explicitly, since it often needs different parts and tooling than
the PC brands.
**Context from competitor research (2026-08-11):** eight competitor sites were examined; **none uses
brand logo images** and none claims authorized-service status. Several position explicitly as
«извънгаранционен сервиз». Torin's row ships as styled text wordmarks for the same reasons (D3-09).
**Blocks:** TRUST-01 content in Phase 3 (a drafted list ships marked `[ASSUMED]` until answered)
**Raised:** Phase 3 discussion

**Currently shipping, unconfirmed:** Lenovo · HP · Dell · Asus · Acer · Apple · MSI · «и др.»
Please strike out any you do not service and add any that are missing.

**The ones most worth an explicit yes/no:**
- **Apple / MacBook** — different parts, different tooling, often pentalobe screws and glued
  assemblies. Do you take them? Board-level too, or only simple work? This is a high-value search
  term, so a wrong answer either way is costly.
- **Lenovo ThinkPad vs consumer Lenovo** — worth distinguishing?
- **Gaming laptops** (MSI, ASUS ROG, Acer Predator) — these are thermally demanding and tie directly
  to category 5.
- **Business/workstation** brands — Fujitsu, Toshiba/Dynabook, Panasonic Toughbook, HP EliteBook?
- **Chromebooks** — commonly refused elsewhere; do you take them?
- **Desktops, all-in-ones, tablets** — the row says «марки», but do you service device types beyond
  laptops? (Interacts with **#3**, category 6.)

**Also:**
- Is there any brand you specifically **do not** take, that should be quietly omitted rather than
  implied by «и др.»?
- Do you hold **any authorised-service status** with a manufacturer? Competitor research found none
  of eight competitors claiming it — if Torin has any, that is a real differentiator worth stating.
  If not, we say nothing, which is what the site does now.
**Answer:** **Exclude Apple and Chromebook.** Both may still be accepted if a customer asks, but they are
preferably avoided and must not be advertised.

**LIVE FACTUAL ERROR:** `site-config.php:175` currently ships `Apple` in the brand row, so the
staging site presently advertises a brand the shop avoids. Needs removal.

The remaining names (Lenovo, HP, Dell, Asus, Acer, MSI) are unconfirmed individually, as are the
gaming, business and workstation sub-questions and authorised-service status.


---

### 23. Do warranty terms vary by type of repair?
**Status:** ANSWERED 2026-09-11
**Question:** TRUST-03 puts a warranty summary on every category page. Is it **one set of terms for
all repairs**, or do they differ — e.g. board-level work vs. a keyboard swap vs. software
optimisation? If they differ, what are the actual terms per type?
**Why it matters:** Phase 3 ships a single shared summary from `site-config.php` (D3-10), reused on
every category page. If terms really do vary, that shared block is wrong on some pages — and
warranty text is the kind of thing customers hold you to.
**Also worth confirming:** the existing warranty page requires the customer to **use the laptop 5–6
hours a day** during the warranty period, to build up 150–200 hours of test time. Is that still a
real condition? The redesign reframes it as a statement of confidence in the repair rather than a
condition that could void a claim — worth checking that reading is right.
**Blocks:** TRUST-03 accuracy in Phase 3
**Raised:** Phase 3 discussion

**Currently shipping site-wide, unconfirmed:** «1 месец гаранция на всеки ремонт».
Confirm or correct — this now renders on every category page.

**The detail the summary needs to be honest:**
- Does the term **vary by work type**? (Board-level BGA vs keyboard swap vs software optimisation vs
  category 6 non-standard work — plausibly all different.)
- Does it cover **parts and labour**, or labour only? What if a supplied part fails?
- Does it cover **only the specific fault repaired**, or the device generally? (Customers routinely
  assume the latter; stating it prevents a counter dispute.)
- **What voids it** — opening the device, liquid, impact, another shop touching it?
- Is a **receipt or service order** required to claim?
- If a repair fails within the term, is it **re-repair, refund, or your choice**?
- Does the warranty transfer if the **device is sold**?

**The 5–6 hours a day condition needs an explicit ruling.** The existing warranty page requires the
customer to *use the laptop 5–6 hours a day* during the warranty period, to accumulate 150–200 hours
of test time. The redesign reframes this as a statement of confidence rather than a condition that
could void a claim. **Is that reframing correct?** If it is genuinely a condition — i.e. a customer
who used the laptop lightly could be refused — then it must be stated as a condition, plainly, and
the reframing is wrong. This is the kind of clause customers hold you to, so it should not be a
developer's interpretation.
**Answer:** **1 month for all repairs, EXCEPT category 6** (medical and industrial equipment), where the standard
terms do not apply.

**Voided by:** liquid, impact, or another shop opening or working on the device — with the owner's own
caveat that there is currently **no reliable way to detect** third-party opening.

Still unanswered: parts-vs-labour cover, whether it covers the specific fault or the device
generally, receipt requirement, re-repair vs refund, and transferability on resale.

**The 5–6 hours a day clause was not ruled on.** `warrently.html` still reproduces it in the shop's
published wording, and the source comment forbidding harmonisation stays in force.


---

### 24. Free diagnostics — what happens if the customer declines the repair?
**Status:** ANSWERED 2026-09-11
**Question:** «Безплатна диагностика» appears across the site as a trust signal. If a customer has
the diagnosis done and then declines the repair, is it still free, or is there a fee?
**Why it matters:** Competitors commonly charge a declined-repair fee. If Torin genuinely doesn't,
that is a bullet worth stating plainly next to every «безплатна диагностика» mention — it's a real
differentiator. If Torin does charge, the site must say so, or it sets up a dispute at the counter.
**Blocks:** Nothing structurally — but the claim ships on many pages, so getting it wrong is
expensive to correct
**Raised:** Phase 3 discussion

**The boundaries of "free" need drawing, because customers will test every one of them:**
- Free **even if the customer declines** the repair — yes or no?
- Does it include **disassembly**? Diagnosing a liquid-damaged board means opening the machine;
  is that still free if they then walk away?
- Is there a **time or depth limit** — free for a quick assessment, chargeable for a full board-level
  investigation that takes hours?
- Does it apply to **category 6 non-standard equipment**, or only to laptops? (Interacts with **#3e**.)
- Is there a **fee for reassembly** if they decline, or is the device returned assembled at no cost?
- What about **data recovery** — usually a separate service with its own pricing. Is it in or out?
- Is diagnosis free for a device **another shop has already opened**?

**Why the precision is worth it:** if the answer is a clean unconditional yes, that is a genuinely
strong differentiator and deserves a prominent line next to every «безплатна диагностика» mention.
If it has conditions, the site must state them — an unqualified promise that turns out to have
conditions is exactly the kind of thing that produces a bad Google review, which then sits next to
the rating badge from question **#7**.
**Answer:** **Applies to categories 1–5 only**, not category 6.

**Wording change requested:** add «**първоначална**» (initial) so it reads as a quick basic
diagnostic rather than an unlimited free investigation — e.g. «безплатна първоначална диагностика».
This ships on many pages and every instance needs updating.

The boundary sub-questions (disassembly, reassembly fee, data recovery, already-opened devices)
remain unanswered.


---

### 25. Which legal entity operates the shop, and what is the ЕИК?
**Status:** ANSWERED (partial) 2026-09-11
**Question:** The site uses two different company names, and states no company registration number
anywhere:

| Name | Where it appears | Count |
|---|---|---|
| **ТОРИН КОМПЮТЪРС** | trading name, headers, titles, throughout | ~72 |
| **Торин Къмпани ООД** | `covid.html` (EU project text), legal pages | ~8 |

Which is the **registered legal entity** that trades as ТОРИН КОМПЮТЪРС, and what is its **ЕИК/
Булстат**? Is the company **VAT-registered** (регистрация по ДДС), and if so under what number?

**Why it matters — three separate reasons:**
1. **Legal pages.** `uslovia.html` (terms) and `warrently.html` (warranty) are contracts between a
   customer and a *company*. Bulgarian consumer-protection practice expects the trading entity,
   its ЕИК and its registered address to be identifiable. Right now a customer cannot tell from the
   site who they are contracting with.
2. **Structured data.** The `LocalBusiness` schema Google reads carries the business identity. Two
   names and no registration number is exactly the ambiguity that weakens an entity in Google's
   knowledge graph.
3. **The EU project page.** `covid.html` names ТОРИН КЪМПАНИ ООД as beneficiary. If that is a
   different legal entity from the one trading today, that is worth understanding before anything is
   published about it (see **#4**).

**Also needed:** is the **registered address** the same as the shop address (`ул. Свети Иван Рилски
№46, София 1606`), or is the company registered elsewhere? They are frequently different, and both
may need stating.
**Blocks:** Legal page accuracy (Phase 3 plan 03-06), `LocalBusiness` schema completeness
**Raised:** Phase 3 execution, 2026-08-19
**Answer:** - **Registered legal entity:** ТОРИН КЪМПАНИ ООД
- **Trade name to use everywhere:** ТОРИН КОМПЮТЪРС (as the logo says)
- **Address:** ул. Свети Иван Рилски 46

**⚠ The ЕИК was NOT provided** — and it is the single thing the legal pages actually need to make the
contracting party identifiable. Still outstanding, as is VAT registration status.


---

### 26. Address format — `46` or `№46`, and is there access guidance?
**Status:** ANSWERED 2026-09-11
**Question:** The site writes the address two ways — `ул. Свети Иван Рилски 46` and `ул. Свети Иван
Рилски №46`. Which is correct/preferred? And is there anything a first-time visitor needs to know to
actually find the door?
**Why it matters:** Minor typographically, but name/address/phone consistency between the site, the
Google Business Profile and directory listings is a real local-SEO signal, and we are about to
freeze one form into structured data on all sixteen pages. Worth settling once.
**Also worth capturing while on the subject:**
- Is the shop **visible from the street**, or inside a building/courtyard? Which floor?
- Is there **parking**, and is it paid (blue/green zone)?
- Nearest **metro/bus stop** or a recognisable landmark?
- Should the site carry a **map embed** or just a link to Google Maps? (An embed costs page weight
  and adds third-party tracking; a link costs nothing.)
**Blocks:** Nothing — but it ships site-wide, so cheap to fix now and annoying later
**Raised:** Phase 3 execution, 2026-08-19
**Answer:** **Use `ул. Свети Иван Рилски 46` — without the № sign.** The owner prefers the № sign aesthetically
but chose consistency with the Google Business Profile, which omits it.

Verified 2026-09-11: GBP renders it `ul. "Sveti Ivan Rilski" 46, 1606 Sofia`, with a «Пette Kyosheta»
district prefix the site does not use. Access guidance (floor, parking, landmark, map embed) still
unasked.


---

### 27. Customer data and device handling — what should the site promise?
**Status:** DEFERRED 2026-09-11
**Question:** Repair customers hand over devices containing personal data. What is the shop's actual
practice, and what should the site say about it?
**Why it matters:** "Is my data safe?" is one of the most common unspoken worries for anyone leaving
a laptop for repair, and almost no competitor addresses it. Answering it plainly is a cheap, real
differentiator — but only if the answer is accurate, since it becomes a public commitment.
**Specifically:**
- Do you **access customer data** at all during repair, and if so only as needed to test?
- Do you ever need the customer's **Windows password**? If so, say it upfront so it is not a
  surprise at the counter.
- Is there a **backup service**, or is the customer expected to back up first? Is that stated
  anywhere today?
- What happens to data on a **board that cannot be repaired**, or a drive that is replaced — is the
  old part returned to the customer?
- Are **unclaimed devices** disposed of after some period, and is data wiped first? (This is worth
  stating in the terms page either way.)
- Is there any **written disclaimer** the customer signs today at drop-off? If so, its content
  should inform `uslovia.html` rather than the page being written from scratch.
**Blocks:** Content accuracy on `uslovia.html` (Phase 3 plan 03-06)
**Raised:** Phase 3 execution, 2026-08-19
**Answer:** Direction rather than answer: **research what competitors say**, decide from there, and **skip
anything that can safely be skipped** rather than making commitments the shop has not thought
through.

Nothing on this ships until it is decided — silence is the safe state for a public commitment.


---

### 28. The EU project disclosure names a different company in its results paragraph
**Status:** OPEN — **new, raised 2026-08-19**
**Question:** The results paragraph on the funding disclosure page names **«Венера-АКС ООД»**, which
is not the beneficiary. Is the same wording in the project documentation you submitted, or is it only
on the website?

**Where exactly.** `site-current/covid.html:140`. The paragraph opens correctly — *«Постигане на
положителен ефект по отношение на ТОРИН КЪМПАНИ ООД…»* — and then, in the very next sentence, states
that *«Венера-АКС ООД»* is expected to continue operating for at least 3 months after the project
closes. Everything else on the page names ТОРИН КЪМПАНИ ООД. It reads exactly like a copy-paste from
another company's application form, which is a common way these paragraphs are produced.

**Why this is a question and not a fix.** It has deliberately **not** been corrected. A funding
disclosure is a compliance artefact, not marketing copy: if the same sentence appears in the
documentation submitted to the managing authority, then the website and the file must continue to
match, and quietly editing the website makes a future audit harder to explain rather than easier.
Correcting a published disclosure is the beneficiary's decision, not the developer's.

**What is needed from you:**
- Does the **submitted project documentation** contain the same company name in this sentence?
- If **yes** — do you want the website left exactly as it is, or corrected with a note, or corrected
  only after you have raised it with the managing authority?
- If **no**, and the error exists only on the website — may it be corrected to ТОРИН КЪМПАНИ ООД?
- Related, and worth answering together: **#25** asks which legal entity operates the shop. If
  ТОРИН КЪМПАНИ ООД is not the entity trading today, that changes what the disclosure page should say
  about itself as well.

**Note for whoever ports this page.** `src/covid.html` is still a stub; the disclosure text above
lives only in `site-current/covid.html`. When the real text is ported, this paragraph must be carried
across **verbatim, including the wrong name**, until this question is answered. Do not "clean it up"
during the port — the error is evidence of what was published, and losing it loses the ability to
answer the question above.

**Blocks:** Nothing in Phase 3 — the disclosure page stays live and unedited either way. Blocks a
final answer on `covid.html` content before cutover.
**Raised:** Phase 3 execution, 2026-08-19, filed during plan 03-05
**Answer:**

---

### 29. The funding disclosure publishes an address that looks misspelled
**Status:** OPEN — **new, raised 2026-08-23 during Phase 3 execution (plan 03-06)**
**Question:** The EU project disclosure gives the beneficiary's administrative address as
**«София, ул. Свсиленица 3А»**. «Свсиленица» is not a Sofia street name we can find — it looks like a
typo for something like «Св. Силеница» or a similar saint-name street. Is the published address
correct as written?
**Why it matters — and why it was NOT silently fixed:** this string is a **registered grant-record
field**, published in a compliance document. It has been ported byte-faithfully from
`site-current/covid.html:149` for exactly that reason: if the grant record itself carries this
spelling, the website should match the record rather than quietly diverge from it. Correcting it on
the site alone could create a mismatch between the published disclosure and the filed paperwork.
**So the real question is two-part:**
1. What is the **correct street name**?
2. Which spelling appears in the **grant paperwork itself**? If the paperwork carries the typo, the
   site probably should too — and the correction belongs with the managing authority, not here.
**Note it interacts with #4 and #28** — all three concern the same compliance document, so they are
worth taking to the owner together.
**Blocks:** Nothing — ported as-is is the safe state
**Raised:** Phase 3 execution, plan 03-06
**Answer:**

---

### 30. The consent line misspelling — depends on whether the contact form survives
**Status:** OPEN — **new, raised 2026-08-23**
**Question:** The legacy homepage carries a misspelling: `site-current/index.html:321` reads
«съгласявате с <a href="uslovia.html">**усливията** за поверителност</a>» — «усливията» should be
«**условията**». Decision D3-13 assigned this fix to the legal-pages port, but **the string does not
exist in the rebuilt tree at all**: it sits in the contact form's consent line, and the rebuilt
homepage has no contact form yet, because whether one should exist is still open as question **#2**.
**Why it matters:** the fix cannot be "done" until there is somewhere to do it. If the contact form
is kept, its consent line must read «условията» — this is the reminder that makes sure the
misspelling is not faithfully reproduced into the new site. If the form is dropped, this item
becomes obsolete along with it.
**Blocks:** Nothing now — becomes live the moment #2 is answered "keep the form"
**Raised:** Phase 3 execution, plan 03-06 (which correctly identified that the string was outside its files)
**Answer:**

---

### 31. SCOPE CHANGE — three service lines discontinued
**Status:** ANSWERED 2026-09-11 — **this changes requirements, not just content**
**Raised:** Owner conversation, recorded 2026-09-11

Three service lines the site currently advertises have been **discontinued by the business**. This is
not a defect in the delivered work — Phase 3 built exactly what was specified. The specification is
what changed.

| Discontinued | Requirement affected | Site surface |
|---|---|---|
| **Battery regeneration** | **DIFF-02** (verified Complete 2026-08-26) | `za-bateriite.html` + 7 other files |
| **BGA / reballing / chip-level** | **DIFF-03** (verified Complete 2026-08-26) | 57 claims across 9 files |
| **Sales line** — used laptops, spare parts | none directly | `laptopi.html`, `rezervni-chasti.html` |

**Two of the three differentiators Phase 3 exists to surface no longer exist.** Only **DIFF-01** (the
self-diagnostic tool) survives. The phase goal reads "surface the shop's genuine trust signals and
differentiators — assets no competitor currently offers", so the goal itself needs revisiting, not
just the pages.

**Category 6 is the likely replacement differentiator.** Medical and industrial equipment is
genuinely unusual — none of the eight competitors researched in Phase 3 touches it.

**Category 4 re-scoped (owner-confirmed 2026-09-11).** «Заливане и ремонт на дънни платки» keeps its
place as a headline category but now covers the **basic board services**:
- liquid cleaning
- corrosion removal
- **non-BGA** soldering — component-level work (capacitors, mosfets, fuses, connectors)
- **board replacement** where the case calls for it

Everything chip-level goes: the computer-controlled infrared BGA station, AMTECH flux, the 90%
success-rate claim, the three reballing stages, «северен мост / южен мост / видеочип».

**Three of the seven repair photographs deployed in Phase 3 are reballing photos** and must be
withdrawn with the claims: `profilaktika17.jpg` (the infrared station), `profilaktika7.jpg` (pads
cleaned before new balls), `profilaktika15.jpg` (hot-air-gun damage).

**Retirement method is not deletion.** `za-bateriite.html`, `laptopi.html` and `rezervni-chasti.html`
are all indexed URLs from the original 16. The project convention — set in #4 and upheld by SEO-04's
intent — is **301 redirect, never 404**. Redirect targets still to be chosen.

**Also reversed:** the **Viber button is dropped** in favour of the contact form (see #21 and #2).
That reverses D-16, which made chat an equal-weight primary action, and retires the cutover gate
tracked in `.planning/todos/pending/verify-viber-button-before-launch.md`.
**Answer:** Recorded. Requires a requirements revision before any content work — see STATE.md.

---

### 32. Category 6 warranty — the exclusion is now published, but nothing else is
**Status:** OPEN
**Question:** #23 established that the standard one-month warranty does **not** apply to
non-standard technics — medical and industrial equipment. What **does** apply? A shorter term? A
per-job term agreed before work starts? No warranty at all, stated plainly?
**Why it matters:** `problem-stari.html` now selects the `nonstandard` warranty entry, and that
entry states only the exclusion, because that is the entirety of what the owner ruled. A customer
reading the page learns what they do *not* get and is given nothing in its place. That is honest,
but it is a worse answer than a short term plainly stated would be, and it is the kind of gap that
gets filled at the counter by whoever is standing there — differently each time.
**Do not answer this by guessing.** A warranty term is the single most expensive sentence on this
site: it is a promise a customer holds the shop to, and an invented one creates a liability the
shop never agreed to. The gap is published as a gap deliberately (CONTEXT D3.5-08).
**Blocks:** ROADMAP SC-7 is met as ruled, but category 6 cannot be *published* with a complete
warranty statement until this is answered. Rides alongside **#3e**.
**Raised:** Phase 3.5 execution, plan 03.5-05, 2026-09-13. A named rider on the already-answered
**#23** — it does **not** re-open #23, whose own answer stands.
**Answer:**

---

### 33. Category 6 diagnostics — same shape as #32
**Status:** OPEN
**Question:** #24 established that free initial diagnostics applies to categories 1–5 **only**. For
category 6, is there a diagnostic fee? A flat call-out charge? Is it quoted case by case on the
phone? Or is assessment simply not offered without a commitment to the repair?
**Why it matters:** `problem-stari.html` previously promised a free assessment in six places — the
meta description, the intro, the services list, two steps of the process and an FAQ answer. All six
are now gone and the intro states the exclusion instead. As with #32, the page says what does not
apply and says nothing about what does, because the owner did not rule on it. Note that two of
those six were invisible to the automated token gate and were found by reading the page — so if the
answer to this changes, the change has to be made by reading too, not only by grepping.
**Blocks:** Same as #32 — not a blocker on ROADMAP SC-7, which is satisfied by stating the
exclusion, but a blocker on category 6 reading as a finished service page. Rides alongside **#3e**.
**Raised:** Phase 3.5 execution, plan 03.5-05, 2026-09-13. A named rider on the already-answered
**#24**; #24's own answer stands.
**Answer:**

---

### 34. Does the shop still **replace** laptop batteries, as distinct from regenerating them?
**Status:** OPEN
**Question:** #31 discontinued battery **regeneration** and the spare-parts **sales** line. Neither
of those is the same thing as fitting a replacement pack a customer brings, or one the shop sources
for a specific job. Does the shop do that? If yes, on what terms — customer-supplied packs only, or
does the shop source them?
**Why it matters:** «Батерията не издържа» is one of the commonest reasons anyone calls a laptop
repair shop at all. Following #31 the site now routes that symptom to charging-circuit and
power-rail diagnosis and **deliberately promises no replacement anywhere**, because no source
supports one. If the shop does in fact fit packs, that is a live service which is currently
invisible on its own website — a lost enquiry every time someone searches for it. If the shop does
not, the site is already correct and this closes as a confirmation.
**Blocks:** Nothing structurally. It is a potential *content gap*, which is why it is filed rather
than written: **DIFF-02** is retired and will not be revived on a guess.
**Raised:** Phase 3.5 execution, plan 03.5-05, 2026-09-13.
**Answer:**

---

### 35. The site no longer describes the workshop or its equipment anywhere
**Status:** OPEN
**Question:** Is there anything true that can be said today about the bench — tools, instruments,
working conditions — and about why a customer should bring a machine here rather than to the shop
next door?
**Why it matters:** `about.html` carried a paragraph describing the equipment, and the paragraph's
entire subject was the withdrawn service (#31). It was deleted outright rather than trimmed, and
**nothing was written in its place**, because no source describes the bench as it stands now. The
neighbouring paragraph about the technicians was restructured around component-level work using
wording already published on `problem-stari.html`, so the page has not been left with a hole — but
the shop's "why us" is now noticeably thinner than it was, and the site says nothing at all about
equipment. It also no longer links anywhere for that detail, because the page that held it is gone.
What is needed is either a few sentences the owner confirms, or a photograph or two of the actual
workbench (which overlaps **#12** and **#13**).
**Blocks:** No requirement directly. It is the honest record of a slot this phase emptied and
could not refill from any source (CONTEXT D3.5-08), so that the gap exists somewhere other than in
an executor's memory.
**Raised:** Phase 3.5 execution, plan 03.5-05, 2026-09-13.
**Answer:**

---

## Answered

*(none yet — move items here as they're resolved, keeping the answer inline)*
