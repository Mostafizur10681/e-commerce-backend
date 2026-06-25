<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Users, Roles & Permissions
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Eloquent\PermissionRepository;

// E-commerce Modules
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Interfaces\AttributeRepositoryInterface;
use App\Repositories\Eloquent\AttributeRepository;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Interfaces\ReviewRepositoryInterface;
use App\Repositories\Eloquent\ReviewRepository;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Repositories\Eloquent\FaqRepository;
use App\Repositories\Interfaces\SubscriptionRepositoryInterface;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Eloquent\BrandRepository;
use App\Repositories\Interfaces\WishlistRepositoryInterface;
use App\Repositories\Eloquent\WishlistRepository;
use App\Repositories\Interfaces\AddressRepositoryInterface;
use App\Repositories\Eloquent\AddressRepository;
use App\Repositories\Interfaces\CustomerProfileRepositoryInterface;
use App\Repositories\Eloquent\CustomerProfileRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bindings
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ReviewRepositoryInterface::class, ReviewRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(AddressRepositoryInterface::class, AddressRepository::class);
        $this->app->bind(CustomerProfileRepositoryInterface::class, CustomerProfileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
