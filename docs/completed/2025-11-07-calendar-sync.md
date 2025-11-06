**Контекст:** Связь требований, календаря и риска (этап 3/5 плана документации).

**Что сделано:**
- При обновлении статуса требования `RequirementController::actionUpdateStatus` синхронизирует связанные события (`scheduled` → `done`/`overdue`) и риски (`open`/`mitigation`/`closed`).
- На вкладке календаря статусы отображаются с цветовым обозначением; на вкладке требований подсвечивается наличие незакрытого риска.
- Обновлены `docs/features/calendar.md`, `docs/features/risk-management.md`, `docs/documentation-todo.md`.

**Артефакты:**
- `yii-app/controllers/RequirementController.php`
- `yii-app/models/CalendarEvent.php`
- `yii-app/views/client/_tab_calendar.php`
- `yii-app/views/client/_tab_requirements.php`
- `docs/features/calendar.md`
- `docs/features/risk-management.md`
- `docs/documentation-todo.md`

**Следующие шаги:** связать календарь с уведомлениями (отправка напоминаний), добавить автотесты и расширить историю рисков.
