<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // hanya login user dengan role 'admin'
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
