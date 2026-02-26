<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Policies\UserProfilePolicy;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

it('denies viewing when a block relationship exists', function () {
    $viewer = new User();
    $viewer->forceFill(['id' => 'user-1']);

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-2', 'user_id' => 'user-2', 'is_private' => false]);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->never();

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(true);

    $policy = new UserProfilePolicy($followRepository, $blockRepository);

    expect($policy->view($viewer, $profile))->toBeFalse();
});

it('requires follow relationship for private profiles', function () {
    $viewer = new User();
    $viewer->forceFill(['id' => 'user-1']);

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-2', 'user_id' => 'user-2', 'is_private' => true]);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->once()->with('user-1', 'user-2')->andReturn(true);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $policy = new UserProfilePolicy($followRepository, $blockRepository);

    expect($policy->view($viewer, $profile))->toBeTrue();
});
