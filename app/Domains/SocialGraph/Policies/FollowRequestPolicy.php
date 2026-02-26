<?php

namespace App\Domains\SocialGraph\Policies;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class FollowRequestPolicy
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    public function viewAny(User $authUser): bool
    {
        return $authUser->id !== '';
    }

    public function accept(User $authUser, FollowRequest $request): bool
    {
        if ($authUser->id !== $request->requestee_id) {
            return false;
        }

        if (! $request->isPending()) {
            return false;
        }

        return ! $this->blockRepository->eitherBlocked($request->requester_id, $request->requestee_id);
    }

    public function reject(User $authUser, FollowRequest $request): bool
    {
        return $authUser->id === $request->requestee_id && $request->isPending();
    }
}
