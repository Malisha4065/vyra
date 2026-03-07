<?php

use App\Domains\Content\Actions\UploadPostMediaAction;
use App\Domains\Content\Data\UploadPostMediaData;
use App\Domains\Content\Repositories\PostMediaStorageInterface;
use App\Domains\Identity\Models\User;
use Illuminate\Http\UploadedFile;

it('uploads post media through the content action', function () {
    $user = new User();
    $user->forceFill(['id' => 'user-1']);

    $file = UploadedFile::fake()->image('photo.jpg');

    $storage = mock(PostMediaStorageInterface::class);
    $storage->shouldReceive('storeUpload')
        ->once()
        ->with($file, 'user-1')
        ->andReturn([
            'disk' => 's3',
            'path' => 'posts/user-1/staging/2026/03/07/file.jpg',
            'url' => 'http://media.test/file.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 1200,
            'kind' => 'image',
        ]);

    $action = new UploadPostMediaAction($storage);

    $result = $action($user, UploadPostMediaData::from([
        'file' => $file,
    ]));

    expect($result['path'])->toBe('posts/user-1/staging/2026/03/07/file.jpg');
    expect($result['kind'])->toBe('image');
});
