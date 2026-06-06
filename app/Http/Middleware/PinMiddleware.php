<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PinMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = $request->session()->get('pin_confirmed_at', 0);

        // PIN is valid for 3 hours (10800 seconds)
        if (time() - $confirmedAt > 10800) {
            $request->session()->put('url.intended', $request->url());
            return redirect()->route('portal.cms.pin');
        }

        return $next($request);
    }
}
