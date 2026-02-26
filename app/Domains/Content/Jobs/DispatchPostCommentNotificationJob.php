<?php

namespace App\Domains\Content\Jobs;

use App\Domains\Content\Repositories\PostRepositoryInterface;
use App\Domains\Notification\Actions\CreateUserNotificationAction;
use App\Domains\Notification\Data\CreateUserNotificationData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchPostCommentNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $commentId,
        public readonly string $postId,
        public readonly string $authorId,
    ) {}

    public function handle(
        PostRepositoryInterface $postRepository,
        CreateUserNotificationAction $createNotificationAction,
    ): void
    {
        $post = $postRepository->findById($this->postId);

        if ($post === null || $post->user_id === $this->authorId) {
            return;
        }

        $createNotificationAction(CreateUserNotificationData::from([
            'user_id' => $post->user_id,
            'type' => 'content.post_commented',
            'title' => 'New comment on your post',
            'body' => 'Someone commented on your post.',
            'data' => [
                'post_id' => $this->postId,
                'comment_id' => $this->commentId,
                'actor_user_id' => $this->authorId,
            ],
        ]));
    }
}
