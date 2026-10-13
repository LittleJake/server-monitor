<?php


namespace app\api\controller;


use app\common\lib\SystemMonitor;
use think\Exception;
use think\Response;

class Thermal extends \app\BaseController
{
    protected $middleware = ['CheckUUID', 'CheckJson'];

    /**
     * 显示资源列表
     *
     * @param string $uuid
     * @return Response
     * @throws Exception
     */
    public function get($uuid = '')
    {
        return json(SystemMonitor::collectionFormat(SystemMonitor::getCollection($uuid), 'Thermal'));
    }
}