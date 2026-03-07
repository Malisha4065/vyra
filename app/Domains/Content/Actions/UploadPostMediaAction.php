<?php

namespace App\Domains\Content\Actions;

use App\Domains\Content\Data\UploadPostMediaData;
use App\Domains\Content\Repositories\PostMediaStorageInterface;
use App\Domains\Identity\Models\User;

class UploadPostMediaAction
{
    public function __construct(
        private readonly PostMediaStorageInterface $postMediaStorage,
    ) {}

    /**
     * @return array{
     *   disk: string,
     *   path: string,
     *   url: string,
     *   original_name: string,
     *   mime_type: string,
     *   size_bytes: int,
     *   kind: string
     * }
     */
    public function __invoke(User $user, UploadPostMediaData $data): array
    {
        return $this->postMediaStorage->storeUpload($data->file, $user->id);
    }
}
