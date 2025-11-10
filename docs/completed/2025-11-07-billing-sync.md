**Контекст:** Этап 1 — блок «Договоры/Счета/Акты» и интеграция mock Bubble API.

**Что сделано:**
- Добавлены зависимости (`yiisoft/yii2-httpclient`), новые поля интеграции в сущностях договоров/счётов/актов (миграция `m230000_000009_extend_billing_entities`).
- Реализован клиент `BubbleApiClient`, сервис `BillingSyncService`, web-контроллер вебхуков и консольная команда `yii billing/sync` для pull-синхронизации.
- Синхронизация обновляет календарь и создаёт риски по просроченным счетам, рассылает уведомления через `NotificationService`.
- Добавлены настройки (`params.php`), новые компоненты в web/console-конфигурациях.
- Все unit-тесты проходят (docker compose run --rm yii-app ./vendor/bin/phpunit tests/unit).

**Затронутые файлы (ключевые):**
- `yii-app/migrations/m230000_000009_extend_billing_entities.php`
- `yii-app/components/BubbleApiClient.php`
- `yii-app/services/BillingSyncService.php`
- `yii-app/controllers/BillingController.php`
- `yii-app/commands/BillingController.php`
- `yii-app/models/{Contract,Invoice,Act}.php`
- `yii-app/config/{web,console}.php`, `params.php`
- `yii-app/tests/unit/ControllerTestCase.php`, Notification stubs

**Следующие шаги:**
- Подключить реальные webhook URL/cron по инфраструктурному runbook’у.
- Расширить e2e-тесты (`services/risk-service-e2e`) чтобы покрыть mock Bubble сценарии.
