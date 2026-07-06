<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Database\Seeders\SystemUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SystemUsersSeeder::class);
    }

    public function test_guest_can_place_order_successfully(): void
    {
        // 1. Create a Category
        $category = Category::create([
            'name' => 'Tech Category',
            'slug' => 'tech-category',
        ]);

        // 2. Create a Product
        $product = Product::create([
            'name' => 'Smartphone X',
            'price' => 500.00,
            'stock' => 10,
            'category_id' => $category->id,
            'SKU' => 'SMART-X',
            'status' => true,
        ]);

        // 3. Make POST request to place an order
        $response = $this->postJson('/api/v1/orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'customer_email' => 'john@example.com',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
            'thana' => 'Mirpur',
            'address' => 'House 12, Road 4',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ]
        ]);

        // 4. Assert response status and structure
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Order placed successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'order_number',
                    'total',
                    'status',
                    'payment_status',
                    'customer_name',
                    'customer_phone',
                    'customer_email',
                    'division',
                    'district',
                    'thana',
                    'address',
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'price',
                        ]
                    ],
                ]
            ]);

        // 5. Assert database records
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'John Doe',
            'customer_phone' => '01712345678',
            'total' => 1000.00,
        ]);

        // 6. Assert product stock decremented
        $this->assertEquals(8, $product->fresh()->stock);
    }
}
