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
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon_photo')->nullable();
            $table->decimal('display_price_adult', 10, 2);
            $table->decimal('display_price_child', 10, 2);
            $table->integer('package_days');
            $table->integer('package_min_days');
            $table->integer('package_max_days');
            $table->text('tnc')->nullable();
            $table->date('package_start_date');
            $table->date('package_end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('adult_price', 10, 2);
            $table->decimal('child_price', 10, 2);
            $table->foreignId('package_id')->constrained('travel_packages')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('season_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('priority')->default(0);
            $table->timestamps();
        });

        Schema::create('season_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_configuration_id')->constrained('season_configurations')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::create('date_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('season_configuration_id')->constrained('season_configurations')->onDelete('cascade');
            $table->foreignId('date_type_id')->constrained('date_types')->onDelete('cascade');
            $table->integer('number_of_adults');
            $table->integer('number_of_children');
            $table->decimal('base_charge_per_adult', 10, 2);
            $table->decimal('base_charge_per_child', 10, 2);
            $table->decimal('surcharge_charge_per_adult', 10, 2);
            $table->decimal('surcharge_charge_per_child', 10, 2);
            $table->decimal('ext_charge_per_adult', 10, 2);
            $table->decimal('ext_charge_per_child', 10, 2);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained('travel_packages')->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained('room_types')->onDelete('cascade');
            $table->integer('total_adults');
            $table->integer('total_children');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        Schema::create('booking_add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('add_on_id')->constrained('add_ons')->onDelete('cascade');
            $table->integer('quantity_adult');
            $table->integer('quantity_child');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('package_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('travel_packages')->onDelete('cascade');
            $table->string('image_url');
            $table->string('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_images');
        Schema::dropIfExists('booking_add_ons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('date_types');
        Schema::dropIfExists('season_dates');
        Schema::dropIfExists('season_configurations');
        Schema::dropIfExists('add_ons');
        Schema::dropIfExists('travel_packages');
    }
};
