<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('booking_children', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable()->after('child_number');
        });

        // Migrate existing data
        DB::statement('UPDATE booking_children SET birth_year = YEAR(date_of_birth) WHERE date_of_birth IS NOT NULL');

        Schema::table('booking_children', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_children', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('child_number');
        });

        // Convert birth_year back to date_of_birth (using January 1st as default)
        DB::statement('UPDATE booking_children SET date_of_birth = CONCAT(birth_year, "-01-01") WHERE birth_year IS NOT NULL');

        Schema::table('booking_children', function (Blueprint $table) {
            $table->dropColumn('birth_year');
        });
    }
};
