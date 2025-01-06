<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;

//TODO: Input sanitization.
class Report extends Controller
{
    protected $middleware = ['FlowControl', 'CheckToken', 'CheckUUID' => ['except' => ['hash']]];


    public function collection($uuid = '')
    {
        $json = $this-> request -> post();
        return json(SystemMonitor::setCollection($uuid, json_encode($json)));
    }

    public function info($uuid = '')
    {
        $json = $this-> request -> post();
        return json(SystemMonitor::setInfo($uuid, $json));
    }

    public function hash($uuid = '')
    {
        $ip = $this-> request->post('ip');
        return json(SystemMonitor::setUUID($uuid, $ip));
    }

    public function command($uuid = '')
    {
        return SystemMonitor::getCommand($uuid);
    }
}
