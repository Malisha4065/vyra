<?php

namespace App\Domains\Notification;

use App\Domains\Notification\Events\UserNotificationCreated;
use App\Domains\Notification\Listeners\QueueBroadcastUserNotificationListener;
use App\Domains\Notification\Models\UserNotification;
use App\Domains\Notification\Policies\UserNotificationPolicy;
use App\Domains\Notification\Repositories\EloquentUserNotificationRepository;
use App\Domains\Notification\Repositories\UserNotificationRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserNotificationRepositoryInterface::class, EloquentUserNotificationRepository::class);
    }

    public function boot(): void
    {
        Event::listen(UserNotificationCreated::class, QueueBroadcastUserNotificationListener::class);
        Gate::policy(UserNotification::class, UserNotificationPolicy::class);
    }
}
