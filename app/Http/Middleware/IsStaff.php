<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsStaff
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // hanya login user dengan role 'staff' atau 'admin'
        if (!$user || !in_array($user->role, ['staff','admin'])) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
