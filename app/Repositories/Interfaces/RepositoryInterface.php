<?php

namespace App\Repositories\Interfaces;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function find(int $id): ?Model;

    public function findOrFail(int $id): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;
}
