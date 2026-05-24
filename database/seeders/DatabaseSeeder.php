<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tạo danh mục trước
        $this->call(CategorySeeder::class);

        // 2. Tạo người dùng (Admin/User)
        $this->call(UserSeeder::class);

        // 3. Tạo sự kiện (Lúc này đã có ID Category và ID User để làm khóa ngoại)
        $this->call(EventSeeder::class);

        // 4. Các dữ liệu phụ thuộc khác
        $this->call([
            RegistrationSeeder::class,
            ReviewSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
