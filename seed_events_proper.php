<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adminId = DB::table('users')->where('email', 'admin@example.com')->value('id');
$now = now();

$events = [
    [
        'name' => 'Đêm nhạc Acoustic ven sông',
        'description' => "Không gian âm nhạc nhẹ nhàng với các bản acoustic Việt Nam và quốc tế dành cho cộng đồng yêu nhạc.\n\nLưu ý khi tham gia:\n- Vui lòng đến sớm trước giờ biểu diễn 20 phút.\n- Giữ yên lặng trong lúc nghệ sĩ biểu diễn.\n- Hạn chế sử dụng đèn flash khi chụp ảnh.\n\nYêu cầu:\n- Có vé hoặc mã check-in hợp lệ.\n- Tôn trọng không gian và người tham dự khác.",
        'category' => 'Music',
        'location' => 'The Riverside Cafe, Đà Nẵng',
        'date_time' => '2026-06-12 19:00:00',
        'capacity' => 120,
        'event_type' => 'Free',
        'status' => 'published',
        'require_additional_info' => false,
        'custom_form_spec' => null,
        'image_url' => 'https://picsum.photos/seed/demNhacAcoustic/400/250',
    ],
    [
        'name' => 'Workshop Thiết kế UI cho người mới',
        'description' => "Buổi thực hành về tư duy layout, màu sắc, typography và cách xây dựng giao diện web cơ bản.\n\nLưu ý khi tham gia:\n- Mang theo laptop cá nhân.\n- Cài sẵn Figma hoặc Adobe XD.\n- Chuẩn bị sạc pin để sử dụng trong suốt workshop.\n\nYêu cầu:\n- Có kiến thức cơ bản về máy tính.\n- Tham gia đầy đủ phần thực hành.",
        'category' => 'Workshop',
        'location' => 'Innovation Hub, TP. Hồ Chí Minh',
        'date_time' => '2026-06-18 09:00:00',
        'capacity' => 60,
        'event_type' => 'Paid',
        'status' => 'published',
        'require_additional_info' => true,
        'custom_form_spec' => json_encode(['questions' => ['Bạn đã dùng Figma chưa?', 'Bạn mong muốn học kỹ năng nào?']]),
        'image_url' => 'https://picsum.photos/seed/workshopUI/400/250',
    ],
    [
        'name' => 'Ngày hội chạy bộ cộng đồng',
        'description' => "Sự kiện chạy bộ 5km dành cho mọi lứa tuổi nhằm khuyến khích lối sống năng động và lành mạnh.\n\nLưu ý khi tham gia:\n- Mang giày thể thao phù hợp.\n- Uống đủ nước trước khi chạy.\n- Có mặt trước giờ xuất phát 30 phút.\n\nYêu cầu:\n- Đảm bảo đủ sức khỏe tham gia hoạt động thể chất.\n- Tuân thủ hướng dẫn của ban tổ chức.",
        'category' => 'Sports',
        'location' => 'Công viên Gia Định, TP. Hồ Chí Minh',
        'date_time' => '2026-06-21 06:00:00',
        'capacity' => 500,
        'event_type' => 'Free',
        'status' => 'published',
        'require_additional_info' => true,
        'custom_form_spec' => json_encode(['questions' => ['Cỡ áo của bạn?', 'Bạn có vấn đề sức khỏe cần lưu ý không?']]),
        'image_url' => 'https://picsum.photos/seed/chayBo/400/250',
    ],
    [
        'name' => 'Talkshow AI trong giáo dục',
        'description' => "Chia sẻ về cách ứng dụng AI vào học tập, giảng dạy và xây dựng kế hoạch phát triển kỹ năng.\n\nLưu ý khi tham gia:\n- Khuyến khích mang laptop để thực hành demo AI.\n- Tắt chuông điện thoại trong lúc diễn giả trình bày.\n- Chuẩn bị câu hỏi để giao lưu cuối chương trình.\n\nYêu cầu:\n- Quan tâm đến công nghệ và giáo dục.\n- Tham gia đúng giờ để không bỏ lỡ nội dung chính.",
        'category' => 'Technology',
        'location' => 'Đại học Bách Khoa Hà Nội',
        'date_time' => '2026-06-25 14:00:00',
        'capacity' => 200,
        'event_type' => 'Free',
        'status' => 'published',
        'require_additional_info' => false,
        'custom_form_spec' => null,
        'image_url' => 'https://picsum.photos/seed/talkshowAI/400/250',
    ],
    [
        'name' => 'Phiên chợ xanh cuối tuần',
        'description' => "Không gian mua sắm sản phẩm thủ công, nông sản sạch và các hoạt động tái chế cho gia đình.\n\nLưu ý khi tham gia:\n- Mang túi cá nhân để hạn chế sử dụng nhựa.\n- Giữ vệ sinh chung tại khu vực sự kiện.\n- Bảo quản tư trang cá nhân cẩn thận.\n\nYêu cầu:\n- Tuân thủ quy định phân loại rác của sự kiện.\n- Có ý thức bảo vệ môi trường.",
        'category' => 'Community',
        'location' => 'Nhà Văn hóa Thanh Niên, TP. Hồ Chí Minh',
        'date_time' => '2026-06-27 08:00:00',
        'capacity' => 350,
        'event_type' => 'Free',
        'status' => 'published',
        'require_additional_info' => false,
        'custom_form_spec' => null,
        'image_url' => 'https://picsum.photos/seed/choXanh/400/250',
    ],
];

DB::table('events')->where('id', '>', 0)->delete();

foreach ($events as $event) {
    DB::table('events')->insert([
        'organizer_id' => $adminId,
        'name' => $event['name'],
        'description' => $event['description'],
        'category' => $event['category'],
        'location' => $event['location'],
        'date_time' => $event['date_time'],
        'capacity' => $event['capacity'],
        'event_type' => $event['event_type'],
        'status' => $event['status'],
        'attendees' => 0,
        'rating' => 4.5,
        'price' => null,
        'require_additional_info' => $event['require_additional_info'],
        'custom_form_spec' => $event['custom_form_spec'],
        'image_url' => $event['image_url'],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}

echo "Seeded " . count($events) . " events with correct data\n";
