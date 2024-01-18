<?php

namespace app\index\controller;

use app\common\lib\SystemMonitor;


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
        $online = SystemMonitor::sortByCountry(SystemMonitor::getOnline());
        $offline = SystemMonitor::sortByCountry(SystemMonitor::getOffline());
        $names = SystemMonitor::getDisplayName(array_keys($uuids));

        $this->assign("names", $names);
        $this->assign("online", $online);
        $this->assign("offline", $offline);
        $this->assign("hide", $hide);
        return $this->fetch();
    }

    public function info($uuid = '')
    {
        $latest = json_decode(SystemMonitor::getLatest($uuid), TRUE);
        $info = SystemMonitor::getInfo($uuid);
        // $uptime_str = SystemMonitor::timeFormat(intval($info['Uptime']));

        ksort($info);
        $this->assign("latest", $latest);
        $this->assign("info", $info);
        $this->assign("uuid", $uuid);
        if ($this->request->isAjax())
            return $this->fetch("index/info_ajax");

        return $this->fetch();
    }
    public function list()
    {
        $uuids = SystemMonitor::getUUIDs();
        //TODO
        if ($this->request->isAjax())
            return $this->fetch("index/list_ajax");
        return $this->fetch();
    }
    public function manifest(){
        $manifest = [
            "short_name"=> "Monitor",
            "name" => "Server Monitor",
            "icons" => [[
                "src"=> "/static/images/icons-192.png",
                "type"=> "image/png",
                "sizes"=>"192x192"
            ],[
                "src"=> "/static/images/icons-512.png",
                "type"=> "image/png",
                "sizes"=> "512x512"
            ]],
            "start_url"=> "/?source=pwa",
            "background_color"=> "#3F51B5",
            "display"=> "standalone",
            "scope"=> "/",
            "theme_color"=> "#3F51B5"
        ];

        return json($manifest);
    }
}
