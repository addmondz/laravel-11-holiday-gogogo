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
        Schema::create('senang_pay_api_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_type')->nullable();
            $table->string('status_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('msg')->nullable();
            $table->string('hash')->nullable();
            $table->json('raw_payload')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('senang_pay_api_logs');
    }
};
