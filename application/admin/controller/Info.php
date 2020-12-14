<?php


namespace app\admin\controller;


use app\common\lib\SystemMonitor;
use think\exception\HttpException;

class Info extends Base
{
    public function index($token){
        //TODO RESTful API
        if (!session('?is_login'))
            throw new HttpException(403, 'Forbidden');

        if ($this->request->isPatch()){
            SystemMonitor::setDisplay($token, $this->request->patch('display'));
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isDelete()){
            SystemMonitor::deleteInfo($token);
            return json(['status' => 200, 'message' => "ok"]);
        } else if ($this->request->isPut()){
            //TODO execute command
            exit;
        }
        throw new HttpException(405, 'Method Not Allowed');
    }
}