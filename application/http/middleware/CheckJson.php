<?php

namespace app\http\middleware;

use think\Exception;

class CheckJson
{
    public function handle($request, \Closure $next)
    {
        if(!$request -> isJson())
            throw new Exception("Not Allow", 405);

        return $next($request);
    }
}
