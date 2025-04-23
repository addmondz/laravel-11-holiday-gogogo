<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    use SoftDeletes;

    protected $fillable = ['season_type_id', 'start_date', 'end_date', 'priority'];

    // protected $casts = [
    //     'start_date' => 'date',
    //     'end_date' => 'date'
    // ];

    public function type()
    {
        return $this->belongsTo(SeasonType::class, 'season_type_id');
    }

    public function configurations()
    {
        return $this->hasMany(PackageConfiguration::class);
    }
}
