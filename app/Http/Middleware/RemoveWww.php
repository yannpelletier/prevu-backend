<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

class RemoveWww
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
        if (substr($request->header('host'), 0, 4) === 'www.' && App::environment() === 'prod') {
            $request->headers->set('host', config('app.domain'));
            return Redirect::to($request->path());
        }

        return $next($request);
    }
}
