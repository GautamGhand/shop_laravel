<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    public function __construct(protected UserService $userService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = (int) $request->get('per_page', 15);
        $customers = $this->userService->getAllCustomers($filters, $perPage);
        return $this->paginated($customers, 'Customers retrieved');
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->userService->createCustomer($request->validated());
        return $this->created($customer, 'Customer created successfully');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $customer = $this->userService->findById($id);
            return $this->success($customer, 'Customer retrieved');
        } catch (\Exception $e) {
            return $this->notFound('Customer not found');
        }
    }

    public function update(UpdateCustomerRequest $request, int $id): JsonResponse
    {
        try {
            $customer = $this->userService->update($id, $request->validated());
            return $this->success($customer, 'Customer updated successfully');
        } catch (\Exception $e) {
            return $this->notFound('Customer not found');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->userService->delete($id);
            return $this->success(null, 'Customer deleted successfully');
        } catch (\Exception $e) {
            return $this->notFound('Customer not found');
        }
    }

    public function toggleActive(int $id): JsonResponse
    {
        try {
            $customer = $this->userService->toggleActive($id);
            return $this->success($customer, 'Customer status updated');
        } catch (\Exception $e) {
            return $this->notFound('Customer not found');
        }
    }
}
