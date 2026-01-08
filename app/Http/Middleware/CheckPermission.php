<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Retrieve permission from the authenticated user's JWT payload 
        $permissions =  auth('api')->payload()->get('permission');


        // Check if the given permission exists in the user's permission
        $hasPermission =  collect($permissions)->contains('name', $permission);


        if (!$hasPermission) {
            return response()->json([
                'error' => "Unauthorized"
            ], 403);
        }
        return $next($request);
    }
}
