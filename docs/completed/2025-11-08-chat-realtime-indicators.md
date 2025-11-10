# Realtime оповещения и подсказки чата

**Контекст**
- Продолжение задач по блокам «Коммуникации»/«Чат»: нужно видеть новые сообщения без перезагрузки и подсвечивать кнопку чата в шапке.
- Связанные артефакты: `docs/features/chat-support.md`, `docs/documentation-todo.md` (рядок “Веб-чат…”).

**Что сделано**
1. В `yii-app/controllers/ChatController.php` добавлен эндпоинт `/chat/alerts` (JSON) и доступ к нему ограничен ролями, которые могут управлять клиентами.
2. Публичный виджет (`yii-app/views/layouts/main.php` + `site.css`) теперь:
   - хранит `lastSeen` в `localStorage`, загружает историю в фоне и показывает подсказку «Новое сообщение», если чат закрыт;
   - подсвечивает пункт «Чаты» в шапке (badge с анимацией) при появлении новых обращений;
   - запускает фоновые опросы каждые 5 сек., поэтому сообщение отображается почти в реальном времени.
3. CSS дополнен стилями для всплывающей подсказки и индикатора в навигации (`chat-tip`, `chat-nav-indicator`), включая анимации.
4. Документация обновлена (`docs/features/chat-support.md`, `docs/documentation-todo.md`).

**Артефакты**
- `yii-app/controllers/ChatController.php`
- `yii-app/views/layouts/main.php`
- `yii-app/web/css/site.css`
- `docs/features/chat-support.md`
- `docs/documentation-todo.md`

**Следующие шаги**
- Перейти от polling к WebSocket/SSE, чтобы снизить задержки и нагрузку.
- Интегрировать Telegram support-bot и общие уведомления (email/бот).
- Добавить метрики по непрочитанным чатам в runbook и мониторинг SLA.
