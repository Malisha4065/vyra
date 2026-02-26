<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Feed\Actions\FanOutPostOnWriteAction;
use App\Domains\Feed\Data\FanOutPostOnWriteData;
use App\Domains\Content\Repositories\PostRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyFollowersOfPublishedPostJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $authorId,
    ) {}

    public function handle(
        PostRepositoryInterface $postRepository,
        FanOutPostOnWriteAction $fanOutPostOnWriteAction,
    ): void
    {
        $post = $postRepository->findById($this->postId);

        if ($post === null) {
            return;
        }

        $fanOutPostOnWriteAction(FanOutPostOnWriteData::from([
            'post_id' => $post->id,
            'author_id' => $this->authorId,
            'published_at' => $post->published_at?->getTimestamp() ?? now()->getTimestamp(),
        ]));
    }
}
