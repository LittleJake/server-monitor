<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;
use think\Exception;
use think\Request;

class Network extends Controller
{
    protected $middleware = ['FlowControl', 'CheckToken'];

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function get($token = '')
    {
        $ip = SystemMonitor::getIPByHash($token);
        return json([
            'RX' => SystemMonitor::networkFormat(SystemMonitor::getNetworkRXCollection($ip)),
            'TX' => SystemMonitor::networkFormat(SystemMonitor::getNetworkTXCollection($ip))
        ]);
    }
}
