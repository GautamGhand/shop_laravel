<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Cart\AddCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class CartController extends BaseController
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Get the authenticated user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cartItems = $this->cartService->getCart($request->user());
        return $this->success($cartItems, 'Cart items retrieved');
    }

    /**
     * Add an item to the cart.
     */
    public function store(AddCartItemRequest $request): JsonResponse
    {
        try {
            $item = $this->cartService->addItem(
                $request->user(),
                $request->input('product_id'),
                $request->input('quantity')
            );
            return $this->created($item, 'Product added to cart');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Update an item's quantity in the cart.
     */
    public function update(UpdateCartItemRequest $request, int $productId): JsonResponse
    {
        try {
            $item = $this->cartService->updateItem(
                $request->user(),
                $productId,
                $request->input('quantity')
            );
            return $this->success($item, 'Cart quantity updated');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        try {
            $this->cartService->removeItem($request->user(), $productId);
            return $this->success(null, 'Product removed from cart');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Clear the cart.
     */
    public function clear(Request $request): JsonResponse
    {
        try {
            $this->cartService->clearCart($request->user());
            return $this->success(null, 'Cart cleared successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
