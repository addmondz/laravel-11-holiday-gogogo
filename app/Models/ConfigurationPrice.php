<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfigurationPrice extends Model
{
    protected $fillable = [
        'package_configuration_id',
        'type',
        'number_of_adults',
        'number_of_children',
        'adult_price',
        'child_price',
    ];

    protected $casts = [
        'adult_price' => 'decimal:2',
        'child_price' => 'decimal:2',
        'number_of_adults' => 'integer',
        'number_of_children' => 'integer',
    ];

    /**
     * Get the package configuration that owns the price.
     */
    public function packageConfiguration(): BelongsTo
    {
        return $this->belongsTo(PackageConfiguration::class);
    }
} 