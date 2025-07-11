<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): array
    {
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = UserRole::USER;
        }

        // Only admins can create admin accounts via API
        if (isset($data['role']) && $data['role'] === UserRole::ADMIN) {
            $currentUser = Auth::user();
            if (!$currentUser || !$currentUser->isAdmin()) {
                throw ValidationException::withMessages([
                    'role' => ['You are not authorized to create admin accounts.']
                ]);
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user->makeHidden(['password']),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Authenticate user and return token
     */
    public function login(array $credentials, bool $remember = false): array
    {
        if (!Auth::attempt($credentials, $remember)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = Auth::user();

        // Revoke all existing tokens for this user to maintain single session
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user->makeHidden(['password']),
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Logout user by revoking current token
     */
    public function logout(User $user): bool
    {
        // Revoke all tokens for this user
        $user->tokens()->delete();

        return true;
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Create admin user (for seeding purposes)
     */
    public function createAdminUser(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => UserRole::ADMIN,
            'email_verified_at' => now(),
        ]);
    }
}
