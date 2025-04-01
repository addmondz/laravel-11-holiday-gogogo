<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageAddOn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'package_id',
        'name',
        'description',
        'adult_price',
        'child_price',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
