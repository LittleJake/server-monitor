<?php

namespace app\http\middleware;

use think\exception\HttpException;
use think\facade\Cache;

class FlowControl
{
    public function handle($request, \Closure $next)
    {
        $ip = $request->ip();
        $key = "FlowControl:$ip";
        if (!Cache::has($key))
            Cache::set($key, 0, 1);

        if(Cache::inc($key) > 50)
            throw new HttpException(503, "Trigger Flow Control");

        return $next($request);
    }
}
