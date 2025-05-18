<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Season extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'season_type_id',
        'start_date',
        'end_date',
        'package_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function type()
    {
        return $this->belongsTo(SeasonType::class, 'season_type_id');
    }

    public function configurations()
    {
        return $this->hasMany(PackageConfiguration::class);
    }

    // Add a scope to check for overlapping dates
    public function scopeOverlapping(Builder $query, $startDate, $endDate, $packageId)
    {
        return $query->where('package_id', $packageId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });
    }

    // Add a method to check if dates overlap
    public static function hasOverlappingDates($startDate, $endDate, $packageId, $excludeId = null)
    {
        $query = static::overlapping($startDate, $endDate, $packageId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
