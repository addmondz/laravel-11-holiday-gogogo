<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'payment_details'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
