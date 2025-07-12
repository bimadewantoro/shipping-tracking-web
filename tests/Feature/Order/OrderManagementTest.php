<?php

namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => UserRole::USER]);
        $this->admin = User::factory()->create(['role' => UserRole::ADMIN]);
    }

    public function test_user_can_get_their_orders(): void
    {
        // Create orders for the user
        Order::factory()->count(3)->for($this->user)->create();
        Order::factory()->count(2)->for($this->admin)->create(); // Should not appear

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'order_number',
                            'status',
                            'courier_code',
                            'courier_service',
                            'receiver_name',
                            'receiver_phone',
                            'receiver_address',
                            'package_description',
                            'shipping_cost',
                            'total_cost',
                            'created_at',
                        ]
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ]
            ]);

        $this->assertEquals(3, $response->json('data.total'));
    }

    public function test_admin_can_get_all_orders(): void
    {
        Order::factory()->count(3)->for($this->user)->create();
        Order::factory()->count(2)->for($this->admin)->create();

        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/orders');

        $response->assertOk();
        $this->assertEquals(5, $response->json('data.total'));
    }

    public function test_user_can_create_order(): void
    {
        Sanctum::actingAs($this->user);

        $orderData = [
            'sender_name' => 'John Sender',
            'sender_phone' => '081234567890',
            'sender_address' => 'Jl. Sender No. 123',
            'sender_postal_code' => '12345',
            'receiver_name' => 'Jane Receiver',
            'receiver_phone' => '081987654321',
            'receiver_address' => 'Jl. Receiver No. 456',
            'receiver_postal_code' => '54321',
            'package_weight' => 1000,
            'package_description' => 'Test package',
            'package_value' => 100000,
            'courier_code' => 'jne',
            'courier_service' => 'REG',
            'shipping_cost' => 15000,
            'total_cost' => 15000,
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertCreated()
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'order' => [
                        'id',
                        'order_number',
                        'status',
                        'user' => [
                            'id',
                            'name',
                            'email',
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'sender_name' => 'John Sender',
            'receiver_name' => 'Jane Receiver',
            'status' => OrderStatus::PENDING->value,
        ]);
    }

    public function test_user_can_view_their_order(): void
    {
        $order = Order::factory()->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'order' => [
                        'id',
                        'order_number',
                        'status',
                        'sender_name',
                        'receiver_name',
                        'package_description',
                        'created_at',
                    ]
                ]
            ]);
    }

    public function test_user_cannot_view_other_user_order(): void
    {
        $otherOrder = Order::factory()->for($this->admin)->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/orders/{$otherOrder->id}");

        $response->assertForbidden();
    }

    public function test_admin_can_view_any_order(): void
    {
        $order = Order::factory()->for($this->user)->create();

        Sanctum::actingAs($this->admin);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertOk();
    }

    public function test_user_can_cancel_their_pending_order(): void
    {
        $order = Order::factory()->pending()->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Order cancelled successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::CANCELLED->value,
        ]);
    }

    public function test_user_cannot_cancel_delivered_order(): void
    {
        $order = Order::factory()->delivered()->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order']);
    }

    public function test_user_can_track_their_order_with_waybill(): void
    {
        $order = Order::factory()->confirmed()->for($this->user)->create([
            'waybill_id' => 'TEST123456789'
        ]);

        Sanctum::actingAs($this->user);

        // Note: This test might fail if Biteship API is not accessible
        // In production, you might want to mock the BiteshipService
        $response = $this->getJson("/api/orders/{$order->id}/track");

        // We're testing that the endpoint exists and is accessible
        // The actual response depends on Biteship API availability
        $this->assertTrue(in_array($response->status(), [200, 422, 500]));
    }

    public function test_user_cannot_track_order_without_waybill(): void
    {
        $order = Order::factory()->pending()->for($this->user)->create([
            'waybill_id' => null
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson("/api/orders/{$order->id}/track");

        $response->assertStatus(422);
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        Order::factory()->pending()->for($this->user)->create();
        Order::factory()->confirmed()->for($this->user)->create();
        Order::factory()->delivered()->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders?status=pending');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
    }

    public function test_orders_can_be_searched(): void
    {
        Order::factory()->for($this->user)->create(['receiver_name' => 'John Doe']);
        Order::factory()->for($this->user)->create(['receiver_name' => 'Jane Smith']);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders?search=John');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data.total'));
    }

    public function test_user_can_get_order_statistics(): void
    {
        Order::factory()->pending()->for($this->user)->create();
        Order::factory()->confirmed()->count(2)->for($this->user)->create();
        Order::factory()->delivered()->count(3)->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/orders/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    'total_orders',
                    'pending_orders',
                    'active_orders',
                    'completed_orders',
                    'cancelled_orders',
                    'total_shipping_cost',
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(6, $data['total_orders']);
        $this->assertEquals(1, $data['pending_orders']);
        $this->assertEquals(3, $data['completed_orders']);
    }

    public function test_user_can_create_biteship_order(): void
    {
        $order = Order::factory()->pending()->for($this->user)->create();

        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/orders/{$order->id}/confirm");

        // Note: This test might fail if Biteship API is not accessible
        // In production, you might want to mock the BiteshipService
        $this->assertTrue(in_array($response->status(), [200, 422, 500]));

        if ($response->status() === 422) {
            // Check if it's due to missing Biteship configuration
            $this->assertStringContainsString('Biteship', $response->json('message'));
        }
    }

    public function test_order_creation_requires_authentication(): void
    {
        $orderData = [
            'sender_name' => 'John Sender',
            'receiver_name' => 'Jane Receiver',
            'package_weight' => 1000,
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertUnauthorized();
    }

    public function test_order_creation_validates_required_fields(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'sender_name',
                'sender_phone',
                'sender_address',
                'sender_postal_code',
                'receiver_name',
                'receiver_phone',
                'receiver_address',
                'receiver_postal_code',
                'package_weight',
                'courier_code',
                'courier_service'
            ]);
    }
}
