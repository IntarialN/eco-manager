# Backend чата и обратного звонка

**Контекст**
- Продолжение задачи «Коммуникации» (см. `docs/features/chat-support.md`, `docs/documentation-todo.md`).
- Требование клиента №7: онлайн-чат + запрос обратного звонка, оператор отвечает через Telegram.

**Что сделано**
1. Добавлены миграции `m230000_000010_create_chat_tables` для таблиц `chat_session`, `chat_message`, `callback_request`, `user_telegram_identity` с индексами и совместимостью SQLite.
2. Созданы AR-модели и формы (`ChatSession`, `ChatMessage`, `CallbackRequest`, `UserTelegramIdentity`, формы в `app/models/forms/*`).
3. Реализован `ChatService` + DI в web/console/test конфигах, NotificationService научен отправлять письмо по событию `chat.callback.requested`.
4. Новый `ChatController` (REST): `POST /chat/session`, `POST /chat/<id>/message`, `POST /chat/<id>/callback`, `GET /chat/<id>` + маршруты в `urlManager`.
5. Покрыто unit-тестами (`tests/unit/ChatServiceTest.php` в docker/phpunit). Все существующие тесты проходят.

**Артефакты**
- Код: `yii-app/migrations/m230000_000010_create_chat_tables.php`, `yii-app/models/*`, `yii-app/services/ChatService.php`, `yii-app/controllers/ChatController.php`, `yii-app/components/NotificationService.php`, конфиги.
- Тесты: `yii-app/tests/unit/ChatServiceTest.php` (docker compose run --rm yii-app ./vendor/bin/phpunit tests/unit).
- Документация: обновлён `docs/documentation-todo.md` (статус по чату).

**Следующие шаги**
- Реализовать фронтовый виджет и вход для Telegram-операторов (support-bot, docker-сервис, мониторинг).
- Расширить NotificationService интеграцией с реальными каналами (email/бот) после выбора провайдера.
