<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomAuthSanctum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!auth('sanctum')->check()){
            if($request->wantsJson()){
                return response()->json(['success'=>false ,'message'=>'unauthenticated!'] , 401);
            }
        }

        return $next($request);
    }
}
