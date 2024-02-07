<?php

namespace app\http\middleware;

use think\exception\HttpException;
use think\facade\Cache;

class CheckToken
{
    public function handle($request, \Closure $next)
    {
        $uuid = $request->param('uuid');
        $auth = $request->header('authorization');
        $node_token = Cache::store('token')->has("node_token")?json_decode(Cache::store("token")->get("node_token"), true):[];
        if($node_token[$uuid] != $auth)
            throw new HttpException(403, "Authorization Failed.");

        return $next($request);
    }
}
