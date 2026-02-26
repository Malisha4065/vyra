<?php

use App\Domains\Identity\Models\User;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Policies\FollowRequestPolicy;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

it('allows requestee to accept pending requests when not blocked', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-2']);

    $request = new FollowRequest();
    $request->forceFill([
        'id' => 'fr-1',
        'requester_id' => 'user-1',
        'requestee_id' => 'user-2',
        'status' => FollowRequest::STATUS_PENDING,
    ]);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $policy = new FollowRequestPolicy($blockRepository);

    expect($policy->accept($authUser, $request))->toBeTrue();
});

it('denies accept when users are blocked', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-2']);

    $request = new FollowRequest();
    $request->forceFill([
        'id' => 'fr-1',
        'requester_id' => 'user-1',
        'requestee_id' => 'user-2',
        'status' => FollowRequest::STATUS_PENDING,
    ]);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(true);

    $policy = new FollowRequestPolicy($blockRepository);

    expect($policy->accept($authUser, $request))->toBeFalse();
});

it('allows requestee to reject pending requests', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-2']);

    $request = new FollowRequest();
    $request->forceFill([
        'id' => 'fr-1',
        'requester_id' => 'user-1',
        'requestee_id' => 'user-2',
        'status' => FollowRequest::STATUS_PENDING,
    ]);

    $blockRepository = mock(BlockRepositoryInterface::class);

    $policy = new FollowRequestPolicy($blockRepository);

    expect($policy->reject($authUser, $request))->toBeTrue();
});
