<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Data;

class UpdatePrivacySettingsData extends Data
{
    public function __construct(
        public readonly bool $is_private,
    ) {}
}
