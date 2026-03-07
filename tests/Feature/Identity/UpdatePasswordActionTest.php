<?php

use App\Domains\Identity\Actions\UpdatePasswordAction;
use App\Domains\Identity\Data\UpdatePasswordData;
use App\Domains\Identity\Events\PasswordUpdated;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('updates the authenticated user password and dispatches an event', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
    ]);

    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('update')
        ->once()
        ->with($user, ['password' => 'NewSecurePass123!'])
        ->andReturn($user);

    $action = new UpdatePasswordAction($repository);
    $result = $action($user, new UpdatePasswordData(
        current_password: 'CurrentPass123!',
        password: 'NewSecurePass123!',
        password_confirmation: 'NewSecurePass123!',
    ));

    expect($result)->toBe($user);

    Event::assertDispatched(PasswordUpdated::class, function (PasswordUpdated $event) use ($user): bool {
        return $event->user === $user;
    });
});
