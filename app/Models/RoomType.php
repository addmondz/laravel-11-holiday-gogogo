<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'bed_desc',
        'package_id',
        'max_occupancy',
        'max_adults',
        'max_children',
        'max_infants',
        'images'
    ];

    protected $casts = [
        'images' => 'array'
    ];

    public function configurations(): HasMany
    {
        return $this->hasMany(PackageConfiguration::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function dateBlockers(): HasMany
    {
        return $this->hasMany(DateBlocker::class);
    }
}
