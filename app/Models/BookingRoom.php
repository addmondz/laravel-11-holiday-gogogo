<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRoom extends Model
{
    protected $fillable = [
        'booking_id',
        'room_type_id',
        'adults',
        'children',
        'infants'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(BookingChild::class);
    }
}
