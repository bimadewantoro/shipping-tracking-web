<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users (Admin only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->integer('per_page', 15), 100); // Max 100 items per page
            $search = $request->string('search');
            $role = $request->string('role');

            $users = $this->userService->getPaginatedUsers($perPage, $search, $role);

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get users list', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users',
            ], 500);
        }
    }

    /**
     * Store a newly created user (Admin only)
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            Log::info('User created by admin', [
                'created_user_id' => $user->id,
                'created_user_email' => $user->email,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => [
                    'user' => $user->makeHidden(['password']),
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user', [
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user',
            ], 500);
        }
    }

    /**
     * Display the specified user (Admin only)
     */
    public function show(User $user): JsonResponse
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user->makeHidden(['password']),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user details', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user details',
            ], 500);
        }
    }

    /**
     * Update the specified user (Admin only)
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userService->updateUser($user, $request->validated());

            Log::info('User updated by admin', [
                'updated_user_id' => $user->id,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => [
                    'user' => $updatedUser->makeHidden(['password']),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user',
            ], 500);
        }
    }

    /**
     * Remove the specified user (Admin only)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot delete your own account',
                ], 403);
            }

            $this->userService->deleteUser($user);

            Log::info('User deleted by admin', [
                'deleted_user_id' => $user->id,
                'deleted_user_email' => $user->email,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user',
            ], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user->makeHidden(['password']),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
            ], 500);
        }
    }

    /**
     * Update current user profile
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $updatedUser = $this->userService->updateUserProfile($user, $request->validated());

            Log::info('User updated their profile', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'user' => $updatedUser->makeHidden(['password']),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user profile', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile',
            ], 500);
        }
    }

    /**
     * Update current user password
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $this->userService->updateUserPassword($user, $request->validated());

            Log::info('User updated their password', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password updated successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user password', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update password',
            ], 500);
        }
    }
}
