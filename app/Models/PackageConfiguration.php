<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_id',
        'season_type_id',
        'date_type_id',
        'room_type_id',
        'configuration_prices',
    ];

    protected $casts = [
        'configuration_prices' => 'array', // or 'json'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function seasonType(): BelongsTo
    {
        return $this->belongsTo(SeasonType::class);
    }

    public function dateType(): BelongsTo
    {
        return $this->belongsTo(DateType::class, 'date_type_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }
}
