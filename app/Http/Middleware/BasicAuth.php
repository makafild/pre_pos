<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\DB;

use Closure;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {



        $AUTH_USER = config('basicAuth.sabz.username');
        $AUTH_PASS =  config('basicAuth.sabz.password');
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW']   != $AUTH_PASS
        );
        if ($is_not_authenticated) {
           return response('دسترسی غیر مجاز!', 401);
        }
        return $next($request);
    }
}
