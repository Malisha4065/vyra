<?php

namespace App\Domains\Identity\Data;

use Spatie\LaravelData\Data;

class DeleteAccountData extends Data
{
    public function __construct(
        public readonly string $current_password,
    ) {}

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
        ];
    }
}
