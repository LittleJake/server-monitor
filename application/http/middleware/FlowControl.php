<?php

namespace app\http\middleware;

use think\Exception;
use think\facade\Cache;

class FlowControl
{
    public function handle($request, \Closure $next)
    {
        $ip = $request->ip();
        if(Cache::inc("FlowControl:$ip") > 50)
            throw new Exception("Trigger Flow Control", 403);
        else
            Cache::set("FlowControl:$ip", 0, 1);
        return $next($request);
    }
}
