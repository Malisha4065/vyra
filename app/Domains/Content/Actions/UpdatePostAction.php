<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\UpdatePostData;
use App\Domains\Content\Events\PostUpdated;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Identity\Models\User;

class UpdatePostAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @throws EmptyPostException
     */
    public function __invoke(User $editor, Post $post, UpdatePostData $data): Post
    {
        $body = $data->normalizedBody();

        if ($body === '') {
            throw new EmptyPostException();
        }

        $updated = $this->postRepository->updateBody($post, $body);

        event(new PostUpdated($updated, $editor));

        return $updated;
    }
}
