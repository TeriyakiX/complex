<?php

namespace App\Jobs\Middleware;

use Illuminate\Support\Str;

class AddUuidToJob
{
    public function handle($job, $next)
    {
        if (empty($job->uuid)) {
            $job->uuid = (string) Str::uuid();
        }

        $next($job);
    }
}
