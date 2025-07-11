<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone', 'role'],
                    'token',
                    'token_type'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'role' => UserRole::USER,
        ]);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'token_type'
                ]
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Logout successful',
            ]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role']
                ]
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                    ]
                ]
            ]);
    }

    public function test_admin_can_create_admin_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);
    }

    public function test_regular_user_cannot_create_admin_user(): void
    {
        $user = User::factory()->create(['role' => UserRole::USER]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password'
            ]);
    }

    public function test_registration_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_valid_data(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
