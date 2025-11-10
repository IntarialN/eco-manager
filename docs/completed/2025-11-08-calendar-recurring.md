**Контекст:** Этап 1 (`docs/documentation-structure.md`) — блок «Календарь». Закрываем TODO «Нет автоматического переноса дат после сдачи отчётов».

**Что сделано:**
- Добавлены поля периодичности в `calendar_event` (миграция `m230000_000013_add_calendar_recurrence.php` и обновление базовой схемы): `start_date`, `periodicity`, `custom_interval_days`, `reminder_days`.
- Расширен `CalendarEvent` моделями периодичности, расчётом следующей даты, отображением подписи в UI (`client/_tab_calendar.php`) и стилями.
- В `RequirementController::syncCalendar` реализован пересчёт: при завершении требования события отмечаются выполненными, а для повторяющихся создаётся новая запись с будущей датой (ежегодной/квартальной/месячной/кастомной). Добавлен тест `testRecurringEventCreatesSubsequentOccurrences`.
- Обновлены фикстуры и etl (`ControllerTestCase`, `RequirementStatusUpdateTest`) и документация (`docs/features/calendar.md`, `docs/documentation-todo.md`).

**Артефакты:**
- `yii-app/migrations/m230000_000013_add_calendar_recurrence.php`
- `yii-app/migrations/m230000_000000_init_schema.php`
- `yii-app/models/CalendarEvent.php`
- `yii-app/controllers/RequirementController.php`
- `yii-app/views/client/_tab_calendar.php`
- `yii-app/tests/unit/ControllerTestCase.php`
- `yii-app/tests/unit/RequirementStatusUpdateTest.php`
- `docs/features/calendar.md`
- `docs/documentation-todo.md`

**Следующие шаги:**
- Добавить управление периодичностью/напоминаниями в UI (форма редактирования событий и шаблоны) и генерацию напоминаний по `reminder_days`.
- Синхронизировать календарь с внешними уведомлениями (email/чат) и отразить шаблоны событий в админке справочников.
