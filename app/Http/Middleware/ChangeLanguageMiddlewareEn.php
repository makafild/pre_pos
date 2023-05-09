<?php

namespace App\Http\Middleware;

use Closure;

class ChangeLanguageMiddlewareEn
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
		\App::setLocale('en');

		return $next($request);
    }
}