<?php

namespace App\Domains\Identity\Jobs;

use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class UpdateUserSearchIndexJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $userId,
    ) {}

    public function handle(UserRepositoryInterface $userRepository): void
    {
        $user = $userRepository->findById($this->userId);

        if ($user === null) {
            return;
        }

        $user->searchable();
    }
}
