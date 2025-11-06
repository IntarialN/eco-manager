**Контекст:** Решение по инфраструктуре и CI/CD (продолжение задач из `docs/documentation-todo.md`).

**Что сделано:** Подготовлен ADR `docs/architecture/adr/0002-infrastructure.md`, дополнены `docs/architecture/code-structure.md`, `docs/architecture/integration-plan.md`, `docs/README.md` и `docs/documentation-todo.md` (статусы обновлены на «Готово» для пунктов про стек и мониторинг). Зафиксированы выбор `docker-compose` + Nx для локальной разработки, GitHub Actions + Argo CD для CI/CD, Kubernetes, RabbitMQ, Prometheus/Grafana/Loki, решения по бэкапам и уведомлениям.

**Артефакты:** `docs/architecture/adr/0002-infrastructure.md`, `docs/architecture/code-structure.md`, `docs/architecture/integration-plan.md`, `docs/README.md`, `docs/documentation-todo.md`.

**Следующие шаги:** Создать черновые конфигурации (`docker-compose`, `.github/workflows/ci.yml`, `infra/k8s/`), подготовить runbook для разработки и деплоя.
