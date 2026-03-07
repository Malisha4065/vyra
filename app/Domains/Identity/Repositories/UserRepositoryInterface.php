<?php

namespace App\Domains\Identity\Repositories;

use App\Domains\Identity\Models\User;

interface UserRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findByEmail(string $email): ?User;

    public function findByUsername(string $username): ?User;

    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    public function delete(User $user): void;

    public function existsByUsername(string $username): bool;

    public function existsByEmail(string $email): bool;

    /**
     * @return array<int, User>
     */
    public function searchDiscoverable(string $query, int $limit = 10): array;
}
