<?php

use App\Http\Controllers\Identity\LoginController;
use App\Http\Controllers\Identity\LogoutController;
use App\Http\Controllers\Identity\ProfileController;
use App\Http\Controllers\Identity\RegisterController;
use App\Http\Controllers\Content\PostController;
use App\Http\Controllers\SocialGraph\BlockController;
use App\Http\Controllers\SocialGraph\FollowController;
use App\Http\Controllers\SocialGraph\FollowRequestController;
use App\Http\Controllers\SocialGraph\MuteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

    // Feed (placeholder until Feed domain is built)
    Route::get('/', fn () => Inertia::render('Feed/Index'))->name('feed');

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
});
