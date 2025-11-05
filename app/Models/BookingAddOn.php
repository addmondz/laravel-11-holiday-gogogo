<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAddOn extends Model
{
    protected $fillable = [
        'booking_id',
        'package_add_on_id',
        'adults',
        'children',
        'infants'
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function packageAddOn(): BelongsTo
    {
        return $this->belongsTo(PackageAddOn::class);
    }
}
