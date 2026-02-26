<?php

namespace App\Domains\SocialGraph\Actions;

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Data\MuteUserData;
use App\Domains\SocialGraph\Events\UserMuted;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;

class MuteUserAction
{
    public function __construct(
        private readonly MuteRepositoryInterface $muteRepository,
    ) {}

    public function __invoke(User $muter, User $muted, MuteUserData $data): void
    {
        if ($muter->id === $data->target_user_id || $muter->id === $muted->id) {
            return;
        }

        if ($this->muteRepository->isMuting($muter->id, $muted->id)) {
            return;
        }

        $this->muteRepository->create($muter->id, $muted->id);

        event(new UserMuted($muter, $muted));
    }
}
