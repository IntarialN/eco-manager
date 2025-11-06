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
