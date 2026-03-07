<?php

namespace App\Domains\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMedia extends Model
{
    use HasUuids;

    protected $fillable = [
        'post_id',
        'url',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size_bytes',
        'kind',
        'position',
        'processed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'position' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
