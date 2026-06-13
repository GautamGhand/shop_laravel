<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Order\PlaceOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Exception;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}

    /**
     * Place a new order.
     */
    public function store(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->placeOrder($request->user(), $request->validated());
            return $this->created($order, 'Order placed successfully');
        } catch (Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
