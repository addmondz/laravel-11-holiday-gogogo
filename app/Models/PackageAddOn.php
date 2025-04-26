<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageAddOn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'package_id',
        'name',
        'description',
        'adult_price',
        'child_price'
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
