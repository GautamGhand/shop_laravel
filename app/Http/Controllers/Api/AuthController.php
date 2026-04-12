<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Admin login
     */
    public function adminLogin(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                'admin'
            );

            return $this->success([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Admin logged in successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Customer login
     */
    public function customerLogin(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->email,
                $request->password,
                'customer'
            );

            return $this->success([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Logged in successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Customer registration
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->created([
                'user'  => $result['user'],
                'token' => $result['token'],
            ], 'Account created successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user(), 'User retrieved');
    }

    /**
     * Logout current device
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());
        return $this->success(null, 'Logged out successfully');
    }

    /**
     * Logout all devices
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());
        return $this->success(null, 'Logged out from all devices');
    }
}
