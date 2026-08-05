# Questions for the Shop Owner

**Purpose:** A single running list of everything that needs an answer from the customer (ТОРИН КОМПЮТЪРС), so questions can be batched into occasional conversations instead of contacting them repeatedly.

**How to use:** Take a batch to the owner, record answers inline under each item, and change the status. Append new questions as they surface in any phase — never replace this file.

**Status key:** `OPEN` · `ASKED` · `ANSWERED` · `OBSOLETE`

**Note on numbering:** Item numbers are stable IDs referenced from phase CONTEXT.md files — they are grouped by category, not sequential. Never renumber existing items; new questions take the next unused number.

*Created: 2026-08-05 (during Phase 2 discussion)*
*Last updated: 2026-08-05*

---

## Blocking — work cannot complete correctly without these

### 1. Google Search Console access
**Status:** OPEN
**Question:** Which Google account verified ownership of torin.bg? The verification file `google1718743335455f1c.html` is live on the site, so someone has access. Can they grant it, or share the login?
**Why it matters:** Needed to cross-check the URL inventory against real indexed pages, to submit the sitemap, and to watch for ranking loss at cutover.
**Blocks:** MIGR-01 (retrofit), SEO-03, cutover monitoring in Phase 4
**Raised:** Phase 1 (D-08/D-09), still open
**Answer:**

---

### 2. Should the contact form exist at all?
**Status:** OPEN
**Question:** The new design leads with phone call + Viber/WhatsApp as the primary contact actions. Is a written enquiry form still wanted alongside them, or should it be dropped?
**Why it matters:** Determines whether `mailer.php` needs hardening at all (PHPMailer/SMTP/honeypot work in Phase 4), and how the CTA blocks are laid out.
**Blocks:** Phase 2 CTA design (partially — designed so removal is a subtraction), CONTACT-03 in Phase 4
**Raised:** Phase 2 discussion
**Answer:**

---

### 3. Category 6 — "Сервиз на нестандартно ел. оборудване": what is actually in scope?
**Status:** OPEN
**Question:** This is one of the six headline categories, but the current site has **no content for it at all**. What kinds of equipment does this cover? What are typical jobs? Any examples or past work worth describing?
**Why it matters:** Now **launch-blocking**. The decision to give all six categories their own page means this page cannot be published without real content — and publishing it thin would actively damage search rankings.
**Blocks:** Phase 3 content, Phase 4 cutover
**Raised:** Phase 1, escalated in Phase 2
**Answer:**

---

### 4. `covid.html` — has the EU publicity obligation expired?
**Status:** OPEN
**Question:** The page publicises EU project **BG16RFOP002-2.073** (ОПИК 2014-2020, 10 000 лв, beneficiary ТОРИН КЪМПАНИ ООД). EU grants carry mandatory publicity obligations for a defined period. Has that period ended, and is it safe to retire the page?
**Why it matters:** Removing it too early risks an audit finding against the company — a legal/financial risk, not just an SEO one.
**Current plan (safe default):** Remove the content from the homepage, but keep `covid.html` live and unlinked. Costs nothing and carries no risk. Only retire it — with a 301 redirect, never a 404 — once this is confirmed.
**Also worth mentioning:** the page contains a copy-paste error — the results paragraph names **"Венера-АКС ООД"**, a different company.
**Blocks:** Nothing (safe default chosen), but resolves CONTENT-02 fully
**Raised:** Phase 1, detailed in Phase 2
**Answer:**

---

### 5. `problem-stari.html` — retire, merge, or keep?
**Status:** OPEN
**Question:** No current requirement covers this page. Content may overlap with `mehanichni-problemi.html` but this is unconfirmed. Keep it, merge it into another page, or retire it with a redirect?
**Why it matters:** It's a currently-indexed URL. Retiring it without review risks losing existing traffic.
**Blocks:** Phase 3 IA finalisation
**Raised:** Phase 1
**Answer:**

---

### 6. Is there a cPanel / hosting control-panel login for `bell.host.bg`?
**Status:** OPEN
**Question:** Separate from the FTP credentials — is there a hosting control-panel account? If so, what are the details?
**Why it matters:** The host runs **PHP 5.2.17**. PHPMailer 6.x needs PHP ≥5.5. The host uses CloudLinux Alt-PHP, which normally exposes PHP-version switching through cPanel's "MultiPHP Manager" / "Select PHP Version". With a cPanel login, upgrading PHP is straightforward; without one, an older mail library must be chosen instead.
**Blocks:** Phase 4 (CONTACT-03)
**Raised:** Phase 1
**Answer:**

---

## Sign-off needed — decisions made on the owner's behalf

### 7. Google Business Profile — is it active, with reviews?
**Status:** OPEN
**Question:** Does the shop have a live Google Business Profile? Roughly how many reviews and what rating?
**Why it matters:** TRUST-02 calls for a Google rating badge linking to the profile. A badge pointing at an empty or non-existent profile is worse than none.
**Blocks:** TRUST-02 in Phase 3
**Raised:** Phase 1
**Answer:**

