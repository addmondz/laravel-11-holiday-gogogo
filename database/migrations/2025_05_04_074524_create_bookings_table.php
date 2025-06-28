<?php

use App\Constants\ApprovalStatus;
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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('uuid')->nullable();
            $table->string('booking_name');
            $table->string('phone_number');
            $table->string('booking_ic');
            $table->string('booking_email');
            $table->integer('status')->default(ApprovalStatus::PENDING_PAYMENT); // ApprovalStatus
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('adults');
            $table->integer('children');
            $table->integer('infants');
            $table->decimal('total_price', 10, 2);
            $table->text('special_remarks')->nullable();

            $table->string('approval_by')->nullable();
            $table->string('approval_date')->nullable();
            $table->string('approval_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
