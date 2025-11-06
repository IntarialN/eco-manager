# Runbook разработки и деплоя (Yii2)

## 1. Предварительные требования
- PHP 8.1+
- Composer 2+
- SQLite (по умолчанию) или PostgreSQL/MySQL при переходе в продакшн
- Git, доступ к репозиторию (`main`, `develop`, `feature/*`)

## 2. Локальный запуск
1. Перейдите в каталог `yii-app/` и установите зависимости:
   ```bash
   cd yii-app
   composer install
   ```
2. Примените миграции (создастся `data/eco_manager.db` с демо-данными):
   ```bash
   php yii migrate
   ```
3. Запустите встроенный сервер Yii2:
   ```bash
   php yii serve --docroot=@app/web --port=8080
   ```
4. Откройте [http://localhost:8080](http://localhost:8080) — будет показан личный кабинет клиента с вкладками.

## 3. Структура проекта
- `controllers/ClientController.php` — основной экран личного кабинета.
- `models/` — ActiveRecord-модели (`Client`, `Requirement`, `Document`, `CalendarEvent`, `Risk`, `Contract`, `Invoice`, `Act`).
- `migrations/` — схема БД + примерные данные.
- `views/client/` — вкладки UI (карта требований, артефакты, календарь, риски, биллинг).
- `web/uploads/` — директория для документов (можно положить реальные файлы).

## 4. Git workflow
1. Ответвляемся от `develop` (`feature/<task>`).
2. Реализуем задачу, запускаем `php yii migrate` (при необходимости) и `php yii serve` для проверки.
3. Перед коммитом — `composer validate` (опц.) и убедиться, что миграции откатываются (`php yii migrate/down`).
4. Создаём PR в `develop`; после ревью — merge → `develop` → `main`.

## 5. Тестирование и миграции
- Юнит-тесты пока не настроены (TODO подключить Codeception/PHPUnit).
- Миграции: `php yii migrate` / `php yii migrate/down 1`.
- Для seed-данных используется текущая миграция `m230000_000000_init_schema`.

## 6. Деплой
- **Dev/stage:** можно использовать встроенный сервер или Docker-контейнер (PHP-FPM + Nginx); обновить `db.php` на PostgreSQL/MySQL.
- **Prod:**
  1. Настроить виртуальный хост на `yii-app/web`.
  2. Сконфигурировать БД, задать переменные окружения (например, через `.env`).
  3. Выполнить `composer install --no-dev`, `php yii migrate --interactive=0`.
  4. Настроить регулярные бэкапы БД (pg_dump / mysqldump) и каталога `uploads`.

## 7. Интеграции и TODO
- Mock Bubble API описан в `docs/architecture/mock-bubble-api.md`; интеграция будет подключена после получения реального API.
- Требуется внедрить RBAC (`docs/admin/roles-and-permissions.md`) и защиту `web/uploads`.
- Подготовить CI/CD (GitHub Actions) после согласования с заказчиком.
