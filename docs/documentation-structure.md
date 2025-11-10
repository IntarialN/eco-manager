# План структурирования документации (текущая сессия)

Документ фиксирует рабочие этапы по детализации и сопровождению документации MVP. Используем как чек-лист на время текущей сессии: каждый завершённый блок фиксируем в `docs/completed/` отдельной записью.

## Матрица файлов по этапам

| Этап | Основные файлы/папки | Цель |
|------|---------------------|------|
| Этап 0 — аудит входного контекста | `docs/README.md`, `docs/documentation-plan.md`, `docs/development-readiness.md`, `docs/documentation-todo.md`, `docs/completed/*.md` | Уточнить текущий статус, выявить пробелы, синхронизировать TODO. |
| Этап 1 — функциональные блоки | `docs/features/*.md`, `docs/features/chat-support.md`, `yii-app/controllers/ClientController.php`, `yii-app/controllers/RequirementController.php`, `yii-app/controllers/ChatController.php`, `yii-app/services/ChatService.php`, `yii-app/views/client/*.php`, `yii-app/views/chat/*.php`, `yii-app/views/layouts/main.php`, `yii-app/web/css/site.css`, `yii-app/tests/unit/*Requirement*.php`, `yii-app/tests/unit/RiskActionPlanControllerTest.php` | Согласовать описание UX, API и тестов по каждому блоку (требования, документы, календарь, риски, договоры, чат/обратный звонок). |
| Этап 2 — архитектура и интеграции | `docs/architecture/*.md`, `docs/architecture/adr/*.md`, `docs/architecture/diagrams/*.puml`, `docs/architecture/chat-realtime.md`, `services/risk-service-e2e/project.json`, `docker-compose.yml`, `package.json` | Поддерживать целевую архитектуру, диаграммы, планы интеграций (Bubble, уведомления, realtime). |
| Этап 3 — требования/справочники/анкета | `docs/requirements/*.md`, `docs/admin/*.md`, `docs/client/*.md`, `yii-app/models/forms/ClientIntakeForm.php`, `yii-app/models/Document.php`, `yii-app/migrations/*` | Актуализировать правила авторасчёта, справочники НПА, роли и клиентские анкеты. |
| Этап 4 — безопасность и инфраструктура | `docs/security/compliance.md`, `docs/infra/runbook.md`, `docs/project-git-workflow.md`, `yii-app/config/{web,console,db}.php`, `params.php`, `docker-compose.yml`, `composer.json`, `package.json` | Обновлять runbook, бэкапы, мониторинг, git-процессы и соответствие законодательству РФ. |
| Этап 5 — правила для ИИ | `docs/assistant-guidelines.md`, `docs/documentation-plan.md`, `docs/documentation-structure.md` | Задаём инструкции ассистентам, перечень обязательных документов перед стартом и регламент фиксации изменений. |
| Этап 6 — управление знаниями | `docs/documentation-todo.md`, `docs/completed/README.md`, `docs/completed/*.md` | Фиксируем прогресс, переносим TODO, поддерживаем целостную историю изменений. |

## Этап 0. Аудит входного контекста
- Проанализировать все материалы в `docs/`, включая completed-лог, чтобы не дублировать уже выполненные задачи.
- Свериться с `docs/documentation-plan.md`, `docs/documentation-todo.md`, `docs/development-readiness.md`.
- Зафиксировать выявленные пробелы или несоответствия в `docs/documentation-todo.md`.

## Этап 1. Функциональные блоки MVP
- **Требования (`docs/features/requirements-tracker.md`)**
  - Проверить актуальность бизнес-правил (особенно req_11) и ссылок на `docs/requirements/`.
  - Дополнить UX и SLA, если появляются новые сценарии.
- **Артефакты (`docs/features/document-storage.md`)**
  - Уточнить маппинг типов документов ↔ НПА.
  - Проверить политику версионирования и аудита (связь с `reference-management.md`).
- **Календарь (`docs/features/calendar.md`)**
  - Досформировать события по договорам/счетам при прогрессе по интеграции Bubble.
  - Синхронизировать напоминания с notification-service.
- **Риски (`docs/features/risk-management.md`)**
  - Уточнить диапазоны штрафов и частоту проверок при поступлении официальных данных.
  - Согласовать логику планов действий с `docs/admin/reference-management.md`.
- **Договоры/Биллинг (`docs/features/contracts-billing.md`)**
  - Актуализировать статусы интеграции, ссылки на mock Bubble API и календарные события.
