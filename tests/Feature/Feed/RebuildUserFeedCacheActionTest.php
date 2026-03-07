<?php

use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Feed\Actions\RebuildUserFeedCacheAction;
use App\Domains\Feed\Data\RebuildUserFeedCacheData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

it('rebuilds the cached feed from regular followed authors and self posts', function () {
    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowingIds')->once()->with('user-1')->andReturn(['author-1', 'author-2']);

    $hybridRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridRepository->shouldReceive('filterHighFollowerAuthors')->once()->with(['author-1', 'author-2'])->andReturn(['author-2']);

    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('clearUserFeed')->once()->with('user-1');
    $feedCacheRepository->shouldReceive('addPostToUserFeed')->once()->with('user-1', 'post-1', 1200);
    $feedCacheRepository->shouldReceive('addPostToUserFeed')->once()->with('user-1', 'post-2', 1100);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('getRecentPublishedPostIdsByAuthors')
        ->once()
        ->with(['user-1', 'author-1'], 500)
        ->andReturn([
            ['post_id' => 'post-1', 'score' => 1200],
            ['post_id' => 'post-2', 'score' => 1100],
        ]);

    $action = new RebuildUserFeedCacheAction(
        $followRepository,
        $feedCacheRepository,
        $hybridRepository,
        $postRepository,
    );

    $result = $action(RebuildUserFeedCacheData::from([
        'user_id' => 'user-1',
    ]));

    expect($result)->toBe(2);
});
