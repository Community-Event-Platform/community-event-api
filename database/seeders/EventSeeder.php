<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tạo User Organizer
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin Organizer',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
            'role' => 'organizer',
            'created_at' => now(),
        ]);

        // 2. Tạo Category
        $musicId = DB::table('categories')->insertGetId(['name' => 'Music', 'created_at' => now()]);
        $sportsId = DB::table('categories')->insertGetId(['name' => 'Sports', 'created_at' => now()]);

        // 3. Tạo Event
        DB::table('events')->insert([
            [
                'organizer_id' => $adminId,
                'category_id' => $musicId,
                'name' => 'Đêm nhạc Acoustic',
                'description' => 'Một đêm nhạc nhẹ nhàng thư giãn.',
                'location' => 'Cà phê Highland',
                'event_date' => '2026-05-20 19:00:00',
                'capacity' => 50,
                'status' => 'published',
                'created_at' => now(),
            ],
            [
                'organizer_id' => $adminId,
                'category_id' => $sportsId,
                'name' => 'Giải bóng đá cộng đồng',
                'description' => 'Giải bóng đá giao hữu cho mọi người.',
                'location' => 'Sân vận động Chi Lăng',
                'event_date' => '2026-05-25 08:00:00',
                'capacity' => 100,
                'status' => 'published',
                'created_at' => now(),
            ]
        ]);
    }
}
