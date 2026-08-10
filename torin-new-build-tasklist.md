# torin.bg/new — Build Task List

**Scope:** content-filling and build tasks for the `/new` development version, prioritised around the six services the owner identified as the bulk of actual jobs. Pages under `/new` are development-only and will later be moved to root, replacing existing pages where applicable.

---

## Priority mapping

The owner's list maps onto **4 category hubs + 5 child pages**:

| Owner's priority | Page | Type |
|---|---|---|
| Ремонт на счупвания | `/remont-na-schupvaniya/` | Hub (cluster 1) |
| Панти | `/smyana-na-panti/` | Child |
| Смяна на матрици | `/smyana-na-matrica/` | Child |
| Клавиатури | `/smyana-na-klaviatura/` | Child |
| USB портове | `/remont-usb-hdmi-portove/` | Child |
| Захранващи букси | `/remont-zahranvashta-buksa/` | Child |
| *(parent of the four above)* | `/ekran-klaviatura-portove/` | Hub (cluster 2) |
| Оптимизация | `/optimizatsiya/` | Hub (cluster 3) |
| Заляти и повредени дънни платки | `/zalyat-laptop-danna-platka/` | Hub (cluster 4) |

**Why the children get their own pages:** each is a distinct search with its own price intent. Bundling all five under one "Екран, клавиатура и портове" page means competing for five keywords with one URL and losing all of them. The hub exists for navigation and internal linking; the children do the ranking.

**Where board-level work lives:** inside cluster 4, as proof rather than as a keyword target. Users search the symptom (`залят лаптоп`, `лаптопът не се включва`, `ремонт на дънна платка`); the IR reflow machine, AMTECH flux, 90% success rate and 10°C figures are the conversion evidence on that page, plus a short "Какво можем, което другите отказват" block on the homepage. No standalone `/reboling/` page in this phase.

**Not in this phase:** прегряване/охлаждане hub, нестандартна техника, регенерация на батерии (link out to SmartBattery.eu instead), употребявани лаптопи, резервни части, per-brand pages, English layer. All parked in the backlog at the end.

---

## Workstream 0 — Foundations (do before writing any content)

- [ ] **Block `/new` from indexing entirely** — `Disallow: /new/` in robots.txt *plus* `<meta name="robots" content="noindex,nofollow">` in the shared header include. The stub pages currently reading *"Тази страница е временен скелет"* must never be crawled. Removing this is a launch-day task (Workstream 5).
- [ ] **Freeze the final URL slugs** using the table above. Decide now whether to keep `.html` extensions or move to trailing-slash directories — changing this after content is written means redoing the internal link graph.
- [ ] **Build one service-page template** with the include structure, so content filling becomes mechanical rather than per-page layout work.
- [ ] **Build the reusable content blocks as includes:**
  - [ ] `cta-block.php` — phone / Viber / quote-form buttons
  - [ ] `price-teaser.php` — accepts a service key, renders "от X лв" + free-diagnostics line
  - [ ] `reviews-block.php` — accepts a count, renders rating + review quotes
  - [ ] `faq-block.php` — accordion markup that also emits FAQPage JSON-LD
  - [ ] `warranty-block.php` — warranty terms summary
  - [ ] `breadcrumbs.php`
- [ ] **Write the JSON-LD helpers:** LocalBusiness (home + contact only), Service (per service page), FAQPage, BreadcrumbList. Emit from the template, not hand-written per page.
- [ ] **Build the 301 redirect map** as a spreadsheet now, filled in as pages are written. Every legacy URL needs a destination:

