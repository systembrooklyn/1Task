<?php

namespace App\Modules\Plan\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            $token = $request->header('X-Admin-Token') ?? $request->query('admin_token');
        }

        $expectedToken = config('app.admin_api_token') ?? env('ADMIN_API_TOKEN');

        if (!$token || $token !== $expectedToken) {
            return response()->json([
                'message' => 'Unauthorized. Invalid admin token.'
            ], 401);
        }

        return $next($request);
    }
}
