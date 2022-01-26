<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateWithCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!$request->headers->has('Authorization') && $request->hasCookie('token')){
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('token'));
        }
        return $next($request);
    }
}
