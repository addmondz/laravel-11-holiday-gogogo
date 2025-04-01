<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigurationPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_configuration_id',
        'type',
        'number_of_adults',
        'number_of_children',
        'adult_price',
        'child_price',
    ];

    public function configuration()
    {
        return $this->belongsTo(PackageConfiguration::class, 'package_configuration_id');
    }
}
