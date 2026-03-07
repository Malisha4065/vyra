<?php

namespace App\Domains\Content\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FlysystemPostMediaStorageRepository implements PostMediaStorageInterface
{
    public function storeUpload(UploadedFile $file, string $userId): array
    {
        $disk = (string) config('filesystems.media_disk', config('filesystems.default', 'local'));
        $extension = $file->getClientOriginalExtension() ?: $file->extension() ?: 'bin';
        $path = sprintf(
            'posts/%s/staging/%s/%s.%s',
            $userId,
            now()->format('Y/m/d'),
            (string) Str::uuid(),
            $extension,
        );

        Storage::disk($disk)->put($path, $file->getContent());

        $mimeType = $file->getMimeType() ?? 'application/octet-stream';

        return [
            'disk' => $disk,
            'path' => $path,
            'url' => Storage::disk($disk)->url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $mimeType,
            'size_bytes' => $file->getSize() ?? 0,
            'kind' => str_starts_with($mimeType, 'video/') ? 'video' : 'image',
        ];
    }
}
