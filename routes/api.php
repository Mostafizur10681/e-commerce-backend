<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerProfileController;
use App\Http\Controllers\API\AdminController;
use Illuminate\Support\Facades\Route;

// Backward Compatibility Fallback
Route::prefix('v1')->group(base_path('routes/api_v1.php'));

// Administrative Locations routes moved to api_v1.php
// Public Authentication APIs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Individual Public Auth APIs
Route::post('/customer/register', [AuthController::class, 'registerCustomer']);
Route::post('/customer/login', [AuthController::class, 'loginCustomer']);
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

// Public Review APIs
Route::get('/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'index']);
Route::get('/reviews/{review}', [\App\Http\Controllers\API\V1\ReviewController::class, 'show']);

// Common FAQ & FAQ Category routes for Customer and Admin
Route::get('/faq-categories', [AdminController::class, 'faqCategoriesIndex']);
Route::get('/faq-categories/{id}', [AdminController::class, 'faqCategoriesShow']);
Route::get('/faqs', [\App\Http\Controllers\API\V1\FaqController::class, 'index']);
Route::get('/faqs/{id}', [\App\Http\Controllers\API\V1\FaqController::class, 'show']);

// Protected APIs
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // Customer Group
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        Route::get('/profile', [CustomerProfileController::class, 'getProfile']);
        Route::put('/profile', [CustomerProfileController::class, 'updateProfile']);
        Route::get('/orders', [CustomerProfileController::class, 'getOrders']);

        // Wishlist
        Route::get('/wishlist', [CustomerProfileController::class, 'getWishlist']);
        Route::post('/wishlist', [CustomerProfileController::class, 'addToWishlist']);
        Route::delete('/wishlist/{id}', [CustomerProfileController::class, 'removeFromWishlist']);

        // Addresses
        Route::get('/addresses', [CustomerProfileController::class, 'getAddresses']);
        Route::post('/addresses', [CustomerProfileController::class, 'addAddress']);
        Route::put('/addresses/{id}', [CustomerProfileController::class, 'updateAddress']);
        Route::post('/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'store']);
    });

    // Admin Group
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Product Management
        Route::get('/products', [AdminController::class, 'productsIndex']);
        Route::post('/products', [AdminController::class, 'productsStore']);
        Route::get('/products/{id}', [AdminController::class, 'productsShow']);
        Route::put('/products/{id}', [AdminController::class, 'productsUpdate']);
        Route::delete('/products/{id}', [AdminController::class, 'productsDestroy']);

        // Category Management
        Route::get('/categories', [AdminController::class, 'categoriesIndex']);
        Route::post('/categories', [AdminController::class, 'categoriesStore']);
        Route::get('/categories/{id}', [AdminController::class, 'categoriesShow']);
        Route::put('/categories/{id}', [AdminController::class, 'categoriesUpdate']);
        Route::delete('/categories/{id}', [AdminController::class, 'categoriesDestroy']);

        // FAQ Category Management
        Route::get('/faq-categories', [AdminController::class, 'faqCategoriesIndex']);
        Route::post('/faq-categories', [AdminController::class, 'faqCategoriesStore']);
        Route::get('/faq-categories/{id}', [AdminController::class, 'faqCategoriesShow']);
        Route::put('/faq-categories/{id}', [AdminController::class, 'faqCategoriesUpdate']);
        Route::delete('/faq-categories/{id}', [AdminController::class, 'faqCategoriesDestroy']);

        // Brand Management
        Route::get('/brands', [AdminController::class, 'brandsIndex']);
        Route::post('/brands', [AdminController::class, 'brandsStore']);
        Route::put('/brands/{id}', [AdminController::class, 'brandsUpdate']);
        Route::delete('/brands/{id}', [AdminController::class, 'brandsDestroy']);

        // Partner Management
        Route::get('/partners', [AdminController::class, 'partnersIndex']);
        Route::post('/partners', [AdminController::class, 'partnersStore']);
        Route::get('/partners/{id}', [AdminController::class, 'partnersShow']);
        Route::put('/partners/{id}', [AdminController::class, 'partnersUpdate']);
        Route::delete('/partners/{id}', [AdminController::class, 'partnersDestroy']);

        // Order Management
        Route::get('/orders', [AdminController::class, 'ordersIndex']);
        Route::get('/orders/{id}', [AdminController::class, 'ordersShow']);
        Route::put('/orders/{id}', [AdminController::class, 'ordersUpdate']);
        Route::patch('/orders/status', [AdminController::class, 'ordersUpdateStatus']);

        // Customer Management
        Route::get('/customers', [AdminController::class, 'customersIndex']);
        Route::post('/customers', [AdminController::class, 'customersStore']);
        Route::get('/customers/{id}', [AdminController::class, 'customersShow']);
        Route::put('/customers/{id}', [AdminController::class, 'customersUpdate']);
        Route::patch('/customers/{id}/status', [AdminController::class, 'customersToggleBlock']);
        Route::delete('/customers/{id}', [AdminController::class, 'customersDestroy']);

        // User Management
        Route::get('/users', [AdminController::class, 'usersIndex']);
        Route::put('/users/{id}', [AdminController::class, 'usersUpdate']);
        Route::delete('/users/{id}', [AdminController::class, 'usersDestroy']);

        // Location Management
        Route::post('/divisions', [\App\Http\Controllers\API\V1\DivisionController::class, 'store']);
        Route::put('/divisions/{division}', [\App\Http\Controllers\API\V1\DivisionController::class, 'update']);
        Route::delete('/divisions/{division}', [\App\Http\Controllers\API\V1\DivisionController::class, 'destroy']);

        Route::post('/districts', [\App\Http\Controllers\API\V1\DistrictController::class, 'store']);
        Route::put('/districts/{district}', [\App\Http\Controllers\API\V1\DistrictController::class, 'update']);
        Route::delete('/districts/{district}', [\App\Http\Controllers\API\V1\DistrictController::class, 'destroy']);

        Route::post('/thanas', [\App\Http\Controllers\API\V1\ThanaController::class, 'store']);
        Route::put('/thanas/{thana}', [\App\Http\Controllers\API\V1\ThanaController::class, 'update']);
        Route::delete('/thanas/{thana}', [\App\Http\Controllers\API\V1\ThanaController::class, 'destroy']);

        // Attribute Management
        Route::get('/attributes', [\App\Http\Controllers\API\V1\AttributeController::class, 'index']);
        Route::get('/attributes/{attribute}', [\App\Http\Controllers\API\V1\AttributeController::class, 'show']);
        Route::post('/attributes', [\App\Http\Controllers\API\V1\AttributeController::class, 'store']);
        Route::put('/attributes/{attribute}', [\App\Http\Controllers\API\V1\AttributeController::class, 'update']);
        Route::delete('/attributes/{attribute}', [\App\Http\Controllers\API\V1\AttributeController::class, 'destroy']);

        // Review Moderation Management
        Route::get('/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'index']);
        Route::post('/reviews', [\App\Http\Controllers\API\V1\ReviewController::class, 'store']);
        Route::put('/reviews/{review}', [\App\Http\Controllers\API\V1\ReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [\App\Http\Controllers\API\V1\ReviewController::class, 'destroy']);
    });
});
