# История чата и операторский интерфейс

**Контекст**
- Требование клиента: чат должен показывать историю сообщений и позволять модераторам брать диалоги в работу.
- Связанные документы: `docs/features/chat-support.md`, `docs/documentation-todo.md` (пункт «Веб-чат и обратный звонок»).

**Что сделано**
1. Добавлены страницы `/chat/inbox` и `/chat/thread/<id>` с закреплением чата и формой ответа (`yii-app/views/chat/*.php`, `ChatController::actionInbox/thread/assign/reply`).
2. В публичном layout (`yii-app/views/layouts/main.php`) чат-виджет теперь хранит sessionId в `localStorage`, подгружает историю (`GET /chat/{id}`), отображает сообщения и позволяет продолжать диалог; фронтенд переписан на JS + polling каждые 5 секунд.
3. Расширены стили (`yii-app/web/css/site.css`) для bubble-ленты, пустого состояния и админского интерфейса; навигация пополнена пунктом «Чаты» для ролей с доступом к клиентам.
4. Обновлена документация (`docs/features/chat-support.md`) с описанием истории/messages и операторского UI, обновлён TODO-статус.

**Артефакты**
- Код: `yii-app/controllers/ChatController.php`, `yii-app/views/chat/inbox.php`, `yii-app/views/chat/thread.php`, `yii-app/views/layouts/main.php`, `yii-app/web/css/site.css`, `yii-app/config/web.php`.
- Документация: `docs/features/chat-support.md`, `docs/documentation-todo.md`.

**Следующие шаги**
- Добавить realtime (SSE/WebSocket) и push-уведомления операторам.
- Реализовать Telegram support-bot и синхронизацию ответов между ботом, вебом и админским UI.
- Поддержать статусные метки (прочитан/отвечен) и SLA-метрики в runbook.
