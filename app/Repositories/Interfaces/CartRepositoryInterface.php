<?php

namespace App\Repositories\Interfaces;

use App\Models\CartItem;

interface CartRepositoryInterface extends RepositoryInterface
{
    public function findByUserAndProduct(int $userId, int $productId): ?CartItem;
}
