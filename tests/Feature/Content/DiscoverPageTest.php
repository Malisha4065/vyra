<?php

use App\Domains\Content\Actions\DiscoverContentAction;
use App\Domains\Content\Data\SearchContentData;
use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the discover page with search results and trends', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'reader',
        'email' => 'reader@example.com',
        'password' => 'secret',
    ]);

    $action = mock(DiscoverContentAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(fn (SearchContentData $data): bool => $data->query === 'laravel')
        ->andReturn([
            'results' => [
                [
                    'id' => 'post-1',
                    'body' => 'Laravel post',
                    'author' => ['id' => 'user-2', 'username' => 'alice'],
                    'published_at' => null,
                    'counts' => ['comments' => 0, 'reactions' => 0],
                    'hashtags' => ['laravel'],
                    'media' => [],
                ],
            ],
            'trending_hashtags' => [
                ['tag' => 'laravel', 'count' => 4],
            ],
        ]);

    $this->app->instance(DiscoverContentAction::class, $action);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')->once()->with('user-1')->andReturn(0);
    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)->get(route('content.discover', ['query' => 'laravel']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Content/Discover')
        ->where('query', 'laravel')
        ->where('results.0.id', 'post-1')
        ->where('trendingHashtags.0.tag', 'laravel'));
});
