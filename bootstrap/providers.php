<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Domains\Content\ContentServiceProvider::class,
    App\Domains\Feed\FeedServiceProvider::class,
    App\Domains\Identity\IdentityServiceProvider::class,
    App\Domains\Notification\NotificationServiceProvider::class,
    App\Domains\SocialGraph\SocialGraphServiceProvider::class,
];
