<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsArtisan
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || $user->role_id !== 1) {
            return response()->json([
                'status' => 403,
                'message' => 'Access denied. Only artisans are allowed.'
            ], 403);
        }

        return $next($request);
    }
}
