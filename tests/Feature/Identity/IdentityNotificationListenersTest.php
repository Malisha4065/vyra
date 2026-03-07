<?php

use App\Domains\Identity\Events\EmailVerificationRequested;
use App\Domains\Identity\Events\PasswordResetLinkRequested;
use App\Domains\Identity\Events\UserRegistered;
use App\Domains\Identity\Jobs\SendEmailVerificationJob;
use App\Domains\Identity\Jobs\SendPasswordResetLinkJob;
use App\Domains\Identity\Listeners\QueueSendEmailVerificationListener;
use App\Domains\Identity\Listeners\QueueSendPasswordResetLinkListener;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Facades\Bus;

it('queues a verification email when a user registers', function () {
    Bus::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'email' => 'alice@example.com',
    ]);

    $listener = new QueueSendEmailVerificationListener();
    $listener->handle(new UserRegistered($user));

    Bus::assertDispatched(SendEmailVerificationJob::class, function (SendEmailVerificationJob $job): bool {
        return $job->userId === 'user-1' && $job->queue === 'notifications';
    });
});

it('queues a verification email when resend is requested', function () {
    Bus::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'email' => 'alice@example.com',
    ]);

    $listener = new QueueSendEmailVerificationListener();
    $listener->handle(new EmailVerificationRequested($user));

    Bus::assertDispatched(SendEmailVerificationJob::class, function (SendEmailVerificationJob $job): bool {
        return $job->userId === 'user-1' && $job->queue === 'notifications';
    });
});

it('queues a password reset email when reset is requested', function () {
    Bus::fake();

    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'email' => 'alice@example.com',
    ]);

    $listener = new QueueSendPasswordResetLinkListener();
    $listener->handle(new PasswordResetLinkRequested($user, 'reset-token'));

    Bus::assertDispatched(SendPasswordResetLinkJob::class, function (SendPasswordResetLinkJob $job): bool {
        return $job->userId === 'user-1'
            && $job->token === 'reset-token'
            && $job->queue === 'notifications';
    });
});
