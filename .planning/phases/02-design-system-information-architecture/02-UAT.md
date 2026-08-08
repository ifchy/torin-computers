---
status: testing
phase: 02-design-system-information-architecture
source: [02-01-SUMMARY.md, 02-02-SUMMARY.md, 02-03-SUMMARY.md, 02-04-SUMMARY.md, 02-05-SUMMARY.md, 02-06-SUMMARY.md, 02-07-SUMMARY.md]
started: 2026-08-07T00:00:00Z
updated: 2026-08-07T00:00:00Z
---

## Current Test

number: 1
name: Общо визуално приемане на редизайна
expected: |
  Отваряте https://torin.bg/new/ на телефон и на компютър. Сайтът изглежда
  модерен и подреден, а не като стария шаблон. Шестте категории услуги се
  виждат ясно като отделни карти, а не като дълъг списък с еднакви иконки.
awaiting: user response

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
result: [pending]
coverage_id: D10
source_summary: 02-02-SUMMARY.md

### 3. Трите нови URL адреса са правилните постоянни адреси
expected: profilaktika-laptop.html, optimizatsiq.html и zalivane-technosti.html са адресите, на които тези страници трябва да останат завинаги
result: [pending]
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

## Summary

total: 22
passed: 18
issues: 1
pending: 3
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
  truth: "The six category icons are recognisable as the services they represent"
  status: failed
  reason: "User finds the icons hard to recognise and suggests replacing them with photographs/images. Reported alongside G-02-1; the 'huge' half of the complaint is likely a symptom of the stale CSS, but the recognisability judgement is independent of size and survives the cache fix."
  severity: major
  test: 1
  note: "Separated from G-02-1 deliberately — one is a delivery defect, the other is a design decision. Confirm after the cache issue is resolved, since icons at the correct size may read differently."
  artifacts: []
  missing: []
