<?php

use App\Domains\Content\Jobs\UpdatePostCommentSearchIndexJob;
use App\Domains\Content\Jobs\UpdatePostReactionSearchIndexJob;
use App\Domains\Content\Jobs\UpdatePublishedPostSearchIndexJob;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;

it('indexes published posts in scout projection job', function () {
    $post = \Mockery::mock(Post::class)->makePartial();
    $post->shouldReceive('searchable')->once();

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);

    $job = new UpdatePublishedPostSearchIndexJob('post-1');
    $job->handle($repository);
});

it('reindexes post when comments change', function () {
    $post = \Mockery::mock(Post::class)->makePartial();
    $post->shouldReceive('searchable')->once();

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);

    $job = new UpdatePostCommentSearchIndexJob('post-1', 'comment-1', 'commented');
    $job->handle($repository);
});

it('reindexes post when reactions change', function () {
    $post = \Mockery::mock(Post::class)->makePartial();
    $post->shouldReceive('searchable')->once();

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);

    $job = new UpdatePostReactionSearchIndexJob('post-1', 'user-2', 'like', 'added');
    $job->handle($repository);
});

it('extracts hashtags into searchable array', function () {
    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
        'body' => 'Testing #Laravel with #MinIO and #laravel again',
        'published_at' => now(),
    ]);

    $payload = $post->toSearchableArray();

    expect($payload['hashtags'])->toBe(['laravel', 'minio']);
});
