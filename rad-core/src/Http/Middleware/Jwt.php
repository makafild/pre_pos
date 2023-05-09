<?php

namespace  Core\System\Http\Middleware;

use Closure;

class Jwt
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user=auth('api')->user();
        if(empty($user)){
            return response(['hasError' => true, 'message' =>'شناسه توکن نامعتبر است']);
        }
        return $next($request) ;
    }
}
