<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateType extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function ranges()
    {
        return $this->hasMany(DateTypeRange::class);
    }

    public function configurations()
    {
        return $this->hasMany(PackageConfiguration::class);
    }
}
