# Vyra

> A modern, scalable social media platform built with Laravel 12, Vue 3, and Domain-Driven Design.

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Vue 3 (Composition API) + Inertia.js |
| **Styling** | Tailwind CSS v4 |
| **Database** | PostgreSQL |
| **Cache & Queues** | Redis |
| **Queue Dashboard** | Laravel Horizon |
| **WebSockets** | Laravel Reverb |
| **Search** | Laravel Scout + Meilisearch |
| **File Storage** | S3-compatible object storage (MinIO supported via Flysystem `s3` driver) |
| **Testing** | Pest PHP |

## Architecture

Vyra follows **Domain-Driven Design (DDD)** with strict architectural constraints:

- **No Fat Controllers** — Controllers only handle HTTP, authorize via Policy, map to DTO, and delegate to an Action.
- **DTOs** — All request/response data flows through strongly-typed `spatie/laravel-data` objects.
- **Action Classes** — Each business operation lives in its own single-responsibility, invokable Action class.
- **Repository Pattern** — All database access goes through interfaces, never raw Eloquent in controllers or actions.
- **Event-Driven** — Side effects (notifications, feed fan-out, media processing) are handled via queued Events and Listeners.
- **Authorization** — Every resource action is authorized through a dedicated Policy.

## Domain Modules

Code is organized by domain inside `app/Domains/`:

| Domain | Responsibility |
|---|---|
| **Identity** | Authentication, registration, profiles, privacy settings |
| **SocialGraph** | Follow/unfollow, follow requests, blocking, muting |
| **Content** | Posts, comments, likes/reactions, hashtags, media |
| **Feed** | Timeline aggregation, fan-out on write, Redis sorted sets |
| **Communication** | Direct messaging, read receipts, typing indicators |
| **Notification** | In-app notifications, preferences, queued delivery |

Each domain contains: `Models/`, `Data/`, `Actions/`, `Events/`, `Listeners/`, `Jobs/`, `Policies/`, `Repositories/`, `ValueObjects/`, `Exceptions/`

## Getting Started

### Prerequisites

- PHP 8.2+
- PostgreSQL
- Redis
- Node.js 18+
- Composer

### Installation

```bash
# Clone and install dependencies
git clone <repo-url> vyra && cd vyra
php composer.phar install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Create database
createdb vyra

# Run migrations
php artisan migrate

# Build frontend
npm run build
```

### Development

```bash
# Start the dev server
php artisan serve

# Start Vite dev server (separate terminal)
npm run dev

# Run tests
./vendor/bin/pest

# Queue processing
php artisan horizon
php artisan ops:queue-health
php artisan scout:sync-index-settings
```

## Feed Architecture

Vyra uses a **fan-out on write** strategy with a hybrid threshold:

- Posts are pushed to each follower's Redis sorted set (`feed:{user_id}`) at publish time.
- Accounts with **≥ 10,000 followers** skip fan-out — their posts are merged at read time.
- Feed reads use `ZREVRANGEBYSCORE` with cursor-based pagination for sub-10ms response times.

See [docs/feed-architecture.md](docs/feed-architecture.md) for the full technical specification.

## Queue Operations

Horizon supervisors are split by workload:

- `notifications`, `communication`: realtime delivery
- `feed`: fan-out and rebuild jobs
- `search`: Scout / Meilisearch indexing
- `media`: media processing
- `default`: everything else

See [docs/queue-operations.md](docs/queue-operations.md) for queue priorities and worker guidance.

The browser queue dashboard lives at `/ops/queues` and uses the same `viewHorizon` gate as Horizon itself. Outside `local`, set `HORIZON_ALLOWED_EMAILS` to the operator email allowlist.

## Search Indexes

Scout is configured with Meilisearch index settings for both `posts` and `users`. After changing ranking/filter/search settings, run:

```bash
php artisan scout:sync-index-settings
```

## License

This project is proprietary.
