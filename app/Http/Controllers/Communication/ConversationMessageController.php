<?php

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Actions\ListConversationMessagesAction;
use App\Domains\Communication\Actions\SendMessageAction;
use App\Domains\Communication\Data\ListConversationMessagesData;
use App\Domains\Communication\Data\SendMessageData;
use App\Domains\Communication\Exceptions\EmptyMessageException;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Repositories\ConversationRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ConversationMessageController extends Controller
{
    public function index(
        string $conversation,
        ListConversationMessagesAction $action,
        ConversationRepositoryInterface $conversationRepository,
    ): JsonResponse {
        $data = ListConversationMessagesData::from([
            ...request()->all(),
            'conversation_id' => $conversation,
        ]);

        $conversationModel = $conversationRepository->findById($data->conversation_id);

        abort_if($conversationModel === null, 404);

        $this->authorize('view', $conversationModel);

        $messages = $action($data);
        $items = array_map(function ($message): array {
            if ($message instanceof Message) {
                return $this->serializeMessage($message);
            }

            return (array) $message;
        }, $messages->items());

        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $messages->total(),
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
            ],
        ]);
    }

    public function store(
        string $conversation,
        SendMessageAction $action,
        ConversationRepositoryInterface $conversationRepository,
    ): JsonResponse {
        $data = SendMessageData::from([
            ...request()->all(),
            'conversation_id' => $conversation,
        ]);

        $conversationModel = $conversationRepository->findById($data->conversation_id);

        abort_if($conversationModel === null, 404);

        $this->authorize('sendMessage', $conversationModel);

        try {
            /** @var \App\Domains\Identity\Models\User $user */
            $user = Auth::user();
            $message = $action($user, $conversationModel, $data);
        } catch (EmptyMessageException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'data' => [
                'message_id' => $message->id,
            ],
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'metadata' => $message->metadata ?? [],
            'created_at' => $message->created_at?->toIso8601String(),
            'sender' => [
                'id' => $message->sender?->id,
                'username' => $message->sender?->username,
            ],
            'read_receipts' => $message->readReceipts
                ->map(static fn ($receipt): array => [
                    'user_id' => $receipt->user_id,
                    'read_at' => $receipt->read_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        ];
    }
}
