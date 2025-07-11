<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Convert string roles to UserRole enums
        $allowedRoles = array_map(function ($role) {
            return UserRole::tryFrom($role);
        }, $roles);

        // Remove any null values (invalid roles)
        $allowedRoles = array_filter($allowedRoles);

        if (empty($allowedRoles)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid role configuration',
            ], 500);
        }

        if (!in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient permissions',
            ], 403);
        }

        return $next($request);
    }
}
