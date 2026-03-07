<?php

namespace App\Domains\Content\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;

class UploadPostMediaData extends Data
{
    public function __construct(
        public readonly UploadedFile $file,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:102400', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime,video/webm'],
        ];
    }
}
