<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Database\Seeders\SystemUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedAuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed default system users
        $this->seed(SystemUsersSeeder::class);
    }

    /**
     * Customer registration.
     */
    public function test_customer_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New Customer',
            'email' => 'new.customer@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '1231231234',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'phone',
                        'role',
                        'status',
                        'customer_profile',
                    ],
                    'access_token',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'new.customer@example.com',
            'role' => 'customer',
        ]);

        $this->assertDatabaseHas('customer_profiles', [
            'user_id' => $response->json('data.user.id'),
        ]);
    }

    /**
     * Login.
     */
    public function test_customer_can_login_successfully(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'access_token',
                ]
            ]);
    }

    public function test_blocked_customer_cannot_login(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();
        $customer->update(['status' => 'blocked']);

        $response = $this->postJson('/api/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /**
     * Role-based access control.
     */
    public function test_customer_cannot_access_admin_routes(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();
        
        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_customer_routes(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/customer/profile');

        $response->assertStatus(403);
    }

    /**
     * Wishlist operations.
     */
    public function test_customer_can_manage_wishlist(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();

        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);

        $product = Product::create([
            'name' => 'Gadget',
            'slug' => 'gadget',
            'price' => 99.99,
            'SKU' => 'GDG123',
            'stock' => 10,
            'category_id' => $category->id,
        ]);

        // Add to wishlist
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/wishlist', [
                'product_id' => $product->id,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $wishlistId = $response->json('data.id');

        // Retrieve wishlist
        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/customer/wishlist');

        $response->assertStatus(200);

        // Delete from wishlist
        $response = $this->actingAs($customer, 'sanctum')
            ->deleteJson("/api/customer/wishlist/{$wishlistId}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', [
            'id' => $wishlistId,
        ]);
    }

    /**
     * Address operations.
     */
    public function test_customer_can_manage_addresses(): void
    {
        $customer = User::where('email', 'customer@example.com')->first();

        // Add Address
        $response = $this->actingAs($customer, 'sanctum')
            ->postJson('/api/customer/addresses', [
                'type' => 'shipping',
                'address_line_1' => '123 Main St',
                'city' => 'Metropolis',
                'postal_code' => '10001',
                'country' => 'USA',
                'is_default' => true,
            ]);

        $response->assertStatus(201);
        $addressId = $response->json('data.id');

        // Update Address
        $response = $this->actingAs($customer, 'sanctum')
            ->putJson("/api/customer/addresses/{$addressId}", [
                'type' => 'shipping',
                'address_line_1' => '456 Second St',
                'city' => 'Metropolis',
                'postal_code' => '10001',
                'country' => 'USA',
                'is_default' => true,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('addresses', [
            'id' => $addressId,
            'address_line_1' => '456 Second St',
        ]);
    }

    /**
     * Admin Brand CRUD operations.
     */
    public function test_admin_can_manage_brands(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        // Create Brand
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/admin/brands', [
                'name' => 'Brand Tech',
                'logo' => 'logo.png',
                'status' => true,
            ]);

        $response->assertStatus(201);
        $brandId = $response->json('data.id');

        // Update Brand
        $response = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/admin/brands/{$brandId}", [
                'name' => 'Brand Tech Updated',
                'logo' => 'logo.png',
                'status' => true,
            ]);

        $response->assertStatus(200);

        // Delete Brand
        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/admin/brands/{$brandId}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('brands', [
            'id' => $brandId,
        ]);
    }

    /**
     * Customer status block/unblock toggling by admin.
     */
    public function test_admin_can_block_and_unblock_customer(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $customer = User::where('email', 'customer@example.com')->first();

        // Toggle Block (Blocks)
        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/customers/{$customer->id}/status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'status' => 'blocked',
        ]);

        // Toggle Block (Unblocks)
        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/admin/customers/{$customer->id}/status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'status' => 'active',
        ]);
    }

    /**
     * Individual Customer registration and login testing.
     */
    public function test_individual_customer_register_and_login(): void
    {
        $regResponse = $this->postJson('/api/customer/register', [
            'name' => 'Indie Customer',
            'email' => 'indie.cust@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '5551234',
        ]);

        $regResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Customer registration successful',
            ]);

        $loginResponse = $this->postJson('/api/customer/login', [
            'email' => 'indie.cust@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Customer login successful',
            ]);
    }

    /**
     * Individual Admin registration and login testing.
     */
    public function test_individual_admin_register_and_login(): void
    {
        $regResponse = $this->postJson('/api/admin/register', [
            'name' => 'Indie Admin',
            'email' => 'indie.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'designation' => 'Tech Lead',
            'department' => 'Engineering',
        ]);

        $regResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Admin registration successful',
            ]);

        $loginResponse = $this->postJson('/api/admin/login', [
            'email' => 'indie.admin@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Admin login successful',
            ]);
    }

    /**
     * Test role mismatch error on individual route login.
     */
    public function test_cross_role_login_fails_on_individual_routes(): void
    {
        // Try logging in as Admin on the Customer route
        $response = $this->postJson('/api/customer/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        // Try logging in as Customer on the Admin route
        $response = $this->postJson('/api/admin/login', [
            'email' => 'customer@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /**
     * Test V1 Admin registration and login.
     */
    public function test_v1_admin_register_and_login(): void
    {
        $regResponse = $this->postJson('/api/v1/auth/admin/register', [
            'name' => 'V1 Admin',
            'email' => 'v1.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $regResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Admin registration successful',
            ]);

        $loginResponse = $this->postJson('/api/v1/auth/admin/login', [
            'email' => 'v1.admin@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Admin login successful',
            ]);
    }
}
