<?php

use App\Domains\Feed\Actions\GetUserFeedAction;
use App\Domains\Feed\Data\GetUserFeedData;
use App\Domains\Feed\Repositories\FeedCacheRepositoryInterface;
use App\Domains\Feed\Repositories\HybridFeedRepositoryInterface;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
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

    $action = new GetUserFeedAction($feedCacheRepository, $hybridFeedRepository, $followRepository, $postRepository);

    $result = $action(GetUserFeedData::from([
        'user_id' => 'user-1',
        'limit' => 4,
    ]));

    expect($result['items'][0]['post_id'])->toBe('post-3');
    expect($result['items'][0]['post']['body'])->toBe('Hybrid post');
    expect($result['items'][1]['post_id'])->toBe('post-1');
    expect($result['items'][2]['post_id'])->toBe('post-2');

    expect($result['next_cursor'])->toBe(350);
});
