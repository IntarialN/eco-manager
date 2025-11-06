## Локальное окружение

`docker-compose.yml` поднимает инфраструктурные зависимости для разработки:

- PostgreSQL (порт 5432)
- Redis (порт 6379)
- RabbitMQ + management (5672/15672)
- MinIO + console (9000/9001)
- Mock Bubble API (4001) — заглушка для интеграции

### Требования

- Docker Engine 20+
- docker-compose v2

### Запуск

```bash
docker compose -f infra/docker/docker-compose.yml up -d
```

Переменные окружения сервисов (NestJS/React) указывают на поднятые контейнеры. Для остановки:

```bash
docker compose -f infra/docker/docker-compose.yml down
```

### TODO

- Добавить seed-скрипты для mock Bubble API.
- Настроить volume для логов и бэкапов (если потребуется).
