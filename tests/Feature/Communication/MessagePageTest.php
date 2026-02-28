<?php

use App\Domains\Identity\Models\User;

it('renders messages page for authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 'user-1',
        'username' => 'alice',
        'email' => 'alice@example.com',
        'password' => 'secret',
    ]);

    $response = $this->actingAs($user)->get(route('messages.index'));

    $response->assertOk();
});
