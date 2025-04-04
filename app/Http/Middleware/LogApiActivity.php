<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogApiActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = Auth::user();
        $email = $user?->email ?? 'guest';
        $routeAction = $request->route()?->getActionMethod();
        $method = $request->method();
        $path = $request->path();

        ActivityLog::create([
            'user_email' => $email,
            'action' => strtolower($method),
            'subject' => $routeAction,
            'description' => "Accessed {$method} {$path}",
            'request_body' => $path == 'login' ? null : json_encode($request->all()),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
        ]);

        return $response;
    }
}
