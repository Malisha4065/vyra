<?php

namespace App\Http\Controllers\Notification;

use App\Domains\Notification\Actions\ListUserNotificationsAction;
use App\Domains\Notification\Actions\BuildUserNotificationPayloadAction;
use App\Domains\Notification\Actions\MarkAllNotificationsReadAction;
use App\Domains\Notification\Actions\MarkNotificationReadAction;
use App\Domains\Notification\Data\ListUserNotificationsData;
use App\Domains\Notification\Data\MarkAllNotificationsReadData;
use App\Domains\Notification\Data\MarkNotificationReadData;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(
        ListUserNotificationsAction $action,
        UserNotificationRepositoryInterface $notificationRepository,
        BuildUserNotificationPayloadAction $buildPayload,
    ): JsonResponse {
        $this->authorize('viewAny', UserNotification::class);

        $data = ListUserNotificationsData::from(request()->all());

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $notifications = $action($user, $data);

        return response()->json([
            'data' => array_map(function ($notification) use ($buildPayload): array {
                if ($notification instanceof UserNotification) {
                    return $buildPayload($notification);
                }

                return (array) $notification;
            }, $notifications->items()),
            'meta' => [
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'unread_total' => $notificationRepository->unreadCount($user->id),
            ],
        ]);
    }

    public function markRead(
        Request $request,
        string $notification,
        MarkNotificationReadAction $action,
        UserNotificationRepositoryInterface $notificationRepository,
        BuildUserNotificationPayloadAction $buildPayload,
    ): JsonResponse|RedirectResponse {
        $data = MarkNotificationReadData::from([
            'notification_id' => $notification,
        ]);

        $notificationModel = $notificationRepository->findById($data->notification_id);

        abort_if($notificationModel === null, 404);

        $this->authorize('markRead', $notificationModel);

        $updatedNotification = $action($notificationModel, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $buildPayload($updatedNotification),
                'meta' => [
                    'unread_total' => $notificationRepository->unreadCount($updatedNotification->user_id),
                ],
            ]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(
        Request $request,
        MarkAllNotificationsReadAction $action,
        UserNotificationRepositoryInterface $notificationRepository,
    ): JsonResponse|RedirectResponse {
        $this->authorize('markAllRead', UserNotification::class);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = MarkAllNotificationsReadData::from([]);

        $updated = $action($user, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'marked_count' => $updated,
                ],
                'meta' => [
                    'unread_total' => $notificationRepository->unreadCount($user->id),
                ],
            ]);
        }

        return back()->with('success', "{$updated} notifications marked as read.");
    }
}
