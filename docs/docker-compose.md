# Docker Compose Stack

This stack is intended for a local or prod-like MVP environment with all required services:

- Laravel app
- Horizon
- Reverb
- PostgreSQL
- Redis
- MinIO
- Meilisearch
- Mailpit

## 1. Prepare Environment

```bash
cp .env.docker.example .env.docker
```

Generate an application key:

```bash
docker compose run --rm app php artisan key:generate --show
```

Paste that value into `APP_KEY` inside `.env.docker`.

## 2. Build and Start

```bash
docker compose up -d --build
```

## 3. Initialize Laravel

```bash
docker compose run --rm app php artisan migrate --force
docker compose run --rm app php artisan scout:sync-index-settings
```

If you want demo data:

```bash
docker compose run --rm app php artisan db:seed
```

## 4. URLs

- App: `http://localhost:8000`
- Reverb: `http://localhost:8080`
- Meilisearch: `http://localhost:7700`
- Mailpit UI: `http://localhost:8025`
- MinIO API: `http://localhost:9000`
- MinIO Console: `http://localhost:9001`

## 5. Useful Commands

```bash
docker compose logs -f app
docker compose logs -f horizon
docker compose logs -f reverb
docker compose exec app php artisan ops:queue-health
docker compose exec app php artisan horizon:status
```

## 6. Shutdown

```bash
docker compose down
```

Remove data volumes too:

```bash
docker compose down -v
```

## Notes

- MinIO is exposed publicly for local MVP convenience. The bucket is made anonymously readable so uploaded media can render directly in the browser.
- The stack uses Redis for queues, cache, and sessions.
- Horizon handles queue workers. There is no separate queue worker service.
