<?php

namespace app\http\middleware;

use app\common\lib\SystemMonitor;
use think\exception\HttpException;
use think\facade\Log;

class CheckUUID
{
    public function handle($request, \Closure $next)
    {
        $uuid = $request->route('uuid') ?: $request->param('uuid');
        if (strlen($uuid) != 32 || empty(SystemMonitor::getUUIDs()[$uuid])) {
            Log::warning('Security event: invalid UUID rejected; ip=' . $request->ip());
            throw new HttpException(403, 'Wrong UUID');
        }

        return $next($request);
    }
}
