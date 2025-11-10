# План realtime-канала чата

**Контекст**
- Клиенту нужен оперативный обмен сообщениями без ручного обновления страниц и гарантированная доставка оператору.
- Ссылаемся на `docs/features/chat-support.md`, `docs/architecture/integration-plan.md`, задачу в `docs/documentation-todo.md`.

**Что сделано**
1. Подготовлен дизайн `docs/architecture/chat-realtime.md`: описаны цели, компоненты (gateway + dispatcher + Redis/RabbitMQ), протокол WebSocket/SSE и план миграции.
2. В `docs/architecture/integration-plan.md` добавлен раздел про `chat-channel-gateway` и описание взаимодействия с web-чатом/ботом.
3. В `docs/documentation-todo.md` добавлен отдельный пункт про внедрение realtime-канала.

**Следующие шаги**
- Добавить Redis + сервисы `channel-gateway`/`channel-dispatcher` в docker-compose и runbook.
- Реализовать выдачу channel-токенов и публикацию событий в `chat-service`.
- Перевести фронтенд/операторский UI на WebSocket, оставить polling как fallback.
