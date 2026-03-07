<?php

namespace App\Http\Controllers\Notification;

use App\Domains\Notification\Actions\UpdateUserNotificationPreferencesAction;
use App\Domains\Notification\Data\UpdateUserNotificationPreferencesData;
use App\Domains\Notification\Models\UserNotificationPreference;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    public function update(
        Request $request,
        UpdateUserNotificationPreferencesData $data,
        UpdateUserNotificationPreferencesAction $action,
    ): JsonResponse|RedirectResponse {
        $this->authorize('update', UserNotificationPreference::class);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();

        $preference = $action($user, $data);

        $payload = [
            'data' => [
                'social_enabled' => $preference->social_enabled,
                'content_enabled' => $preference->content_enabled,
                'communication_enabled' => $preference->communication_enabled,
                'account_enabled' => $preference->account_enabled,
            ],
        ];

        if ($request->expectsJson()) {
            return response()->json($payload);
        }

        return back()->with('success', 'Notification preferences updated.');
    }
}
