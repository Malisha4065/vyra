<?php

use App\Http\Controllers\Content\CommentController;
use App\Http\Controllers\Communication\ConversationController;
use App\Http\Controllers\Communication\ConversationMessageController;
use App\Http\Controllers\Communication\MessagePageController;
use App\Http\Controllers\Feed\FeedController;
use App\Http\Controllers\Identity\LoginController;
use App\Http\Controllers\Identity\LogoutController;
use App\Http\Controllers\Identity\ProfileController;
use App\Http\Controllers\Identity\RegisterController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Content\PostController;
use App\Http\Controllers\Content\PostReactionController;
use App\Http\Controllers\SocialGraph\BlockController;
use App\Http\Controllers\SocialGraph\FollowController;
use App\Http\Controllers\SocialGraph\FollowRequestController;
use App\Http\Controllers\SocialGraph\MuteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'destroy'])->name('logout');

    // Feed
    Route::get('/', [FeedController::class, 'index'])->name('feed');

    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/privacy', [ProfileController::class, 'updatePrivacy'])->name('profile.privacy');
    Route::get('/@{username}', [ProfileController::class, 'show'])->name('profile.show');

    // ─── SocialGraph ─────────────────────────────────────────────────
    // Follow / Unfollow
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('user.follow');
    Route::delete('/users/{user}/unfollow', [FollowController::class, 'destroy'])->name('user.unfollow');

    // Follow Requests (for private accounts)
    Route::get('/follow-requests', [FollowRequestController::class, 'index'])->name('follow-requests.index');
    Route::post('/follow-requests/{id}/accept', [FollowRequestController::class, 'accept'])->name('follow-requests.accept');
    Route::post('/follow-requests/{id}/reject', [FollowRequestController::class, 'reject'])->name('follow-requests.reject');

    // Block / Unblock
    Route::post('/users/{user}/block', [BlockController::class, 'store'])->name('user.block');
    Route::delete('/users/{user}/unblock', [BlockController::class, 'destroy'])->name('user.unblock');

    // Mute / Unmute
    Route::post('/users/{user}/mute', [MuteController::class, 'store'])->name('user.mute');
    Route::delete('/users/{user}/unmute', [MuteController::class, 'destroy'])->name('user.unmute');

    // Content
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::get('/comments/{comment}/replies', [CommentController::class, 'replies'])->name('comments.replies.index');
    Route::post('/posts/{post}/reactions', [PostReactionController::class, 'store'])->name('posts.reactions.store');
    Route::delete('/posts/{post}/reactions', [PostReactionController::class, 'destroy'])->name('posts.reactions.destroy');
    Route::get('/posts/{post}/reactions/summary', [PostReactionController::class, 'summary'])->name('posts.reactions.summary');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // Communication
    Route::get('/messages', [MessagePageController::class, 'index'])->name('messages.index');
    Route::get('/messages/conversations', [ConversationController::class, 'index'])->name('messages.conversations.index');
    Route::post('/messages/conversations/direct', [ConversationController::class, 'startDirect'])->name('messages.conversations.direct.start');
    Route::get('/messages/conversations/{conversation}/messages', [ConversationMessageController::class, 'index'])->name('messages.conversations.messages.index');
    Route::post('/messages/conversations/{conversation}/messages', [ConversationMessageController::class, 'store'])->name('messages.conversations.messages.store');
    Route::put('/messages/conversations/{conversation}/read', [ConversationController::class, 'markRead'])->name('messages.conversations.read');
    Route::post('/messages/conversations/{conversation}/typing', [ConversationController::class, 'typing'])->name('messages.conversations.typing');
});
