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
