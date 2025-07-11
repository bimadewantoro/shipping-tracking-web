<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $packageWeight = $this->faker->numberBetween(100, 5000); // 100g - 5kg
        $shippingCost = $this->faker->numberBetween(10000, 50000); // 10k - 50k IDR
        $insuranceCost = $this->faker->numberBetween(0, 5000); // 0 - 5k IDR

        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'biteship_order_id' => $this->faker->optional(0.6)->uuid(),
            'status' => $this->faker->randomElement(OrderStatus::cases()),
            'courier_code' => $this->faker->randomElement(['jne', 'tiki', 'pos', 'sicepat', 'jnt', 'anteraja']),
            'courier_service' => $this->faker->randomElement(['REG', 'YES', 'OKE', 'CTCYES', 'CTC']),
            'waybill_id' => $this->faker->optional(0.7)->regexify('[A-Z0-9]{10,15}'),

            // Sender information
            'sender_name' => $this->faker->name(),
            'sender_phone' => $this->faker->phoneNumber(),
            'sender_address' => $this->faker->address(),
            'sender_postal_code' => $this->faker->postcode(),
            'sender_area_id' => $this->faker->optional()->numberBetween(1000, 9999),
            'sender_latitude' => $this->faker->optional()->latitude(-10.0, 6.0), // Indonesia bounds
            'sender_longitude' => $this->faker->optional()->longitude(95.0, 141.0), // Indonesia bounds

            // Receiver information
            'receiver_name' => $this->faker->name(),
            'receiver_phone' => $this->faker->phoneNumber(),
            'receiver_address' => $this->faker->address(),
            'receiver_postal_code' => $this->faker->postcode(),
            'receiver_area_id' => $this->faker->optional()->numberBetween(1000, 9999),
            'receiver_latitude' => $this->faker->optional()->latitude(-10.0, 6.0), // Indonesia bounds
            'receiver_longitude' => $this->faker->optional()->longitude(95.0, 141.0), // Indonesia bounds

            // Package information
            'package_type' => $this->faker->randomElement(['package', 'document', 'electronics', 'clothing', 'food']),
            'package_weight' => $packageWeight,
            'package_length' => $this->faker->optional()->numberBetween(10, 100), // 10-100 cm
            'package_width' => $this->faker->optional()->numberBetween(10, 100), // 10-100 cm
            'package_height' => $this->faker->optional()->numberBetween(5, 50), // 5-50 cm
            'package_description' => $this->faker->optional()->sentence(),
            'package_value' => $this->faker->optional()->numberBetween(50000, 1000000), // 50k - 1M IDR

            // Pricing
            'shipping_cost' => $shippingCost,
            'insurance_cost' => $insuranceCost,
            'total_cost' => $shippingCost + $insuranceCost,

            // Additional information
            'notes' => $this->faker->optional()->sentence(),
            'biteship_response' => $this->faker->optional()->randomElement([
                ['status' => 'confirmed', 'timestamp' => now()->toISOString()],
                null
            ]),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+7 days'),
            'delivered_at' => $this->faker->optional(0.3)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Create an order with pending status
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::PENDING,
            'biteship_order_id' => null,
            'waybill_id' => null,
            'delivered_at' => null,
        ]);
    }

    /**
     * Create an order with confirmed status
     */
    public function confirmed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::CONFIRMED,
            'biteship_order_id' => $this->faker->uuid(),
            'waybill_id' => $this->faker->regexify('[A-Z0-9]{10,15}'),
            'delivered_at' => null,
        ]);
    }

    /**
     * Create a delivered order
     */
    public function delivered(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::DELIVERED,
            'biteship_order_id' => $this->faker->uuid(),
            'waybill_id' => $this->faker->regexify('[A-Z0-9]{10,15}'),
            'delivered_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Create a cancelled order
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::CANCELLED,
            'delivered_at' => null,
        ]);
    }
}
