<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->paginate($perPage, $filters);
    }

    public function findById(int $id): Product
    {
        return $this->productRepository->findOrFail($id);
    }

    public function create(array $data): Product
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);
        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data): Product
    {
        if (isset($data['name'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $id);
        }
        return $this->productRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    public function toggleActive(int $id): Product
    {
        $product = $this->productRepository->findOrFail($id);
        return $this->productRepository->update($id, ['is_active' => !$product->is_active]);
    }

    protected function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Product::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
