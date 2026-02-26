<?php

namespace App\Http\Controllers\Communication;

use App\Domains\Communication\Actions\ListConversationMessagesAction;
use App\Domains\Communication\Actions\SendMessageAction;
use App\Domains\Communication\Data\ListConversationMessagesData;
use App\Domains\Communication\Data\SendMessageData;
use App\Domains\Communication\Exceptions\EmptyMessageException;
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

        return response()->json([
            'data' => $messages->items(),
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
}
