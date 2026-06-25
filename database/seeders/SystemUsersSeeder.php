<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CustomerProfile;
use App\Models\AdminProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Seed Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '1234567890',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        AdminProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'designation' => 'Lead Manager',
                'department' => 'IT',
            ]
        );

        // Seed Customer User
        $customer = User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        CustomerProfile::updateOrCreate(
            ['user_id' => $customer->id],
            [
                'gender' => 'male',
                'date_of_birth' => '1995-10-12',
                'shipping_address' => '123 Customer Shipping Ave',
                'billing_address' => '123 Customer Billing Ave',
            ]
        );
    }
}
