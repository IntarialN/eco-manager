# Mock Bubble API и контракт синхронизации

Документ описывает временный REST API, который эмулирует учётную систему (Bubble) для локальной разработки и тестирования. Реальный API заменит mock после получения спецификации от заказчика.

## 1. Общие положения

- Базовый URL mock-сервиса: `http://localhost:4001/api`.
- Аутентификация: заголовок `X-API-Key: demo-key` (значение настраивается в `.env`).
- Формат данных: JSON (UTF-8).
- Все объекты содержат поля `id`, `created_at`, `updated_at`.
- Статусы синхронизируются с нашей системой через `billing-service`.

## 2. Сущности и статусы

### 2.1 Договор (`Contract`)

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | string (UUID) | Идентификатор договора |
| `client_external_id` | string | ID клиента в Bubble |
| `number` | string | Номер договора |
| `title` | string | Название/предмет |
| `status` | enum (`draft`, `active`, `suspended`, `terminated`) | Статус договора |
| `total_amount` | number | Сумма по договору |
| `currency` | string | Код валюты (RUB) |
| `valid_from` | date | Дата начала действия |
| `valid_until` | date|null | Дата окончания, если есть |

### 2.2 Счёт (`Invoice`)

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | string (UUID) | Идентификатор счёта |
| `contract_id` | string | Ссылка на договор |
| `number` | string | Номер счёта |
| `issue_date` | date | Дата выставления |
| `due_date` | date | Срок оплаты |
| `amount` | number | Сумма |
| `status` | enum (`issued`, `paid`, `overdue`, `cancelled`) | Статус оплаты |
| `payment_date` | date|null | Дата фактической оплаты |

### 2.3 Акт (`Act`)

| Поле | Тип | Описание |
|------|-----|----------|
| `id` | string (UUID) | Идентификатор акта |
| `contract_id` | string | Ссылка на договор |
| `invoice_id` | string|null | Связанный счёт |
| `number` | string | Номер акта |
| `issue_date` | date | Дата формирования |
| `status` | enum (`draft`, `pending_sign`, `signed`, `archived`) | Статус подписания |

## 3. Эндпоинты mock API

### 3.1 Contracts

- `GET /contracts?client_external_id=<id>` — список договоров клиента.
- `GET /contracts/<id>` — детали договора.
- `PATCH /contracts/<id>` — обновление статуса (поля `status`, `valid_until`).
- `POST /contracts` — создание договора (используется для тестовых данных).

Пример ответа:

```json
{
  "id": "c9a10c18-9d43-4bd5-a6f7-2f7f5c8c0f31",
  "client_external_id": "client-001",
  "number": "Д-001/25",
  "title": "Экологическое сопровождение 2025",
  "status": "active",
  "total_amount": 450000,
  "currency": "RUB",
  "valid_from": "2025-01-10",
  "valid_until": "2025-12-31",
  "created_at": "2025-01-10T09:00:00Z",
  "updated_at": "2025-01-10T09:00:00Z"
}
```

### 3.2 Invoices

- `GET /contracts/<id>/invoices` — счета по договору.
- `GET /invoices/<id>` — детали счёта.
- `PATCH /invoices/<id>` — обновление статуса (`status`, `payment_date`).
- `POST /contracts/<id>/invoices` — добавление счёта (mock-операция).

Пример PATCH:

```json
{
  "status": "paid",
  "payment_date": "2025-02-15"
}
```

### 3.3 Acts

- `GET /contracts/<id>/acts`
- `GET /acts/<id>`
- `PATCH /acts/<id>` — обновление статуса (`status`).
- `POST /contracts/<id>/acts`

## 4. Вебхуки (опционально)

Mock может отправлять события на наш webhook `POST /api/billing/webhook`:

| Событие | Payload |
|---------|---------|
| `invoice.paid` | `{ "invoice_id": "...", "payment_date": "..." }` |
| `invoice.overdue` | `{ "invoice_id": "...", "due_date": "..." }` |
| `contract.terminated` | `{ "contract_id": "...", "terminated_at": "..." }` |

В реальной интеграции убедиться, что тайминги и подписи событий соответствуют требованиям безопасности (HMAC).

## 5. Cron-синхронизация

`billing-service` выполняет задачи:

- `syncContracts` — каждые 6 часов.
- `syncInvoices` — каждые 2 часа.
- `syncActs` — раз в сутки (или по webhook).

Локально можно запускать `nx run billing-service:sync --project=billing-service`.

## 6. Реализация mock сервиса

- Отдельный NestJS (или fastify) сервис внутри `services/mock-bubble/`.
- Эндпоинты описаны выше, данные хранятся в in-memory массиве или SQLite для повторяемости.
- Скрипт `scripts/mock-bubble-seed.ts` создаёт тестовые договоры/счета для демо.

## 7. TODO после получения реального API

- Сверить поля и статусы с документацией Bubble.
- Проверить механизмы аутентификации (API key, OAuth, JWT).
- Определить ограничения по rate-limit и размеру ответа.
- Обновить конфигурацию cron/вебхуков и внести правки в `billing-service`.
