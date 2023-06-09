<?php
/**
 * Created by PhpStorm.
 * User: zizili
 * Date: 3/3/2021
 * Time: 2:56 AM
 */

namespace  Core\System\Http\Middleware;


use Closure;

class Cors {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
    }

}