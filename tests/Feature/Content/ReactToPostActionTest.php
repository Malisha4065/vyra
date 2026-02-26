<?php

use App\Domains\Content\Actions\ReactToPostAction;
use App\Domains\Content\Actions\RemovePostReactionAction;
use App\Domains\Content\Data\ReactToPostData;
use App\Domains\Content\Data\RemovePostReactionData;
use App\Domains\Content\Events\PostReactionAdded;
use App\Domains\Content\Events\PostReactionRemoved;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Models\PostReaction;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('adds or updates a reaction and dispatches domain event', function () {
    Event::fake([PostReactionAdded::class]);

    $reactor = new User();
    $reactor->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $reaction = new PostReaction();
    $reaction->forceFill(['id' => 'reaction-1', 'post_id' => 'post-1', 'user_id' => 'user-1', 'type' => 'love']);

    $repository = mock(PostReactionRepositoryInterface::class);
    $repository->shouldReceive('upsert')->once()->with('post-1', 'user-1', 'love')->andReturn($reaction);

    $action = new ReactToPostAction($repository);
    $data = ReactToPostData::from([
        'post_id' => 'post-1',
        'type' => 'love',
    ]);

    $result = $action($reactor, $post, $data);

    expect($result)->toBe($reaction);

    Event::assertDispatched(PostReactionAdded::class);
});

it('removes a reaction and dispatches domain event', function () {
    Event::fake([PostReactionRemoved::class]);

    $reactor = new User();
    $reactor->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $reaction = new PostReaction();
    $reaction->forceFill(['id' => 'reaction-1', 'post_id' => 'post-1', 'user_id' => 'user-1', 'type' => 'like']);

    $repository = mock(PostReactionRepositoryInterface::class);
    $repository->shouldReceive('deleteByUserAndPost')->once()->with('post-1', 'user-1')->andReturn($reaction);

    $action = new RemovePostReactionAction($repository);
    $data = RemovePostReactionData::from([
        'post_id' => 'post-1',
    ]);

    $action($reactor, $post, $data);

    Event::assertDispatched(PostReactionRemoved::class, function (PostReactionRemoved $event) use ($post, $reactor) {
        return $event->post === $post && $event->reactor === $reactor && $event->type === 'like';
    });
});
