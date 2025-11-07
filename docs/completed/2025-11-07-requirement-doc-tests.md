**Контекст:** Этап 3 `docs/documentation-plan.md` — блок «Артефакты / хранилище» (строка TODO «Документы | Управление файлами из карточки требования»).

**Что сделано:**
- Настроен общий тестовый каркас для `RequirementController` (in-memory SQLite, временное хранилище `uploads`).
- Добавлен unit-тест `RequirementDocumentActionsTest`, проверяющий загрузку, подтверждение и отклонение документов.
- Попутно импортирован `UploadedFile` и зафиксирована документация о новом покрытии.

- **Артефакты:**
- `yii-app/controllers/RequirementController.php`
- `yii-app/tests/unit/ControllerTestCase.php`
- `yii-app/tests/unit/RequirementDocumentActionsTest.php`
- `yii-app/tests/unit/RequirementStatusUpdateTest.php`
- `docs/documentation-todo.md`
- `docs/features/document-storage.md`

**Следующие шаги:** расширить клиентскую валидацию типов/размеров файлов и предусмотреть отдельные тесты на права доступа/аудит (см. TODO в документе по хранилищу).
