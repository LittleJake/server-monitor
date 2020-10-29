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
    public function RX($token = '')
    {
        $ip = SystemMonitor::getIPByHash($token);

        $network = SystemMonitor::getNetworkRXCollection($ip);
        return json(SystemMonitor::networkFormat($network));
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function TX($token = '')
    {
        $ip = SystemMonitor::getIPByHash($token);

        $network = SystemMonitor::getNetworkTXCollection($ip);
        return json(SystemMonitor::networkFormat($network));
    }
}
