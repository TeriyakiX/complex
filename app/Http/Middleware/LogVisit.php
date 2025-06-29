<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Visit;
use Illuminate\Http\Request;

class LogVisit
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        // Проверяем, был ли уже такой IP
        $alreadyVisited = Visit::where('ip_address', $ip)->exists();

        if (! $alreadyVisited) {
            Visit::create([
                'ip_address'  => $ip,
                'user_agent'  => $request->header('User-Agent'),
            ]);
        }

        return $next($request);
    }
}
