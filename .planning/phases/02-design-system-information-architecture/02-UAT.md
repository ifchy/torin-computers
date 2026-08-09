---
status: complete
phase: 02-design-system-information-architecture
source: [02-01-SUMMARY.md, 02-02-SUMMARY.md, 02-03-SUMMARY.md, 02-04-SUMMARY.md, 02-05-SUMMARY.md, 02-06-SUMMARY.md, 02-07-SUMMARY.md, 02-08-SUMMARY.md, 02-09-SUMMARY.md, 02-VERIFICATION.md]
started: 2026-08-07T00:00:00Z
updated: 2026-08-09T14:58:25Z
round: 2
---

## Current Test

[testing complete — round 2]

Всички 28 теста са отработени. Нула отворени дефекта за Фаза 2.
Две отложени за по-късни фази, и двете със записано решение и проследяване:
  - тест 23 / G-02-1b  -> Фаза 3 (преначертаване на иконките)
  - тест 28 / G-02-5   -> Фаза 4 (cutover проверка на Viber бутона)

## Tests

<!-- Entries 1-20 are deterministically covered: either by a SUMMARY coverage
     block whose verification refs pass, or by a measured run of the
     scripts/render-check.sh harness recorded in 02-RENDERED-VERIFICATION.md.
     They are NOT presented to the user. -->

### 1. Общо визуално приемане на редизайна
expected: Сайтът изглежда модерен на телефон и компютър; шестте категории се четат като отделни карти
result: pass
reported: "на телефон изглежда ок, но на компютър почти нищо не е както хората, лентата за навигиране вместо да показва отделните елементи на един ред ги подредило в колона като дори и съдържанието на 'Услуги' е видимо през цялото време и съответно само лентата за навигиране заема приблизително половината екран, скролвайки надолу всяка една от иконките заема целия екран и необходимо да оскролнеш още за да видиш и текста, не може и да става дума за карти за всяка една от тези услуги. като цяло всички икони са огромни и освен това ги намирам за трудни за разпознаване. мисля че ще е по-добре ако бъдат заменени с изображения"
original_result: issue
original_severity: blocker
closed_by: 25
closed_at: 2026-08-09
closure_note: |
  Затворен от тест 25: собственикът потвърди правилното рендиране на същия
  компютър с нормално презареждане, след като план 02-08 достави
  инвалидирането на кеша. Забележката за иконките в същия доклад НЕ се затваря
  тук — тя е отделена като G-02-1b и отложена за фаза 3 по решение на
  собственика (тест 23).
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
result: pass
coverage_id: D8
source_summary: 02-05-SUMMARY.md, 02-09-SUMMARY.md
source: automated
measured: "0px на 360x640, 390x844 и 1440x900 (праг 8px); heroHeightDeltaPx: 0 във всеки проход. Беше 27.1px."
retested: 2026-08-09
retest_note: |
  Първоначалното измерване беше FAIL — 27.1px, h1 минаваше от 110.4px на 73.6px.
  План 02-09 затвори пропуска G-02-4 с метрично напаснат резервен шрифт
  ('Sofia Sans Fallback', size-adjust: 97%).

  Резултатът 0px беше ПРОВЕРЕН, а не приет на доверие: точно такова число би
  докладвал и заслепен тест. Верификаторът обори хипотезата за заслепяване с
  CSS.getPlatformFontsForNode — при блокиран woff2 h1 се изрисува с Arial
  (isCustomFont false), при разрешен — със Sofia Sans (isCustomFont true).
  Наистина различни шрифтове, еднаква височина на блока.

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
result: skipped
reported: "проблема с иконите не е в размера а абстрактността така да се каже, без да се види текста отдолу би било мн трудно да се предположи какво искат да кажат. определено не искам да слагам снимки на тяхно място точно поради причините които ти изброи, но все пак бих искал да са някак по разпознаваеми и по възможност да не са само един цвят а може би да ползват двата основни цвята от темата"
reason: "Deferred follow-up: нека ги оставим за фаза 3, дотогава ще използвам времето да търся още идеи защото засега не съм удовлетворен, но и не искам да губя прекалено много време за тях сега при положения че замяната на иконките може да стане лесно във всеки един момент"
gap_ref: G-02-1b (deferred, not blocking)

