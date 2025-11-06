# Контекст
- `docs/documentation-plan.md`: блок «Инфраструктура / локальная среда»
- `docs/documentation-plan.md`: блок «Фронтенд MVP»

# Что сделано
- Обновлён Dockerfile: запуск приложения переведён на `yii serve`, добавлены composer-замки и настройка `assetManager` для корректной раздачи фронтенд-ассетов.
- Обновлена миграция `m230000_000000_init_schema` для поддержки SQLite (отложенное создание FK) и выполнен `yii migrate`.
- Добавлены зависимости (`twbs/bootstrap-icons`), пересобран `composer.lock`.
- Переработан клиентский дашборд (`views/client/view.php`) и стили (`web/css/site.css`) — реализован виджет метрик и вкладки MVP.
- Обновлён `docs/documentation-todo.md`: добавлены строки по окружению и UI, отмечены выполненные чек-листы.

# Артефакты
- `docker/php/Dockerfile`
- `yii-app/config/web.php`
- `yii-app/composer.{json,lock}`
- `yii-app/migrations/m230000_000000_init_schema.php`
- `yii-app/views/client/view.php`
- `yii-app/web/css/site.css`
- `docs/documentation-todo.md`

# Следующие шаги
- Создать ветку от `develop`, зафиксировать перечисленные изменения отдельным коммитом и оформить PR в `develop`.
- Начать реализацию блока авторизации/RBAC из `docs/admin/roles-and-permissions.md`.
