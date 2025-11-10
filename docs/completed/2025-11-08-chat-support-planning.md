# Планирование чата и обратного звонка

**Контекст**
- Требование клиента №7 (чат-бот/обратный звонок) из `docs/requirements/` и roadmap.
- Этап «Коммуникации» в `docs/documentation-plan.md` и `docs/documentation-todo.md`.

**Что сделано**
1. Создан документ `docs/features/chat-support.md` с целями, моделью данных (`chat_session`, `chat_message`, `callback_request`, `user_telegram_identity`), API, UX и инструкциями для ассистентов.
2. Обновлён `docs/architecture/integration-plan.md`: добавлен сервис `chat-service`, события `chat.*`, детализация Telegram support-bot и TODO по интеграции.
3. Таблица `docs/documentation-todo.md` дополнена отдельными задачами по веб-чату, support-bot и мониторингу.

**Артефакты**
- `docs/features/chat-support.md`
- `docs/architecture/integration-plan.md`
- `docs/documentation-todo.md`

**Следующие шаги**
- Согласовать провайдера обратного звонка и стек Telegram-бота, после чего перейти к миграциям/реализации (`chat-service`, docker-сервис support-bot, UI-виджет).
- Дополнить `docs/infra/runbook.md` разделом по мониторингу чата (метрики SLA, алерты webhook) и зафиксировать это в отдельной completed-записи после выполнения.
