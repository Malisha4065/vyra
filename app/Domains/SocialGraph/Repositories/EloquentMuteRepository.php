<?php

namespace App\Domains\SocialGraph\Repositories;

use App\Domains\SocialGraph\Models\Mute;

class EloquentMuteRepository implements MuteRepositoryInterface
{
    public function __construct(
        private readonly Mute $model,
    ) {}

    public function isMuting(string $muterId, string $mutedId): bool
    {
        return $this->model
            ->where('muter_id', $muterId)
            ->where('muted_id', $mutedId)
            ->exists();
    }

    public function create(string $muterId, string $mutedId): Mute
    {
        return $this->model->create([
            'muter_id' => $muterId,
            'muted_id' => $mutedId,
        ]);
    }

    public function delete(string $muterId, string $mutedId): void
    {
        $this->model
            ->where('muter_id', $muterId)
            ->where('muted_id', $mutedId)
            ->delete();
    }
}
