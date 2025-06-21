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
        Schema::create('booking_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status'); // pending, paid, failed
            $table->string('transaction_id')->nullable();
            $table->string('status_id')->nullable();
            $table->string('message')->nullable();
            $table->string('order_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('senang_pay_api_log_id')->nullable()->constrained('senang_pay_api_logs')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_transactions');
    }
};
