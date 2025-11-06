**Контекст:** Этап 3 `docs/documentation-plan.md` — развитие блока «Риски» (обработка планов действий).

**Что сделано:**
- Реализован `RiskController` с детализацией риска и управлением планом действий (добавление задач, смена статуса).
- Созданы модели/миграции `RiskActionPlan` и `RiskLog`, автоматизирующие пересчёт статуса риска и журналирование всех операций.
- Обновлены карточки рисков в клиентском профиле (кнопка «Перейти») и добавлен юнит-тест жизненного цикла плана.

**Артефакты:**
- `yii-app/migrations/m230000_000004_create_risk_action_plan_table.php`
- `yii-app/models/Risk.php`, `yii-app/models/RiskActionPlan.php`, `yii-app/models/forms/RiskActionPlanForm.php`
- `yii-app/controllers/RiskController.php`
- `yii-app/views/risk/view.php`, `yii-app/views/client/_tab_risks.php`
- `yii-app/tests/unit/ControllerTestCase.php`, `yii-app/tests/unit/RiskActionPlanControllerTest.php`
- `docs/features/risk-management.md`, `docs/documentation-todo.md`

**Следующие шаги:** добавить `RiskLog`, уведомления при эскалации и связать задачи с документами подтверждения устранения.
