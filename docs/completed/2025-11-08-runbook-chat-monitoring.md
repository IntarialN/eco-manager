# Runbook: чат и мониторинг

**Контекст**
- Задача из `docs/documentation-todo.md` (DevOps/Monitoring) и раздел 8 в `docs/features/chat-support.md`.
- После реализации ChatService нужно описать процедуру запуска/проверки для разработчиков и DevOps.

**Что сделано**
1. В `docs/infra/runbook.md` добавлен раздел «8. Чат/обратный звонок»: миграции, curl-примеры API, инструкции по логам и SLA.
2. Зафиксированы требования к будущему `support-bot` (переменные окружения, webhook) и метрикам (first reply, открытые сессии, webhook-ошибки).
3. Таблица `docs/documentation-todo.md` обновлена: статус задачи переведён в «В процессе (док)» с указанием следующих шагов.

**Артефакты**
- `docs/infra/runbook.md`
- `docs/documentation-todo.md`

**Следующие шаги**
- После реализации Telegram-бота дополнить runbook: деплой контейнера `support-bot`, переменные (`SUPPORT_BOT_TOKEN`, `SUPPORT_BOT_WEBHOOK`) и процедуры перезапуска.
- Добавить раздел об наблюдении метрик (Prometheus/Grafana) и обновить статус TODO на «готово».
