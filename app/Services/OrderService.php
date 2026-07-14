<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllOrders(array $relations = ['user', 'items.product']): Collection
    {
        return $this->orderRepository->all(['*'], $relations);
    }

    public function paginateOrders(int $perPage = 15, array $relations = ['user', 'items.product']): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($perPage, $relations);
    }

    public function getOrderById(int|string $id, array $relations = ['user', 'items.product.images']): ?Model
    {
        if (is_numeric($id)) {
            $order = $this->orderRepository->find($id, ['*'], $relations);
            if ($order) {
                return $order;
            }
        }
        return \App\Models\Order::with($relations)->where('order_number', $id)->firstOrFail();
    }

    public function createOrder(array $data): ?Model
    {
        return DB::transaction(function () use ($data) {
            // Generate unique order number
            $data['order_number'] = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Calculate total price from items
            $total = 0;
            $itemsData = [];
            
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Use sale price if active, otherwise normal price
                $price = $product->sale_price ?? $product->price;
                $subtotal = $price * $item['quantity'];
                $total += $subtotal;

                // Adjust product stock
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Product {$product->name} does not have enough stock.");
                }
                $product->decrement('stock', $item['quantity']);

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'attributes' => $item['attributes'] ?? null,
                ];
            }

            $data['total'] = $total;

            // Create order
            $order = $this->orderRepository->create([
                'user_id' => $data['user_id'] ?? null,
                'order_number' => $data['order_number'],
                'total' => $data['total'],
                'status' => $data['status'] ?? 'pending',
                'payment_status' => $data['payment_status'] ?? 'pending',
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'division' => $data['division'],
                'district' => $data['district'],
                'thana' => $data['thana'],
                'address' => $data['address'],
            ]);

            // Save order items
            $order->items()->createMany($itemsData);

            return $order;
        });
    }

    public function updateOrderStatus(int|string $id, string $status, ?string $paymentStatus = null): bool
    {
        $payload = ['status' => $status];
        if ($paymentStatus !== null) {
            $payload['payment_status'] = $paymentStatus;
        }
        return $this->orderRepository->update($id, $payload);
    }

    public function deleteOrder(int|string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $order = $this->orderRepository->find($id);
            if ($order) {
                // Restore product stock when order is deleted / cancelled
                if ($order->status !== 'cancelled') {
                    foreach ($order->items as $item) {
                        if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
                    }
                }
                return $order->delete();
            }
            return false;
        });
    }
}
