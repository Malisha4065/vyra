<?php

use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\UpdateUserSearchIndexJob;
use App\Domains\Identity\Listeners\CreateUserProfileListener;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use Illuminate\Support\Facades\Bus;

it('creates a profile and queues user search indexing after registration', function () {
    Bus::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
    ]);

    $profile = new UserProfile();
    $profile->forceFill([
        'id' => 'profile-1',
        'user_id' => 'user-1',
    ]);

    $repository = mock(UserProfileRepositoryInterface::class);
    $repository->shouldReceive('create')
        ->once()
        ->with(['user_id' => 'user-1'])
        ->andReturn($profile);

    $listener = new CreateUserProfileListener($repository);
    $listener->handle(new UserRegistered($user));

    Bus::assertDispatched(UpdateUserSearchIndexJob::class, function (UpdateUserSearchIndexJob $job): bool {
        return $job->userId === 'user-1' && $job->queue === 'search';
    });
});
