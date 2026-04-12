<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function getAdmins(int $perPage = 15): LengthAwarePaginator;

    public function getCustomers(int $perPage = 15): LengthAwarePaginator;

    public function toggleActive(int $id): User;
}
