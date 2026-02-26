<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Data;

class UpdateProfileData extends Data
{
    public function __construct(
        #[Max(50)]
        public readonly ?string $display_name = null,

        #[Max(500)]
        public readonly ?string $bio = null,

        #[Max(255), Url]
        public readonly ?string $website = null,

        #[Max(100)]
        public readonly ?string $location = null,

        #[DateFormat('Y-m-d')]
        public readonly ?string $date_of_birth = null,
    ) {}
}
