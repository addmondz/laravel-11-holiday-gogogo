<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'icon_photo',
        'display_price_adult',
        'display_price_child',
        'package_min_days',
        'package_max_days',
        'terms_and_conditions',
        'location',
        'package_start_date',
        'package_end_date',
        'is_active',
    ];

    public function addOns()
    {
        return $this->hasMany(PackageAddOn::class);
    }

    public function configurations()
    {
        return $this->hasMany(PackageConfiguration::class);
    }
}
