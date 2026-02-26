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

        return response()->json([
            'data' => $conversations->items(),
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
}
