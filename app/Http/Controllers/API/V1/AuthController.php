<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new Customer.
     */
    public function registerCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign Customer role (create if not exists)
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Store customer details in customers table
        $customer = Customer::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'customer' => $customer,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Customer registration successful', 201);
    }

    /**
     * Register a new Seller.
     */
    public function registerSeller(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign Seller role (create if not exists)
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Seller', 'guard_name' => 'web']);
        $user->assignRole($role);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Seller registration successful', 201);
    }

    /**
     * Register a new Admin.
     */
    public function registerAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Assign Admin role (create if not exists)
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Store admin details in admin_profiles table
        $adminProfile = \App\Models\AdminProfile::create([
            'user_id' => $user->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'admin_profile' => $adminProfile,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin registration successful', 201);
    }

    /**
     * Log in a Customer.
     */
    public function loginCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid login credentials', [
                'email' => ['The provided credentials do not match our records.']
            ], 401);
        }

        // Verify the user has Customer role
        if (!$user->hasRole('Customer')) {
            return $this->error('Unauthorized role access', [
                'role' => ['You are not authorized to login as a customer.']
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles.permissions', 'permissions', 'customer'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Customer login successful');
    }

    /**
     * Log in a Seller.
     */
    public function loginSeller(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid login credentials', [
                'email' => ['The provided credentials do not match our records.']
            ], 401);
        }

        // Verify the user has Seller role
        if (!$user->hasRole('Seller')) {
            return $this->error('Unauthorized role access', [
                'role' => ['You are not authorized to login as a seller.']
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles.permissions', 'permissions'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Seller login successful');
    }

    /**
     * Log in an Admin.
     */
    public function loginAdmin(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid login credentials', [
                'email' => ['The provided credentials do not match our records.']
            ], 401);
        }

        // Verify the user has Admin or Editor role
        if (!$user->hasRole(['Admin', 'Editor'])) {
            return $this->error('Unauthorized role access', [
                'role' => ['You are not authorized to login as an admin.']
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles.permissions', 'permissions'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin login successful');
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default Customer role if it exists
        if (\Spatie\Permission\Models\Role::where('name', 'Customer')->exists()) {
            $user->assignRole('Customer');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful', 201);
    }

    /**
     * Log in a user.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->error('Invalid login credentials', [
                'email' => ['The provided credentials do not match our records.']
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('roles.permissions', 'permissions'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Log out a user (revoke token).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([], 'Logged out successfully');
    }

    /**
     * Get the authenticated user's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions', 'permissions');
        return $this->success($user, 'Profile retrieved successfully');
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success([], 'Password changed successfully');
    }

    /**
     * Send password reset link/token.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        // We use Laravel's native Password broker which generates reset tokens.
        // It will send the notification/email which is caught in storage/logs/laravel.log.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success([], __($status));
        }

        return $this->error(__($status), [
            'email' => [__($status)]
        ], 400);
    }

    /**
     * Reset the user's password using reset token.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success([], __($status));
        }

        return $this->error(__($status), [
            'email' => [__($status)]
        ], 400);
    }
}
