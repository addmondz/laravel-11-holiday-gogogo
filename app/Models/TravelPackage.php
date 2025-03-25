<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TravelPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'icon_photo',
        'display_price_adult',
        'display_price_child',
        'package_days',
        'package_min_days',
        'package_max_days',
        'tnc',
        'package_start_date',
        'package_end_date',
        'is_active'
    ];

    protected $casts = [
        'display_price_adult' => 'decimal:2',
        'display_price_child' => 'decimal:2',
        'package_days' => 'integer',
        'package_min_days' => 'integer',
        'package_max_days' => 'integer',
        'package_start_date' => 'date',
        'package_end_date' => 'date',
        'is_active' => 'boolean'
    ];

    public function addOns()
    {
        return $this->hasMany(AddOn::class, 'package_id');
    }

    public function images()
    {
        return $this->hasMany(PackageImage::class, 'package_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_id');
    }
}
