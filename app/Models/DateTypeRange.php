<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateTypeRange extends Model
{
    use SoftDeletes;

    protected $fillable = ['date_type_id', 'start_date', 'end_date'];

    public function dateType()
    {
        return $this->belongsTo(DateType::class);
    }
}
