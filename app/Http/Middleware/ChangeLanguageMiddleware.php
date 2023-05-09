<?php

namespace App\Http\Middleware;

use Closure;

class ChangeLanguageMiddleware
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
		\App::setLocale($request->headers->get('accept-language') ?: 'fa');

		return $next($request);
    }
}
