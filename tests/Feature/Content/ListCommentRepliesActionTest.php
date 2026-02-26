<?php

use App\Domains\Content\Actions\ListCommentRepliesAction;
use App\Domains\Content\Data\ListCommentRepliesData;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

it('returns paginated replies for comment', function () {
    $paginator = new LengthAwarePaginator(
        items: [['id' => 'reply-1']],
        total: 1,
        perPage: 20,
        currentPage: 1,
    );

    $repository = mock(CommentRepositoryInterface::class);
    $repository->shouldReceive('getReplies')
        ->once()
        ->with('comment-1', 20)
        ->andReturn($paginator);

    $action = new ListCommentRepliesAction($repository);

    $result = $action(ListCommentRepliesData::from([
        'comment_id' => 'comment-1',
        'per_page' => 20,
    ]));

    expect($result)->toBe($paginator);
});
