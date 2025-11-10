**Контекст:** Этап 1 `docs/documentation-structure.md` — блок «Артефакты / Хранилище документов».

**Что сделано:**
- Переработан UX карточки требования: форма загрузки вынесена в отдельный блок «Добавить документ», появились статусные карточки, подсказки и подсвечивание режимов (`yii-app/views/requirement/view.php`, `yii-app/web/css/site.css`).
- Контроллер подгружает аудитора для документов, добавлены серверные фильтры истории (дата/статус/комментарий) и пагинация на 10 записей (`yii-app/controllers/RequirementController.php`).
- Документация дополнена описанием новой формы, подсказок и истории с фильтрами (`docs/features/document-storage.md`).

**Артефакты:**
- `docs/features/document-storage.md`
- `yii-app/controllers/RequirementController.php`
- `yii-app/views/requirement/view.php`
- `yii-app/web/css/site.css`

**Следующие шаги:** покрыть рефакторинг тестами (`RequirementDocumentActionsTest`), описать актуальные сценарии календаря/рисков по плану этапа 1 и зафиксировать workflow аудита в `docs/documentation-todo.md`.
