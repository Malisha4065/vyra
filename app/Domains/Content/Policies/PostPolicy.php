<?php

namespace App\Domains\Content\Policies;

use App\Domains\Content\Models\Post;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\UserProfileRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;
use App\Domains\SocialGraph\Repositories\FollowRepositoryInterface;

class PostPolicy
{
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly FollowRepositoryInterface $followRepository,
        private readonly UserProfileRepositoryInterface $profileRepository,
    ) {}

    public function create(User $authUser): bool
    {
        return $authUser->id !== '';
    }

    public function view(User $authUser, Post $post): bool
    {
        if ($authUser->id === $post->user_id) {
            return true;
        }

        if ($this->blockRepository->eitherBlocked($authUser->id, $post->user_id)) {
            return false;
        }

        $profile = $this->profileRepository->findByUserId($post->user_id);

        if ($profile === null || ! $profile->is_private) {
            return true;
        }

        return $this->followRepository->isFollowing($authUser->id, $post->user_id);
    }

    public function update(User $authUser, Post $post): bool
    {
        return $authUser->id === $post->user_id;
    }

    public function delete(User $authUser, Post $post): bool
    {
        return $authUser->id === $post->user_id;
    }
}
