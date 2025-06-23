<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'surname' => 'User',
            'email' => 'apicomplexadmin@gmail.com',
            'phone' => '+79000000000',
            'password' => Hash::make('Admin735!'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Станислав',
            'surname' => 'Гладких',
            'email' => 'gl4dkikh@mail.ru',
            'phone' => '79528955302',
            'password' => Hash::make('Admin735!'),
            'role' => 'admin',
        ]);
    }
}
