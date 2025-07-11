<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    /**
     * Get paginated users with optional filters
     */
    public function getPaginatedUsers(int $perPage = 15, ?string $search = null, ?string $role = null): LengthAwarePaginator
    {
        $query = User::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        // Apply role filter
        if ($role && UserRole::tryFrom($role)) {
            $query->where('role', $role);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        // Set default role if not provided
        if (!isset($data['role'])) {
            $data['role'] = UserRole::USER;
        }

        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $data): User
    {
        // Handle password update if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Update user profile (name, email, phone only)
     */
    public function updateUserProfile(User $user, array $data): User
    {
        // Only allow specific fields for profile updates
        $allowedFields = ['name', 'email', 'phone'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        $user->update($filteredData);

        return $user->fresh();
    }

    /**
     * Update user password with current password verification
     */
    public function updateUserPassword(User $user, array $data): bool
    {
        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.']
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password'])
        ]);

        // Revoke all existing tokens to force re-login
        $user->tokens()->delete();

        return true;
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user): bool
    {
        // Revoke all tokens first
        $user->tokens()->delete();

        return $user->delete();
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics(): array
    {
        return [
            'total_users' => User::count(),
            'admin_users' => User::admins()->count(),
            'regular_users' => User::users()->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
        ];
    }
}
