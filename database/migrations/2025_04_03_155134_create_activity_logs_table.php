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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_email')->nullable(); // User email or system
            $table->string('action'); // e.g., login, create, update
            $table->string('subject')->nullable(); // e.g., 'User', 'Claim', etc.
            $table->text('description')->nullable(); // Optional details
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_body')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
