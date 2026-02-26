<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Mute;

interface MuteRepositoryInterface
{
    public function isMuting(string $muterId, string $mutedId): bool;

    public function create(string $muterId, string $mutedId): Mute;

    public function delete(string $muterId, string $mutedId): void;
}
