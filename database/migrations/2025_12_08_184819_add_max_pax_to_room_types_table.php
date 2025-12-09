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
        Schema::table('room_types', function (Blueprint $table) {
            $table->integer('max_adults')->nullable()->after('max_occupancy');
            $table->integer('max_children')->nullable()->after('max_adults');
            $table->integer('max_infants')->nullable()->after('max_children');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn(['max_adults', 'max_children', 'max_infants']);
        });
    }
};
