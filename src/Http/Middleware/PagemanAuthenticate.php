<?php

namespace Tsrgtm\Pageman\Http\Middleware; // Replace YourVendorName\Pageman

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagemanAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // User is authenticated with the specified guard.
                // Now, check if they can access the Pageman admin panel.
                $user = Auth::guard($guard)->user();
                if ($user && method_exists($user, 'canAccessPagemanAdmin') && $user->canAccessPagemanAdmin()) {
                    return $next($request);
                }
                // If user is authenticated but not authorized for Pageman admin,
                // you might want to redirect them to a different page or show a 403 error.
                // For now, we'll let them fall through to the Pageman login,
                // or you can abort(403, 'Unauthorized access to Pageman admin.');
            }
        }

        // If none of the guards authenticated the user, or they are not authorized,
        // redirect to Pageman's custom login route.
        return redirect()->route(config('pageman.auth.login_route', 'pageman.auth.login'));
    }
}
