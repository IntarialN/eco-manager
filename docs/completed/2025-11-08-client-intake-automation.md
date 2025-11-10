**Контекст:** Этап 3 из `docs/documentation-plan.md` — перенос анкеты и авторасчёта требований в рабочее приложение (строка TODO «Анкета клиента»).

**Что сделано:**
- Расширена форма `ClientIntakeForm` и экран `/client/onboard` данными из `docs/client/onboarding-form.md`: объёмы выбросов/отходов, водопользование, опасные вещества, побочная продукция, обучение ответственных.
- В `RequirementBuilderService` реализована генерация требований по правилам `docs/requirements/catalog.md` и `docs/requirements/rules.md` (коды `REQ-01…REQ-22`, а также дополнительные `REQ-WATER-*`, `REQ-TRAINING-RESP`, `REQ-BYPRODUCT-01`). Данные анкеты сохраняются в новых полях `client` (миграция `m230000_000012_extend_client_profile`), что позволяет повторно использовать профиль.
- Обновлены юнит-тесты (`RequirementBuilderServiceTest`) и тестовая схема (`ControllerTestCase`) для проверки условий включения инструкций, обучения, водопользования.
- Таблица TODO отражает выполненный пункт («Анкета клиента» → «Готово»).

**Артефакты:**
- `yii-app/migrations/m230000_000012_extend_client_profile.php`
- `yii-app/models/Client.php`
- `yii-app/models/forms/ClientIntakeForm.php`
- `yii-app/views/client/onboard.php`
- `yii-app/components/RequirementBuilderService.php`
- `yii-app/tests/unit/RequirementBuilderServiceTest.php`
- `docs/documentation-todo.md`

**Следующие шаги:**
- Подтвердить перечень вопросов анкеты у заказчика и дополнить форму (если появятся новые атрибуты) вместе с документацией.
- Подключить генерацию календарных событий и уведомлений сразу после расчёта требований, используя сохранённые атрибуты профиля.
