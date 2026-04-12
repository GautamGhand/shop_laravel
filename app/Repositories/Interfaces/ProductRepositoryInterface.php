<?php

namespace App\Repositories\Interfaces;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findBySlug(string $slug): ?Product;

    public function getActiveProducts(int $perPage = 15): LengthAwarePaginator;

    public function updateStock(int $id, int $quantity): Product;

    public function getByCategory(string $category): LengthAwarePaginator;
}
