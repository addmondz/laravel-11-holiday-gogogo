<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'package_id',
        'booking_name',
        'phone_number',
        'booking_ic',
        'booking_email',
        'start_date',
        'end_date',
        'adults',
        'children',
        'infants',
        'total_price',
        'special_remarks',
        'status',
        'uuid',
        
        'approval_by',
        'approval_date',
        'approval_status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
        'approval_date' => 'datetime',
        'approval_by' => 'integer'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(BookingRoom::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_by');
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(BookingAddOn::class);
    }
}
