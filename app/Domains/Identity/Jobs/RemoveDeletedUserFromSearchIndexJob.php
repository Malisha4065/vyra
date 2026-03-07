<?php

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RemoveDeletedUserFromSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userId,
        public readonly string $username,
    ) {}

    public function handle(): void
    {
        $user = new User();
        $user->forceFill([
            'id' => $this->userId,
            'username' => $this->username,
        ]);

        $user->unsearchable();
    }
}
