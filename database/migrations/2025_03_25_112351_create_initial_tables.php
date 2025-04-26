<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Package table
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon_photo')->nullable();
            $table->decimal('display_price_adult', 10, 2)->nullable();
            $table->decimal('display_price_child', 10, 2)->nullable();
            $table->integer('package_min_days');
            $table->integer('package_max_days');
            $table->text('terms_and_conditions')->nullable();
            $table->string('location')->nullable();
            $table->date('package_start_date');
            $table->date('package_end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add-ons
        Schema::create('package_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('adult_price', 10, 2)->nullable();
            $table->decimal('child_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Season Types (no dates)
        Schema::create('season_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Early Bird, Peak Season
            $table->timestamps();
            $table->softDeletes();
        });

        // Seasons (date ranges for a season type)
        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('priority')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Date Types (like Weekend, Weekday)
        Schema::create('date_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Weekend, Roomsur 60
            $table->timestamps();
            $table->softDeletes();
        });

        // Date Type Ranges
        Schema::create('date_type_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('date_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->integer('max_occupancy')->default(2);
            $table->timestamps();
            $table->softDeletes();
        });

        // Configurations
        Schema::create('package_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->foreignId('date_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

        // Configuration Prices
        Schema::create('configuration_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_configuration_id')->constrained('package_configurations')->onDelete('cascade');
            $table->enum('type', ['base_charge', 'sur_charge', 'ext_charge']);
            $table->integer('number_of_adults')->default(1);
            $table->integer('number_of_children')->default(0);
            $table->decimal('adult_price', 10, 2)->nullable();
            $table->decimal('child_price', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuration_prices');
        Schema::dropIfExists('package_configurations');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('date_type_ranges');
        Schema::dropIfExists('date_types');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('season_types');
        Schema::dropIfExists('package_add_ons');
        Schema::dropIfExists('packages');
    }
};
