<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Follow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentFollowRepository implements FollowRepositoryInterface
{
    public function __construct(
        private readonly Follow $model,
    ) {}

    public function isFollowing(string $followerId, string $followeeId): bool
    {
        return $this->model
            ->where('follower_id', $followerId)
            ->where('followee_id', $followeeId)
            ->exists();
    }

    public function create(string $followerId, string $followeeId): Follow
    {
        return $this->model->create([
            'follower_id' => $followerId,
            'followee_id' => $followeeId,
        ]);
    }

    public function delete(string $followerId, string $followeeId): void
    {
        $this->model
            ->where('follower_id', $followerId)
            ->where('followee_id', $followeeId)
            ->delete();
    }

    public function getFollowers(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('followee_id', $userId)
            ->with('follower.profile')
            ->latest()
            ->paginate($perPage);
    }

    public function getFollowing(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('follower_id', $userId)
            ->with('followee.profile')
            ->latest()
            ->paginate($perPage);
    }

    public function followersCount(string $userId): int
    {
        return $this->model->where('followee_id', $userId)->count();
    }

    public function followingCount(string $userId): int
    {
        return $this->model->where('follower_id', $userId)->count();
    }

    public function getFollowerIds(string $userId): array
    {
        return $this->model
            ->where('followee_id', $userId)
            ->pluck('follower_id')
            ->all();
    }

    public function getFollowingIds(string $userId): array
    {
        return $this->model
            ->where('follower_id', $userId)
            ->pluck('followee_id')
            ->all();
    }

    public function getFollowerIdsChunk(string $userId, ?string $afterFollowerId = null, int $limit = 1000): array
    {
        $query = $this->model
            ->where('followee_id', $userId)
            ->orderBy('follower_id')
            ->limit($limit);

        if ($afterFollowerId !== null) {
            $query->where('follower_id', '>', $afterFollowerId);
        }

        return $query->pluck('follower_id')->all();
    }
}
