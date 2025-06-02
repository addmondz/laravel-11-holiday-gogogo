<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class DateBlocker extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_id',
        'start_date',
        'end_date',
        'room_type_id'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    // Add a scope to check for overlapping dates
    public function scopeOverlapping(Builder $query, $startDate, $endDate, $packageId, $roomTypeId = null)
    {
        return $query->where('package_id', $packageId)
            ->when($roomTypeId, function ($query) use ($roomTypeId) {
                $query->where('room_type_id', $roomTypeId);
            })
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    // Add a method to check if dates overlap
    public static function hasOverlappingDates($startDate, $endDate, $packageId, $excludeId = null, $roomTypeId = null)
    {
        $query = static::overlapping($startDate, $endDate, $packageId, $roomTypeId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
} 