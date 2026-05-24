<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$adminId = DB::table('users')->where('email', 'admin@example.com')->value('id');
$now = now();

$events = [
    ['name' => 'Đêm nhạc Acoustic ven sông', 'category' => 'Music', 'location' => 'The Riverside Cafe, Đà Nẵng', 'date_time' => '2026-06-12 19:00:00', 'capacity' => 120, 'event_type' => 'Free', 'status' => 'published', 'image_url' => 'https://picsum.photos/seed/acoustic/400/250'],
    ['name' => 'Workshop Thiết kế UI cho người mới', 'category' => 'Workshop', 'location' => 'Innovation Hub, TP. Hồ Chí Minh', 'date_time' => '2026-06-18 09:00:00', 'capacity' => 60, 'event_type' => 'Paid', 'status' => 'published', 'image_url' => 'https://picsum.photos/seed/workshop/400/250'],
    ['name' => 'Ngày hội chạy bộ cộng đồng', 'category' => 'Sports', 'location' => 'Công viên Gia Định, TP. Hồ Chí Minh', 'date_time' => '2026-06-21 06:00:00', 'capacity' => 500, 'event_type' => 'Free', 'status' => 'published', 'image_url' => 'https://picsum.photos/seed/running/400/250'],
    ['name' => 'Talkshow AI trong giáo dục', 'category' => 'Technology', 'location' => 'Đại học Bách Khoa Hà Nội', 'date_time' => '2026-06-25 14:00:00', 'capacity' => 200, 'event_type' => 'Free', 'status' => 'published', 'image_url' => 'https://picsum.photos/seed/ai/400/250'],
    ['name' => 'Phiên chợ xanh cuối tuần', 'category' => 'Community', 'location' => 'Nhà Văn hóa Thanh Niên, TP. HCM', 'date_time' => '2026-06-27 08:00:00', 'capacity' => 350, 'event_type' => 'Free', 'status' => 'published', 'image_url' => 'https://picsum.photos/seed/market/400/250'],
];

foreach ($events as $event) {
    DB::table('events')->insert([
        'organizer_id' => $adminId,
        'name' => $event['name'],
        'description' => 'Mô tả sự kiện ' . $event['name'],
        'category' => $event['category'],
        'location' => $event['location'],
        'date_time' => $event['date_time'],
        'capacity' => $event['capacity'],
        'event_type' => $event['event_type'],
        'status' => $event['status'],
        'attendees' => 0,
        'rating' => 4.5,
        'image_url' => $event['image_url'],
        'created_at' => $now,
        'updated_at' => $now,
    ]);
}
echo 'Inserted ' . count($events) . " events\n";