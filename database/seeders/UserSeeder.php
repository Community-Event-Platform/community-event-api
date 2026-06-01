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
        $keepNames = ['Thi Vai', 'Van Tiet'];

        User::whereNotIn('name', $keepNames)->delete();

        User::withTrashed()->updateOrCreate(
            ['email' => 'organizer1@example.com'],
            [
                'name' => 'Trần Sơn',
                'password' => Hash::make('password123'),
                'role' => 'organizer',
                'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=organizer1',
            ]
        );
        User::withTrashed()->updateOrCreate(
            ['email' => 'attendee3@example.com'],
            [
                'name' => 'Đỗ Linh',
                'password' => Hash::make('password123'),
                'role' => 'attendee',
                'avatar_url' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=attendee3',
            ]
        );
    }
}