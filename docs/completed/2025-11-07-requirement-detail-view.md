**Контекст:** Этап 3 `docs/documentation-plan.md` — углубление блока «Карта требований».

**Что сделано:**
- Создан отдельный экран требования (`RequirementController::actionView`) с карточкой, историей и списком документов.
- Таблица требований получила ссылку «Открыть», чтобы переходить на детальный просмотр.
- Документ `docs/features/requirements-tracker.md` обновлён описанием нового сценария.

**Артефакты:**
- `yii-app/controllers/RequirementController.php`
- `yii-app/views/requirement/view.php`
- `yii-app/views/client/_tab_requirements.php`
- `docs/features/requirements-tracker.md`

**Следующие шаги:** добавить действия по документам (загрузка/одобрение) прямо из карточки, связать статус с календарём и уведомлениями, покрыть сценарий тестами.
