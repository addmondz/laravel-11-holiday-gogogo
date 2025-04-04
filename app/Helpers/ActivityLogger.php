<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log($action, $subject = null, $description = null)
    {
        $user = auth()->user();
        ActivityLog::create([
            'user_email' => $user?->email ?? 'guest',
            'action' => $action,
            'subject' => $subject,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}
