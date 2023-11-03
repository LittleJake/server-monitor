<?php

namespace app\http\middleware;

use app\common\lib\SystemMonitor;
use think\Exception;

class CheckUUID
{
    public function handle($request, \Closure $next)
    {
        $uuid = $request->param('uuid');
        if(strlen($uuid) != 32 || empty(SystemMonitor::getUUIDs()[$uuid]))
            throw new Exception("Wrong UUID", 403);

        return $next($request);
    }
}
