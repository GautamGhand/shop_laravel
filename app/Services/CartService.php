<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\User;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    public function __construct(
        protected CartRepositoryInterface $cartRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get user's cart items.
     */
    public function getCart(User $user): Collection
    {
        return $this->cartRepository->all(['user_id' => $user->id]);
    }

    /**
     * Add item to user's cart.
     *
     * @throws Exception
     */
    public function addItem(User $user, int $productId, int $quantity): CartItem
    {
        $product = $this->productRepository->findOrFail($productId);

        if (!$product->is_active) {
            throw new Exception("Product '{$product->name}' is no longer active.");
        }

        $existingItem = $this->cartRepository->findByUserAndProduct($user->id, $productId);
        $newQuantity = $existingItem ? $existingItem->quantity + $quantity : $quantity;

        if ($product->stock < $newQuantity) {
            throw new Exception("Insufficient stock for product '{$product->name}'. Only {$product->stock} items remaining.");
        }

        if ($existingItem) {
            $item = $this->cartRepository->update($existingItem->id, ['quantity' => $newQuantity]);
            return $item->load('product');
        }

        $item = $this->cartRepository->create([
            'user_id'    => $user->id,
            'product_id' => $productId,
            'quantity'   => $quantity,
        ]);

        return $item->load('product');
    }

    /**
     * Update item quantity in user's cart.
     *
     * @throws Exception
     */
    public function updateItem(User $user, int $productId, int $quantity): CartItem
    {
        $product = $this->productRepository->findOrFail($productId);

        if (!$product->is_active) {
            throw new Exception("Product '{$product->name}' is no longer active.");
        }

        if ($product->stock < $quantity) {
            throw new Exception("Insufficient stock for product '{$product->name}'. Only {$product->stock} items remaining.");
        }

        $existingItem = $this->cartRepository->findByUserAndProduct($user->id, $productId);
        if (!$existingItem) {
            throw new Exception("Product not found in cart.");
        }

        $item = $this->cartRepository->update($existingItem->id, ['quantity' => $quantity]);
        return $item->load('product');
    }

    /**
     * Remove item from user's cart.
     *
     * @throws Exception
     */
    public function removeItem(User $user, int $productId): bool
    {
        $existingItem = $this->cartRepository->findByUserAndProduct($user->id, $productId);
        if (!$existingItem) {
            throw new Exception("Product not found in cart.");
        }

        return $this->cartRepository->delete($existingItem->id);
    }

    /**
     * Clear all user's cart items.
     */
    public function clearCart(User $user): void
    {
        $cartItems = $this->getCart($user);
        foreach ($cartItems as $item) {
            $this->cartRepository->delete($item->id);
        }
    }
}
