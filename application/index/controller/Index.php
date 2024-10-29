<?php
namespace app\index\controller;

use app\common\lib\SystemMonitor;


class Index extends Base
{
    protected $middleware = [
        'FlowControl',
    ];

    public function index()
    {
        $hash = SystemMonitor::getHashes();
        $ip = SystemMonitor::fetchIPInfo(array_values($hash));
        $info = SystemMonitor::getInfo(array_values($hash));
//        $json = SystemMonitor::getStat(array_values($hash));
//
//        $this->assign("json", $json);

        $this->assign("hash", $hash);
        $this->assign("ip", $ip);
        $this->assign("info", $info);
        return $this->fetch();
    }

    public function info($token = ''){
        if(strlen($token) != 64)
            $this->error("Wrong Token");

        $ip = SystemMonitor::getIPByHash($token);

        $json = SystemMonitor::getStat($ip);
        $uptime_str = SystemMonitor::timeFormat(intval($json['Uptime']));
        $info = SystemMonitor::getInfo($ip);
        $this->assign("json", $json);
        $this->assign("uptime", $uptime_str);
        $this->assign("info", $info);
        $this->assign("token", $token);
        if($this->request->isAjax())
            return $this->fetch("index/info_ajax");
        return $this->fetch();
    }
}
