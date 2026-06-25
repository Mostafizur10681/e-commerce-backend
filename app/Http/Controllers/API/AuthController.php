<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\RegisterRequest;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\AdminRegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function registerCustomer(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->registerCustomer($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('customerProfile'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Customer registration successful', 201);
    }

    public function loginCustomer(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginCustomer($request->validated());
        return $this->success($result, 'Customer login successful');
    }

    public function registerAdmin(AdminRegisterRequest $request): JsonResponse
    {
        $user = $this->authService->registerAdmin($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('adminProfile'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Admin registration successful', 201);
    }

    public function loginAdmin(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginAdmin($request->validated());
        return $this->success($result, 'Admin login successful');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->registerCustomer($request->validated());
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->success([
            'user' => $user->load('customerProfile'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Registration successful', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return $this->success($result, 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success([], 'Logged out successfully');
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('customerProfile', 'adminProfile');
        return $this->success($user, 'Profile retrieved successfully');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return $this->success([], 'Password changed successfully');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return $this->success([], __($status));
        }

        return $this->error(__($status), ['email' => [__($status)]], 400);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->success([], __($status));
        }

        return $this->error(__($status), ['email' => [__($status)]], 400);
    }
}