<!-- Round 2 — added 2026-08-09 from 02-VERIFICATION.md (status: human_needed,
     4/4 success criteria verified) after plans 02-08 and 02-09 closed G-02-1
     and G-02-4. These four are the only items no probe can settle. -->

### 24. Български буквени форми в Sofia Sans
expected: Кирилицата се изписва с българските буквени форми (локализирани д, л, п, ц, ш), а не с руските подразбиращи се очертания
result: pass
source: human + automated (converted from judgment to measurement during testing)
measured: |
  Контролирано сравнение на живата страница: един и същ низ, същият шрифт и
  размер, сменен само lang="bg" -> lang="ru".
    - ширини ИДЕНТИЧНИ (1288.75px и в двата случая) — очаквано, locl сменя
      формата на глифа, не ширината му
    - пиксели РАЗЛИЧНИ -> шрифтът носи български locl форми И те са активни
  Видимо: т->m, и->u, п->n, л->заоблено ʌ, д->g-подобно, г->r.
  resolvedFamily: "Sofia Sans", "Sofia Sans Fallback", "Segoe UI", Roboto, ...
method_note: |
  Тестът беше записан като „човешка преценка", но се оказа измерим. Ако двата
  реда бяха байт за байт еднакви, българските форми нямаше да се прилагат —
  това е разграничението, което окото не може да направи без еталон.
  Диагностиката беше еднократна (scratchpad, не в репото). Проверката с
  CSS.getPlatformFontsForNode в нея НЕ се изпълни (грешка в probe-а); изводът
  не зависи от нея, а верификаторът вече я беше направил отделно.
reported: "pass"
why_human: "Judgment-tier prohibition from plan 02-01 and the whole reason Sofia Sans was chosen (D-06a). Substantially de-risked: CSS.getPlatformFontsForNode confirms the live h1 is painted by the self-hosted Sofia Sans (postScriptName SofiaSans-Regular_Bold, isCustomFont true), and D-06a's premise is that Bulgarian forms are that family's DEFAULT outlines. What remains is eyes on the glyph shapes, which no probe can judge."
how: "Отвори https://torin.bg/new/index.html и една категорийна страница; прочети заглавията и текста на десктоп и на телефон."

### 25. Потвърждение на десктоп рендирането след поправката на кеша
expected: Навигацията е на един ред, шестте категории са с картов вид, иконките са с нормален размер — и остава така без hard reload
result: pass
source: human
reported: "pass"
closed_test: 1
measured: |
  Собственикът потвърди на същия компютър, който съобщи проблема в тест 1, с
  НОРМАЛНО презареждане. Това е потвърждението, което липсваше: верификаторът
  беше възпроизвел правилното рендиране headless на 1440x900 (навигация на
  един ред, под-менюто display:none, картова мрежа 3 колони, иконки 143px), но
  headless браузър винаги тръгва със студен кеш и затова НЕ може да докаже, че
  механизмът за инвалидиране работи при реален посетител с топъл кеш.
  Точно това доказва този тест.
why_human: "Test 1 is still recorded as `issue` / severity blocker and has not been re-confirmed by the owner since 02-08 shipped. The verifier reproduced the correct rendering headlessly at 1440x900 (nav on one row, sub-menu display:none, 3-column card grid, 143px icons) and confirmed the invalidation mechanism works, but the owner's acceptance is what closes the entry."
how: "На същия десктоп, който съобщи проблема в тест 1: зареди страницата с НОРМАЛНО презареждане (не Cmd+Shift+R), после я презареди пак след няколко минути."
closes: 1

