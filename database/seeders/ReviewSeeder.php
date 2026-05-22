<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy ID của các sự kiện
        $event1 = DB::table('events')->where('name', 'Đêm nhạc Acoustic')->value('id');
        $event2 = DB::table('events')->where('name', 'Giải bóng đá cộng đồng')->value('id');

        $attendee1 = DB::table('users')->where('email', 'attendee1@example.com')->value('id');
        $attendee2 = DB::table('users')->where('email', 'attendee2@example.com')->value('id');
        $attendee3 = DB::table('users')->where('email', 'attendee3@example.com')->value('id');
        $attendee4 = DB::table('users')->where('email', 'attendee4@example.com')->value('id');

        // Tạo đánh giá cho các sự kiện
        DB::table('reviews')->insert([
            [
                'event_id' => $event1,
                'attendee_id' => $attendee1,
                'rating' => 5,
                'comment' => 'Sự kiện tuyệt vời! Âm nhạc hay, không khí tuyệt vời.',
                'created_at' => now(),
            ],
            [
                'event_id' => $event1,
                'attendee_id' => $attendee2,
                'rating' => 4,
                'comment' => 'Rất thích buổi hòa nhạc này. Chỉ hơi quá đông.',
                'created_at' => now(),
            ],
            [
                'event_id' => $event2,
                'attendee_id' => $attendee1,
                'rating' => 5,
                'comment' => 'Giải đấu tuyệt vời, tổ chức chuyên nghiệp.',
                'created_at' => now(),
            ],
            [
                'event_id' => $event2,
                'attendee_id' => $attendee4,
                'rating' => 4,
                'comment' => 'Thích hợp tác với mọi người tại đây. Sân tốt.',
                'created_at' => now(),
            ],
        ]);
    }
}