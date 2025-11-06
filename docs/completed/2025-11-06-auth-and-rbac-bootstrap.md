**Контекст:** Этап 2 `docs/project-roadmap.md` (архитектурный каркас) — добавление аутентификации и базовой поддержки ролей в Yii2 прототипе.

**Что сделано:**
- Реализована таблица `user` с привязкой к клиенту, ролью и демо-аккаунтами (`m230000_000001_create_user_table`).
- Добавлен `User` как `IdentityInterface`, модель `LoginForm`, экран входа и навигация с отображением пользователя/роли.
- Контроллеры обновлены для проверки доступа (обязательный логин, ограничение на просмотр клиентского кабинета для `client_user`).
- Runbook и документ по ролям дополнены разделами с демо-учётными записями и инструкциями по использованию.

**Артефакты:**
- `yii-app/migrations/m230000_000001_create_user_table.php`
- `yii-app/models/User.php`, `yii-app/models/LoginForm.php`
- `yii-app/controllers/SiteController.php`, `yii-app/controllers/ClientController.php`
- `yii-app/views/site/login.php`, `yii-app/views/layouts/main.php`
- `yii-app/config/web.php`
- `docs/infra/runbook.md`, `docs/admin/roles-and-permissions.md`

**Следующие шаги:**
- Расширить RBAC (добавить матрицу разрешений, проверку действия по ролям).
- Настроить RLS на уровне запросов (фильтрация данных по `client_id` для менеджеров/специалистов).
- Добавить управление пользователями из админского интерфейса.
