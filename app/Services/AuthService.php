<?php

namespace App\Services;

use App\Models\User;
use App\Models\CustomerProfile;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\CustomerProfileRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected UserRepositoryInterface $userRepository;
    protected CustomerProfileRepositoryInterface $customerProfileRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        CustomerProfileRepositoryInterface $customerProfileRepository
    ) {
        $this->userRepository = $userRepository;
        $this->customerProfileRepository = $customerProfileRepository;
    }

    public function registerCustomer(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'status' => 'active',
        ];

        /** @var User $user */
        $user = $this->userRepository->create($userData);

        $this->customerProfileRepository->create([
            'user_id' => $user->id,
        ]);

        return $user;
    }

    public function registerAdmin(array $data): User
    {
        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'status' => 'active',
        ];

        /** @var User $user */
        $user = $this->userRepository->create($userData);

        \App\Models\AdminProfile::create([
            'user_id' => $user->id,
            'designation' => $data['designation'],
            'department' => $data['department'],
        ]);

        return $user;
    }

    public function loginCustomer(array $credentials): array
    {
        $result = $this->login($credentials);
        
        if ($result['user']->role !== 'customer') {
            throw ValidationException::withMessages([
                'role' => ['You are not authorized to login as a customer.'],
            ]);
        }

        return $result;
    }

    public function loginAdmin(array $credentials): array
    {
        $result = $this->login($credentials);
        
        if ($result['user']->role !== 'admin') {
            throw ValidationException::withMessages([
                'role' => ['You are not authorized to login as an admin.'],
            ]);
        }

        return $result;
    }

    public function login(array $credentials): array
    {
        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if ($user->status === 'blocked') {
            throw ValidationException::withMessages([
                'status' => ['Your account has been blocked.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('customerProfile', 'adminProfile'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
