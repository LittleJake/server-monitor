<?php

namespace app\http\middleware;

use app\common\lib\SystemMonitor;
use think\Exception;

class CheckToken
{
    public function handle($request, \Closure $next)
    {
        $token = $request->param('token');
        if(strlen($token) != 32)
            throw new Exception("Wrong Token", 403);


        return $next($request);
    }
}
