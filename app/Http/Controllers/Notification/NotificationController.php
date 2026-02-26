<?php

namespace App\Http\Controllers\Notification;

use App\Domains\Notification\Actions\ListUserNotificationsAction;
use App\Domains\Notification\Actions\MarkAllNotificationsReadAction;
use App\Domains\Notification\Actions\MarkNotificationReadAction;
use App\Domains\Notification\Data\ListUserNotificationsData;
use App\Domains\Notification\Data\MarkAllNotificationsReadData;
use App\Domains\Notification\Data\MarkNotificationReadData;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(
        ListUserNotificationsAction $action,
    ): JsonResponse {
        $this->authorize('viewAny', UserNotification::class);

        $data = ListUserNotificationsData::from(request()->all());

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $notifications = $action($user, $data);

        return response()->json([
            'data' => $notifications->items(),
            'meta' => [
                'total' => $notifications->total(),
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
            ],
        ]);
    }

    public function markRead(
        string $notification,
        MarkNotificationReadAction $action,
        UserNotificationRepositoryInterface $notificationRepository,
    ): RedirectResponse {
        $data = MarkNotificationReadData::from([
            'notification_id' => $notification,
        ]);

        $notificationModel = $notificationRepository->findById($data->notification_id);

        abort_if($notificationModel === null, 404);

        $this->authorize('markRead', $notificationModel);

        $action($notificationModel, $data);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead(
        MarkAllNotificationsReadAction $action,
    ): RedirectResponse {
        $this->authorize('markAllRead', UserNotification::class);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $data = MarkAllNotificationsReadData::from([]);

        $updated = $action($user, $data);

        return back()->with('success', "{$updated} notifications marked as read.");
    }
}
