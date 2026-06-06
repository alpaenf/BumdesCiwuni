<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder for Administrator
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'nama' => 'Admin BUMDes',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password'),
            ]
        );

        // Seeder for Manager
        User::updateOrCreate(
            ['username' => 'manager'],
            [
                'nama' => 'Manager BUMDes',
                'role' => User::ROLE_MANAGER,
                'password' => Hash::make('password'),
            ]
        );
    }
}