| Old | New |
|---|---|
| `mehanichni-problemi.html` | `/remont-na-schupvaniya/` |
| `zalivane-technosti.html` | `/zalyat-laptop-danna-platka/` |
| `optimizatsiq.html` | `/optimizatsiya/` |
| `profilaktika-laptop.html` | `/polezno/pregryavane-profilaktika/` |
| `za-bateriite.html` | `/polezno/za-bateriite/` |
| `test-laptop.html` | `/testvai-sam/` |
| `tokov-udar.html` | `/zalyat-laptop-danna-platka/` |
| `problem-stari.html` | `/nestandartna-tehnika/` |
| `about.html` | `/za-nas/` |
| `warrently.html` | `/garanciya/` |
| `uslovia.html` | `/uslovia/` |
| `laptopi.html` | `/upotrebyavani-laptopi/` |
| `rezervni-chasti.html` | `/rezervni-chasti/` |
| `covid.html` | keep, de-emphasise to footer |

- [ ] **Set up Google Search Console + Analytics** on the new structure before launch, with conversion events defined (call click, Viber click, form submit).

---

## Workstream 1 — Definition of Done for every service page

Apply this checklist to each of the nine pages in Workstream 2. Don't mark a page complete until all items pass.

- [ ] One `<h1>` containing the primary keyword + "София" where it reads naturally
- [ ] `<title>` ≤ 60 characters, primary keyword first
- [ ] Meta description ≤ 155 characters, ending in a CTA
- [ ] **Symptom block** — "Разпознайте проблема": 4–6 bullets in the customer's words, not technical language
- [ ] **Process block** — "Какво правим": numbered steps, what the customer hands over and gets back
- [ ] **Timeframe** — realistic turnaround, plus the express option if offered
- [ ] **Price teaser** — `price-teaser.php` with "от X лв", labour vs. parts distinction, free diagnostics line
- [ ] **Warranty block**
- [ ] **3–5 FAQ** answering real objections (cost, time, is it worth repairing, do you have the part)
- [ ] **Reviews block** — rating + 2 relevant review quotes
- [ ] **2–3 real repair photos** with descriptive Bulgarian alt text (never "Blog Single")
- [ ] **CTA above the fold and repeated after the process block**
- [ ] **Internal links:** up to hub, across to 2–3 sibling pages, down to `/ceni`, out to one relevant `/polezno` article
- [ ] **Schema:** Service + FAQPage + BreadcrumbList
- [ ] 600–1,000 words Bulgarian
- [ ] Mobile check: sticky call bar visible, tap targets ≥ 44px, no horizontal scroll

---

## Workstream 2 — The nine priority pages

Written in this order. Clusters 4 and 2's matrix page carry the most commercial value; start there.

### 2.1 `/zalyat-laptop-danna-platka/` — Заляти и повредени дънни платки
- **Primary:** ремонт на залят лаптоп; **secondary:** ремонт на дънна платка на лаптоп, лаптопът не се включва, разлях кафе на лаптопа
- **Angle:** urgency + capability. Liquid damage is time-critical, which justifies an aggressive CTA ("Не включвайте лаптопа — обадете се веднага").
- **Must include:** immediate first-aid instructions (power off, remove battery, do not charge, do not use a hairdryer); why other shops refuse board-level work and Torin doesn't; the IR reflow machine, AMTECH flux, 90% durable-repair rate, 10°C lower temperatures; ultrasonic cleaning; honest statement of when a board isn't recoverable and what the alternatives cost.
- **Tasks:**
  - [ ] Write first-aid section (this doubles as the highest-value snippet-bait on the site)
  - [ ] Photograph 3 board repairs before/after for this page specifically
  - [ ] Write the "кога не си струва" honesty block — this is a trust differentiator, not a lost sale
  - [ ] Complete Definition of Done checklist

