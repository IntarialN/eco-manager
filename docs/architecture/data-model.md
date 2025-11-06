# Модель данных (черновая)

Документ систематизирует основные сущности и связи MVP. После выбора конкретной СУБД необходимо уточнить типы полей и ограничения.

## 1. Сущности клиентов и пользователей

### `Client`
- `id` (UUID)
- `name`
- `inn`
- `ogrn`
- `group_name` (опционально)
- `created_at`, `updated_at`
- `manager_id` (ответственный менеджер)

### `Site`
- `id`
- `client_id` (FK `Client.id`)
- `name`
- `address`
- `emission_category` (enum: I, II, III, IV)
- `category_source_doc` (номер, дата)
- `has_stationary_sources` (bool)
- `hazardous_substances_class` (enum/null)

### `User`
- `id`
- `email`
- `hashed_password`
- `role` (enum: admin, client_manager, project_specialist, client_user, ...)
- `client_id` (nullable, для клиентских пользователей)
- `metadata` (jsonb)
- `last_login_at`
- `status` (active, blocked)

### `RoleAssignment`
- `user_id`
- `role`
- `scope` (например, client_id)
- Позволяет хранить дополнительные роли (финансовый менеджер и т.д.).

## 2. Требования и правила

### `Requirement`
- `id`
- `client_id`
- `site_id`
- `code` (req_01 …)
- `title`
- `group` (air, water, waste, training, financial)
- `status` (new, in_progress, completed, blocked, expired)
- `due_date`
- `renewal_period` (annual, quarterly, once)
- `responsible_user_id`
- `source_rule` (auto, manual_override)
- `needs_clarification` (bool)
- `created_at`, `updated_at`

### `RequirementHistory`
- `id`
- `requirement_id`
- `changed_by`
- `change_type` (status_update, comment, file_linked, override)
- `payload` (jsonb)
- `created_at`

### `RequirementRule`
- `id`
- `code`
- `description`
- `condition` (DSL/JSON)
- `source_npa`
- `version`
- `effective_from`
- `effective_to`
- `status`

### `RequirementOverride`
- `id`
- `requirement_id`
- `action` (added, excluded, reassigned)
- `initiator_id`
- `approved_by`
- `reason`
- `created_at`

## 3. Документы

### `Document`
- `id`
- `client_id`
- `site_id` (nullable)
- `requirement_id` (nullable)
- `contract_id` (nullable)
- `type_code`
- `title`
- `status` (pending_review, approved, rejected, archived)
- `requires_audit` (bool)
- `audit_status` (pending, in_progress, done)
- `file_url`
- `file_hash`
- `file_size`
- `uploaded_by`
- `uploaded_at`

### `DocumentVersion`
- `id`
- `document_id`
- `version_number`
- `file_url`
- `uploaded_by`
- `uploaded_at`

### `DocumentAccessLog`
- `id`
- `document_id`
- `user_id`
- `action` (view, download, delete)
- `timestamp`
- `ip_address`

## 4. Календарь

### `CalendarEvent`
- `id`
- `client_id`
- `site_id`
- `requirement_id` (nullable)
- `title`
- `description`
- `event_type` (reporting, license, audit, payment, training, custom)
- `periodicity` (once, monthly, quarterly, yearly, custom)
- `start_date`
- `due_date`
- `reminder_offsets` (array<int>)
- `status` (scheduled, in_progress, done, overdue, cancelled)
- `assigned_user_id`
- `created_at`, `updated_at`

### `EventReminder`
- `id`
- `event_id`
- `offset_days`
- `channel` (email, chat, sms)
- `sent_at`

### `EventLog`
- `id`
- `event_id`
- `action`
- `user_id`
- `notes`
- `timestamp`

## 5. Риски

### `Risk`
- `id`
- `client_id`
- `site_id`
- `source_type` (requirement, event, manual)
- `source_id`
- `description`
- `legal_reference`
- `fine_min`, `fine_max`
- `severity` (low, medium, high)
- `probability` (0–100)
- `score`
- `status` (open, mitigation, closed, escalated)
- `due_date`
- `responsible_user_id`
- `created_at`, `updated_at`

### `RiskActionPlan`
- `id`
- `risk_id`
- `task`
- `owner_id`
- `due_date`
- `status` (new, in_progress, done)
- `created_at`

