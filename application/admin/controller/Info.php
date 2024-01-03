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
                SystemMonitor::setDisplay($uuid, $this->request->patch('display'));
            if (!empty($this->request->patch('rename')))
                SystemMonitor::setDisplayName($uuid, $this->request->patch('rename'));
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isDelete()){
            SystemMonitor::deleteInfo($uuid);
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isPut()){
            //TODO execute command
            exit;
        }
        throw new HttpException(405, 'Method Not Allowed');
    }
}