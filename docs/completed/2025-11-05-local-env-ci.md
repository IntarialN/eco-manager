**Контекст:** Реализация задач по локальному окружению и CI из `docs/documentation-todo.md`.

**Что сделано:** Добавлены `infra/docker/docker-compose.yml` и `infra/docker/README.md` для локального запуска инфраструктурных зависимостей (PostgreSQL, Redis, RabbitMQ, MinIO, mock Bubble). Создан базовый GitHub Actions workflow `.github/workflows/ci.yml` (lint, test, build, docker images). Таблица TODO обновлена с учётом новых артефактов.

**Артефакты:** `infra/docker/docker-compose.yml`, `infra/docker/README.md`, `.github/workflows/ci.yml`, `docs/documentation-todo.md`.

**Следующие шаги:** Добавить реальные Dockerfile в сервисы/приложения, расширить CI проверками (e2e, security scan), настроить CD (Argo CD) после появления удалённого окружения.
