<?php

namespace App\Repositories\Eloquent;

use App\Models\CartItem;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function all(array $filters = []): Collection
    {
        $query = $this->model->newQuery()->with('product');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with('product');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate($perPage);
    }

    public function findByUserAndProduct(int $userId, int $productId): ?CartItem
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }
}
