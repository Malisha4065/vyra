<?php

namespace App\Domains\Identity\Repositories;

use App\Domains\Identity\Models\User;
use Illuminate\Database\Eloquent\Builder;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model,
    ) {}

    public function findById(string $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', strtolower($email))->first();
    }

    public function findByUsername(string $username): ?User
    {
        return $this->model->whereRaw('LOWER(username) = ?', [strtolower($username)])->first();
    }

    public function create(array $attributes): User
    {
        return $this->model->create($attributes);
    }

    public function update(User $user, array $attributes): User
    {
        $user->update($attributes);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function existsByUsername(string $username): bool
    {
        return $this->model->whereRaw('LOWER(username) = ?', [strtolower($username)])->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return $this->model->where('email', strtolower($email))->exists();
    }

    public function searchDiscoverable(string $query, int $limit = 10): array
    {
        $term = mb_strtolower(trim($query));

        if ($term === '') {
            return [];
        }

        if (! in_array(config('scout.driver'), ['null', 'collection', null], true)) {
            return $this->model->search($query)
                ->take($limit)
                ->get()
                ->load('profile')
                ->all();
        }

        $like = '%'.$term.'%';

        return $this->model
            ->with('profile')
            ->where(function (Builder $builder) use ($like): void {
                $builder->whereRaw('LOWER(username) LIKE ?', [$like])
                    ->orWhereHas('profile', function (Builder $profileQuery) use ($like): void {
                        $profileQuery->whereRaw('LOWER(display_name) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(bio) LIKE ?', [$like]);
                    });
            })
            ->limit($limit)
            ->get()
            ->all();
    }
}
