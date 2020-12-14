<?php
namespace app\index\controller;

use app\common\lib\SystemMonitor;


class Index extends Base
{
    protected $middleware = ['FlowControl',];

    public function index()
    {
        $hash = SystemMonitor::getHashes();
        $ip = SystemMonitor::fetchIPInfo(array_values($hash));
        $info = SystemMonitor::getInfo(array_values($hash));
        $hide = array_flip(SystemMonitor::getHide());


        asort($hash);
        $this->assign("hash", $hash);
        $this->assign("hide", $hide);
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
        ksort($info);
        $this->assign("json", $json);
        $this->assign("uptime", $uptime_str);
        $this->assign("info", ($info));
        $this->assign("token", $token);
        if($this->request->isAjax())
            return $this->fetch("index/info_ajax");

        return $this->fetch();
    }
}
