<?php

namespace App\Domains\Feed\ValueObjects;

final class FeedKey
{
    public static function userFeed(string $userId): string
    {
        return "feed:user:{$userId}:timeline";
    }

    public static function highFollowerAuthorPosts(string $authorId): string
    {
        return "feed:author:{$authorId}:high:posts";
    }

    public static function highFollowerAuthorsSet(): string
    {
        return 'feed:authors:high';
    }
}
