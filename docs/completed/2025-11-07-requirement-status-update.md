**Контекст:** Этап 3 `docs/documentation-plan.md` — интерактивная «Карта требований».

**Что сделано:**
- Добавлена форма изменения статуса требований напрямую на вкладке клиента (фильтры сохранены при возврате).
- Создан `RequirementController::actionUpdateStatus`, который проверяет доступы и фиксирует `completed_at`.
- Модель `Requirement` дополнена константами статусов, метками и проверкой просрочки.
- Уведомление в документации о возможности менять статус через UI.

**Артефакты:**
- `yii-app/models/Requirement.php`
- `yii-app/controllers/RequirementController.php`
- `yii-app/views/client/_tab_requirements.php`, `yii-app/views/client/view.php`
- `docs/features/requirements-tracker.md`

**Следующие шаги:** добавить историю изменений и комментарии, синхронизировать статусы с календарём/рисками и покрыть новый сценарий автотестами.
