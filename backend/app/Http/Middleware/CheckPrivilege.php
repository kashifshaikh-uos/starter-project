<?php

namespace App\Http\Middleware;

use App\Models\Privilege;
use Closure;
use Illuminate\Http\Request;

class CheckPrivilege
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $method = $request->method();
        $path = '/' . ltrim($request->path(), '/');

        // Normalize: replace numeric segments with {id}
        $normalized = preg_replace('#/\d+#', '/{id}', $path);

        // Find matching privilege by api_route and method
        $privilege = Privilege::where('api_route', $normalized)
            ->where('method', $method)
            ->where('is_active', true)
            ->first();

        // If no privilege is registered for this route, allow access (unregistered = open)
        if (!$privilege) {
            return $next($request);
        }

        // Check if user has this privilege
        if (!$user->hasPrivilege($privilege->slug)) {
            return response()->json(['message' => 'You do not have permission to access this resource'], 403);
        }

        return $next($request);
    }
}
