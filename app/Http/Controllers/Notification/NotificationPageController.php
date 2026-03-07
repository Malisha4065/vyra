<?php

namespace App\Http\Controllers\Notification;

use App\Domains\Notification\Models\UserNotificationPreference;
use App\Domains\Notification\Repositories\NotificationPreferenceRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPageController extends Controller
{
    public function index(NotificationPreferenceRepositoryInterface $preferenceRepository): Response
    {
        $this->authorize('view', UserNotificationPreference::class);

        /** @var \App\Domains\Identity\Models\User $user */
        $user = Auth::user();
        $preference = $preferenceRepository->firstOrCreateByUserId($user->id);

        return Inertia::render('Notification/Index', [
            'preferences' => [
                'social_enabled' => $preference->social_enabled,
                'content_enabled' => $preference->content_enabled,
                'communication_enabled' => $preference->communication_enabled,
                'account_enabled' => $preference->account_enabled,
            ],
        ]);
    }
}
