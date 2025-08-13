<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create(['name' => 'Free User', 'email' => 'free@example.com', 'password' => Hash::make('password'), 'tier' => 'free']);
        User::create(['name' => 'Pro User', 'email' => 'pro@example.com', 'password' => Hash::make('password'), 'tier' => 'pro']);
        User::create(['name' => 'Enterprise User', 'email' => 'enterprise@example.com', 'password' => Hash::make('password'), 'tier' => 'enterprise']);
    }
}
