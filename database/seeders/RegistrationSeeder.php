<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy ID của các sự kiện và người dùng
        $event1 = DB::table('events')->where('name', 'Đêm nhạc Acoustic')->value('id');
        $event2 = DB::table('events')->where('name', 'Giải bóng đá cộng đồng')->value('id');
        $event3 = DB::table('events')->where('name', 'Workshop: Lập trình Web với Laravel')->value('id');

        $attendee1 = DB::table('users')->where('email', 'attendee1@example.com')->value('id');
        $attendee2 = DB::table('users')->where('email', 'attendee2@example.com')->value('id');
        $attendee3 = DB::table('users')->where('email', 'attendee3@example.com')->value('id');
        $attendee4 = DB::table('users')->where('email', 'attendee4@example.com')->value('id');
        $attendee5 = DB::table('users')->where('email', 'attendee5@example.com')->value('id');

        // Tạo đăng ký cho các sự kiện
        DB::table('registrations')->insert([
            [
                'event_id' => $event1,
                'attendee_id' => $attendee1,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event1,
                'attendee_id' => $attendee2,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event1,
                'attendee_id' => $attendee3,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event2,
                'attendee_id' => $attendee1,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event2,
                'attendee_id' => $attendee4,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event2,
                'attendee_id' => $attendee5,
                'status' => 'Cancelled',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event3,
                'attendee_id' => $attendee2,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $event3,
                'attendee_id' => $attendee3,
                'status' => 'Confirmed',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
