<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;
use think\Exception;
use think\Response;

class Network extends Controller
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
        return json([
            'RX' => SystemMonitor::networkFormat(SystemMonitor::getCollection($token, 'network:RX')),
            'TX' => SystemMonitor::networkFormat(SystemMonitor::getCollection($token, 'network:TX')),
        ]);
    }
}
