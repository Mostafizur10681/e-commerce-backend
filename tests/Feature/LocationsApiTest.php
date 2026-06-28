<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\User;
use Database\Seeders\SystemUsersSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationsApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the Bangladesh locations data for endpoints that require it
        $this->seed(\Database\Seeders\BangladeshLocationsSeeder::class);
        // Seed default system users (contains admin@example.com)
        $this->seed(SystemUsersSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    /**
     * Test list divisions.
     */
    public function test_can_list_divisions(): void
    {
        $response = $this->getJson('/api/v1/divisions?per_page=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'division_name',
                            'division_name_bn',
                            'division_code',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ]
                ]
            ]);
    }

    /**
     * Test get single division.
     */
    public function test_can_get_single_division(): void
    {
        $division = Division::first();

        $response = $this->getJson("/api/v1/divisions/{$division->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.division_name', $division->name);
    }

    /**
     * Test creating a division.
     */
    public function test_can_create_division(): void
    {
        $payload = [
            'division_name' => 'Mymensingh New',
            'division_name_bn' => 'ময়মনসিংহ নতুন',
            'division_code' => 'mym-new',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/divisions', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.division_name', 'Mymensingh New');

        $this->assertDatabaseHas('divisions', [
            'code' => 'mym-new',
            'deleted_at' => null,
        ]);
    }

    /**
     * Test updating a division.
     */
    public function test_can_update_division(): void
    {
        $division = Division::create([
            'name' => 'Test Division',
            'bn_name' => 'টেস্ট বিভাগ',
            'code' => 'test-div',
            'status' => 1,
        ]);

        $payload = [
            'division_name' => 'Updated Division',
            'division_name_bn' => 'আপডেটেড বিভাগ',
            'division_code' => 'test-div-updated',
            'status' => 0,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/admin/divisions/{$division->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.division_name', 'Updated Division');
    }

    /**
     * Test soft deleting a division.
     */
    public function test_can_soft_delete_division(): void
    {
        $division = Division::create([
            'name' => 'Delete Me',
            'bn_name' => 'ডিলিট মি',
            'code' => 'del-me',
            'status' => 1,
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/admin/divisions/{$division->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('divisions', [
            'id' => $division->id,
        ]);
    }

    /**
     * Test thana validation rule: district must belong to selected division.
     */
    public function test_thana_validation_fails_if_district_does_not_belong_to_division(): void
    {
        // Dhaka Division (id: 3) and Dhaka District (id: 1)
        // Barishal Division (id: 1) and Barguna District (id: 34)
        $dhakaDivisionId = 3;
        $bargunaDistrictId = 34; // belongs to Barishal Division

        $payload = [
            'division_id' => $dhakaDivisionId,
            'district_id' => $bargunaDistrictId,
            'thana_name' => 'Test Thana',
            'thana_name_bn' => 'টেস্ট থানা',
            'thana_code' => 'test-thana-code',
            'status' => 1,
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/admin/thanas', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['district_id']]);
    }
}
