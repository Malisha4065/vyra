<?php

use App\Domains\Content\Actions\UpdatePostAction;
use App\Domains\Content\Data\UpdatePostData;
use App\Domains\Content\Events\PostUpdated;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('updates a post and dispatches post updated event', function () {
    Event::fake([PostUpdated::class]);

    $editor = new User();
    $editor->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-1', 'body' => 'old']);

    $updatedPost = new Post();
    $updatedPost->forceFill(['id' => 'post-1', 'user_id' => 'user-1', 'body' => 'new body']);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('updateBody')->once()->with($post, 'new body')->andReturn($updatedPost);

    $action = new UpdatePostAction($repository);

    $data = UpdatePostData::from([
        'post_id' => 'post-1',
        'body' => 'new body',
    ]);

    $result = $action($editor, $post, $data);

    expect($result)->toBe($updatedPost);

    Event::assertDispatched(PostUpdated::class, function (PostUpdated $event) use ($updatedPost, $editor) {
        return $event->post === $updatedPost && $event->editor === $editor;
    });
});

it('rejects empty post body on update', function () {
    $editor = new User();
    $editor->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-1', 'body' => 'old']);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('updateBody')->never();

    $action = new UpdatePostAction($repository);

    $data = UpdatePostData::from([
        'post_id' => 'post-1',
        'body' => '   ',
    ]);

    expect(fn () => $action($editor, $post, $data))->toThrow(EmptyPostException::class);
});