### 26. Sticky лентата за обаждане на iPhone с home indicator (WR-11)
expected: И двата бутона се натискат изцяло; никоя част не се прихваща от системната жестова зона и долният колонтитул не е закрит
result: pass
source: human
reported: "buttons can be pressed on iPhone"
measured: |
  WR-11 не се потвърждава на практика: и двата бутона се натискат изцяло на
  реален iPhone. Кодовият факт остава — .callbar е position:fixed; bottom:0
  без safe-area отстъп и grep -rn 'env(' src/css/ връща нула съвпадения — но
  предвиденото практическо последствие не се възпроизвежда.
  Оставя се като известен риск, не като дефект: заслужава повторна проверка,
  ако лентата стане по-ниска или отстъпите ѝ се променят.
note: |
  По време на този тест изплува ОТДЕЛЕН дефект на Android — виж тест 28.

### 28. Бутонът «Пишете във Viber» отваря разговор
expected: Натискането на бутона отваря разговор във Viber с магазина
result: deferred
source: human
reported: "there is a problem with the пишете във Viber button, when pressed on the android it says 'the requested page is unavailable. please update to the latest version.' pressing the update button takes me to the store only to see I am running the latest version of Viber"
result_note: |
  Дефектът е реален и потвърден, но НЕ е блокер за Фаза 2. Решение 2026-08-09:
  бутонът остава на 088 945 8404, собственикът ще подсигури Viber акаунт на
  този номер, а проверката се пренася като cutover gate за Фаза 4.
found_during: 26
gap_ref: G-02-5
status: deferred to phase 4
tracked_as: .planning/todos/pending/verify-viber-button-before-launch.md
measured: |
  Четири деплоя на staging, всеки натиснат на реален Android телефон.
  Схемата viber://chat?number= е една и съща във всичките четири — сменя се
  само номерът, така че резултатът изолира точно една променлива:

    +35929549710  (02 954 9710, стационарен) -> ГРЕШКА
    +359879128244 (087 912 8244, мобилен)    -> ГРЕШКА
    +359889458404 (088 945 8404, мобилен)    -> ГРЕШКА
    контролен номер, за който се знае, че има Viber -> ОТВАРЯ РАЗГОВОР

  Контролният номер е това, което прави останалите три показателни: без него
  нямаше как да се разграничи „грешен номер" от „грешна схема". Схемата е
  правилна и НЕ трябва да се пипа — viber://add или друга промяна би гонила
  вече отхвърлена хипотеза.
conclusion: |
  Магазинът няма Viber акаунт на нито един от трите номера, които публикува.
  Въпросът престава да е технически и става продуктов: D-16 прави чата
  равнопоставено основно действие, а такова действие в момента е задънена
  улица на всичките 16 страници. Решението е на собственика —
  OWNER-QUESTIONS #21, преформулиран.
why_human: "Review finding WR-11 is still UNMITIGATED and untriaged: `.callbar` is `position: fixed; bottom: 0; height: 56px` with no safe-area allowance, and `grep -rn 'env(' src/css/` returns zero hits. The code fact is established; the practical impact is device-specific and cannot be measured under viewport emulation. The fix is already written out at 02-REVIEW.md:479."
how: "Отвори https://torin.bg/new/index.html на iPhone X или по-нов и опитай да натиснеш долната половина на двата бутона в лепкавата лента."

### 27. Съдба на problem-stari.html
expected: Записано решение на собственика — да се линкне, да се слее с друго съдържание, или да се пенсионира
result: pass
source: human
decision: RETIRE with a 301
decided_at: 2026-08-09
reported: "пенсионираме я"
rationale: |
  Страницата не попада в това, което собственикът заяви като фокус на новия
  сайт — шестте категории. Това е продуктово решение и то надделява над
  маргинална SEO стойност.
