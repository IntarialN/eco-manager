**Контекст:** Этап 4 `docs/documentation-plan.md` — админ-функционал и управление пользователями.

**Что сделано:**
- Добавлен экран для администраторов (`/user/index`, `/user/update`) с возможностью назначать менеджерам и специалистам несколько клиентов через pivot-таблицу `user_client_assignment`.
- Реализована форма `UserAssignmentForm` с валидацией и массовым сохранением, а также модель `UserClientAssignment`.
- Навигация обновлена: при роли `admin` появляется пункт «Пользователи»; после обновления назначений данные сразу применяются.

**Артефакты:**
- `yii-app/controllers/UserController.php`
- `yii-app/models/forms/UserAssignmentForm.php`
- `yii-app/models/UserClientAssignment.php`
- `yii-app/migrations/m230000_000002_create_user_client_assignment_table.php`
- `yii-app/views/user/index.php`, `yii-app/views/user/update.php`
- `yii-app/views/layouts/main.php`
- `docs/admin/roles-and-permissions.md`

**Следующие шаги:** добавить журнал действий (кто изменил назначения), предусмотреть фильтрацию/поиск по пользователям и расширить автотесты на проверку доступа и UI.
