<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority'
    ];

    protected $casts = [
        'priority' => 'integer'
    ];

    public function seasonDates()
    {
        return $this->hasMany(SeasonDate::class, 'season_configuration_id');
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class, 'season_configuration_id');
    }
}
