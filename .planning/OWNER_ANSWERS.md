*Torin Computers · Website redesign*

# Questions for the shop owner

Everything that needs an answer from ТОРИН КОМПЮТЪРС, batched so the shop is asked once rather than repeatedly. Take it to the meeting; record answers under each item.

Updated 19 Aug 2026 — 17 open · 4 resolved · 3 confirm-only — Phase 3 of 4 in progress

---

## Start here — if the owner only has twenty minutes

Ordered by what unblocks the most work per minute of their time. Everything else can wait for a second conversation.

| # | Question | Why it is first |
| --- | --- | --- |
| 3 | Category 6 — what is it? | The one page of sixteen with **zero** source material. Cannot be written without them. |
| 16 | What do customers actually say when they call? | Feeds every category's symptom line and much of the search traffic. Only they know it. |
| 20 | Working hours | Ships on all 16 pages and into Google's structured data. A wrong value sends customers to a closed shop. |
| 7 | Google rating, review count, profile URL | Four values, two minutes with the profile open. Unblocks the rating badge, already built and waiting. |
| 23 | Warranty terms — and the 5–6 hours clause | Ships on every category page. The clause needs a ruling we should not make for them. |
| 24 | Free diagnostics — what if they decline? | A one-sentence answer that is either a strong differentiator or a dispute at the counter. |
| 22 | Which brands — Apple especially | A drafted list is live and marked assumed. Needs a strike-through pass. |

Two need someone else's involvement, so start them early for lead time: **#1** (Google Search Console access) and **#6** (hosting control panel). One needs an action rather than an answer: **#21** — provisioning a Viber account on 088 9458404.

---

## Blocking — work cannot complete correctly without these

### Category 6 — «Сервиз на нестандартно ел. оборудване»: what is actually in scope?

_Blocking · Biggest content gap · Phase 3 · plan 03-05_

This is one of the six headline categories the owner explicitly asked to feature, and there is **not one sentence about it anywhere on the current site**. Everything on the new page will be written from these answers. Nothing can be inferred.

**Why it is urgent now.** Plan 03-05 builds this page in the current wave. It ships with structure, metadata and CTAs regardless, but every factual claim has to come from these answers — so it is the one page of sixteen at real risk of shipping thin, and a thin page on a headline category damages rankings for the whole site, not just itself.

**A complication found on 19 Aug that the owner should know about.** The plan assigns category 6 to the existing URL `problem-stari.html`, chosen because the slug reads as «стари». But that page's actual content is something else entirely: a long, genuinely expert technical article on **low-quality batteries and adapters damaging the motherboard's charging circuitry** — the Charger and StandBy processors, charge-current control, what fails and why. That is category 4 material, or battery material. It is not «нестандартна техника».

So the repurpose would overwrite real, substantial content with new content on an unrelated topic. Three things follow, and the owner should decide:

- Is that battery/adapter article worth keeping? It reads as the most technically credible writing on the site and may be attracting search traffic for adapter and charging queries.
- If so, where should it live — folded into category 4, or into `za-bateriite.html`, already the battery depth page?
- If it moves, category 6 needs a different home: a new slug, or one of the other unused pages.

#### 3a · What equipment does this cover?

*Be concrete — a list of actual things customers have brought in is far more useful than a category name.*

- What **kinds of devices**, beyond laptops and PCs? (Shape of answer: industrial controllers, medical or lab equipment, audio amplifiers, power supplies, TVs and monitors, appliance boards, car electronics, arcade or gaming hardware, test instruments, CNC boards, vintage computers…)
- Is there an **age or era** angle — equipment for which parts are no longer made?
- Is there a **"nobody else will touch it"** angle — and if so, *why* do others refuse (no parts, no schematics, no manufacturer support, too labour-intensive)?
- Any **specific brands or device families** worth naming, the way the brand row names laptop brands?

#### 3b · What work is actually performed?

