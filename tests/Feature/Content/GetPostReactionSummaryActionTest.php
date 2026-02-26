<?php

use App\Domains\Content\Actions\GetPostReactionSummaryAction;
use App\Domains\Content\Data\GetPostReactionSummaryData;
use App\Domains\Content\Repositories\PostReactionRepositoryInterface;

it('returns reaction aggregate summary', function () {
    $repository = mock(PostReactionRepositoryInterface::class);
    $repository->shouldReceive('aggregateForPost')
        ->once()
        ->with('post-1')
        ->andReturn([
            'total' => 3,
            'by_type' => [
                'like' => 2,
                'love' => 1,
            ],
        ]);

    $action = new GetPostReactionSummaryAction($repository);

    $result = $action(GetPostReactionSummaryData::from([
        'post_id' => 'post-1',
    ]));

    expect($result)->toBe([
        'total' => 3,
        'by_type' => [
            'like' => 2,
            'love' => 1,
        ],
    ]);
});
