<?php

use App\Domains\Identity\Actions\DeleteAccountAction;
use App\Domains\Identity\Data\DeleteAccountData;
use App\Domains\Identity\Events\AccountDeleted;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('deletes the authenticated account and dispatches an event', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
    ]);

    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('delete')->once()->with($user);

    $action = new DeleteAccountAction($repository);
    $action($user, new DeleteAccountData(
        current_password: 'CurrentPass123!',
    ));

    Event::assertDispatched(AccountDeleted::class, function (AccountDeleted $event): bool {
        return $event->userId === 'user-1' && $event->username === 'alice';
    });
});
