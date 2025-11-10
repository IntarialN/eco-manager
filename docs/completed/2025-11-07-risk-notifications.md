**Контекст:** Этап 3 `docs/documentation-plan.md` — развитие блока «Риски» (уведомления и аудит).

**Что сделано:**
- Добавлен компонент `NotificationService` и конфигурация приложения; уведомления о задачах/статусах риска отправляются через единый сервис.
- `RiskController` теперь пишет в `RiskLog`, обновляет статус риска и инициирует события уведомлений при создании задач и смене статусов.
- Страница риска отображает историю операций; юнит-тесты проверяют журнал и отправку событий.

**Артефакты:**
- `yii-app/components/NotificationService.php`, `yii-app/config/web.php`
- `yii-app/controllers/RiskController.php`
- `yii-app/models/Risk.php`, `yii-app/models/RiskLog.php`, `yii-app/models/forms/RiskActionPlanForm.php`
- `yii-app/views/risk/view.php`, `yii-app/views/client/_tab_risks.php`
- `yii-app/tests/unit/ControllerTestCase.php`, `yii-app/tests/support/NotificationServiceStub.php`, `yii-app/tests/unit/RiskActionPlanControllerTest.php`
- `docs/features/risk-management.md`, `docs/documentation-todo.md`

**Следующие шаги:** подключить фактические каналы доставки (email/чат-бот) и расширить эскалации/уведомления для руководства.
