<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // <-- Import User model
use Illuminate\Support\Facades\Hash; // <-- Import Hash facade

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@news254.co.ke',
            'password' => Hash::make('password'), // Set a default password
            'is_admin' => true, // <-- Set as admin directly!
        ]);
    }
}