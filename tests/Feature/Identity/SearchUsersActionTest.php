<?php

use App\Domains\Identity\Actions\BuildUserSearchPayloadAction;
use App\Domains\Identity\Actions\SearchUsersAction;
use App\Domains\Identity\Data\SearchUsersData;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

it('filters self and blocked users from search results', function () {
    $viewer = new User();
    $viewer->forceFill(['id' => 'user-1', 'username' => 'viewer']);
    $viewer->setRelation('profile', null);

    $visible = new User();
    $visible->forceFill(['id' => 'user-2', 'username' => 'alice']);
    $visible->setRelation('profile', null);

    $blocked = new User();
    $blocked->forceFill(['id' => 'user-3', 'username' => 'blocked']);
    $blocked->setRelation('profile', null);

    $repository = mock(UserRepositoryInterface::class);
    $repository->shouldReceive('searchDiscoverable')
        ->once()
        ->with('al', 10)
        ->andReturn([$viewer, $visible, $blocked]);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-3')->andReturn(true);

    $action = new SearchUsersAction($repository, $blockRepository, new BuildUserSearchPayloadAction());
    $result = $action(SearchUsersData::from([
        'viewer_id' => 'user-1',
        'query' => 'al',
    ]));

    expect($result)->toHaveCount(1);
    expect($result[0]['username'])->toBe('alice');
});
