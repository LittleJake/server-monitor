<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;
use think\Exception;
use think\Response;

class Memory extends Controller
{
    protected $middleware = ['FlowControl', 'CheckToken'];

    /**
     * 显示资源列表
     *
     * @param string $token
     * @return Response
     * @throws Exception
     */
    public function get($token = '')
    {
        $ip = SystemMonitor::getIPByHash($token);

        return json([
            'swap' => SystemMonitor::collectionFormat(SystemMonitor::getSwapCollection($ip)),
            'memory' => SystemMonitor::collectionFormat(SystemMonitor::getMemoryCollection($ip))
        ]);
    }
}