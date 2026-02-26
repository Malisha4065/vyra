<?php

use App\Domains\Feed\Actions\RemovePostFromFeedsAction;
use App\Domains\Feed\Data\RemovePostFromFeedsData;
use App\Domains\Feed\Jobs\RemovePostFromFollowersChunkJob;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use Illuminate\Support\Facades\Bus;

it('removes post from follower feeds for regular accounts', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(100);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('removePostFromUserFeed')->once()->with('author-1', 'post-1');
    $feedCacheRepository->shouldReceive('removePostFromUserFeeds')->never();

    $hybridRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridRepository->shouldReceive('removePostForHighFollowerAuthor')->never();

    $action = new RemovePostFromFeedsAction($followRepository, $feedCacheRepository, $hybridRepository);

    $action(RemovePostFromFeedsData::from([
        'post_id' => 'post-1',
        'author_id' => 'author-1',
    ]));

    expect(true)->toBeTrue();
    Bus::assertDispatched(RemovePostFromFollowersChunkJob::class);
});

it('removes post from hybrid author stream for high follower accounts', function () {
    Bus::fake();

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('followersCount')->once()->with('author-1')->andReturn(10000);

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
    Bus::assertNotDispatched(RemovePostFromFollowersChunkJob::class);
});
