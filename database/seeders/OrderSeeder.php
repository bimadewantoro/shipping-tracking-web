<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run AdminUserSeeder first.');
            return;
        }

        $this->command->info('Creating sample orders...');

        // Create orders for each user
        foreach ($users as $user) {
            // Create different types of orders for variety

            // Pending orders
            Order::factory()
                ->count(2)
                ->pending()
                ->for($user)
                ->create();

            // Confirmed orders
            Order::factory()
                ->count(3)
                ->confirmed()
                ->for($user)
                ->create();

            // Delivered orders
            Order::factory()
                ->count(4)
                ->delivered()
                ->for($user)
                ->create();

            // Some cancelled orders
            Order::factory()
                ->count(1)
                ->cancelled()
                ->for($user)
                ->create();
        }

        $totalOrders = Order::count();
        $this->command->info("Created {$totalOrders} sample orders successfully!");

        // Show summary
        $this->command->table(
            ['Status', 'Count'],
            [
                ['Pending', Order::where('status', 'pending')->count()],
                ['Confirmed', Order::where('status', 'confirmed')->count()],
                ['Delivered', Order::where('status', 'delivered')->count()],
                ['Cancelled', Order::where('status', 'cancelled')->count()],
                ['Total', $totalOrders],
            ]
        );
    }
}
