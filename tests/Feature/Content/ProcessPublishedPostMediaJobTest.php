<?php

use App\Domains\Content\Jobs\ProcessPublishedPostMediaJob;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;

it('marks post media as processed for existing post', function () {
    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'user_id' => 'user-1',
    ]);

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('post-1')->andReturn($post);
    $repository->shouldReceive('markMediaProcessed')->once()->with('post-1')->andReturn(1);

    $job = new ProcessPublishedPostMediaJob('post-1');
    $job->handle($repository);

    expect(true)->toBeTrue();
});

it('skips media processing when post is missing', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findById')->once()->with('missing-post')->andReturn(null);
    $repository->shouldReceive('markMediaProcessed')->never();

    $job = new ProcessPublishedPostMediaJob('missing-post');
    $job->handle($repository);

    expect(true)->toBeTrue();
});
