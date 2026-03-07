<?php

namespace App\Domains\Identity\Actions;

use App\Domains\Identity\Data\SearchUsersData;
use App\Domains\Identity\Repositories\UserRepositoryInterface;
use App\Domains\SocialGraph\Repositories\BlockRepositoryInterface;

class SearchUsersAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly BuildUserSearchPayloadAction $buildPayload,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function __invoke(SearchUsersData $data): array
    {
        $results = [];

        foreach ($this->userRepository->searchDiscoverable($data->query, $data->limit) as $user) {
            if ($user->id === $data->viewer_id) {
                continue;
            }

            if ($this->blockRepository->eitherBlocked($data->viewer_id, $user->id)) {
                continue;
            }

            $results[] = ($this->buildPayload)($user);
        }

        return $results;
    }
}
