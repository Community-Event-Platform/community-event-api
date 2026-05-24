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
                    'message' => 'Đơn đăng ký sự kiện "Đêm nhạc Acoustic ven sông" của bạn đã được xác nhận!',
                    'is_read' => 1,
                    'created_at' => now()->subDays(5),
                ],
                [
                    'user_id' => $attendee2,
                    'event_id' => $event1,
                    'message' => 'Sự kiện "Đêm nhạc Acoustic ven sông" sẽ diễn ra trong 3 ngày nữa!',
                    'is_read' => 1,
                    'created_at' => now()->subDays(2),
                ],
                [
                    'user_id' => $attendee3,
                    'event_id' => $event1,
                    'message' => 'Đơn đăng ký sự kiện "Đêm nhạc Acoustic ven sông" của bạn đang chờ xác nhận.',
                    'is_read' => 0,
                    'created_at' => now(),
                ],
                [
                    'user_id' => $attendee1,
                    'event_id' => $event2,
                    'message' => 'Bạn đã được xác nhận tham gia sự kiện "Workshop Thiết kế UI cho người mới".',
                    'is_read' => 1,
                    'created_at' => now()->subDays(4),
                ],
                [
                    'user_id' => $attendee2,
                    'event_id' => $event3,
                    'message' => 'Xác nhận: Bạn đã đăng ký cho sự kiện "Ngày hội chạy bộ cộng đồng".',
                    'is_read' => 0,
                    'created_at' => now(),
                ],
            ]);
        }
    }
}