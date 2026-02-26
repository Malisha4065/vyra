# Feed Architecture: Fan-out on Write with Hybrid Threshold

## Overview

Vyra's Feed domain uses a **fan-out on write** (push model) strategy, where new posts are pushed into each follower's pre-built timeline cache at publish time. This eliminates expensive fan-out on read queries and delivers sub-10ms feed loads.

For high-follower ("celebrity") accounts (≥ 10,000 followers), a **hybrid strategy** is used to avoid the cost of writing to millions of feed caches.

---

## 1. Redis Sorted Set Design

### Key Format

```
feed:{user_uuid}
```

Each authenticated user has one sorted set that represents their personal timeline.

### Score

```
Unix timestamp in milliseconds (float) of the post's published_at
```

Using milliseconds avoids score collisions between posts published in the same second.

### Member (Value)

```
{post_uuid}
```

Only the post ID is stored. Full post data is hydrated at read time from the database (with aggressive model caching via Redis hash `post:{post_uuid}`).

### Example

```redis
ZADD feed:550e8400-e29b-41d4-a716-446655440000 1709827200000 "a1b2c3d4-..."
ZADD feed:550e8400-e29b-41d4-a716-446655440000 1709827260000 "e5f6a7b8-..."
```

### Feed Size Cap

Each feed sorted set is capped at **1,000 entries** via `ZREMRANGEBYRANK` after each write to bound memory usage. Older entries can be fetched from the database as needed.

---

## 2. Fan-out on Write Flow

### Sequence

```
User publishes post
    → Controller delegates to PublishPostAction
    → Action persists to DB, fires PostPublished event
    → Queued Listener dispatches FanOutPostJob (queue: "feed")
    → FanOutPostJob:
        1. Load follower IDs from follows table (paginated, 1000 per batch)
        2. Skip followers who have muted or been blocked by the author
        3. For each follower: ZADD feed:{follower_id} {published_at_ms} {post_id}
        4. ZREMRANGEBYRANK feed:{follower_id} 0 -(MAX_FEED_SIZE + 1) [trim oldest]
```

### Queue Configuration

- Fan-out jobs run on a dedicated `feed` queue.
- Horizon configuration: 3 workers on `feed`, `balance: auto`.
- Batch size per job: 1,000 followers. If a user has 5,000 followers, 5 chained batch jobs are dispatched.

---

## 3. Hybrid Strategy for High-Follower Accounts

### Threshold

A user is classified as "high-follower" when their `followers_count >= 10,000`. This is tracked via a cached counter on the `user_profiles` table (column: `followers_count`, updated via `FollowUserAction` / `UnfollowUserAction`).

### Behavior

| Account Type | On Write | On Read |
|---|---|---|
| **Normal** (< 10k followers) | Post ID is pushed to every follower's feed sorted set | Feed is read directly from `feed:{user_id}` sorted set |
| **High-follower** (≥ 10k followers) | Post ID is **NOT** pushed to follower feeds | At read time, the user's followed high-follower accounts are queried and their recent posts are merged into the cached feed |

### Read-Time Merge (High-Follower Injection)

```
GetFeedAction:
    1. ZREVRANGEBYSCORE feed:{user_id} +inf {cursor} LIMIT 0 {page_size}
    2. Load list of high-follower accounts the user follows (cached in Redis set: followed_celebrities:{user_id})
    3. For each celebrity, fetch their recent posts from DB (indexed on user_id + published_at, with Redis cache)
    4. Merge celebrity posts into the cached feed results, sort by published_at
    5. Return paginated, merged result
```

### Why 10,000?

- Below 10k, fan-out on write is fast: 10 batches of 1,000 `ZADD` operations complete in ~200ms.
- Above 10k, write latency grows beyond acceptable queue throughput. Read-time merge adds ~20ms per celebrity follow (typically a user follows < 20 celebrity accounts).

---

## 4. Cache Invalidation

### Post Deleted

```
User deletes post
    → DeletePostAction fires PostDeleted event
    → Queued Listener dispatches RemovePostFromFeedsJob (queue: "feed")
    → RemovePostFromFeedsJob:
        1. Load all follower IDs (same batching as fan-out)
        2. ZREM feed:{follower_id} {post_id} for each follower
        3. Delete post cache: DEL post:{post_id}
```

### Post Updated (edit)

No feed-level invalidation needed — the feed only stores post IDs. The post cache (`post:{post_uuid}`) is invalidated/refreshed, and the next read will fetch the updated content.

### User Unfollowed

When user A unfollows user B:
- **No immediate feed cleanup.** B's posts remain in A's feed cache until they naturally age out (via the 1,000-entry cap or 7-day TTL).
- This is a deliberate trade-off: cleaning up is O(n) per post, while staleness is invisible (the feed read path filters out posts from unfollowed users).

### User Blocked

When user A blocks user B:
- A `CleanBlockedUserFeedJob` is dispatched to remove all of B's posts from A's feed.
- B's feed is also cleaned of A's posts.
- This is active cleanup because blocked-user content must **never** be visible.

### TTL

All `feed:{user_id}` sorted sets have a **7-day TTL** (refreshed on every write/read). If a user is inactive for 7 days, their feed cache expires and is rebuilt on next login from the database.

---

## 5. Feed Read Path

### Pagination

Cursor-based pagination using the sorted set score (timestamp):

```
ZREVRANGEBYSCORE feed:{user_id} {cursor_score} -inf LIMIT 0 {page_size}
```

- First page: cursor = `+inf`
- Next page: cursor = score of last item from previous page (exclusive, using `(` prefix)
- Default page size: 20

### Hydration

Post IDs returned from Redis are hydrated in batch:
1. Multi-get from Redis hash cache: `MGET post:{id1} post:{id2} ...`
2. Cache misses are loaded from PostgreSQL and written back to Redis with a 1-hour TTL.
3. Post data includes author info (joined via eager-load or cached user object).

### Feed Staleness Recovery

If `feed:{user_id}` key does not exist (TTL expired or new user):
1. Query the `follows` table for all followed user IDs.
2. Query the `posts` table: `WHERE user_id IN (...followed_ids...) ORDER BY published_at DESC LIMIT 1000`.
3. Populate the Redis sorted set from the query results.
4. Return the first page.

This cold-start rebuild is an O(1) operation per user and happens transparently.

---

## 6. Monitoring & Observability

- **Horizon dashboard** tracks `feed` queue depth, throughput, and failed jobs.
- Key Redis metrics to monitor: memory usage per `feed:*` key, `ZADD` latency, sorted set cardinality.
- A scheduled command (`feed:health-check`) samples random feed keys and validates cardinality ≤ 1,000 and TTL is set.
