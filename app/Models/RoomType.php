<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'season_configuration_id',
        'date_type_id',
        'number_of_adults',
        'number_of_children',
        'base_charge_per_adult',
        'base_charge_per_child',
        'surcharge_charge_per_adult',
        'surcharge_charge_per_child',
        'ext_charge_per_adult',
        'ext_charge_per_child'
    ];

    protected $casts = [
        'number_of_adults' => 'integer',
        'number_of_children' => 'integer',
        'base_charge_per_adult' => 'decimal:2',
        'base_charge_per_child' => 'decimal:2',
        'surcharge_charge_per_adult' => 'decimal:2',
        'surcharge_charge_per_child' => 'decimal:2',
        'ext_charge_per_adult' => 'decimal:2',
        'ext_charge_per_child' => 'decimal:2'
    ];

    public function seasonConfiguration()
    {
        return $this->belongsTo(SeasonConfiguration::class, 'season_configuration_id');
    }

    public function dateType()
    {
        return $this->belongsTo(DateType::class, 'date_type_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_type_id');
    }
}