implementation_for_phase_4: |
  Пенсионирането НЕ е изтриване. Фаза 4 при cutover трябва да добави:

    Redirect 301 /problem-stari.html /zalivane-technosti.html

  Целта е избрана тематично, а не произволно: съдържанието на страницата НЕ е
  за батерии въпреки заглавието си — то описва захранващата част на дънната
  платка (Charger и StandBy процесорите, ACPI, управление на вентилатора и
  клавиатурата). Батерията/адапторът са причината, дънната платка е темата.
  Категория kat-4 «Заливане и ремонт на дънни платки» вече носи симптома
  „не зарежда", което е точно това, което страницата обяснява.

  Тематичната близост е задължителна, не козметична: 301 към несвързана
  страница (напр. началната) може да бъде третирано от Google като soft 404 и
  изобщо да не се зачете като пренасочване.

  Правилото да остане постоянно. Google препоръчва минимум една година, но
  цената е един ред, а външни линкове и отметки не изтичат.
verified_feasible: |
  mod_rewrite е активен на хоста — src/.htaccess вече съдържа работещо
  RewriteEngine On и 301 правило (редовете 5-12). И двата URL-а връщат 200
  към момента на решението.
no_cleanup_needed: |
  Няма вътрешни препратки за чистене: страницата има НУЛА входящи линка от
  всичките 16 страници — това беше и оригиналната находка, довела до теста.
open_question_for_owner: |
  Не е проверено дали URL-ът изобщо получава импресии. Ако собственикът иска
  да е сигурен, Google Search Console → Performance → филтър по URL показва
  това за минути. Нула импресии прави решението безспорно; не променя
  препоръката, само увереността в нея.
why_human: "A content/product decision, not a code fact. The code fact is established: zero inbound links across all sixteen deployed pages. If retired, Phase 4 must serve a 301 rather than a bare 404 (D-36)."
how: "Реши дали «Чести проблеми» (problem-stari.html) да остане в новия сайт."

## Summary

total: 28
passed: 26
issues: 0
pending: 0
skipped: 1
deferred: 1
blocked: 0

<!-- passed: 24 = the original 20, plus test 4 (re-measured 27.1px -> 0px by
     02-09), plus test 1 (closed by test 25), plus tests 24 and 25.
     skipped: 1 = test 23 (G-02-1b icons, deferred to Phase 3 by owner decision). -->

## Current Round

round: 2
pending_tests: []
issues_open: []
deferred_to_later_phases: [23, 28]
round_complete: 2026-08-09
source: 02-VERIFICATION.md (status: human_needed, 4/4 success criteria verified)

## Gaps

- gap_id: G-02-1
  truth: "Every page renders with the new responsive design system and displays correctly on mobile and desktop viewports"
  status: resolved
  resolved_by: 02-08-PLAN.md
  resolved_at: 2026-08-09
  resolution: |
    Closed by plan 02-08. Every stylesheet and script URL emitted by the shared
    head now carries ?v=<filemtime> via src/includes/asset-version.php, so a
    deployed change alters the URL and a returning visitor fetches new bytes
    instead of a cached copy. The staging text/css and JS cache lifetime dropped
    from 604800s to 300s as a second, independent line of defence.

    Verified twice, by two parties. The executor's own freeze detector: a
    byte-identical redeploy of js/site.js MOVED the token (1785984699 →
    1786283329) with sha256 unchanged — which is what proves the stamp is live,
    since the token/Last-Modified equality proves only path resolution.
    The verifier independently ran scripts/asset-version-check.sh (exit 0) and
    added a second freeze argument: five distinct token values, each equal to its
    own asset's origin Last-Modified, cannot come from a constant.

    The method_defect below is closed durably rather than for one run:
    scripts/asset-version-check.sh fetches all sixteen pages on BARE URLs with no
    cache-buster, so the blind spot that let this class of defect through is now
    a committed gate.

    Note on impact_at_cutover: the recorded cutover reasoning was already
    retracted in severity_correction (zero filename overlap between legacy and
    new CSS means no returning visitor holds a new file cached at cutover). What
    this fix actually buys is the OTHER half — every future CSS edit after launch
    now invalidates immediately instead of stranding returning customers on a
    stale copy.
  reason: "User reported the desktop rendering is broken: nav stacked in a column with the Услуги sub-list permanently expanded and consuming ~half the viewport, no card treatment on the six categories, icons rendering at full-viewport size with the text pushed below them. Phone renders correctly."
  severity: major
  test: 1
  severity_correction: |
    Originally recorded as blocker on the reasoning that returning torin.bg
    customers would get new HTML with old CSS at cutover. That reasoning was
    WRONG and is retracted. The owner asked why the CSS is not simply renamed in
    the new version, which prompted the check: the legacy site loads
    assets1/css/theme.min.css, business.css, animation.css; the new site loads
    css/base.css, layout.css, components.css. Zero overlap in filename OR
    directory, so at cutover no returning visitor holds any of the new files
    cached and everyone gets fresh CSS.

    What remains real, hence major rather than resolved: staging review is
    unreliable while /new/ is being iterated (this is what bit the owner), and
    every FUTURE css edit after launch leaves returning customers on a stale
    copy for up to 7 days.
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

