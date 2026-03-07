<?php

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Actions\EmitTypingIndicatorAction;
use App\Domains\Communication\Actions\ListUserConversationsAction;
use App\Domains\Communication\Actions\MarkConversationReadAction;
use App\Domains\Communication\Actions\StartDirectConversationAction;
use App\Domains\Communication\Data\EmitTypingIndicatorData;
use App\Domains\Communication\Data\ListUserConversationsData;
use App\Domains\Communication\Data\MarkConversationReadData;
use App\Domains\Communication\Data\StartDirectConversationData;
use App\Domains\Communication\Exceptions\CannotMessageSelfException;
use App\Domains\Communication\Exceptions\DirectConversationBlockedException;
use App\Domains\Communication\Exceptions\EmptyMessageException;
use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index(
        ListUserConversationsAction $action,
    ): JsonResponse {
        $data = ListUserConversationsData::from(request()->all());

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $conversations = $action($user, $data);
        $items = array_map(function ($conversation): array {
            if ($conversation instanceof Conversation) {
                return $this->serializeConversation($conversation);
            }

            return (array) $conversation;
        }, $conversations->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $conversations->total(),
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
            ],
        ]);
    }

    public function startDirect(
        StartDirectConversationData $data,
        StartDirectConversationAction $action,
        UserRepositoryInterface $userRepository,
    ): JsonResponse {
        $targetUser = $userRepository->findById($data->target_user_id);

        abort_if($targetUser === null, 404);

        $this->authorize('createDirect', [Conversation::class, $targetUser]);

        try {
            /** @var \App\Domains\Identity\Models\User $user */
            $user = Auth::user();
            $conversation = $action($user, $targetUser, $data);
        } catch (CannotMessageSelfException|DirectConversationBlockedException|EmptyMessageException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => [
                'conversation_id' => $conversation->id,
            ],
        ], 201);
    }

    public function markRead(
        string $conversation,
        MarkConversationReadAction $action,
        ConversationRepositoryInterface $conversationRepository,
    ): JsonResponse {
        $data = MarkConversationReadData::from([
            'conversation_id' => $conversation,
        ]);

        $conversationModel = $conversationRepository->findById($data->conversation_id);

        abort_if($conversationModel === null, 404);

        $this->authorize('markRead', $conversationModel);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $readCount = $action($user, $conversationModel, $data);

        return response()->json([
            'data' => [
                'marked_count' => $readCount,
            ],
        ]);
    }

    public function typing(
        string $conversation,
        EmitTypingIndicatorAction $action,
        ConversationRepositoryInterface $conversationRepository,
    ): JsonResponse {
        $data = EmitTypingIndicatorData::from([
            ...request()->all(),
            'conversation_id' => $conversation,
        ]);

        $conversationModel = $conversationRepository->findById($data->conversation_id);

        abort_if($conversationModel === null, 404);

        $this->authorize('emitTyping', $conversationModel);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $action($user, $conversationModel, $data);

        return response()->json([
            'data' => [
                'is_typing' => $data->is_typing,
            ],
        ], 202);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeConversation(Conversation $conversation): array
    {
        /** @var Message|null $latestMessage */
        $latestMessage = $conversation->latestMessage;

        return [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'updated_at' => $conversation->updated_at?->toIso8601String(),
            'participants' => $conversation->participants->map(static function ($participant): array {
                $lastReadAt = $participant->pivot?->last_read_at;

                return [
                    'id' => $participant->id,
                    'username' => $participant->username,
                    'read_state' => [
                        'last_read_message_id' => $participant->pivot?->last_read_message_id,
                        'last_read_at' => $lastReadAt instanceof \DateTimeInterface
                            ? $lastReadAt->format(DATE_ATOM)
                            : $lastReadAt,
                    ],
                ];
            })->values()->all(),
            'latest_message' => $latestMessage ? [
                'id' => $latestMessage->id,
                'body' => $latestMessage->body,
                'sender_id' => $latestMessage->sender_id,
                'created_at' => $latestMessage->created_at?->toIso8601String(),
                'sender' => [
                    'id' => $latestMessage->sender?->id,
                    'username' => $latestMessage->sender?->username,
                ],
            ] : null,
        ];
    }
}
