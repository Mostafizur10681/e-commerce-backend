<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles if they don't exist
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $sellerRole = Role::firstOrCreate(['name' => 'Seller', 'guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);

        // Create Admin User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->assignRole($adminRole);

        // Create Seller User
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Seller User',
                'password' => Hash::make('password'),
            ]
        );
        $sellerUser->assignRole($sellerRole);

        // Create Customer User
        $customerUser = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('password'),
            ]
        );
        $customerUser->assignRole($customerRole);

        // Create Customer Profile for Customer User if not exists
        Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            [
                'name' => $customerUser->name,
                'email' => $customerUser->email,
                'phone' => '1234567890',
                'address' => '123 E-Commerce St',
            ]
        );
    }
}
