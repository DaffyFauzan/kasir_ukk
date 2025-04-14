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
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Administrator',
        ]);

        User::create([
            'name' => 'Staff',
            'email' => 'staff@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'Staff',
        ]);
    }
}
