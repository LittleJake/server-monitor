<?php

namespace app\api\controller;

use app\common\lib\SystemMonitor;
use think\Controller;
use think\Exception;
use think\Response;

class Network extends Controller
{
    protected $middleware = ['FlowControl', 'CheckUUID', 'CheckJson'];

    /**
     * 显示资源列表
     *
     * @param string $uuid
     * @return Response
     * @throws Exception
     */
    public function get($uuid = '')
    {
        return json(SystemMonitor::collectionFormat(SystemMonitor::getCollection($uuid), "Network"));
    }
}
