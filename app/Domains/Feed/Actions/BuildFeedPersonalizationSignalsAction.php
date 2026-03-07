<?php

namespace App\Domains\Feed\Actions;

use App\Domains\Content\Repositories\PostRepositoryInterface;

class BuildFeedPersonalizationSignalsAction
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
    ) {}

    /**
     * @return array{viewer_id: string, preferred_hashtags: array<int, string>, prefers_media: bool}
     */
    public function __invoke(string $viewerId): array
    {
        $posts = $this->postRepository->getRecentPublishedByAuthor($viewerId, 8);
        $tagCounts = [];
        $prefersMedia = false;

        foreach ($posts as $post) {
            foreach (($post->toSearchableArray()['hashtags'] ?? []) as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }

            if ($post->relationLoaded('media') && $post->media->isNotEmpty()) {
                $prefersMedia = true;
            }
        }

        arsort($tagCounts);

        return [
            'viewer_id' => $viewerId,
            'preferred_hashtags' => array_slice(array_keys($tagCounts), 0, 5),
            'prefers_media' => $prefersMedia,
        ];
    }
}
