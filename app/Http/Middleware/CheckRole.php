<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next , string $role): Response
    {
        //$request->user()->role->name !== $role if the array compare not work "in_array"

        $allowedRoles = explode('|' , $role);
        if (!$request->user() ||  !in_array($request->user()->role->name, $allowedRoles)) {
            return response()->json(['message'=>'forbidden action!'] , 403);
        }

        return $next($request);
    }
}
