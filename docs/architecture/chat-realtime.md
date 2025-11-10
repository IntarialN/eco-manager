# Chat Realtime Service

Документ описывает, как перейти от «polling каждые 5 секунд» к потоковой доставке сообщений и уведомлений для операторов и клиентов.

## 1. Цели
- Мгновенно доставлять новые сообщения в веб-виджет клиента и в операторский интерфейс без перезагрузки.
- Избавиться от дублирующих запросов и уменьшить нагрузку на `chat-service`.
- Подготовить единый канал для Telegram-бота/мобильных клиентов.

## 2. Общая архитектура
```
[Client widget]──WS/SSE──┐
                          │
[Operator UI]────WS/SSE───┼──▶ chat-channel-gateway (NestJS, ws)
                          │          │
[Telegram bot]────────────┘          ▼
                               Redis pub/sub
                                    │
                        chat-channel-dispatcher (worker)
                                    │
                              REST chat-service
```
- **chat-channel-gateway** — отдельный сервис (Node.js/NestJS + `@nestjs/websockets` или Go/Fiber), принимает WebSocket/SSE подключения, проверяет токен (JWT от `auth-service` для операторов, session-token для клиентов).
- **Redis pub/sub (или RabbitMQ fanout)** — мгновенная пересылка событий `chat.message.sent`, `chat.session.updated`. Используем существующий брокер RabbitMQ, но для простоты MVP можно начать с Redis (поддерживает `PUBLISH/SUBSCRIBE`, TTL на ключи).
- **chat-channel-dispatcher** — worker, который слушает базу/очередь `chat.message.sent` (уже предусмотрено). После сохранения сообщения в `chat-service` событие публикуется в очередь, а dispatcher пушит payload в gateway.
- **chat-service REST** остаётся источником истины (хранит историю, статусы, обратные звонки).

## 3. Протокол WebSocket
- URL: `wss://<domain>/channel/ws?session=<id>&token=<signature>` для клиента, `token` — HMAC от `session_id + created_at`, выдаётся при `POST /chat/session`.
- Для операторов: `wss://<domain>/channel/ws?type=staff&access_token=<jwt>`.
- Формат сообщений (JSON):
```json
{
  "type": "chat.message",
  "session_id": 123,
  "data": {
    "id": 456,
    "sender_type": "client",
    "body": "…",
    "created_at": "2025-11-08T10:30:11+03:00"
  }
}
```
- Дополнительно: `chat.session.assigned`, `chat.session.status`, `chat.alert`. Клиенты подтверждают доставку (`{"type":"ack","last_id":456}`), чтобы обновлять `lastSeen`.

## 4. Изменения в `chat-service`
1. Добавить таблицу `chat_channel_token` (session_id, token, expires_at) или генерировать HMAC на лету из `session_id` + секрет в параметрах.
2. После `postMessage` публиковать событие в RabbitMQ `chat.message.sent` с полезной нагрузкой + списком подписчиков (session_id, assigned_user_id).
3. Экспорт endpoint `GET /chat/session/<id>/channel-token` (для уже существующих чатов) и webhook `chat/alerts` расширить атрибутами `unread_count`.

## 5. Gateway/Dispatcher задачи
- **Gateway**
  - HTTP endpoint `/healthz`.
  - Поддержка fallback SSE (`/channel/sse`).
  - Жизненный цикл подключения: authenticate → subscribe (session_id/staff) → ping/pong каждые 30 сек.
  - Backpressure: ограничить >10 соединений на token.
- **Dispatcher**
  - Подписка на RabbitMQ `chat.message.sent`, `chat.session.updated`.
  - Для каждого события публикует в Redis канал `chat:<session_id>` и `chat:staff`.
  - Следит за TTL `last_seen` (хранится в Redis Hash, обновляется при ack).

## 6. Изменения в UI
- Клиентский виджет: после получения channel-token открывает WebSocket, подписывается на `chat.message`. При отключении возвращается к polling.
- Операторский UI: получает события `chat.session.created`, `chat.message.client`. Badge «Чаты» обновляется из real-time канала, polling становится резервным.
- Telegram-бот: вместо polling REST подписывается на `chat:staff` канал через Redis/XRead (в следующих итерациях).

## 7. План внедрения
1. **Инфраструктура**: добавить Redis в docker-compose, описать в runbook (`redis` используется также для кэша). Создать сервис `channel-gateway` (NestJS) и `channel-dispatcher` (Node worker или PHP daemon).
2. **chat-service**: добавить генерацию токена (секрет в `params.php`), события RabbitMQ и endpoint для получения токена существующим клиентам.
3. **Frontend**: реализовать WebSocket клиент (fallback SSE) в `layouts/main.php`, подключить ack/lastSeen. Операторский UI тоже переключить на WebSocket.
4. **Monitoring**: показатели `active_connections`, `dispatch_errors`, `ws_ping_latency`, алерты по недоступности Redis/gateway.
5. **Документация**: обновить `docs/infra/runbook.md`, `docs/documentation-todo.md`, `docs/completed/`.

## 8. Открытые вопросы
- Нужно ли писать историчные сообщения в Redis для быстрой выдачи? Пока нет — история берётся из REST.
- Ограничения по соединениям? MVP: 5 одновременных вкладок клиента, 20 операторов.
- Выбор стека: NestJS (совместимо с планом из ADR 0001) или Go. Решить после оценки команды.

## 9. Безопасность
- Все токены живут не более 24 часов, HMAC на стороне сервера.
- Для SSE можно использовать подписанную ссылку `chat/<session_id>/stream?sig=...`.
- Redis размещаем в приватной сети, доступ только у gateway/dispatcher.
