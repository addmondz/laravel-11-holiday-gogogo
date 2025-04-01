<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageConfiguration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_id',
        'season_id',
        'date_type_id',
        'room_type',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function dateType()
    {
        return $this->belongsTo(DateType::class);
    }

    public function prices()
    {
        return $this->hasMany(ConfigurationPrice::class);
    }
}