### 2.2 `/smyana-na-matrica/` — Смяна на матрица / екран
- **Primary:** смяна на матрица на лаптоп; **secondary:** смяна на матрица на лаптоп цена, счупен екран на лаптоп, пукната матрица
- **Angle:** highest price-intent search in the set. Competitors publish a number here; a page without one loses.
- **Must include:** matrix vs. glass vs. touch distinction, how to tell if it's the matrix or the graphics chip (link to `/testvai-sam/`), resolution/panel-type matching, availability and lead time for parts, original vs. compatible panels.
- **Tasks:**
  - [ ] Write the "матрица или видеочип?" diagnostic block — this is the differentiator vs. competitors who just quote a price
  - [ ] Confirm price banding with owner (by size / panel type)
  - [ ] Complete Definition of Done checklist

### 2.3 `/remont-zahranvashta-buksa/` — Захранващи букси
- **Primary:** смяна на захранваща букса на лаптоп; **secondary:** лаптопът не се зарежда, ремонт на букса лаптоп, разклатена букса
- **Angle:** classic high-volume, low-complexity, fast-turnaround job — ideal for an express-service pitch.
- **Must include:** symptoms (charges only at an angle, intermittent charging, loose connector), soldered-to-board vs. cable-type jacks and why the price differs, why ignoring it damages the board (natural upsell link to cluster 4).
- **Tasks:**
  - [ ] Write the "защо не отлагате" block linking to `/zalyat-laptop-danna-platka/`
  - [ ] Complete Definition of Done checklist

