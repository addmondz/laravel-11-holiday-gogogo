<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DateTypeRange extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'date_type_id',
        'start_date',
        'end_date',
        'status',
        'package_id'
    ];

    // protected $casts = [
    //     'start_date' => 'date',
    //     'end_date' => 'date'
    // ];

    public function dateType(): BelongsTo
    {
        return $this->belongsTo(DateType::class);
    }
}
