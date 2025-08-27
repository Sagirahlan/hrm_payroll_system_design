<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string  $permission
	 * @return mixed
	 */
	
    public function handle(Request $request, Closure $next, $permission)
    {
		if (!Auth::check()) {
            return redirect()->route('login');
        }
        if (!Auth::user()->hasPermissionTo($permission)) {
            abort(403, 'Unauthorized action.');
        }
        return $next($request);
    }
}


