<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;

class EnsureTokenIsEmployee
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() instanceof Employee) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
