<?php

namespace Database\Seeders;

use App\Models\Admin\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        Admin::create([
            'username' => 'Prnzdiamond',
            'email' => 'oseahumenagboifoh@gmail.com',
            'password' => Hash::make('12345678'),
            'first_name' => 'Prince',
            'last_name' => 'Diamond',
            'role' => 'super_admin',
            'is_active' => true,
            'created_by' => null, // First admin, no creator
        ]);

        // Optional: Create additional admin roles for testing
        Admin::create([
            'username' => 'admin_user',
            'email' => 'admin@vitalaid.com',
            'password' => Hash::make('12345678'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin',
            'is_active' => true,
            'created_by' => null,
        ]);

        Admin::create([
            'username' => 'moderator_user',
            'email' => 'moderator@vitalaid.com',
            'password' => Hash::make('12345678'),
            'first_name' => 'Moderator',
            'last_name' => 'User',
            'role' => 'moderator',
            'is_active' => true,
            'created_by' => null,
        ]);

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin - Username: Prnzdiamond, Email: oseahumenagboifoh@gmail.com');
        $this->command->info('Admin - Username: admin_user, Email: admin@vitalaid.com');
        $this->command->info('Moderator - Username: moderator_user, Email: moderator@vitalaid.com');
        $this->command->info('All passwords: 12345678');
    }
}