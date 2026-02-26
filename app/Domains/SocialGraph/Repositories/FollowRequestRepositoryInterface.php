<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\FollowRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface FollowRequestRepositoryInterface
{
    public function hasPendingRequest(string $requesterId, string $requesteeId): bool;

    public function create(string $requesterId, string $requesteeId): FollowRequest;

    public function findById(string $id): ?FollowRequest;

    public function getPendingForUser(string $userId, int $perPage = 20): LengthAwarePaginator;

    public function updateStatus(FollowRequest $request, string $status): FollowRequest;

    public function delete(string $requesterId, string $requesteeId): void;
}
