<?php

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\MuteRepositoryInterface;

it('merges cached and hybrid feed entries ordered by score', function () {
    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('getUserFeed')
        ->once()
        ->with('user-1', 12, null)
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
        ->with(['author-9'], 10, null)
        ->andReturn([
            ['post_id' => 'post-3', 'score' => 500, 'author_id' => 'author-9'],
            ['post_id' => 'post-2', 'score' => 350, 'author_id' => 'author-9'],
        ]);

    $postThree = new Post();
    $postThree->forceFill([
        'id' => 'post-3',
        'user_id' => 'author-9',
        'body' => 'Hybrid post',
    ]);

    $postOne = new Post();
    $postOne->forceFill([
        'id' => 'post-1',
        'user_id' => 'author-1',
        'body' => 'Cached post',
    ]);

    $postTwo = new Post();
    $postTwo->forceFill([
        'id' => 'post-2',
        'user_id' => 'author-9',
        'body' => 'Upgraded post',
    ]);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findByIds')
        ->once()
        ->with(['post-3', 'post-1', 'post-2'])
        ->andReturn([$postThree, $postOne, $postTwo]);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'author-9')->andReturn(false);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'author-1')->andReturn(false);

    $muteRepository = mock(MuteRepositoryInterface::class);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-9')->andReturn(false);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-1')->andReturn(false);

    $action = new GetUserFeedAction(
        $feedCacheRepository,
        $hybridFeedRepository,
        $followRepository,
        $postRepository,
        $blockRepository,
        $muteRepository,
    );

    $result = $action(GetUserFeedData::from([
        'user_id' => 'user-1',
        'limit' => 4,
    ]));

    expect($result->items[0]->post_id)->toBe('post-3');
    expect($result->items[0]->post->body)->toBe('Hybrid post');
    expect($result->items[1]->post_id)->toBe('post-1');
    expect($result->items[2]->post_id)->toBe('post-2');

    expect($result->next_cursor)->toBe(350);
});

it('uses ranked cursor when stale ids reduce hydrated result size', function () {
    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('getUserFeed')
        ->once()
        ->with('user-1', 9, null)
        ->andReturn([
            ['post_id' => 'post-9', 'score' => 900],
            ['post_id' => 'post-8', 'score' => 800],
            ['post_id' => 'post-7', 'score' => 700],
        ]);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowingIds')->once()->with('user-1')->andReturn([]);

    $hybridFeedRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridFeedRepository->shouldReceive('filterHighFollowerAuthors')->once()->with([])->andReturn([]);
    $hybridFeedRepository->shouldReceive('getRecentPostsForHighFollowerAuthors')
        ->once()
        ->with([], 10, null)
        ->andReturn([]);

    $postNine = new Post();
    $postNine->forceFill([
        'id' => 'post-9',
        'user_id' => 'author-1',
        'body' => 'Only hydrated post',
    ]);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findByIds')
        ->once()
        ->with(['post-9', 'post-8', 'post-7'])
        ->andReturn([$postNine]); // post-8 and post-7 behave like stale/deleted ids

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'author-1')->andReturn(false);

    $muteRepository = mock(MuteRepositoryInterface::class);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-1')->andReturn(false);

    $action = new GetUserFeedAction(
        $feedCacheRepository,
        $hybridFeedRepository,
        $followRepository,
        $postRepository,
        $blockRepository,
        $muteRepository,
    );

    $result = $action(GetUserFeedData::from([
        'user_id' => 'user-1',
        'limit' => 3,
    ]));

    expect($result->items)->toHaveCount(1);
    expect($result->items[0]->post_id)->toBe('post-9');
    expect($result->next_cursor)->toBe(700);
});

it('filters out muted and blocked authors from hydrated feed items', function () {
    $feedCacheRepository = mock(FeedCacheRepositoryInterface::class);
    $feedCacheRepository->shouldReceive('getUserFeed')
        ->once()
        ->with('user-1', 9, null)
        ->andReturn([
            ['post_id' => 'post-a', 'score' => 900],
            ['post_id' => 'post-b', 'score' => 800],
            ['post_id' => 'post-c', 'score' => 700],
        ]);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('getFollowingIds')->once()->with('user-1')->andReturn([]);

    $hybridFeedRepository = mock(HybridFeedRepositoryInterface::class);
    $hybridFeedRepository->shouldReceive('filterHighFollowerAuthors')->once()->with([])->andReturn([]);
    $hybridFeedRepository->shouldReceive('getRecentPostsForHighFollowerAuthors')
        ->once()
        ->with([], 10, null)
        ->andReturn([]);

    $postA = new Post();
    $postA->forceFill(['id' => 'post-a', 'user_id' => 'author-muted', 'body' => 'Muted']);

    $postB = new Post();
    $postB->forceFill(['id' => 'post-b', 'user_id' => 'author-blocked', 'body' => 'Blocked']);

    $postC = new Post();
    $postC->forceFill(['id' => 'post-c', 'user_id' => 'author-visible', 'body' => 'Visible']);

    $postRepository = mock(PostRepositoryInterface::class);
    $postRepository->shouldReceive('findByIds')
        ->once()
        ->with(['post-a', 'post-b', 'post-c'])
        ->andReturn([$postA, $postB, $postC]);

    $muteRepository = mock(MuteRepositoryInterface::class);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-muted')->andReturn(true);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-blocked')->andReturn(false);
    $muteRepository->shouldReceive('isMuting')->once()->with('user-1', 'author-visible')->andReturn(false);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->never()->with('user-1', 'author-muted');
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'author-blocked')->andReturn(true);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'author-visible')->andReturn(false);

    $action = new GetUserFeedAction(
        $feedCacheRepository,
        $hybridFeedRepository,
        $followRepository,
        $postRepository,
        $blockRepository,
        $muteRepository,
    );

    $result = $action(GetUserFeedData::from([
        'user_id' => 'user-1',
        'limit' => 3,
    ]));

    expect($result->items)->toHaveCount(1);
    expect($result->items[0]->post_id)->toBe('post-c');
    expect($result->next_cursor)->toBe(700);
});
