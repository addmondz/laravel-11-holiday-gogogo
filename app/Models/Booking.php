<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'room_type_id',
        'total_adults',
        'total_children',
        'start_date',
        'end_date',
        'total_price',
        'status'
    ];

    protected $casts = [
        'total_adults' => 'integer',
        'total_children' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(TravelPackage::class, 'package_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function bookingAddOns()
    {
        return $this->hasMany(BookingAddOn::class, 'booking_id');
    }
}
