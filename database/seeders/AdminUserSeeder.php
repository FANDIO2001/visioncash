<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@visioncash.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@visioncash.com',
                'phone_number' => '+1234567890',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully.');
        $this->command->info('Email: admin@visioncash.com');
        $this->command->info('Password: admin123');
    }
}
