# Runbook разработки и деплоя (черновик)

Документ описывает базовые операции для команды: запуск проекта локально, выполнение тестов, взаимодействие с CI/CD и деплой.

## 1. Предварительные требования
- Docker Engine 20+, docker-compose v2.
- Node.js 20 (для фронта/бэкена), npm 9+.
- Nx CLI (`npm install -g nx@latest`) — опционально.
- Доступ к репозиторию (ветки `main`, `develop`, `feature/*`).

## 2. Локальный запуск

### 2.1 Поднять инфраструктуру
```bash
docker compose -f infra/docker/docker-compose.yml up -d
```
Поднимает PostgreSQL, Redis, RabbitMQ, MinIO и mock Bubble API.

### 2.2 Настроить переменные окружения
- Скопировать `.env.example` → `.env` (для каждого сервиса/приложения, TBD).
- Указать параметры подключения:
  - `DATABASE_URL=postgresql://eco_user:eco_password@localhost:5432/eco_db`
  - `REDIS_URL=redis://localhost:6379`
  - `RABBITMQ_URL=amqp://eco_user:eco_password@localhost:5672`
  - `MINIO_ENDPOINT=http://localhost:9000`
  - `BUBBLE_API_URL=http://localhost:4001/api`

### 2.3 Запуск сервисов
Используем Nx или npm:
```bash
npm run dev:web-client
npm run dev:api-gateway
npm run dev:requirements-service
# остальные сервисы по необходимости
```

## 3. Тестирование
- `npm run lint` — линтер.
- `npm run test` — юнит/интеграционные тесты (Jest/Vitest).
- `npm run test:e2e` — энд-ту-энд (Playwright/Cypress) — планируется.
- Перед коммитом рекомендуется запускать `npm run verify` (объединение всех проверок, TBD).

## 4. Рабочий процесс с Git
- Создаём ветку `feature/<topic>` от `develop`.
- Коммиты по Conventional Commits (`docs:`, `feat:`, `fix:`).
- PR → `develop`, после апрува и успешного CI — merge.
- После набора функционала — PR `develop` → `main` (релиз).

## 5. CI/CD
- GitHub Actions (`.github/workflows/ci.yml`):
  - Линт, тесты, build.
  - Сборка Docker образов и push в GHCR (по push в `develop`).
- CD (план): Argo CD отслеживает манифесты в `infra/k8s/`.
- Для ручного деплоя на dev/stage:
  ```bash
  kubectl apply -k infra/k8s/overlays/stage
  ```
  (фактические пути будут добавлены после подготовки манифестов).

## 6. Работа с mock Bubble API
- Исходники предполагаются в `services/mock-bubble/`.
- Start: `npm run dev` внутри контейнера docker-compose.
- Seed данным: `npm run seed` (TBD).
- При появлении реального API — обновить `docs/architecture/mock-bubble-api.md`.

## 7. Бэкапы и мониторинг (локальный контур)
- Backup/restore в локальном окружении вручную (скрипты TBD).
- Grafana/Prometheus планируются для stage/prod; локально можно использовать `docker compose -f infra/docker/monitoring-compose.yml` (будет добавлено).

## 8. FAQ (обновлять по мере работы)
- **Проблемы с подключением к БД**: убедитесь, что контейнер `eco-postgres` работает (`docker ps`), пароль соответствует `.env`.
- **RabbitMQ порт занят**: остановить локальный экземпляр, перезапустить `docker compose`.
- **Ошибки npm install**: очистить кеш `npm cache clean --force`, повторить установку.

## 9. TODO
- Добавить примеры `.env` для каждого сервиса.
- Описать процедуру миграций БД (Prisma `npx prisma migrate dev`).
- Подготовить раздел для prod/stage деплоя после выбора облака.
