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
            ['email' => 'admin@bumdes.com'],
            [
                'nama' => 'Admin BUMDes',
                'role' => User::ROLE_ADMIN,
                'password' => Hash::make('password'),
            ]
        );

        // Seeder for Manager
        User::updateOrCreate(
            ['email' => 'manager@bumdes.com'],
            [
                'nama' => 'Manager BUMDes',
                'role' => User::ROLE_MANAGER_PUSAT,
                'password' => Hash::make('password'),
            ]
        );

        // Fetch unit IDs dynamically
        $wifiUnit = \App\Models\Unit::where('slug', 'wifi')->first();
        $panganUnit = \App\Models\Unit::where('slug', 'ketahanan-pangan')->first();
        $perdaganganUnit = \App\Models\Unit::where('slug', 'perdagangan-besar')->first();

        // Seeder for Unit Wifi Admin
        if ($wifiUnit) {
            User::updateOrCreate(
                ['email' => 'wifi@bumdes.com'],
                [
                    'nama' => 'Admin Unit Wifi',
                    'role' => User::ROLE_ADMIN_UNIT,
                    'unit_id' => $wifiUnit->id,
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Seeder for Unit Ketahanan Pangan Admin
        if ($panganUnit) {
            User::updateOrCreate(
                ['email' => 'pangan@bumdes.com'],
                [
                    'nama' => 'Admin Unit Ketahanan Pangan',
                    'role' => User::ROLE_ADMIN_UNIT,
                    'unit_id' => $panganUnit->id,
                    'password' => Hash::make('password'),
                ]
            );
        }

        // Seeder for Unit Perdagangan Besar Admin
        if ($perdaganganUnit) {
            User::updateOrCreate(
                ['email' => 'perdagangan@bumdes.com'],
                [
                    'nama' => 'Admin Unit Perdagangan Besar',
                    'role' => User::ROLE_ADMIN_UNIT,
                    'unit_id' => $perdaganganUnit->id,
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}
