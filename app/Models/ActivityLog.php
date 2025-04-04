<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_email',
        'action',
        'subject',
        'description',
        'request_body',
        'ip_address',
        'user_agent',
    ];
}
