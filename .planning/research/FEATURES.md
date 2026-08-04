# Feature Research

**Domain:** Bulgarian computer/laptop/electronics repair shop website (torin.bg redesign)
**Researched:** 2026-08-04
**Confidence:** MEDIUM-HIGH (based on direct examination of 9 real competitor websites via live fetch, cross-checked with one consumer-facing "how to choose a repair shop" article and multiple Bulgarian-language searches; no analytics/traffic data available to rank competitors by size, so "top" is based on search visibility for core Bulgarian queries, not verified revenue/traffic)

## Competitors Examined (real sites, fetched live)

| # | Business | URL | Notes |
|---|----------|-----|-------|
| 1 | Quantum Computers (branded as "RemontLaptop.bg" on-site) | https://www.remontlaptop.bg/ | Sofia, Pirotska 47. 15+ yrs claimed. Strong blog. |
| 2 | Крос Компютърс (Cros Computers) | https://www.croscomputers.net/ | Sofia. Embedded Google reviews, on-site + home-visit service. |
| 3 | ИТ Сервиз (ITServiz) | https://itserviz.com/ | Sofia (Lyulin). Very broad service list, dated design, no trust signals. |
| 4 | ACS — Attractive Computer Systems | https://acs-bg.net/ | Sofia. Laptop/tablet/TV specialist, dated design. |
| 5 | AdminBG | https://adminbg.net/ | Sofia. Runs as parts e-commerce store + repair service hybrid; multi-language (BG/RO/GR). |
| 6 | SofiaComputers.net | https://sofiacomputers.net/ | Sofia. Huge service catalog (140+ line items), online request form, home-visit fee stated (7€). |
| 7 | Computer-Serviz.bg | http://www.computer-serviz.bg/ | Sofia. Simple static price list, "10+ years" claim, very dated HTML. |
| 8 | Trierra Soft | https://trierrasoft.com/serviz/ | Plovdiv (national courier). **Most modern/complete competitor found** — real price table, courier pickup, Google rating badge (4.9/5), explicit non-standard-electronics repair. |
| 9 | Plasico IT Superstore | https://plasico.bg/serviz/sofiya/remont-laptopi | Sofia (Mladost). Live chat present, e-commerce-integrated. |

