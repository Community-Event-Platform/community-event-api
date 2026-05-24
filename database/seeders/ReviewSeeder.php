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
            DB::table('reviews')->insert([
                [
                    'event_id' => $event1,
                    'attendee_id' => $attendee1,
                    'rating' => 5,
                    'comment' => 'Sá»± kiá»‡n tuyá»‡t vá»i! Ã‚m nháº¡c hay, khÃ´ng khÃ­ tuyá»‡t vá»i.',
                    'created_at' => now(),
                ],
                [
                    'event_id' => $event1,
                    'attendee_id' => $attendee2,
                    'rating' => 4,
                    'comment' => 'Ráº¥t thÃ­ch buá»•i hÃ²a nháº¡c nÃ y. Chá»‰ hÆ¡i quÃ¡ Ä‘Ã´ng.',
                    'created_at' => now(),
                ],
                [
                    'event_id' => $event2,
                    'attendee_id' => $attendee1,
                    'rating' => 5,
                    'comment' => 'Giáº£i Ä‘áº¥u tuyá»‡t vá»i, tá»• chá»©c chuyÃªn nghiá»‡p.',
                    'created_at' => now(),
                ],
                [
                    'event_id' => $event2,
                    'attendee_id' => $attendee4,
                    'rating' => 4,
                    'comment' => 'ThÃ­ch há»£p tÃ¡c vá»›i má»i ngÆ°á»i táº¡i Ä‘Ã¢y. SÃ¢n tá»‘t.',
                    'created_at' => now(),
                ],
            ]);
        }
    }
}