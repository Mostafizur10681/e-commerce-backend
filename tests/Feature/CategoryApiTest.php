<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Database\Seeders\SystemUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SystemUsersSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    /**
     * Test admin can create category with Base64 image.
     */
    public function test_admin_can_create_category_with_base64_image(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/categories', [
                'name' => 'New Tech Category',
                'description' => 'Latest tech gadgets',
                'image' => $base64Image,
                'status' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'name' => 'New Tech Category',
            'image' => $base64Image,
        ]);
    }

    /**
     * Test admin can update category with Base64 image.
     */
    public function test_admin_can_update_category_with_base64_image(): void
    {
        $category = Category::create([
            'name' => 'Old Category',
            'slug' => 'old-category',
            'image' => 'categories/old.png',
        ]);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/categories/{$category->id}", [
                'name' => 'Updated Category',
                'image' => $base64Image,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'image' => $base64Image,
        ]);
    }

    /**
     * Test CategoryResource outputs Base64 image correctly.
     */
    public function test_category_resource_outputs_base64_image_directly(): void
    {
        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        
        $category = Category::create([
            'name' => 'Test Base64 Image',
            'slug' => 'test-base64-image',
            'image' => $base64Image,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.image', $base64Image);
    }
}
