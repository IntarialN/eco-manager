**Контекст:** Задача из `docs/documentation-todo.md` — конфликт-менеджмент ручных overrides требований.

**Что сделано:** В `docs/admin/reference-management.md` добавлен алгоритм обработки ручных overrides при обновлении правил (preview-расчёт, приоритет `EXCLUDE/INCLUDE`, контроль `expires_at`, аудит изменений). Статус задачи в `docs/documentation-todo.md` переключён на «Готово».

**Артефакты:** `docs/admin/reference-management.md`, `docs/documentation-todo.md`.

**Следующие шаги:** При реализации requirements-service внедрить логику overrides, обеспечить тесты на сценарии `EXCLUDE/INCLUDE` и истечение `expires_at`.
