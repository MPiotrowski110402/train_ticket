<?php

namespace App\Http\Middleware;

use App\Services\TripSchedulerService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTrainPoolIsFresh
{
    public function handle(Request $request, Closure $next): Response
    {
        // Cache "throttle" 30s, żeby nie odpalać ciężkiego sprawdzenia na KAŻDY request
        if (! cache()->has('trip_pool_checked')) {
            app(TripSchedulerService::class)->ensurePoolIsFull();
            cache()->put('trip_pool_checked', true, now()->addSeconds(30));
        }

        return $next($request);
    }
}