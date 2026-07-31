<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tagupendo.com'],
            [
                'name' => 'Administrator',
                'password' => 'Admin@123',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
