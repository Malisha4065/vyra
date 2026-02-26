<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\FollowRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentFollowRequestRepository implements FollowRequestRepositoryInterface
{
    public function __construct(
        private readonly FollowRequest $model,
    ) {}

    public function hasPendingRequest(string $requesterId, string $requesteeId): bool
    {
        return $this->model
            ->where('requester_id', $requesterId)
            ->where('requestee_id', $requesteeId)
            ->where('status', FollowRequest::STATUS_PENDING)
            ->exists();
    }

    public function create(string $requesterId, string $requesteeId): FollowRequest
    {
        return $this->model->create([
            'requester_id' => $requesterId,
            'requestee_id' => $requesteeId,
            'status' => FollowRequest::STATUS_PENDING,
        ]);
    }

    public function findById(string $id): ?FollowRequest
    {
        return $this->model->find($id);
    }

    public function getPendingForUser(string $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('requestee_id', $userId)
            ->where('status', FollowRequest::STATUS_PENDING)
            ->with('requester.profile')
            ->latest()
            ->paginate($perPage);
    }

    public function updateStatus(FollowRequest $request, string $status): FollowRequest
    {
        $request->update(['status' => $status]);

        return $request->fresh();
    }

    public function delete(string $requesterId, string $requesteeId): void
    {
        $this->model
            ->where('requester_id', $requesterId)
            ->where('requestee_id', $requesteeId)
            ->delete();
    }
}
