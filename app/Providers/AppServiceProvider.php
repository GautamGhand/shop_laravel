<?php

namespace App\Providers;

use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repository bindings
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