---

### 8. `otpuska.js` holiday banner — keep or drop?
**Status:** OPEN
**Question:** The site has a script that shows a holiday/absence banner. Is this still used? Should the redesign keep an equivalent, or drop it?
**Why it matters:** If kept, it needs a maintained modern replacement rather than the current jQuery-era implementation.
**Blocks:** Phase 4
**Raised:** Phase 1
**Answer:**

---

### 9. DIFF-02 downgrade — battery regeneration placement
**Status:** OPEN
**Question:** The requirement says the battery-regeneration story (Panasonic-cell regeneration vs. simply reselling new batteries) should be "surfaced as a distinct differentiator." The current plan puts it in the folded «Не откривате проблема си?» section instead, to keep the six categories undiluted. Is that acceptable, or should it get its own prominent block?
**Why it matters:** It's a genuine competitive advantage no competitor offers. Folding it is a deliberate trade-off, recorded so it doesn't fail verification silently.
**Blocks:** Phase 3 (DIFF-02 verification)
**Raised:** Phase 2 discussion
**Answer:**

---

### 10. Which theme goes live?
**Status:** OPEN
**Question:** Two themes will be built and switchable at `torin.bg/new` during development:
- **Theme A — logo colours:** amber `#fbad03` + electric blue `#0547dc`
- **Theme B — current site colours:** amber `#ffc70a` + navy `#0e305d`

Which should ship?
**Why it matters:** The switcher is development-only and gets removed at cutover with one theme hard-baked.
**Blocks:** Phase 4 cutover
**Raised:** Phase 2 discussion
**Answer:**

---

### 11. Is there an original vector or high-resolution logo file?
**Status:** OPEN
**Question:** The only logo on the site is `torin-logo.png` at **150×80 pixels**. Does an original vector (AI/EPS/SVG/PDF) or larger raster version exist — perhaps from whoever designed it?
**Why it matters:** At 150×80 the logo looks visibly soft on modern phone and laptop screens. If no original exists it will need redrawing from scratch.
**Blocks:** Logo redraw (timing flexible)
**Raised:** Phase 2 discussion
**Answer:**

---

## Structural — reshapes the site's organisation

### 15. Is the sales line still active, and should it have nav prominence?
**Status:** OPEN
**Question:** The site sells **употребявани лаптопи** (`laptopi.html`) and **резервни части** (`rezervni-chasti.html`) — a second business line alongside repair. Is this still active? The plan gives it a nav item **«Лаптопи и части»**. Is that the right weight, or is repair the only focus now?
**Why it matters:** If still active it's revenue that would otherwise disappear from navigation. If not, the pages should be handled differently.
**Blocks:** Phase 2 nav finalisation
**Raised:** Phase 2 discussion
**Answer:**

---

### 16. What do customers actually say when they call?
**Status:** OPEN
**Question:** In the owner's own words — what are the most common complaints, phrased the way customers phrase them? (e.g. «прегрява», «не се включва», «изключва се сам», «бавен е», «залях го»…) Ideally 4–6 per service category.
**Why it matters:** The new design puts a symptom line under each category card and builds a symptom-organised «Не откривате проблема си?» section. Customers describe *symptoms*; the six categories are named by *cause*. The owner hears the real phrasing daily — far better than inventing it.
**Blocks:** Phase 3 content
**Raised:** Phase 2 discussion
**Answer:**

---

### 17. Categories 1 and 2 overlap — what was meant to be separate?
**Status:** OPEN
**Question:** "Ремонт на счупвания" and "Смяна на матрици, клавиатури, USB портове, захранващи букси, панти" describe overlapping work — a cracked screen is both a счупване and a смяна на матрица. What distinction was intended?
**Working assumption until answered:** Category 1 = physical/impact damage (паднал, счупен корпус, счупени панти, изкривено шаси); Category 2 = component replacement regardless of cause.
**Why it matters:** As written, category 1 has no services that aren't already in category 2 — it would launch with nothing of its own.
**Blocks:** Phase 3 content structure
**Raised:** Phase 2 discussion
**Answer:**

---

### 19. Category 5 is thin, and named as a solution rather than a problem
**Status:** OPEN
**Question:** "Смяна на вентилатори" describes the *fix*, but customers only know the *symptom* — «прегрява», «шуми», «изключва се сам». They may not recognise the category as theirs. It's also the thinnest of the six, holding essentially one service.
**Proposed option:** Rename to something symptom-led such as **«Прегряване и охлаждане»**, which customers would recognise immediately *and* which naturally absorbs профилактика (dust cleaning, thermal paste), fixing the thinness at the same time. Is that acceptable, or is there a reason to keep the fan-replacement framing?
**Why it matters:** Same recognition gap applies in milder form to categories 4 and 6 — worth checking the naming of all six while this is open.
**Blocks:** Phase 3 content structure
**Raised:** Phase 2 discussion
**Answer:**

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

## Answered

*(none yet — move items here as they're resolved, keeping the answer inline)*
