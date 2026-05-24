<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy ID của 3 sự kiện đầu tiên
        $events = DB::table('events')->limit(3)->pluck('id')->toArray();
        $event1 = $events[0] ?? null;
        $event2 = $events[1] ?? null;
        $event3 = $events[2] ?? null;

        // Lấy ID của người dùng (attendees)
        $attendees = DB::table('users')->pluck('id')->toArray();
        $attendee1 = $attendees[0] ?? null;
        $attendee2 = $attendees[1] ?? null;
        $attendee3 = $attendees[2] ?? null;
        $attendee4 = $attendees[3] ?? null;
        $attendee5 = $attendees[4] ?? null;

        // Chỉ tạo đăng ký nếu có đủ dữ liệu
        if ($event1 && $event2 && $event3 && $attendee1 && $attendee2 && $attendee3 && $attendee4 && $attendee5) {
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
}