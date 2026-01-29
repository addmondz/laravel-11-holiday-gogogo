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
        Schema::create('booking_children', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_room_id')->constrained()->onDelete('cascade');
            $table->integer('child_number');
            $table->date('date_of_birth');
            $table->timestamps();
            $table->unique(['booking_room_id', 'child_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_children');
    }
};