Also reviewed: **088support.bg** consumer-advice article "Как да изберем най-добрия компютърен сервиз" (https://088support.bg/nai-dobriya-kompyutyren-serviz/) — used to validate what Bulgarian consumers actually say they look for (reviews, warranty, turnaround speed, brand specialization, price transparency, location/parking).

**Baseline examined:** current torin.bg source (`site-current/index.html` + service pages) — a single long-scroll homepage built on the "Liquid" jQuery/Bootstrap template with heavy parallax/fittext animation, a giant unstructured grid of ~18 service icon-boxes (dense paragraph text, no prices, no photos, no categorization), a Zendesk chat widget snippet embedded in `<head>` (unclear if actually staffed/active), a working self-diagnostic tool (`test-laptop.html`), a Google Maps iframe, a contact form via `mailer.php`, and a warranty/spare-parts informational section.

## Feature Landscape

### Table Stakes (Users Expect These)

Features nearly every competitor has, and Bulgarian buyers explicitly say they check for (per 088support.bg) before choosing a shop. Missing these makes torin.bg look behind the market it's already competing in.

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Clear, categorized service sections (not one long text wall) | Every competitor except the oldest (ITServiz, ACS, computer-serviz.bg) organizes services into distinct pages/cards by problem type; torin's current homepage is one undifferentiated scroll of ~18 icon-boxes with dense paragraphs and no visual hierarchy | MEDIUM | Directly maps to the owner's 6 priority categories — this is the single biggest structural gap |
| Static price list / indicative price ranges | Trierra Soft (best-in-class) publishes a real price table in BGN+EUR; remontlaptop.bg and computer-serviz.bg also publish price pages; 088support.bg explicitly lists price transparency as a trust factor. Torin currently shows **zero prices anywhere** | LOW | Doesn't need to be exact — "от X лв." ranges per service are the norm and sufficient; avoid a dynamic calculator (see anti-features) |
| Multiple phone numbers + click-to-call on mobile | Universal across all 9 competitors | LOW | Torin already has 3 numbers — keep, just make tap-to-call on mobile |
| Physical address + embedded Google Map | Universal | LOW | Torin already has this (iframe) — carry over, style-match |
| Business hours clearly stated | Universal | LOW | Torin has this — keep, verify accuracy |
| Contact/inquiry form | Universal (form or WhatsApp/Viber equivalent) | LOW | Torin has this (`mailer.php`) — keep, modernize styling and add validation |
| Warranty terms clearly stated | Cros, remontlaptop, Trierra all state warranty explicitly (6–24 months); 088support.bg names warranty as a top trust factor | LOW | Torin already has `warrently.html` — keep, surface a short warranty summary on relevant service pages instead of burying it in a separate page only |
| Free diagnostics messaging | Cros ("free diagnostics"), Trierra, and most others advertise no-cost or low-cost diagnostics as a lead-in | LOW | Torin already states this prominently on the homepage — preserve, it's a genuine strength |
| "All brands serviced" messaging (Lenovo/HP/Dell/Asus/Acer/Apple/MSI etc.) | Universal claim; ITServiz shows 40+ brand names as a list | LOW | Torin doesn't currently list brands anywhere — add a simple brand-name/logo row |
| Mobile-responsive, fast-loading layout | Table stakes for 2026; several older competitors (ITServiz, ACS, computer-serviz.bg, sofiacomputers.net) fail this and look correspondingly dated/untrustworthy — that's the exact impression torin currently gives | MEDIUM-HIGH | Torin's current "Liquid" theme is heavy (jQuery UI, ScrollMagic, pagePiling, parallax/fittext animation) — likely the single biggest visible driver of the "dated" complaint in PROJECT.md |
| Google/Facebook review presence acknowledged on-site | Cros embeds live Google reviews; Trierra displays a "4.9/5 on Google" badge; AdminBG links out to Google/Facebook reviews | LOW-MEDIUM | Torin has none. Even a simple star-rating badge + link to Google Business Profile is low effort and closes most of the gap — full review widget is a differentiator (below) |

### Differentiators (Competitive Advantage)

Features where only some or none of the examined competitors compete — building these well would put torin ahead of the market, not just even with it.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| Before/after repair photo gallery | **Zero of the 9 competitors examined have this.** For visually obvious problems (broken screens, liquid/corrosion damage, chassis breaks) a real photo gallery is high-trust proof of capability that nobody else in this market offers | MEDIUM | Needs the owner to supply real repair photos over time; start with a handful, add per completed job going forward |
| Explicit, prominent "нестандартно ел. оборудване" (non-standard electrical equipment) servicing | Only Trierra Soft (industrial electronics, vending machines) and general electronics shops like Kanev-service (UPS, power tools) do this at all, and none frame it as a headline category — most laptop-focused competitors don't touch it. This is a genuine underserved niche (small businesses/workshops with odd equipment have few options) | LOW (content), MEDIUM (if it implies handling genuinely unusual intake workflows) | Owner explicitly wants this as one of the 6 headline categories — market data confirms it's rare enough to be a real differentiator, not just filler |
| Interactive self-diagnostic tool ("Тествай сам своя лаптоп") | **None of the 9 competitors have anything like this.** Torin already has `test-laptop.html` — an existing, unique asset that's currently buried in the nav under generic "Тествай сам" | LOW (already built — needs surfacing/redesign, not rebuilding) | Strong candidate for a homepage-level feature, not a footer link |
| Battery regeneration (vs. new-battery resale) | Torin's own content states they stopped importing new batteries due to poor component quality and instead do full battery cell regeneration with Panasonic cells — none of the competitors examined mention this as a distinct offering (most just sell/replace with new aftermarket batteries) | LOW (already exists — needs surfacing) | This is a real, defensible differentiator already embedded in torin's content that's currently invisible amid the wall of text |
| Deep technical repair detail (BGA reballing, chip-level motherboard repair) as visible expertise proof | Torin's existing content already describes BGA reballing/rebooling, chipset replacement, power-circuit repair — more technically detailed than any competitor site examined (most just say "motherboard repair"). This reads as genuine expertise, not marketing fluff, once given proper visual hierarchy | LOW (content exists — needs restructuring, not rewriting) | Pair with the liquid-damage/motherboard category — this is where torin can visibly out-expert the market |
| Courier pickup/delivery service | Trierra Soft (national courier) and Cros/SofiaComputers (home-visit, fee-based) offer this; most Sofia competitors do not | MEDIUM | This is as much an operational/business decision for the owner as a website feature — only add if the owner actually offers or wants to offer this; do not fabricate a promise the shop won't fulfill |
| WhatsApp/Viber click-to-chat | Only AdminBG links Viber/Skype; nobody offers WhatsApp | LOW | Cheap way to feel more responsive than the market without committing to live-chat staffing |
| Explicit turnaround-time commitment ("готово до X работни дни" or express option) | Nobody examined states a hard turnaround commitment — Cros only implies speed ("no waiting days"), Trierra mentions "1–3 days typical" informally | LOW | Needs the owner to confirm realistic numbers per repair type; don't promise what can't be kept (see pitfalls in PITFALLS.md if generated) |
| Real Google Reviews embed/widget (not just a badge) | Cros is the only one with a live embedded reviews feed; most just claim experience years instead | LOW-MEDIUM | A step beyond table-stakes review acknowledgment — genuinely differentiates if torin has decent review volume/rating to show |

### Anti-Features (Commonly Requested, Often Problematic)

Patterns actually observed on competitor sites (or implied by the current torin build) that look appealing but should be deliberately avoided.

| Feature | Why Requested | Why Problematic | Alternative |
|---------|---------------|------------------|-------------|
| Full e-commerce parts store with cart/checkout | AdminBG runs a full parts storefront alongside repair services; feels "more modern/complete" | PROJECT.md explicitly rules out e-commerce/checkout — business is service-based, not product sales; building a cart adds real ongoing maintenance burden (inventory, payment, shipping) for a shared-hosting static-leaning site | Keep `rezervni-chasti.html` as an informational "we have used parts, call to ask" page — no cart |
| Interactive price *calculator* (dynamic quote tool) | Feels cutting-edge, "quote in seconds" | None of the 9 competitors have a real one (only static price tables); building a genuinely accurate one requires either a backend or an elaborate client-side rules engine that will drift out of sync with real repair pricing and create disputes when quotes don't match reality | Static price table with "от X лв." ranges + "точна цена след безплатна диагностика" (final price after free diagnosis) — matches how every competitor and torin's own existing messaging already works |
| Overly broad service diversification (rental computers, website design/SEO services, video surveillance installation, training courses) | ITServiz, Cros, and SofiaComputers all bolt these on, presumably to catch more search traffic | Dilutes the core positioning ("we fix your specific problem fast") that PROJECT.md defines as the core value; a visitor with a cracked screen shouldn't have to wade past "computer rental" and "SEO services" to find repair info | Stay strictly scoped to repair/maintenance services (the 6 owner categories + existing content); if the owner does other things, keep them minimal/secondary, not homepage-competing |
| Multi-language switcher | AdminBG offers BG/RO/GR | PROJECT.md explicitly requires Bulgarian-only | Don't add |
| Dense, multi-level mega-menus (30+ subcategory links) | SofiaComputers' nav has extensive dropdowns (30+ monitor brands, socket types, RAM types, etc.) | Overwhelms a visitor who has one specific problem — actively works against the "core value" of instant problem recognition defined in PROJECT.md | Flat, shallow navigation organized around the 6 priority categories + a handful of secondary pages |
| Heavy parallax/scroll-hijacking animation libraries (ScrollMagic, pagePiling, ScrollTrigger-style fittext/split-text effects) | This is what torin's *current* site already does — "keeps it visually interesting" | Slows mobile load, feels dated rather than modern in 2026 (the trend among competitors that look decent — Trierra, Cros — is clean, fast, static-feeling layouts, not scroll-jacking), and directly undermines the "make it obvious faster than competitors" goal in PROJECT.md | Clean, fast-loading static/near-static layout with restrained, purposeful motion (simple fade/slide-in on scroll at most) |
| Un-staffed chat widget (dead promise) | Torin's current `index.html` already embeds a Zendesk snippet in `<head>` | If nobody's actually monitoring it, an unanswered chat bubble is worse than no chat bubble — it signals unresponsiveness at the exact moment a visitor is trying to convert | Either commit to actually staffing a lightweight chat/WhatsApp channel, or remove the widget and rely on prominent phone/WhatsApp click-to-contact instead |
| Publishing an empty/placeholder testimonials section | Quantum Computers (remontlaptop.bg) has a "Отзиви от наши клиенти" section with no content in it | An empty trust section reads worse than no section — it's proof reviews weren't collected, undermining the reviews it's supposed to display | Don't add a reviews section until there's real content (even 3–5 genuine reviews) to put in it; use a Google rating badge/link in the meantime instead |
| Unrelated EU-funding/COVID project badges on the homepage | Torin's current homepage has a full "ЕВРОПЕЙСКИ ПРОЕКТИ" (COVID-era EU grant) section competing for attention with actual repair content | Irrelevant to a visitor trying to solve a repair problem; clutters the page and dilutes the core value proposition | Move to the About page as a footnote/credential, not homepage real estate |

## Feature Dependencies

```
Clear service categorization (6 owner categories)
    └──requires──> Restructured information architecture / navigation
                       └──requires──> Content audit of existing service pages (laptopi, mehanichni-problemi,
                                        zalivane-technosti, tokov-udar, profilaktika-laptop, optimizatsiq,
                                        za-bateriite, rezervni-chasti) mapped onto the 6 categories

Static price list / indicative ranges
    └──requires──> Owner-supplied real price ranges per service (business input, not research)

Before/after photo gallery
    └──requires──> Owner-supplied real repair photos (ongoing content, not a one-time build)

Google Reviews badge/widget
    └──requires──> Existing Google Business Profile with reviews (confirm this exists / its rating before promising to feature it)

Courier pickup/delivery messaging
    └──requires──> Owner decision on whether this is actually offered (business decision, not a technical one)

Mobile-responsive modern layout ──enables──> All of the above reading credibly on mobile
    (competitor sites without this — ITServiz, ACS, computer-serviz.bg — look untrustworthy regardless of content quality)

Un-staffed live chat widget ──conflicts with── Trust/responsiveness goal
    (only add chat if staffing commitment exists; otherwise remove entirely)
```

### Dependency Notes

- **Clear categorization requires an IA restructure, not just a redesign:** torin's existing content already substantively covers 5 of the 6 owner-priority categories (breakage → `mehanichni-problemi.html`; screen/keyboard/port/hinge replacement → scattered across the homepage icon-box list; optimization → `optimizatsiq.html`; liquid/motherboard damage → `zalivane-technosti.html` + homepage BGA content; fan replacement → homepage icon-box). Category 6 (non-standard electrical equipment) has **no existing dedicated content** and will need net-new copy from the owner about what specifically they service in that category.
- **Price list and photo gallery both require owner-supplied real data**, not just template/design work — flag this early so it doesn't block the phase that needs it.
- **Mobile-responsive layout is a prerequisite, not a nice-to-have add-on** — every other trust signal (reviews, prices, brand logos) reads as less credible if the surrounding page looks broken or dated on a phone, which is where most of these searches (per the "ремонт на лаптопи София" query pattern) will originate.
- **Live chat conflicts with an un-staffed default:** decide staffing before deciding whether to build it at all.

## MVP Definition

### Launch With (v1)

Minimum viable set to close the most damaging gaps and deliver the 6 owner-priority categories credibly.

- [ ] Modern, mobile-responsive layout replacing the heavy parallax "Liquid" theme — table stakes, currently the most visible competitive gap
- [ ] Homepage restructured around the 6 owner-priority service categories as clear, distinct cards/sections (not a 18-item icon-box wall of text)
- [ ] Static indicative price ranges (from-price) shown per category — closes the single most requested trust signal (per 088support.bg) that torin currently has zero of
- [ ] Brand-name/logo row ("обслужваме всички марки: Lenovo, HP, Dell, Asus, Acer, Apple/MacBook, MSI...")
- [ ] Google rating badge/link to the business's Google reviews (verify a Google Business Profile exists first)
- [ ] Warranty summary surfaced on service pages (not just buried in `warrently.html`)
- [ ] Surfaced, redesigned self-diagnostic tool (`test-laptop.html`) as a homepage-level feature — unique existing asset
- [ ] Surfaced battery-regeneration differentiator — unique existing asset currently buried in text
- [ ] Content for category 6 (non-standard electrical equipment servicing) — net-new, needs owner input on scope
- [ ] Click-to-call phone links + WhatsApp/Viber click-to-chat
- [ ] Remove or fix the currently-embedded, likely-unstaffed Zendesk chat widget
- [ ] Move EU-project/COVID content off the homepage to About page

### Add After Validation (v1.x)

- [ ] Before/after repair photo gallery — start with a handful of real photos, grow over time
- [ ] Explicit turnaround-time commitments per service type (needs owner-confirmed realistic numbers)
- [ ] Embedded live Google Reviews widget (beyond a static badge) once review volume justifies it
- [ ] Expanded guide/blog content building on the existing profilaktika/zalivane/optimizatsiq pages

### Future Consideration (v2+)

- [ ] Courier pickup/delivery service (contingent on owner operational decision)
- [ ] Staffed live chat (contingent on staffing commitment)
- [ ] Full price calculator (only if a real backend/rules engine can be justified — static ranges are sufficient for now)

## Feature Prioritization Matrix

| Feature | User Value | Implementation Cost | Priority |
|---------|------------|---------------------|----------|
| Mobile-responsive modern layout | HIGH | HIGH | P1 |
| 6-category service restructure | HIGH | MEDIUM | P1 |
| Static price ranges | HIGH | LOW | P1 |
| Brand logos/names row | MEDIUM | LOW | P1 |
| Google rating badge | MEDIUM | LOW | P1 |
| Surface self-diagnostic tool | MEDIUM | LOW | P1 |
| Surface battery-regeneration differentiator | MEDIUM | LOW | P1 |
| Non-standard equipment category content | MEDIUM | LOW (content) / MEDIUM (owner scoping) | P1 |
| Click-to-call / WhatsApp-Viber | MEDIUM | LOW | P1 |
| Remove unstaffed chat widget / EU-project homepage clutter | MEDIUM | LOW | P1 |
| Before/after photo gallery | HIGH | MEDIUM (needs ongoing content) | P2 |
| Explicit turnaround-time guarantees | MEDIUM | LOW (needs owner input) | P2 |
| Embedded live reviews widget | MEDIUM | MEDIUM | P2 |
| Courier pickup/delivery | MEDIUM | MEDIUM-HIGH (operational) | P3 |
| Staffed live chat | LOW-MEDIUM | MEDIUM (ongoing staffing) | P3 |
| Interactive price calculator | LOW | HIGH | P3 (likely never — static ranges suffice) |
| E-commerce parts checkout | LOW | HIGH | Not planned (out of scope) |

**Priority key:**
- P1: Must have for launch
- P2: Should have, add when possible
- P3: Nice to have, future consideration

## Competitor Feature Analysis

| Feature | Trierra Soft (best-in-class) | Cros Computers | ITServiz / ACS / computer-serviz.bg (dated tier) | Torin.bg today | Our Approach |
|---------|-------------------------------|-----------------|----------------------------------------------------|-----------------|--------------|
| Price list | Real BGN+EUR price table | Package-based pricing page | Basic price list (computer-serviz.bg) / none (ITServiz, ACS) | None | Static indicative ranges, P1 |
| Service categorization | Clear categories incl. non-standard electronics | 7 clear service areas | Long undifferentiated lists | One long icon-box wall | Restructure around 6 owner categories, P1 |
| Reviews | Google 4.9/5 badge | Embedded live Google reviews | None | None | Google rating badge (P1) → embedded widget (P2) |
| Mobile/modern design | Yes, clean | Yes, clean | No — dated, text-heavy | No — heavy legacy parallax theme | Full responsive redesign, P1 |
| Courier/pickup | Yes (national courier) | Home/office visits | No | No | Defer to owner decision, P3 |
| Before/after gallery | No | No | No | No | Build as differentiator, P2 |
| Non-standard equipment | Yes (industrial, vending machines) | No | No | Implicit only (no dedicated content) | Net-new content, P1 |
| Self-diagnostic tool | No | No | No | **Yes (unique, but buried)** | Surface prominently, P1 |
| Battery regeneration | No (sells new) | No (sells new) | No | **Yes (unique, but buried)** | Surface prominently, P1 |
| Live chat | No | No | No | Zendesk snippet present, unclear if staffed | Fix or remove, P1 |

## Sources

- https://www.remontlaptop.bg/ — direct site fetch, HIGH confidence
- https://www.croscomputers.net/ — direct site fetch, HIGH confidence
- https://itserviz.com/ — direct site fetch, HIGH confidence
- https://acs-bg.net/ — direct site fetch, HIGH confidence
- https://adminbg.net/ — direct site fetch, HIGH confidence
- https://sofiacomputers.net/popravka-i-remont-na-laptopi.php — direct site fetch, HIGH confidence
- http://www.computer-serviz.bg/comp.html — direct site fetch, HIGH confidence
- https://trierrasoft.com/serviz/ — direct site fetch, HIGH confidence
- https://plasico.bg/serviz/sofiya/remont-laptopi — direct site fetch, HIGH confidence
- https://088support.bg/nai-dobriya-kompyutyren-serviz/ — direct site fetch (consumer-advice article, secondary source on buyer criteria), MEDIUM confidence
- Bulgarian-language web searches: "ремонт на лаптопи София сервиз", "сервиз за компютри и лаптопи София", "заляна дънна платка лаптоп ремонт цена сервиз", "компютърен сервиз онлайн чат заявка ремонт", "ремонт нестандартно електронно оборудване сервиз", "компютърен сервиз София отзиви Google карта" — used to identify the competitor set and cross-reference pricing/positioning claims, MEDIUM confidence (search snippets, not independently verified against live pages in all cases)
- `/Users/alabala/Documents/projects/torin/site-current/index.html` and directory listing — direct inspection of current torin.bg baseline, HIGH confidence
- `/Users/alabala/Documents/projects/torin/.planning/PROJECT.md` — project context and constraints

---
*Feature research for: Bulgarian computer/laptop/electronics repair shop website*
*Researched: 2026-08-04*
