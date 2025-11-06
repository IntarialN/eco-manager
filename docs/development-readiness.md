# Готовность к разработке (аудит документации)

Документ фиксирует текущее состояние проектной документации и подтверждает, что контур MVP описан и реализован в виде прототипа на Yii2.

## 1. Покрытие документацией

| Область | Документы | Статус |
|---------|-----------|--------|
| Требования и правила | `docs/requirements/catalog.md`, `docs/requirements/rules.md` | Описано и подтверждено (в т.ч. `req_11`). |
| Клиентская анкета и профилирование | `docs/client/onboarding-form.md`, `docs/client/profile-logic.md` | Описано |
| Функциональные блоки | `docs/features/*.md` | Описано, реализовано во view Yii2 |
| Роли и справочники | `docs/admin/roles-and-permissions.md`, `docs/admin/reference-management.md` | Описано, таблицы загружены в миграции |
| Безопасность | `docs/security/compliance.md` | Описано, ожидает подтверждения DPO |
| Архитектура | `docs/architecture/code-structure.md`, `docs/architecture/overview.md`, `docs/architecture/adr/0003-switch-to-yii2.md` | Описано, отражает текущий стек |
| Дорожная карта | `docs/project-roadmap.md` | Описано |
| Управление знаниями | `docs/completed/README.md`, `docs/documentation-todo.md` | Описано |

## 2. Проверка этапов документационного плана

| Этап | Completed-файл | Статус |
|------|----------------|--------|
| Этап 1 — карта требований | `docs/completed/2025-11-05-requirements-stage1.md`, `2025-11-05-requirement11-confirmed.md` | Завершено |
| Этап 2 — анкета и профилирование | `docs/completed/2025-11-05-client-onboarding-stage2.md` | Завершено |
| Этап 3 — функциональные блоки | `docs/completed/2025-11-05-features-stage3.md`, `2025-11-05-risk-assumptions.md` | Завершено |
| Этап 4 — админ-функционал | `docs/completed/2025-11-05-admin-stage4.md`, `2025-11-05-role-extensions.md` | Завершено |
| Этап 5 — безопасность | `docs/completed/2025-11-05-security-stage5.md`, `2025-11-05-security-updates.md` | Завершено (ожидает подтверждения DPO) |
| Этап 6 — архитектура | `docs/completed/2025-11-05-architecture-stage6.md`, `2025-11-06-yii-scaffold.md` (добавить) | Прототип реализован |
| Этап 7 — дорожная карта | `docs/completed/2025-11-05-project-roadmap-stage7.md` | Завершено |
| Этап 8 — управление знаниями | `docs/completed/2025-11-05-documentation-audit-stage8.md` | Завершено |

## 3. Открытые вопросы

- TODO-таблица в `docs/documentation-todo.md` содержит пункты, зависящие от верификации заказчика (НПА, штрафы, сертификация провайдера).
- Для production потребуется внедрить авторизацию, разграничение доступа и реальные загрузки документов.
- Интеграция с Bubble API реализуется после получения реальной спецификации (см. `docs/architecture/mock-bubble-api.md`).

## 4. Рекомендации перед деплоем

1. Запустить `composer install`, `php yii migrate` и убедиться, что демо-данные доступны.
2. Настроить веб-сервер (Nginx/Apache) на `yii-app/web`, включить HTTPS и защиту `uploads`.
3. Подтвердить УЗ ПДн и сертификацию хранилища с DPO-заказчика.
4. Добавить авторизацию/ролей (RBAC) согласно `docs/admin/roles-and-permissions.md`.

## 5. Статус готовности

- Прототип (UI + backend) готов и покрывает ключевые блоки MVP.
- Документация синхронизирована с текущим стеком.
- Требуется внешняя верификация и последующий деплой на сервер.
