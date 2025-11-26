<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class CheckPerms
{
    public function handle($request, Closure $next)
    {
        $user=$request->user();
        if ($user->hasRole('super_admin')){
            return $next($request);
        } elseif ((str_contains($request->path(),"circuits")) and (str_contains($request->path(),"edit"))){
            if ($user->circuits){
                foreach ($user->circuits as $circ){
                    if (str_contains($request->path(),$circ)){
                        return $next($request);
                    }
                }
                abort(Response::HTTP_UNAUTHORIZED);    
            } else {
                abort(Response::HTTP_UNAUTHORIZED);    
            }
        } elseif ((str_contains($request->path(),"societies")) and (str_contains($request->path(),"edit"))){
            if ($user->societies){
                foreach ($user->societies as $soc){
                    if (str_contains($request->path(),$soc)){
                        return $next($request);
                    }
                }
                abort(Response::HTTP_UNAUTHORIZED);    
            } else {
                abort(Response::HTTP_UNAUTHORIZED);    
            }
        } else {
            return $next($request);
        }
    }
}