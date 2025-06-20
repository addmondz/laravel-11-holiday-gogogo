<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'booking_transactions';
    
    protected $fillable = [
        'booking_id',
        'payment_method',
        'amount',
        'status',
        'transaction_id',
        'status_id',
        'message',
        'order_id',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime'
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
