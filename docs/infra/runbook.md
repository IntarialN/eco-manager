# Runbook разработки и деплоя (Yii2)

## 1. Предварительные требования
- Docker/ Docker Compose
- Git
- (Для локального запуска без Docker) PHP 8.1+, Composer 2+

## 2. Быстрый старт через Docker
```bash
make up           # поднимет php-fpm, postgres и adminer
make migrate      # выполнит миграции внутри контейнера
```
После запуска приложение доступно на [http://localhost:8080](http://localhost:8080).

### Контейнеры
- `yii-app`: php-fpm + composer, стартует `php yii serve --port=8080`
- `db`: PostgreSQL 15 (`eco_manager/eco_user/eco_password`)
- `adminer`: административная панель на http://localhost:8081

> Если появились новые миграции (например, для ролей или требований), достаточно повторно выполнить `make migrate` — команда применит только недостающие изменения.

Логи: `docker compose logs -f yii-app`

## 3. Установка зависимостей без Docker
```bash
cd yii-app
composer install
php yii migrate
php yii serve --docroot=@app/web --port=8080
```

## 4. Git workflow
- `feature/<task>` → PR в `develop` → `main`
- Перед PR выполнить `make migrate` (или `php yii migrate`) и убедиться, что UI открывается.

## 5. Бэкапы и деплой
- Бэкапы PostgreSQL: `pg_dump eco_manager > backup.sql`
- `uploads` хранить в отдельном volume/облаке
- Для production настроить Nginx + PHP-FPM, обновить `config/db.php` на рабочую СУБД.

## 6. TODO
- Интеграция Bubble API после получения спецификации.
- Подключение RBAC/авторизации.
- CI (GitHub Actions) для `composer install`, `php yii migrate`, статического анализа.

## 7. Mock Bubble API и синхронизация биллинга
1. Поднять mock-службу:
```bash
docker compose up -d mock-bubble
```
Mock доступен на `http://localhost:4001/api`, ключ `X-API-Key: demo-key`.
2. Выполнить синхронизацию договоров/счетов/актов:
```bash
docker compose run --rm yii-app php yii billing/sync
```
Команда подтянет данные и обновит календарь/риски, а уведомления попадут в лог/почту (см. `params.php`).
3. Для автоматизации добавить cron либо GitHub Actions, вызывающие `yii billing/sync`.

## 8. Чат/обратный звонок (MVP)
1. **Миграции.** После синка стоить запустить `make migrate` (или `docker compose run --rm yii-app php yii migrate`), чтобы применились таблицы `chat_session`, `chat_message`, `callback_request`, `user_telegram_identity`.
2. **API проверки.**  
   - Создать сессию:  
     ```bash
     curl -X POST http://localhost:8080/chat/session \
       -H 'Content-Type: application/json' \
       -d '{"external_contact":"demo@example.com","name":"Демонстрация","initial_message":"Нужна консультация"}'
     ```
   - Отправить сообщение/запросить звонок: `POST /chat/<id>/message`, `POST /chat/<id>/callback`. Ответ приходит в JSON, а уведомление — на почту `notifications.support`.
3. **Мониторинг/логи.**
   - Web: см. `runtime/logs/app.log`; события (`chat_callback`) помечены категорией `NotificationService::sendChatCallbackRequest`.
   - БД: запрос `SELECT status, COUNT(*) FROM chat_session GROUP BY status;` показывает очередь обращений.
   - Для prod добавить метрики: время первого ответа (`chat_session.first_reply_seconds`), кол-во открытых сессий, ошибки webhook (после подключения Telegram-бота). Черновик метрик зафиксирован в `docs/features/chat-support.md`.
    - Пока используется polling (`GET /chat/<id>?since_id=...`) каждые ~5 секунд; после перевода на Centrifugo/WebSocket обновим этот пункт.
4. **Telegram support-bot (планы).** Docker-сервис будет добавлен как `support-bot`; токен хранить в `.env` (`SUPPORT_BOT_TOKEN`), webhook на `/bot/support`. До его реализации операторы отвечают через админку/почту.
5. **SLA.** По умолчанию контролируем: первый ответ ≤30 мин, обратный звонок ≤2 часа рабочего времени. При отклонении фиксировать запись в `chat_session` `status = pending_callback` и уведомлять менеджера.
6. **Архивация неактивных чатов.** Раз в 15–30 минут запускаем
   ```bash
   docker compose run --rm yii-app php yii chat-maintenance/archive --timeout=30
   ```
   Команда переводит сессии без сообщений старше таймаута в статус `closed`. При новом сообщении чат автоматически активируется (status → `open`).

## Связанные документы и регламент обновлений

- `docs/security/compliance.md` — требования по бэкапам, мониторингу и реагированию.
- `docs/architecture/code-structure.md` / `docs/architecture/overview.md` — структура приложения и компоненты, которые описаны в runbook.
- `docs/architecture/integration-plan.md` и `docs/architecture/mock-bubble-api.md` — интеграции и сценарии синхронизации.
- `docs/project-git-workflow.md` — правила работы с ветками и CI.
- `docs/documentation-todo.md`, `docs/completed/` — статусы задач по окружению и журнал изменений.

Перед обновлением runbook:
1. Проверить, отражены ли изменения в инфраструктуре, CI/CD или интеграциях (свериться с ADR и планами).
2. Обновить связанные документы и TODO-таблицу.
3. Зафиксировать результат в `docs/completed/`, указав, какие инструкции или команды изменились.

## 7. Демо-учётные записи

После запуска миграций доступны тестовые пользователи:

| Логин | Пароль | Роль | Описание доступа |
|-------|--------|------|------------------|
| `admin` | `Admin#2025` | admin | Полный доступ ко всем клиентам и разделам. |
| `manager` | `Manager#2025` | client_manager | Доступ к клиенту №1 и его данным. |
| `client` | `Client#2025` | client_user | Просмотр собственного кабинета клиента №1. |

Для входа откройте `/site/login`. После авторизации навбар отображает активного пользователя и роль; выход выполняется кнопкой «Выход».
