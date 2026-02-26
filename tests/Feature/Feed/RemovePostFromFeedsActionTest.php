<?php

use App\Domains\Feed\Actions\RemovePostFromFeedsAction;
use App\Domains\Feed\Data\RemovePostFromFeedsData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

it('removes post from follower feeds for regular accounts', function () {
    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(100);
    $followRepository->shouldReceive('getFollowerIds')->once()->with('author-1')->andReturn(['u1', 'u2']);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('removePostFromUserFeed')->once()->with('author-1', 'post-1');
    $feedCacheRepository->shouldReceive('removePostFromUserFeeds')->once()->with(['u1', 'u2'], 'post-1');

    $hybridRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridRepository->shouldReceive('removePostForHighFollowerAuthor')->never();

    $action = new RemovePostFromFeedsAction($followRepository, $feedCacheRepository, $hybridRepository);

    $action(RemovePostFromFeedsData::from([
        'post_id' => 'post-1',
        'author_id' => 'author-1',
    ]));

    expect(true)->toBeTrue();
});

it('removes post from hybrid author stream for high follower accounts', function () {
    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(10000);
    $followRepository->shouldReceive('getFollowerIds')->never();

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('removePostFromUserFeed')->once()->with('author-1', 'post-1');
    $feedCacheRepository->shouldReceive('removePostFromUserFeeds')->never();

    $hybridRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridRepository->shouldReceive('removePostForHighFollowerAuthor')->once()->with('author-1', 'post-1');

    $action = new RemovePostFromFeedsAction($followRepository, $feedCacheRepository, $hybridRepository);

    $action(RemovePostFromFeedsData::from([
        'post_id' => 'post-1',
        'author_id' => 'author-1',
    ]));

    expect(true)->toBeTrue();
});
