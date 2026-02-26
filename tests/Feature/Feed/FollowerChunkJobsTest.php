<?php

use App\Domains\Feed\Jobs\PushPostToFollowersChunkJob;
use App\Domains\Feed\Jobs\RemovePostFromFollowersChunkJob;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use Illuminate\Support\Facades\Bus;

it('pushes follower chunk and dispatches next job when chunk is full', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowerIdsChunk')
        ->once()
        ->with('author-1', null, 2)
        ->andReturn(['u1', 'u2']);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('addPostToUserFeedsIfAbsent')
        ->once()
        ->with(['u1', 'u2'], 'post-1', 1000);

    $job = new PushPostToFollowersChunkJob(
        postId: 'post-1',
        authorId: 'author-1',
        score: 1000,
        afterFollowerId: null,
        chunkSize: 2,
    );

    $job->handle($followRepository, $feedCacheRepository);

    Bus::assertDispatched(PushPostToFollowersChunkJob::class, function (PushPostToFollowersChunkJob $next) {
        return $next->afterFollowerId === 'u2';
    });
});

it('removes follower chunk and stops when chunk is partial', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowerIdsChunk')
        ->once()
        ->with('author-1', null, 3)
        ->andReturn(['u1', 'u2']);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('removePostFromUserFeeds')
        ->once()
        ->with(['u1', 'u2'], 'post-1');

    $job = new RemovePostFromFollowersChunkJob(
        postId: 'post-1',
        authorId: 'author-1',
        afterFollowerId: null,
        chunkSize: 3,
    );

    $job->handle($followRepository, $feedCacheRepository);

    Bus::assertNotDispatched(RemovePostFromFollowersChunkJob::class);
});
