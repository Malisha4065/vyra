<?php

namespace App\Domains\Notification\Actions;

use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\Notification\Models\UserNotification;

class BuildUserNotificationPayloadAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function __invoke(UserNotification $notification): array
    {
        $action = $this->resolveAction($notification);

        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data ?? [],
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'action_url' => $action['url'],
            'action_label' => $action['label'],
        ];
    }

    /**
     * @return array{url:?string,label:?string}
     */
    private function resolveAction(UserNotification $notification): array
    {
        $data = $notification->data ?? [];

        if (isset($data['action_url'])) {
            return [
                'url' => $data['action_url'],
                'label' => $data['action_label'] ?? 'Open',
            ];
        }

        if (str_starts_with($notification->type, 'content.') && isset($data['post_id'])) {
            return [
                'url' => route('feed', ['focus_post_id' => $data['post_id']]),
                'label' => 'View post',
            ];
        }

        if ($notification->type === 'social.follow_request_received') {
            return [
                'url' => route('follow-requests.index'),
                'label' => 'Review request',
            ];
        }

        if (str_starts_with($notification->type, 'communication.') && isset($data['conversation_id'])) {
            return [
                'url' => route('messages.index', ['conversation_id' => $data['conversation_id']]),
                'label' => 'Open conversation',
            ];
        }

        $username = $this->resolveUsername(
            $data['actor_user_id']
                ?? $data['target_user_id']
                ?? $data['muted_user_id']
                ?? $data['blocked_user_id']
                ?? null,
        );

        if ($username !== null) {
            return [
                'url' => route('profile.show', ['username' => $username]),
                'label' => 'View profile',
            ];
        }

        return [
            'url' => null,
            'label' => null,
        ];
    }

    private function resolveUsername(?string $userId): ?string
    {
        if ($userId === null || $userId === '') {
            return null;
        }

        return $this->userRepository->findById($userId)?->username;
    }
}
