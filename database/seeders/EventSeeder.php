<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy ID của các organizer
        $organizer1 = DB::table('users')->where('email', 'admin@example.com')->value('id');
        $organizer2 = DB::table('users')->where('email', 'organizer1@example.com')->value('id');
        $organizer3 = DB::table('users')->where('email', 'organizer2@example.com')->value('id');

        // Tạo các sự kiện
        DB::table('events')->insert([
            [
                'organizer_id' => $organizer1,
                'name' => 'Đêm nhạc Acoustic',
                'description' => 'Một đêm nhạc nhẹ nhàng thư giãn với các ca sỹ nổi tiếng. Thưởng thức âm nhạc trong không khí ấm áp.',
                'category' => 'Music',
                'location' => 'Cà phê Highland, Quận 1, TP.HCM',
                'date_time' => '2026-06-15 19:00:00',
                'capacity' => 100,
                'event_type' => 'Free',
                'status' => 'Published',
                'require_additional_info' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=500&h=300&fit=crop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer2,
                'name' => 'Giải bóng đá cộng đồng',
                'description' => 'Giải bóng đá giao hữu cho mọi người, tất cả trình độ đều được chào đón.',
                'category' => 'Sports',
                'location' => 'Sân vận động Chi Lăng, Quận 7, TP.HCM',
                'date_time' => '2026-06-20 08:00:00',
                'capacity' => 200,
                'event_type' => 'Free',
                'status' => 'Published',
                'require_additional_info' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?w=500&h=300&fit=crop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer3,
                'name' => 'Workshop: Lập trình Web với Laravel',
                'description' => 'Học hỏi về Framework Laravel từ những chuyên gia trong ngành. Khóa học bao gồm 3 buổi.',
                'category' => 'Workshop',
                'location' => 'Trung tâm Công nghệ, Quận Tây Hồ, Hà Nội',
                'date_time' => '2026-06-25 14:00:00',
                'capacity' => 50,
                'event_type' => 'Paid',
                'status' => 'Published',
                'require_additional_info' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=300&fit=crop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer1,
                'name' => 'Hội thảo về Phát triển bền vững',
                'description' => 'Trao đổi về các giải pháp phát triển bền vững cho cộng đồng. Buổi hội thảo mở để tất cả mọi người.',
                'category' => 'Conference',
                'location' => 'Trung tâm Hội nghị Quốc tế, Hà Nội',
                'date_time' => '2026-07-10 09:00:00',
                'capacity' => 150,
                'event_type' => 'Free',
                'status' => 'Published',
                'require_additional_info' => 0,                'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop',                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer2,
                'name' => 'Cuộc thi Startup Pitch',
                'description' => 'Cơ hội thể hiện ý tưởng kinh doanh của bạn trước các nhà đầu tư. Giải thưởng lớn cho đội chiến thắng.',
                'category' => 'Competition',
                'location' => 'Tòa nhà InCube, TP.HCM',
                'date_time' => '2026-07-15 10:00:00',
                'capacity' => 100,
                'event_type' => 'Free',
                'status' => 'Draft',
                'require_additional_info' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
