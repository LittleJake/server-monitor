<?php

namespace app\admin\controller;

use app\admin\validate\LoginTokenValidate;
use app\common\lib\SystemMonitor;
use think\facade\Log;
use think\facade\Request;
use think\facade\Session;

class Index extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index($type = '')
    {
        if (!session('?is_login') && session("is_login") != 1)
            return $this->redirect('/admin/login');

        $uuids = SystemMonitor::getUUIDs();
        $data = SystemMonitor::getInfo(array_keys($uuids));
        $hide = array_flip(SystemMonitor::getHide());
        $names = SystemMonitor::getDisplayName(array_keys($uuids));
        asort($uuids);
        $this->assign("names", $names);
        $this->assign("uuids", $uuids);
        $this->assign("hide", $hide);
        $this->assign("data", $data);

        if(Request::isAjax() && $type == 'list')
            return  $this->fetch('index_ajax_list');
        else if(Request::isAjax() && $type == 'main')
            return  $this->fetch('index_ajax_main');
        else if(!Request::isAjax())
            return $this->fetch();
    }

    public function login()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $validate = new LoginTokenValidate();
            if (!$validate->check($data)) {
                Log::warning('Security event: admin login failed; ip=' . $this->request->ip());
                return json(['status' => 401, 'message' => $validate->getError()], 401);
            }

            session('is_login',1);
            Log::info('Security event: admin login succeeded; ip=' . $this->request->ip());
            return json(['status' => 200, 'message' => 'OK']);
        }
        return $this->fetch();
    }

    public function logout(){
        if (session('?is_login') && session('is_login') == 1)
            Log::info('Security event: admin logout; ip=' . $this->request->ip());
        Session::destroy();
        return $this->redirect('/admin/login');
    }
}
