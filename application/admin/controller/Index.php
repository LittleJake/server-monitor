<?php

namespace app\admin\controller;

use app\admin\validate\LoginTokenValidate;
use app\common\lib\SystemMonitor;
class Index extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (!session('?is_login'))
            $this->redirect('admin/index/login');
        $hash = SystemMonitor::getHashes();
        $ip = SystemMonitor::fetchIPInfo(array_values($hash));
        $hide = array_flip(SystemMonitor::getHide());

        asort($hash);
        $this->assign("hash", $hash);
        $this->assign("hide", $hide);
        $this->assign("ip", $ip);
        return $this->fetch();
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $validate = new LoginTokenValidate();
            if (!$validate->check($data))
                return json(['status' => 401, 'message' => $validate->getError()], 401);

            session('is_login',1);
            return json(['status' => 200, 'message' => 'OK']);
        }
        return $this->fetch();
    }

    public function logout(){
        session('is_login', null);
        $this->redirect('admin/index/login');
    }
}
