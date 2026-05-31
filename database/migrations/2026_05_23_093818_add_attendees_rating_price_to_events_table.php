<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->unsignedInteger('attendees')->default(0)->after('event_type');
        $table->decimal('rating', 3, 1)->default(4.5)->after('attendees');

        // XÓA DÒNG NÀY
        // $table->unsignedInteger('price')->nullable()->after('rating');
    });
}

public function down(): void
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn(['attendees', 'rating']);
    });
}
};