<?php

namespace App\Domains\Content\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'user_id',
        'type',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function reactor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
