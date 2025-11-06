**Контекст:** Этап 3 `docs/documentation-plan.md` — контроль синхронизации риска/календаря (см. TODO: «Риски | Синхронизация…»).

**Что сделано:**
- Подключён PHPUnit и тестовая конфигурация для Yii-приложения.
- Реализован `tests/unit/RequirementStatusUpdateTest.php`, проверяющий историю требований, обновление статусов рисков и событий календаря при `RequirementController::actionUpdateStatus`.
- Добавлена тестовая заглушка сессии и настройка in-memory SQLite для изолированного окружения.

**Артефакты:**
- `yii-app/composer.json`, `yii-app/composer.lock`
- `yii-app/phpunit.xml`, `yii-app/tests/bootstrap.php`, `yii-app/tests/config/test.php`
- `yii-app/tests/support/ArraySession.php`, `yii-app/tests/unit/RequirementStatusUpdateTest.php`
- `docs/documentation-todo.md`, `docs/features/requirements-tracker.md`, `docs/features/calendar.md`

**Следующие шаги:** покрыть сценарии управления документами из карточки требования и план действий по рискам (см. строки TODO для блока документов и рисков).
