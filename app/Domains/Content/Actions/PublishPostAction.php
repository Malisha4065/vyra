<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\PublishPostData;
use App\Domains\Content\Events\PostPublished;
use App\Domains\Content\Exceptions\EmptyPostException;
use App\Domains\Content\Models\Post;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Identity\Models\User;

class PublishPostAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @throws EmptyPostException
     */
    public function __invoke(User $author, PublishPostData $data): Post
    {
        $body = $data->normalizedBody();

        if ($body === null && $data->media === []) {
            throw new EmptyPostException();
        }

        $post = $this->postRepository->publish(
            userId: $author->id,
            body: $body,
            media: $data->media,
        );

        event(new PostPublished($post, $author));

        return $post;
    }
}
