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
        // Admin/Organizers
        User::create([
            'name' => 'Nguyễn Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'organizer',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=admin',
        ]);

        User::create([
            'name' => 'Trần Sơn',
            'email' => 'organizer1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'organizer',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=organizer1',
        ]);

        User::create([
            'name' => 'Lê Quỳnh',
            'email' => 'organizer2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'organizer',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=organizer2',
        ]);

        // Attendees
        User::create([
            'name' => 'Phạm Hải',
            'email' => 'attendee1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee1',
        ]);

        User::create([
            'name' => 'Vũ Minh',
            'email' => 'attendee2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee2',
        ]);

        User::create([
            'name' => 'Đỗ Linh',
            'email' => 'attendee3@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee3',
        ]);

        User::create([
            'name' => 'Bùi Hương',
            'email' => 'attendee4@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee4',
        ]);

        User::create([
            'name' => 'Cao Tùng',
            'email' => 'attendee5@example.com',
            'password' => Hash::make('password123'),
            'role' => 'attendee',
            'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee5',
        ]);
    }
}
