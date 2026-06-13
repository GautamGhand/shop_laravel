<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Place a new order with stock validation and inventory deduction in a transaction.
     *
     * @param User $customer
     * @param array $data
     * @return Order
     * @throws Exception
     */
    public function placeOrder(User $customer, array $data): Order
    {
        return DB::transaction(function () use ($customer, $data) {
            $items = $data['items'];
            $total = $data['total'];

            // 1. Create the Order
            $order = $this->orderRepository->create([
                'customer_id' => $customer->id,
                'total'       => $total,
                'status'      => 'pending',
            ]);

            // 2. Validate products, decrement stock, and create OrderItems
            foreach ($items as $item) {
                // Find product
                $product = $this->productRepository->findOrFail($item['product_id']);

                // Ensure product is active
                if (!$product->is_active) {
                    throw new Exception("Product '{$product->name}' is no longer available.");
                }

                // Verify stock
                if ($product->stock < $item['quantity']) {
                    throw new Exception("Insufficient stock for product '{$product->name}'. Only {$product->stock} items remaining.");
                }

                // Decrement stock
                $this->productRepository->update($product->id, [
                    'stock' => $product->stock - $item['quantity']
                ]);

                // Create OrderItem
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            // Return order with eager-loaded relationships
            return $order->load(['items.product', 'customer']);
        });
    }
}
