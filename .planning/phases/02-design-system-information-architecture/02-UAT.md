---
status: diagnosed
phase: 02-design-system-information-architecture
source: [02-01-SUMMARY.md, 02-02-SUMMARY.md, 02-03-SUMMARY.md, 02-04-SUMMARY.md, 02-05-SUMMARY.md, 02-06-SUMMARY.md, 02-07-SUMMARY.md]
started: 2026-08-07T00:00:00Z
updated: 2026-08-07T00:00:00Z
---

## Current Test

[testing complete]

## Tests

<!-- Entries 1-20 are deterministically covered: either by a SUMMARY coverage
     block whose verification refs pass, or by a measured run of the
     scripts/render-check.sh harness recorded in 02-RENDERED-VERIFICATION.md.
     They are NOT presented to the user. -->

### 1. Общо визуално приемане на редизайна
expected: Сайтът изглежда модерен на телефон и компютър; шестте категории се четат като отделни карти
result: issue
reported: "на телефон изглежда ок, но на компютър почти нищо не е както хората, лентата за навигиране вместо да показва отделните елементи на един ред ги подредило в колона като дори и съдържанието на 'Услуги' е видимо през цялото време и съответно само лентата за навигиране заема приблизително половината екран, скролвайки надолу всяка една от иконките заема целия екран и необходимо да оскролнеш още за да видиш и текста, не може и да става дума за карти за всяка една от тези услуги. като цяло всички икони са огромни и освен това ги намирам за трудни за разпознаване. мисля че ще е по-добре ако бъдат заменени с изображения"
severity: blocker
resolution: |
  Hypothesis CONFIRMED by the user: a hard reload (Cmd+Shift+R) restored the
  correct rendering — nav on one row, six cards, icons at normal size. The
  stylesheets as deployed are correct; the defect is that a cached copy cannot
  be invalidated. Gap G-02-1 is re-scoped accordingly: the rendering assertion
  passes on a cold cache and FAILS for any returning visitor, which is the
  condition that matters at cutover.

### 2. Симптомните редове на шестте карти звучат като езика на клиента
expected: Кратките редове под всяко заглавие ("паднал лаптоп, счупен корпус, разхлабени панти" и т.н.) са формулировки, които клиентите наистина използват
result: pass
reported: "нека да останат така засега"
note: "Owner accepted the developer-written phrasing as-is for now. Explicitly provisional ('засега') — if real customer phrasing is gathered later, this is a low-cost content edit in categories.php, not a structural change. Does NOT block the phase."
coverage_id: D10
source_summary: 02-02-SUMMARY.md

### 3. Трите нови URL адреса са правилните постоянни адреси
expected: profilaktika-laptop.html, optimizatsiq.html и zalivane-technosti.html са адресите, на които тези страници трябва да останат завинаги
result: pass
reported: "pass"
note: "Owner confirmed the three slugs are permanent. These are now committed URLs — a later change requires 301 redirects to preserve ranking and inbound links (MIGR-01/SEO-04 territory in Phase 4)."
coverage_id: D11
source_summary: 02-02-SUMMARY.md

### 4. Преместване на бутоните при зареждане на шрифта (FOUT)
expected: На бавна връзка при първо посещение двата бутона в началния екран не се преместват видимо, когато шрифтът Sofia Sans се зареди
result: [pending]
coverage_id: D8
source_summary: 02-05-SUMMARY.md
measured: "FAIL — CTA бутоните се местят 27.1px нагоре (праг 8px). h1 минава от 110.4px на 73.6px."

### 5. Набиране на телефона от реален телефон
expected: Докосването на бутона за обаждане отваря звънилката с попълнен номер +359 2 954 9710
result: pass
source: owner-confirmed
note: "Потвърдено от собственика на 2026-08-06. Записано като приемане от собственика, не като измерване."

