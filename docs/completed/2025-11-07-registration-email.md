**Контекст:** Этап 3 `docs/documentation-plan.md` — улучшение аутентификации для MVP.

**Что сделано:**
- Добавлена регистрация по email с выбором роли, проверкой пароля и автоматическим входом.
- Обновлены формы входа/регистрации, разрешён вход по email, добавлена ссылка на регистрацию.
- Реализован `RegisterForm`, view `site/register`, юнит-тест `RegisterFormTest`.

**Артефакты:**
- `yii-app/models/RegisterForm.php`, `yii-app/models/LoginForm.php`
- `yii-app/controllers/SiteController.php`
- `yii-app/views/site/login.php`, `yii-app/views/site/register.php`
- `yii-app/tests/unit/RegisterFormTest.php`
- `docs/documentation-todo.md`

**Следующие шаги:** подключить подтверждение email и ограничить выбор ролей для production-среды.