- gap_id: G-02-5
  truth: "The «Пишете във Viber» button opens a Viber conversation with the shop"
  status: deferred
  deferred_to_phase: 4
  deferred_at: 2026-08-09
  deferred_reason: |
    Решение 2026-08-09: бутонът ОСТАВА на 088 945 8404, а собственикът ще
    подсигури Viber акаунт на този номер. Кодът е завършен и правилен —
    липсва акаунт от другата страна, което не е задача за разработка.

    Не е блокер за Фаза 2. Превръща се в CUTOVER gate: преди официалното
    пускане бутонът трябва да се натисне на реален телефон с инсталиран Viber
    и да се потвърди, че отваря разговор.
  tracked_as: .planning/todos/pending/verify-viber-button-before-launch.md
  original_severity: blocker
  reason: "User reported on Android: 'the requested page is unavailable. please update to the latest version.' The Viber update prompt leads to the store, which shows the latest version is already installed — i.e. Viber cannot resolve the deep link, not a stale client."
  test: 28
  found_during: 26
  affects: "16 deployed pages — index.html (hero + mid-page + callbar), footer.php (every page), category-page.php"
  root_cause: |
    RESOLVED BY TEST 2026-08-09: the shop has no Viber account on ANY of the
    three numbers it publishes. Candidate (b) — wrong deep-link scheme — is
    ELIMINATED: a control number known to have Viber opened a conversation
    normally through the identical viber://chat?number= href. Candidate (a) is
    confirmed and is total, not partial: all three numbers fail, including both
    mobiles. The fix is therefore NOT a code change and NOT another number.
  escalated_to: "OWNER-QUESTIONS #21, reframed from 'which number' to a D-16 design decision: should the chat button exist at all, and on what account"
  root_cause_candidates:
    - candidate: "The linked number has no Viber account"
      confidence: CONFIRMED — all three numbers tested and failed
      evidence: |
        site-config.php:66 sets 'viber' => '+35929549710'. That is 02 954 9710 —
        a SOFIA LANDLINE (area code 02). Viber accounts are provisioned against
        mobile numbers, so a landline will not resolve to a Viber user.
        The shop's other two numbers, 088 9458404 and 087 9128244, are mobiles.
        This was never a discovered defect — it was a KNOWN open assumption:
        site-config.php:65 marks it [ASSUMED] against OWNER-QUESTIONS #21, and
        index.html:22-26 repeats the flag. OWNER-QUESTIONS.md:101 predicted this
        exact outcome verbatim: "A chat link to a number that has no Viber
        account is a dead end on the site's single most important conversion
        action."
    - candidate: "viber://chat is the wrong deep-link path for a non-contact"
      confidence: ELIMINATED — control number opened a chat through this exact href
      evidence: |
        The emitted href is viber://chat?number=%2B35929549710. Viber also
        documents viber://add?number=... and the generic error text the user saw
        is Viber's fallback for ANY deep link it cannot resolve — so the scheme
        cannot be exonerated by this report alone. Must be retested with a number
        that definitely HAS Viber before concluding the path is correct;
        otherwise a number fix could be credited with a scheme fix or vice versa.
  blocked_on: "OWNER-QUESTIONS #21 (reframed) — the shop appears to have no Viber account at all; whether the button should exist is a D-16 decision"
  missing:
    - "An owner decision: does a Viber account exist on any number, published or not (incl. Viber Business)?"
    - "If not — remove the button, replace it (WhatsApp? enquiry form? interacts with OWNER-QUESTIONS #2), or keep it with a fallback"
    - "A fallback for visitors with no Viber installed (currently a dead end for them too, independently of the account question)"
  not_missing:
    - "A scheme change. viber://chat?number= is proven correct; changing it would chase an eliminated hypothesis."

