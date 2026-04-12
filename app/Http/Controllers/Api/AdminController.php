<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Admin\StoreAdminRequest;
use App\Http\Requests\Admin\UpdateAdminRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends BaseController
{
    public function __construct(protected UserService $userService) {}

    /**
     * List all admins
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active']);
        $perPage = (int) $request->get('per_page', 15);

        $admins = $this->userService->getAllAdmins($filters, $perPage);
        return $this->paginated($admins, 'Admins retrieved');
    }

    /**
     * Create a new admin
     */
    public function store(StoreAdminRequest $request): JsonResponse
    {
        $admin = $this->userService->createAdmin($request->validated());
        return $this->created($admin, 'Admin created successfully');
    }

    /**
     * Get a single admin
     */
    public function show(int $id): JsonResponse
    {
        try {
            $admin = $this->userService->findById($id);
            return $this->success($admin, 'Admin retrieved');
        } catch (\Exception $e) {
            return $this->notFound('Admin not found');
        }
    }

    /**
     * Update an admin
     */
    public function update(UpdateAdminRequest $request, int $id): JsonResponse
    {
        try {
            $admin = $this->userService->update($id, $request->validated());
            return $this->success($admin, 'Admin updated successfully');
        } catch (\Exception $e) {
            return $this->notFound('Admin not found');
        }
    }

    /**
     * Delete an admin
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            // Prevent deleting self
            if (auth()->id() === $id) {
                return $this->error('You cannot delete your own account', 403);
            }

            $this->userService->delete($id);
            return $this->success(null, 'Admin deleted successfully');
        } catch (\Exception $e) {
            return $this->notFound('Admin not found');
        }
    }

    /**
     * Toggle admin active status
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            if (auth()->id() === $id) {
                return $this->error('You cannot deactivate your own account', 403);
            }

            $admin = $this->userService->toggleActive($id);
            return $this->success($admin, 'Admin status updated');
        } catch (\Exception $e) {
            return $this->notFound('Admin not found');
        }
    }
}
