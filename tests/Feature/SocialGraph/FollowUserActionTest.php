<?php

use App\Domains\Identity\Models\User;
use App\Domains\Identity\Models\UserProfile;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use App\Domains\SocialGraph\Actions\FollowUserAction;
use App\Domains\SocialGraph\Data\FollowUserData;
use App\Domains\SocialGraph\Events\FollowRequestReceived;
use App\Domains\SocialGraph\Events\UserFollowed;
use App\Domains\SocialGraph\Exceptions\CannotFollowSelfException;
use App\Domains\SocialGraph\Models\FollowRequest;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRequestRepositoryInterface;
use Illuminate\Support\Facades\Event;

it('creates a direct follow for public accounts', function () {
    Event::fake([UserFollowed::class, FollowRequestReceived::class]);

    $follower = new User();
    $follower->forceFill(['id' => 'user-1']);

    $followee = new User();
    $followee->forceFill(['id' => 'user-2']);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->once()->with('user-1', 'user-2')->andReturn(false);
    $followRepository->shouldReceive('create')->once()->with('user-1', 'user-2');

    $followRequestRepository = mock(FollowRequestRepositoryInterface::class);
    $followRequestRepository->shouldReceive('hasPendingRequest')->never();
    $followRequestRepository->shouldReceive('create')->never();

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-2', 'user_id' => 'user-2', 'is_private' => false]);

    $profileRepository = mock(UserProfileRepositoryInterface::class);
    $profileRepository->shouldReceive('findByUserId')->once()->with('user-2')->andReturn($profile);

    $action = new FollowUserAction(
        $followRepository,
        $followRequestRepository,
        $blockRepository,
        $profileRepository,
    );

    $data = FollowUserData::from([
        'target_user_id' => 'user-2',
    ]);

    $result = $action($follower, $followee, $data);

    expect($result)->toBe('followed');

    Event::assertDispatched(UserFollowed::class);
    Event::assertNotDispatched(FollowRequestReceived::class);
});

it('creates a follow request for private accounts', function () {
    Event::fake([UserFollowed::class, FollowRequestReceived::class]);

    $follower = new User();
    $follower->forceFill(['id' => 'user-1']);

    $followee = new User();
    $followee->forceFill(['id' => 'user-2']);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRepository->shouldReceive('isFollowing')->once()->with('user-1', 'user-2')->andReturn(false);
    $followRepository->shouldReceive('create')->never();

    $followRequest = new FollowRequest();
    $followRequest->forceFill(['id' => 'fr-1', 'requester_id' => 'user-1', 'requestee_id' => 'user-2', 'status' => FollowRequest::STATUS_PENDING]);

    $followRequestRepository = mock(FollowRequestRepositoryInterface::class);
    $followRequestRepository->shouldReceive('hasPendingRequest')->once()->with('user-1', 'user-2')->andReturn(false);
    $followRequestRepository->shouldReceive('create')->once()->with('user-1', 'user-2')->andReturn($followRequest);

    $blockRepository = mock(BlockRepositoryInterface::class);
    $blockRepository->shouldReceive('eitherBlocked')->once()->with('user-1', 'user-2')->andReturn(false);

    $profile = new UserProfile();
    $profile->forceFill(['id' => 'profile-2', 'user_id' => 'user-2', 'is_private' => true]);

    $profileRepository = mock(UserProfileRepositoryInterface::class);
    $profileRepository->shouldReceive('findByUserId')->once()->with('user-2')->andReturn($profile);

    $action = new FollowUserAction(
        $followRepository,
        $followRequestRepository,
        $blockRepository,
        $profileRepository,
    );

    $data = FollowUserData::from([
        'target_user_id' => 'user-2',
    ]);

    $result = $action($follower, $followee, $data);

    expect($result)->toBe('requested');

    Event::assertDispatched(FollowRequestReceived::class);
    Event::assertNotDispatched(UserFollowed::class);
});

it('rejects self-follow attempts', function () {
    $user = new User();
    $user->forceFill(['id' => 'user-1']);

    $followRepository = mock(FollowRepositoryInterface::class);
    $followRequestRepository = mock(FollowRequestRepositoryInterface::class);
    $blockRepository = mock(BlockRepositoryInterface::class);
    $profileRepository = mock(UserProfileRepositoryInterface::class);

    $action = new FollowUserAction(
        $followRepository,
        $followRequestRepository,
        $blockRepository,
        $profileRepository,
    );

    $data = FollowUserData::from([
        'target_user_id' => 'user-1',
    ]);

    expect(fn () => $action($user, $user, $data))->toThrow(CannotFollowSelfException::class);
});
