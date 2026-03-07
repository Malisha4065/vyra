<?php

use App\Domains\Content\Actions\DiscoverContentAction;
use App\Domains\Content\Data\SearchContentData;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;

it('searches published posts by query', function () {
    $post = new Post();
    $post->forceFill([
        'id' => 'post-1',
        'body' => 'Laravel search',
    ]);

    $author = new \App\Domains\Identity\Models\User();
    $author->forceFill(['id' => 'user-1', 'username' => 'alice']);
    $post->setRelation('author', $author);
    $post->setRelation('comments', collect());
    $post->setRelation('reactions', collect());
    $post->setRelation('media', collect());

    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('searchPublished')->once()->with('laravel', 20)->andReturn([$post]);
    $repository->shouldReceive('getTrendingHashtags')->once()->with(8)->andReturn([['tag' => 'laravel', 'count' => 3]]);
    $repository->shouldReceive('findPublishedByHashtag')->never();

    $action = new DiscoverContentAction($repository);

    $result = $action(SearchContentData::from([
        'query' => 'laravel',
    ]));

    expect($result['results'][0]['id'])->toBe('post-1');
    expect($result['trending_hashtags'][0]['tag'])->toBe('laravel');
});

it('treats hash-prefixed queries as hashtag discovery', function () {
    $repository = mock(PostRepositoryInterface::class);
    $repository->shouldReceive('findPublishedByHashtag')->once()->with('vyra', 20)->andReturn([]);
    $repository->shouldReceive('getTrendingHashtags')->once()->with(8)->andReturn([]);
    $repository->shouldReceive('searchPublished')->never();

    $action = new DiscoverContentAction($repository);

    $action(SearchContentData::from([
        'query' => '#Vyra',
    ]));

    expect(true)->toBeTrue();
});
