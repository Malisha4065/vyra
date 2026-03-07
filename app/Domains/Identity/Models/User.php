<?php

namespace App\Domains\Identity\Models;

use App\Domains\Communication\Models\Conversation;
use App\Domains\Communication\Models\Message;
use App\Domains\Communication\Models\MessageReadReceipt;
use App\Domains\Notification\Models\UserNotification;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ──────────────────────────────────────────────
    // Identity Relationships
    // ──────────────────────────────────────────────

    /**
     * Get the user's profile.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    // ──────────────────────────────────────────────
    // SocialGraph Relationships
    // ──────────────────────────────────────────────

    /**
     * Users who follow this user.
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'followee_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Users this user follows.
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'followee_id')
            ->withTimestamps();
    }

    /**
     * Users this user has blocked.
     */
    public function blockedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_id', 'blocked_id')
            ->withTimestamps();
    }

    /**
     * Users who have blocked this user.
     */
    public function blockedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_id', 'blocker_id')
            ->withTimestamps();
    }

    /**
     * Users this user has muted.
     */
    public function mutedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'mutes', 'muter_id', 'muted_id')
            ->withTimestamps();
    }

    /**
     * Conversations the user participates in.
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['joined_at', 'last_read_message_id', 'last_read_at'])
            ->withTimestamps();
    }

    /**
     * Messages sent by this user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Message read receipts for this user.
     */
    public function messageReadReceipts(): HasMany
    {
        return $this->hasMany(MessageReadReceipt::class);
    }

    /**
     * Notifications targeted at this user.
     */
    public function notificationsFeed(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function searchableAs(): string
    {
        return 'users';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->username !== '';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $profile = $this->relationLoaded('profile') ? $this->profile : null;

        return [
            'id' => $this->id,
            'username' => $this->username,
            'display_name' => $profile?->display_name,
            'bio' => $profile?->bio,
            'location' => $profile?->location,
            'is_private' => (bool) ($profile?->is_private ?? false),
        ];
    }
}
