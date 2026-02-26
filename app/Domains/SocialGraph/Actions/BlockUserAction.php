<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Data\BlockUserData;
use App\Domains\SocialGraph\Events\UserBlocked;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;

class BlockUserAction
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly FollowRepositoryInterface $followRepository,
        private readonly FollowRequestRepositoryInterface $followRequestRepository,
    ) {}

    public function __invoke(User $blocker, User $blocked, BlockUserData $data): void
    {
        if ($blocker->id === $data->target_user_id || $blocker->id === $blocked->id) {
            return;
        }

        // Already blocking?
        if ($this->blockRepository->isBlocking($blocker->id, $blocked->id)) {
            return;
        }

        // Create the block
        $this->blockRepository->create($blocker->id, $blocked->id);

        // Remove follows in BOTH directions
        $this->followRepository->delete($blocker->id, $blocked->id);
        $this->followRepository->delete($blocked->id, $blocker->id);

        // Cancel any pending follow requests in both directions
        $this->followRequestRepository->delete($blocker->id, $blocked->id);
        $this->followRequestRepository->delete($blocked->id, $blocker->id);

        event(new UserBlocked($blocker, $blocked));
    }
}
