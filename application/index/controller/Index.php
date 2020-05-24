<?php
namespace app\index\controller;

use think\facade\Cache;

class Index extends Base
{
    public function index()
    {

        $hash = Cache::handler()->hGetAll("system_monitor:hashes");

        $this->assign("hash", $hash);
        return $this->fetch();
    }

    public function info($token = ''){
        if(strlen($token) != 64){
            return $this->error("Wrong Token");
        }

        $ip = Cache::handler()->hMGet("system_monitor:hashes", [$token])[$token];
        if(empty($ip)){
            return $this->error("Wrong Token");
        }
        $json = json_decode(Cache::handler()->get("system_monitor:stat:".$ip), true);
        $uptime = intval($json['Uptime']);
        $uptime_str = floor($uptime / (24*60*60)) ." Days ".
            floor(($uptime / (60*60)) % 24) ." Hours ".
            floor(($uptime / (60)) % 60) . " Minutes ".
            floor($uptime % 60) . " Seconds";
        $info = Cache::handler()->hGetAll("system_monitor:info:".$ip);
        $this->assign("json", $json);
        $this->assign("uptime", $uptime_str);
        $this->assign("info", $info);
        return $this->fetch();
    }
}
