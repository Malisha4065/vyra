<?php

namespace App\Domains\Content\Models;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Post extends Model
{
    use HasUuids, Searchable;

    protected $fillable = [
        'user_id',
        'body',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function searchableAs(): string
    {
        return 'posts';
    }

    public function shouldBeSearchable(): bool
    {
        return $this->published_at !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $body = $this->body ?? '';
        preg_match_all('/#([\p{L}\p{N}_]+)/u', $body, $matches);
        $hashtags = array_values(array_unique(array_map('mb_strtolower', $matches[1] ?? [])));

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'body' => $body,
            'author_username' => $this->relationLoaded('author') ? $this->author?->username : null,
            'hashtags' => $hashtags,
            'media_kinds' => $this->relationLoaded('media')
                ? $this->media->pluck('kind')->filter()->values()->all()
                : [],
            'comments_count' => $this->relationLoaded('comments') ? $this->comments->count() : 0,
            'reactions_count' => $this->relationLoaded('reactions') ? $this->reactions->count() : 0,
            'published_at' => $this->published_at?->timestamp,
        ];
    }
}
