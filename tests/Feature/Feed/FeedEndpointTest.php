<?php

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Identity\Models\User;
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
        ->andReturn([
            'items' => [
                [
                    'post_id' => 'post-1',
                    'score' => 1000,
                    'post' => [
                        'id' => 'post-1',
                        'body' => 'Hydrated post',
                    ],
                ],
            ],
            'next_cursor' => 1000,
        ]);

    $this->app->instance(GetUserFeedAction::class, $action);

    $response = $this->actingAs($user)->get(route('feed'));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Feed/Index')
        ->where('feed.next_cursor', 1000)
        ->where('feed.items.0.post_id', 'post-1')
        ->where('feed.items.0.post.body', 'Hydrated post'));
});
