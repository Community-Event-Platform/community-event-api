# EventHub — Community Event API

Mô tả
-
Backend API (Laravel) cung cấp toàn bộ endpoint cho nền tảng EventHub: quản lý sự kiện, danh mục, đăng ký, thông báo và người dùng.

Yêu cầu
-
- PHP 8.1+ với các extension chuẩn (OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, BCMath)
- Composer
- MySQL / PostgreSQL hoặc DB được hỗ trợ
- Redis (nếu sử dụng queue/cache)

Cài đặt nhanh (local)
-
1. Cài dependencies PHP:

```bash
composer install
```

2. Copy `.env` và cấu hình:

```bash
cp .env.example .env
# chỉnh sửa .env: DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, MAIL_*, APP_URL...
```

3. Tạo APP KEY:

```bash
php artisan key:generate
```

4. Chạy migration & seeder (nếu cần):

```bash
php artisan migrate --seed
```

5. (Tùy chọn) Cài node dependencies cho assets nếu bạn sẽ build front-end:

```bash
npm install
npm run build
```

6. Chạy server local:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

API docs & Postman
-
- File Postman collection có tại `Community Event Platform.postman_collection.json` trong thư mục gốc.
- Nếu có swagger/openapi, thêm đường dẫn vào đây.

Lệnh artisan thường dùng
-
- `php artisan migrate` — chạy migration
- `php artisan db:seed` — chạy seeder
- `php artisan queue:work` — chạy worker queue
- `php artisan config:cache` — cache cấu hình

Kiểm thử
-
- Chạy test PHPUnit:

```bash
./vendor/bin/phpunit
```

Triển khai (tóm tắt)
-
- Thiết lập biến môi trường production trong `.env`
- Sử dụng worker queue (supervisor) nếu dùng queue
- Cấu hình scheduler (`crontab`) để chạy `php artisan schedule:run`
- Thiết lập storage symlink: `php artisan storage:link`

Bảo mật & lưu ý
-
- Không commit file `.env` vào git
- Hạn chế quyền truy cập DB/queues cho production

Đóng góp
-
- Mọi thay đổi xin gửi PR vào nhánh `main`. Mô tả rõ thay đổi và kèm hướng dẫn chạy (nếu cần).

Liên hệ
-
- Xem file `Community Event Platform.postman_collection.json` hoặc mở issue/PR để trao đổi API.

License
-
- Kiểm tra file `LICENSE` trong repo để biết chi tiết bản quyền.
