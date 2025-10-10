<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'sadanand@ioepc.edu.np'], // unique field
            [
                'name' => 'Sadanand Paneru',
                'password' => bcrypt('S@ddy9843'),
                'contact' => '9843521965',
            ]
        );
        Admin::updateOrCreate(
        ['email' => 'admin@menyanbo.org'], // unique field
        [
            'name' => 'Menyanbo Admin',
            'password' => bcrypt('Admin@menyanbo123'),
            'contact' => '9843521965',
        ]
    );
    }
}
