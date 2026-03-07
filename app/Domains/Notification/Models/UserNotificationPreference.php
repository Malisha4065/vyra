<?php

namespace App\Domains\Notification\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends Model
{
    use HasUuids;

    protected $table = 'user_notification_preferences';

    protected $fillable = [
        'user_id',
        'social_enabled',
        'content_enabled',
        'communication_enabled',
        'account_enabled',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'social_enabled' => 'bool',
            'content_enabled' => 'bool',
            'communication_enabled' => 'bool',
            'account_enabled' => 'bool',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
