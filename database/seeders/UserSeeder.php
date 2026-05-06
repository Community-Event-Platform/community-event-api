<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Organizer account
        User::create([
            'name' => 'Organizer One',
            'email' => 'organizer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'organizer',
        ]);

        // Attendee account
        User::create([
            'name' => 'Attendee One',
            'email' => 'attendee@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
        ]);
    }
}
