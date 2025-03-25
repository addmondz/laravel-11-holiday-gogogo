<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'adult_price',
        'child_price',
        'package_id'
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2'
    ];

    public function package()
    {
        return $this->belongsTo(TravelPackage::class, 'package_id');
    }

    public function bookingAddOns()
    {
        return $this->hasMany(BookingAddOn::class, 'add_on_id');
    }
}