### 6. Шестте категории като визуално отделни карти (D1)
expected: The homepage presents the six owner-priority services as six visually distinct cards
result: pass
source: automated
coverage_id: 02-02/D1

### 7. Симптомен ред под всяко заглавие (D2)
expected: Each of the six cards carries a short symptom line in plain customer language
result: pass
source: automated
coverage_id: 02-02/D2

### 8. Всяка карта води до работеща дестинация (D3)
expected: Every one of the six cards resolves to a working destination
result: pass
source: automated
coverage_id: 02-02/D3

### 9. Катч-ол за несъвпадащи проблеми (D4)
expected: A visitor whose problem matches none of the six finds a symptom-organised catch-all
result: pass
source: automated
coverage_id: 02-02/D4

### 10. Самодиагностика със собствен блок (D5)
expected: The self-diagnostic tool has its own homepage feature block
result: pass
source: automated
coverage_id: 02-02/D5

### 11. Двете равнопоставени основни действия (D6)
expected: Phone and Viber are reachable from the category area
result: pass
source: automated
coverage_id: 02-02/D6

### 12. Най-дългото име на категория се пренася на два реда (D7)
expected: The longest category name wraps to two lines inside its card at 360px
result: pass
source: automated
coverage_id: 02-02/D7

### 13. Първата карта е над сгъвката при 360x640 (D9)
expected: The first category card is fully visible above the fold at 360x640
result: pass
source: automated
coverage_id: 02-02/D9

### 14. Контраст на значката «Безплатна диагностика» (D1)
expected: Badge renders as dark on-brand ink on the amber fill in both themes
result: pass
source: automated
coverage_id: 02-05/D1
measured: "10.14:1 — беше 1.06:1"

### 15. Височина на съдържанието в началния екран (D3)
expected: Mobile hero content stack stays strictly under the resolved clamp min-height
result: pass
source: automated
coverage_id: 02-05/D3
measured: "233.4px под min-height 268.8px при 360x640 — 35.4px запас"

### 16. Фокус пръстен на всички CTA върху тъмна повърхност (D4, D7)
expected: All primary CTAs on a dark surface draw a focus ring at >= 3:1 (WCAG 2.1 SC 1.4.11), keyboard-observed, both themes, homepage and a category page
result: pass
source: automated
coverage_id: 02-05/D4+D7
measured: "index: 28 контрола, 0 без пръстен, най-лош 5.93:1 (тема B) / 4.50:1 (тема A). mehanichni-problemi: 25 контрола, 0 без пръстен, същите стойности."

### 17. CTA върху светла повърхност остават непроменени (D5)
expected: Light-surface primary CTAs unchanged, navy ring retained
result: pass
source: automated
coverage_id: 02-05/D5
measured: "7.85:1 (тема B) / 7.71:1 (тема A)"

### 18. Навигация при изключен JavaScript (N7)
expected: With scripting disabled, all five top-level items and all six category links are visible and activatable
result: pass
source: automated
coverage_id: 02-06/N7
measured: "Потвърдено при 360x640, 900x900 и 1440x900"

### 19. Няма хоризонтално превъртане при изключен JavaScript (N8)
expected: scrollWidth <= innerWidth at 360px, 900px and 1440px with scripting disabled
result: pass
source: automated
coverage_id: 02-06/N8
measured: "360/360 · 885/900 · 1425/1440"

### 20. Няма проблясък на отворена навигация (N9)
expected: With scripting enabled, the nav behaves as 02-03 verified, with no flash of an open nav
result: pass
source: automated
coverage_id: 02-06/N9
measured: "Мобилно и десктоп: списъкът е свит при зареждане, aria-expanded=false, no-js.css не е приложен"

### 21. covid.html е достъпна от долния колонтитул
expected: Линкът «Проект BG16RFOP002-2.073» присъства във всички шестнадесет страници и е четим
result: pass
source: automated
coverage_id: 02-07/IA-02
measured: "covid=1 на всички 16 страници; контраст на линка 10.44:1"

