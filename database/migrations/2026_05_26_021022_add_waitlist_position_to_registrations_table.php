<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CEP-82: Implement waitlist database logic
 * Adds waitlist_position (nullable integer) to the registrations table.
 * A null value means the registration is confirmed (not on the waitlist).
 * A positive integer indicates the position in the waitlist queue (FIFO).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // waitlist_position: null = confirmed, 1 = first in line, 2 = second, etc.
            $table->unsignedInteger('waitlist_position')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('waitlist_position');
        });
    }
};
