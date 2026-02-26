<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Events\UserUnfollowed;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class UnfollowUserAction
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
    ) {}

    public function __invoke(User $follower, User $followee): void
    {
        $this->followRepository->delete($follower->id, $followee->id);

        event(new UserUnfollowed($follower, $followee));
    }
}
