<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/admin/login',    [AuthController::class, 'adminLogin']);
    Route::post('/customer/login', [AuthController::class, 'customerLogin']);
    Route::post('/register',       [AuthController::class, 'register']);
});

// Public product listing
Route::get('/products',      [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {

    // Order Routes
    Route::get('/orders',      [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders',     [OrderController::class, 'store']);

    // Cart Routes
    Route::get('/cart',                 [CartController::class, 'index']);
    Route::post('/cart',                [CartController::class, 'store']);
    Route::put('/cart/{productId}',     [CartController::class, 'update']);
    Route::delete('/cart/{productId}',  [CartController::class, 'destroy']);
    Route::delete('/cart',              [CartController::class, 'clear']);

    // Auth actions
    Route::prefix('auth')->group(function () {
        Route::get('/me',          [AuthController::class, 'me']);
        Route::post('/logout',     [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    });

    // Admin-only Routes
    Route::middleware('admin')->group(function () {

    
        // Products (admin management)
        Route::post('/products',               [ProductController::class, 'store']);
        Route::put('/products/{id}',           [ProductController::class, 'update']);
        Route::delete('/products/{id}',        [ProductController::class, 'destroy']);
        Route::patch('/products/{id}/toggle',  [ProductController::class, 'toggleActive']);

        // Admin management
        Route::apiResource('admins', AdminController::class);
        Route::patch('/admins/{id}/toggle',    [AdminController::class, 'toggleActive']);

        // Customer management
        Route::apiResource('customers', CustomerController::class);
        Route::patch('/customers/{id}/toggle', [CustomerController::class, 'toggleActive']);
    });
});
