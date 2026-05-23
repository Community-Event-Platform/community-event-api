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

        // Tạo các sự kiện với đầy đủ thông tin
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
                'attendees' => 45,
                'rating' => 4.8,
                'price' => null,
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
                'attendees' => 128,
                'rating' => 4.6,
                'price' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer3,
                'name' => 'Workshop: Lập trình Web với Laravel',
                'description' => 'Học hỏi về Framework Laravel từ những chuyên gia trong ngành. Khóa học bao gồm 3 buổi.',
                'category' => 'Education',
                'location' => 'Trung tâm Công nghệ, Quận Tây Hồ, Hà Nội',
                'date_time' => '2026-06-25 14:00:00',
                'capacity' => 50,
                'event_type' => 'Paid',
                'status' => 'Published',
                'require_additional_info' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=500&h=300&fit=crop',
                'attendees' => 32,
                'rating' => 4.9,
                'price' => 299000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer1,
                'name' => 'Hội thảo về Phát triển bền vững',
                'description' => 'Trao đổi về các giải pháp phát triển bền vững cho cộng đồng. Buổi hội thảo mở để tất cả mọi người.',
                'category' => 'Community',
                'location' => 'Trung tâm Hội nghị Quốc tế, Hà Nội',
                'date_time' => '2026-07-10 09:00:00',
                'capacity' => 150,
                'event_type' => 'Free',
                'status' => 'Published',
                'require_additional_info' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop',
                'attendees' => 87,
                'rating' => 4.7,
                'price' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer2,
                'name' => 'Cuộc thi Startup Pitch',
                'description' => 'Cơ hội thể hiện ý tưởng kinh doanh của bạn trước các nhà đầu tư. Giải thưởng lớn cho đội chiến thắng.',
                'category' => 'Community',
                'location' => 'Tòa nhà InCube, TP.HCM',
                'date_time' => '2026-07-15 10:00:00',
                'capacity' => 100,
                'event_type' => 'Free',
                'status' => 'Published',
                'require_additional_info' => 1,
                'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&h=300&fit=crop',
                'attendees' => 54,
                'rating' => 4.5,
                'price' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'organizer_id' => $organizer3,
                'name' => 'Festival Ẩm thực Đất Việt',
                'description' => 'Lễ hội ẩm thực truyền thống Việt Nam với các đặc sản từ các vùng.',
                'category' => 'Food',
                'location' => 'Công viên Gia Định, TP.HCM',
                'date_time' => '2026-08-01 10:00:00',
                'capacity' => 300,
                'event_type' => 'Paid',
                'status' => 'Published',
                'require_additional_info' => 0,
                'image_url' => 'https://images.unsplash.com/photo-1555939594-58d7cb561404?w=500&h=300&fit=crop',
                'attendees' => 156,
                'rating' => 4.8,
                'price' => 99000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
