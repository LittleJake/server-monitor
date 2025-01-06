<?php


namespace app\admin\controller;


use app\common\lib\SystemMonitor;
use think\exception\HttpException;

class Info extends Base
{
    public function index($uuid){
        //TODO RESTful API
        if (!session('?is_login'))
            throw new HttpException(403, 'Forbidden');

        if ($this->request->isPatch()){
            if (!empty($this->request->patch('display')))
                SystemMonitor::setHide($uuid, $this->request->patch('display'));
            if (!empty($this->request->patch('rename')))
                SystemMonitor::setDisplayName($uuid, $this->request->patch('rename'));
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isDelete()){
            SystemMonitor::deleteInfo($uuid);
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isPut()){
            $command_list = ['start', 'stop', 'restart', 'update', 'reboot', 'shutdown'];
            if (!empty($this->request->put('command')) && in_array($this->request->put('command'), $command_list))
                return json(SystemMonitor::setCommand($uuid, $this->request->put('command')));
        }
        throw new HttpException(405, 'Method Not Allowed');
    }
    public function clear(){
        //TODO RESTful API
        if (!session('?is_login'))
            throw new HttpException(403, 'Forbidden');
        
        $count = SystemMonitor::clearInfo();
        return json(['status' => 200, 'message' => "$count cleared"]);
    }
    public function purge(){
        //TODO RESTful API
        if (!session('?is_login'))
            throw new HttpException(403, 'Forbidden');
        SystemMonitor::deleteInfo();
        return json(['status' => 200, 'message' => "ok"]);
    }
}