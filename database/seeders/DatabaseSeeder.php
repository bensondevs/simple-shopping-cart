<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->withoutTwoFactor()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        User::factory()->withoutTwoFactor()->create([
            'name' => 'Dummy Administrator User',
            'email' => 'admin@example.com',
        ]);

        // Create admin user for notifications
        User::factory()->withoutTwoFactor()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        Product::factory()->count(10)->create();
    }
}
