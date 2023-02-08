<?php
namespace app\index\controller;

use app\common\lib\SystemMonitor;


class Index extends Base
{
    protected $middleware = [
        'FlowControl',
        'CheckToken' => ['only'=> ['info']]
    ];

    public function index()
    {
        $hashes = SystemMonitor::getHashes();
        $hide = array_flip(SystemMonitor::getHide());
        $hashes_online = [];
        $hashes_offline = [];
        $time = time();
        
        foreach ($hashes as $hash => $ip) {
            $hash_temp = [
                'IP' => SystemMonitor::fetchIPInfo($ip),
                'INFO' => SystemMonitor::getInfo($hash)
            ];
            if (empty($hash_temp['INFO'])) continue;
            if (empty($hash_temp["IP"]['country_code']))
                $hash_temp["IP"]["country_name"] = "Private";

            if ((time() - intval($hash_temp['INFO']['Update Time'])) > 500)
                $hashes_offline[$hash] = $hash_temp;
            else
                $hashes_online[$hash] = $hash_temp;
        }

        $hashes_online = SystemMonitor::sortByCountry($hashes_online);
        $hashes_offline = SystemMonitor::sortByCountry($hashes_offline);
        $this->assign("hashes_online", $hashes_online);
        $this->assign("hashes_offline", $hashes_offline);
        $this->assign("hide", $hide);
        return $this->fetch();
    }

    public function info($token = ''){
        $latest = json_decode(SystemMonitor::getLatest($token), TRUE);
        $info = SystemMonitor::getInfo($token);
        // $uptime_str = SystemMonitor::timeFormat(intval($info['Uptime']));
        $ip = SystemMonitor::fetchIPInfo(SystemMonitor::getIPByHash($token));
        ksort($info);
        $this->assign("latest", $latest);
        $this->assign("info", $info);
        $this->assign("token", $token);
        $this->assign("ip", $ip);
        if($this->request->isAjax())
            return $this->fetch("index/info_ajax");

        return $this->fetch();
    }
}
