# Структура БД eco-manager

Документ описывает актуальные таблицы (по состоянию на ветку `feature/register-client-select`) и запланированные сущности, которые ещё не реализованы, но нужны для целостной платформы.

## 1. Клиенты и площадки
| Таблица | Поля | Назначение |
|---------|------|------------|
| `client` | `id`, `name`, `registration_number`, `category`, `description`, `annual_emissions_tons`, `annual_waste_kg`, `hazardous_waste_present`, `hazardous_substances_class`, `has_well`, `uses_surface_water`, `livestock_byproducts`, `responsible_person_trained`, `responsible_person_count`, `instruction_docs_required`, `water_source`, `training_valid_until`, `created_at`, `updated_at` | Профиль компании клиента и экологические атрибуты, определяющие объём требований и рисков. |
| `site` | `id`, `client_id`, `name`, `address`, `emission_category` | Производственные площадки клиента, чтобы привязывать требования и события к конкретному объекту. |
| `user_client_assignment` | `user_id`, `client_id` | Many-to-Many между менеджерами и клиентами. |

### Планируется
- `client_contact` — отдельная таблица контактных лиц (email/телефон/роль) для уведомлений и эскалаций.

## 2. Пользователи и доступ
| Таблица | Поля | Назначение |
|---------|------|------------|
| `user` | `id`, `client_id`, `username`, `email`, `role`, `password_hash`, `auth_key`, `is_active`, `email_confirm_token`, `email_confirmed_at`, `access_token`, `last_login_at`, `created_at`, `updated_at` | Учетные записи сотрудников EcoManager и клиентов. |

### Планируется
- `user_role_history` — история смен ролей и статусов для аудита RBAC.

## 3. Требования и документы
| Таблица | Поля | Назначение |
|---------|------|------------|
| `requirement` | `id`, `client_id`, `site_id`, `code`, `title`, `status`, `due_date`, `completed_at`, `category` | Регуляторные требования; основной бизнес-процесс. |
| `requirement_history` | `id`, `requirement_id`, `user_id`, `old_status`, `new_status`, `comment`, `created_at` | Хронология изменений. |
| `document` | `id`, `client_id`, `requirement_id`, `title`, `type`, `status`, `review_mode`, `path`, `uploaded_at`, `auditor_id`, `audit_comment`, `audit_completed_at` | Файлы, подтверждающие выполнение требований, с режимами проверки. |

### Планируется
- `document_version` | `document_id`, `version`, `path`, `uploaded_by`, `uploaded_at` — хранить все версии файла.
- `document_access_log` | `document_id`, `user_id`, `action`, `timestamp`, `ip` — аудит скачиваний/удалений.

## 4. Календарь и события
| Таблица | Поля | Назначение |
|---------|------|------------|
| `calendar_event` | `id`, `client_id`, `requirement_id`, `title`, `type`, `status`, `due_date`, `start_date`, `periodicity`, `custom_interval_days`, `reminder_days`, `completed_at` | Дедлайны и инспекции; поддерживает повторяющиеся события. |

### Планируется
- `calendar_notification` | `event_id`, `channel`, `recipient`, `status`, `sent_at` — лог уведомлений, чтобы понимать, кому ушло напоминание.

## 5. Риски и планы действий
| Таблица | Поля | Назначение |
|---------|------|------------|
| `risk` | `id`, `client_id`, `site_id`, `requirement_id`, `title`, `description`, `legal_reference`, `severity`, `probability`, `status`, `due_date`, `responsible_user_id`, `loss_min`, `loss_max`, `detected_at`, `resolved_at` | Оценённые риски нарушения законодательства. |
| `risk_action_plan` | `id`, `risk_id`, `task`, `owner_id`, `status`, `due_date`, `created_at`, `updated_at` | Чеклист задач по риску. |
| `risk_log` | `id`, `risk_id`, `user_id`, `action`, `notes`, `created_at` | Журнал действий. |

### Планируется
- `risk_metric_snapshot` — агрегаты (score, SLA, количество просроченных задач) для аналитики дашбордов.

## 6. Биллинг и интеграции
| Таблица | Поля | Назначение |
|---------|------|------------|
| `contract` | `id`, `client_id`, `number`, `title`, `status`, `amount`, `currency`, `signed_at`, `valid_from`, `valid_until`, `client_external_id`, `integration_id`, `integration_revision` | Договоры из Bubble/ERP. |
| `invoice` | `id`, `contract_id`, `number`, `status`, `amount`, `currency`, `issued_at`, `paid_at`, `due_date`, `integration_id` | Счета, по которым формируются финансовые риски. |
| `act` | `id`, `contract_id`, `invoice_id`, `number`, `status`, `issued_at`, `integration_id`, `integration_revision` | Акты выполненных работ. |

### Планируется
- `payment` — платежи, пришедшие из банка/ERP, чтобы сопоставлять с инвойсами.
- `integration_job` — состояние синхронизаций с внешними API (Bubble, будущий Росприроднадзор).

## 7. Коммуникации и чат поддержка
| Таблица | Поля | Назначение |
|---------|------|------------|
| `chat_session` | `id`, `client_id`, `external_contact`, `status`, `priority`, `assigned_user_id`, `channel`, `created_at`, `updated_at`, `assigned_seen_at` | Обращения клиента (чат, сайт, callback). |
| `chat_message` | `id`, `session_id`, `sender_type`, `sender_id`, `content`, `attachments`, `created_at` | Сообщения в рамках сессии. |
| `callback_request` | `id`, `session_id`, `status`, `requested_at`, `processed_at`, `operator_id`, `notes` | Запросы на обратный звонок. |
| `user_telegram_identity` | `id`, `user_id`, `telegram_user_id`, `username`, `linked_at` | Связка операторов с Telegram-ботом. |

### Планируется
- `chat_event` — лог автоматических событий ( переключение очередей, SLA-таймеры).
- `chat_attachment` — таблица файлов (ссылки, типы, размер) для сообщений.

## 8. Прочие служебные сущности (TODO)
- `notification_queue` — универсальная очередь для email/SMS/мессенджеров (канал, payload, состояние).
- `audit_log` — глобальный аудит действий пользователей (CRUD по ключевым объектам).
- `feature_toggle` — хранить флаги включения/выключения экспериментальных функций.
- `task_scheduler` — конфигурация cron-задач (типы, периодичность, последний запуск).

## 9. Связи высокого уровня
- `client` ← `site`, `requirement`, `document`, `calendar_event`, `risk`, `contract`, `chat_session`.
- `requirement` ↔ `document`, `calendar_event`, `risk`.
- `risk` ↔ `risk_action_plan`, `risk_log`.
- `contract` ↔ `invoice` ↔ `act`.
- `chat_session` ↔ `chat_message`, `callback_request`.
- `user` ↔ `user_client_assignment`, `risk_action_plan`, `chat_session` (назначения), `document` (аудиторы).

Все новые таблицы следует учитывать в миграциях (`migrations/`), а связи документировать в этом файле при реализации.
