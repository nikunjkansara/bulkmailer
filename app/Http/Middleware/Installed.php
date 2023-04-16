<?php

namespace horsefly\Http\Middleware;

use Closure;

class Installed
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (isInitiated()) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
