<?php

use App\Domains\Identity\Actions\SearchUsersAction;
use App\Domains\Identity\Data\SearchUsersData;
use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;

it('returns user search results for autocomplete endpoint', function () {
    $viewer = new User();
    $viewer->forceFill([
        'id' => 'user-1',
        'username' => 'viewer',
        'email' => 'viewer@example.com',
        'password' => 'secret',
    ]);

    $action = mock(SearchUsersAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (SearchUsersData $data): bool => $data->viewer_id === 'user-1' && $data->query === 'ali')
        ->andReturn([
            [
                'id' => 'user-2',
                'username' => 'alice',
                'display_name' => 'Alice',
                'bio' => null,
                'avatar_url' => null,
                'is_private' => false,
            ],
        ]);

    $this->app->instance(SearchUsersAction::class, $action);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->never();
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($viewer)
        ->get(route('users.search', ['query' => 'ali']));

    $response->assertOk();
    $response->assertJsonPath('meta.query', 'ali');
    $response->assertJsonPath('data.0.username', 'alice');
});
