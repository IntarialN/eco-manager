**Контекст:** Этап 2 `docs/project-roadmap.md` — усиление ролевой модели и разграничения доступа.

**Что сделано:**
- Ограничен доступ к данным клиента на уровне контроллеров: `ClientController` использует метод `User::canAccessClient`, `SiteController` перенаправляет пользователя на «свой» `client_id`.
- Навигация в layout адаптируется под пользователя: ссылка «Клиент» ведёт к разрешённому клиенту, отображается актуальная роль.
- Модель `User` дополнена методами `canAccessClient()`, `getDefaultClientId()` и поддержкой множественных назначений (pivot `user_client_assignment`).
- Добавлены модель `UserClientAssignment` и миграция `m230000_000002_create_user_client_assignment_table.php` с примером назначения для демо-менеджера.
- Документ `docs/admin/roles-and-permissions.md` обновлён описанием привязки ролей и таблицы назначений.

**Артефакты:**
- `yii-app/models/User.php`
- `yii-app/models/UserClientAssignment.php`
- `yii-app/migrations/m230000_000002_create_user_client_assignment_table.php`
- `yii-app/controllers/ClientController.php`
- `yii-app/controllers/SiteController.php`
- `yii-app/views/layouts/main.php`
- `docs/admin/roles-and-permissions.md`

**Следующие шаги:** внедрить UI для управления назначениями клиентов (справочник + формы), покрыть проверки доступов юнит-тестами и расширить аудит пользовательских действий.
