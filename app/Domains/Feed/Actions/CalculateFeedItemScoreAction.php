<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Models\Post;

class CalculateFeedItemScoreAction
{
    public function __invoke(Post $post, int $sourceScore, string $mode = 'top'): int
    {
        if ($mode === 'latest') {
            return $sourceScore;
        }

        $publishedTimestamp = $post->published_at?->timestamp ?? $sourceScore;
        $commentsCount = $post->relationLoaded('comments') ? $post->comments->count() : 0;
        $reactionsCount = $post->relationLoaded('reactions') ? $post->reactions->count() : 0;
        $mediaCount = $post->relationLoaded('media') ? $post->media->count() : 0;

        $engagementBoost = (min($commentsCount, 50) * 240)
            + (min($reactionsCount, 50) * 120)
            + (min($mediaCount, 4) * 90);

        return $publishedTimestamp + $engagementBoost;
    }
}
