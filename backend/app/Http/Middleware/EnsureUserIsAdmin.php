<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator access required',
                'errors' => [],
            ], 403);
        }

        return $next($request);
    }
}