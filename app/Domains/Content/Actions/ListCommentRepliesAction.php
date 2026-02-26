<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\ListCommentRepliesData;
use App\Domains\Content\Repositories\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCommentRepliesAction
{
    public function __construct(
        private readonly CommentRepositoryInterface $commentRepository,
    ) {}

    public function __invoke(ListCommentRepliesData $data): LengthAwarePaginator
    {
        return $this->commentRepository->getReplies($data->comment_id, $data->per_page);
    }
}