- The **three to five most common jobs** in this category?
- Mostly **board-level electronics** — the same BGA and chip-level skill the site already advertises — or also mechanical work, rewiring, part fabrication?
- Does it use the **same equipment** already described on the site (the infrared BGA station, AMTECH flux), or different tooling?
- Roughly **how often** does this work come in — a steady line, or occasional?

#### 3c · What is explicitly out of scope?

*This matters as much as what is in scope: the page will generate enquiries, and the wrong enquiries waste time on the phone.*

- What do you **not** take? (White goods? Anything mains-powered above a certain rating? Anything requiring certification?)
- **Safety or legal limits** — equipment needing a licensed electrician, gas appliances, medical devices with regulatory constraints?
- A **size or weight** limit, given the premises?
- Anything the page should **actively discourage**, so the phone doesn't ring for it?

#### 3d · Proof and specifics — what makes this credible?

*The other five categories carry real photographs and specific technical claims. This one has none yet.*

- **Photographs** of past non-standard jobs — even phone snapshots. Nothing else on the page will carry the same weight.
- **One or two war stories** — a device someone else refused, what was wrong, how it was fixed. A single concrete example outperforms any amount of general description.
- Any **capability worth stating plainly** that a customer wouldn't assume — reverse-engineering without schematics, sourcing obsolete components, repairing a board with no replacement available at any price?

#### 3e · Commercial framing

