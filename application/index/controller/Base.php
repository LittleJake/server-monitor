<?php

namespace app\index\controller;

use think\App;
use think\Controller;
use think\facade\Lang;

class Base extends Controller
{
    public function __construct(App $app = null)
    {
        parent::__construct($app);

        // 设置允许的语言
        Lang::setAllowLangList(['zh-cn','en-us']);
    }
}
