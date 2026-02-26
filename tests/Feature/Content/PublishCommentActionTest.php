<?php

use App\Domains\Content\Actions\PublishCommentAction;
use App\Domains\Content\Data\PublishCommentData;
use App\Domains\Content\Events\PostCommented;
use App\Domains\Content\Exceptions\EmptyCommentException;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Event;

it('publishes a comment for a post', function () {
    Event::fake([PostCommented::class]);

    $author = new User();
    $author->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $comment = new Comment();
    $comment->forceFill(['id' => 'comment-1', 'post_id' => 'post-1', 'user_id' => 'user-1', 'body' => 'Nice post']);

    $repository = mock(CommentRepositoryInterface::class);
    $repository->shouldReceive('findById')->never();
    $repository->shouldReceive('create')
        ->once()
        ->with([
            'post_id' => 'post-1',
            'user_id' => 'user-1',
            'parent_comment_id' => null,
            'body' => 'Nice post',
        ])
        ->andReturn($comment);

    $action = new PublishCommentAction($repository);

    $data = PublishCommentData::from([
        'post_id' => 'post-1',
        'body' => 'Nice post',
    ]);

    $result = $action($author, $post, $data);

    expect($result)->toBe($comment);

    Event::assertDispatched(PostCommented::class, function (PostCommented $event) use ($comment, $post, $author) {
        return $event->comment === $comment && $event->post === $post && $event->author === $author;
    });
});

it('rejects empty comments', function () {
    $author = new User();
    $author->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $repository = mock(CommentRepositoryInterface::class);
    $repository->shouldReceive('findById')->never();
    $repository->shouldReceive('create')->never();

    $action = new PublishCommentAction($repository);

    $data = PublishCommentData::from([
        'post_id' => 'post-1',
        'body' => '   ',
    ]);

    expect(fn () => $action($author, $post, $data))->toThrow(EmptyCommentException::class);
});
