<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        User::updateOrCreate(
            ['email' => 'admin@irteguhsolution.com'],
            [
                'name' => 'Super Administrator',
                'password' => 'admin123', // Will be hashed automatically by Laravel
                'role' => 'super_admin',
                'is_admin' => true,
            ]
        );
    }
}
