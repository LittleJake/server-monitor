<?php

namespace app\index\controller;

use app\common\lib\SystemMonitor;
use think\Facade\Log;


class Index extends Base
{
    protected $middleware = [
        'FlowControl',
        'CheckUUID' => ['only' => ['info']]
    ];

    public function index()
    {
        $uuids = SystemMonitor::getUUIDs();
        $hide = array_flip(SystemMonitor::getHide());
        $uuids_online = [];
        $uuids_offline = [];
        $time = time();

        foreach ($uuids as $uuid => $ip) {
            $uuid_temp = [
                'IP' => [],
                'INFO' => SystemMonitor::getInfo($uuid)
            ];
            if (empty($uuid_temp['INFO'])) continue;

            if (!empty($uuid_temp['INFO']['Country']) && !empty($uuid_temp['INFO']['Country Code']))
                $uuid_temp['IP'] = [
                    'country' => $uuid_temp['INFO']['Country'],
                    "countryCode" => $uuid_temp['INFO']['Country Code']
                ];
            else {
                $uuid_temp['IP'] = SystemMonitor::fetchIPInfo($ip);
                if (empty($uuid_temp["IP"]['countryCode']))
                    $uuid_temp["IP"]["country"] = "Private";
            }

            if (($time - intval($uuid_temp['INFO']['Update Time'])) > 500)
                $uuids_offline[$uuid] = $uuid_temp;
            else
                $uuids_online[$uuid] = $uuid_temp;
        }
        $uuids_online = SystemMonitor::sortByCountry($uuids_online);
        $uuids_offline = SystemMonitor::sortByCountry($uuids_offline);
        $this->assign("uuids_online", $uuids_online);
        $this->assign("uuids_offline", $uuids_offline);
        $this->assign("hide", $hide);
        return $this->fetch();
    }

    public function info($uuid = '')
    {
        $latest = json_decode(SystemMonitor::getLatest($uuid), TRUE);
        $info = SystemMonitor::getInfo($uuid);
        // $uptime_str = SystemMonitor::timeFormat(intval($info['Uptime']));
        $ip = SystemMonitor::fetchIPInfo(SystemMonitor::getIPByUUID($uuid));
        ksort($info);
        $this->assign("latest", $latest);
        $this->assign("info", $info);
        $this->assign("uuid", $uuid);
        $this->assign("ip", $ip);
        if ($this->request->isAjax())
            return $this->fetch("index/info_ajax");

        return $this->fetch();
    }
}
