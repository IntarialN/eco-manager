**Контекст:** Задача из `docs/documentation-todo.md` по описанию интеграции с Bubble и внутреннего mock-сервиса.

**Что сделано:** Подготовлен документ `docs/architecture/mock-bubble-api.md` с контрактом (эндпоинты, DTO, статусы, вебхуки, cron-синхронизация). `docs/architecture/integration-plan.md` дополнен ссылкой на mock, статус задачи в `docs/documentation-todo.md` переведён в «Готово».

**Артефакты:** `docs/architecture/mock-bubble-api.md`, `docs/architecture/integration-plan.md`, `docs/documentation-todo.md`.

**Следующие шаги:** Реализовать mock-сервис в `services/mock-bubble/` и при появлении официальной спецификации обновить контракт и адаптер `billing-service`.
