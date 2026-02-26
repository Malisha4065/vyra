<?php

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

it('merges cached and hybrid feed entries ordered by score', function () {
    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('getUserFeed')
        ->once()
        ->with('user-1', 4, null)
        ->andReturn([
            ['post_id' => 'post-1', 'score' => 400],
            ['post_id' => 'post-2', 'score' => 300],
        ]);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowingIds')->once()->with('user-1')->andReturn(['author-9']);

    $hybridFeedRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridFeedRepository->shouldReceive('filterHighFollowerAuthors')->once()->with(['author-9'])->andReturn(['author-9']);
    $hybridFeedRepository->shouldReceive('getRecentPostsForHighFollowerAuthors')
        ->once()
        ->with(['author-9'], 5, null)
        ->andReturn([
            ['post_id' => 'post-3', 'score' => 500, 'author_id' => 'author-9'],
            ['post_id' => 'post-2', 'score' => 350, 'author_id' => 'author-9'],
        ]);

    $action = new GetUserFeedAction($feedCacheRepository, $hybridFeedRepository, $followRepository);

    $result = $action(GetUserFeedData::from([
        'user_id' => 'user-1',
        'limit' => 4,
    ]));

    expect($result['items'])->toBe([
        ['post_id' => 'post-3', 'score' => 500],
        ['post_id' => 'post-1', 'score' => 400],
        ['post_id' => 'post-2', 'score' => 350],
    ]);

    expect($result['next_cursor'])->toBe(350);
});
