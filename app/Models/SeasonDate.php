<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeasonDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_configuration_id',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function seasonConfiguration()
    {
        return $this->belongsTo(SeasonConfiguration::class, 'season_configuration_id');
    }
}
