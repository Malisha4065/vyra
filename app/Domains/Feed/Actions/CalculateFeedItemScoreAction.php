<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Models\Post;

class CalculateFeedItemScoreAction
{
    /**
     * @param array{viewer_id?: string, preferred_hashtags?: array<int, string>, prefers_media?: bool} $signals
     */
    public function __invoke(Post $post, int $sourceScore, string $mode = 'top', array $signals = []): int
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

        $viewerAffinityBoost = 0;

        if (($signals['viewer_id'] ?? null) === $post->user_id) {
            $viewerAffinityBoost += 900;
        }

        $preferredHashtags = $signals['preferred_hashtags'] ?? [];
        $postHashtags = $post->toSearchableArray()['hashtags'] ?? [];
        $overlapCount = count(array_intersect($preferredHashtags, $postHashtags));

        $viewerAffinityBoost += $overlapCount * 180;

        if (($signals['prefers_media'] ?? false) && $mediaCount > 0) {
            $viewerAffinityBoost += 140;
        }

        return $publishedTimestamp + $engagementBoost + $viewerAffinityBoost;
    }
}
