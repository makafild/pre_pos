<?php

namespace App\Http\Middleware;

use Closure;
use http\Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Tymon\JWTAuth\Exceptions\JWTException
     */
    public function handle($request, Closure $next)
    {
        if (isset(auth('api')->user()->id)) {
            $userLog = DB::table('log_login')->where('user_id', auth('api')->user()->id)->first();
            if (!empty($userLog)) {
                $now = Carbon::now()->format('Y-m-d H:i:s');
                $minutes = 30;
                $lastLogTime = Carbon::createFromTimestamp(strtotime('+' . $minutes . ' minutes', strtotime($userLog->created_at)))->format('Y-m-d H:i:s');
                if ($lastLogTime < $now) {
                    DB::table('log_login')
                        ->where('user_id', auth('api')->user()->id)
                        ->update(['created_at' => now()]);
                }
            } else {
                DB::table('log_login')->insert(['user_id' => auth('api')->user()->id, 'created_at' => now()]);
            }
        }


        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch
        (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid']);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['status' => 'Token is Expired']);
            } else {
                return response()->json(['status' => 'Authorization Token not found']);
            }
        }
        return $next($request);
    }
}
