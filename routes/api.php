<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes requiring authentication
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes (authenticated)
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });

    // User management routes
    Route::prefix('users')->group(function () {
        // Routes accessible by all authenticated users
        Route::get('profile', [UserController::class, 'profile']);
        Route::put('profile', [UserController::class, 'updateProfile']);
        Route::patch('profile/password', [UserController::class, 'updatePassword']);

        // Admin-only routes
        Route::middleware('role:admin')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{user}', [UserController::class, 'show']);
            Route::put('/{user}', [UserController::class, 'update']);
            Route::delete('/{user}', [UserController::class, 'destroy']);
        });
    });

    // Order management routes
    Route::prefix('orders')->group(function () {
        // Statistics route (must be before the parameterized routes)
        Route::get('statistics', [OrderController::class, 'statistics']);

        // Routes accessible by all authenticated users
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::post('/{order}/confirm', [OrderController::class, 'createBiteshipOrder']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
        Route::get('/{order}/track', [OrderController::class, 'track']);
    });
});

// Public routes (no authentication required)
Route::prefix('public')->group(function () {
    // Public tracking for customers
    Route::get('track', [OrderController::class, 'publicTracking']);
});



// Health check route
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'Shipping Tracking API',
    ]);
});