### 22. Един телефонен номер на едно място
expected: Every call CTA across the site dials one and the same string
result: pass
source: automated
coverage_id: 02-07/WR-10
measured: "Точно една CTA tel: стойност (+35929549710) на всичките 16 страници"

### 23. Разпознаваемост на шестте иконки (след поправката на кеша)
expected: Всяка от шестте иконки подсказва за коя услуга става дума още преди да се прочете заглавието
result: issue
reported: "проблема с иконите не е в размера а абстрактността така да се каже, без да се види текста отдолу би било мн трудно да се предположи какво искат да кажат. определено не искам да слагам снимки на тяхно място точно поради причините които ти изброи, но все пак бих искал да са някак по разпознаваеми и по възможност да не са само един цвят а може би да ползват двата основни цвята от темата"
severity: major
gap_ref: G-02-1b

## Summary

total: 23
passed: 20
issues: 3
pending: 0
skipped: 0
blocked: 0

## Gaps

- gap_id: G-02-1
  truth: "Every page renders with the new responsive design system and displays correctly on mobile and desktop viewports"
  status: failed
  reason: "User reported the desktop rendering is broken: nav stacked in a column with the Услуги sub-list permanently expanded and consuming ~half the viewport, no card treatment on the six categories, icons rendering at full-viewport size with the text pushed below them. Phone renders correctly."
  severity: blocker
  test: 1
  hypothesis: |
    Stale CSS cache, not a defect in the current stylesheets. Evidence:
      - components.css is served with cache-control: max-age=604800 (7 days),
        while the HTML is max-age=0 (always fresh).
      - Stylesheet links carry no version/hash (href="css/components.css"),
        so a new deploy cannot invalidate a cached copy.
      - components.css last-modified 2026-08-06 18:22 GMT; plans 02-01..02-04
        deployed earlier the same day. A desktop visit before that time holds a
        components.css from a state where the card grid and nav rules did not
        yet exist — which reproduces every reported symptom exactly (unstyled
        <ul> renders as a column, sub-list has no display:none, SVG icons have
        no width/height, cards have no surface).
      - Phone is unaffected because it is a separate cache.
    CONFIRMED 2026-08-08: the user hard-reloaded and the rendering corrected
    itself completely. The stylesheets are right; only cache invalidation is
    missing.
  impact_at_cutover: |
    This is the reason the gap stays open rather than being closed as "not a
    code defect". torin.bg is a live site with returning local customers. At the
    Phase 4 cutover the HTML changes immediately (max-age=0) while every
    returning visitor keeps a components.css from the OLD site for up to seven
    days — new markup styled by old CSS, which is precisely the broken rendering
    reported here. It would hit real customers, not reviewers, and there is no
    way to flush it remotely once shipped.
  method_defect: |
    Every harness probe requested the page with a ?v=<timestamp> cache-buster,
    which bypassed exactly this cache. The automated verification therefore
    measured a state no returning visitor sees, and would have missed this class
    of defect entirely regardless of how many checks it ran.
  artifacts:
    - path: "src/.htaccess"
      issue: "ExpiresByType text/css 'access plus 7 days' on a live staging preview whose CSS changes several times per day"
    - path: "src/includes/header.php"
      issue: "Stylesheet links have no version query string, so a deploy cannot invalidate a cached copy"
  missing:
    - "Cache-busting on CSS links (e.g. ?v=<filemtime>, which needs no build step since the pages are PHP)"
    - "Or a short CSS max-age while /new/ is an actively-reviewed staging preview"

