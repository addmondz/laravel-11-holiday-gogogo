<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SenangPayApiLog extends Model
{
    protected $table = 'senang_pay_api_logs';
    protected $fillable = ['log_type', 'status_id', 'order_id', 'transaction_id', 'msg', 'hash', 'raw_payload', 'is_processed'];
    
    protected $casts = [
        'raw_payload' => 'array',
        'is_processed' => 'boolean'
    ];
}
