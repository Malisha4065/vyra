<?php

use App\Domains\Content\Repositories\CommentRepositoryInterface;
use App\Domains\Content\Repositories\PostMediaStorageInterface;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Event;

it('updates a post via endpoint with thin controller flow', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
        'body' => 'old',
    ]);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);
    $postRepository->shouldReceive('updateBody')->once()->with($post, 'updated body')->andReturn($post);

    $this->app->instance(PostRepositoryInterface::class, $postRepository);

    $response = $this->actingAs($user)
        ->from('/')
        ->put(route('posts.update', ['post' => 'post-1']), [
            'body' => 'updated body',
        ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'Post updated.');
});

it('deletes a post via endpoint with thin controller flow', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
    ]);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);
    $postRepository->shouldReceive('delete')->once()->with($post);

    $this->app->instance(PostRepositoryInterface::class, $postRepository);

    $response = $this->actingAs($user)
        ->from('/')
        ->delete(route('posts.destroy', ['post' => 'post-1']));

    $response->assertRedirect('/');
    $response->assertSessionHas('success', 'Post deleted.');
});

it('returns comment replies via endpoint', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
    ]);

    $comment = new Comment();
    $comment->forceFill([
        'id' => 'comment-1',
        'post_id' => 'post-1',
        'user_id' => 'user-2',
        'body' => 'parent',
    ]);
    $comment->setRelation('post', $post);

    $paginator = new LengthAwarePaginator(
        items: [
            ['id' => 'reply-1', 'body' => 'reply'],
        ],
        total: 1,
        perPage: 20,
        currentPage: 1,
    );

    $commentRepository = mock(CommentRepositoryInterface::class);
    $commentRepository->shouldReceive('findById')->once()->with('comment-1')->andReturn($comment);
    $commentRepository->shouldReceive('getReplies')->once()->with('comment-1', 20)->andReturn($paginator);

    $this->app->instance(CommentRepositoryInterface::class, $commentRepository);

    $response = $this->actingAs($user)->get(route('comments.replies.index', ['comment' => 'comment-1']));

    $response->assertOk();
    $response->assertJsonPath('meta.total', 1);
    $response->assertJsonPath('data.0.id', 'reply-1');
});

it('returns reaction summary via endpoint', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
    ]);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);

    $reactionRepository = mock(PostReactionRepositoryInterface::class);
    $reactionRepository->shouldReceive('aggregateForPost')->once()->with('post-1')->andReturn([
        'total' => 4,
        'by_type' => [
            'like' => 3,
            'love' => 1,
        ],
    ]);

    $this->app->instance(PostRepositoryInterface::class, $postRepository);
    $this->app->instance(PostReactionRepositoryInterface::class, $reactionRepository);

    $response = $this->actingAs($user)->get(route('posts.reactions.summary', ['post' => 'post-1']));

    $response->assertOk();
    $response->assertExactJson([
        'total' => 4,
        'by_type' => [
            'like' => 3,
            'love' => 1,
        ],
    ]);
});

it('uploads post media via endpoint', function () {
    Event::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'owner',
        'email' => 'owner@example.com',
        'password' => 'secret',
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');

    $storage = mock(PostMediaStorageInterface::class);
    $storage->shouldReceive('storeUpload')
        ->once()
        ->with($file, 'user-1')
        ->andReturn([
            'disk' => 'local',
            'path' => 'posts/user-1/staging/2026/03/07/photo.jpg',
            'url' => 'http://localhost/storage/posts/user-1/staging/2026/03/07/photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 2048,
            'kind' => 'image',
        ]);

    $this->app->instance(PostMediaStorageInterface::class, $storage);

    $response = $this->actingAs($user)
        ->post(route('posts.media.store'), [
            'file' => $file,
        ]);

    $response->assertCreated();
    $response->assertJsonPath('data.kind', 'image');
    $response->assertJsonPath('data.original_name', 'photo.jpg');
});
