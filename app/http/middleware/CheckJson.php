<?php

namespace app\http\middleware;

use think\exception\HttpException;
use think\facade\Log;

class CheckJson
{
    public function handle($request, \Closure $next)
    {
        if (!$request->isGet() && !$request->isJson()) {
            Log::warning('Security event: Method Not Allowed; ip=' . $request->ip() . '; method=' . $request->method());
            throw new HttpException(405, 'Method Not Allowed');
        }

        return $next($request);
    }
}
