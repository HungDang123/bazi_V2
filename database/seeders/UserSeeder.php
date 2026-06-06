<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo user admin mặc định
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );

        // Tạo thêm user test
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test123'),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Đã tạo tài khoản mẫu:');
        $this->command->info('Email: admin@example.com | Password: admin123');
        $this->command->info('Email: test@example.com | Password: test123');
    }
}

