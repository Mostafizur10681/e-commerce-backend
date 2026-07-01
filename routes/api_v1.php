<?php

use App\Http\Controllers\API\V1\AttributeController;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\CustomerController;
use App\Http\Controllers\API\V1\FaqController;
use App\Http\Controllers\API\V1\OrderController;
use App\Http\Controllers\API\V1\ProductController;
use App\Http\Controllers\API\V1\ReviewController;
use App\Http\Controllers\API\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Health Check API
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is running'
    ]);
});

// Public Catalog Routes
Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/{category}', [CategoryController::class, 'show']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('attributes', [AttributeController::class, 'index']);
Route::get('attributes/{attribute}', [AttributeController::class, 'show']);
Route::get('reviews', [ReviewController::class, 'index']);
Route::get('reviews/{review}', [ReviewController::class, 'show']);
Route::get('faqs', [FaqController::class, 'index']);
Route::get('faqs/{faq}', [FaqController::class, 'show']);
Route::get('faq-categories', [\App\Http\Controllers\API\AdminController::class, 'faqCategoriesIndex']);
Route::get('faq-categories/{id}', [\App\Http\Controllers\API\AdminController::class, 'faqCategoriesShow']);
Route::post('subscriptions', [SubscriptionController::class, 'store']);

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Role-wise Authentication Routes
    Route::post('/customer/register', [AuthController::class, 'registerCustomer']);
    Route::post('/customer/login', [AuthController::class, 'loginCustomer']);
    Route::post('/seller/register', [AuthController::class, 'registerSeller']);
    Route::post('/seller/login', [AuthController::class, 'loginSeller']);
    Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
    Route::post('/admin/login', [AuthController::class, 'loginAdmin']);

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // User & Role management
        Route::apiResource('users', \App\Http\Controllers\API\V1\UserController::class);
        Route::apiResource('roles', \App\Http\Controllers\API\V1\RoleController::class);
        Route::apiResource('permissions', \App\Http\Controllers\API\V1\PermissionController::class)->only(['index', 'store', 'destroy']);

        // Protected Category Routes
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);

        // Protected Product Routes
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);

        // Protected Attribute Routes
        Route::apiResource('attributes', AttributeController::class)->except(['index', 'show']);

        // Order Routes
        Route::apiResource('orders', OrderController::class);

        // Customer Profile Routes
        Route::apiResource('customers', CustomerController::class);

        // Protected Review Routes
        Route::apiResource('reviews', ReviewController::class)->except(['index', 'show']);

        // Protected FAQ Routes
        Route::apiResource('faqs', FaqController::class)->except(['index', 'show']);

        // Protected Subscription Routes
        Route::apiResource('subscriptions', SubscriptionController::class)->except(['store']);
    });
});
