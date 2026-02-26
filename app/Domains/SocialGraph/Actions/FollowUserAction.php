<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use App\Domains\SocialGraph\Events\FollowRequestReceived;
use App\Domains\SocialGraph\Events\UserFollowed;
use App\Domains\SocialGraph\Exceptions\AlreadyFollowingException;
use App\Domains\SocialGraph\Exceptions\CannotFollowSelfException;
use App\Domains\SocialGraph\Exceptions\UserBlockedException;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;

class FollowUserAction
{
    public function __construct(
        private readonly FollowRepositoryInterface $followRepository,
        private readonly FollowRequestRepositoryInterface $followRequestRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    /**
     * Follow a user. If the target is private, creates a follow request instead.
     *
     * @return string 'followed' or 'requested'
     *
     * @throws CannotFollowSelfException
     * @throws AlreadyFollowingException
     * @throws UserBlockedException
     */
    public function __invoke(User $follower, User $followee): string
    {
        if ($follower->id === $followee->id) {
            throw new CannotFollowSelfException();
        }

        if ($this->blockRepository->eitherBlocked($follower->id, $followee->id)) {
            throw new UserBlockedException();
        }

        if ($this->followRepository->isFollowing($follower->id, $followee->id)) {
            throw new AlreadyFollowingException();
        }

        // Check if the target account is private
        $profile = $this->profileRepository->findByUserId($followee->id);

        if ($profile && $profile->is_private) {
            // Already has a pending request?
            if ($this->followRequestRepository->hasPendingRequest($follower->id, $followee->id)) {
                return 'requested';
            }

            $request = $this->followRequestRepository->create($follower->id, $followee->id);
            event(new FollowRequestReceived($request));

            return 'requested';
        }

        // Public account — follow directly
        $this->followRepository->create($follower->id, $followee->id);
        event(new UserFollowed($follower, $followee));

        return 'followed';
    }
}
