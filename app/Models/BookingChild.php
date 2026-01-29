<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingChild extends Model
{
    protected $fillable = [
        'booking_room_id',
        'child_number',
        'birth_year'
    ];

    protected $casts = [
        'birth_year' => 'integer'
    ];

    public function bookingRoom(): BelongsTo
    {
        return $this->belongsTo(BookingRoom::class);
    }
}
