<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;
use think\Exception;

class Cpu extends Controller
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

        $cpu = SystemMonitor::getCpuCollection($ip);
        return json(SystemMonitor::collectionFormat($cpu));
    }
}
