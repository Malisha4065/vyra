<?php

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\FeedAuthorData;
use App\Domains\Feed\Data\FeedItemData;
use App\Domains\Feed\Data\FeedPostData;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Data\UserFeedResponseData;
use App\Domains\Identity\Models\User;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Inertia\Testing\AssertableInertia as Assert;

it('renders feed page with hydrated feed payload', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'reader',
        'email' => 'reader@example.com',
        'password' => 'secret',
    ]);

    $action = mock(GetUserFeedAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(function (GetUserFeedData $data) {
            return $data->user_id === 'user-1' && $data->limit === 50;
        })
        ->andReturn(new UserFeedResponseData(
            items: [
                new FeedItemData(
                    post_id: 'post-1',
                    score: 1000,
                    post: new FeedPostData(
                        id: 'post-1',
                        user_id: 'author-1',
                        body: 'Hydrated post',
                        published_at: null,
                        author: new FeedAuthorData(
                            id: 'author-1',
                            username: 'writer',
                        ),
                        counts: ['comments' => 0, 'reactions' => 0],
                        media: [],
                    ),
                ),
            ],
            next_cursor: 1000,
        ));

    $this->app->instance(GetUserFeedAction::class, $action);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)->get(route('feed'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Feed/Index')
        ->where('focusPostId', null)
        ->where('feed.next_cursor', 1000)
        ->where('feed.items.0.post_id', 'post-1')
        ->where('feed.items.0.post.body', 'Hydrated post'));
});

it('passes focus post id to feed page when requested', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'reader',
        'email' => 'reader@example.com',
        'password' => 'secret',
    ]);

    $action = mock(GetUserFeedAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->andReturn(new UserFeedResponseData(items: [], next_cursor: null));

    $this->app->instance(GetUserFeedAction::class, $action);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')
        ->once()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)->get(route('feed', ['focus_post_id' => 'post-99']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Feed/Index')
        ->where('focusPostId', 'post-99')
    );
});

it('returns feed json for cursor pagination requests', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'reader',
        'email' => 'reader@example.com',
        'password' => 'secret',
    ]);

    $action = mock(GetUserFeedAction::class);
    $action->shouldReceive('__invoke')
        ->once()
        ->withArgs(function (GetUserFeedData $data) {
            return $data->user_id === 'user-1' && $data->before_score === 900;
        })
        ->andReturn(new UserFeedResponseData(
            items: [
                new FeedItemData(
                    post_id: 'post-2',
                    score: 800,
                    post: new FeedPostData(
                        id: 'post-2',
                        user_id: 'author-2',
                        body: 'Older post',
                        published_at: null,
                        author: new FeedAuthorData(
                            id: 'author-2',
                            username: 'older-writer',
                        ),
                        counts: ['comments' => 1, 'reactions' => 2],
                        media: [],
                    ),
                ),
            ],
            next_cursor: 800,
        ));

    $this->app->instance(GetUserFeedAction::class, $action);

    $notificationRepository = mock(UserNotificationRepositoryInterface::class);
    $notificationRepository->shouldReceive('unreadCount')
        ->never()
        ->with('user-1')
        ->andReturn(0);

    $this->app->instance(UserNotificationRepositoryInterface::class, $notificationRepository);

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->get(route('feed', ['before_score' => 900]));

    $response->assertOk();
    $response->assertJsonPath('meta.next_cursor', 800);
    $response->assertJsonPath('data.0.post.id', 'post-2');
});
