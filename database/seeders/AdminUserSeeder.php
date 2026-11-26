<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@kraeplin.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'HR Manager',
            'email' => 'hr@kraeplin.test',
            'password' => Hash::make('password123'),
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);
    }
}