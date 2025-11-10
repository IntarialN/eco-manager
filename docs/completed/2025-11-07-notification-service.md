**Контекст:** расширение уведомлений после интеграции mock Bubble API.

**Что сделано:**
- `NotificationService` теперь поддерживает письма/логи для рисков, счетов, документов и событий календаря; конфигурация (`params.php`) задаёт категории email-адресов.
- `BillingSyncService`, `RequirementController` и `syncCalendar` вызывают новые методы, чтобы операционная и финансовая команды получали уведомления.
- Конфиги web/console и NotificationServiceStub в тестах обновлены.
- Runbook дополнен инструкциями по запуску mock Bubble и `yii billing/sync`.

**Затронутые файлы:**
- `yii-app/components/NotificationService.php`
- `yii-app/controllers/RequirementController.php`
- `yii-app/services/BillingSyncService.php`
- `yii-app/config/{params,web,console}.php`
- `yii-app/tests/support/NotificationServiceStub.php`
- `docs/infra/runbook.md`

**Следующие шаги:** подключить реальные почтовые/чат-каналы и е2е-тесты mock Bubble → календарь/риски.
