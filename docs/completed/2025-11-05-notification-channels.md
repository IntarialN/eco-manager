**Контекст:** Задача из `docs/documentation-todo.md` по настройке каналов уведомлений и SLA.

**Что сделано:** Обновлён `docs/architecture/integration-plan.md` — зафиксирован порядок каналов (email → чат-бот → SMS), SLA, fallback-логика и профиль уведомлений по типам событий. Таблица TODO переведена в статус «Готово».

**Артефакты:** `docs/architecture/integration-plan.md`, `docs/documentation-todo.md`.

**Следующие шаги:** При внедрении notification-service учесть пользовательские настройки каналов и добавить мониторинг отправок (таблица `notification_attempts`).
