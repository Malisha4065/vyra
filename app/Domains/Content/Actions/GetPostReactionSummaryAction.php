<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\GetPostReactionSummaryData;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;

class GetPostReactionSummaryAction
{
    public function __construct(
        private readonly PostReactionRepositoryInterface $reactionRepository,
    ) {}

    /**
     * @return array{total: int, by_type: array<string, int>}
     */
    public function __invoke(GetPostReactionSummaryData $data): array
    {
        return $this->reactionRepository->aggregateForPost($data->post_id);
    }
}
