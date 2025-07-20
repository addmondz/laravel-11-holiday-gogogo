<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_photo',
        'display_price_adult',
        'display_price_child',
        'display_price_infant',
        'package_min_days',
        'package_max_days',
        'terms_and_conditions',
        'location',
        'package_start_date',
        'package_end_date',
        'uuid',
        'images',
        'child_max_age_desc',
        'infant_max_age_desc',
        'weekend_days',
    ];

    protected $casts = [
        'images' => 'array',
        'weekend_days' => 'array'
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
            ->withPivot(['season_type_id', 'date_type_id'])
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

    public function dateBlockers(): HasMany
    {
        return $this->hasMany(DateBlocker::class);
    }

    /**
     * Get unique season_type_id values from configurations.
     */
    public function uniqueSeasonTypes(): Collection
    {
        $season_type_id = Season::where('package_id', $this->id)->get()->pluck('season_type_id')->unique();

        return SeasonType::whereIn('id', $season_type_id)->get();
    }

    /**
     * Get unique date_type_id values from configurations.
     */
    public function uniqueDateTypes(): Collection
    {
        $date_type_id = DateTypeRange::where('package_id', $this->id)->get()->pluck('date_type_id')->unique();

        return DateType::whereIn('id', $date_type_id)->get();
    }

    public function getUniqueSeasonTypesAttribute()
    {
        return $this->roomTypes->pluck('seasonType')->unique();
    }

    public function getUniqueDateTypesAttribute()
    {
        return $this->roomTypes->pluck('dateType')->filter()->unique('id')->values();
    }
}
