<?php

namespace App\Http\Middleware;

use Closure;

class IncreaseUploadLimits
{
    public function handle($request, Closure $next)
    {
        // Set higher limits
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '110M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        return $next($request);
    }
}
