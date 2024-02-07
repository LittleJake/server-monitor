<?php

namespace app\http\middleware;

use think\exception\HttpException;
use think\facade\Cache;

class FlowControl
{
    public function handle($request, \Closure $next)
    {
        $ip = $request->ip();
        if(Cache::inc("FlowControl:$ip") > 50)
            throw new HttpException(503, "Trigger Flow Control");
        else
            Cache::set("FlowControl:$ip", 0, 1);
        return $next($request);
    }
}
