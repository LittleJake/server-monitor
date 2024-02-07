<?php

namespace app\http\middleware;

use app\common\lib\SystemMonitor;
use think\exception\HttpException;

class CheckUUID
{
    public function handle($request, \Closure $next)
    {
        $uuid = $request->param('uuid');
        if(strlen($uuid) != 32 || empty(SystemMonitor::getUUIDs()[$uuid]))
            throw new HttpException(403, "Wrong UUID");

        return $next($request);
    }
}
