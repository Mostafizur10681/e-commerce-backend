<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\AddressRequest;
use App\Http\Requests\API\ProfileUpdateRequest;
use App\Services\CustomerProfileService;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    protected CustomerProfileService $profileService;

    public function __construct(CustomerProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function getProfile(Request $request): JsonResponse
    {
        $this->profileService->getProfile($request->user());
        return $this->success([
            'user' => $request->user()->load('customerProfile')
        ], 'Profile retrieved successfully');
    }

    public function updateProfile(ProfileUpdateRequest $request): JsonResponse
    {
        $this->profileService->updateProfile($request->user(), $request->validated());
        return $this->success([
            'user' => $request->user()->load('customerProfile')
        ], 'Profile updated successfully');
    }

    // Orders
    public function getOrders(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)->with('items.product')->paginate(15);
        return $this->success($orders, 'Orders retrieved successfully');
    }

    // Wishlist
    public function getWishlist(Request $request): JsonResponse
    {
        $wishlist = $this->profileService->getWishlist($request->user());
        return $this->success($wishlist, 'Wishlist retrieved successfully');
    }

    public function addToWishlist(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        $item = $this->profileService->addToWishlist($request->user(), $request->only('product_id'));
        return $this->success($item, 'Product added to wishlist successfully', 201);
    }

    public function removeFromWishlist(Request $request, string $id): JsonResponse
    {
        $this->profileService->removeFromWishlist($request->user(), $id);
        return $this->success([], 'Product removed from wishlist successfully');
    }

    // Addresses
    public function getAddresses(Request  $request): JsonResponse
    {
        $addresses = $this->profileService->getAddresses($request->user());
        return $this->success($addresses, 'Addresses retrieved successfully');
    }

    public function addAddress(AddressRequest $request): JsonResponse
    {
        $address = $this->profileService->addAddress($request->user(), $request->validated());
        return $this->success($address, 'Address added successfully', 201);
    }

    public function updateAddress(AddressRequest $request, string $id): JsonResponse
    {
        $this->profileService->updateAddress($request->user(), $id, $request->validated());
        return $this->success([], 'Address updated successfully');
    }
}
