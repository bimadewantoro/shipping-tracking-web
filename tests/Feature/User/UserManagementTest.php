<?php

namespace Tests\Feature\User;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->user = User::factory()->create(['role' => UserRole::USER]);
    }

    public function test_admin_can_view_all_users(): void
    {
        // Create additional users for testing
        User::factory()->count(3)->create();

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'email', 'role']
                    ],
                    'current_page',
                    'total'
                ]
            ]);

        // Should have 5 users total (admin + user + 3 created)
        $this->assertEquals(5, $response->json('data.total'));
    }

    public function test_regular_user_cannot_view_all_users(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        Sanctum::actingAs($this->admin);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User created successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '081234567890',
            'role' => UserRole::USER,
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => 'original@example.com',
            'role' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'email' => 'original@example.com',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $targetUser = User::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/users/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'You cannot delete your own account',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_regular_user_cannot_create_user(): void
    {
        Sanctum::actingAs($this->user);

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'role' => 'user',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_update_other_users(): void
    {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/users/{$otherUser->id}", [
            'name' => 'Updated Name',
            'email' => $otherUser->email,
            'role' => 'user',
        ]);

        $response->assertStatus(403);
    }

    public function test_regular_user_cannot_delete_users(): void
    {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->user);

        $response = $this->deleteJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_view_own_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/users/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone', 'role']
                ]
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $this->user->id,
                        'name' => $this->user->name,
                        'email' => $this->user->email,
                    ]
                ]
            ]);
    }

    public function test_user_can_update_own_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/profile', [
            'name' => 'Updated Name',
            'phone' => '081234567890',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Profile updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'phone' => '081234567890',
        ]);
    }

    public function test_user_can_update_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/users/profile/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password updated successfully',
            ]);

        // Verify old password doesn't work
        $user->refresh();
        $this->assertFalse(Hash::check('oldpassword', $user->password));
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_user_cannot_update_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/users/profile/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_user_creation_validates_required_fields(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
                'role'
            ]);
    }

    public function test_user_creation_validates_unique_email(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/users', [
            'name' => 'Test User',
            'email' => $this->user->email, // Use existing email
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_update_validates_unique_email(): void
    {
        $targetUser = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->admin);

        $response = $this->putJson("/api/users/{$targetUser->id}", [
            'name' => 'Updated Name',
            'email' => $otherUser->email, // Use another user's email
            'role' => 'user',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_profile_update_validates_data(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/users/profile', [
            'name' => '', // Empty name
            'phone' => 'invalid-phone',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_password_update_validates_confirmation(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->patchJson('/api/users/profile/password', [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}
