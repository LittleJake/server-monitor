<?php

namespace app\http\middleware;

use think\exception\HttpException;

class CheckJson
{
    public function handle($request, \Closure $next)
    {
        if(!$request -> isJson())
            throw new HttpException(405, 'Method Not Allowed');

        return $next($request);
    }
}
