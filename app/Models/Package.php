<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
        'uuid'
    ];

    public function addOns(): HasMany
    {
        return $this->hasMany(PackageAddOn::class);
    }

    public function configurations(): HasMany
    {
        return $this->hasMany(PackageConfiguration::class);
    }

    public function loadRoomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class);
    }

    public function roomTypes(): BelongsToMany
    {
        return $this->belongsToMany(RoomType::class, 'package_configurations', 'package_id', 'room_type_id')
            ->withPivot(['season_id', 'date_type_id'])
            ->withTimestamps();
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function dateTypeRanges(): HasMany
    {
        return $this->hasMany(DateTypeRange::class);
    }
}
