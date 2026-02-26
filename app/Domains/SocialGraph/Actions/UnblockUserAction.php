<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class UnblockUserAction
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
    ) {}

    public function __invoke(User $blocker, User $blocked): void
    {
        $this->blockRepository->delete($blocker->id, $blocked->id);
    }
}
