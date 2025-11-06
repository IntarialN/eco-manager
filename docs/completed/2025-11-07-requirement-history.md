**Контекст:** Этап 3 `docs/documentation-plan.md` — история изменений «Карты требований».

**Что сделано:**
- Добавлена таблица `requirement_history` и модель, фиксирующие смену статусов.
- `RequirementController::actionUpdateStatus` теперь пишет записи в историю, принимает комментарий и отображает его на вкладке требований.
- UI вкладки пополнен полем «Комментарий» и collapse-историей.
- Документ `docs/features/requirements-tracker.md` обновлён описанием нового сценария.

**Артефакты:**
- `yii-app/migrations/m230000_000003_create_requirement_history.php`
- `yii-app/models/RequirementHistory.php`
- `yii-app/controllers/RequirementController.php`
- `yii-app/views/client/_tab_requirements.php`
- `docs/features/requirements-tracker.md`

**Следующие шаги:** добавить комментарии/причины изменений, включить историю в отчётность и покрыть миграцию автотестами.
