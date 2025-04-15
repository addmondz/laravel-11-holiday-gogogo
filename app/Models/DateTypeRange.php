<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DateTypeRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_type_id',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function dateType(): BelongsTo
    {
        return $this->belongsTo(DateType::class);
    }
}
