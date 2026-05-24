<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Organizer',
                'password' => Hash::make('123456'),
                'role' => 'organizer',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $adminId = DB::table('users')->where('email', 'admin@example.com')->value('id');

        DB::table('events')->insert([
            [
                'organizer_id' => $adminId,
                'name' => 'Đêm nhạc Acoustic ven sông',
                'description' => 'Không gian âm nhạc nhẹ nhàng với các bản acoustic Việt Nam và quốc tế dành cho cộng đồng yêu nhạc.

Lưu ý khi tham gia:
- Vui lòng đến sớm trước giờ biểu diễn 20 phút.
- Giữ yên lặng trong lúc nghệ sĩ biểu diễn.
- Hạn chế sử dụng đèn flash khi chụp ảnh.

Yêu cầu:
- Có vé hoặc mã check-in hợp lệ.
- Tôn trọng không gian và người tham dự khác.',
                'category_id' => 'Music',
                'location' => 'The Riverside Cafe, Đà Nẵng',
                'date_time' => '2026-06-12 19:00:00',
                'capacity' => 120,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-01.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Workshop Thiết kế UI cho người mới',
                'description' => 'Buổi thực hành về tư duy layout, màu sắc, typography và cách xây dựng giao diện web cơ bản.

Lưu ý khi tham gia:
- Mang theo laptop cá nhân.
- Cài sẵn Figma hoặc Adobe XD.
- Chuẩn bị sạc pin để sử dụng trong suốt workshop.

Yêu cầu:
- Có kiến thức cơ bản về máy tính.
- Tham gia đầy đủ phần thực hành.',
                'category_id' => 'Workshop',
                'location' => 'Innovation Hub, TP. Hồ Chí Minh',
                'date_time' => '2026-06-18 09:00:00',
                'capacity' => 60,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 299000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn đã dùng Figma chưa?', 'Bạn mong muốn học kỹ năng nào?']]),
                'image_url' => 'event-02.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Ngày hội chạy bộ cộng đồng',
                'description' => 'Sự kiện chạy bộ 5km dành cho mọi lứa tuổi nhằm khuyến khích lối sống năng động và lành mạnh.

Lưu ý khi tham gia:
- Mang giày thể thao phù hợp.
- Uống đủ nước trước khi chạy.
- Có mặt trước giờ xuất phát 30 phút.

Yêu cầu:
- Đảm bảo đủ sức khỏe tham gia hoạt động thể chất.
- Tuân thủ hướng dẫn của ban tổ chức.',
                'category_id' => 'Sports',
                'location' => 'Công viên Gia Định, TP. Hồ Chí Minh',
                'date_time' => '2026-06-21 06:00:00',
                'capacity' => 500,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Cỡ áo của bạn?', 'Bạn có vấn đề sức khỏe cần lưu ý không?']]),
                'image_url' => 'event-03.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Talkshow AI trong giáo dục',
                'description' => 'Chia sẻ về cách ứng dụng AI vào học tập, giảng dạy và xây dựng kế hoạch phát triển kỹ năng.

Lưu ý khi tham gia:
- Khuyến khích mang laptop để thực hành demo AI.
- Tắt chuông điện thoại trong lúc diễn giả trình bày.
- Chuẩn bị câu hỏi để giao lưu cuối chương trình.

Yêu cầu:
- Quan tâm đến công nghệ và giáo dục.
- Tham gia đúng giờ để không bỏ lỡ nội dung chính.',
                'category_id' => 'Technology',
                'location' => 'Đại học Bách Khoa Hà Nội',
                'date_time' => '2026-06-25 14:00:00',
                'capacity' => 200,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-04.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Phiên chợ xanh cuối tuần',
                'description' => 'Không gian mua sắm sản phẩm thủ công, nông sản sạch và các hoạt động tái chế cho gia đình.

Lưu ý khi tham gia:
- Mang túi cá nhân để hạn chế sử dụng nhựa.
- Giữ vệ sinh chung tại khu vực sự kiện.
- Bảo quản tư trang cá nhân cẩn thận.

Yêu cầu:
- Tuân thủ quy định phân loại rác của sự kiện.
- Có ý thức bảo vệ môi trường.',
                'category_id' => 'Community',
                'location' => 'Nhà Văn hóa Thanh Niên, TP. Hồ Chí Minh',
                'date_time' => '2026-06-27 08:00:00',
                'capacity' => 350,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-05.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Lớp học nấu món Việt hiện đại',
                'description' => 'Chef địa phương hướng dẫn nấu các món Việt quen thuộc theo phong cách trình bày hiện đại.


Lưu ý khi tham gia:
- Mang tạp dề nếu cần.
- Báo trước nếu có dị ứng thực phẩm.
- Có mặt đúng giờ để chuẩn bị nguyên liệu.

Yêu cầu:
- Tuân thủ quy tắc an toàn nhà bếp.
- Tham gia đầy đủ các phần thực hành nấu ăn.',
                'category_id' => 'Food',
                'location' => 'Saigon Culinary Studio',
                'date_time' => '2026-07-03 18:30:00',
                'capacity' => 35,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 399000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn có dị ứng thực phẩm nào không?']]),
                'image_url' => 'event-06.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Triển lãm ảnh Thành phố trong tôi',
                'description' => 'Triển lãm ảnh ghi lại nhịp sống đô thị, con người và những góc nhìn thân thuộc của thành phố.

Lưu ý khi tham gia:
- Không chạm vào hiện vật trưng bày.
- Tắt đèn flash khi chụp ảnh.
- Giữ trật tự trong khu vực triển lãm.

Yêu cầu:
- Tuân thủ hướng dẫn của nhân viên triển lãm.
- Giữ gìn không gian nghệ thuật chung.',
                'category_id' => 'Art',
                'location' => 'Bảo tàng Mỹ thuật TP. Hồ Chí Minh',
                'date_time' => '2026-07-05 09:30:00',
                'capacity' => 180,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-07.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Khởi nghiệp xã hội: từ ý tưởng đến sản phẩm',
                'description' => 'Buổi meetup kết nối các nhóm dự án xã hội, mentor và nhà đầu tư thiên thần.

Lưu ý khi tham gia:
- Chuẩn bị danh thiếp hoặc hồ sơ dự án.
- Ăn mặc lịch sự phù hợp môi trường networking.
- Đến sớm để check-in và giao lưu.

Yêu cầu:
- Có tinh thần kết nối và học hỏi.
- Tôn trọng ý tưởng và quan điểm của người khác.',
                'category_id' => 'Meetup',
                'location' => 'Dreamplex Nguyễn Trung Ngạn',
                'date_time' => '2026-07-09 17:30:00',
                'capacity' => 90,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn đang làm dự án trong lĩnh vực nào?']]),
                'image_url' => 'event-08.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Cuộc thi lập trình Hack for Community',
                'description' => 'Cuộc thi 24 giờ dành cho sinh viên xây dựng giải pháp công nghệ phục vụ cộng đồng.

Lưu ý khi tham gia:
- Mang laptop và thiết bị cần thiết.
- Chuẩn bị sẵn source code hoặc tài liệu tham khảo.
- Nghỉ ngơi hợp lý trong thời gian thi.

Yêu cầu:
- Tham gia theo đúng đội đã đăng ký.
- Tuân thủ quy định và thời gian cuộc thi.',
                'category_id' => 'Competition',
                'location' => 'FPT Software Campus, Đà Nẵng',
                'date_time' => '2026-07-12 08:00:00',
                'capacity' => 160,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Tên đội của bạn?', 'Số thành viên trong đội?']]),
                'image_url' => 'event-09.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Webinar Quản lý tài chính cá nhân',
                'description' => 'Chuyên gia tài chính chia sẻ cách lập ngân sách, tiết kiệm và xây dựng mục tiêu tài chính.

Lưu ý khi tham gia:
- Kiểm tra kết nối internet trước khi tham gia webinar.
- Vào phòng họp online trước 10 phút.
- Chuẩn bị sổ tay để ghi chú.

Yêu cầu:
- Sử dụng tên thật khi tham gia phòng họp.
- Giữ thái độ lịch sự trong phần hỏi đáp.',
                'category_id' => 'Webinar',
                'location' => 'Online',
                'date_time' => '2026-07-15 20:00:00',
                'capacity' => 1000,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-10.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Ngày hội sách và văn hóa đọc',
                'description' => 'Hoạt động trao đổi sách, giao lưu tác giả trẻ và workshop xây dựng thói quen đọc.

Lưu ý khi tham gia:
- Có thể mang sách để trao đổi cùng cộng đồng.
- Giữ gìn sách và tài liệu cẩn thận.
- Hạn chế gây ồn trong khu vực đọc sách.

Yêu cầu:
- Tôn trọng diễn giả và người tham dự.
- Khuyến khích tham gia các hoạt động giao lưu.',
                'category_id' => 'Education',
                'location' => 'Đường sách Nguyễn Văn Bình',
                'date_time' => '2026-07-19 08:30:00',
                'capacity' => 300,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-11.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Festival Ẩm thực đường phố',
                'description' => 'Sự kiện quy tụ các gian hàng món ăn địa phương, âm nhạc ngoài trời và hoạt động gia đình.

Lưu ý khi tham gia:
- Mang theo tiền mặt hoặc ví điện tử.
- Giữ vệ sinh tại khu vực ăn uống.
- Cẩn thận khi di chuyển ở nơi đông người.

Yêu cầu:
- Xếp hàng văn minh khi mua hàng.
- Tuân thủ quy định an toàn thực phẩm của sự kiện.',
                'category_id' => 'Festival',
                'location' => 'Công viên 23/9, TP. Hồ Chí Minh',
                'date_time' => '2026-07-24 16:00:00',
                'capacity' => 800,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 199000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-12.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Lớp Yoga sáng chủ nhật',
                'description' => 'Buổi yoga ngoài trời phù hợp cho người mới bắt đầu, tập trung vào thở và giãn cơ.

Lưu ý khi tham gia:
- Mang thảm tập cá nhân nếu có.
- Mặc trang phục thoải mái dễ vận động.
- Không ăn quá no trước giờ tập.

Yêu cầu:
- Có mặt trước giờ tập 15 phút.
- Tuân theo hướng dẫn của huấn luyện viên.',
                'category_id' => 'Sports',
                'location' => 'Công viên Lê Văn Tám',
                'date_time' => '2026-07-26 06:30:00',
                'capacity' => 80,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn có cần mượn thảm tập không?']]),
                'image_url' => 'event-13.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Seminar Cloud Computing căn bản',
                'description' => 'Giới thiệu nền tảng điện toán đám mây, kiến trúc triển khai và các case study thực tế.

Lưu ý khi tham gia:
- Mang laptop để theo dõi demo kỹ thuật.
- Chuẩn bị tài khoản cloud nếu cần thực hành.
- Tắt âm điện thoại trong buổi seminar.

Yêu cầu:
- Có kiến thức cơ bản về CNTT là lợi thế.
- Tham gia đầy đủ phần trao đổi cuối buổi.',
                'category_id' => 'Technology',
                'location' => 'VNG Campus',
                'date_time' => '2026-07-30 13:30:00',
                'capacity' => 150,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-14.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Workshop Kỹ năng thuyết trình',
                'description' => 'Thực hành xây dựng nội dung, trình bày slide và kiểm soát giọng nói trước đám đông.

Lưu ý khi tham gia:
- Chuẩn bị trước một chủ đề thuyết trình ngắn.
- Mang laptop nếu muốn chỉnh sửa slide trực tiếp.
- Sẵn sàng tham gia phần thực hành nhóm.

Yêu cầu:
- Chủ động phát biểu và tương tác.
- Hoàn thành các bài tập trình bày được giao.',
                'category_id' => 'Workshop',
                'location' => 'PNV Training Center, Đà Nẵng',
                'date_time' => '2026-08-02 09:00:00',
                'capacity' => 45,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 349000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn muốn cải thiện điểm nào khi thuyết trình?']]),
                'image_url' => 'event-15.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Giao lưu tình nguyện viên mùa hè',
                'description' => 'Kết nối các nhóm tình nguyện, chia sẻ kinh nghiệm tổ chức hoạt động và tuyển thành viên mới.

Lưu ý khi tham gia:
- Mang theo áo nhóm hoặc đồng phục nếu có.
- Giữ gìn hình ảnh tích cực của cộng đồng tình nguyện.
- Chủ động giao lưu với các nhóm khác.

Yêu cầu:
- Có tinh thần hỗ trợ cộng đồng.
- Tôn trọng các hoạt động và diễn giả chia sẻ.',
                'category_id' => 'Community',
                'location' => 'Cung Văn hóa Lao động TP. Hồ Chí Minh',
                'date_time' => '2026-08-06 18:00:00',
                'capacity' => 220,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-16.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Đêm phim ngoài trời',
                'description' => 'Buổi chiếu phim cộng đồng với khu vực picnic, đồ ăn nhẹ và thảo luận sau phim.

Lưu ý khi tham gia:
- Mang khăn hoặc thảm ngồi nếu cần.
- Không quay phim hoặc livestream nội dung chiếu.
- Giữ yên lặng trong thời gian xem phim.

Yêu cầu:
- Có mặt đúng giờ trước khi phim bắt đầu.
- Giữ vệ sinh khu vực picnic sau khi kết thúc.',
                'category_id' => 'Entertainment',
                'location' => 'Sala Park, TP. Hồ Chí Minh',
                'date_time' => '2026-08-09 19:00:00',
                'capacity' => 250,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-17.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Bootcamp React trong 1 ngày',
                'description' => 'Lớp học chuyên sâu giúp người tham dự xây dựng một ứng dụng React hoàn chỉnh từ đầu.


Lưu ý khi tham gia:
- Mang laptop đã cài Node.js và VS Code.
- Kiểm tra kết nối Wi-Fi trước buổi học.
- Chuẩn bị kiến thức JavaScript cơ bản.

Yêu cầu:
- Tham gia đầy đủ các phần coding thực hành.
- Chủ động trao đổi khi gặp lỗi kỹ thuật.',
                'category_id' => 'Technology',
                'location' => 'CodeGym Hà Nội',
                'date_time' => '2026-08-15 08:30:00',
                'capacity' => 70,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 499000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Bạn đã học JavaScript bao lâu?']]),
                'image_url' => 'event-18.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Ngày hội định hướng nghề nghiệp',
                'description' => 'Các diễn giả từ nhiều ngành chia sẻ lộ trình nghề nghiệp, kỹ năng cần có và cơ hội thực tập.

Lưu ý khi tham gia:
- Chuẩn bị CV nếu muốn ứng tuyển thực tập.
- Ăn mặc lịch sự phù hợp môi trường chuyên nghiệp.
- Mang sổ tay để ghi chú thông tin tuyển dụng.

Yêu cầu:
- Chủ động đặt câu hỏi cho diễn giả.
- Tôn trọng nội quy hội trường.',
                'category_id' => 'Education',
                'location' => 'Trung tâm Hội nghị White Palace',
                'date_time' => '2026-08-22 08:00:00',
                'capacity' => 400,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 0,
                'event_type' => 'Free',
                'status' => 'published',
                'require_additional_info' => true,
                'custom_form_spec' => json_encode(['questions' => ['Ngành nghề bạn quan tâm?']]),
                'image_url' => 'event-19.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'organizer_id' => $adminId,
                'name' => 'Gala kết nối cộng đồng EventHub',
                'description' => 'Đêm gặp gỡ cuối mùa với phần networking, trao giải dự án nổi bật và biểu diễn nghệ thuật.

Lưu ý khi tham gia:
- Ăn mặc lịch sự hoặc theo dress code của chương trình.
- Mang mã QR check-in để vào cổng nhanh chóng.
- Đến đúng giờ để tham gia phần khai mạc.

Yêu cầu:
- Giữ thái độ chuyên nghiệp khi networking.
- Tôn trọng khách mời và các tiết mục biểu diễn.',
                'category_id' => 'Networking',
                'location' => 'Gem Center, TP. Hồ Chí Minh',
                'date_time' => '2026-08-29 18:30:00',
                'capacity' => 300,
                'attendees' => 0,
                'rating' => 4.5,
                'price' => 599000,
                'event_type' => 'Paid',
                'status' => 'published',
                'require_additional_info' => false,
                'custom_form_spec' => null,
                'image_url' => 'event-20.jpg',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}