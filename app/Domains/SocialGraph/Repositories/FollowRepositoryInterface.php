<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Follow;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FollowRepositoryInterface
{
    public function isFollowing(string $followerId, string $followeeId): bool;

    public function create(string $followerId, string $followeeId): Follow;

    public function delete(string $followerId, string $followeeId): void;

    public function getFollowers(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function getFollowing(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function followersCount(string $userId): int;

    public function followingCount(string $userId): int;
}