### `RiskLog`
- `id`
- `risk_id`
- `action`
- `user_id`
- `details`
- `timestamp`

## 6. Финансы

### `Contract`
- `id`
- `client_id`
- `title`
- `number`
- `date_signed`
- `valid_until`
- `status` (draft, active, suspended, terminated)
- `total_amount`
- `currency`
- `integration_id`
- `created_at`, `updated_at`

### `Invoice`
- `id`
- `contract_id`
- `number`
- `issue_date`
- `due_date`
- `amount`
- `currency`
- `status` (issued, paid, overdue, cancelled)
- `payment_date`
- `integration_id`
- `created_at`, `updated_at`

### `Act`
- `id`
- `contract_id`
- `number`
- `issue_date`
- `status` (draft, pending_sign, signed, archived)
- `linked_invoice_id`
- `document_id` (ссылка на `Document`)
- `created_at`, `updated_at`

### `BillingLog`
- `id`
- `entity_type`
- `entity_id`
- `action`
- `details`
- `user_id`
- `timestamp`

## 7. Унифицированные справочники

### `ReferenceNPA`
- `id`
- `code`
- `title`
- `link`
- `category` (air, water, waste, etc.)
- `effective_from`
- `effective_to`
- `status`

### `DocumentType`
- `type_code`
- `name`
- `description`
- `storage_policy` (json)
- `related_requirements` (array)

### `NotificationTemplate`
- `id`
- `channel`
- `subject`
- `body`
- `variables`
- `status`

## 8. Аудит и безопасность

### `AuditLog`
- `id`
- `entity_type`
- `entity_id`
- `action`
- `user_id`
- `payload`
- `timestamp`
- `ip_address`

### `Session`
- `id`
- `user_id`
- `issued_at`
- `expires_at`
- `device_info`
- `refresh_token_hash`

## 9. Связи (ER-модель — текстовое описание)

- `Client` 1→N `Site`, `Contract`, `Requirement`, `CalendarEvent`, `Risk`, `Document`.
- `Site` 1→N `Requirement`, `CalendarEvent`, `Risk`, `Document`.
- `Requirement` 1→N `RequirementHistory`, `Document`, `CalendarEvent`, `Risk`.
- `Document` 1→N `DocumentVersion`, `DocumentAccessLog`.
- `Risk` 1→N `RiskActionPlan`, `RiskLog`.
- `Contract` 1→N `Invoice`, `Act`.
- `User` 1→N `Requirement`, `CalendarEvent`, `Document` (через `uploaded_by`), `RiskActionPlan` (через `owner_id`).
- `RequirementRule` 1→N `Requirement` (через `code`), `RequirementOverride`.

## 10. TODO

- Уточнить требования к нормализации и денормализации (например, хранение агрегатов для отчётов).
- Определить индексы и ограничения (уникальность ИНН, номер договора).
- Описать схемы sharding/partitioning при масштабировании.
- Добавить DIAGRAM (ERD) после утверждения полей.
- Синхронизировать миграции и ActiveRecord-модели с этим документом и диаграммами.

## Чек-лист готовности

- [x] Перечислены основные сущности и поля.
- [x] Описаны связи и вспомогательные таблицы.
- [x] Зафиксированы TODO для дальнейшей детализации.
- [ ] Добавить визуальную ER-диаграмму после согласования.

## Связанные документы и регламент обновлений

- `docs/architecture/diagrams/erd.puml` — визуальная ERD-диаграмма (обновляется вместе с описанием).
- `yii-app/migrations/*.php` — фактические миграции, определяющие схему БД.
- `docs/architecture/code-structure.md` — расположение моделей и миграций.
- `docs/features/*.md` — бизнес-требования к сущностям (требования, документы, риски, биллинг).
- `docs/security/compliance.md` — ограничения по хранению данных (ПДн, коммерческая тайна).

Перед обновлением модели данных:
1. Согласовать изменения с миграциями и ER-диаграммами, чтобы избежать расхождений.
2. Проверить влияние на функциональные блоки и обновить соответствующие документы.
3. Зафиксировать изменения в `docs/documentation-todo.md` и создать запись в `docs/completed/`.
