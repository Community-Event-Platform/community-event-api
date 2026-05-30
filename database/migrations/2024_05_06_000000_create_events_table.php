<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organizer_id')->constrained('users')->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('location', 255);
            $table->dateTime('date_time'); // Đổi từ event_date
            $table->integer('capacity')->unsigned();
            $table->string('event_type', 50)->default('Free'); // Mới
            $table->decimal('price', 10, 2)->nullable(); // Ticket price for Paid events
            $table->string('status', 50)->default('Draft'); // Đổi từ ENUM
            $table->boolean('require_additional_info')->default(false); // Mới
            $table->text('custom_form_spec')->nullable(); // Mới
            $table->softDeletes(); // Thêm deleted_at
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};