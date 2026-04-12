<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getAllAdmins(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['role'] = User::ROLE_ADMIN;
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function getAllCustomers(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters['role'] = User::ROLE_CUSTOMER;
        return $this->userRepository->paginate($perPage, $filters);
    }

    public function findById(int $id): User
    {
        return $this->userRepository->findOrFail($id);
    }

    public function createAdmin(array $data): User
    {
        return $this->userRepository->create([
            ...$data,
            'role'      => User::ROLE_ADMIN,
            'is_active' => true,
        ]);
    }

    public function createCustomer(array $data): User
    {
        return $this->userRepository->create([
            ...$data,
            'role'      => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);
    }

    public function update(int $id, array $data): User
    {
        return $this->userRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function toggleActive(int $id): User
    {
        return $this->userRepository->toggleActive($id);
    }
}
