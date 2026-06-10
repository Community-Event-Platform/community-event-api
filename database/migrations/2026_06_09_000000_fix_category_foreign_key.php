<?php

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Chuyển từ category (VARCHAR) sang category_id (FK):
     * - Tạo các bản ghi Category từ dữ liệu VARCHAR hiện có
     * - Thêm cột category_id vào bảng events
     * - Đồng bộ dữ liệu cũ
     * - Xóa cột category (VARCHAR) cũ
     */
    public function up(): void
    {
        // Bước 1: Tạo các Category từ dữ liệu VARCHAR hiện có (nếu chưa tồn tại)
        $existingCategories = Event::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category');

        foreach ($existingCategories as $catName) {
            Category::firstOrCreate(['name' => $catName]);
        }

        // Bước 2: Thêm cột category_id (nullable để không gây lỗi với dữ liệu cũ)
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('description')
                ->constrained('categories')
                ->nullOnDelete();
        });

        // Bước 3: Đồng bộ category_id từ giá trị VARCHAR category
        $events = Event::whereNotNull('category')->where('category', '!=', '')->get();
        foreach ($events as $event) {
            $category = Category::where('name', $event->category)->first();
            if ($category) {
                $event->category_id = $category->id;
                $event->save();
            }
        }

        // Bước 4: Xóa cột category (VARCHAR) cũ
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Khôi phục cột category (VARCHAR)
        Schema::table('events', function (Blueprint $table) {
            $table->string('category', 100)->index()->after('description');
        });

        // Khôi phục dữ liệu từ category_id
        $events = Event::whereNotNull('category_id')->get();
        foreach ($events as $event) {
            $category = Category::find($event->category_id);
            if ($category) {
                $event->category = $category->name;
                $event->save();
            }
        }

        // Xóa cột category_id
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};