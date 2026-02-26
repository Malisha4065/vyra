<?php

use App\Domains\Content\Models\Comment;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Policies\CommentPolicy;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

it('denies comment creation when users are blocked', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(true);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->never();

    $profileRepository = mock(UserProfileRepositoryInterface::class);
    $profileRepository->shouldReceive('findByUserId')->never();

    $policy = new CommentPolicy($blockRepository, $followRepository, $profileRepository);

    expect($policy->create($authUser, $post))->toBeFalse();
});

it('allows comment deletion by comment owner', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $comment = new Comment();
    $comment->forceFill(['id' => 'comment-1', 'user_id' => 'user-1']);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $followRepository = mock(FollowRepositoryInterface::class);
    $profileRepository = mock(UserProfileRepositoryInterface::class);

    $policy = new CommentPolicy($blockRepository, $followRepository, $profileRepository);

    expect($policy->delete($authUser, $comment))->toBeTrue();
});

it('requires follow for private post owner when commenting', function () {
    $authUser = new User();
    $authUser->forceFill(['id' => 'user-1']);

    $post = new Post();
    $post->forceFill(['id' => 'post-1', 'user_id' => 'user-2']);

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-2', 'user_id' => 'user-2', 'is_private' => true]);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->once()->with('user-1', 'user-2')->andReturn(true);

    $profileRepository = mock(UserProfileRepositoryInterface::class);
    $profileRepository->shouldReceive('findByUserId')->once()->with('user-2')->andReturn($profile);

    $policy = new CommentPolicy($blockRepository, $followRepository, $profileRepository);

    expect($policy->create($authUser, $post))->toBeTrue();
});