- How does **pricing** work — always quote-after-inspection, unlike the more predictable laptop repairs?
- Does **«безплатна диагностика»** apply here too? (Interacts with **#24** — that promise ships on many pages.)
- Does the **warranty** in **#23** cover this work on the same terms?
- What **turnaround** is realistic — inherently slower because parts must be sourced?

#### 3f · Naming

- Currently displayed as **«Нестандартна техника»**, shortened from the original «Сервиз на нестандартно ел. оборудване» which is too long for a card. Right name, or is there a phrase customers actually use?
- Its symptom line reads **«нестандартна или стара техника, която другаде не приемат»** and is marked assumed — written by the developer, not the owner. Accurate?

**Answer:**: what falls into this category is medical and industrial equipment. This category is quite far apart from rest of the devices being fixed => laptops and computers, so most of the related to things related to the fixes like warranty turnaround etc do not exactly apply. 

---

### Google Search Console access

_Blocking · Has lead time — start early_

Which Google account verified ownership of torin.bg? The verification file `google1718743335455f1c.html` is live on the site, so someone has access. Can they grant it, or share the login?

**Why it matters.** Needed to cross-check the URL inventory against real indexed pages, submit the sitemap, and watch for ranking loss at cutover.

**If the account genuinely cannot be found, we are not stuck** — verification can be re-established another way, but each needs something only the owner can provide:

- **DNS TXT record** — needs the domain registrar login. Who is the registrar for torin.bg?
- **HTML file upload** — we already have FTP access, so we can do this alone *if* you confirm it is acceptable to add a file to the site root.
- **Google Analytics / Tag Manager** — is either already installed? Nothing was found in the page source, but an account may exist.

Three more worth asking in the same breath:

- Is there a **Bing Webmaster Tools** account? Small in Bulgaria, but free to submit to.
- Has the site **ever been penalised or deindexed**, or had a manual action? We need to know before cutover, not after.
- Are there **other domains** pointing here — a `.com`, or an old domain redirecting in? Those need handling at cutover too.

**Answer:** it would appear there is no GSC acc, but i can provide access to a new acc i've enabled on the same google business account that the owner provided me. also i would like to have google analytics added for the contact buttons and also the services that has been visited so i know which ones are those visited the most. 

---

### Should the contact form exist at all?

_Open · Phase 4 · CONTACT-03_

The new design leads with phone call plus Viber as the primary contact actions. Is a written enquiry form still wanted alongside them, or should it be dropped?

**Why it matters.** Determines whether `mailer.php` needs hardening at all, and how the CTA blocks are laid out.

**If the answer is "keep it", these all need answering too** — none can be guessed:

- **Which email address** should submissions go to? The current script sends somewhere; is that address still monitored, and by whom?
- **How quickly** does someone typically reply? If the form promises a response time, it must be one the shop can meet.
- **Which fields** are useful — name, phone, email, device model, fault description? Should phone be required, given the shop prefers to call back?
- Should customers be able to **attach a photo** of the damage? More work technically, but genuinely useful for a repair shop.
- **GDPR:** a form collecting personal data needs a privacy note and, in practice, a consent checkbox. Is there an existing privacy policy, or does one need writing? `uslovia.html` exists — does it cover this?

**If the answer is "drop it":** this interacts with **#21** — the Viber button is currently a dead end for anyone without Viber installed, and the form is the obvious fallback. Dropping both leaves phone as the only path.

**Answer:** Yes the contact form should exist. as of now we add all these name, phone, email, device model, fault description as well as uploading photos.

---

### `covid.html` — has the EU publicity obligation expired?

_Open · Legal / compliance_

The page publicises EU project **BG16RFOP002-2.073** (ОПИК 2014-2020, 10 000 лв, beneficiary ТОРИН КЪМПАНИ ООД). EU grants carry mandatory publicity obligations for a defined period. Has that period ended, and is it safe to retire the page?

**Why it matters.** Removing it too early risks an audit finding against the company — a legal and financial risk, not just an SEO one.

**Safe default already chosen:** remove the content from the homepage, but keep `covid.html` live and unlinked. Costs nothing, carries no risk. Only retire it — with a 301 redirect, never a 404 — once confirmed.

Specifically needed to close this:

- **When did the project end?** The obligation runs for a defined period from completion, commonly five years for ОПИК grants, but the contract states it exactly.
- **Is there paperwork** — the grant agreement or closure letter — stating the requirement and its duration? A photo of the clause settles this permanently.
- **Who is the contact** at the managing authority if we need to ask directly?

**A separate decision, independent of retirement:** the page names **«Венера-АКС ООД»** in its results paragraph — a different company, evidently copy-pasted from another beneficiary's text. Should we (a) correct it, (b) leave it exactly as-is because it reproduces submitted grant text that shouldn't be altered, or (c) leave it and note it? A judgement call about a document with a compliance dimension, so not a developer decision.

**Answer:**in general the owner itself is not worried to remove it completely. i did a reserch and it would appear only a paperwork related to this grants is needed and the obligations related to the publicity on the website has already expired

---

### Is there a cPanel or hosting control-panel login?

_Open · Has lead time — start early_

Separate from the FTP credentials — is there a hosting control-panel account for `bell.host.bg`?

**Why it matters.** The host runs **PHP 5.2.17**. Modern mail libraries need PHP 5.5 or newer. With a control-panel login, upgrading is straightforward; without one, an older library must be chosen instead.

Useful even if no login exists:

- **Which email address owns the hosting account?** Support will act on a request from the account holder even without a login — "please switch this account to PHP 7.4" is a normal ticket.
- **Who set the hosting up** — the shop directly, or a developer or agency? An agency may hold the login.
- Is there a **billing contact or invoice**? Invoices usually name the account.
- Would you be comfortable with us **asking the host to upgrade PHP** on your behalf, if you forward one email authorising it?

*Worth the owner knowing: PHP 5.2 was released in 2006 and has had no security patches since 2011. Everything the redesign needs works on it, so this is not blocking — but the server software is nineteen years old.*

**Answer:** i've been provided credentials to the control panel for the host, when we get to this point we can go through the details of what you will need or guide me through what i need to do.

---

### Working hours — 8:00–16:00 or 9:00–17:00?

_Blocks cutover · Ships on all 16 pages_

The current site contradicts itself: `index.html` and `about.html` say **8:00–16:00**; `profilaktika-laptop.html` says **9:00–17:00**; the holiday banner says **8:00–16:00**. Which is correct today?

**Why it matters.** Two of three sources plus the banner agree on 8:00–16:00, so the odd page is almost certainly stale — but that is an inference, not a confirmation. The hours ship in the footer of all sixteen pages *and* into the structured data Google reads, so a wrong value goes site-wide and into search results at once, sending real customers to a closed shop.

**The full set needed for the structured data**, not just the headline hours — every gap becomes a guess:

- **Weekday hours** — confirmed start and end.
- **Is there a lunch break** when the shop is closed? Structured data can express it; if it exists and we omit it, customers arrive at a locked door.
- **Saturday** — closed, or open some hours? The current site says nothing at all about Saturday.
- **Sunday** — assumed closed; confirm.
- **Public holidays** — closed on all Bulgarian public holidays? (Interacts with **#8**.)
- **Is drop-off different from collection?** Some workshops accept devices later than they handle counter enquiries.
- Should the site say anything about **calling ahead**, or is walk-in fine?

**Answer:**i think this has been already resolved but once again 8:00–16:00 is the one to be used from monday to friday, however i would like to create a file where this could be easily modified by the owner and this is file is read to display the working hours, if u have any concerns here let me know and see if it could be resolved.

---

## Sign-off needed — decisions made on the owner's behalf

### Google Business Profile — the live rating, count and URL

_Open · Badge built, gated off, waiting_

Aggregators report review counts in the **128–146** range across crawl dates, so the profile exists and is healthy — the "is a badge worth having" question is settled. What is still needed is the live figure from the profile itself.

**Why it matters.** These ship as fixed values on every page, so a wrong number stays wrong until someone edits it. Use only what the profile itself shows, never an aggregator — that is where the 128–146 spread came from.

**Exactly what to write down while looking at the profile:**

1. The **rating** as displayed, e.g. 4.8
2. The **review count** as displayed
3. The **profile URL** — the "Share" link on the listing
4. The **date** you read them, so we know how stale the figure is

**Then three consistency checks while the profile is open** — these matter more for local search ranking than the badge does:

- Is the **business name** exactly the trading name the site uses, «ТОРИН КОМПЮТЪРС»?
- Is the **address** identical, character for character, to ул. Свети Иван Рилски №46, София 1606? (See **#26** — the site itself is inconsistent about the № sign.)
- Is the **phone number** one of the three the site lists, and is it the one that should be primary?

Google cross-checks name, address and phone between a site and its profile; a mismatch quietly costs local ranking. Also note **which categories** the profile is listed under, and whether its **opening hours** match the answer to **#20**.

**Answer:** once again this is something you already resolved, however i'd like to clarify here is by using the business account there is not a programatic way to obtain the number needed. if it is not possible then use the numbers that u obtain and for the count of reviews use the + sign at the end rounding the number so it is accurete for a longer period of time. also since there is a missmatch in the names used in the bussines account as well the ones used in the page i would like to know if the name displayed in the business account can be changed and how it could be done as TORIN COMPUTERS is the name to be used

---

### Do warranty terms vary by type of repair?

_Open · Ships on every category page_

Currently shipping site-wide, unconfirmed: **«1 месец гаранция на всеки ремонт»**. Confirm or correct.

**Why it matters.** A single shared summary is reused on every category page. If terms really do vary, that block is wrong on some pages — and warranty text is the kind of thing customers hold you to.

The detail the summary needs to be honest:

- Does the term **vary by work type**? Board-level BGA vs keyboard swap vs software optimisation vs category 6 work — plausibly all different.
- Does it cover **parts and labour**, or labour only? What if a supplied part fails?
- Does it cover **only the specific fault repaired**, or the device generally? Customers routinely assume the latter.
- **What voids it** — opening the device, liquid, impact, another shop touching it?
- Is a **receipt or service order** required to claim?
- If a repair fails within the term, is it **re-repair, refund, or your choice**?
- Does the warranty transfer if the **device is sold**?

**The 5–6 hours a day clause needs an explicit ruling.** The existing warranty page requires the customer to use the laptop 5–6 hours a day during the warranty period, to accumulate 150–200 hours of test time. The redesign reframes this as a statement of confidence rather than a condition that could void a claim.

**Is that reframing correct?** If it is genuinely a condition — a customer who used the laptop lightly could be refused — then it must be stated as a condition, plainly, and the reframing is wrong. This should not be a developer's interpretation.

**Answer:** the warranty is 1 month valid for all except the category 6. here all the standard things should apply e.g. the warranty is void liquid, impact, another shop touching or opening the device (although no real way to confirm it exists currently)

---

### Free diagnostics — what happens if the customer declines the repair?

_Open · Claim appears on many pages_

«Безплатна диагностика» appears across the site as a trust signal. If a customer has the diagnosis done and then declines the repair, is it still free, or is there a fee?

**Why it matters.** Competitors commonly charge a declined-repair fee. If Torin genuinely doesn't, that is worth stating plainly next to every mention — a real differentiator. If Torin does charge, the site must say so, or it sets up a dispute at the counter.

**The boundaries of "free" need drawing, because customers will test every one:**

- Free **even if the customer declines** — yes or no?
- Does it include **disassembly**? Diagnosing a liquid-damaged board means opening the machine; still free if they then walk away?
- A **time or depth limit** — free for a quick assessment, chargeable for a full board-level investigation taking hours?
- Does it apply to **category 6 equipment**, or only laptops?
- A **fee for reassembly** if they decline, or is the device returned assembled at no cost?
- **Data recovery** — usually a separate service with its own pricing. In or out?
- Is diagnosis free for a device **another shop has already opened**?

*If the answer is a clean unconditional yes, it deserves a prominent line everywhere. If it has conditions, the site must state them — an unqualified promise that turns out to have conditions is exactly what produces a bad Google review, which then sits next to the rating badge from #7.*

**Answer:** here also the free diagnostics applies for the first 5 categories. also here would be nice to add 'initial' to make it clear that only a quick basic diagnostic is meant here.

---

### Which brands does Torin actually service?

_Open · Drafted list is live, marked assumed_

Currently shipping, unconfirmed: **Lenovo · HP · Dell · Asus · Acer · Apple · MSI · «и др.»** Please strike out any you do not service and add any that are missing.

**Why it matters.** The list came from requirements drafting, not from the owner. Naming a brand the shop doesn't service is a promise it can't keep; omitting one it specialises in loses searches.

The ones most worth an explicit yes or no:

- **Apple / MacBook** — different parts, different tooling, pentalobe screws and glued assemblies. Do you take them? Board-level too, or only simple work? A high-value search term, so a wrong answer either way is costly.
- **Lenovo ThinkPad vs consumer Lenovo** — worth distinguishing?
- **Gaming laptops** (MSI, ASUS ROG, Acer Predator) — thermally demanding, ties directly to category 5.
- **Business and workstation** brands — Fujitsu, Toshiba/Dynabook, Panasonic Toughbook, HP EliteBook?
- **Chromebooks** — commonly refused elsewhere; do you take them?
- **Desktops, all-in-ones, tablets** — do you service device types beyond laptops? (Interacts with **#3**.)

Also: any brand you specifically **do not** take, that should be quietly omitted rather than implied by «и др.»? And do you hold **any authorised-service status** with a manufacturer? Competitor research found none of eight competitors claiming it — if Torin has any, that is a real differentiator. If not, we say nothing, which is what the site does now.

**Answer:** brands that have to be excluded are apple and chromebook. although if asked still possible to be accepted they are preferrably avoided

---

## Content only the owner can supply

### What do customers actually say when they call?

_Open · Highest-value answer in this document_

In the owner's own words — the most common complaints, phrased the way customers phrase them. For example «прегрява», «не се включва», «изключва се сам», «бавен е», «залях го». Ideally four to six per category.

**Why it matters.** Every symptom line, every entry in the «Не откривате проблема си?» section, and much of the search traffic depends on matching the words customers actually type and say. Customers describe *symptoms*; the six categories are named by *cause*. Invented phrasing is the difference between a page that ranks and one that does not.

| # | Category | Customer phrases |
| --- | --- | --- |
| 1 | Счупвания и механични повреди |  |
| 2 | Екран, клавиатура и портове |  |
| 3 | Оптимизация |  |
| 4 | Заливане и ремонт на дънни платки |  |
| 5 | Прегряване и охлаждане |  |
| 6 | Нестандартна техника |  |

Also worth capturing while thinking about it:

- The **wrong self-diagnoses** customers arrive with — "they always say X when it's actually Y". These make excellent page content because they answer a question the customer already has.
- The **questions asked on every call** — price, how long, is my data safe, is it worth repairing? Answering those on the page reduces phone time.
- Anything said in **English or transliterated** — «лаптопа ми не буутва», «дъното гърми». People search that way too.

**Answer:** here we did not have the time to go into details but i would suggest that u make a research related to what users are usually searching related to these categories

---

### Which legal entity operates the shop, and what is the ЕИК?

_Open · New · 19 Aug · Legal pages_

The site uses two different company names and states no company registration number anywhere: **ТОРИН КОМПЮТЪРС** appears about 72 times as the trading name, and **Торин Къмпани ООД** about 8 times in the EU project text and legal pages.

Which is the **registered legal entity** trading as ТОРИН КОМПЮТЪРС, and what is its **ЕИК / Булстат**? Is the company VAT-registered, and if so under what number?

**Why it matters — three separate reasons.** 

1. **Legal pages.** The terms and warranty pages are contracts between a customer and a company. Bulgarian consumer-protection practice expects the trading entity, its ЕИК and its registered address to be identifiable. Right now a customer cannot tell from the site who they are contracting with.
2. **Structured data.** The business schema Google reads carries the identity. Two names and no registration number is exactly the ambiguity that weakens an entity in Google's knowledge graph.
3. **The EU project page.** It names ТОРИН КЪМПАНИ ООД as beneficiary. If that is a different legal entity from the one trading today, that is worth understanding before anything is published about it — see **#4**.

Also needed: is the **registered address** the same as the shop address, or is the company registered elsewhere? They are frequently different, and both may need stating.

**Answer:** the official name of the company is ТОРИН КЪМПАНИ ООД, however the preferred trade name to be used just as the logo says it is ТОРИН КОМПЮТЪРС, the address to be used is ул. Св, Иван Рилски 46


---

### Customer data and device handling — what should the site promise?

_Open · New · 19 Aug_

Repair customers hand over devices containing personal data. What is the shop's actual practice, and what should the site say about it?

**Why it matters.** "Is my data safe?" is one of the most common unspoken worries for anyone leaving a laptop for repair, and almost no competitor addresses it. Answering plainly is a cheap, real differentiator — but only if accurate, since it becomes a public commitment.

- Do you **access customer data** at all during repair, and if so only as needed to test?
- Do you ever need the customer's **Windows password**? If so, say it upfront so it is not a surprise at the counter.
- Is there a **backup service**, or is the customer expected to back up first? Is that stated anywhere today?
- What happens to data on a **board that cannot be repaired**, or a drive that is replaced — is the old part returned?
- Are **unclaimed devices** disposed of after some period, and is data wiped first? Worth stating in the terms either way.
- Is there a **written disclaimer** the customer signs at drop-off today? If so, its content should inform the terms page rather than it being written from scratch.

**Answer:**i would say we check the competiton here and decide what we do in regard to the questions above. however those that could be safely skipped i'd rather skip them

---

### Address format, and access guidance for first-time visitors

_Open · New · 19 Aug · Cheap now, annoying later_

The site writes the address two ways — `ул. Свети Иван Рилски 46` and `ул. Свети Иван Рилски №46`. Which is preferred? And is there anything a first-time visitor needs in order to find the door?

**Why it matters.** Minor typographically, but name/address/phone consistency between the site, the Google profile and directory listings is a real local-SEO signal — and we are about to freeze one form into structured data on all sixteen pages.

- Is the shop **visible from the street**, or inside a building or courtyard? Which floor?
- Is there **parking**, and is it paid (blue or green zone)?
- Nearest **metro or bus stop**, or a recognisable landmark?
- Should the site carry a **map embed** or just a link to Google Maps? An embed costs page weight and adds third-party tracking; a link costs nothing.

**Answer:** lets go with ул. Свети Иван Рилски 46 although both are valid and i like having the number sign but in the google business account it is missing so in order to be consistent lets keep it without it

---

### Is the sales line still active, and should it have nav prominence?

_Open_

The site sells **употребявани лаптопи** and **резервни части** — a second business line alongside repair. Is this still active? The plan gives it a nav item «Лаптопи и части». Is that the right weight, or is repair the only focus now?

**If still active:**

- Is the **stock on those pages current**, or years out of date? If stale, showing it is worse than not showing it — customers ask for machines long gone.
- Should **prices** be shown, or is it enquire-only?
- **Used only**, or also new? Refurbished-with-warranty?
- Do you want to **update stock yourself**? A static site can carry a simple list, but a frequently-changing catalogue is a different kind of project and should be scoped separately.
- For **spare parts** — retail to walk-in customers, or mainly parts used in your own repairs? That changes whether it is a sales page or a credibility page.

**If no longer active:** both URLs are indexed, so they should be redirected rather than deleted — tell us where each should point.

**Answer:** no the sales line goes away, so does the part about battery regeneration and the BGA/reballing related staff

---

### The holiday banner — keep or drop?

_Open · Phase 4_

The site has a script showing a holiday or absence banner. Is this still used? Should the redesign keep an equivalent, or drop it?

- **How often is it actually used** — a couple of times a year, or more?
- **Who updates it** today, and how? The current one needs a code edit, which likely means it is either never updated or updated by whoever built the site.
- **What has it said** in the past? A real example tells us what the replacement must support.
- Should it also cover **unexpected closures** — illness, emergency — not just planned holidays?
- Would you want to **turn it on and off yourself** without a developer? Buildable, but it changes the design, so worth knowing now.

*Interacts with #20: if the shop closes for holidays, the structured data should ideally reflect that too, or Google may show the shop as open when it is not.*

**Answer:**the banner should be disablable, by default it should be disabled and controlled using a file where the owner could easily define when a vacation period will be. also it should be hidden automatically once the period has expired, not only when enabled. also any suggestion related to redesign of this banner is welcome

---

### Is there an original vector or high-resolution logo file?

_Open · Timing flexible_

The only logo on the site is `torin-logo.png` at **150×80 pixels**. Does an original vector or larger raster version exist — perhaps from whoever designed it?

**Why it matters.** At 150×80 the logo looks visibly soft on modern phone and laptop screens. If no original exists it needs redrawing from scratch.

- **Who designed it**, and are they contactable? Designers usually keep source files for years.
- Any **other places it exists at higher resolution** — a shop sign, vehicle livery, business cards, invoice template, a printer's file, an old Facebook cover?
- Are there **brand guidelines** or agreed exact colours, or were the site's colours simply picked at the time? This bears on the theme choice — we matched the current site, but an official brand colour should win.
- Is the logo **registered as a trademark**? If so the exact form matters legally and it must not be redrawn loosely.
- Is there a **favicon** source, or should one be generated? The current site has none at modern sizes.

**Answer:**i have a bigger image that i can provide you later, just let me know when and how to deliver it

---

## Confirm only — decided, not blocking

### Categories 1 and 2 overlap — confirm the split

_Confirm_

"Ремонт на счупвания" and "Смяна на матрици, клавиатури, USB портове, захранващи букси, панти" describe overlapping work — a cracked screen is both a счупване and a смяна на матрица. As originally written, category 1 had no services not already in category 2.

**Decision taken:** category 1 becomes «Счупвания и механични повреди», owning physical and impact damage. Category 2 becomes «Екран, клавиатура и портове», owning component replacement regardless of cause.

**One thing to flag:** панти (hinges) moved from category 2 to category 1. The owner's original list put them under category 2, but under a physical-damage split they belong with breakage. Deliberate, not an oversight — worth a sentence at review.

**Answer:**

---

### Category 5 renamed — confirm

_Confirm_

"Смяна на вентилатори" describes the *fix*, but customers only know the *symptom* — «прегрява», «шуми», «изключва се сам». It was also the thinnest of the six, holding essentially one service.

**Decision taken:** renamed to «Прегряване и охлаждане». Customers recognise the symptom immediately, and it naturally absorbs профилактика — dust cleaning, thermal paste — so the category is no longer thin. **Is there a reason to keep the fan-replacement framing?**

The full naming set now in use: 1 Счупвания и механични повреди · 2 Екран, клавиатура и портове · 3 Оптимизация · 4 Заливане и ремонт на дънни платки · 5 Прегряване и охлаждане · 6 Нестандартна техника

**Answer:** keep the name that you've introduced

---

### Which theme goes live?

_Confirm · Answered by developer_

Two themes are built and switchable during development. **Theme B** — amber #ffc70a with navy #0e305d, matching the current site — is the default and ships live. Theme A, the logo's amber and electric blue, stays as the comparison option.

*Still worth showing the owner both before cutover, since it is their brand.*

**Answer:** use theme B

---

## Owner action owed, not an answer

### Viber — the account still needs provisioning

_Direction decided · Blocks cutover_

All three published numbers were tested on a real handset and **none has a Viber account**: the landline 02 9549710 cannot have one, and both mobiles — 087 9128244 and 088 9458404 — returned the same failure.

**What was decided.** The button stays, on **088 9458404**, and the owner will provision a Viber account on that number. The number in the config is now the intended target rather than a placeholder, and must not be changed while chasing this.

**This is now a cutover gate rather than a code change:** before the site goes live, someone must press «Пишете във Viber» on a real handset with Viber installed and confirm it opens a conversation.

**Still open — the second half of this question.** What should happen for a visitor with **no Viber installed at all**? Today the button is a dead end for them too, entirely independently of the account question, and provisioning an account does not address it. If the answer is "nothing, accept it", that should be a recorded decision rather than an oversight.

Interacts with **#2** — whether a written enquiry form should exist as the alternative path.

**Answer:** actually the viber button goes away in favor of the contact form

---

## Materials, whenever convenient

### Workshop photos to replace the six category icons

_Not blocking_

The design uses icons now, with every image slot built so a real photo can replace it later without any layout change. A later quality upgrade, not a dependency.

---

### Remaining site photos

_Not blocking_

Hero, workshop, team and trust imagery. See the photo brief in the planning folder.

---

### Prices, turnaround times, before/after photos

_Deferred to v2_

Indicative price ranges per category, realistic turnaround commitments, and before/after repair photos. All deferred out of the first version, but all depend on owner-supplied real numbers — so worth collecting whenever convenient.

**Answer:** prices will not be specified, they are dependant on the time that it will take to fix them, any replacement parts to be used etc, also the prices of all services are changing really dinamically and adjusting the prices is an ongoing process and it is very possible the prices might get outdated very soon and i suspect this is the case for big part of the competion if not all; turnaround times however will be provided but i would like to make a list of different services which ill pass to the owner of the shop and he can give me the expected times. i remember in some of the competition sites there was a list of the repair jobs they do and the prices, it would be nice to have something similar even mentionining the competion prices just as additiotional info for the owner

---

## Already resolved — no action needed

### `problem-stari.html` — kept and repurposed

_Resolved 11 Aug_

The page is kept and becomes the category 6 page, putting category 6 on an existing indexed URL rather than a new slug. Nothing is retired, so no redirect and no ranking risk.

*See the complication raised under #3 — the page's actual content is about batteries and adapters, not non-standard equipment, so this decision may need revisiting.*

---

### Battery regeneration gets prominent treatment

_Resolved 11 Aug_

The proposed downgrade no longer exists, so the sign-off is moot. `za-bateriite.html` becomes the battery depth page. What forced it: SmartBattery.eu no longer exists, and the current site sends battery customers there from three places, plus lists an address at that domain in a fourth. With nowhere to link out to, the page has to carry the story itself — which is what the requirement wanted in the first place.

---
