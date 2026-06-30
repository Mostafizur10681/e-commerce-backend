<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Partner;
use Database\Seeders\SystemUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerApiTest extends TestCase
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
     * Test admin can list partners.
     */
    public function test_admin_can_list_partners(): void
    {
        Partner::create([
            'name' => 'Tech Partner',
            'website' => 'https://techpartner.com',
            'logo' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/partners');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Tech Partner');
    }

    /**
     * Test admin can create a partner.
     */
    public function test_admin_can_create_partner(): void
    {
        $base64Logo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/partners', [
                'name' => 'New Partner',
                'website' => 'https://newpartner.com',
                'logo' => $base64Logo,
                'description' => 'A new partner description',
                'status' => true,
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('partners', [
            'name' => 'New Partner',
            'website' => 'https://newpartner.com',
            'logo' => $base64Logo,
        ]);
    }

    /**
     * Test admin can update a partner.
     */
    public function test_admin_can_update_partner(): void
    {
        $partner = Partner::create([
            'name' => 'Old Partner Name',
            'slug' => 'old-partner-name',
            'website' => 'https://oldpartner.com',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/partners/{$partner->id}", [
                'name' => 'Updated Partner Name',
                'website' => 'https://updatedpartner.com',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Updated Partner Name',
            'website' => 'https://updatedpartner.com',
        ]);
    }

    /**
     * Test admin can delete a partner.
     */
    public function test_admin_can_delete_partner(): void
    {
        $partner = Partner::create([
            'name' => 'Delete Me',
            'slug' => 'delete-me',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/partners/{$partner->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);
    }
}
