<?php

namespace App\Domains\Content\Repositories;

use Illuminate\Http\UploadedFile;

interface PostMediaStorageInterface
{
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
    public function storeUpload(UploadedFile $file, string $userId): array;
}
