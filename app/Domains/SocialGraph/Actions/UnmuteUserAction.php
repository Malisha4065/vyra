<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;

class UnmuteUserAction
{
    public function __construct(
        private readonly MuteRepositoryInterface $muteRepository,
    ) {}

    public function __invoke(User $muter, User $muted): void
    {
        $this->muteRepository->delete($muter->id, $muted->id);
    }
}
