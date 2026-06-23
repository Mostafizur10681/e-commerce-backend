<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreOrderRequest;
use App\Http\Requests\API\V1\UpdateOrderRequest;
use App\Http\Resources\API\V1\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        
        // Users can only view their own orders, unless they are Admin or Editor
        $user = $request->user();
        if ($user->hasRole(['Admin', 'Editor'])) {
            $orders = $this->orderService->paginateOrders($perPage);
        } else {
            $query = \App\Models\Order::where('user_id', $user->id)->with(['user', 'items.product']);
            $orders = $query->paginate($perPage);
        }

        return $this->success(OrderResource::collection($orders)->response()->getData(true), 'Orders retrieved successfully');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->validated());
            return $this->success(new OrderResource($order->load('items.product')), 'Order placed successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), [], 422);
        }
    }

    public function show(string $id, Request $request): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);
        
        // Authorization check: User can only see their own order, unless Admin or Editor
        $user = $request->user();
        if ($order->user_id !== $user->id && !$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to view this order', [], 403);
        }

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
    }

    public function update(UpdateOrderRequest $request, string $id): JsonResponse
    {
        // Admin or Editor can update orders status / payment status
        $user = $request->user();
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized to update orders', [], 403);
        }

        $validated = $request->validated();
        $updated = $this->orderService->updateOrderStatus(
            $id, 
            $validated['status'] ?? 'pending', 
            $validated['payment_status'] ?? null
        );

        if ($updated) {
            $order = $this->orderService->getOrderById($id);
            return $this->success(new OrderResource($order), 'Order updated successfully');
        }
        return $this->error('Failed to update order');
    }

    public function destroy(string $id, Request $request): JsonResponse
    {
        // Admin only can delete orders
        $user = $request->user();
        if (!$user->hasRole('Admin')) {
            return $this->error('Unauthorized to delete orders', [], 403);
        }

        $deleted = $this->orderService->deleteOrder($id);
        if ($deleted) {
            return $this->success([], 'Order deleted successfully');
        }
        return $this->error('Failed to delete order');
    }
}
