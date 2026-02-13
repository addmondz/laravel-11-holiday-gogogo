<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedInteger('sequence')->default(0)->after('package_id');
        });

        // Backfill existing room types with sequential values per package
        $packages = DB::table('room_types')
            ->select('package_id')
            ->distinct()
            ->pluck('package_id');

        foreach ($packages as $packageId) {
            $roomTypes = DB::table('room_types')
                ->where('package_id', $packageId)
                ->orderBy('id', 'asc')
                ->pluck('id');

            foreach ($roomTypes as $index => $id) {
                DB::table('room_types')
                    ->where('id', $id)
                    ->update(['sequence' => $index]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('sequence');
        });
    }
};
