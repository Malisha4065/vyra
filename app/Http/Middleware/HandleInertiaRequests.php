<?php

namespace App\Http\Middleware;

use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'username' => $request->user()->username,
                    'email' => $request->user()->email,
                    'email_verified_at' => $request->user()->email_verified_at?->toIso8601String(),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'notification_summary' => fn (): array => $request->user()
                ? [
                    'unread_count' => app(UserNotificationRepositoryInterface::class)
                        ->unreadCount($request->user()->id),
                ]
                : [
                    'unread_count' => 0,
                ],
            'abilities' => fn (): array => [
                'can_view_horizon' => $request->user()
                    ? Gate::forUser($request->user())->allows('viewHorizon')
                    : false,
            ],
        ];
    }
}
