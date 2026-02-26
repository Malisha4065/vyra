<?php

use App\Domains\Feed\Actions\FanOutPostOnWriteAction;
use App\Domains\Feed\Data\FanOutPostOnWriteData;
use App\Domains\Feed\Jobs\PushPostToFollowersChunkJob;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use Illuminate\Support\Facades\Bus;

it('fans out to follower feeds for regular accounts', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(120);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('addPostToUserFeedIfAbsent')->once()->with('author-1', 'post-1', 1700000000);
    $feedCacheRepository->shouldReceive('addPostToUserFeed')->never();
    $feedCacheRepository->shouldReceive('addPostToUserFeeds')->never();
    $feedCacheRepository->shouldReceive('addPostToUserFeedsIfAbsent')->never();

    $hybridFeedRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridFeedRepository->shouldReceive('unmarkAuthorAsHighFollower')->once()->with('author-1');
    $hybridFeedRepository->shouldReceive('markAuthorAsHighFollower')->never();
    $hybridFeedRepository->shouldReceive('addPostForHighFollowerAuthor')->never();

    $action = new FanOutPostOnWriteAction($followRepository, $feedCacheRepository, $hybridFeedRepository);

    $result = $action(FanOutPostOnWriteData::from([
        'post_id' => 'post-1',
        'author_id' => 'author-1',
        'published_at' => 1700000000,
    ]));

    expect($result)->toBe('fanout');
    Bus::assertDispatched(PushPostToFollowersChunkJob::class);
});

it('switches to hybrid for high follower accounts', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(10000);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('addPostToUserFeedIfAbsent')->once()->with('author-1', 'post-1', 1700000000);
    $feedCacheRepository->shouldReceive('addPostToUserFeed')->never();
    $feedCacheRepository->shouldReceive('addPostToUserFeeds')->never();
    $feedCacheRepository->shouldReceive('addPostToUserFeedsIfAbsent')->never();

    $hybridFeedRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridFeedRepository->shouldReceive('markAuthorAsHighFollower')->once()->with('author-1');
    $hybridFeedRepository->shouldReceive('addPostForHighFollowerAuthor')->once()->with('author-1', 'post-1', 1700000000);
    $hybridFeedRepository->shouldReceive('unmarkAuthorAsHighFollower')->never();

    $action = new FanOutPostOnWriteAction($followRepository, $feedCacheRepository, $hybridFeedRepository);

    $result = $action(FanOutPostOnWriteData::from([
        'post_id' => 'post-1',
        'author_id' => 'author-1',
        'published_at' => 1700000000,
    ]));

    expect($result)->toBe('hybrid');
    Bus::assertNotDispatched(PushPostToFollowersChunkJob::class);
});