- gap_id: G-02-1b
  truth: "The six category icons are recognisable as the services they represent, without reading the label beneath"
  status: deferred
  deferred_to_phase: 3
  deferred_reason: "Owner decision 2026-08-09: wants time to gather more ideas, and the swap is cheap at any point. NOT a Phase 2 blocker and must not spawn a gap-closure plan. Tracked as .planning/todos/pending/redraw-category-icons.md (resolves_phase: 3). Premise verified: icon names occupy 6 lines in categories.php, the drawings live in icons.php, and components.css:195 already supports <img>, so even a switch to raster images touches nothing else." 
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
  status: resolved
  resolved_by: 02-09-PLAN.md
  resolved_at: 2026-08-09
  resolution: |
    Closed by plan 02-09. Displacement measured at 0px, down from 27.1px
    (threshold 8px), at 360x640, 390x844 and 1440x900 — identical h1 heights and
    heroHeightDeltaPx: 0 in every pass.

    Mechanism is the metric-adjusted fallback the implementation_note prescribed,
    NOT a min-height: a 'Sofia Sans Fallback' @font-face at size-adjust: 97%,
    inserted into --font-sans immediately after 'Sofia Sans'. Every number was
    measured by the committed probe scripts/probes/font-fallback-metrics.js, not
    estimated; the two-line range measured 70-106%, so the shipped 97% carries
    9 points of margin on the safe (narrow) side.

    Two deviations from the plan as written, both forced and both measured:
    ONE face at weight 400 rather than two, because in Chromium local() resolves
    only by family name — every bold form (Arial Bold, Arial-BoldMT,
    HelveticaNeue-Bold, ...) rejects outright, and sourcing a 700 face from a
    regular file suppresses synthetic bolding and renders the heading light.

    The maxAbsDeltaPx: 0 was verified rather than trusted, since that is also
    exactly what a blinded gate reports. The verifier falsified the blinding
    hypothesis with CSS.getPlatformFontsForNode: in the blocked pass the h1 is
    painted by Arial (isCustomFont false) with both Sofia faces in status error;
    in the allowed pass by Sofia Sans (isCustomFont true). Genuinely different
    faces, same block height. The woff2 preload is still unstamped, so
    font-swap.js's *.woff2 block glob still bites.

    D-30 re-measured unchanged: 233.4px content stack under a 268.8px min-height.

    NOT verified on Segoe UI (Windows) or Roboto (Android) — neither is installed
    on the measuring machine. Both are narrower than Arial so the calibration errs
    safe on them, but that is a reasoned expectation, not a measurement.
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

## Deferred Follow-Ups

- test: 23
  gap_id: G-02-1b
  idea: "Преначертаване на шестте иконки — по-конкретни и двуцветни. Собственикът иска време да събере идеи; смяната е евтина по всяко време."
  deferred_at: 2026-08-09
  deferred_to_phase: 3
  tracked_as: .planning/todos/pending/redraw-category-icons.md
