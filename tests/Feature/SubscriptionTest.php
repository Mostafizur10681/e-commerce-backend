<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Anyone can subscribe with a valid email.
     */
    public function test_anyone_can_subscribe_with_valid_email(): void
    {
        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'subscriber@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Subscribed successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'email',
                    'status',
                    'created_at',
                    'updated_at',
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'email' => 'subscriber@example.com',
            'status' => true,
        ]);
    }

    /**
     * Subscribing with an existing email returns a validation error.
     */
    public function test_subscribing_with_existing_email_returns_error(): void
    {
        Subscription::create([
            'email' => 'duplicate@example.com',
            'status' => true,
        ]);

        $response = $this->postJson('/api/v1/subscriptions', [
            'email' => 'duplicate@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Guest/non-admin users cannot access the subscription list.
     */
    public function test_guests_cannot_access_subscription_list(): void
    {
        $response = $this->getJson('/api/v1/auth/subscriptions');
        $response->assertStatus(401); // Unauthorized by auth:sanctum
    }

    public function test_non_admin_users_cannot_access_subscription_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/subscriptions');
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized to view subscriptions',
            ]);
    }

    /**
     * Admins and Editors can view, update, and delete subscriptions.
     */
    public function test_admin_can_view_subscription_list(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $admin->assignRole($adminRole);

        Subscription::create(['email' => 'sub1@example.com']);
        Subscription::create(['email' => 'sub2@example.com']);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/auth/subscriptions');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Subscriptions retrieved successfully',
            ])
            ->assertJsonCount(2, 'data.data');
    }

    public function test_admin_can_update_subscription(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $admin->assignRole($adminRole);

        $subscription = Subscription::create(['email' => 'sub@example.com', 'status' => true]);

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/v1/auth/subscriptions/{$subscription->id}", [
            'status' => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Subscription updated successfully',
                'data' => [
                    'email' => 'sub@example.com',
                    'status' => false,
                ]
            ]);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => false,
        ]);
    }

    public function test_admin_can_delete_subscription(): void
    {
        $admin = User::factory()->create();
        $adminRole = Role::create(['name' => 'Admin']);
        $admin->assignRole($adminRole);

        $subscription = Subscription::create(['email' => 'sub@example.com']);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/v1/auth/subscriptions/{$subscription->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Subscription deleted successfully',
            ]);

        $this->assertDatabaseMissing('subscriptions', [
            'id' => $subscription->id,
        ]);
    }
}