- **Коммуникации и поддержка (`docs/features/chat-support.md`)**
  - Сверять описание веб-виджета, операторского UI (`yii-app/views/chat/*.php`, `yii-app/views/layouts/main.php`, `yii-app/web/css/site.css`) и сервиса (`yii-app/services/ChatService.php`, `yii-app/controllers/ChatController.php`) с текущим кодом.
  - Фиксировать переходы на realtime (`docs/architecture/chat-realtime.md`), cron-задачи (`ChatMaintenanceController`) и мониторинг/инструкции в runbook.
- Для каждого подблока: если вносим изменения, готовим запись в `docs/completed/` с указанием затронутых файлов и причин правок.

## Этап 2. Архитектура и интеграции
- **Codebase (`docs/architecture/code-structure.md`, `docs/architecture/overview.md`)**
  - Проверить, отражены ли последние изменения в Yii-прототипе и потенциальные микросервисы.
- **Модель данных и диаграммы (`docs/architecture/data-model.md`, `docs/architecture/diagrams/*.puml`)**
  - При изменении сущностей обновлять текст и диаграммы синхронно.
- **Интеграции (`docs/architecture/integration-plan.md`, `docs/architecture/mock-bubble-api.md`)**
  - Уточнять последовательности событий, SLA, форматы DTO.
  - Фиксировать новые интеграции (уведомления, внешние базы НПА).
- **ADR** — любые архитектурные решения документируем отдельным ADR (см. `docs/architecture/adr/`), обновляем индекс `docs/README.md`.

## Этап 3. Требования, справочники, админ-блок
- **Каталог и правила (`docs/requirements/*.md`)**
  - При поступлении новых нормативов обновлять таблицы, условия назначения.
- **Справочники (`docs/admin/reference-management.md`)**
  - Актуализировать НПА, типы документов, штрафы; вести журнал ручных корректировок.
- **Роли (`docs/admin/roles-and-permissions.md`)**
  - Проверять матрицу доступа при добавлении новых ролей или сценариев.
- **Клиентский профиль (`docs/client/*.md`)**
  - Расширять анкеты и маппинг при появлении дополнительных вопросов или интеграций.

## Этап 4. Безопасность, инфраструктура, runbook
- **Безопасность (`docs/security/compliance.md`)**
  - Подтверждать уровень защищённости, бэкапы, реагирование на инциденты.
- **Инфраструктура (`docs/infra/runbook.md`, ADR 0002)**
  - Обновлять инструкции по локальному окружению, CI/CD, деплою.
- **Git-процесс (`docs/project-git-workflow.md`)**
  - Следить за актуальностью веток, шаблонов PR, защитой веток.

## Этап 5. Поведение ИИ и правила работы
- Перед любым действием перечитывать `docs/documentation-plan.md`, `docs/documentation-structure.md`, `docs/documentation-todo.md` и последние записи в `docs/completed/`, чтобы не дублировать работу.
- Проверять и при необходимости расширять `docs/assistant-guidelines.md` (новые ограничения, процессы утверждения, шаблоны ответов).
- Фиксировать для себя список обязательных источников по задаче (например, перед работой над требованиями — `docs/requirements/*.md`, перед чатом — `docs/features/chat-support.md` + runbook).
- Напоминать себе:
  - Не переписывать историю — добавлять новые записи.
  - Обновлять `docs/README.md`, если появляются новые разделы.
  - Поддерживать внутренние ссылки между документами.

## Этап 6. Управление знаниями и фиксация результатов
- Перед завершением любой задачи:
  - Обновить профильные документы (фичи, архитектура, требования).
  - Синхронизировать статусы в `docs/documentation-todo.md`.
  - Создать запись в `docs/completed/` формата `YYYY-MM-DD-title.md` с контекстом, описанием и ссылками на артефакты.
- Регулярно (минимум раз за сессию) выполнять аудит:
  - `rg "TODO" docs` — перенос в таблицу TODO.
  - Проверка чек-листов в конце каждого файла (галочки отражают реальное состояние).

## Напоминания по фиксации прогресса
- Каждый этап или подэтап = отдельная запись в `docs/completed/` с перечислением изменённых файлов (код, документация, тесты) и ссылкой на ветку/коммит.
- Если задача затрагивает несколько этапов, допускается одна запись, но обязательно перечисляем все подпункты и файлы, чтобы не терять контекст.
- При обновлении нескольких документов одной темой (например, интеграции или чат) ссылаться на все затронутые файлы и отражать изменения в `docs/documentation-todo.md`.
- После мёрджа изменений обновлять completed-лог, при необходимости индекс `docs/README.md`, и проверять, что TODO-таблица показывает актуальные статусы.
