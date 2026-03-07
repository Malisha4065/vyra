# MVP Launch Checklist

## 1. Core Runtime

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set a real `APP_URL`
- Run behind HTTPS only
- Configure process manager for PHP workers and Horizon

## 2. Database

- Provision PostgreSQL with backups enabled
- Run `php artisan migrate --force`
- Verify connection pool / max connections for Horizon workers
- Confirm `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST`, `DB_PORT`

## 3. Redis / Queues

- Set `QUEUE_CONNECTION=redis`
- Set `CACHE_STORE=redis`
- Set `SESSION_DRIVER=redis`
- Start Horizon and confirm supervisors are healthy
- Run `php artisan ops:queue-health`
- Verify `notifications`, `communication`, `feed`, `search`, `media`, and `default` queues exist

## 4. Object Storage (MinIO)

- Keep Laravel `s3` driver
- Point `AWS_ENDPOINT` to MinIO
- Set `AWS_USE_PATH_STYLE_ENDPOINT=true`
- Set `FILESYSTEM_DISK=s3`
- Set `MEDIA_DISK=s3`
- Verify uploaded avatars and post media resolve correctly

## 5. Search

- Set `SCOUT_DRIVER=meilisearch`
- Set `MEILISEARCH_HOST` and `MEILISEARCH_KEY`
- Run `php artisan scout:sync-index-settings`
- Backfill indexes if needed

## 6. Mail

- Configure a real mail provider
- Verify registration email verification delivery
- Verify password reset delivery
- Confirm `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME`

## 7. Realtime

- Configure Reverb keys and public host values
- Start Reverb and verify websocket connectivity
- Confirm live chat and notification broadcasts in-browser

## 8. Security

- Confirm login/register/reset throttles are active
- Confirm authenticated product routes require verified email
- Confirm Horizon access is restricted via `HORIZON_ALLOWED_EMAILS`
- Confirm blocked users cannot view or interact across domains

## 9. Frontend / Assets

- Run `npm run build`
- Confirm `public/build/manifest.json` exists in deployed artifact
- Verify feed, discover, notifications, messages, and profile pages render without Vite dev server

## 10. Smoke Tests

- Register a user
- Verify email
- Publish a post with media
- Follow another user
- Receive a notification
- Send a DM
- Reload feed and notifications

## 11. Operations

- Set log aggregation / retention
- Set failed job monitoring
- Set backup schedule for PostgreSQL and MinIO
- Document deploy and rollback steps