- gap_id: G-02-1b
  truth: "The six category icons are recognisable as the services they represent, without reading the label beneath"
  status: failed
  reason: "Confirmed at correct size after the cache fix, so this is independent of G-02-1. User: the icons are too abstract — without the caption it would be very hard to guess what they depict. Photographs were explicitly REJECTED by the owner for the trust/weight reasons discussed. Direction given: keep icons, make them more literal, and use the theme's two main colours rather than a single flat colour."
  severity: major
  test: 23
  owner_direction:
    - "Keep SVG icons — do NOT substitute photographs (owner decision, 2026-08-08)"
    - "Make the depicted object more literal/concrete so it reads without the label"
    - "Use the two main theme colours rather than one flat colour"
  artifacts:
    - path: "src/includes/icons.php"
      issue: "All six category glyphs are single-colour line art (stroke=currentColor, fill=none, 15 of each) and depict abstract compositions: cat-1 fracture line across a lid corner, cat-2 panel lifting with ribbon connector, cat-3 rising arrow over a laptop, cat-4 droplet over a chip grid, cat-5 fan blades with heat waves, cat-6 wrench crossing an instrument outline"
  missing:
    - "Redrawn cat-1..cat-6 with more literal subject matter"
    - "A two-tone treatment using --c-brand (#ffc70a) and the navy ink token"
  constraints: |
    Three constraints the redraw must respect, all established earlier in this phase:

    1. THEME SWITCHING. The icons currently inherit their colour via
       stroke="currentColor", which is why they work unchanged in Theme A and
       Theme B. Hardcoding two hex values would break that. The accent must come
       from a CSS custom property or a class, so both themes still resolve.

    2. CONTRAST. Amber #ffc70a measures roughly 1.7:1 on white — this phase just
       spent two plans (CR-01, CR-02) fixing exactly that class of defect. Amber
       may carry a filled accent area, but it must NOT be the stroke that carries
       the meaning of the glyph, and it must never be the only thing
       distinguishing one icon from another.

    3. CARD SURFACE. The icons sit on --c-surface-2 in the card header, not on
       white and not on the amber fill, so the navy/amber pairing has to be
       checked against that surface specifically.

- gap_id: G-02-4
  truth: "The web-font swap does not visibly displace the two hero CTA buttons on a throttled first visit"
  status: failed
  reason: "Measured FAIL: hero CTAs move 27.1px upward across the font swap (threshold 8px). At 360x640 the h1 renders 110.4px tall in the fallback stack (three lines) and 73.6px with Sofia Sans (two lines); the 36.8px collapse pulls both CTAs up. Owner chose the reserve-space option over font-display:optional, so first-paint Cyrillic readability (the stated reason swap was chosen) is preserved."
  severity: major
  test: 4
  owner_direction:
    - "Option 3 — reserve space for the heading. NOT font-display:optional (would leave slow first visits permanently in the fallback face), NOT accept-as-is."
  artifacts:
    - path: "src/css/base.css"
      issue: "Two @font-face blocks declare font-display:swap with no metric-matched fallback, so the fallback stack sets a different line count for the same string"
  missing:
    - "A metrics-matched fallback so the fallback and Sofia Sans occupy the same block height for the hero h1"
  implementation_note: |
    IMPORTANT — a naive min-height on the h1 does NOT fix this, and the plan must
    not reach for one. The fallback needs MORE lines than Sofia Sans (three vs
    two), not fewer, so a min-height sized to the Sofia Sans height would be
    overflowed by the fallback rather than absorbing it; the CTAs would still move.

    The mechanism that actually reserves the space is a metric-adjusted fallback
    @font-face — a local() fallback declared with size-adjust (and, if needed,
    ascent-override / descent-override) tuned so the fallback sets the SAME
    number of lines for the hero string at the narrow breakpoint. Widely
    supported in current Chrome, Firefox and Safari.

    Whatever mechanism is chosen must be re-measured with
    scripts/render-check.sh scripts/probes/font-swap.js at 360x640, and must not
    regress the D-30 above-the-fold invariant — the hero content stack currently
    measures 233.4px against a 268.8px min-height (35.4px of headroom), verified
    in 02-RENDERED-VERIFICATION.md.