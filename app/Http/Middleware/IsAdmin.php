<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
// Import both Response types
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse; // 1. ADD THIS LINE

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    // 2. CHANGE THE RETURN TYPE HERE to allow both Response and RedirectResponse
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // This passes the request to the controller. The controller can return
        // a view (Response) or a redirect (RedirectResponse). This function
        // will now correctly return whichever one it receives.
        return $next($request);
    }
}