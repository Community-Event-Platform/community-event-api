<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $event1 = DB::table('events')->where('name', 'Đêm nhạc Acoustic')->value('id');
        $event2 = DB::table('events')->where('name', 'Giải bóng đá cộng đồng')->value('id');
        $event3 = DB::table('events')->where('name', 'Workshop: Lập trình Web với Laravel')->value('id');

        $attendee1 = DB::table('users')->where('email', 'attendee1@example.com')->value('id');
        $attendee2 = DB::table('users')->where('email', 'attendee2@example.com')->value('id');
        $attendee3 = DB::table('users')->where('email', 'attendee3@example.com')->value('id');

        // Tạo thông báo
        DB::table('notifications')->insert([
            [
                'user_id' => $attendee1,
                'event_id' => $event1,
                'message' => 'Đơn đăng ký sự kiện "Đêm nhạc Acoustic" của bạn đã được xác nhận!',
                'is_read' => 1,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => $attendee2,
                'event_id' => $event1,
                'message' => 'Sự kiện "Đêm nhạc Acoustic" sẽ diễn ra trong 3 ngày nữa!',
                'is_read' => 1,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => $attendee3,
                'event_id' => $event1,
                'message' => 'Đơn đăng ký sự kiện "Đêm nhạc Acoustic" của bạn đang chờ xác nhận.',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $attendee1,
                'event_id' => $event2,
                'message' => 'Bạn đã được xác nhận tham gia sự kiện "Giải bóng đá cộng đồng".',
                'is_read' => 1,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => $attendee2,
                'event_id' => $event3,
                'message' => 'Xác nhận: Bạn đã đăng ký cho Workshop "Lập trình Web với Laravel".',
                'is_read' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
