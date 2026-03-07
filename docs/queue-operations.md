# Queue Operations

## Required Runtime

Vyra uses named Redis queues and expects Horizon to supervise them in non-test environments.

Set:

```env
QUEUE_CONNECTION=redis
```

## Queue Priority Model

Queues are isolated by workload instead of mixing everything into `default`.

| Queue | Purpose | Priority |
|---|---|---|
| `notifications` | in-app notification fan-out and realtime delivery | highest |
| `communication` | direct-message broadcast jobs and read/typing updates | highest |
| `feed` | feed rebuilds and heavy fan-out/backfill work | high |
| `search` | Scout / Meilisearch indexing for posts and users | medium |
| `media` | image/video processing | medium |
| `default` | lightweight domain listeners and non-specialized jobs | baseline |

## Horizon Supervisors

`config/horizon.php` defines separate supervisors for:

- `supervisor-realtime`
- `supervisor-feed`
- `supervisor-search`
- `supervisor-media`
- `supervisor-default`

This keeps feed rebuilds and media work from starving realtime jobs.

## Recommended Commands

```bash
php artisan horizon
php artisan horizon:status
php artisan horizon:terminate
```

## Operational Notes

- Realtime queues should remain small. If `notifications` or `communication` backlog grows, increase `supervisor-realtime.maxProcesses`.
- Feed rebuilds are intentionally isolated on `feed` because cache backfills can burst heavily.
- Search indexing runs independently so Meilisearch lag does not affect message delivery or feed writes.
- Media jobs get the longest timeout because they are the most CPU / IO heavy jobs in the system.
