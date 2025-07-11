<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'phone' => '081234567890',
                'password' => Hash::make('admin123'),
                'role' => UserRole::ADMIN,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@example.com');
            $this->command->info('Password: admin123');
        } else {
            $this->command->info('Admin user already exists.');
        }

        // Create a regular user if it doesn't exist
        if (!User::where('email', 'user@example.com')->exists()) {
            User::create([
                'name' => 'Regular User',
                'email' => 'user@example.com',
                'phone' => '081234567891',
                'password' => Hash::make('user123'),
                'role' => UserRole::USER,
                'email_verified_at' => now(),
            ]);

            $this->command->info('Regular user created successfully!');
            $this->command->info('Email: user@example.com');
            $this->command->info('Password: user123');
        } else {
            $this->command->info('Regular user already exists.');
        }
    }
}