### 2.4 `/remont-na-schupvaniya/` — Ремонт на счупвания (hub)
- **Primary:** ремонт на счупен лаптоп; **secondary:** ремонт на лаптоп след падане, счупен корпус на лаптоп, счупена рамка
- **Angle:** hub page. Routes to панти, матрица, and cluster 4 depending on what broke.
- **Must include:** a short "какво точно е счупено?" router (screen / hinges / case / nothing visible but it won't start), plastics repair and case replacement, when a drop causes hidden board damage.
- **Tasks:**
  - [ ] Build the visual router block linking to the three child destinations
  - [ ] Complete Definition of Done checklist

### 2.5 `/smyana-na-panti/` — Панти
- **Primary:** счупени панти на лаптоп; **secondary:** смяна на панти на лаптоп цена, ремонт на панти
- **Angle:** genuinely common, rarely well-covered by competitors. Strong long-tail win.
- **Must include:** why hinges break the plastic housing around them (the repair is usually hinge + housing, not hinge alone — set expectations early), the risk of continued use tearing the display cable, reinforcement vs. replacement.
- **Tasks:**
  - [ ] Photograph a hinge-and-housing repair
  - [ ] Cross-link from both `/remont-na-schupvaniya/` and `/smyana-na-matrica/`
  - [ ] Complete Definition of Done checklist

### 2.6 `/smyana-na-klaviatura/` — Клавиатури
- **Primary:** смяна на клавиатура на лаптоп; **secondary:** не работят клавиши на лаптоп, залята клавиатура
- **Must include:** individual keys vs. whole keyboard, keyboard-integrated-into-topcase models and why those cost more, BG layout / backlight availability, spilled-liquid keyboards as a route to cluster 4.
- **Tasks:**
  - [ ] Confirm BG-layout sourcing and lead times with owner
  - [ ] Complete Definition of Done checklist

### 2.7 `/remont-usb-hdmi-portove/` — USB / HDMI / audio портове
- **Primary:** ремонт на USB порт на лаптоп; **secondary:** смяна на HDMI порт, не работи USB на лаптоп, счупен жак за слушалки
- **Must include:** torn-off vs. worn ports, soldered-to-board ports (this *is* microsoldering — say so plainly, it's the natural place to demonstrate capability on a low-cost job), Type-C ports specifically.
- **Tasks:**
  - [ ] Complete Definition of Done checklist

### 2.8 `/optimizatsiya/` — Оптимизация
- **Primary:** оптимизация на лаптоп; **secondary:** бавен лаптоп, ъпгрейд на SSD за лаптоп, преинсталация на Windows София, почистване от вируси
- **Angle:** the only cluster where the customer isn't in pain — they're annoyed. Lead with the outcome (boot time, responsiveness), not the procedure.
- **Must include:** HDD→SSD as the single biggest gain with a concrete before/after boot-time figure, RAM upgrade, clean Windows install with data preserved, malware cleanup, what optimisation *cannot* fix (sets honest expectations, prevents refund friction).
- **Tasks:**
  - [ ] Get real before/after boot-time measurements from the owner for two or three typical machines — a concrete number here outperforms any amount of copy
  - [ ] Decide whether `/optimizatsiya/upgrade-ssd/` becomes a child page in phase 2 (recommended — it has its own search volume)
  - [ ] Complete Definition of Done checklist

### 2.9 `/ekran-klaviatura-portove/` — Cluster 2 hub
- **Purpose:** navigation and internal-link distribution only. Deliberately shorter (~350 words).
- **Tasks:**
  - [ ] Write short intro + four cards linking to the child pages
  - [ ] Ensure every child links back here and to at least two siblings
  - [ ] Do **not** target a competitive keyword with this page — it exists to pass authority to the children

---

## Workstream 3 — Price list integration

The price list is coming; these are the tasks for wiring it in properly.

- [ ] **Display in both лв and €** on every price, following the dual-display convention competitors are already using.
- [ ] **Do not confine prices to `/ceni`.** Every service page gets its own price block via `price-teaser.php`. Price-intent searches land on the service page, not the price page.
- [ ] **Structure each line as:** услуга | труд (fixed) | части (from X) | ориентировъчно общо. Separating labour from parts prevents the "why is it more than the website said" conversation.
- [ ] **Use "от X лв"** with a visible line: *"Точната цена се определя след безплатна диагностика."*
- [ ] **State the diagnostics policy explicitly** — free with repair, and what happens if the customer declines the repair. Competitors charge a declined-repair fee; if Torin doesn't, that's a bullet worth having.
- [ ] **Add an express-service tier** with a stated surcharge and a stated turnaround, if the owner wants it.
- [ ] **Add Offer / PriceSpecification schema** to each service page once real numbers exist.
- [ ] **Add a `lastmod` / "Цените са актуални към [дата]" line** — visible freshness signals help on price pages.
- [ ] **Sanity-check against the market** before publishing: the visible Sofia benchmarks sit around 26 € labour for a matrix swap, ~26 € for board repair, ~47 € for liquid damage. Torin can price above these, but the page must then carry the justification (equipment, success rate, warranty) next to the number.

---

## Workstream 4 — Google reviews integration

Reviews are coming; these are the tasks.

- [ ] **Decide the ingestion method:** Google Places API with server-side caching (best — always current, no third-party script weight), a paid widget (fastest), or manual curation (free, but goes stale and must be updated on a schedule). Recommend the API route given there's already a PHP include layer.
- [ ] **Place reviews in three locations:** homepage (rating + 3 quotes), every service page (rating + 2 quotes relevant to that service), and a dedicated `/otzivi/` page.
- [ ] **Filter quotes by service** where possible so the matrix page shows screen-repair reviews, not battery reviews.
- [ ] **AggregateRating schema must reflect reviews actually visible on the page.** Emitting a rating with no on-page reviews risks a structured-data penalty.
- [ ] **Put the rating in the header or hero**, not only in a testimonials section low on the page.
- [ ] **Build a review-generation flow:** a printed card or SMS with a short link handed over at collection. A steady trickle of recent reviews outranks a large but stale count in local search.
- [ ] **Respond to every review in Google Business Profile**, including old ones. Response rate is a visible trust signal and a ranking input.
- [ ] **Reconcile the count before launch** — aggregators currently show figures in the 128–146 range from different crawl dates. Pull the live number from GBP and use only that.

---

## Workstream 5 — Lead capture

- [ ] **Photo-upload quote form.** Fields: име, телефон, устройство, описание на проблема, снимка (optional), GDPR checkbox. Nothing else. CTA copy: *"Изпратете снимка — вземете безплатна оценка."* No competitor in Sofia does this well.
- [ ] **Sticky mobile call bar** — phone + Viber, persistent on scroll, all pages.
- [ ] **Keep the existing click-to-call and Viber deep links** from the current `/new` hero. They work; don't lose them in the rebuild.
- [ ] **Fix the contact-form typo** carried over from the live site: "усливията" → "условията".
- [ ] **Build a real thank-you page** (not an inline message) with a stated response time — needed for conversion tracking.
- [ ] **Keep the symptom router** from the current `/new` homepage. It's the best thing on the redesign so far. Re-point its links at the new page URLs.
- [ ] **Define conversion events** in Analytics: call click, Viber click, form submit, price-page view.

---

## Workstream 6 — Supporting pages

Lower priority than Workstream 2, but required before launch.

- [ ] `/za-nas/` — replace the stub. Since 1993, the equipment, the engineers, repairs-completed counter, B2B/government history.
- [ ] `/ceni/` — the full price table (Workstream 3).
- [ ] `/otzivi/` — reviews page (Workstream 4).
- [ ] `/garanciya/` — port and clarify warranty terms.
- [ ] `/kontakti/` — quote form, click-to-call, Viber, map, hours, NAP consistent with schema and GBP.
- [ ] `/testvai-sam/` — port the existing self-diagnosis guide; it's genuinely good top-funnel content. Add routing links from each symptom to the matching service page.
- [ ] `/polezno/` — port `profilaktika-laptop.html` and `za-bateriite.html`, rewritten with proper headings and CTAs. Two articles is enough to launch.
- [ ] `/uslovia/` — privacy/terms.
- [ ] Footer: demote the EU COVID project to a small link.

---

## Workstream 7 — Migration and launch

- [ ] Content freeze; final proofread of all nine priority pages
- [ ] Move `/new` to root
- [ ] Deploy the full 301 redirect map
- [ ] **Remove the `/new` noindex and the robots.txt Disallow** — easy to forget, catastrophic if missed
- [ ] Generate and submit `sitemap.xml`
- [ ] Verify `robots.txt` allows the new structure
- [ ] Crawl the live site (Screaming Frog or similar) — check for 404s, redirect chains, missing meta, duplicate titles, orphan pages
- [ ] Validate all schema in Google's Rich Results Test
- [ ] Update Google Business Profile website URL and confirm NAP matches the site exactly
- [ ] Core Web Vitals check on mobile
- [ ] Submit key URLs for indexing in Search Console
- [ ] Monitor 404 logs daily for two weeks

---

## Backlog (explicitly not this phase)

Listed so they don't get pulled forward by accident:

- Standalone реболинг / BGA page
- Прегряване и охлаждане hub
- Нестандартна техника hub *(keep the angle in the homepage copy — it's good positioning — just no page yet)*
- Регенерация на батерии — link out to SmartBattery.eu rather than building depth here
- Per-brand pages (HP / Dell / Lenovo / Asus / Acer / MSI)
- English `/en/` layer with hreflang
- Употребявани лаптопи, резервни части
- Neighbourhood-targeted local pages
- Blog cadence beyond the two ported articles

---

## Suggested sequence

| Stage | Contents | Blocking? |
|---|---|---|
| 1 | Workstream 0 in full | Yes — everything downstream depends on the template and slugs |
| 2 | Pages 2.1, 2.2, 2.3 | No |
| 3 | Pages 2.4–2.9 | No |
| 4 | Workstreams 3 + 4 wired into completed pages | Needs price list and review feed to exist |
| 5 | Workstream 5 | Can run parallel to stages 2–3 |
| 6 | Workstream 6 | Required before launch |
| 7 | Workstream 7 | Yes — final gate |

Stage 1 is the one worth not rushing. Every hour spent on the template and the include blocks is repaid nine times over during content filling.
