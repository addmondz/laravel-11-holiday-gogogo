<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_id',
        'image_url',
        'caption'
    ];

    public function package()
    {
        return $this->belongsTo(TravelPackage::class, 'package_id');
    }
}
