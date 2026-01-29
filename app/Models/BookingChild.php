<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChild extends Model
{
    protected $fillable = [
        'booking_room_id',
        'child_number',
        'date_of_birth'
    ];

    protected $casts = [
        'date_of_birth' => 'date'
    ];

    public function bookingRoom(): BelongsTo
    {
        return $this->belongsTo(BookingRoom::class);
    }
}
