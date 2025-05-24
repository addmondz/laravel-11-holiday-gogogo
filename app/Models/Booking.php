<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'package_id',
        'room_type_id',
        'booking_name',
        'phone_number',
        'booking_ic',
        'start_date',
        'end_date',
        'adults',
        'children',
        'total_price',
        'special_remarks',
        'status',
        'payment_status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2'
    ];

    public function package()
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
}
