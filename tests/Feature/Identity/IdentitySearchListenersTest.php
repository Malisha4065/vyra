<?php

use App\Domains\Identity\Events\AccountDeleted;
use App\Domains\Identity\Events\ProfileUpdated;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\RemoveDeletedUserFromSearchIndexJob;
use App\Domains\Identity\Jobs\UpdateUserSearchIndexJob;
use App\Domains\Identity\Listeners\QueueRegisteredUserSearchIndexListener;
use App\Domains\Identity\Listeners\QueueRemoveDeletedUserSearchIndexListener;
use App\Domains\Identity\Listeners\QueueUserSearchIndexListener;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use Illuminate\Support\Facades\Bus;

it('dispatches user search index update when a user registers', function () {
    Bus::fake();

    $user = new User();
    $user->forceFill(['id' => 'user-1', 'username' => 'alice']);

    $listener = new QueueRegisteredUserSearchIndexListener();
    $listener->handle(new UserRegistered($user));

    Bus::assertDispatched(UpdateUserSearchIndexJob::class, function (UpdateUserSearchIndexJob $job): bool {
        return $job->userId === 'user-1' && $job->queue === 'search';
    });
});

it('dispatches user search index update when a profile changes', function () {
    Bus::fake();

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-1', 'user_id' => 'user-1']);

    $listener = new QueueUserSearchIndexListener();
    $listener->handle(new ProfileUpdated($profile));

    Bus::assertDispatched(UpdateUserSearchIndexJob::class, function (UpdateUserSearchIndexJob $job): bool {
        return $job->userId === 'user-1' && $job->queue === 'search';
    });
});

it('dispatches user search index removal when an account is deleted', function () {
    Bus::fake();

    $listener = new QueueRemoveDeletedUserSearchIndexListener();
    $listener->handle(new AccountDeleted(
        userId: 'user-1',
        username: 'alice',
        email: 'alice@example.com',
    ));

    Bus::assertDispatched(RemoveDeletedUserFromSearchIndexJob::class, function (RemoveDeletedUserFromSearchIndexJob $job): bool {
        return $job->userId === 'user-1'
            && $job->username === 'alice'
            && $job->queue === 'search';
    });
});
