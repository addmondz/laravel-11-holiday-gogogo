<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConfigurationPrice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_configuration_id',
        'type',
        'number_of_adults',
        'number_of_children',
        'adult_price',
        'child_price',
    ];

    public function configuration(): BelongsTo
    {
        return $this->belongsTo(PackageConfiguration::class, 'package_configuration_id');
    }
}
