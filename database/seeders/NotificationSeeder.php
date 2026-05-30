<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $event1 = DB::table('events')->where('name', 'Đêm nhạc Acoustic ven sông')->value('id');
        $event2 = DB::table('events')->where('name', 'Workshop Thiết kế UI cho người mới')->value('id');
        $event3 = DB::table('events')->where('name', 'Ngày hội chạy bộ cộng đồng')->value('id');

        $attendee1 = DB::table('users')->where('email', 'attendee1@example.com')->value('id');
        $attendee2 = DB::table('users')->where('email', 'attendee2@example.com')->value('id');
        $attendee3 = DB::table('users')->where('email', 'attendee3@example.com')->value('id');

        if ($event1 && $event2 && $event3 && $attendee1 && $attendee2 && $attendee3) {
            DB::table('notifications')->insert([
                [
                    'user_id' => $attendee1,
                    'event_id' => $event1,
                    'message' => 'Your ticket for "Acoustic Night by the River" has been confirmed!',
                    'is_read' => 1,
                    'created_at' => now()->subDays(5),
                ],
                [
                    'user_id' => $attendee2,
                    'event_id' => $event1,
                    'message' => 'Reminder: "Acoustic Night by the River" starts in 3 days!',
                    'is_read' => 1,
                    'created_at' => now()->subDays(2),
                ],
                [
                    'user_id' => $attendee3,
                    'event_id' => $event1,
                    'message' => 'Your registration for "Acoustic Night by the River" is pending approval.',
                    'is_read' => 0,
                    'created_at' => now(),
                ],
                [
                    'user_id' => $attendee1,
                    'event_id' => $event2,
                    'message' => 'You have been confirmed for "Beginner UI Design Workshop".',
                    'is_read' => 1,
                    'created_at' => now()->subDays(4),
                ],
                [
                    'user_id' => $attendee2,
                    'event_id' => $event3,
                    'message' => 'Confirmed: You are registered for "Community Run Day".',
                    'is_read' => 0,
                    'created_at' => now(),
                ],
            ]);
        }
    }
}