<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    public function __construct(protected ProductService $productService) {}

    /**
     * List all products (admin: all, public: active only)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category', 'is_active', 'sort_by', 'sort_dir']);
        $perPage = (int) $request->get('per_page', 15);

        // Non-admins only see active products
        if (!$request->user()?->isAdmin()) {
            $filters['is_active'] = true;
        }

        $products = $this->productService->getAll($filters, $perPage);
        return $this->paginated($products, 'Products retrieved');
    }

    /**
     * Create a new product (admin only)
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());
        return $this->created($product, 'Product created successfully');
    }

    /**
     * Get a single product
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->findById($id);
            return $this->success($product, 'Product retrieved');
        } catch (\Exception $e) {
            return $this->notFound('Product not found');
        }
    }

    /**
     * Update a product (admin only)
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->update($id, $request->validated());
            return $this->success($product, 'Product updated successfully');
        } catch (\Exception $e) {
            return $this->notFound('Product not found');
        }
    }

    /**
     * Delete a product (admin only)
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->delete($id);
            return $this->success(null, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->notFound('Product not found');
        }
    }

    /**
     * Toggle product active status (admin only)
     */
    public function toggleActive(int $id): JsonResponse
    {
        try {
            $product = $this->productService->toggleActive($id);
            return $this->success($product, 'Product status updated');
        } catch (\Exception $e) {
            return $this->notFound('Product not found');
        }
    }
}
