<?php

use App\Domains\Content\Actions\DeletePostAction;
use App\Domains\Content\Data\DeletePostData;
use App\Domains\Content\Events\PostDeleted;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('deletes a post and dispatches post deleted event', function () {
    Event::fake([PostDeleted::class]);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'author-1']);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('delete')->once()->with($post);

    $action = new DeletePostAction($repository);

    $data = DeletePostData::from([
        'post_id' => 'post-1',
    ]);

    $action($post, $data);

    Event::assertDispatched(PostDeleted::class, function (PostDeleted $event) {
        return $event->postId === 'post-1' && $event->authorId === 'author-1';
    });
});
