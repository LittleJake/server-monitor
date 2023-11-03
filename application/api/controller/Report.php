<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;

class Report extends Controller
{
    protected $middleware = ['FlowControl', 'CheckToken', 'CheckUUID', 'CheckJson'];


    public function collection($uuid = '')
    {
        $json = $this-> request -> input();
        return json(SystemMonitor::setCollection($uuid, $json));
    }

    public function info($uuid = '')
    {
        $json = $this-> request -> input();
        return json(SystemMonitor::setInfo($uuid, $json));
    }

    public function hash($uuid = '')
    {
        $ip = $this-> request->post('ip');
        return json(SystemMonitor::setUUID($uuid, $ip));
    }
}
