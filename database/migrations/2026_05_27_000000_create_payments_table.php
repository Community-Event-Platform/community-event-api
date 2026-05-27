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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('registration_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method', 50); // vnpay, paypal, stripe, credit_card
            $table->string('transaction_id')->nullable(); // External transaction ID
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('VND');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('payment_url')->nullable(); // URL to redirect for payment
            $table->json('payment_data')->nullable(); // Raw response from gateway
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index('transaction_id');
        });

        // Add payment_id to registrations table for tracking
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('additional_info')->constrained('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
        
        Schema::dropIfExists('payments');
    }
};
