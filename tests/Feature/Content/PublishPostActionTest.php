<?php

use App\Domains\Content\Actions\PublishPostAction;
use App\Domains\Content\Data\PublishPostData;
use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('publishes a post through the content action', function () {
    Event::fake([PostPublished::class]);

    $author = new User();
    $author->forceFill(['id' => 'user-1']);

    $expectedPost = new Post();
    $expectedPost->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
        'body' => 'First content domain post',
    ]);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('publish')
        ->once()
        ->with('user-1', 'First content domain post', [])
        ->andReturn($expectedPost);

    $action = new PublishPostAction($repository);
    $data = PublishPostData::from([
        'body' => 'First content domain post',
        'media' => [],
    ]);

    $post = $action($author, $data);

    expect($post)->toBe($expectedPost);

    Event::assertDispatched(PostPublished::class, function (PostPublished $event) use ($expectedPost, $author) {
        return $event->post === $expectedPost && $event->author === $author;
    });
});

it('rejects an empty payload in the publish action', function () {
    Event::fake([PostPublished::class]);

    $author = new User();
    $author->forceFill(['id' => 'user-1']);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldNotReceive('publish');

    $action = new PublishPostAction($repository);
    $data = PublishPostData::from([
        'body' => '   ',
        'media' => [],
    ]);

    expect(fn () => $action($author, $data))->toThrow(EmptyPostException::class);

    Event::assertNotDispatched(PostPublished::class);
});
