<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function getAdmins(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('role', User::ROLE_ADMIN)->latest()->paginate($perPage);
    }

    public function getCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('role', User::ROLE_CUSTOMER)->latest()->paginate($perPage);
    }

    public function toggleActive(int $id): User
    {
        $user = $this->findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh();
    }
}
