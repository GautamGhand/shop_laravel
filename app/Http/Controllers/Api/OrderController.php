<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Order\PlaceOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OrderController extends BaseController
{
    public function __construct(protected OrderService $orderService) {}

    /**
     * List orders.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'customer_id']);
        $perPage = (int) $request->get('per_page', 15);

        $orders = $this->orderService->getOrders($request->user(), $filters, $perPage);
        return $this->paginated($orders, 'Orders retrieved successfully');
    }

    /**
     * Get details of a single order.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($request->user(), $id);
            return $this->success($order, 'Order retrieved successfully');
        } catch (Exception $e) {
            $code = $e->getCode() === 403 ? 403 : 404;
            return $this->error($e->getMessage(), $code);
        }
    }

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
