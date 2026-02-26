<?php

namespace App\Domains\SocialGraph\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FollowRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'requester_id',
        'requestee_id',
        'status',
    ];

    /** Status constants */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requestee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requestee_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
