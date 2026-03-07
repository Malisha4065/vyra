<?php

namespace App\Domains\Identity\Listeners;

use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\UpdateUserSearchIndexJob;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateUserProfileListener implements ShouldQueue
{
    public string $queue = 'default';

    public function __construct(
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function handle(UserRegistered $event): void
    {
        $this->profileRepository->create([
            'user_id' => $event->user->id,
        ]);

        UpdateUserSearchIndexJob::dispatch($event->user->id)->onQueue('search');
    }
}
