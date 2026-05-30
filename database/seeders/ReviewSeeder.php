<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Láº¥y ID cá»§a 2 sá»± kiá»‡n Ä‘áº§u tiÃªn
        $events = DB::table('events')->limit(2)->pluck('id')->toArray();
        $event1 = $events[0] ?? null;
        $event2 = $events[1] ?? null;

        // Láº¥y ID cá»§a ngÆ°á»i dÃ¹ng (attendees)
        $attendees = DB::table('users')->pluck('id')->toArray();
        $attendee1 = $attendees[0] ?? null;
        $attendee2 = $attendees[1] ?? null;
        $attendee3 = $attendees[2] ?? null;
        $attendee4 = $attendees[3] ?? null;

        // Chá»‰ táº¡o Ä‘Ã¡nh giÃ¡ náº¿u cÃ³ Ä‘á»§ dá»¯ liá»‡u
        if ($event1 && $event2 && $attendee1 && $attendee2 && $attendee3 && $attendee4) {
            DB::table('reviews')->upsert([
                [
                    'event_id' => $event1,
                    'attendee_id' => $attendee1,
                    'rating' => 5,
                    'comment' => 'Sự kiện tuyệt vời! Âm nhạc hay, không khí tuyệt vời.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => $event1,
                    'attendee_id' => $attendee2,
                    'rating' => 4,
                    'comment' => 'Rất thích buổi hòa nhạc này. Chỉ hơi quá đông.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => $event2,
                    'attendee_id' => $attendee1,
                    'rating' => 5,
                    'comment' => 'Giải đấu tuyệt vời, tổ chức chuyên nghiệp.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'event_id' => $event2,
                    'attendee_id' => $attendee4,
                    'rating' => 4,
                    'comment' => 'Thích hợp tác với mọi người tại đây. Sân tốt.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['event_id', 'attendee_id'], ['rating', 'comment', 'updated_at']);
        }
    }
}