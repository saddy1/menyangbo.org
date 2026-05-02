<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'sadanand@ioepc.edu.np'],
            [
                'name'              => 'Sadanand Paneru',
                'password'          => Hash::make('S@ddy9843'),
                'role'              => User::ROLE_SUPER_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@menyanbo.org'],
            [
                'name'              => 'Menyanbo Admin',
                'password'          => Hash::make('Admin@menyanbo123'),
                'role'              => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }
}
