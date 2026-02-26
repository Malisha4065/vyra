<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchPostReactionNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $postId,
        public readonly string $reactorId,
        public readonly string $reactionType,
    ) {}

    public function handle(
        PostRepositoryInterface $postRepository,
        CreateUserNotificationAction $createNotificationAction,
    ): void
    {
        $post = $postRepository->findById($this->postId);

        if ($post === null || $post->user_id === $this->reactorId) {
            return;
        }

        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $post->user_id,
            'type' => 'content.post_reacted',
            'title' => 'New reaction on your post',
            'body' => "Your post received a {$this->reactionType} reaction.",
            'data' => [
                'post_id' => $this->postId,
                'reaction_type' => $this->reactionType,
                'actor_user_id' => $this->reactorId,
            ],
        ]));
    }
}
