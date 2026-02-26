<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\SocialGraph\Exceptions\FollowRequestNotFoundException;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;

class RejectFollowRequestAction
{
    public function __construct(
        private readonly FollowRequestRepositoryInterface $followRequestRepository,
    ) {}

    /**
     * @throws FollowRequestNotFoundException
     */
    public function __invoke(string $requestId): void
    {
        $request = $this->followRequestRepository->findById($requestId);

        if (! $request || ! $request->isPending()) {
            throw new FollowRequestNotFoundException();
        }

        $this->followRequestRepository->updateStatus($request, FollowRequest::STATUS_REJECTED);
    }
}
